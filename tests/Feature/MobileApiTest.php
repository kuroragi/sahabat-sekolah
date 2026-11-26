<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class MobileApiTest extends TestCase
{
    use RefreshDatabase;

    private string $schoolNpsn = '10307412';

    private int $counselorId = 0;

    protected function setUp(): void
    {
        parent::setUp();

        $now = now();

        $this->counselorId = DB::table('users')->insertGetId([
            'name' => 'Guru BK Mobile Uji',
            'email' => 'bk-mobile@school.test',
            'password' => Hash::make('password123'),
            'role' => 'COUNSELOR',
            'school_npsn' => $this->schoolNpsn,
            'school_name' => 'SMP Negeri Uji Bukittinggi',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    private function getBearerToken(): string
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'bk-mobile@school.test',
            'password' => 'password123',
        ]);

        return $response->json('data.token');
    }

    public function test_public_can_fetch_categories(): void
    {
        $response = $this->getJson('/api/v1/public/categories');

        $response->assertOk();
        $response->assertJsonStructure([
            'success',
            'data' => [
                '*' => ['id', 'name', 'status', 'subcategories'],
            ],
        ]);
    }

    public function test_public_can_submit_anonymous_report(): void
    {
        $response = $this->postJson('/api/v1/public/reports', [
            'school_npsn' => $this->schoolNpsn,
            'reporter_role' => 'VICTIM',
            'identity_mode' => 'ANONYMOUS',
            'category' => 'Cyberbullying',
            'subcategory' => 'Ancaman digital',
            'description' => 'Ini adalah kronologi pengaduan anonim dari aplikasi mobile.',
        ]);

        $response->assertStatus(201);
        $response->assertJson([
            'success' => true,
            'data' => [
                'identity_mode' => 'ANONYMOUS',
                'category' => 'Cyberbullying',
                'status' => 'PENDING_RESPONSE',
            ],
        ]);

        $token = $response->json('data.anonymous_token');
        $this->assertNotNull($token);
        $this->assertEquals(12, strlen($token));

        $caseNumber = $response->json('data.case_number');
        $this->assertNotNull($caseNumber);

        // Verify tracking works with the token
        $trackResponse = $this->postJson('/api/v1/public/reports/track', [
            'tracking_code' => $token,
        ]);

        $trackResponse->assertOk();
        $trackResponse->assertJson([
            'success' => true,
            'data' => [
                'case_number' => $caseNumber,
                'category' => 'Cyberbullying',
                'is_anonymous' => true,
            ],
        ]);
    }

    public function test_anonymous_messaging_channel(): void
    {
        $reportResponse = $this->postJson('/api/v1/public/reports', [
            'school_npsn' => $this->schoolNpsn,
            'reporter_role' => 'STUDENT',
            'identity_mode' => 'ANONYMOUS',
            'category' => 'Cyberbullying',
            'subcategory' => 'Ancaman digital',
            'description' => 'Laporan uji pesan anonim pada mobile.',
        ]);

        $token = $reportResponse->json('data.anonymous_token');
        $caseNumber = $reportResponse->json('data.case_number');

        // Reporter sends message
        $sendResponse = $this->postJson("/api/v1/public/anonymous/{$token}/messages", [
            'message' => 'Halo Bu Guru BK, saya sangat takut.',
        ]);
        $sendResponse->assertStatus(201);

        // Fetch messages
        $getMessagesResponse = $this->getJson("/api/v1/public/anonymous/{$token}/messages");
        $getMessagesResponse->assertOk();
        $getMessagesResponse->assertJsonCount(1, 'data.messages');

        // Counselor replies via API
        $bearerToken = $this->getBearerToken();
        $replyResponse = $this->withHeader('Authorization', "Bearer {$bearerToken}")
            ->postJson("/api/v1/cases/{$caseNumber}/anonymous-reply", [
                'message' => 'Tenang ya Nak, identitas kamu aman bersama kami.',
            ]);
        $replyResponse->assertStatus(201);

        // Reporter sees the counselor's reply
        $getMessagesAgain = $this->getJson("/api/v1/public/anonymous/{$token}/messages");
        $getMessagesAgain->assertJsonCount(2, 'data.messages');
    }

    public function test_counselor_auth_and_dashboard_overview(): void
    {
        $loginResponse = $this->postJson('/api/v1/auth/login', [
            'email' => 'bk-mobile@school.test',
            'password' => 'password123',
        ]);

        $loginResponse->assertOk();
        $token = $loginResponse->json('data.token');

        // Get Profile
        $profileResponse = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/me');
        $profileResponse->assertOk();
        $profileResponse->assertJsonPath('data.email', 'bk-mobile@school.test');

        // Get Dashboard
        $dashResponse = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/dashboard');
        $dashResponse->assertOk();
        $dashResponse->assertJsonStructure([
            'success',
            'data' => [
                'counts',
                'sla',
                'categories',
                'recent_cases',
            ],
        ]);

        // Logout
        $logoutResponse = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/auth/logout');
        $logoutResponse->assertOk();

        // Attempting to access protected route with deleted token fails
        $afterLogout = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/me');
        $afterLogout->assertStatus(401);
    }

    public function test_counselor_case_management_and_action(): void
    {
        $token = $this->getBearerToken();

        // Create a case via public report
        $reportResponse = $this->postJson('/api/v1/public/reports', [
            'school_npsn' => $this->schoolNpsn,
            'reporter_role' => 'VICTIM',
            'identity_mode' => 'CONFIDENTIAL',
            'reporter_name' => 'Budi Siswa',
            'category' => 'Cyberbullying',
            'subcategory' => 'Ancaman digital',
            'description' => 'Ada pesan mengancam di grup media sosial.',
        ]);

        $caseNumber = $reportResponse->json('data.case_number');

        // List cases
        $listResponse = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/cases');
        $listResponse->assertOk();
        $listResponse->assertJsonStructure(['success', 'data', 'meta']);

        // Show case details
        $showResponse = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson("/api/v1/cases/{$caseNumber}");
        $showResponse->assertOk();
        $showResponse->assertJsonPath('data.case.case_number', $caseNumber);

        // Process Action (Status transition to UNDER_VERIFICATION + note)
        $actionResponse = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/v1/cases/{$caseNumber}/actions", [
                'status' => 'UNDER_VERIFICATION',
                'note_type' => 'INTERNAL',
                'note_content' => 'Telah dilakukan verifikasi akun penyebar pesan.',
                'reason' => 'Verifikasi bukti tangkapan layar',
            ]);

        $actionResponse->assertOk();
        $actionResponse->assertJsonPath('data.status', 'UNDER_VERIFICATION');

        // Add Note
        $noteResponse = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/v1/cases/{$caseNumber}/notes", [
                'note_type' => 'COUNSELING',
                'content' => 'Siswa telah diberikan sesi konseling awal.',
            ]);
        $noteResponse->assertStatus(201);
    }

    public function test_counselor_notifications_api(): void
    {
        $token = $this->getBearerToken();

        $id1 = DB::table('notifications')->insertGetId([
            'type' => 'NEW_REPORT',
            'priority' => 'HIGH',
            'title' => 'Notif API 1',
            'message' => 'Pesan 1',
            'case_number' => null,
            'recipient_name' => 'Guru BK Mobile Uji',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $id2 = DB::table('notifications')->insertGetId([
            'type' => 'NEW_REPORT',
            'priority' => 'NORMAL',
            'title' => 'Notif API 2',
            'message' => 'Pesan 2',
            'case_number' => null,
            'recipient_name' => 'Guru BK Mobile Uji',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // List notifications
        $listResponse = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/notifications');
        $listResponse->assertOk();
        $this->assertEquals(2, $listResponse->json('meta.unread_count'));

        // Mark single read
        $readSingle = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/v1/notifications/{$id1}/read");
        $readSingle->assertOk();

        // Mark all read
        $readAll = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/notifications/read-all');
        $readAll->assertOk();

        $listAfter = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/notifications');
        $this->assertEquals(0, $listAfter->json('meta.unread_count'));
    }

    public function test_counselor_change_password_and_logout_all(): void
    {
        $token = $this->getBearerToken();

        // Failed change password: wrong current password
        $failResponse = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/auth/change-password', [
                'current_password' => 'wrong-pass',
                'new_password' => 'newpassword123',
                'new_password_confirmation' => 'newpassword123',
            ]);
        $failResponse->assertStatus(422);

        // Success change password
        $successResponse = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/auth/change-password', [
                'current_password' => 'password123',
                'new_password' => 'newpassword123',
                'new_password_confirmation' => 'newpassword123',
            ]);
        $successResponse->assertOk();

        // Old password can no longer login
        $oldLogin = $this->postJson('/api/v1/auth/login', [
            'email' => 'bk-mobile@school.test',
            'password' => 'password123',
        ]);
        $oldLogin->assertStatus(422);

        // New password can login
        $newLogin = $this->postJson('/api/v1/auth/login', [
            'email' => 'bk-mobile@school.test',
            'password' => 'newpassword123',
        ]);
        $newLogin->assertOk();
        $newToken = $newLogin->json('data.token');

        // Logout All
        $logoutAll = $this->withHeader('Authorization', "Bearer {$newToken}")
            ->postJson('/api/v1/auth/logout-all');
        $logoutAll->assertOk();

        // Both tokens now invalid
        $afterLogout1 = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/me');
        $afterLogout1->assertStatus(401);

        $afterLogout2 = $this->withHeader('Authorization', "Bearer {$newToken}")
            ->getJson('/api/v1/me');
        $afterLogout2->assertStatus(401);
    }

    public function test_achievements_api_categories_update_and_portfolio(): void
    {
        $token = $this->getBearerToken();

        $categoryId = DB::table('achievement_categories')->insertGetId([
            'code' => 'ROBOTIK',
            'name' => 'Sains & Robotik',
            'status' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Categories dropdown API
        $catResponse = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/achievements/categories');
        $catResponse->assertOk();
        $catResponse->assertJsonFragment(['name' => 'Sains & Robotik']);

        // Store Achievement
        $storeResponse = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/achievements', [
                'nisn' => '0098765432',
                'student_name' => 'Ahmad Rabbani',
                'class_name' => '8A',
                'category_id' => $categoryId,
                'achievement_name' => 'Juara 1 Olimpiade Fisika',
                'achievement_level' => 'KOTA',
                'ranking' => 'Juara 1',
                'achievement_date' => '2026-05-10',
            ]);
        $storeResponse->assertStatus(201);
        $achievementId = $storeResponse->json('data.id');

        // Update Achievement
        $updateResponse = $this->withHeader('Authorization', "Bearer {$token}")
            ->putJson("/api/v1/achievements/{$achievementId}", [
                'category_id' => $categoryId,
                'achievement_name' => 'Juara 1 Olimpiade Fisika Tingkat Kota Bukittinggi',
                'achievement_level' => 'KOTA',
                'ranking' => 'Juara 1 Emas',
                'achievement_date' => '2026-05-10',
            ]);
        $updateResponse->assertOk();
        $updateResponse->assertJsonPath('data.ranking', 'Juara 1 Emas');

        // Student Profile Portfolio
        $profileResponse = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/achievements/student/0098765432');
        $profileResponse->assertOk();
        $profileResponse->assertJsonPath('data.student_name', 'Ahmad Rabbani');
        $profileResponse->assertJsonPath('data.total_achievements', 1);
    }

    public function test_case_risk_assessment_reassign_and_multi_target_escalate(): void
    {
        $token = $this->getBearerToken();

        // 1. Create case
        $reportResponse = $this->postJson('/api/v1/public/reports', [
            'school_npsn' => $this->schoolNpsn,
            'reporter_role' => 'VICTIM',
            'identity_mode' => 'CONFIDENTIAL',
            'reporter_name' => 'Siswa Korban',
            'category' => 'Cyberbullying',
            'subcategory' => 'Ancaman digital',
            'description' => 'Kasus ancaman siber yang butuh asesmen risiko dan eskalasi.',
        ]);
        $caseNumber = $reportResponse->json('data.case_number');

        // 2. Risk Assessment (Score: 20 + 20 + 20 + 20 = 80 => CRITICAL)
        $riskResponse = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/v1/cases/{$caseNumber}/risk", [
                'category_score' => 20,
                'safety_score' => 20,
                'repetition_score' => 20,
                'impact_score' => 20,
                'reason' => 'Ancaman menyangkut keselamatan fisik korban',
            ]);
        $riskResponse->assertOk();
        $riskResponse->assertJsonPath('data.final_score', 80);
        $riskResponse->assertJsonPath('data.risk_level', 'CRITICAL');

        // 3. Reassign Case to another counselor in same school
        $counselor2Id = DB::table('users')->insertGetId([
            'name' => 'Guru BK Dua',
            'email' => 'bk-dua@school.test',
            'password' => Hash::make('password123'),
            'role' => 'COUNSELOR',
            'school_npsn' => $this->schoolNpsn,
            'school_name' => 'SMP Negeri Uji Bukittinggi',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $reassignResponse = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/v1/cases/{$caseNumber}/reassign", [
                'counselor_id' => $counselor2Id,
                'reason' => 'Beban kasus dialihkan ke Guru BK 2',
            ]);
        $reassignResponse->assertOk();
        $reassignResponse->assertJsonPath('data.assigned_to', 'Guru BK Dua');

        // 4. Multi-target Escalate (e.g. to UPTD PPA)
        $escalateResponse = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/v1/cases/{$caseNumber}/escalate", [
                'escalate_to' => 'CITY_PPA',
                'reason' => 'Kasus butuh pendampingan hukum dan psikologis UPTD PPA',
            ]);
        $escalateResponse->assertOk();
        $escalateResponse->assertJsonPath('data.escalated_to', 'CITY_PPA');
        $escalateResponse->assertJsonPath('data.notified_to', 'UPTD PPA Kota Bukittinggi');
    }

    public function test_principal_monitoring_api(): void
    {
        $token = $this->getBearerToken();

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/dashboard/principal');

        $response->assertOk();
        $response->assertJsonStructure([
            'success',
            'data' => [
                'overview' => [
                    'total_cases',
                    'active_cases',
                    'resolved_cases',
                    'high_risk_cases',
                    'escalated_cases',
                    'sla_overdue_cases',
                    'sla_warning_cases',
                ],
                'counselor_workload',
                'recent_escalations',
                'sla_alerts',
            ],
        ]);
    }
}
