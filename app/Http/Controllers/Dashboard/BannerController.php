<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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
        // Agora NÃO usamos mais base64 — recebemos um ARQUIVO REAL
        $data = $request->validate([
            'title'    => 'nullable|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'image'    => 'required|image|max:1024', // 1MB (em KB)
        ]);

        // Salva no storage público
        $path = $request->file('image')->store('banners', 'public_direct');

        Banner::create([
            'user_id'    => auth()->id(),
            'title'      => $data['title'] ?? null,
            'subtitle'   => $data['subtitle'] ?? null,
            'image_url'  => '/storage/'.$path, // agora salvamos caminho, não base64
        ]);

        return back()->with('success', 'Banner criado!');
    }

    public function destroy(Banner $banner)
    {
        abort_if($banner->user_id !== auth()->id(), 403);

        // Remover arquivo físico
        if ($banner->image_url && Storage::disk('public_direct')->exists($banner->image_url)) {
            Storage::disk('public_direct')->delete($banner->image_url);
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
