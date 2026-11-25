<?php

require __DIR__.'/../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

$pdfDir = dirname(__DIR__).'/docs/panduan/pdf';
if (! is_dir($pdfDir)) {
    mkdir($pdfDir, 0777, true);
}

function createPdf(string $title, string $subtitle, string $targetRole, string $contentHtml, string $filename)
{
    global $pdfDir;

    $options = new Options;
    $options->set('isHtml5ParserEnabled', true);
    $options->set('isRemoteEnabled', true);
    $options->set('defaultFont', 'Helvetica');

    $dompdf = new Dompdf($options);

    $html = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{$title}</title>
    <style>
        @page {
            margin: 20mm 18mm 22mm 18mm;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 10.5pt;
            line-height: 1.6;
            color: #1e293b;
        }
        .header {
            border-bottom: 2px solid #0284c7;
            padding-bottom: 12px;
            margin-bottom: 24px;
        }
        .header-brand {
            font-size: 8.5pt;
            font-weight: bold;
            color: #0284c7;
            letter-spacing: 1.5px;
            text-transform: uppercase;
        }
        h1 {
            font-size: 18pt;
            color: #0f172a;
            margin: 6px 0 4px 0;
            line-height: 1.25;
        }
        .subtitle {
            font-size: 11pt;
            color: #475569;
            font-style: italic;
            margin: 0;
        }
        .meta-box {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 10px 14px;
            margin-bottom: 24px;
            font-size: 9pt;
        }
        .meta-box table {
            width: 100%;
            border-collapse: collapse;
        }
        .meta-box td {
            padding: 3px 0;
            vertical-align: top;
        }
        .meta-box td.label {
            width: 25%;
            color: #64748b;
            font-weight: 600;
        }
        .meta-box td.val {
            color: #0f172a;
        }
        h2 {
            font-size: 13pt;
            color: #0369a1;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 5px;
            margin-top: 22px;
            margin-bottom: 10px;
        }
        h3 {
            font-size: 11pt;
            color: #0f172a;
            margin-top: 14px;
            margin-bottom: 6px;
        }
        p {
            margin: 0 0 10px 0;
            text-align: justify;
        }
        ul, ol {
            margin: 0 0 12px 0;
            padding-left: 20px;
        }
        li {
            margin-bottom: 4px;
        }
        .step {
            margin-bottom: 10px;
        }
        .step-num {
            display: inline-block;
            background: #0284c7;
            color: #ffffff;
            font-weight: bold;
            font-size: 8pt;
            padding: 1px 6px;
            border-radius: 4px;
            margin-right: 6px;
        }
        .callout {
            background-color: #f0fdf4;
            border-left: 4px solid #16a34a;
            padding: 10px 14px;
            border-radius: 0 6px 6px 0;
            margin: 14px 0;
            font-size: 9.5pt;
        }
        .callout.danger {
            background-color: #fef2f2;
            border-left-color: #dc2626;
        }
        .callout.warning {
            background-color: #fffbeb;
            border-left-color: #d97706;
        }
        .callout.info {
            background-color: #f0f9ff;
            border-left-color: #0284c7;
        }
        .callout-title {
            font-weight: bold;
            margin-bottom: 3px;
        }
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin: 14px 0;
            font-size: 9pt;
        }
        table.data-table th {
            background-color: #f1f5f9;
            color: #334155;
            font-weight: 600;
            text-align: left;
            padding: 8px 10px;
            border: 1px solid #cbd5e1;
        }
        table.data-table td {
            padding: 8px 10px;
            border: 1px solid #cbd5e1;
            vertical-align: top;
        }
        .footer {
            position: fixed;
            bottom: -15mm;
            left: 0;
            right: 0;
            height: 12mm;
            border-top: 1px solid #e2e8f0;
            padding-top: 4px;
            font-size: 8pt;
            color: #94a3b8;
            display: table;
            width: 100%;
        }
        .footer-left { display: table-cell; text-align: left; }
        .footer-right { display: table-cell; text-align: right; }
    </style>
</head>
<body>
    <div class="footer">
        <div class="footer-left">Sahabat Sekolah — Sistem Manajemen Kasus Perundungan Kota Bukittinggi</div>
        <div class="footer-right">Dokumen Resmi Panduan Operasional</div>
    </div>

    <div class="header">
        <div class="header-brand">Pemerintah Kota Bukittinggi · Dinas Pendidikan & Kebudayaan</div>
        <h1>{$title}</h1>
        <p class="subtitle">{$subtitle}</p>
    </div>

    <div class="meta-box">
        <table>
            <tr>
                <td class="label">Peruntukan Pengguna</td>
                <td class="val"><strong>{$targetRole}</strong></td>
                <td class="label">Versi Dokumen</td>
                <td class="val">Edisi 2.0 (November 2026)</td>
            </tr>
            <tr>
                <td class="label">Dasar Regulasi</td>
                <td class="val">Permendikbudristek No. 46 Tahun 2023 (PPKSP) & UU Perlindungan Anak</td>
                <td class="label">Klasifikasi Akses</td>
                <td class="val">Kedinasan & Terbatas</td>
            </tr>
        </table>
    </div>

    {$contentHtml}
</body>
</html>
HTML;

    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    file_put_contents($pdfDir.'/'.$filename, $dompdf->output());
    echo "Saved: {$filename} (".filesize($pdfDir.'/'.$filename)." bytes)\n";
}

// -------------------------------------------------------------
// 1. PDF GURU BK (COUNSELOR)
// -------------------------------------------------------------
$bkHtml = <<<'HTML'
<h2>BAB I: GAMBARAN UMUM & FILOSOFI SISTEM</h2>
<p>Platform <strong>Sahabat Sekolah</strong> dikembangkan oleh Pemerintah Kota Bukittinggi sebagai <em>Case Management System (CMS)</em> terpadu untuk pencegahan dan penanganan kekerasan di lingkungan satuan pendidikan. Sistem ini bukan sekadar kotak pengaduan digital biasa, melainkan platform komprehensif yang mengawal siklus hidup penanganan kasus dari laporan masuk hingga tercapainya resolusi tuntas dan penutupan kasus resmi (closed).</p>

<div class="callout danger">
    <div class="callout-title">Prinsip Utama: Kerahasiaan Identitas Anak</div>
    Kerahasiaan identitas anak (baik korban maupun saksi) adalah kewajiban hukum tertinggi sesuai UU Perlindungan Anak dan Permendikbudristek No. 46 Tahun 2023. Guru BK dilarang keras membocorkan identitas pelapor anonim/rahasia ke publik atau teman sebaya.
</div>

<h2>BAB II: HAK AKSES & WEWENANG GURU BK</h2>
<table class="data-table">
    <thead>
        <tr>
            <th>Modul / Fitur</th>
            <th>Wewenang Guru BK</th>
            <th>Batasan Akses</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td><strong>Laporan Masuk (Inbox)</strong></td>
            <td>Menerima notifikasi instan laporan baru, memeriksa kronologi, dan mengunduh bukti.</td>
            <td>Hanya laporan pada NPSN sekolah sendiri.</td>
        </tr>
        <tr>
            <td><strong>Asesmen Risiko</strong></td>
            <td>Menetapkan tingkat risiko kasus: LOW, MEDIUM, HIGH, CRITICAL berdasarkan subkategori dan dampak.</td>
            <td>Mengikuti matriks indikator objektif sekolah.</td>
        </tr>
        <tr>
            <td><strong>Kanal Komunikasi Anonim</strong></td>
            <td>Berkomunikasi dua arah secara real-time dengan pelapor anonim tanpa mengetahui nomor HP/nama.</td>
            <td>Kerahasiaan kriptografis terjaga.</td>
        </tr>
        <tr>
            <td><strong>Catatan Kasus (Case Notes)</strong></td>
            <td>Menulis log investigasi, wawancara, bimbingan konseling, dan mediasi orang tua.</td>
            <td>Tersimpan aman dengan jejak audit.</td>
        </tr>
        <tr>
            <td><strong>Eskalasi Kasus</strong></td>
            <td>Mengangkat kasus ke Kepala Sekolah atau Satgas PPKSP (Dinas Pendidikan & UPTD PPA).</td>
            <td>Wajib menyertakan alasan urgensi objektif.</td>
        </tr>
        <tr>
            <td><strong>Penyelesaian & Tutup Kasus</strong></td>
            <td>Mengunggah Berita Acara Mediasi Damai (PDF/Foto) dan mengubah status menjadi CLOSED.</td>
            <td>Status CLOSED mensyaratkan dokumen sah.</td>
        </tr>
    </tbody>
</table>

<h2>BAB III: PANDUAN PENGOPERASIAN LANGKAH DEMI LANGKAH</h2>
<div class="step">
    <span class="step-num">1</span> <strong>Membaca & Merespons Laporan Masuk:</strong>
    Buka daftar laporan dengan status <em>PENDING_RESPONSE</em>. Pelajari deskripsi dan bukti. Klik tombol <strong>"Mulai Verifikasi"</strong> untuk mengubah status menjadi <em>UNDER_VERIFICATION</em>. Langkah ini akan menghentikan penghitung SLA respons awal.
</div>
<div class="step">
    <span class="step-num">2</span> <strong>Penilaian Risiko Otomatis & Dinamis:</strong>
    Sistem secara otomatis mendeteksi kategori (Fisik, Verbal, Siber, Sosial, Seksual, Pemalakan/Pengompasan) dan menetapkan tingkat risiko awal. Guru BK dapat menyesuaikan skor keparahan, keamanan korban, pengulangan, dan dampak (0-100).
</div>
<div class="step">
    <span class="step-num">3</span> <strong>Melakukan Komunikasi Dua Arah dengan Pelapor Anonim:</strong>
    Jika pelapor menggunakan mode anonim, gunakan panel obrolan di bagian bawah detail kasus untuk menanyakan klarifikasi waktu dan tempat kejadian tanpa membuka identitas murid.
</div>
<div class="step">
    <span class="step-num">4</span> <strong>Eskalasi Kasus (Bila Diperlukan):</strong>
    Jika kasus melibatkan pemerasan rutin geng luar, kekerasan fisik parah, atau kebuntuan mediasi, klik <strong>"Eskalasi Kasus"</strong> ke Kepala Sekolah atau Satgas PPKSP Kota (Disdik & PPA).
</div>
<div class="step">
    <span class="step-num">5</span> <strong>Resolusi & Penutupan Kasus (CLOSED):</strong>
    Catat kesepakatan damai pada catatan kasus. Unggah file scan <em>Berita Acara Mediasi Damai</em> atau surat pernyataan perlindungan anak bermaterai, lalu ubah status kasus menjadi <strong>CLOSED</strong>.
</div>
HTML;
createPdf('Panduan Penggunaan Guru BK', 'Modul Bimbingan Konseling & Manajemen Kasus Sekolah', 'Guru BK / Tim TPPK Sekolah', $bkHtml, 'panduan_guru_bk.pdf');

// -------------------------------------------------------------
// 2. PDF KEPALA SEKOLAH (PRINCIPAL)
// -------------------------------------------------------------
$ksHtml = <<<'HTML'
<h2>BAB I: FUNGSI KEPALA SEKOLAH DALAM SISTEM</h2>
<p>Sebagai penanggung jawab tertinggi di satuan pendidikan, Kepala Sekolah memegang kendali atas iklim keamanan dan kenyamanan belajar peserta didik. Modul Kepala Sekolah pada platform Sahabat Sekolah dirancang sebagai <em>Executive Dashboard</em> yang memberikan visibilitas penuh terhadap tren perundungan, kepatuhan waktu kerja Guru BK, serta penanganan kasus-kasus yang memerlukan diskresi atau intervensi pimpinan.</p>

<div class="callout info">
    <div class="callout-title">Fokus Pimpinan Sekolah</div>
    Pastikan tidak ada laporan yang dibiarkan melewati batas respons (Overdue). Lakukan evaluasi berkala terhadap lokasi-lokasi rawan (hotspots) perundungan di sekolah berdasarkan data analitik laporan.
</div>

<h2>BAB II: ANATOMI DASHBOARD KEPALA SEKOLAH (/principal)</h2>
<table class="data-table">
    <thead>
        <tr>
            <th>Komponen Panel</th>
            <th>Fungsi & Informasi yang Disajikan</th>
            <th>Tindakan Rekomendasi Pimpinan</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td><strong>Kartu Metrik Eksekutif</strong></td>
            <td>Total laporan aktif, kasus butuh tindakan, kasus risiko tinggi/kritis, dan angka resolusi.</td>
            <td>Mengevaluasi tren keamanan lingkungan belajar setiap awal pekan.</td>
        </tr>
        <tr>
            <td><strong>Panel Kasus Eskalasi</strong></td>
            <td>Daftar kasus yang diteruskan secara khusus oleh Guru BK kepada Kepala Sekolah.</td>
            <td>Wajib segera dibuka untuk membaca kronologi dan memberikan instruksi tertulis.</td>
        </tr>
        <tr>
            <td><strong>Indikator Kepatuhan SLA</strong></td>
            <td>Statistik persentase kasus On-Time vs Peringatan (Warning) vs Terlambat (Overdue).</td>
            <td>Bila angka peringatan tinggi, berikan arahan atau koordinasi tambahan bagi tim BK.</td>
        </tr>
        <tr>
            <td><strong>Cetak Rekapitulasi Berkas</strong></td>
            <td>Fitur unduh cetak lembar profil kasus dengan watermark dinas resmi.</td>
            <td>Digunakan untuk bahan laporan tertutup rapat dinas atau komite sekolah.</td>
        </tr>
    </tbody>
</table>

<h2>BAB III: LANGKAH PENANGANAN KASUS ESKALASI</h2>
<div class="step">
    <span class="step-num">1</span> <strong>Menerima Notifikasi Eskalasi:</strong>
    Kepala Sekolah menerima notifikasi instan ketika Guru BK memerlukan arahan pimpinan (misal: orang tua pelaku menolak hadir atau insiden melibatkan pendidik).
</div>
<div class="step">
    <span class="step-num">2</span> <strong>Tinjau Kronologi & Dokumen:</strong>
    Buka nomor kasus pada panel eskalasi untuk meninjau rekaman konseling awal dan bukti fisik.
</div>
<div class="step">
    <span class="step-num">3</span> <strong>Mediasi Pimpinan & Disposisi Tertulis:</strong>
    Panggil pihak terkait untuk mediasi tingkat pimpinan dan berikan catatan disposisi resmi di sistem.
</div>
<div class="step">
    <span class="step-num">4</span> <strong>Eskalasi ke Satgas PPKSP Kota:</strong>
    Jika insiden melibatkan siswa lintas sekolah atau membutuhkan pendampingan visum/hukum korban, teruskan kasus ke meja Dinas Pendidikan dan UPTD PPA Kota Bukittinggi.
</div>
HTML;
createPdf('Panduan Penggunaan Kepala Sekolah', 'Modul Pengawasan Pimpinan & Penanganan Eskalasi', 'Kepala Sekolah / Ketua TPPK', $ksHtml, 'panduan_kepala_sekolah.pdf');

// -------------------------------------------------------------
// 3. PDF INSTANSI KOTA / SATGAS PPKSP (CITY_ADMIN)
// -------------------------------------------------------------
$cityHtml = <<<'HTML'
<h2>BAB I: RUANG LINGKUP INSTANSI TINGKAT KOTA</h2>
<p>Berdasarkan regulasi nasional pencegahan kekerasan, penanganan kasus di lingkungan sekolah seringkali membutuhkan intervensi lintas sektoral di luar kewenangan tunggal sekolah. Peran <strong>CITY_ADMIN</strong> dirancang untuk menyatukan dua instansi pilar Pemerintah Kota Bukittinggi:</p>

<ul>
    <li><strong>Dinas Pendidikan & Kebudayaan Kota Bukittinggi (dinas@sahabat.test):</strong> Berfokus pada pengawasan manajerial sekolah, kepatuhan kode etik guru/staf, pemanggilan antar-kepala sekolah, mediasi sanksi administratif, dan perlindungan hak kelanjutan belajar murid.</li>
    <li><strong>UPTD PPA - DP3APPKB Kota Bukittinggi (ppa@sahabat.test):</strong> Unit Pelaksana Teknis Daerah Perlindungan Perempuan dan Anak berfokus pada pendampingan psikologis klinis, rujukan medis dan visum et repertum, bantuan hukum anak berhadapan dengan hukum (ABH), serta fasilitas rumah aman (safe house).</li>
    <li><strong>Satgas Gabungan PPKSP Kota Bukittinggi:</strong> Forum tanggap darurat koordinatif untuk menangani insiden tawuran, pengeroyokan geng, kekerasan seksual berat, atau pemerasan teror berkelompok.</li>
</ul>

<h2>BAB II: HAK AKSES LINTAS SEKOLAH (CITY-WIDE SCOPE)</h2>
<table class="data-table">
    <thead>
        <tr>
            <th>Modul Aplikasi</th>
            <th>Hak Akses Instansi Kota</th>
            <th>Manfaat Operasional</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td><strong>Dashboard Monitoring Kota</strong></td>
            <td>Melihat grafik tren perundungan per bulan, rasio kepatuhan SLA sekolah, dan sebaran kategori se-Bukittinggi.</td>
            <td>Pemetaan zona kerawanan wilayah dan evaluasi efektivitas program sekolah ramah anak.</td>
        </tr>
        <tr>
            <td><strong>Penanganan Eskalasi Jenjang 3</strong></td>
            <td>Menerima notifikasi otomatis untuk kasus yang diteruskan oleh sekolah ke meja dinas/UPTD PPA.</td>
            <td>Intervensi proaktif sebelum insiden membesar atau berdampak hukum pidana.</td>
        </tr>
        <tr>
            <td><strong>Detail Kasus Terbuka</strong></td>
            <td>Membuka kronologi, asesmen risiko, rekaman mediasi, dan bukti lampiran dari sekolah mana pun.</td>
            <td>Pemeriksaan data faktual secara transparan dan akurat tanpa sekat NPSN.</td>
        </tr>
    </tbody>
</table>

<h2>BAB III: ALUR PENANGANAN ESKALASI TINGKAT KOTA</h2>
<div class="step">
    <span class="step-num">1</span> <strong>Penerimaan Kasus Masuk dari Sekolah:</strong>
    Sistem menyaring otomatis kasus yang dieskalasikan oleh Kepala Sekolah ke akun Dinas Pendidikan atau UPTD PPA.
</div>
<div class="step">
    <span class="step-num">2</span> <strong>Penelaahan Bukti Medis & Digital:</strong>
    Petugas meninjau foto visum lebam korban, rekaman video CCTV kejadian, atau jejak digital teror media sosial.
</div>
<div class="step">
    <span class="step-num">3</span> <strong>Penerjunan Tim Dampingan & Disposisi:</strong>
    UPTD PPA menerjunkan psikolog klinis pendamping trauma anak, sementara Dinas Pendidikan menjadwalkan pemanggilan kepala sekolah pihak terduga pelaku untuk rapat koordinasi sanksi disipliner.
</div>
HTML;
createPdf('Panduan Penggunaan Instansi Kota & Satgas PPKSP', 'Modul Monitoring Wilayah & Intervensi Jenjang 3', 'Dinas Pendidikan & UPTD PPA Kota Bukittinggi', $cityHtml, 'panduan_instansi_kota.pdf');

// -------------------------------------------------------------
// 4. PDF ADMINISTRATOR SISTEM (ADMIN)
// -------------------------------------------------------------
$admHtml = <<<'HTML'
<h2>BAB I: RUANG LINGKUP & TANGGUNG JAWAB ADMINISTRATOR</h2>
<p>Administrator Sistem memegang hak akses tertinggi (Superuser) dalam ekosistem Sahabat Sekolah. Tanggung jawab Administrator berfokus pada kelangsungan layanan infrastruktur, manajemen akun pengguna se-kota, konfigurasi matriks SLA, pemutakhiran kategori perundungan, dan pemantauan jejak audit kepatuhan privasi data.</p>

<h2>BAB II: PANDUAN PENGELOLAAN PENGGUNA (/admin/users)</h2>
<div class="step">
    <span class="step-num">1</span> <strong>Pendaftaran Akun Baru:</strong>
    Buka menu <em>Pengguna</em> lalu klik <em>"+ Tambah Pengguna"</em>. Masukkan nama lengkap dan email dinas resmi.
</div>
<div class="step">
    <span class="step-num">2</span> <strong>Penugasan Peran & Sekolah:</strong>
    Untuk peran Guru BK (COUNSELOR) atau Kepala Sekolah (PRINCIPAL), wajib menautkan sekolah asal dari direktori NPSN AConnect Bukittinggi. Untuk Instansi Kota (CITY_ADMIN), pilih instansi Dinas Pendidikan atau UPTD PPA.
</div>
<div class="step">
    <span class="step-num">3</span> <strong>Reset Kata Sandi Terenkripsi:</strong>
    Gunakan tombol kunci pada baris pengguna untuk menginisiasi pengiriman tautan token reset yang berlaku selama 60 menit ke email pengguna.
</div>

<h2>BAB III: PENGATURAN SLA & MASTER DATA</h2>
<table class="data-table">
    <thead>
        <tr>
            <th>Tingkat Risiko</th>
            <th>Target Respons Awal</th>
            <th>Target Resolusi Kasus</th>
            <th>Ketentuan Operasional</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td><strong>LOW</strong></td>
            <td>24 Jam Kerja</td>
            <td>7 Hari Kerja</td>
            <td>Dihitung pada jam kerja kedinasan (Senin - Jumat 08:00 - 16:00 WIB).</td>
        </tr>
        <tr>
            <td><strong>MEDIUM</strong></td>
            <td>24 Jam Kerja</td>
            <td>7 Hari Kerja</td>
            <td>Dihitung pada jam kerja kedinasan (Senin - Jumat 08:00 - 16:00 WIB).</td>
        </tr>
        <tr>
            <td><strong>HIGH</strong></td>
            <td>4 Jam Kerja</td>
            <td>3 Hari Kerja</td>
            <td>Dipercepat untuk mencegah ancaman kekerasan berkelanjutan.</td>
        </tr>
        <tr>
            <td><strong>CRITICAL</strong></td>
            <td>1 Jam (Instant)</td>
            <td>24 Jam</td>
            <td>Berlaku darurat 24 jam non-stop (termasuk hari libur dan malam hari).</td>
        </tr>
    </tbody>
</table>

<h2>BAB IV: PEMELIHARAAN SISTEM</h2>
<ul>
    <li><strong>Background Queue:</strong> Pastikan service <code>php artisan queue:work</code> aktif untuk memproses pengiriman email notifikasi.</li>
    <li><strong>SLA Scanner:</strong> Scheduler <code>php artisan sahabat:sla-scan</code> dijadwalkan berjalan otomatis setiap jam.</li>
    <li><strong>Audit Log:</strong> Seluruh aktivitas sensitif (unduh dokumen, ubah status, akses laporan anonim) terekam di tabel <code>audit_logs</code>.</li>
</ul>
HTML;
createPdf('Panduan Penggunaan Administrator Sistem', 'Modul Administrasi Pengguna, SLA, & Master Data', 'Administrator Sistem (Superuser)', $admHtml, 'panduan_admin_sistem.pdf');

// -------------------------------------------------------------
// 5. PDF PELAPOR & SISWA (PUBLIC REPORTER)
// -------------------------------------------------------------
$usrHtml = <<<'HTML'
<h2>BAB I: RUANG AMAN UNTUK BERSUARA</h2>
<p>Halo Teman-Teman dan Sahabat Sekolah! Apakah kamu pernah melihat temanmu diejek terus-menerus, dipukul, dipalak uang jajannya, dikucilkan, atau diancam di media sosial? Atau mungkin kamu sendiri yang mengalaminya dan merasa bingung harus cerita ke siapa?</p>
<p><strong>Sahabat Sekolah</strong> dibuat khusus oleh Pemerintah Kota Bukittinggi agar setiap anak di Bukittinggi bisa belajar dengan aman, tenang, dan gembira. Di sini, kamu bisa bersuara tanpa rasa takut karena ceritamu didengar, dilindungi, dan ditangani oleh Guru BK penuh kepedulian.</p>

<div class="callout info">
    <div class="callout-title">Tiga Pilihan Identitas yang Aman:</div>
    <ul>
        <li><strong>Lapor Terbuka (Identified):</strong> Nama dan kelasmu tercatat jelas agar Guru BK bisa langsung mengajakmu berdiskusi bersama.</li>
        <li><strong>Lapor Rahasia (Confidential):</strong> Nama dan kelasmu HANYA diketahui oleh Guru BK yang menangani. Teman lain atau pelaku tidak akan pernah tahu.</li>
        <li><strong>Lapor Anonim (Anonymous):</strong> Kamu sama sekali TIDAK PERLU menuliskan nama, email, nomor HP, atau membuat akun! Kamu akan mendapatkan 'Kunci Rahasia 4 Kata'.</li>
    </ul>
</div>

<h2>BAB II: CARA MUDAH MEMBUAT LAPORAN</h2>
<div class="step">
    <span class="step-num">1</span> <strong>Buka Form Lapor:</strong> Buka website Sahabat Sekolah di HP atau komputermu, atau pindai (scan) QR Code di mading sekolahmu.
</div>
<div class="step">
    <span class="step-num">2</span> <strong>Pilih Sekolah & Kategori:</strong> Pilih nama sekolahmu, lalu pilih jenis kejadian (Fisik, Verbal, Pemalakan/Pengompasan Uang, Siber, atau Pengucilan Sosial).
</div>
<div class="step">
    <span class="step-num">3</span> <strong>Ceritakan Kejadiannya:</strong> Ketikkan apa yang terjadi, kapan, di mana, dan siapa yang terlibat. Kamu juga bisa menggunakan tombol <strong>"Bicara / Dikte Suara"</strong> untuk berbicara langsung ke mikrofon HP-mu.
</div>
<div class="step">
    <span class="step-num">4</span> <strong>Unggah Bukti (Opsional):</strong> Lampirkan foto luka, foto tempat kejadian, atau screenshot pesan ancaman hingga 5 file (maksimal 10 MB per file). Lokasi foto kamu otomatis dirahasiakan oleh sistem.
</div>
<div class="step">
    <span class="step-num">5</span> <strong>Simpan Kunci Rahasia 4 Kata:</strong> Setelah laporan terkirim, catat atau screenshot 4 kata rahasiamu (contoh: <em>kancil-cerdas-lembah-merapi</em>). Kunci ini digunakan untuk melacak laporan dan mengobrol rahasia dengan Guru BK.
</div>

<h2>BAB III: NOMOR BANTUAN DARURAT KOTA BUKITTINGGI</h2>
<table class="data-table">
    <thead>
        <tr>
            <th>Layanan Bantuan</th>
            <th>Nomor Kontak</th>
            <th>Keterangan</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td><strong>Panggilan Darurat Bukittinggi</strong></td>
            <td>112 (Bebas Pulsa)</td>
            <td>Layanan tanggap darurat 24 jam Pemerintah Kota Bukittinggi.</td>
        </tr>
        <tr>
            <td><strong>Layanan SAPA PPPA</strong></td>
            <td>129 / WhatsApp 08111-129-129</td>
            <td>Layanan Sahabat Perempuan dan Anak Kementerian PPPA & UPTD PPA.</td>
        </tr>
        <tr>
            <td><strong>UPTD PPA Kota Bukittinggi</strong></td>
            <td>Kantor Dinas DP3APPKB</td>
            <td>Layanan pendampingan hukum, psikologis, dan safe house ramah anak.</td>
        </tr>
    </tbody>
</table>
HTML;
createPdf('Panduan Penggunaan Pelapor & Siswa', 'Kanal Pelaporan Ramah Anak & Perlindungan Saksi/Korban', 'Peserta Didik, Saksi, & Orang Tua Murid', $usrHtml, 'panduan_pelapor_dan_siswa.pdf');

echo "ALL 5 PDF MANUALS GENERATED SUCCESSFULLY!\n";
