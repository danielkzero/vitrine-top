<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\Request;

class BannerController extends Controller
{
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

        
        $data = $request->validate([
            'title' => 'nullable|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'image_base64' => 'required|string',
        ]);

        // Garantir tamanho máximo 1MB
        $bytes = strlen(base64_decode($data['image_base64']));
        if ($bytes > 1024 * 1024) {
            return back()->withErrors([
                'image_base64' => 'A imagem não pode exceder 1MB.'
            ]);
        }

        Banner::create([
            'user_id' => auth()->id(),
            ...$data
        ]);

        return back()->with('success', 'Banner criado!');
    }

    public function destroy(Banner $banner)
    {
        abort_if($banner->user_id !== auth()->id(), 403);

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
