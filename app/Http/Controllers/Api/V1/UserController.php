<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Repositories\UserRepository;
use App\Http\Resources\UserResource;

class UserController extends Controller
{
    public function show(string $slug)
    {
        $user = (new UserRepository)->findBySlug($slug)->load('settings');

        return new UserResource($user);
    }
}
