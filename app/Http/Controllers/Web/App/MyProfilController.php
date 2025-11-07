<?php

namespace App\Http\Controllers\Web\App;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class MyProfilController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        return view('pages.my-profile.index', compact('user'));
    }

    /**
     * Update the specified user
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'password' => 'nullable|min:8|confirmed',
        ]);

        if (!empty($validated['password'])) {
            $validated['password'] = bcrypt($validated['password']);
        } else {
            unset($validated['password']);
        }
        $user->update($validated);

        return redirect()->back()->with('success', 'Profile updated successfully');
    }
}
