<?php

namespace App\Repositories;

use App\Models\User;

class UserRepository
{
    public function findBySlug(string $slug): User
    {
        return User::where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();
    }
}
