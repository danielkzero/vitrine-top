<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\ProfileUpdateRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class ProfileController extends Controller
{
    /**
     * Mostra a página de edição do perfil.
     */
    public function edit(Request $request)
    {
        $user = $request->user();

        return Inertia::render('settings/Profile', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'surname' => $user->surname,
                'business_name' => $user->business_name,
                'slug' => $user->slug,
                'subtitle' => $user->subtitle,
                'avatar' => $user->avatar ? asset('storage/'.$user->avatar) : null,
                'logo_path' => $user->logo_path ? asset('storage/'.$user->logo_path) : null,
                'background_path' => $user->background_path ? asset('storage/'.$user->background_path) : null,
                'email' => $user->email,
                'address' => $user->address,
                'city' => $user->city,
                'state' => $user->state,
                'zip' => $user->zip,
                'phone_primary' => $user->phone_primary,
                'plan' => $user->plan,
                'is_active' => $user->is_active,
                'whatsapp' => $user->whatsapp,
            ],
        ]);
    }

    public function updateStore(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'business_name' => ['required', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string'],
            'slug' => [
                'required',
                'string',
                'max:255',
                Rule::unique('users', 'slug')->ignore($user->id),
            ],
            'description' => ['nullable', 'string'],
            'theme_color' => ['required', 'string', 'max:20'],
            'logo_file' => ['nullable', 'image', 'max:2048'],
            'background_file' => ['nullable', 'image', 'max:4096'],
            'whatsapp' => ['nullable', 'string', 'max:20'],
        ]);

        /* ================================
           APAGAR LOGO ANTERIOR
        ================================= */
        if ($request->hasFile('logo_file')) {
            // remove o /storage/ para o delete funcionar
            if ($user->logo_path) {
                $oldLogo = str_replace('/storage/', '', $user->logo_path);

                if (Storage::disk('public_direct')->exists($oldLogo)) {
                    Storage::disk('public_direct')->delete($oldLogo);
                }
            }

            $path = $request->file('logo_file')->store('logos', 'public_direct');
            $user->logo_path = '/storage/'.$path;
        }

        /* ================================
           APAGAR BACKGROUND ANTERIOR
        ================================= */
        if ($request->hasFile('background_file')) {
            if ($user->background_path) {
                $oldBg = str_replace('/storage/', '', $user->background_path);

                if (Storage::disk('public_direct')->exists($oldBg)) {
                    Storage::disk('public_direct')->delete($oldBg);
                }
            }

            $path = $request->file('background_file')->store('backgrounds', 'public_direct');
            $user->background_path = '/storage/'.$path;
        }

        /* ================================
           DADOS NORMAIS
        ================================= */
        $user->business_name = $data['business_name'];
        $user->subtitle = $data['subtitle'] ?? null;
        $user->slug = Str::slug($data['slug']);
        $user->description = $data['description'] ?? null;
        $user->theme_color = $data['theme_color'];
        $user->whatsapp = $data['whatsapp'];

        $user->save();

        return back()->with('success', 'Configurações da loja atualizadas!');
    }

    /**
     * Atualiza os dados gerais do perfil do usuário.
     */
    public function update(ProfileUpdateRequest $request)
    {
        $user = $request->user();
        $user->fill($request->validated());

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return redirect()->route('profile.edit')->with('success', 'Perfil atualizado com sucesso.');
    }

    /**
     * Remove a conta do usuário.
     */
    public function destroy(Request $request)
    {
        $request->validate([
            'password' => ['required', 'string'],
        ]);

        $user = $request->user();

        if (! Hash::check($request->password, $user->password)) {
            return back()->withErrors(['password' => 'A senha informada está incorreta.']);
        }

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('success', 'Conta removida com sucesso.');
    }
}
