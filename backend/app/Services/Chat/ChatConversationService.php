<?php

namespace App\Services\Chat;

use App\Models\Conversation;
use App\Models\ConversationParticipant;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ChatConversationService
{
    private const ALLOWED_VISIBILITIES = ['private', 'public'];

    private const ALLOWED_JOIN_POLICIES = [
        'invite_only',
        'participants_can_invite',
        'anyone_with_permission',
        'public_join',
    ];

    private const ALLOWED_PARTICIPANT_ROLES = ['owner', 'admin', 'member', 'viewer', 'support'];

    public function __construct(
        protected ChatAccessService $accessService,
        protected ChatHistoryImportService $historyImportService,
    ) {
    }

    public function createPrivateGroupFromDirect(
        User $actor,
        Conversation $directConversation,
        array $newParticipantIds,
        array $payload
    ): Conversation {
        if ($directConversation->type !== 'direct') {
            throw ValidationException::withMessages([
                'conversation' => ['Source conversation must be a direct chat.'],
            ]);
        }

        if (! $this->accessService->canViewConversation($actor, $directConversation)) {
            throw new AuthorizationException('You are not allowed to create group from this direct conversation.');
        }

        $participant = $this->accessService->getParticipant($directConversation, $actor);
        $canCreateFromDirect = $this->accessService->canManage($actor, $directConversation)
            || $this->accessService->canInvite($actor, $directConversation)
            || in_array($participant?->role, ['owner', 'admin', 'support'], true);

        if (! $canCreateFromDirect) {
            throw new AuthorizationException('You are not allowed to create private group from this direct conversation.');
        }

        $historyMode = (string) ($payload['history_import_mode'] ?? 'none');
        if (! in_array($historyMode, ['none', 'from_date', 'from_message', 'full'], true)) {
            throw ValidationException::withMessages([
                'history_import_mode' => ['Invalid history import mode.'],
            ]);
        }

        $activeSourceParticipantIds = ConversationParticipant::query()
            ->where('conversation_id', $directConversation->id)
            ->where('status', 'active')
            ->pluck('user_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $candidateParticipantIds = array_values(array_unique(array_merge(
            $activeSourceParticipantIds,
            array_map('intval', $newParticipantIds),
            [$actor->id]
        )));

        $candidateParticipantIds = array_values(array_filter($candidateParticipantIds, fn (int $id) => $id > 0));
        if (count($candidateParticipantIds) < 3) {
            throw ValidationException::withMessages([
                'participant_ids' => ['New private group must have at least 3 unique participants.'],
            ]);
        }

        $users = User::query()->whereIn('id', $candidateParticipantIds)->get()->keyBy('id');
        if ($users->count() !== count($candidateParticipantIds)) {
            throw ValidationException::withMessages([
                'participant_ids' => ['One or more participants do not exist.'],
            ]);
        }

        return DB::transaction(function () use ($actor, $directConversation, $payload, $historyMode, $candidateParticipantIds, $users): Conversation {
            $conversation = Conversation::query()->create([
                'uuid' => (string) Str::uuid(),
                'type' => 'group',
                'visibility' => 'private',
                'title' => $payload['title'] ?? null,
                'description' => $payload['description'] ?? null,
                'owner_id' => $actor->id,
                'created_by' => $actor->id,
                'created_from_conversation_id' => $directConversation->id,
                'source' => 'internal',
                'status' => 'active',
                'join_policy' => 'invite_only',
                'history_import_mode' => $historyMode,
                'history_import_from_message_id' => $historyMode === 'from_message'
                    ? (int) ($payload['history_import_from_message_id'] ?? 0) ?: null
                    : null,
                'history_import_from_at' => $historyMode === 'from_date'
                    ? ($payload['history_import_from_at'] ?? null)
                    : null,
                'metadata' => null,
            ]);

            foreach ($candidateParticipantIds as $userId) {
                /** @var User $user */
                $user = $users->get($userId);
                $this->upsertParticipant($conversation, $user, [
                    'role' => $user->id === $actor->id ? 'owner' : 'member',
                    'status' => 'active',
                    'access_state' => 'full',
                    'can_invite' => $user->id === $actor->id,
                    'can_remove' => $user->id === $actor->id,
                    'can_send' => true,
                    'can_attach' => true,
                    'can_manage' => $user->id === $actor->id,
                    'can_moderate' => $user->id === $actor->id,
                ]);
            }

            $this->historyImportService->importHistory(
                $actor,
                $directConversation,
                $conversation,
                $historyMode,
                $payload['history_import_from_message_id'] ?? null,
                $payload['history_import_from_at'] ?? null
            );

            return $conversation->fresh();
        });
    }

    public function createDirectConversation(User $creator, int $targetUserId): Conversation
    {
        if ($creator->id === $targetUserId) {
            throw ValidationException::withMessages([
                'user_id' => ['Target user must be different from creator.'],
            ]);
        }

        $targetUser = User::query()->find($targetUserId);
        if (! $targetUser) {
            throw ValidationException::withMessages([
                'user_id' => ['Target user not found.'],
            ]);
        }

        $existing = $this->findExistingDirectConversation($creator->id, $targetUserId);
        if ($existing) {
            return $existing;
        }

        return DB::transaction(function () use ($creator, $targetUser): Conversation {
            $conversation = Conversation::query()->create([
                'uuid' => (string) Str::uuid(),
                'type' => 'direct',
                'visibility' => 'private',
                'title' => null,
                'description' => null,
                'owner_id' => $creator->id,
                'created_by' => $creator->id,
                'created_from_conversation_id' => null,
                'source' => 'internal',
                'status' => 'active',
                'join_policy' => 'invite_only',
                'history_import_mode' => 'none',
                'metadata' => null,
            ]);

            $this->upsertParticipant($conversation, $creator, [
                'role' => 'owner',
                'status' => 'active',
                'access_state' => 'full',
                'can_invite' => false,
                'can_remove' => true,
                'can_send' => true,
                'can_attach' => true,
                'can_manage' => true,
                'can_moderate' => false,
            ]);

            $this->upsertParticipant($conversation, $targetUser, [
                'role' => 'member',
                'status' => 'active',
                'access_state' => 'full',
                'can_invite' => false,
                'can_remove' => false,
                'can_send' => true,
                'can_attach' => true,
                'can_manage' => false,
                'can_moderate' => false,
            ]);

            return $conversation->fresh();
        });
    }

    public function createGroupConversation(User $creator, array $participantUserIds, array $payload): Conversation
    {
        $visibility = (string) ($payload['visibility'] ?? 'private');
        if (! in_array($visibility, self::ALLOWED_VISIBILITIES, true)) {
            throw ValidationException::withMessages([
                'visibility' => ['Invalid visibility.'],
            ]);
        }

        $joinPolicy = (string) ($payload['join_policy'] ?? 'invite_only');
        if (! in_array($joinPolicy, self::ALLOWED_JOIN_POLICIES, true)) {
            throw ValidationException::withMessages([
                'join_policy' => ['Invalid join policy.'],
            ]);
        }

        $participantUserIds = array_values(array_unique(array_map('intval', $participantUserIds)));
        $participantUserIds = array_values(array_filter($participantUserIds, fn (int $id) => $id > 0 && $id !== $creator->id));
        if (count($participantUserIds) < 1) {
            throw ValidationException::withMessages([
                'participant_ids' => ['Group conversation must include at least one additional participant.'],
            ]);
        }

        $users = User::query()->whereIn('id', $participantUserIds)->get()->keyBy('id');
        if ($users->count() !== count($participantUserIds)) {
            throw ValidationException::withMessages([
                'participant_ids' => ['One or more participants do not exist.'],
            ]);
        }

        return DB::transaction(function () use ($creator, $users, $payload, $visibility, $joinPolicy): Conversation {
            $conversation = Conversation::query()->create([
                'uuid' => (string) Str::uuid(),
                'type' => 'group',
                'visibility' => $visibility,
                'title' => $payload['title'] ?? null,
                'description' => $payload['description'] ?? null,
                'owner_id' => $creator->id,
                'created_by' => $creator->id,
                'created_from_conversation_id' => null,
                'source' => 'internal',
                'status' => 'active',
                'join_policy' => $joinPolicy,
                'history_import_mode' => 'none',
                'metadata' => null,
            ]);

            $this->upsertParticipant($conversation, $creator, [
                'role' => 'owner',
                'status' => 'active',
                'access_state' => 'full',
                'can_invite' => true,
                'can_remove' => true,
                'can_send' => true,
                'can_attach' => true,
                'can_manage' => true,
                'can_moderate' => true,
            ]);

            foreach ($users as $user) {
                $this->upsertParticipant($conversation, $user, [
                    'role' => 'member',
                    'status' => 'active',
                    'access_state' => 'full',
                    'can_invite' => false,
                    'can_remove' => false,
                    'can_send' => true,
                    'can_attach' => true,
                    'can_manage' => false,
                    'can_moderate' => false,
                ]);
            }

            return $conversation->fresh();
        });
    }

    public function addParticipant(User $actor, Conversation $conversation, int $userId, array $options = []): ConversationParticipant
    {
        if (! $this->accessService->canInvite($actor, $conversation)) {
            throw new AuthorizationException('You are not allowed to add participants to this conversation.');
        }

        $user = User::query()->find($userId);
        if (! $user) {
            throw ValidationException::withMessages([
                'user_id' => ['User not found.'],
            ]);
        }

        if (! in_array((string) $conversation->status, ['active', 'archived', 'closed'], true) || $conversation->status === 'deleted') {
            throw ValidationException::withMessages([
                'conversation' => ['Cannot add participant to deleted conversation.'],
            ]);
        }

        $role = $options['role'] ?? 'member';
        if (! in_array($role, self::ALLOWED_PARTICIPANT_ROLES, true)) {
            throw ValidationException::withMessages([
                'role' => ['Invalid participant role.'],
            ]);
        }

        return DB::transaction(function () use ($conversation, $user, $options, $role): ConversationParticipant {
            $existing = ConversationParticipant::query()
                ->where('conversation_id', $conversation->id)
                ->where('user_id', $user->id)
                ->first();

            if ($existing && in_array($existing->status, ['active', 'invited', 'blocked'], true)) {
                return $existing;
            }

            $attributes = [
                'role' => $role,
                'status' => 'active',
                'access_state' => 'full',
                'block_display_mode' => null,
                'can_invite' => (bool) ($options['can_invite'] ?? false),
                'can_remove' => (bool) ($options['can_remove'] ?? false),
                'can_send' => (bool) ($options['can_send'] ?? true),
                'can_attach' => (bool) ($options['can_attach'] ?? true),
                'can_manage' => (bool) ($options['can_manage'] ?? false),
                'can_moderate' => (bool) ($options['can_moderate'] ?? false),
                'blocked_by' => null,
                'blocked_at' => null,
                'blocked_reason' => null,
                'joined_at' => now(),
                'left_at' => null,
                'removed_at' => null,
            ];

            if ($existing) {
                $existing->fill($attributes)->save();

                return $existing->fresh();
            }

            return ConversationParticipant::query()->create(array_merge($attributes, [
                'conversation_id' => $conversation->id,
                'user_id' => $user->id,
            ]));
        });
    }

    public function removeParticipant(User $actor, Conversation $conversation, int $userId): void
    {
        if (! $this->accessService->canRemoveParticipant($actor, $conversation)) {
            throw new AuthorizationException('You are not allowed to remove participants from this conversation.');
        }

        $participant = ConversationParticipant::query()
            ->where('conversation_id', $conversation->id)
            ->where('user_id', $userId)
            ->whereIn('status', ['active', 'invited', 'blocked'])
            ->first();

        if (! $participant) {
            throw ValidationException::withMessages([
                'user_id' => ['Participant not found in conversation.'],
            ]);
        }

        if ($participant->role === 'owner') {
            $activeOwners = ConversationParticipant::query()
                ->where('conversation_id', $conversation->id)
                ->where('role', 'owner')
                ->where('status', 'active')
                ->count();

            if ($activeOwners <= 1) {
                throw ValidationException::withMessages([
                    'user_id' => ['Cannot remove the last owner from conversation.'],
                ]);
            }
        }

        $participant->status = 'removed';
        $participant->access_state = 'hidden';
        $participant->removed_at = now();
        $participant->can_invite = false;
        $participant->can_remove = false;
        $participant->can_manage = false;
        $participant->can_moderate = false;
        $participant->save();
    }

    public function listParticipants(User $actor, Conversation $conversation): Collection
    {
        if (! $this->accessService->canViewConversation($actor, $conversation)) {
            throw new AuthorizationException('You are not allowed to view conversation participants.');
        }

        return ConversationParticipant::query()
            ->where('conversation_id', $conversation->id)
            ->whereIn('status', ['active', 'invited', 'blocked'])
            ->orderByRaw("FIELD(role, 'owner', 'admin', 'support', 'member', 'viewer')")
            ->orderBy('id')
            ->get();
    }

    private function findExistingDirectConversation(int $userA, int $userB): ?Conversation
    {
        return Conversation::query()
            ->where('type', 'direct')
            ->where('visibility', 'private')
            ->where('status', 'active')
            ->whereHas('participants', function ($q) use ($userA): void {
                $q->where('user_id', $userA)->where('status', 'active');
            })
            ->whereHas('participants', function ($q) use ($userB): void {
                $q->where('user_id', $userB)->where('status', 'active');
            })
            ->withCount([
                'participants as active_participants_count' => function ($q): void {
                    $q->where('status', 'active');
                },
            ])
            ->get()
            ->first(fn (Conversation $conversation) => (int) $conversation->active_participants_count === 2);
    }

    private function upsertParticipant(Conversation $conversation, User $user, array $attributes): ConversationParticipant
    {
        $participant = ConversationParticipant::query()->firstOrNew([
            'conversation_id' => $conversation->id,
            'user_id' => $user->id,
        ]);

        $participant->fill(array_merge([
            'history_visibility_mode' => 'full',
            'history_visible_from_message_id' => null,
            'history_visible_from_at' => null,
            'history_visible_until_message_id' => null,
            'history_visible_until_at' => null,
            'last_read_message_id' => null,
            'last_read_at' => null,
            'muted_until' => null,
            'metadata' => null,
        ], $attributes));

        if (blank($participant->joined_at)) {
            $participant->joined_at = now();
        }

        $participant->save();

        return $participant->fresh();
    }
}
