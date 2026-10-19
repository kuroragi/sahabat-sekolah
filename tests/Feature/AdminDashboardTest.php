<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    private int $adminId = 0;

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake([
            '*/rest/v1/sekolah*' => Http::response([
                ['npsn' => '10300074', 'nama' => 'SMP Negeri 2 Bukittinggi'],
            ], 200),
        ]);

        $now = now();
        $this->adminId = DB::table('users')->insertGetId([
            'name' => 'Admin Sistem',
            'email' => 'admin-test@example.test',
            'password' => Hash::make('password'),
            'role' => 'ADMIN',
            'school_npsn' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    private function adminSession(): array
    {
        return [
            'user_id' => $this->adminId,
            'user_name' => 'Admin Sistem',
            'user_role' => 'ADMIN',
            'school_npsn' => null,
        ];
    }

    public function test_admin_dashboard_renders_successfully_with_empty_cases(): void
    {
        $response = $this->withSession($this->adminSession())->get(route('admin.index'));

        $response->assertStatus(200);
        $response->assertViewHas('monthlyLabels');
        $response->assertViewHas('monthlyCounts');
        $response->assertViewHas('slaChartData');
        $response->assertViewHas('statusChartData');

        $monthlyLabels = $response->viewData('monthlyLabels');
        $monthlyCounts = $response->viewData('monthlyCounts');
        $slaChartData = $response->viewData('slaChartData');
        $statusChartData = $response->viewData('statusChartData');

        $this->assertCount(12, $monthlyLabels);
        $this->assertCount(12, $monthlyCounts);
        $this->assertEquals(0, array_sum($monthlyCounts));
        $this->assertEquals(0, $slaChartData['total']);
        $this->assertEquals(0, $statusChartData['total']);

        // Assert asset and containers exist in view
        $response->assertSee('js/apexcharts.min.js');
        $response->assertSee('id="chart-monthly"', false);
        $response->assertSee('Belum ada data SLA');
        $response->assertSee('Belum ada data status');
    }

    public function test_admin_dashboard_renders_chart_data_accurately_with_cases(): void
    {
        $now = now();

        $reportId = DB::table('reports')->insertGetId([
            'school_npsn' => '10300074',
            'report_number' => 'TEST-001',
            'reporter_role' => 'VICTIM',
            'identity_mode' => 'CONFIDENTIAL',
            'category' => 'Perundungan Verbal',
            'description' => 'Contoh pengaduan tes',
            'submitted_at' => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $caseId = DB::table('cases')->insertGetId([
            'school_npsn' => '10300074',
            'report_id' => $reportId,
            'case_number' => 'CAS-2026-0001',
            'category' => 'Perundungan Verbal',
            'status' => 'IN_HANDLING',
            'risk_level' => 'HIGH',
            'opened_at' => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('case_slas')->insert([
            'case_id' => $caseId,
            'status' => 'ON_TRACK',
            'response_deadline' => $now->copy()->addHours(24),
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $response = $this->withSession($this->adminSession())->get(route('admin.index'));

        $response->assertStatus(200);

        $monthlyCounts = $response->viewData('monthlyCounts');
        $slaChartData = $response->viewData('slaChartData');
        $statusChartData = $response->viewData('statusChartData');

        $this->assertEquals(1, array_sum($monthlyCounts));
        $this->assertEquals(1, $slaChartData['total']);
        $this->assertEquals(1, $statusChartData['total']);
        $this->assertContains('On Track', $slaChartData['labels']);
        $this->assertContains('Ditangani', $statusChartData['labels']);

        $response->assertSee('id="chart-sla"', false);
        $response->assertSee('id="chart-case-status"', false);
    }

    public function test_store_user_fails_gracefully_when_email_already_exists(): void
    {
        $response = $this->withSession($this->adminSession())
            ->from('/admin/users')
            ->post('/admin/users', [
                'name' => 'Duplikat Admin',
                'email' => 'admin-test@example.test',
                'role' => 'COUNSELOR',
                'school_npsn' => '10300074',
            ]);

        $response->assertRedirect('/admin/users');
        $response->assertSessionHasErrors(['email']);
        $this->assertEquals(
            'Email ini sudah terdaftar dalam sistem.',
            session('errors')->first('email')
        );
    }

    public function test_store_user_normalizes_email_and_prevents_case_insensitive_duplicate(): void
    {
        // First user with mixed case and whitespace
        $response = $this->withSession($this->adminSession())
            ->from('/admin/users')
            ->post('/admin/users', [
                'name' => 'Guru BK Baru',
                'email' => '  Bk-Baru@School.Test  ',
                'role' => 'COUNSELOR',
                'school_npsn' => '10300074',
            ]);

        $response->assertRedirect('/admin/users');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'name' => 'Guru BK Baru',
            'email' => 'bk-baru@school.test',
        ]);

        // Second attempt with exact or uppercase version
        $responseDuplicate = $this->withSession($this->adminSession())
            ->from('/admin/users')
            ->post('/admin/users', [
                'name' => 'Guru BK Lain',
                'email' => 'BK-BARU@SCHOOL.TEST',
                'role' => 'COUNSELOR',
                'school_npsn' => '10300074',
            ]);

        $responseDuplicate->assertRedirect('/admin/users');
        $responseDuplicate->assertSessionHasErrors(['email']);
    }
}
