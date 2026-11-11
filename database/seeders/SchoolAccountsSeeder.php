<?php

namespace Database\Seeders;

use App\Helpers\AConnect;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class SchoolAccountsSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command?->info('Menghubungi API data sekolah Bukittinggi...');

        $aConnect = new AConnect;
        $schools = $aConnect->getSekolahList();

        if ($schools->isEmpty()) {
            $this->command?->warn('Data sekolah dari API kosong atau tidak dapat diakses.');

            return;
        }

        $now = Carbon::now();
        $bkPassword = Hash::make('GuruBK#2026!');
        $kepsekPassword = Hash::make('Kepsek#2026!');

        $totalSchools = $schools->count();
        $this->command?->info("Ditemukan {$totalSchools} data sekolah dari API. Memproses pembuatan akun BK & Kepsek...");

        $createdOrUpdated = 0;

        foreach ($schools as $school) {
            $npsn = trim((string) ($school->npsn ?? ''));
            $schoolName = trim((string) ($school->nama_sekolah ?? ''));

            if ($npsn === '' || $schoolName === '') {
                continue;
            }

            // 1. Akun Guru BK (COUNSELOR)
            $bkEmail = strtolower("bk.{$npsn}@sahabat.sekolah");
            $bkName = "BK {$schoolName}";

            DB::table('users')->updateOrInsert(
                ['email' => $bkEmail],
                [
                    'name' => $bkName,
                    'password' => $bkPassword,
                    'role' => 'COUNSELOR',
                    'status' => 'ACTIVE',
                    'school_npsn' => $npsn,
                    'school_name' => $schoolName,
                    'email_verified_at' => $now,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );

            // 2. Akun Kepala Sekolah (PRINCIPAL)
            $kepsekEmail = strtolower("kepsek.{$npsn}@sahabat.sekolah");
            $kepsekName = "Kepsek {$schoolName}";

            DB::table('users')->updateOrInsert(
                ['email' => $kepsekEmail],
                [
                    'name' => $kepsekName,
                    'password' => $kepsekPassword,
                    'role' => 'PRINCIPAL',
                    'status' => 'ACTIVE',
                    'school_npsn' => $npsn,
                    'school_name' => $schoolName,
                    'email_verified_at' => $now,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );

            $createdOrUpdated += 2;
        }

        $this->command?->info("Selesai! Berhasil membuat / memperbarui {$createdOrUpdated} akun (BK & Kepala Sekolah) untuk {$totalSchools} sekolah.");
        $this->command?->line('Format Login:');
        $this->command?->line('- BK: bk.[npsn]@sahabat.sekolah | Password: GuruBK#2026!');
        $this->command?->line('- Kepsek: kepsek.[npsn]@sahabat.sekolah | Password: Kepsek#2026!');
    }
}
