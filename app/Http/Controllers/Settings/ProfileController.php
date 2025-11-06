<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class ProfileController extends Controller
{
    /**
     * Mostra a página de edição do perfil.
     */
    public function edit(Request $request)
    {
        $user = $request->user();

        return Inertia::render('Settings/Profile', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'surname' => $user->surname,
                'business_name' => $user->business_name,
                'slug' => $user->slug,
                'email' => $user->email,
                'address' => $user->address,
                'city' => $user->city,
                'state' => $user->state,
                'zip' => $user->zip,
                'phone_primary' => $user->phone_primary,
                'plan' => $user->plan,
                'is_active' => $user->is_active,
            ],
        ]);
    }

    /**
     * Atualiza os dados do perfil.
     */
    public function update(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'surname' => ['nullable', 'string', 'max:255'],
            'business_name' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'state' => ['nullable', 'string', 'max:2'],
            'zip' => ['nullable', 'string', 'max:10'],
            'phone_primary' => ['nullable', 'string', 'max:20'],
        ]);

        $user->update($data);

        return back()->with('success', 'Perfil atualizado com sucesso.');
    }

    /**
     * Exclui a conta do usuário.
     */
    public function destroy(Request $request)
    {
        $request->validate([
            'password' => ['required', 'string'],
        ]);

        $user = $request->user();

        if (!Hash::check($request->password, $user->password)) {
            return back()->withErrors(['password' => 'A senha informada está incorreta.']);
        }

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('success', 'Conta removida com sucesso.');
    }
}
