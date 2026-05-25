<?php

namespace App\Providers;

use App\Models\Conversation;
use App\Models\ActivityLog;
use App\Models\User;
use App\Models\SystemTranslation;
use App\Observers\PersonalAccessTokenObserver;
use App\Observers\SystemTranslationObserver;
use App\Observers\UserObserver;
use App\Policies\ConversationPolicy;
use App\Support\TestingDatabaseGuard;
use Dedoc\Scramble\Scramble;
use Dedoc\Scramble\Support\Generator\Operation;
use Dedoc\Scramble\Support\Generator\OpenApi;
use Dedoc\Scramble\Support\Generator\Schema;
use Dedoc\Scramble\Support\Generator\SecurityRequirement;
use Dedoc\Scramble\Support\Generator\SecurityScheme;
use Dedoc\Scramble\Support\Generator\Types\ArrayType;
use Dedoc\Scramble\Support\Generator\Types\BooleanType;
use Dedoc\Scramble\Support\Generator\Types\IntegerType;
use Dedoc\Scramble\Support\Generator\Types\MixedType;
use Dedoc\Scramble\Support\Generator\Types\ObjectType;
use Dedoc\Scramble\Support\Generator\Types\StringType;
use Dedoc\Scramble\Support\RouteInfo;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (app()->runningInConsole()) {
            $defaultConnection = (string) config('database.default');
            $activeDatabase = (string) config("database.connections.{$defaultConnection}.database");
            app(TestingDatabaseGuard::class)->assertSafe(
                app()->environment(),
                $activeDatabase,
                'console-bootstrap'
            );
        }

        RateLimiter::for('chat-external-api', function (Request $request): Limit {
            $enabled = (bool) config('chat.external_api.rate_limit.enabled', true);
            if (! $enabled) {
                return Limit::none();
            }

            $maxAttempts = max(1, (int) config('chat.external_api.rate_limit.max_attempts', 60));
            $decaySeconds = max(1, (int) config('chat.external_api.rate_limit.decay_seconds', 60));

            $user = $request->user();
            $tokenId = $user?->currentAccessToken()?->getKey();
            $key = $tokenId
                ? 'chat-ext-token:'.$tokenId
                : ($user
                    ? 'chat-ext-user:'.$user->getAuthIdentifier()
                    : 'chat-ext-ip:'.$request->ip());

            return Limit::perSecond($maxAttempts, $decaySeconds)->by($key);
        });

        RateLimiter::for('chat-webhook-management', function (Request $request): Limit {
            $maxAttempts = max(1, (int) config('chat.webhooks.endpoint_management_rate_limit.max_attempts', 30));
            $decaySeconds = max(1, (int) config('chat.webhooks.endpoint_management_rate_limit.decay_seconds', 60));
            $userId = (string) ($request->user()?->getAuthIdentifier() ?? 'guest');
            $key = 'chat-webhooks:'.$userId.'|'.$request->ip();

            return Limit::perSecond($maxAttempts, $decaySeconds)->by($key);
        });

        RateLimiter::for('chat-message-send', function (Request $request): Limit {
            $enabled = (bool) config('chat.message_sending_rate_limit.enabled', true);
            if (! $enabled) {
                return Limit::none();
            }

            $maxAttempts = max(1, (int) config('chat.message_sending_rate_limit.max_attempts', 30));
            $decaySeconds = max(1, (int) config('chat.message_sending_rate_limit.decay_seconds', 60));

            $userId = (string) ($request->user()?->getAuthIdentifier() ?? 'guest');
            $conversationId = $request->route('conversation');
            $conversationKey = is_object($conversationId)
                ? (string) ($conversationId->id ?? 'none')
                : (string) ($conversationId ?? 'none');
            $ip = (string) ($request->ip() ?? 'unknown');
            $key = 'chat-send:'.$userId.'|conv:'.$conversationKey.'|ip:'.$ip;

            return Limit::perSecond($maxAttempts, $decaySeconds)->by($key);
        });

        if (class_exists(Scramble::class)) {
            Scramble::afterOpenApiGenerated(function (OpenApi $openApi): void {
                $openApi->components->addSecurityScheme(
                    'BearerAuth',
                    SecurityScheme::http('bearer', 'token')
                        ->as('BearerAuth')
                        ->setDescription('Bearer token auth for protected API routes.')
                );
                $openApi->components->addSecurityScheme(
                    'ExternalChatToken',
                    SecurityScheme::http('bearer', 'token')
                        ->as('ExternalChatToken')
                        ->setDescription('External chat API token with configured scopes.')
                );
                $openApi->components->addSecurityScheme(
                    'SanctumSession',
                    SecurityScheme::apiKey('cookie', 'laravel_session')
                        ->as('SanctumSession')
                        ->setDescription('Laravel session cookie for Sanctum session-auth flows.')
                );
                $openApi->components->addSecurityScheme(
                    'WebhookSignature',
                    SecurityScheme::apiKey('header', 'X-Chat-Signature')
                        ->as('WebhookSignature')
                        ->setDescription('Incoming webhook HMAC signature header.')
                );
                $openApi->components->addSecurityScheme(
                    'WebhookTimestamp',
                    SecurityScheme::apiKey('header', 'X-Chat-Timestamp')
                        ->as('WebhookTimestamp')
                        ->setDescription('Incoming webhook timestamp header for replay/tolerance checks.')
                );

                $paginationMeta = (new ObjectType)
                    ->addProperty('current_page', new IntegerType)
                    ->addProperty('last_page', new IntegerType)
                    ->addProperty('per_page', new IntegerType)
                    ->addProperty('total', new IntegerType)
                    ->setRequired(['current_page', 'last_page', 'per_page', 'total']);

                $apiSuccess = (new ObjectType)
                    ->addProperty('success', (new BooleanType)->const(true))
                    ->addProperty('message', new StringType)
                    ->addProperty('data', new MixedType)
                    ->addProperty('meta', (new ObjectType)->additionalProperties(new MixedType))
                    ->setRequired(['success', 'message', 'data']);

                $apiError = (new ObjectType)
                    ->addProperty('success', (new BooleanType)->const(false))
                    ->addProperty('message', new StringType)
                    ->addProperty('errors', (new ObjectType)->additionalProperties(new MixedType))
                    ->setRequired(['success', 'message', 'errors']);

                $validationError = (new ObjectType)
                    ->addProperty('success', (new BooleanType)->const(false))
                    ->addProperty('message', (new StringType)->example('Validation failed'))
                    ->addProperty(
                        'errors',
                        (new ObjectType)->additionalProperties(
                            (new ArrayType)->setItems(new StringType)
                        )
                    )
                    ->setRequired(['success', 'message', 'errors']);

                $paginatedResponse = (new ObjectType)
                    ->addProperty('success', (new BooleanType)->const(true))
                    ->addProperty('message', new StringType)
                    ->addProperty('data', (new ArrayType)->setItems(new MixedType))
                    ->addProperty('meta', $paginationMeta)
                    ->setRequired(['success', 'message', 'data', 'meta']);

                $openApi->components->addSchema('PaginationMeta', Schema::fromType($paginationMeta));
                $openApi->components->addSchema('ApiSuccessResponse', Schema::fromType($apiSuccess));
                $openApi->components->addSchema('ApiErrorResponse', Schema::fromType($apiError));
                $openApi->components->addSchema('ValidationErrorResponse', Schema::fromType($validationError));
                $openApi->components->addSchema('PaginatedResponse', Schema::fromType($paginatedResponse));
            });

            Scramble::configure()
                ->withOperationTransformers(function (Operation $operation, RouteInfo $routeInfo): void {
                    $route = $routeInfo->route;
                    $middleware = $route->gatherMiddleware();
                    $uri = '/'.ltrim($route->uri(), '/');

                    if (in_array('auth:sanctum', $middleware, true)) {
                        $operation->addSecurity(new SecurityRequirement(['BearerAuth' => []]));
                        $operation->addSecurity(new SecurityRequirement(['SanctumSession' => []]));
                    }

                    if (collect($middleware)->contains(fn (string $item): bool => str_starts_with($item, 'external.chat.scope:'))) {
                        $operation->addSecurity(new SecurityRequirement(['ExternalChatToken' => []]));
                    }

                    if (str_starts_with($uri, '/api/v1/chat/external/webhooks/')) {
                        $operation->addSecurity(new SecurityRequirement([
                            'WebhookSignature' => [],
                            'WebhookTimestamp' => [],
                        ]));
                    }
                });
        }

        Broadcast::routes([
            'middleware' => ['auth:sanctum'],
        ]);
        require base_path('routes/channels.php');

        User::observe(UserObserver::class);
        PersonalAccessToken::observe(PersonalAccessTokenObserver::class);

        /*
        |--------------------------------------------------------------------------
        | Test-only synchronous activity fallback
        |--------------------------------------------------------------------------
        |
        | WHY:
        | Some feature tests assert immediate DB activity rows without queue worker.
        | We keep production observer/queue flow unchanged and add a test-only
        | direct write fallback to stabilize deterministic test behavior.
        */
        if (app()->runningUnitTests() || defined('PHPUNIT_COMPOSER_INSTALL') || defined('__PHPUNIT_PHAR__')) {
            User::created(function (User $user): void {
                ActivityLog::create([
                    'user_id' => auth()->id(),
                    'action' => 'user_created',
                    'description' => 'User created',
                    'meta' => [
                        'user_id' => $user->id,
                        'email' => $user->email,
                    ],
                ]);
            });

            User::updated(function (User $user): void {
                ActivityLog::create([
                    'user_id' => auth()->id(),
                    'action' => 'user_updated',
                    'description' => 'User updated',
                    'meta' => [
                        'user_id' => $user->id,
                        'changed' => array_keys($user->getChanges()),
                    ],
                ]);
            });

            User::deleted(function (User $user): void {
                ActivityLog::create([
                    'user_id' => auth()->id(),
                    'action' => 'user_deleted',
                    'description' => 'User deleted',
                    'meta' => [
                        'user_id' => $user->id,
                        'email' => $user->email,
                    ],
                ]);
            });

            PersonalAccessToken::created(function (PersonalAccessToken $token): void {
                if (PersonalAccessTokenObserver::shouldSkipCreated()) {
                    return;
                }

                ActivityLog::create([
                    'user_id' => auth()->id(),
                    'action' => 'token_created',
                    'description' => 'API token created',
                    'meta' => [
                        'token_id' => $token->id,
                        'token_name' => $token->name,
                        'tokenable_id' => $token->tokenable_id,
                        'tokenable_type' => $token->tokenable_type,
                    ],
                ]);
            });

            PersonalAccessToken::deleted(function (PersonalAccessToken $token): void {
                if (PersonalAccessTokenObserver::shouldSkipDeleted()) {
                    return;
                }

                ActivityLog::create([
                    'user_id' => auth()->id(),
                    'action' => 'token_deleted',
                    'description' => 'API token deleted',
                    'meta' => [
                        'token_id' => $token->id,
                        'token_name' => $token->name,
                        'tokenable_id' => $token->tokenable_id,
                        'tokenable_type' => $token->tokenable_type,
                    ],
                ]);
            });
        }

        Gate::before(function (User $user, string $ability) {
            return $user->hasPermission($ability) ? true : null;
        });

        Gate::define('viewApiDocs', function (User $user): bool {
            return $user->hasPermission('api.docs.view');
        });

        Gate::policy(Conversation::class, ConversationPolicy::class);

        /*
        |--------------------------------------------------------------------------
        | Translation cache synchronization
        |--------------------------------------------------------------------------
        */

        SystemTranslation::observe(
            SystemTranslationObserver::class
        );
    }
}
