<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class PasswordController extends Controller
{
    /**
     * Mostra a tela de alteração de senha.
     */
    public function edit()
    {
        return Inertia::render('Settings/Password');
    }

    /**
     * Atualiza a senha do usuário.
     */
    public function update(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'A senha atual está incorreta.']);
        }

        $user->forceFill([
            'password' => Hash::make($request->password),
        ])->save();

        return back()->with('success', 'Senha alterada com sucesso.');
    }
}
