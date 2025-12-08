<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Repositories\UserRepository;
use App\Http\Resources\SettingsResource;

class SettingsController extends Controller
{
    public function index(string $slug)
    {
        $user = (new UserRepository)->findBySlug($slug);

        return new SettingsResource($user->settings);
    }
}
