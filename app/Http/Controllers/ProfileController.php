<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function show()
    {
        $user = Auth::guard('web')->user();
        return view('profile.show', compact('user'));
    }

    public function update(Request $request)
    {
        $user = Auth::guard('web')->user();

        $validated = $request->validate([
            'name' => ['required','string','max:255'],
            'email' => ['required','email','max:255','unique:users,email,'.$user->id],
        ]);

        $user->update($validated);

        return back()->with('status', 'Perfil actualizado');
    }

    public function updatePassword(Request $request)
    {
        $user = Auth::guard('web')->user();

        $validated = $request->validate([
            'current_password' => ['required'],
            'password' => ['required','string','min:8','confirmed'],
        ]);

        if (! Hash::check($validated['current_password'], $user->password)) {
            return back()->withErrors(['current_password' => 'La contraseña actual no es correcta.']);
        }

        $user->password = Hash::make($validated['password']);
        $user->save();

        return back()->with('status', 'Contraseña actualizada');
    }
}
