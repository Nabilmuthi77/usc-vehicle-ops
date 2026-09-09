<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('success', 'Informasi profil berhasil diperbarui.');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ], [
            'password.required' => 'Password diperlukan untuk menghapus akun.',
            'password.current_password' => 'Password yang Anda masukkan salah.',
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }

    /**
     * Update the user's profile photo.
     */
    public function updatePhoto(Request $request): RedirectResponse
    {
        $request->validate([
            'photo' => ['required', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
        ], [
            'photo.required' => 'Mohon pilih foto terlebih dahulu.',
            'photo.image' => 'File harus berupa gambar.',
            'photo.mimes' => 'Format foto harus berupa: jpeg, png, jpg, gif.',
            'photo.max' => 'Ukuran foto maksimal adalah 2MB.',
        ]);

        $user = $request->user();

        if ($request->hasFile('photo')) {
            if ($user->photo_profile && \Illuminate\Support\Facades\Storage::disk('public')->exists($user->photo_profile)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($user->photo_profile);
            }

            $path = $request->file('photo')->store('profile_photos', 'public');
            $user->photo_profile = $path;
            $user->save();
        }

        return Redirect::route('profile.edit')->with('success', 'Foto profil berhasil diperbarui.');
    }
}
