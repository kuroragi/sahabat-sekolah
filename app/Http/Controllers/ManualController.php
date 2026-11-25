<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ManualController extends Controller
{
    /**
     * Daftar manual yang tersedia berdasarkan peran (Role-based scope).
     *
     * @return array<string, array<string, mixed>>
     */
    private function getAvailableManuals(): array
    {
        $role = session('user_role');

        $allManuals = [
            'guru-bk' => [
                'title' => 'Buku Panduan Guru Bimbingan dan Konseling (BK)',
                'subtitle' => 'Modul Penanganan Kasus, Komunikasi Anonim, Asesmen Risiko, dan Resolusi Sekolah',
                'target' => 'Guru BK / Konselor / TPPK Sekolah',
                'filename' => 'panduan_guru_bk.pdf',
                'docx' => 'PANDUAN_PENGGUNAAN_GURU_BK.docx',
                'roles' => ['COUNSELOR', 'ADMIN'],
                'icon' => 'book-heart',
                'badge' => 'Konseling & Kasus',
            ],
            'kepala-sekolah' => [
                'title' => 'Buku Panduan Kepala Sekolah (Principal)',
                'subtitle' => 'Modul Monitoring Pimpinan, Penanganan Kasus Eskalasi, dan Rekapitulasi Berkas',
                'target' => 'Kepala Sekolah / Ketua TPPK',
                'filename' => 'panduan_kepala_sekolah.pdf',
                'docx' => 'PANDUAN_PENGGUNAAN_KEPALA_SEKOLAH.docx',
                'roles' => ['PRINCIPAL', 'ADMIN'],
                'icon' => 'shield-check',
                'badge' => 'Pengawasan Pimpinan',
            ],
            'instansi-kota' => [
                'title' => 'Buku Panduan Instansi Kota & Satgas PPKSP',
                'subtitle' => 'Modul Monitoring Wilayah Se-Kota Bukittinggi & Intervensi Kasus Jenjang 3 (Disdik & PPA)',
                'target' => 'Dinas Pendidikan & UPTD PPA Kota Bukittinggi',
                'filename' => 'panduan_instansi_kota.pdf',
                'docx' => 'PANDUAN_PENGGUNAAN_INSTANSI_KOTA_SATGAS_PPKSP.docx',
                'roles' => ['CITY_ADMIN', 'ADMIN'],
                'icon' => 'building-2',
                'badge' => 'Tingkat Kota',
            ],
            'admin-sistem' => [
                'title' => 'Buku Panduan Administrator Sistem',
                'subtitle' => 'Modul Manajemen Pengguna, Konfigurasi SLA, Master Kategori, dan Audit Keamanan',
                'target' => 'Administrator TI Dinas Pendidikan',
                'filename' => 'panduan_admin_sistem.pdf',
                'docx' => 'PANDUAN_PENGGUNAAN_ADMINISTRATOR_SISTEM.docx',
                'roles' => ['ADMIN'],
                'icon' => 'settings',
                'badge' => 'Superuser',
            ],
            'pelapor-siswa' => [
                'title' => 'Buku Panduan Peserta Didik & Pelapor Publik',
                'subtitle' => 'Panduan Ramah Anak: 3 Mode Identitas, Dikte Suara, dan Chat Rahasia dengan Guru BK',
                'target' => 'Siswa, Saksi, dan Orang Tua Murid',
                'filename' => 'panduan_pelapor_dan_siswa.pdf',
                'docx' => 'PANDUAN_PENGGUNAAN_PELAPOR_DAN_SISWA.docx',
                'roles' => ['COUNSELOR', 'PRINCIPAL', 'CITY_ADMIN', 'ADMIN'],
                'icon' => 'heart-handshake',
                'badge' => 'Kanal Publik',
            ],
        ];

        // Saring berdasarkan hak akses peran pengguna
        return array_filter($allManuals, function ($manual) use ($role) {
            return in_array($role, $manual['roles'], true);
        });
    }

    /**
     * Tampilan utama halaman panduan penggunaan.
     */
    public function index(Request $request)
    {
        $manuals = $this->getAvailableManuals();

        // Manual aktif yang dipilih untuk ditampilkan di preview PDF
        $selectedKey = $request->query('doc');
        if (!$selectedKey || !isset($manuals[$selectedKey])) {
            $selectedKey = array_key_first($manuals);
        }

        $selectedManual = $manuals[$selectedKey] ?? null;

        return view('manuals.index', [
            'activeNav' => 'manuals',
            'manuals' => $manuals,
            'selectedKey' => $selectedKey,
            'selectedManual' => $selectedManual,
        ]);
    }

    /**
     * Menampilkan inline stream file PDF atau mengunduh dokumen panduan.
     */
    public function streamPdf(string $key): BinaryFileResponse
    {
        $manuals = $this->getAvailableManuals();

        if (!isset($manuals[$key])) {
            abort(403, 'Anda tidak memiliki hak akses untuk membaca buku panduan ini.');
        }

        $manual = $manuals[$key];
        $filePath = base_path('docs/panduan/pdf/' . $manual['filename']);

        if (!file_exists($filePath)) {
            abort(404, 'Berkas panduan PDF belum tersedia.');
        }

        return response()->file($filePath, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $manual['filename'] . '"',
        ]);
    }

    /**
     * Mengunduh berkas Word (.docx) resmi buku panduan.
     */
    public function downloadDocx(string $key): BinaryFileResponse
    {
        $manuals = $this->getAvailableManuals();

        if (!isset($manuals[$key])) {
            abort(403, 'Anda tidak memiliki hak akses untuk mengunduh dokumen ini.');
        }

        $manual = $manuals[$key];
        $filePath = base_path('docs/panduan/' . $manual['docx']);

        if (!file_exists($filePath)) {
            abort(404, 'Berkas dokumen Word belum tersedia.');
        }

        return response()->download($filePath, $manual['docx']);
    }
}
