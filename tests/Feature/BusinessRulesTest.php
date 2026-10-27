<?php

namespace Tests\Feature;

use App\Helpers\SlaCalculator;
use App\Mail\CaseEscalatedPrincipalMail;
use App\Mail\DailyDigestMail;
use App\Mail\NewReportCounselorMail;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class BusinessRulesTest extends TestCase
{
    use RefreshDatabase;

    private string $schoolNpsn = '10307412';

    private string $otherSchoolNpsn = '10307415';

    private string $caseNumber = 'SS-TEST-000001';

    protected function setUp(): void
    {
        parent::setUp();
        Http::fake([
            '*/v1/data-sekolah*' => Http::response(['data' => [
                ['npsn' => $this->schoolNpsn, 'nama_sekolah' => 'SMP Negeri Uji'],
                ['npsn' => $this->otherSchoolNpsn, 'nama_sekolah' => 'SMP Negeri Lain'],
                ['npsn' => '99999999', 'nama_sekolah' => 'MIN Uji'],
            ]], 200),
            '*/v1/*' => Http::response(['data' => []], 200),
        ]);
        $now = now();
        DB::table('users')->insert(['name' => 'BK Uji', 'email' => 'bk-test@example.test', 'password' => Hash::make('password'), 'role' => 'COUNSELOR', 'school_npsn' => $this->schoolNpsn, 'school_name' => 'SMP Negeri Uji', 'created_at' => $now, 'updated_at' => $now]);
        $reportId = DB::table('reports')->insertGetId(['school_npsn' => $this->schoolNpsn, 'report_number' => $this->caseNumber, 'reporter_role' => 'WITNESS', 'identity_mode' => 'CONFIDENTIAL', 'category' => 'Perundungan Verbal', 'description' => 'Kronologi laporan uji yang cukup panjang.', 'submitted_at' => $now, 'created_at' => $now, 'updated_at' => $now]);
        $caseId = DB::table('cases')->insertGetId(['school_npsn' => $this->schoolNpsn, 'report_id' => $reportId, 'case_number' => $this->caseNumber, 'category' => 'Perundungan Verbal', 'risk_level' => 'MEDIUM', 'status' => 'PENDING_RESPONSE', 'opened_at' => $now, 'created_at' => $now, 'updated_at' => $now]);
        DB::table('case_slas')->insert(['case_id' => $caseId, 'status' => 'ON_TIME', 'response_deadline' => $now->addDay(), 'created_at' => $now, 'updated_at' => $now]);
    }

    private function counselorSession(): array
    {
        return ['user_id' => 1, 'user_name' => 'BK Uji', 'user_role' => 'COUNSELOR', 'school_npsn' => $this->schoolNpsn];
    }

    public function test_school_scope_denies_other_school_case(): void
    {
        $response = $this->withSession($this->counselorSession())->get('/cases/SS-NOT-IN-SCOPE');
        $response->assertNotFound();
    }

    public function test_invalid_case_transition_is_rejected(): void
    {
        $response = $this->withSession($this->counselorSession())->post('/cases/'.$this->caseNumber.'/status', ['status' => 'CLOSED']);
        $response->assertStatus(422);
    }

    public function test_case_cannot_close_without_valid_resolution_document(): void
    {
        DB::table('cases')->where('case_number', $this->caseNumber)->update(['status' => 'RESOLVED']);
        $response = $this->withSession($this->counselorSession())->post('/cases/'.$this->caseNumber.'/status', ['status' => 'CLOSED']);
        $response->assertStatus(422);
    }

    public function test_anonymous_report_creates_hashed_token(): void
    {
        $response = $this->post('/reports', [
            'school_npsn' => $this->schoolNpsn, 'reporter_role' => 'WITNESS', 'identity_mode' => 'ANONYMOUS',
            'category' => 'Cyberbullying', 'description' => 'Laporan anonim untuk pengujian token yang aman.',
        ]);
        $response->assertRedirect(route('reports.receipt'));
        $this->assertDatabaseCount('anonymous_report_tokens', 1);
        $this->assertDatabaseMissing('reporter_identities', ['full_name' => '']);
    }

    public function test_anonymous_report_creates_four_word_token_and_displays_receipt(): void
    {
        $response = $this->post('/reports', [
            'school_npsn' => $this->schoolNpsn,
            'reporter_role' => 'WITNESS',
            'identity_mode' => 'ANONYMOUS',
            'category' => 'Cyberbullying',
            'description' => 'Laporan uji anonim untuk pengujian token 4 kata yang ramah anak.',
        ]);

        $response->assertRedirect(route('reports.receipt'));
        $token = session('anonymous_token');
        $this->assertNotNull($token);

        $words = explode('-', $token);
        $this->assertCount(4, $words);

        $receiptResponse = $this->get(route('reports.receipt'));
        $receiptResponse->assertOk();
        $receiptResponse->assertSee($token);
        $receiptResponse->assertSee('Kartu Bukti Lapor Kamu');
        $receiptResponse->assertSee('Buka Ruang Obrolan Rahasia');
    }

    public function test_anonymous_channel_works_with_four_word_token(): void
    {
        $this->post('/reports', [
            'school_npsn' => $this->schoolNpsn,
            'reporter_role' => 'WITNESS',
            'identity_mode' => 'ANONYMOUS',
            'category' => 'Cyberbullying',
            'description' => 'Laporan uji anonim untuk pengujian saluran komunikasi dua arah.',
        ]);

        $token = session('anonymous_token');
        $this->assertNotNull($token);

        $response = $this->get('/anonymous-channel?token='.$token);
        $response->assertOk();
        $response->assertSee('Kanal Komunikasi Anonim');

        $msgResponse = $this->post('/anonymous-channel/message', [
            'token' => $token,
            'message' => 'Ini adalah pesan lanjutan dari pelapor anonim.',
        ]);

        $msgResponse->assertRedirect();
        $this->assertDatabaseHas('anonymous_messages', [
            'sender_type' => 'REPORTER',
            'message' => 'Ini adalah pesan lanjutan dari pelapor anonim.',
        ]);
    }

    public function test_crisis_keywords_trigger_critical_risk_and_immediate_sla(): void
    {
        $response = $this->post('/reports', [
            'school_npsn' => $this->schoolNpsn,
            'reporter_role' => 'VICTIM',
            'identity_mode' => 'ANONYMOUS',
            'category' => 'Perundungan Verbal',
            'description' => 'Saya merasa sangat tertekan dan ada ancaman senjata tajam dan ingin mati saja karena tidak tahan.',
        ]);

        $response->assertRedirect(route('reports.receipt'));
        $this->assertTrue(session('is_crisis'));

        $case = DB::table('cases')->orderByDesc('id')->first();
        $this->assertEquals('CRITICAL', $case->risk_level);

        $sla = DB::table('case_slas')->where('case_id', $case->id)->first();
        $diffHours = now()->diffInHours($sla->response_deadline);
        $this->assertLessThanOrEqual(1, $diffHours);
    }

    public function test_qr_code_preselects_school_on_create_page(): void
    {
        Http::fake([
            '*/v1/data-sekolah*' => Http::response([
                'data' => [
                    ['npsn' => $this->schoolNpsn, 'nama_sekolah' => 'SMP Negeri Uji'],
                ],
            ], 200),
        ]);

        $response = $this->get('/reports/create?npsn='.$this->schoolNpsn);
        $response->assertOk();
        $response->assertSee('Sekolah Terpilih Otomatis');
        $response->assertSee('Keluar Cepat');
    }

    public function test_sla_scanner_creates_only_one_warning_reminder(): void
    {
        DB::table('case_slas')->where('case_id', 1)->update(['response_deadline' => now()->addHours(5)]);
        $this->artisan('sahabat:sla-scan')->assertSuccessful();
        $this->artisan('sahabat:sla-scan')->assertSuccessful();
        $this->assertDatabaseHas('case_slas', ['case_id' => 1, 'status' => 'WARNING']);
        $this->assertDatabaseCount('case_reminders', 1);
    }

    public function test_case_reassignment_updates_assignee_and_logs_audit_note(): void
    {
        $response = $this->withSession($this->counselorSession())->post('/cases/'.$this->caseNumber.'/reassign', [
            'assigned_to' => 'Bpk. Ahmad (Wali Kelas)',
            'reason' => 'Guru BK sedang dinas luar, penanganan didelegasikan.',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('cases', [
            'case_number' => $this->caseNumber,
            'assigned_to' => 'Bpk. Ahmad (Wali Kelas)',
        ]);
        $this->assertDatabaseHas('case_notes', [
            'case_id' => 1,
            'note_type' => 'INTERNAL',
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'REASSIGN_CASE',
            'resource_id' => $this->caseNumber,
        ]);
    }

    public function test_fallback_notification_to_principal_when_school_has_no_counselor(): void
    {
        Mail::fake();

        // NPSN 99999999 tidak memiliki counselor, hanya principal
        $madrasahNpsn = '99999999';
        DB::table('users')->insert([
            'name' => 'Kepala Madrasah',
            'email' => 'kamad@madrasah.test',
            'password' => Hash::make('password'),
            'role' => 'PRINCIPAL',
            'school_npsn' => $madrasahNpsn,
            'school_name' => 'MIN Uji',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->post('/reports', [
            'school_npsn' => $madrasahNpsn,
            'reporter_role' => 'VICTIM',
            'identity_mode' => 'CONFIDENTIAL',
            'reporter_name' => 'Siswa Penguji',
            'category' => 'Perundungan Verbal',
            'description' => 'Laporan uji untuk madrasah tanpa guru BK terdaftar di sistem.',
        ]);

        $response->assertRedirect();

        Mail::assertQueued(NewReportCounselorMail::class, function (NewReportCounselorMail $mail) {
            return $mail->hasTo('kamad@madrasah.test') && ($mail->reportData['is_fallback'] ?? false) === true;
        });
    }

    public function test_level_3_escalation_to_city_admin(): void
    {
        Mail::fake();

        DB::table('users')->insert([
            'name' => 'Admin Disdikpora',
            'email' => 'admin.disdik@bukittinggi.test',
            'password' => Hash::make('password'),
            'role' => 'ADMIN',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->withSession($this->counselorSession())->post('/cases/'.$this->caseNumber.'/escalate', [
            'reason' => 'Perlu penanganan lintas sektor oleh Satgas PPKSP Kota Bukittinggi.',
            'escalate_to' => 'CITY_ADMIN',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('case_escalations', [
            'case_id' => 1,
            'notified_to' => 'Dinas Pendidikan Kota Bukittinggi',
        ]);

        Mail::assertQueued(CaseEscalatedPrincipalMail::class, function (CaseEscalatedPrincipalMail $mail) {
            return $mail->hasTo('admin.disdik@bukittinggi.test');
        });
    }

    public function test_sla_calculator_business_hours_and_waiting_reporter_info_pause(): void
    {
        // Uji SlaCalculator: Jumat jam 15:00 + 4 jam kerja -> Senin jam 11:00
        $fridayAfternoon = Carbon::create(2026, 10, 23, 15, 0, 0, 'Asia/Jakarta'); // Jumat
        $deadline = SlaCalculator::calculateDeadline($fridayAfternoon, 4, 'HIGH');
        $this->assertTrue($deadline->isMonday());
        $this->assertEquals(11, $deadline->hour);
        $this->assertEquals(0, $deadline->minute);

        // Uji transisi status ke WAITING_REPORTER_INFO (jeda SLA)
        DB::table('cases')->where('case_number', $this->caseNumber)->update(['status' => 'UNDER_VERIFICATION']);
        $response = $this->withSession($this->counselorSession())->post('/cases/'.$this->caseNumber.'/status', [
            'status' => 'WAITING_REPORTER_INFO',
            'reason' => 'Menunggu bukti tangkapan layar tambahan dari pelapor di kanal anonim.',
        ]);
        $response->assertRedirect();
        $this->assertDatabaseHas('cases', [
            'case_number' => $this->caseNumber,
            'status' => 'WAITING_REPORTER_INFO',
        ]);
    }

    public function test_daily_digest_command_queues_digest_email(): void
    {
        Mail::fake();

        $this->artisan('sahabat:send-daily-digest', ['--school' => $this->schoolNpsn])
            ->assertSuccessful();

        Mail::assertQueued(DailyDigestMail::class, function (DailyDigestMail $mail) {
            return $mail->hasTo('bk-test@example.test') && $mail->digestData['school_npsn'] === $this->schoolNpsn;
        });
    }
}
