<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class SimulateCasesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sahabat:simulate-cases';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Simulasi alur 2 kasus perundungan: (1) Selesai tuntas di sekolah, (2) Eskalasi bertingkat ke Disdik & UPTD PPA';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('================================================================');
        $this->info('  SIMULASI 2 KASUS PERUNDUNGAN PLATFORM SAHABAT SEKOLAH');
        $this->info('================================================================');

        $npsn = '10300074';
        $schoolName = 'SMP NEGERI 2 BUKITTINGGI';
        $now = Carbon::now();

        // -------------------------------------------------------------
        // KASUS 1: SELESAI DI TINGKAT SEKOLAH (RESOLVED & CLOSED)
        // -------------------------------------------------------------
        $this->newLine();
        $this->info('>>> [1/2] Mensimulasikan Kasus 1: Selesai di Sekolah...');

        $case1Number = 'SS-2026-000201';
        $case1OpenedAt = $now->copy()->subDays(4)->setTime(9, 30);
        $case1ResolvedAt = $now->copy()->subDays(1)->setTime(14, 0);
        $case1ClosedAt = $now->copy()->subHours(2);

        // Bersihkan data lama jika ada
        $existingCase1 = DB::table('cases')->where('case_number', $case1Number)->first();
        if ($existingCase1) {
            DB::table('reports')->where('id', $existingCase1->report_id)->delete();
            DB::table('cases')->where('id', $existingCase1->id)->delete();
        }

        $report1Id = DB::table('reports')->insertGetId([
            'school_npsn' => $npsn,
            'report_number' => $case1Number,
            'reporter_role' => 'VICTIM',
            'identity_mode' => 'CONFIDENTIAL',
            'category' => 'Perundungan Verbal',
            'subcategory' => 'Hinaan atau julukan merendahkan',
            'description' => 'Saya terus-menerus diejek dan dipanggil dengan nama julukan yang merendahkan kondisi fisik saya oleh 2 teman sekelas saat jam istirahat di lorong sekolah. Hal ini membuat saya malu dan takut masuk kelas.',
            'submitted_at' => $case1OpenedAt,
            'created_at' => $case1OpenedAt,
            'updated_at' => $case1ClosedAt,
        ]);

        DB::table('reporter_identities')->insert([
            'report_id' => $report1Id,
            'full_name' => 'Muhammad Fauzan',
            'contact' => '08126789001',
            'created_at' => $case1OpenedAt,
            'updated_at' => $case1OpenedAt,
        ]);

        $case1Id = DB::table('cases')->insertGetId([
            'school_npsn' => $npsn,
            'report_id' => $report1Id,
            'case_number' => $case1Number,
            'category' => 'Perundungan Verbal',
            'risk_level' => 'MEDIUM',
            'status' => 'CLOSED',
            'assigned_to' => 'Bu Ratna Sari',
            'opened_at' => $case1OpenedAt,
            'resolved_at' => $case1ResolvedAt,
            'closed_at' => $case1ClosedAt,
            'created_at' => $case1OpenedAt,
            'updated_at' => $case1ClosedAt,
        ]);

        // SLA Kasus 1 (Completed on time)
        DB::table('case_slas')->insert([
            'case_id' => $case1Id,
            'status' => 'COMPLETED',
            'response_deadline' => $case1OpenedAt->copy()->addHours(24),
            'initial_response_at' => $case1OpenedAt->copy()->addHours(2),
            'created_at' => $case1OpenedAt,
            'updated_at' => $case1ClosedAt,
        ]);

        // Histori Status Kasus 1
        $histories1 = [
            ['PENDING_RESPONSE', 'UNDER_VERIFICATION', 'Guru BK membaca laporan dan memulai verifikasi awal.', $case1OpenedAt->copy()->addHours(2)],
            ['UNDER_VERIFICATION', 'IN_HANDLING', 'Laporan valid, memanggil korban dan terduga pelaku untuk konseling terpisah.', $case1OpenedAt->copy()->addHours(5)],
            ['IN_HANDLING', 'RESOLVED', 'Mediasi bersama orang tua kedua pihak berhasil dengan komitmen tertulis.', $case1ResolvedAt],
            ['RESOLVED', 'CLOSED', 'Berita Acara Resolusi telah ditandatangani dan diunggah. Kasus ditutup resmi.', $case1ClosedAt],
        ];
        foreach ($histories1 as [$old, $new, $reason, $time]) {
            DB::table('case_status_histories')->insert([
                'case_id' => $case1Id,
                'old_status' => $old,
                'new_status' => $new,
                'reason' => $reason,
                'changed_by' => 'Bu Ratna Sari',
                'created_at' => $time,
                'updated_at' => $time,
            ]);
        }

        // Tindakan Kasus 1
        DB::table('case_actions')->insert([
            [
                'case_id' => $case1Id,
                'action_type' => 'VERIFICATION',
                'description' => 'Wawancara mendalam dengan korban (M. Fauzan) di ruang BK yang aman.',
                'performed_by' => 'Bu Ratna Sari',
                'created_at' => $case1OpenedAt->copy()->addHours(3),
                'updated_at' => $case1OpenedAt->copy()->addHours(3),
            ],
            [
                'case_id' => $case1Id,
                'action_type' => 'HANDLING',
                'description' => 'Konseling pembinaan karakter dan empati kepada kedua pelaku bersama Wali Kelas 8B.',
                'performed_by' => 'Bu Ratna Sari',
                'created_at' => $case1OpenedAt->copy()->addDay(),
                'updated_at' => $case1OpenedAt->copy()->addDay(),
            ],
            [
                'case_id' => $case1Id,
                'action_type' => 'RESOLUTION',
                'description' => 'Penandatanganan Berita Acara Mediasi Damai & Komitmen Perlindungan Ramah Anak.',
                'performed_by' => 'Bu Ratna Sari',
                'created_at' => $case1ResolvedAt,
                'updated_at' => $case1ResolvedAt,
            ],
        ]);

        // Catatan Konseling Kasus 1
        DB::table('case_notes')->insert([
            'case_id' => $case1Id,
            'note_type' => 'COUNSELING',
            'content' => 'Kedua pelaku menyadari dampak buruk ejekan verbal, meminta maaf secara tulus kepada Fauzan, dan menandatangani surat komitmen untuk menjaga pertemanan yang sehat.',
            'visibility' => 'PRIVATE_BK',
            'created_by' => 'Bu Ratna Sari',
            'created_at' => $case1ResolvedAt,
            'updated_at' => $case1ResolvedAt,
        ]);

        // Dokumen Resolusi Sah untuk Kasus 1
        $dummyResDocPath = 'resolutions/Berita_Acara_Mediasi_Damai_Fauzan.pdf';
        try {
            Storage::disk('s3-private')->put($dummyResDocPath, "DOKUMEN RESMI BERITA ACARA MEDIASI KASUS {$case1Number}\nTanggal: {$case1ResolvedAt}\nStatus: DAMAI & SELESAI");
        } catch (\Throwable $e) {
            Storage::disk('local')->put($dummyResDocPath, "DOKUMEN RESMI BERITA ACARA MEDIASI KASUS {$case1Number}");
        }

        DB::table('case_resolution_documents')->insert([
            'case_id' => $case1Id,
            'file_name' => 'Berita_Acara_Mediasi_Damai_Fauzan.pdf',
            'file_path' => $dummyResDocPath,
            'verification_status' => 'VALID',
            'uploaded_by' => 'Bu Ratna Sari',
            'created_at' => $case1ResolvedAt,
            'updated_at' => $case1ResolvedAt,
        ]);

        $this->info(" [OK] Kasus 1 Berhasil Dibuat: {$case1Number} (Status: CLOSED - Selesai di Sekolah)");

        // -------------------------------------------------------------
        // KASUS 2: ESKALASI KE DINAS PENDIDIKAN & UPTD PPA
        // -------------------------------------------------------------
        $this->newLine();
        $this->info('>>> [2/2] Mensimulasikan Kasus 2: Eskalasi ke Dinas Pendidikan & UPTD PPA...');

        $case2Number = 'SS-2026-000202';
        $case2OpenedAt = $now->copy()->subHours(18);

        $existingCase2 = DB::table('cases')->where('case_number', $case2Number)->first();
        if ($existingCase2) {
            DB::table('reports')->where('id', $existingCase2->report_id)->delete();
            DB::table('cases')->where('id', $existingCase2->id)->delete();
        }

        $report2Id = DB::table('reports')->insertGetId([
            'school_npsn' => $npsn,
            'report_number' => $case2Number,
            'reporter_role' => 'VICTIM',
            'identity_mode' => 'IDENTIFIED',
            'category' => 'Perundungan Fisik',
            'subcategory' => 'Memukul atau menendang',
            'description' => 'Terjadi pengeroyokan dan pemukulan fisik di dekat gerbang luar sekolah sepulang sekolah oleh sekelompok siswa dari sekolah lain yang dipimpin oleh seorang alumni. Korban mengalami lebam di wajah dan trauma psikologis berat, serta ada pesan ancaman teror susulan di media sosial.',
            'submitted_at' => $case2OpenedAt,
            'created_at' => $case2OpenedAt,
            'updated_at' => $now,
        ]);

        DB::table('reporter_identities')->insert([
            'report_id' => $report2Id,
            'full_name' => 'Rizky Pratama',
            'contact' => '08139876543',
            'created_at' => $case2OpenedAt,
            'updated_at' => $case2OpenedAt,
        ]);

        $case2Id = DB::table('cases')->insertGetId([
            'school_npsn' => $npsn,
            'report_id' => $report2Id,
            'case_number' => $case2Number,
            'category' => 'Perundungan Fisik',
            'risk_level' => 'HIGH',
            'status' => 'IN_HANDLING',
            'assigned_to' => 'Bu Ratna Sari',
            'opened_at' => $case2OpenedAt,
            'created_at' => $case2OpenedAt,
            'updated_at' => $now,
        ]);

        // SLA Kasus 2
        DB::table('case_slas')->insert([
            'case_id' => $case2Id,
            'status' => 'ON_TIME',
            'response_deadline' => $case2OpenedAt->copy()->addHours(8),
            'initial_response_at' => $case2OpenedAt->copy()->addHour(),
            'created_at' => $case2OpenedAt,
            'updated_at' => $now,
        ]);

        // Histori Status Kasus 2
        DB::table('case_status_histories')->insert([
            [
                'case_id' => $case2Id,
                'old_status' => 'PENDING_RESPONSE',
                'new_status' => 'UNDER_VERIFICATION',
                'reason' => 'Guru BK merespons darurat dan mendatangi korban di ruang UKS.',
                'changed_by' => 'Bu Ratna Sari',
                'created_at' => $case2OpenedAt->copy()->addHour(),
                'updated_at' => $case2OpenedAt->copy()->addHour(),
            ],
            [
                'case_id' => $case2Id,
                'old_status' => 'UNDER_VERIFICATION',
                'new_status' => 'IN_HANDLING',
                'reason' => 'Korban dirujuk ke Puskesmas untuk visum luka fisik dan pendampingan trauma.',
                'changed_by' => 'Bu Ratna Sari',
                'created_at' => $case2OpenedAt->copy()->addHours(3),
                'updated_at' => $case2OpenedAt->copy()->addHours(3),
            ],
        ]);

        // ESKALASI TINGKAT 1: Guru BK eskalasikan ke Kepala Sekolah
        $escalateTime1 = $case2OpenedAt->copy()->addHours(4);
        DB::table('case_escalations')->insert([
            'case_id' => $case2Id,
            'escalation_type' => 'MANUAL',
            'reason' => 'Insiden melibatkan pelaku dari luar sekolah (alumni dan siswa sekolah lain) dengan kekerasan fisik berulang. Memerlukan penanganan pimpinan dan perlindungan saksi/korban.',
            'escalated_by' => 'Bu Ratna Sari',
            'notified_to' => 'Kepala Sekolah',
            'status' => 'SENT',
            'created_at' => $escalateTime1,
            'updated_at' => $escalateTime1,
        ]);

        // ESKALASI TINGKAT 2: Kepala Sekolah eskalasikan ke Satgas PPKSP Kota (Disdik & UPTD PPA)
        $escalateTime2 = $case2OpenedAt->copy()->addHours(6);
        $satgasLabel = 'Satgas PPKSP Kota Bukittinggi (Disdik & UPTD PPA)';
        DB::table('case_escalations')->insert([
            'case_id' => $case2Id,
            'escalation_type' => 'MANUAL',
            'reason' => 'Pihak sekolah membutuhkan intervensi lintas sektor dari Dinas Pendidikan untuk memanggil kepala sekolah asal pelaku, serta pendampingan psikologis klinis dan hukum bagi korban oleh UPTD PPA.',
            'escalated_by' => 'Kepala Sekolah SMP Negeri 2 Bukittinggi',
            'notified_to' => $satgasLabel,
            'status' => 'SENT',
            'created_at' => $escalateTime2,
            'updated_at' => $escalateTime2,
        ]);

        // Notifikasi Terkirim ke Pihak Dinas & PPA
        DB::table('notifications')->insert([
            [
                'recipient_name' => 'Dinas Pendidikan Kota Bukittinggi',
                'type' => 'MANUAL_ESCALATION',
                'title' => 'Eskalasi Kasus Kekerasan Lintas Sekolah: '.$case2Number,
                'message' => "Kasus {$case2Number} membutuhkan koordinasi lintas dinas dan pembinaan pimpinan sekolah asal terduga pelaku.",
                'priority' => 'URGENT',
                'case_number' => $case2Number,
                'created_at' => $escalateTime2,
                'updated_at' => $escalateTime2,
            ],
            [
                'recipient_name' => 'UPTD PPA - DP3APPKB Kota Bukittinggi',
                'type' => 'MANUAL_ESCALATION',
                'title' => 'Permintaan Perlindungan & Pendampingan Trauma Anak: '.$case2Number,
                'message' => "Kasus {$case2Number} membutuhkan konselor psikologi klinis anak dan perlindungan hukum bagi korban Rizky Pratama.",
                'priority' => 'URGENT',
                'case_number' => $case2Number,
                'created_at' => $escalateTime2,
                'updated_at' => $escalateTime2,
            ],
        ]);

        // Catatan Tindakan Bersama Dinas Pendidikan & UPTD PPA
        DB::table('case_notes')->insert([
            [
                'case_id' => $case2Id,
                'note_type' => 'DISPOSITION',
                'content' => 'Disposisi Bidang Pembinaan SMP Disdik: Telah dijadwalkan pemanggilan kepala sekolah dari pihak terduga pelaku untuk rapat koordinasi sanksi disiplin dan pencegahan tawuran lanjutan pada hari kerja berikutnya.',
                'visibility' => 'CASE_RESTRICTED',
                'created_by' => 'Dinas Pendidikan Kota Bukittinggi',
                'created_at' => $escalateTime2->copy()->addHours(2),
                'updated_at' => $escalateTime2->copy()->addHours(2),
            ],
            [
                'case_id' => $case2Id,
                'note_type' => 'PROTECTION',
                'content' => 'Telah dilakukan asesmen awal kondisi psikologis ananda Rizky Pratama. UPTD PPA menerjunkan psikolog klinis pendamping serta memastikan keamanan korban dari ancaman digital medsos.',
                'visibility' => 'CASE_RESTRICTED',
                'created_by' => 'UPTD PPA Kota Bukittinggi',
                'created_at' => $escalateTime2->copy()->addHours(4),
                'updated_at' => $escalateTime2->copy()->addHours(4),
            ],
        ]);

        $this->info(" [OK] Kasus 2 Berhasil Dibuat: {$case2Number} (Status: IN_HANDLING - Dieskalasikan ke Disdik & PPA)");

        // -------------------------------------------------------------
        // RINGKASAN & PANDUAN AKSES BROWSER
        // -------------------------------------------------------------
        $this->newLine();
        $this->info('================================================================');
        $this->info('  SIMULASI BERHASIL DISIAPKAN DENGAN LENGKAP!');
        $this->info('================================================================');
        $this->line('Silakan buka dan navigasikan di browser Anda:');
        $this->newLine();

        $this->comment('KASUS 1 (Selesai di Tingkat Sekolah):');
        $this->line("- Nomor Kasus : {$case1Number}");
        $this->line('- Sekolah     : SMP Negeri 2 Bukittinggi');
        $this->line('- Status Akhir: CLOSED (Tuntas dengan Berita Acara Mediasi Damai)');
        $this->line('- URL Kasus   : http://sahabat-sekolah.test/cases/'.$case1Number);
        $this->line('- Login Guru  : Email: bk@sahabat.test | Password: GuruBK#SMPN2Bkt2026!');
        $this->newLine();

        $this->comment('KASUS 2 (Eskalasi ke Dinas Pendidikan & UPTD PPA):');
        $this->line("- Nomor Kasus : {$case2Number}");
        $this->line('- Sekolah     : SMP Negeri 2 Bukittinggi');
        $this->line('- Status Akhir: IN_HANDLING (Dalam Intervensi Satgas PPKSP Kota)');
        $this->line('- Riwayat Esk.: Guru BK -> Kepala Sekolah -> Satgas Disdik & UPTD PPA');
        $this->line('- URL Kasus   : http://sahabat-sekolah.test/cases/'.$case2Number);
        $this->line('- Login Disdik: Email: dinas@sahabat.test | Password: Dinas#Bkt2026!Secure');
        $this->line('- Login PPA   : Email: ppa@sahabat.test   | Password: Ppa#Bkt2026!Secure');
        $this->line('- Dashboard   : http://sahabat-sekolah.test/admin');
        $this->newLine();

        return self::SUCCESS;
    }
}
