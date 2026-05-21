<?php

namespace App\Services\Chat;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Builder;

class ChatConversationQueryService
{
    public function __construct(
        protected ChatAccessService $access
    ) {
    }

    public function visibleConversationsFor(User $user, array $filters = []): Builder
    {
        $query = Conversation::query()
            ->where('status', '!=', 'deleted');

        if ($this->canAdminBrowseConversations($user)) {
            return $this->applyConversationFilters($query, $filters);
        }

        if (! $user->hasAnyPermission(['chat.view', 'chat.conversations.view'])) {
            return $query->whereRaw('1 = 0');
        }

        $query->whereHas('participants', function (Builder $participantQuery) use ($user): void {
            $participantQuery
                ->where('user_id', $user->id)
                ->where(function (Builder $statusQuery): void {
                    $statusQuery
                        ->where(function (Builder $activeQuery): void {
                            $activeQuery
                                ->where('status', 'active')
                                ->where('access_state', '!=', 'hidden');
                        })
                        ->orWhere(function (Builder $blockedQuery): void {
                            $blockedQuery
                                ->where('status', 'blocked')
                                ->where('access_state', 'blocked')
                                ->whereIn('block_display_mode', ['show_notice', 'show_read_only_history']);
                        });
                });
        });

        return $this->applyConversationFilters($query, $filters);
    }

    public function visibleMessagesFor(User $user, Conversation $conversation): Builder
    {
        if (! $this->access->canViewMessages($user, $conversation)) {
            return Message::query()->whereRaw('1 = 0');
        }

        $query = Message::query()
            ->where('conversation_id', $conversation->id);

        if (! $this->canAdminBrowseConversations($user)) {
            $query->whereNull('deleted_at')
                ->where('status', '!=', 'deleted');
        }

        $bounds = $this->access->getVisibleHistoryBounds($user, $conversation);

        if ($bounds['from_message_id'] !== null) {
            $query->where('id', '>=', $bounds['from_message_id']);
        }

        if ($bounds['until_message_id'] !== null) {
            $query->where('id', '<=', $bounds['until_message_id']);
        }

        if ($bounds['from_at'] !== null) {
            $query->where('created_at', '>=', $bounds['from_at']);
        }

        if ($bounds['until_at'] !== null) {
            $query->where('created_at', '<=', $bounds['until_at']);
        }

        return $query->orderBy('id');
    }

    public function visibleMessagesCountFor(User $user, Conversation $conversation): int
    {
        return $this->visibleMessagesFor($user, $conversation)->count();
    }

    public function searchVisibleMessages(User $user, Conversation $conversation, array $filters = []): Builder
    {
        $query = $this->visibleMessagesFor($user, $conversation);

        $term = trim((string) ($filters['q'] ?? ''));
        if ($term !== '') {
            $query->where('body', 'like', '%'.$term.'%');
        }

        if (! empty($filters['type'])) {
            $query->where('type', (string) $filters['type']);
        }

        if (! empty($filters['sender_id'])) {
            $query->where('sender_id', (int) $filters['sender_id']);
        }

        if (! empty($filters['from'])) {
            $query->where('created_at', '>=', $filters['from']);
        }

        if (! empty($filters['to'])) {
            $query->where('created_at', '<=', $filters['to']);
        }

        if (array_key_exists('imported', $filters) && $filters['imported'] !== null) {
            $query->where('is_imported', (bool) $filters['imported']);
        }

        if (array_key_exists('has_attachments', $filters) && $filters['has_attachments'] !== null) {
            $query->whereHas('attachments', function (Builder $attachmentQuery): void {
                $attachmentQuery
                    ->whereNull('deleted_at')
                    ->where('status', 'active');
            }, $filters['has_attachments'] ? '>' : '=', 0);
        }

        return $query;
    }

    public function unreadCountFor(User $user, Conversation $conversation): int
    {
        $participant = $this->access->getParticipant($conversation, $user);
        if (! $participant) {
            return 0;
        }

        $query = $this->visibleMessagesFor($user, $conversation)
            ->where(function (Builder $senderQuery) use ($user): void {
                // WHY:
                // In chat UX, own messages are considered already read by sender
                // and should not inflate unread badges/counters.
                $senderQuery
                    ->whereNull('sender_id')
                    ->orWhere('sender_id', '!=', $user->id);
            });

        if ($participant->last_read_message_id !== null) {
            $query->where('id', '>', $participant->last_read_message_id);
        } elseif ($participant->last_read_at !== null) {
            $query->where('created_at', '>', $participant->last_read_at);
        }

        return $query->count();
    }

    /**
     * @throws AuthorizationException
     */
    public function adminConversationsFor(User $user, array $filters = []): Builder
    {
        if (! $user->hasPermission('chat.admin.view')) {
            throw new AuthorizationException('You are not authorized to view admin chat conversations.');
        }

        $query = Conversation::query()
            ->where('status', '!=', 'deleted');

        return $this->applyConversationFilters($query, $filters);
    }

    public function applyAdminMetadataGate(User $user, Conversation $conversation): bool
    {
        return $this->access->canViewConversation($user, $conversation)
            && $user->hasPermission('chat.admin.view_metadata');
    }

    private function canAdminBrowseConversations(User $user): bool
    {
        return $user->hasAnyPermission(['chat.admin.view', 'chat.admin.view_metadata']);
    }

    private function applyConversationFilters(Builder $query, array $filters): Builder
    {
        if (! empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (! empty($filters['visibility'])) {
            $query->where('visibility', $filters['visibility']);
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['source'])) {
            $query->where('source', $filters['source']);
        }

        if (($filters['unread'] ?? false) === true && isset($filters['user']) && $filters['user'] instanceof User) {
            $user = $filters['user'];
            $query->whereHas('participants', function (Builder $participantQuery) use ($user): void {
                $participantQuery
                    ->where('user_id', $user->id)
                    ->where(function (Builder $readQuery): void {
                        $readQuery
                            ->where(function (Builder $idQuery): void {
                                $idQuery
                                    ->whereColumn('conversation_participants.last_read_message_id', '<', 'conversations.last_message_id');
                            })
                            ->orWhere(function (Builder $timeQuery): void {
                                $timeQuery
                                    ->whereNotNull('conversation_participants.last_read_at')
                                    ->whereNotNull('conversations.last_message_at')
                                    ->whereColumn('conversation_participants.last_read_at', '<', 'conversations.last_message_at');
                            })
                            ->orWhere(function (Builder $freshUnreadQuery): void {
                                $freshUnreadQuery
                                    ->whereNull('conversation_participants.last_read_message_id')
                                    ->whereNull('conversation_participants.last_read_at')
                                    ->whereNotNull('conversations.last_message_id');
                            });
                    });
            });
        }

        return $query;
    }
}
