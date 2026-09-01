<?php

namespace App\Http\Controllers\Api\Concerns;

use App\Models\User;

trait ResolvesStore
{
    protected function resolveStore(string $storeSlug): User
    {
        return User::query()->where('slug', $storeSlug)->firstOrFail();
    }
}
