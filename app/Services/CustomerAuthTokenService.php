<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\CustomerApiToken;
use Illuminate\Support\Str;

class CustomerAuthTokenService
{
    public function issueToken(Customer $customer, string $name = 'default', ?\DateTimeInterface $expiresAt = null): array
    {
        $plainToken = Str::random(60);
        $hash = hash('sha256', $plainToken);

        CustomerApiToken::create([
            'customer_id' => $customer->id,
            'user_id' => $customer->user_id,
            'name' => $name,
            'token_hash' => $hash,
            'abilities' => ['*'],
            'expires_at' => $expiresAt,
        ]);

        return [
            'token' => $plainToken,
            'expires_at' => $expiresAt?->toDateTimeString(),
        ];
    }

    public function revokeCurrentToken(CustomerApiToken $token): void
    {
        $token->delete();
    }

    public function findValidToken(string $plainToken): ?CustomerApiToken
    {
        $hash = hash('sha256', $plainToken);

        $token = CustomerApiToken::query()
            ->with('customer')
            ->where('token_hash', $hash)
            ->first();

        if (!$token || $token->isExpired()) {
            return null;
        }

        $token->forceFill(['last_used_at' => now()])->save();

        return $token;
    }
}
