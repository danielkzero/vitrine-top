<?php

namespace App\Services;

use App\Repositories\UserRepository;

class UserService
{
    public function __construct(
        protected UserRepository $users
    ) {}

    public function getUserWithBasicData(string $slug)
    {
        return $this->users->findBySlug($slug)
            ->load(['settings']);
    }
}
