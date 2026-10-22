<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    /**
     * Authenticate mobile user (Counselor / Principal / Staff) and issue API Bearer token.
     */
    public function login(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = DB::table('users')->where('email', $data['email'])->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Email atau kata sandi tidak sesuai.',
            ], 422);
        }

        $plainToken = Str::random(64);
        $expiresAt = now()->addDays(30);

        DB::table('api_tokens')->insert([
            'user_id' => $user->id,
            'token_hash' => hash('sha256', $plainToken),
            'last_used_at' => now(),
            'expires_at' => $expiresAt,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Login berhasil.',
            'data' => [
                'token' => $plainToken,
                'token_type' => 'Bearer',
                'expires_at' => $expiresAt->toIso8601String(),
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'school_npsn' => $user->school_npsn,
                    'school_name' => $user->school_name ?? DB::table('users')->where('school_npsn', $user->school_npsn)->value('school_name'),
                ],
            ],
        ]);
    }

    /**
     * Invalidate the current Bearer token.
     */
    public function logout(Request $request): JsonResponse
    {
        $plainToken = (string) $request->bearerToken();

        if ($plainToken) {
            DB::table('api_tokens')->where('token_hash', hash('sha256', $plainToken))->delete();
        }

        return response()->json([
            'success' => true,
            'message' => 'Berhasil logout.',
        ]);
    }

    /**
     * Get authenticated user profile.
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->attributes->get('api_user');

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
            ], 401);
        }

        $schoolName = $user->school_name ?? DB::table('users')->where('school_npsn', $user->school_npsn)->value('school_name');

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'school_npsn' => $user->school_npsn,
                'school_name' => $schoolName,
            ],
        ]);
    }
}
