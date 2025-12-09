<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class AppearanceController extends Controller
{
    /**
     * Exibe a tela de personalização de aparência (tema, cores, imagens, SEO).
     */
    public function edit(Request $request)
    {
        $settings = Setting::firstOrCreate(
            ['user_id' => $request->user()->id],
            [
                'theme_color' => '#000000',
                'text_color' => '#ffffff',
                'seo_defaults' => json_encode([]),
            ]
        );

        return Inertia::render('Settings/Appearance', [
            'settings' => $settings,
        ]);
    }

    /**
     * Atualiza as configurações visuais do usuário.
     */
    public function update(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'theme_color' => 'nullable|string|max:20',
            'text_color' => 'nullable|string|max:20',
            'seo_defaults' => 'nullable|array',
            'allow_name_suggestions' => 'boolean',
            'cover_image' => 'nullable|image|max:2048',
            'profile_image' => 'nullable|image|max:2048',
        ]);

        $settings = Setting::firstOrCreate(['user_id' => $user->id]);

        // Upload de imagens
        if ($request->hasFile('cover_image')) {
            if ($settings->cover_image) {
                Storage::disk('public_direct')->delete($settings->cover_image);
            }
            $data['cover_image'] = $request->file('cover_image')->store('covers', 'public_direct');
        }

        if ($request->hasFile('profile_image')) {
            if ($settings->profile_image) {
                Storage::disk('public_direct')->delete($settings->profile_image);
            }
            $data['profile_image'] = $request->file('profile_image')->store('profiles', 'public_direct');
        }

        $settings->update($data);

        return back()->with('success', 'Aparência atualizada com sucesso!');
    }
}
