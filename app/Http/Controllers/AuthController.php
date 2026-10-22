<?php

namespace App\Http\Controllers;

use App\Helpers\AConnect;
use App\Mail\ResetPasswordMail;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $user = DB::table('users')->where('email', $data['email'])->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            return back()->withInput()->with('error', 'Email atau password tidak sesuai.');
        }

        $schoolName = null;
        if ($user->school_npsn) {
            $schoolsMap = (new AConnect)->getSekolahMap();
            $schoolName = $schoolsMap[$user->school_npsn] ?? $user->school_npsn;
        }

        $request->session()->regenerate();
        $request->session()->put([
            'user_id' => $user->id,
            'user_name' => $user->name,
            'user_role' => $user->role,
            'school_npsn' => $user->school_npsn,
            'school_name' => $schoolName,
        ]);

        $destination = match ($user->role) {
            'ADMIN' => route('admin.index'),
            'PRINCIPAL' => route('principal.index'),
            default => route('dashboard'),
        };

        return redirect($destination);
    }

    public function logout(Request $request): RedirectResponse
    {
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    /**
     * Show the forgot password request form.
     */
    public function showForgotPassword(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Send password reset link email.
     */
    public function sendResetLinkEmail(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
        ]);

        $user = DB::table('users')->where('email', $data['email'])->first();

        if ($user) {
            $token = Str::random(64);
            $now = now();

            DB::table('password_reset_tokens')->updateOrInsert(
                ['email' => $user->email],
                [
                    'token' => Hash::make($token),
                    'created_at' => $now,
                ]
            );

            $resetUrl = route('password.reset', [
                'token' => $token,
                'email' => $user->email,
            ]);

            try {
                Mail::to($user->email)->queue(new ResetPasswordMail([
                    'reset_url' => $resetUrl,
                    'expires_minutes' => 60,
                    'email' => $user->email,
                ], $user->name));
            } catch (\Throwable $e) {
                return back()->withInput()->with('error', 'Gagal mengirim email reset password: '.$e->getMessage());
            }
        }

        return back()->with('status', 'Jika alamat email terdaftar, tautan pengaturan ulang kata sandi telah dikirimkan ke kotak masuk email Anda.');
    }

    /**
     * Show the reset password form.
     */
    public function showResetPassword(Request $request, string $token): View
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->query('email', ''),
        ]);
    }

    /**
     * Process password reset update.
     */
    public function resetPassword(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $resetRecord = DB::table('password_reset_tokens')->where('email', $data['email'])->first();

        if (! $resetRecord) {
            return back()->withInput()->with('error', 'Permintaan reset kata sandi tidak ditemukan atau telah digunakan.');
        }

        // Cek kedaluwarsa 60 menit
        $createdAt = Carbon::parse($resetRecord->created_at);
        if ($createdAt->addMinutes(60)->isPast()) {
            DB::table('password_reset_tokens')->where('email', $data['email'])->delete();

            return back()->withInput()->with('error', 'Tautan reset kata sandi telah kedaluwarsa (lebih dari 60 menit). Silakan ajukan ulang.');
        }

        if (! Hash::check($data['token'], $resetRecord->token)) {
            return back()->withInput()->with('error', 'Token reset kata sandi tidak valid.');
        }

        $user = DB::table('users')->where('email', $data['email'])->first();
        if (! $user) {
            return back()->withInput()->with('error', 'Pengguna tidak ditemukan.');
        }

        // Perbarui kata sandi
        DB::table('users')->where('id', $user->id)->update([
            'password' => Hash::make($data['password']),
            'updated_at' => now(),
        ]);

        // Hapus token reset yang sudah digunakan
        DB::table('password_reset_tokens')->where('email', $data['email'])->delete();

        // Cabut token API aktif pengguna untuk keamanan sesi
        DB::table('api_tokens')->where('user_id', $user->id)->delete();

        return redirect()->route('login')->with('success', 'Kata sandi Anda berhasil diperbarui! Silakan masuk dengan kata sandi baru Anda.');
    }
}
