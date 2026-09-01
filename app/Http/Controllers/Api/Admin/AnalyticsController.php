<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Services\AnalyticsService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    public function __construct(private readonly AnalyticsService $analyticsService)
    {
    }

    public function summary(Request $request): JsonResponse
    {
        $user = $request->user();

        $start = $request->filled('start')
            ? Carbon::parse((string) $request->string('start'))->startOfDay()
            : null;
        $end = $request->filled('end')
            ? Carbon::parse((string) $request->string('end'))->endOfDay()
            : null;

        return response()->json(
            $this->analyticsService->summary($user, $start, $end)
        );
    }

    public function ordersByPeriod(Request $request): JsonResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'group_by' => ['nullable', 'in:day,month'],
            'start' => ['nullable', 'date'],
            'end' => ['nullable', 'date'],
        ]);

        $start = isset($data['start']) ? Carbon::parse($data['start'])->startOfDay() : null;
        $end = isset($data['end']) ? Carbon::parse($data['end'])->endOfDay() : null;

        $result = $this->analyticsService->ordersByPeriod($user, $data['group_by'] ?? 'day', $start, $end);

        return response()->json(['data' => $result]);
    }
}
