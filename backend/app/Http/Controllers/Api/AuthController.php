<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Auth\Events\Registered;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
            'phone' => 'required|string',
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'phone' => $data['phone'],
            'role_id' => 2
        ]);

        event(new Registered($user));

        return response()->json([
            'success' => true,
            'message' => 'Registration successful. Verification email has been sent.',
            'data' => [
                'email' => $user->email
            ]
        ], 200);
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $user = User::where('email', $data['email'])->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        if (! $user->hasVerifiedEmail()) {
        return response()->json([
            'message' => 'Please verify your email before logging in.'
        ], 403);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out']);
    }

    public function me(Request $request)
    {
        return response()->json($request->user());
    }

    public function setPassword(Request $request)
    {
        $data = $request->validate([
            'token' => 'required',
            'password' => 'required|min:6|confirmed',
        ]);

        try {
            $userId = \Illuminate\Support\Facades\Crypt::decryptString($data['token']);
            $user = User::findOrFail($userId);
            $user->password = Hash::make($data['password']);
            $user->email_verified_at = now();
            $user->save();

            return response()->json(['message' => 'Password set successfully']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Invalid token'], 400);
        }
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'name' => 'sometimes|required|string',
            'phone' => 'sometimes|required|string',
            'image' => 'sometimes|nullable|image|max:2048',
        ]);

        if (isset($data['image']) && $data['image'] instanceof \Illuminate\Http\UploadedFile) {
            $user->addMedia($data['image'])->toMediaCollection(User::MEDIA_COLLECTION);
        }
        if ($request->remove_avatar) {
            $user->clearMediaCollection(User::MEDIA_COLLECTION);
        }
        unset($data['image']);

        $user->update($data);

        return response()->json(['message' => 'Profile updated successfully', 'user' => $user]);
    }

    public function updatePassword(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:6|confirmed',
        ]);

        if (! Hash::check($data['current_password'], $user->password)) {
            return response()->json(['message' => 'Current password is incorrect'], 400);
        }

        $user->password = Hash::make($data['new_password']);
        $user->save();

        return response()->json(['message' => 'Password updated successfully']);
    }
}
