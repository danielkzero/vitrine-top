<?php

namespace App\Http\Controllers\Vitrine;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;

class VitrineController extends Controller
{
    private function renderPageShell(bool $withId = false)
    {
        return Inertia::render(
            $withId ? 'Vitrine/ShowId' : 'Vitrine/Show',
            []
        );
    }

    public function home(string $slug)
    {
        return $this->renderPageShell();
    }

    public function page(string $slug, string $pageKey)
    {
        return $this->renderPageShell();
    }

    public function pageWithId(string $slug, string $pageKey, int $id)
    {
        return $this->renderPageShell(true);
    }
}
