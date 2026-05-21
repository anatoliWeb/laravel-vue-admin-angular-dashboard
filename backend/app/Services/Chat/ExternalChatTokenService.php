<?php

namespace App\Services\Chat;

use Illuminate\Support\Str;

class ExternalChatTokenService
{
    public function generatePlainToken(): string
    {
        return $this->tokenPrefix().Str::random(48);
    }

    public function hashToken(string $plainToken): string
    {
        $algo = (string) config('chat.external_api.token_hash_algo', 'sha256');

        return hash($algo, $plainToken);
    }

    public function tokenPrefix(): string
    {
        return (string) config('chat.external_api.token_prefix', 'chat_ext_');
    }

    public function verifyToken(string $plainToken, string $storedHash): bool
    {
        return hash_equals($storedHash, $this->hashToken($plainToken));
    }
}

