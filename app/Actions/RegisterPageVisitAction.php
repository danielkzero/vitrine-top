<?php

namespace App\Actions;

use App\Models\Page;
use App\Models\PageVisit;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RegisterPageVisitAction
{
    public function execute(Page $page, Request $request): void
    {
        $visitorHash = $request->cookie('visitor_id');

        if (!$visitorHash) {
            $visitorHash = hash('sha256', sprintf('%s|%s', $request->ip(), (string) $request->userAgent()));
        }

        $page->increment('total_visits');

        PageVisit::firstOrCreate(
            [
                'page_id' => $page->id,
                'visitor_hash' => Str::limit($visitorHash, 64, ''),
            ],
            [
                'ip' => $request->ip(),
            ]
        );
    }
}
