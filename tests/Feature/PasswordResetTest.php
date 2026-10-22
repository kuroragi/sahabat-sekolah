<?php

namespace Tests\Feature;

use App\Mail\ResetPasswordMail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    private string $userEmail = 'guru.bk@sekolah.test';

    private int $userId = 0;

    protected function setUp(): void
    {
        parent::setUp();

        $now = now();
        $this->userId = DB::table('users')->insertGetId([
            'name' => 'Guru BK Ratna',
            'email' => $this->userEmail,
            'password' => Hash::make('passwordLama123'),
            'role' => 'COUNSELOR',
            'school_npsn' => '10307412',
            'school_name' => 'SMP Negeri Uji Bukittinggi',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    public function test_user_can_view_forgot_password_screen(): void
    {
        $response = $this->get('/forgot-password');

        $response->assertOk();
        $response->assertSee('Lupa Kata Sandi?');
    }

    public function test_login_screen_links_to_forgot_password(): void
    {
        $response = $this->get('/login');

        $response->assertOk();
        $response->assertSee(route('password.request'));
        $response->assertSee('Lupa password?');
    }

    public function test_submitting_forgot_password_queues_reset_email(): void
    {
        Mail::fake();

        $response = $this->post('/forgot-password', [
            'email' => $this->userEmail,
        ]);

        $response->assertSessionHas('status');

        $resetToken = DB::table('password_reset_tokens')->where('email', $this->userEmail)->first();
        $this->assertNotNull($resetToken);

        Mail::assertQueued(ResetPasswordMail::class, function (ResetPasswordMail $mail) {
            return $mail->hasTo($this->userEmail) &&
                str_contains($mail->resetData['reset_url'], 'reset-password/') &&
                $mail->resetData['expires_minutes'] === 60;
        });
    }

    public function test_user_can_view_reset_password_screen_with_token(): void
    {
        $token = Str::random(64);
        DB::table('password_reset_tokens')->insert([
            'email' => $this->userEmail,
            'token' => Hash::make($token),
            'created_at' => now(),
        ]);

        $response = $this->get("/reset-password/{$token}?email=".urlencode($this->userEmail));

        $response->assertOk();
        $response->assertSee('Atur Kata Sandi Baru');
    }

    public function test_user_can_reset_password_with_valid_token(): void
    {
        $token = Str::random(64);
        DB::table('password_reset_tokens')->insert([
            'email' => $this->userEmail,
            'token' => Hash::make($token),
            'created_at' => now(),
        ]);

        $response = $this->post('/reset-password', [
            'token' => $token,
            'email' => $this->userEmail,
            'password' => 'PasswordBaru456#',
            'password_confirmation' => 'PasswordBaru456#',
        ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('success');

        // Token reset harus sudah dihapus dari tabel
        $this->assertDatabaseMissing('password_reset_tokens', [
            'email' => $this->userEmail,
        ]);

        // Verifikasi password baru dapat digunakan untuk login
        $updatedUser = DB::table('users')->where('id', $this->userId)->first();
        $this->assertTrue(Hash::check('PasswordBaru456#', $updatedUser->password));
    }

    public function test_cannot_reset_password_with_invalid_token(): void
    {
        $validToken = Str::random(64);
        DB::table('password_reset_tokens')->insert([
            'email' => $this->userEmail,
            'token' => Hash::make($validToken),
            'created_at' => now(),
        ]);

        $response = $this->post('/reset-password', [
            'token' => 'invalid-token-123456',
            'email' => $this->userEmail,
            'password' => 'PasswordBaru456#',
            'password_confirmation' => 'PasswordBaru456#',
        ]);

        $response->assertSessionHas('error', 'Token reset kata sandi tidak valid.');
    }

    public function test_cannot_reset_password_with_expired_token(): void
    {
        $token = Str::random(64);
        DB::table('password_reset_tokens')->insert([
            'email' => $this->userEmail,
            'token' => Hash::make($token),
            'created_at' => now()->subMinutes(65), // Kedaluwarsa > 60 menit
        ]);

        $response = $this->post('/reset-password', [
            'token' => $token,
            'email' => $this->userEmail,
            'password' => 'PasswordBaru456#',
            'password_confirmation' => 'PasswordBaru456#',
        ]);

        $response->assertSessionHas('error');
    }

    public function test_mobile_api_forgot_password_and_reset_password(): void
    {
        Mail::fake();

        // 1. API Forgot Password
        $forgotResponse = $this->postJson('/api/v1/auth/forgot-password', [
            'email' => $this->userEmail,
        ]);

        $forgotResponse->assertOk();
        $forgotResponse->assertJson([
            'success' => true,
        ]);

        Mail::assertQueued(ResetPasswordMail::class);

        // 2. Set token langsung untuk simulasi reset API
        $token = Str::random(64);
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $this->userEmail],
            [
                'token' => Hash::make($token),
                'created_at' => now(),
            ]
        );

        // 3. API Reset Password
        $resetResponse = $this->postJson('/api/v1/auth/reset-password', [
            'email' => $this->userEmail,
            'token' => $token,
            'password' => 'PasswordMobileBaru789#',
            'password_confirmation' => 'PasswordMobileBaru789#',
        ]);

        $resetResponse->assertOk();
        $resetResponse->assertJson([
            'success' => true,
        ]);

        $updatedUser = DB::table('users')->where('id', $this->userId)->first();
        $this->assertTrue(Hash::check('PasswordMobileBaru789#', $updatedUser->password));
    }
}
