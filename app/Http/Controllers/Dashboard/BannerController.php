<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Services\PlanLimitService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class BannerController extends Controller
{
    public function __construct(private readonly PlanLimitService $planLimitService)
    {
    }

    public function index()
    {
        $banners = Banner::where('user_id', auth()->id())
            ->orderBy('order')
            ->get();

        return inertia('Dashboard/Banners/Index', [
            'banners' => $banners,
        ]);
    }

    public function store(Request $request)
    {
        $count = Banner::where('user_id', auth()->id())->count();
        try {
            $this->planLimitService->ensureCanAddBanner(auth()->user(), $count);
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        $data = $request->validate([
            'title' => 'nullable|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'image' => 'required|image|max:1024',
        ]);

        $path = $request->file('image')->store('banners', 'public_direct');

        Banner::create([
            'user_id' => auth()->id(),
            'title' => $data['title'] ?? null,
            'subtitle' => $data['subtitle'] ?? null,
            'image_url' => '/storage/'.$path,
        ]);

        return back()->with('success', 'Banner criado.');
    }

    public function destroy(Banner $banner)
    {
        abort_if($banner->user_id !== auth()->id(), 403);

        if ($banner->image_url) {
            $storedPath = ltrim(str_replace('/storage/', '', $banner->image_url), '/');
            if (Storage::disk('public_direct')->exists($storedPath)) {
                Storage::disk('public_direct')->delete($storedPath);
            }
        }

        $banner->delete();

        return back()->with('success', 'Banner removido.');
    }

    public function reorder(Request $request)
    {
        foreach ($request->banners as $item) {
            Banner::where('id', $item['id'])
                ->where('user_id', auth()->id())
                ->update(['order' => $item['order']]);
        }

        return back();
    }
}
