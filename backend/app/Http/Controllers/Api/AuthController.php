<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Handle API Login.
     */
    public function login(Request $request)
    {
        // 1. Validasi Input
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // 2. Cari User berdasarkan Email
        $user = User::where('email', $request->email)->first();

        // 3. Verifikasi Password
        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Kredensial yang Anda berikan salah.'],
            ]);
        }

        // 4. Generate Sanctum Token
        $token = $user->createToken('auth_token')->plainTextToken;

        // 5. Return Response JSON yang bersih
        return response()->json([
            'message' => 'Login berhasil',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'department_id' => $user->department_id,
            ]
        ], 200);
    }

    /**
     * Handle API Logout.
     */
    public function logout(Request $request)
    {
        // Menghapus token aktif milik user yang sedang login
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logout berhasil'
        ], 200);
    }
}