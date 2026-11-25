<?php

require_once __DIR__.'/DocxBuilder.php';

$outputDir = dirname(__DIR__).'/docs/panduan';
if (! is_dir($outputDir)) {
    mkdir($outputDir, 0777, true);
}

echo "Generating Updated Comprehensive User Manuals (.docx) for Sahabat Sekolah...\n";

// =========================================================================
// 1. MANUAL GURU BK (COUNSELOR)
// =========================================================================
echo "Building: PANDUAN_PENGGUNAAN_GURU_BK.docx...\n";
$bk = new DocxBuilder(
    'PANDUAN OPERASIONAL & PENGGUNAAN APLIKASI',
    'MODUL GURU BIMBINGAN DAN KONSELING (COUNSELOR)',
    'Guru BK'
);

$bk->addTitle('SAHABAT SEKOLAH — KOTA BUKITTINGGI')
    ->addSubtitle('Panduan Operasional & Tata Cara Manajemen Kasus bagi Guru Bimbingan dan Konseling (BK)')
    ->addMetaTable([
        'Nama Dokumen' => 'Buku Panduan Pengguna — Peran Guru Bimbingan dan Konseling (COUNSELOR)',
        'Platform' => 'SAHABAT SEKOLAH (Case Management System Penanganan Kekerasan)',
        'Wilayah Implementasi' => 'Satuan Pendidikan (SD, SMP, MTs, MIN) Kota Bukittinggi',
        'Dasar Hukum' => 'Permendikbudristek No. 46 Tahun 2023 (PPKSP) & Regulasi Pemkot Bukittinggi',
        'Peruntukan Role' => 'Guru Bimbingan dan Konseling (BK) / Tim Pencegahan dan Penanganan Kekerasan (TPPK)',
        'Versi Panduan' => '2.0 (November 2026 - Edisi Komprehensif)',
        'Klasifikasi Keamanan' => 'Dokumen Kedinasan & Penanganan Kasus (Terbatas & Rahasia)',
    ]);

$bk->addHeading1('BAB I: GAMBARAN UMUM & FILOSOFI SISTEM');
$bk->addParagraph('Platform Sahabat Sekolah dikembangkan oleh Pemerintah Kota Bukittinggi sebagai Case Management System (CMS) terpadu untuk pencegahan dan penanganan kekerasan di lingkungan satuan pendidikan. Sistem ini bukan sekadar kotak pengaduan digital biasa, melainkan platform komprehensif yang mengawal siklus hidup penanganan kasus dari laporan masuk hingga tercapainya resolusi tuntas dan penutupan kasus resmi (closed).');

$bk->addHeading2('1.1. Tiga Prinsip Utama Operasional');
$bk->addBullet('Zero Fear Reporting (Pelaporan Bebas Rasa Takut):', 'Peserta didik dapat memilih 3 mode identitas: Terbuka (Identified), Rahasia (Confidential - hanya Guru BK yang mengetahui), dan Anonim Kriptografis (Anonymous - menggunakan token dan kunci 4 kata tanpa data pribadi).');
$bk->addBullet('Akuntabilitas Waktu Respons (Strict SLA):', 'Sistem memberlakukan batas waktu respons awal terukur: 1 jam untuk Kritis (CRITICAL), 4 jam untuk Tinggi (HIGH), dan 24 jam kerja untuk Sedang (MEDIUM) dan Rendah (LOW) yang dipantau oleh SLA scanner otomatis.');
$bk->addBullet('Pemberkasan Sah & Bukti Terenkripsi:', 'Penyelesaian kasus wajib disertai bukti konkret (Berita Acara Mediasi, Surat Komitmen, atau Rujukan Medis/Psikologis) dengan dukungan berkas hingga 10 MB per file dan perlindungan privasi GPS EXIF.');

$bk->addHeading2('1.2. Keamanan & Isolasi Data Multi-Tenant Berbasis NPSN');
$bk->addParagraph('Setiap sekolah diisolasi secara ketat berdasarkan Nomor Pokok Sekolah Nasional (NPSN). Guru BK hanya memiliki hak akses melihat, memverifikasi, dan mengelola kasus yang terdaftar pada sekolah tempatnya bertugas. Data antar-sekolah terpisah secara absolut demi menjamin kerahasiaan murid.');

$bk->addCallout('Kerahasiaan Identitas Anak', 'Kerahasiaan identitas anak (baik korban maupun saksi) adalah kewajiban hukum tertinggi sesuai UU Perlindungan Anak dan Permendikbudristek No. 46 Tahun 2023. Guru BK dilarang keras membocorkan identitas pelapor anonim/rahasia ke publik atau teman sebaya.', 'danger');

$bk->addHeading1('BAB II: HAK AKSES & WEWENANG GURU BK');
$bk->addTable(
    ['Modul / Fitur', 'Wewenang Guru BK', 'Batasan Akses'],
    [
        ['Laporan Masuk (Inbox)', 'Menerima notifikasi instan laporan baru, memeriksa kronologi, dan mengunduh bukti', 'Hanya laporan pada NPSN sekolah sendiri'],
        ['Asesmen Risiko', 'Menetapkan tingkat risiko kasus: LOW, MEDIUM, HIGH, CRITICAL berdasarkan subkategori dan dampak', 'Mengikuti matriks indikator objektif sekolah'],
        ['Kanal Komunikasi Anonim', 'Berkomunikasi dua arah secara real-time dengan pelapor anonim tanpa mengetahui nomor HP/nama', 'Kerahasiaan kriptografis terjaga'],
        ['Catatan Kasus (Case Notes)', 'Menulis log investigasi, wawancara, bimbingan konseling, dan mediasi orang tua', 'Tersimpan aman dengan jejak audit'],
        ['Eskalasi Kasus', 'Mengangkat kasus ke Kepala Sekolah atau Satgas PPKSP (Dinas Pendidikan & UPTD PPA)', 'Wajib menyertakan alasan urgensi objektif'],
        ['Penyelesaian & Tutup Kasus', 'Mengunggah Berita Acara Mediasi Damai (PDF/Foto) dan mengubah status menjadi CLOSED', 'Status CLOSED mensyaratkan dokumen sah'],
        ['Portofolio Prestasi Murid', 'Mencatat prestasi siswa dan melakukan sinkronisasi dengan database AConnect', 'Siswa aktif pada NPSN sekolah sendiri'],
    ],
    [2400, 4600, 2600]
);

$bk->addHeading1('BAB III: PANDUAN PENGOPERASIAN LANGKAH DEMI LANGKAH');
$bk->addHeading2('3.1. Masuk ke Akun Guru BK');
$bk->addStep(1, 'Buka Portal Sahabat Sekolah', "Akses website Sahabat Sekolah melalui peramban web (browser), lalu klik tombol 'Masuk Petugas' di pojok kanan atas.");
$bk->addStep(2, 'Masukkan Kredensial Resmi', 'Ketikkan alamat email kedinasan Guru BK (contoh: bk@sahabat.test) dan kata sandi akun Anda.');
$bk->addStep(3, 'Tampilan Dashboard Utama', 'Setelah login berhasil, Anda langsung disajikan antarmuka Dashboard Manajemen Kasus SMP/SD Anda.');

$bk->addHeading2('3.2. Membaca & Merespons Laporan Masuk');
$bk->addStep(1, 'Pilih Laporan Baru', 'Pada tabel laporan, pilih kasus berstatus PENDING_RESPONSE (Menunggu Respons).');
$bk->addStep(2, 'Verifikasi Kronologi & Lampiran Bukti', 'Baca deskripsi insiden. Periksa lampiran foto luka lebam, rekaman suara, tangkapan layar chat, atau dokumen pendukung (mendukung hingga 10 MB per file).');
$bk->addStep(3, 'Mulai Verifikasi Kasus', "Klik tombol 'Mulai Verifikasi' untuk mengubah status menjadi UNDER_VERIFICATION. Langkah ini akan menghentikan penghitung batas respons awal (SLA) dan mencatat respons resmi pertama sekolah.");

$bk->addHeading2('3.3. Mengelola Kategori Baru & Penilaian Risiko (Risk Assessment)');
$bk->addParagraph('Sistem Sahabat Sekolah versi terbaru telah dilengkapi kategori yang sangat komprehensif, mencakup perundungan fisik, verbal, siber, sosial, seksual, diskriminasi SARA, serta kategori Pemalakan & Pengompasan (pemerasan uang/barang).');
$bk->addBullet('Otomatisasi Tingkat Risiko Awal:', 'Ketika laporan masuk, sistem secara otomatis menetapkan risiko awal berdasarkan subkategori (misal: Pengeroyokan atau Pemerasan Rutin berstatus CRITICAL, Pemalakan Harian berstatus HIGH, Hinaan Verbal berstatus LOW/MEDIUM).');
$bk->addBullet('Penyesuaian Risiko Dinamis:', "Guru BK dapat menyesuaikan skor risiko (0-100) di panel 'Penilaian Risiko' berdasarkan observasi dampak trauma korban dan tingkat ancaman pengulangan.");

$bk->addHeading2('3.4. Melakukan Komunikasi Dua Arah dengan Pelapor Anonim');
$bk->addStep(1, 'Buka Panel Pesan Anonim', "Jika laporan berstatus ANONYMOUS, panel 'Kanal Komunikasi Anonim' akan aktif di bagian bawah halaman kasus.");
$bk->addStep(2, 'Kirimkan Pesan Bimbingan', "Ketik pertanyaan klarifikasi atau pesan dukungan empati (contoh: 'Halo Nak, terima kasih atas keberanianmu melapor. Jangan takut, Guru BK siap mendampingimu. Bisakah kamu sebutkan di lorong mana kejadian itu berlangsung?').");
$bk->addStep(3, 'Menerima Jawaban Murid', 'Jawaban pelapor akan langsung masuk ke panel tanpa pernah menampilkan identitas atau perangkat pelapor.');

$bk->addHeading2('3.5. Alur Eskalasi Kasus (Bila Diperlukan)');
$bk->addParagraph('Jika kasus melibatkan kekerasan fisik berat, penolakan mediasi oleh orang tua pelaku, atau melibatkan pihak luar sekolah, Guru BK wajib melakukan eskalasi:');
$bk->addStep(1, "Klik Tombol 'Eskalasi Kasus'", 'Pilih target eskalasi: Kepala Sekolah, Dinas Pendidikan Kota, atau UPTD PPA Kota Bukittinggi.');
$bk->addStep(2, 'Tulis Alasan & Dokumen Permohonan', 'Jelaskan hambatan penanganan di sekolah dan lampirkan surat kronologis singkat.');
$bk->addStep(3, 'Pemantauan Bersama', 'Setelah dieskalasi, pihak Kepala Sekolah atau Satgas PPKSP Dinas/PPA akan bergabung dalam pemantauan kasus dan memberikan arahan disposisi tertulis.');

$bk->addHeading2('3.6. Resolusi Tuntas & Penutupan Kasus (Closing)');
$bk->addStep(1, 'Catat Tindakan Mediasi / Konseling', "Tuliskan hasil akhir mediasi damai pada bagian 'Tambah Catatan Kasus'.");
$bk->addStep(2, 'Unggah Dokumen Penyelesaian (Wajib)', "Pilih jenis dokumen 'Dokumen Penyelesaian', lalu unggah file scan 'Berita Acara Mediasi Damai' atau 'Surat Pernyataan Komitmen Perlindungan Anak' bermaterai.");
$bk->addStep(3, 'Tandai Kasus Selesai (CLOSED)', "Pilih opsi status 'CLOSED (Kasus Selesai Tuntas)' lalu klik simpan. Kasus akan diarsipkan dan tercatat sebagai keberhasilan resolusi sekolah.");

$bk->save($outputDir.'/PANDUAN_PENGGUNAAN_GURU_BK.docx');
echo "Completed: PANDUAN_PENGGUNAAN_GURU_BK.docx\n\n";

// =========================================================================
// 2. MANUAL KEPALA SEKOLAH (PRINCIPAL)
// =========================================================================
echo "Building: PANDUAN_PENGGUNAAN_KEPALA_SEKOLAH.docx...\n";
$ks = new DocxBuilder(
    'PANDUAN OPERASIONAL & PENGAWASAN PIMPINAN',
    'MODUL KEPALA SEKOLAH (PRINCIPAL)',
    'Kepala Sekolah'
);

$ks->addTitle('SAHABAT SEKOLAH — KOTA BUKITTINGGI')
    ->addSubtitle('Buku Panduan Monitoring Pimpinan, Penanganan Kasus Eskalasi, dan Akuntabilitas Penanganan Kekerasan Sekolah')
    ->addMetaTable([
        'Nama Dokumen' => 'Buku Panduan Pengguna — Peran Kepala Sekolah (PRINCIPAL)',
        'Platform' => 'SAHABAT SEKOLAH (Portal Pengawasan Satuan Pendidikan)',
        'Wilayah Implementasi' => 'Satuan Pendidikan Negeri & Swasta se-Kota Bukittinggi',
        'Dasar Hukum' => 'Permendikbudristek No. 46 Tahun 2023 tentang Pencegahan dan Penanganan Kekerasan (PPKSP)',
        'Peruntukan Role' => 'Kepala Sekolah / Ketua Tim Pencegahan dan Penanganan Kekerasan (TPPK)',
        'Versi Panduan' => '2.0 (November 2026)',
        'Klasifikasi Keamanan' => 'Dokumen Manajerial & Kebijakan Sekolah (Terbatas)',
    ]);

$ks->addHeading1('BAB I: FUNGSI KEPALA SEKOLAH DALAM SISTEM');
$ks->addParagraph('Sebagai penanggung jawab tertinggi di satuan pendidikan, Kepala Sekolah memegang kendali atas iklim keamanan dan kenyamanan belajar peserta didik. Modul Kepala Sekolah pada platform Sahabat Sekolah dirancang sebagai Executive Dashboard yang memberikan visibilitas penuh terhadap tren perundungan, kepatuhan waktu kerja Guru BK, serta penanganan kasus-kasus yang memerlukan diskresi atau intervensi pimpinan.');

$ks->addHeading2('1.1. Tanggung Jawab Utama Pimpinan');
$ks->addBullet('Pemantauan Indikator Kinerja SLA:', 'Memastikan tidak ada laporan siswa yang dibiarkan melewati batas respons (Overdue).');
$ks->addBullet('Disposisi Kasus Eskalasi:', 'Memberikan keputusan manajerial terhadap kasus yang membutuhkan pemanggilan orang tua tingkat pimpinan atau koordinasi aparat hukum.');
$ks->addBullet('Otorisasi Eskalasi Lintas Instansi:', 'Meneruskan kasus kritis sekolah kepada Satgas PPKSP Dinas Pendidikan atau UPTD PPA bila pelaku berasal dari luar sekolah atau butuh penampungan aman.');
$ks->addBullet('Pengawasan Portofolio Prestasi:', 'Mendorong apresiasi capaian bakat minat siswa di bidang akademik, olahraga, seni budaya, dan keagamaan.');

$ks->addHeading1('BAB II: ANATOMI DASHBOARD KEPALA SEKOLAH');
$ks->addTable(
    ['Komponen Panel', 'Fungsi & Informasi yang Disajikan', 'Tindakan Rekomendasi Pimpinan'],
    [
        ['Kartu Metrik Eksekutif', 'Total laporan aktif, kasus butuh tindakan, kasus risiko tinggi/kritis, dan angka resolusi', 'Mengevaluasi tren keamanan lingkungan belajar setiap awal pekan'],
        ['Panel Kasus Eskalasi', 'Daftar kasus yang diteruskan secara khusus oleh Guru BK kepada Kepala Sekolah', 'Wajib segera dibuka untuk membaca kronologi dan memberikan instruksi tertulis'],
        ['Indikator Kepatuhan SLA', 'Statistik persentase kasus On-Time vs Peringatan (Warning) vs Terlambat (Overdue)', 'Bila angka peringatan tinggi, berikan dukungan staf atau koordinasi tambahan bagi Guru BK'],
        ['Cetak Rekapitulasi Berkas', 'Fitur unduh cetak lembar profil kasus dengan watermark dinas resmi', 'Digunakan untuk bahan laporan tertutup rapat dinas atau komite sekolah'],
    ],
    [2400, 4400, 2800]
);

$ks->addHeading1('BAB III: LANGKAH PENGOPERASIAN APLIKASI');
$ks->addHeading2('3.1. Masuk ke Akun Pimpinan');
$ks->addStep(1, 'Akses Halaman Login', "Buka portal Sahabat Sekolah, klik menu 'Masuk Petugas'.");
$ks->addStep(2, 'Gunakan Akun Resmi Kepala Sekolah', 'Ketikkan email kedinasan Kepala Sekolah (misal: kepsek_sd01@sahabat.test atau kepsek@sahabat.test).');
$ks->addStep(3, 'Masuk ke Halaman Pimpinan (/principal)', 'Sistem otomatis mengarahkan Anda ke portal khusus pimpinan sekolah.');

$ks->addHeading2('3.2. Menindaklanjuti Kasus yang Membutuhkan Intervensi');
$ks->addStep(1, 'Notifikasi Kasus Eskalasi', 'Kepala Sekolah menerima notifikasi email otomatis jika ada kasus berisiko tinggi atau terhambat yang dieskalasikan oleh Guru BK.');
$ks->addStep(2, 'Buka Rincian Kasus', 'Klik tautan kasus untuk membaca rangkuman laporan, identitas pihak terlibat, dan catatan bimbingan yang telah dicatat.');
$ks->addStep(3, 'Lakukan Mediasi Tingkat Pimpinan', 'Jadwalkan pertemuan tertutup dengan orang tua kedua pihak di ruang pimpinan untuk menyepakati resolusi damai dan sanksi edukatif.');
$ks->addStep(4, 'Eskalasi ke Satgas PPKSP Kota (Jika Diperlukan)', "Jika kasus merupakan pemerasan terorganisir (pengompasan geng luar) atau kekerasan fisik berbahaya, klik 'Eskalasi ke Satgas Kota' untuk mengundang Dinas Pendidikan dan UPTD PPA.");

$ks->save($outputDir.'/PANDUAN_PENGGUNAAN_KEPALA_SEKOLAH.docx');
echo "Completed: PANDUAN_PENGGUNAAN_KEPALA_SEKOLAH.docx\n\n";

// =========================================================================
// 3. MANUAL INSTANSI KOTA / SATGAS PPKSP (CITY_ADMIN)
// =========================================================================
echo "Building: PANDUAN_PENGGUNAAN_INSTANSI_KOTA_SATGAS_PPKSP.docx...\n";
$city = new DocxBuilder(
    'PANDUAN OPERASIONAL MONITORING KOTA & SATGAS PPKSP',
    'MODUL INSTANSI KOTA / SATGAS PPKSP (CITY_ADMIN)',
    'Instansi Kota'
);

$city->addTitle('SAHABAT SEKOLAH — KOTA BUKITTINGGI')
    ->addSubtitle('Panduan Operasional Monitoring Wilayah & Penanganan Eskalasi Jenjang 3 bagi Dinas Pendidikan dan UPTD PPA')
    ->addMetaTable([
        'Nama Dokumen' => 'Buku Panduan Pengguna — Peran Instansi Kota / Satgas PPKSP (CITY_ADMIN)',
        'Platform' => 'SAHABAT SEKOLAH (Case Management System Penanganan Kekerasan)',
        'Wilayah Implementasi' => 'Tingkat Kota Bukittinggi (Lintas Satuan Pendidikan SD, SMP, MTs, MIN)',
        'Dasar Hukum' => 'Permendikbudristek 46/2023, UU No. 35/2014 tentang Perlindungan Anak, & SK Satgas PPKSP Bukittinggi',
        'Instansi Terkait' => "1. Dinas Pendidikan dan Kebudayaan Kota Bukittinggi\n2. UPTD PPA - Dinas DP3APPKB Kota Bukittinggi",
        'Peruntukan Role' => 'Pejabat Dinas Pendidikan, Konselor Psikolog UPTD PPA, dan Anggota Satgas PPKSP Kota',
        'Versi Panduan' => '2.0 (November 2026)',
        'Klasifikasi Keamanan' => 'Dokumen Kedinasan Strategis & Koordinasi Lintas Sektor (Terbatas)',
    ]);

$city->addHeading1('BAB I: RUANG LINGKUP INSTANSI TINGKAT KOTA');
$city->addParagraph('Berdasarkan regulasi nasional pencegahan kekerasan, penanganan kasus di lingkungan sekolah seringkali membutuhkan intervensi lintas sektoral di luar kewenangan tunggal sekolah. Peran CITY_ADMIN dirancang untuk menyatukan dua instansi pilar Pemerintah Kota Bukittinggi:');
$city->addBullet('Dinas Pendidikan & Kebudayaan Kota Bukittinggi (Akun: dinas@sahabat.test):', 'Berfokus pada pengawasan manajerial sekolah, kepatuhan kode etik guru/staf, pemanggilan antar-kepala sekolah, mediasi sanksi administratif, dan perlindungan hak kelanjutan belajar murid.');
$city->addBullet('UPTD PPA - DP3APPKB Kota Bukittinggi (Akun: ppa@sahabat.test):', 'Unit Pelaksana Teknis Daerah Perlindungan Perempuan dan Anak berfokus pada pendampingan psikologis klinis, rujukan medis dan visum et repertum, bantuan hukum anak berhadapan dengan hukum (ABH), serta fasilitas rumah aman (safe house).');
$city->addBullet('Satgas Gabungan PPKSP Kota Bukittinggi:', 'Forum tanggap darurat koordinatif untuk menangani insiden tawuran, pengeroyokan geng, kekerasan seksual berat, atau pemerasan teror berkelompok.');

$city->addHeading1('BAB II: HAK AKSES LINTAS SEKOLAH (CITY-WIDE SCOPE)');
$city->addParagraph('Berbeda dengan akun sekolah yang terkunci pada 1 NPSN, akun Instansi Kota memiliki hak akses lintas sekolah se-Kota Bukittinggi:');
$city->addTable(
    ['Modul Aplikasi', 'Hak Akses Instansi Kota', 'Manfaat Operasional'],
    [
        ['Dashboard Monitoring Kota', 'Melihat grafik tren perundungan per bulan, rasio kepatuhan SLA sekolah, dan sebaran kategori', 'Pemetaan zona kerawanan wilayah dan evaluasi efektivitas program ramah anak'],
        ['Penanganan Eskalasi Jenjang 3', 'Menerima notifikasi otomatis untuk kasus yang diteruskan oleh sekolah ke meja dinas/UPTD PPA', 'Intervensi proaktif sebelum insiden membesar atau berdampak hukum'],
        ['Detail Kasus Terbuka', 'Membuka kronologi, asesmen risiko, rekaman mediasi, dan bukti lampiran dari sekolah mana pun', 'Pemeriksaan data faktual secara transparan dan akurat'],
        ['Sinkronisasi Direktori Sekolah', 'Memantau keterhubungan data pokok pendidikan sekolah se-Bukittinggi via API AConnect', 'Memastikan setiap sekolah aktif memiliki petugas penanggung jawab'],
    ],
    [2400, 4400, 2800]
);

$city->addHeading1('BAB III: TATA CARA PENANGANAN KASUS ESKALASI KOTA');
$city->addStep(1, 'Penerimaan Notifikasi Kasus Eskalasi', 'Saat Kepala Sekolah atau Guru BK mengeskalasikan kasus ke tingkat kota, sistem mengirimkan email pemberitahuan prioritas tinggi ke email instansi terkait.');
$city->addStep(2, 'Pemeriksaan Rekam Medis & Kronologis', 'Masuk ke portal (/admin), buka nomor kasus yang bersangkutan. Tinjau foto visum, bukti CCTV, serta riwayat upaya damai yang telah dilakukan sekolah.');
$city->addStep(3, 'Intervensi Lapangan & Disposisi', "Pejabat Dinas Pendidikan atau Konselor UPTD PPA mencatat tindakan pendampingan resmi pada kolom catatan kasus (misal: 'UPTD PPA menerjunkan psikolog klinis untuk pendampingan trauma healing ananda').");
$city->addStep(4, 'Koordinasi Lintas Sekolah', 'Dinas Pendidikan dapat memanggil Kepala Sekolah asal pelaku untuk memastikan sanksi disipliner dan komitmen tidak mengulangi perbuatan.');

$city->save($outputDir.'/PANDUAN_PENGGUNAAN_INSTANSI_KOTA_SATGAS_PPKSP.docx');
echo "Completed: PANDUAN_PENGGUNAAN_INSTANSI_KOTA_SATGAS_PPKSP.docx\n\n";

// =========================================================================
// 4. MANUAL ADMINISTRATOR SISTEM (ADMIN)
// =========================================================================
echo "Building: PANDUAN_PENGGUNAAN_ADMINISTRATOR_SISTEM.docx...\n";
$adm = new DocxBuilder(
    'PANDUAN TEKNIS & ADMINISTRASI SISTEM',
    'MODUL ADMINISTRATOR SISTEM (ADMIN)',
    'Administrator Sistem'
);

$adm->addTitle('SAHABAT SEKOLAH — KOTA BUKITTINGGI')
    ->addSubtitle('Buku Panduan Pengelolaan Akun, Konfigurasi SLA, Master Data Kategori, dan Audit Keamanan Sistem')
    ->addMetaTable([
        'Nama Dokumen' => 'Buku Panduan Pengguna — Peran Administrator Sistem (ADMIN)',
        'Platform' => 'SAHABAT SEKOLAH (Case Management System Penanganan Kekerasan)',
        'Wilayah Implementasi' => 'Pusat Pengelolaan Sistem Kota Bukittinggi',
        'Peruntukan Role' => 'Administrator TI Dinas Pendidikan / Tim Pengembang Sistem',
        'Versi Panduan' => '2.0 (November 2026)',
        'Klasifikasi Keamanan' => 'Dokumen Konfigurasi Teknis & Manajemen Akun (Superuser / Rahasia)',
    ]);

$adm->addHeading1('BAB I: RUANG LINGKUP & TANGGUNG JAWAB ADMINISTRATOR');
$adm->addParagraph('Administrator Sistem memegang hak akses tertinggi (Superuser) dalam ekosistem Sahabat Sekolah. Tanggung jawab Administrator berfokus pada kelangsungan layanan infrastruktur, manajemen akun pengguna se-kota, konfigurasi matriks SLA, pemutakhiran kategori perundungan, dan pemantauan jejak audit kepatuhan privasi data.');

$adm->addHeading1('BAB II: PANDUAN PENGELOLAAN PENGGUNA (/admin/users)');
$adm->addStep(1, 'Akses Menu Manajemen Pengguna', "Buka menu 'Pengguna' pada bilah navigasi admin (/admin/users).");
$adm->addStep(2, 'Pendaftaran Akun Baru', "Klik tombol '+ Tambah Pengguna'. Masukkan nama lengkap dan email resmi.");
$adm->addStep(3, 'Pemilihan Peran & Sekolah', 'Pilih peran pengguna: Guru BK (COUNSELOR) atau Kepala Sekolah (PRINCIPAL) wajib menautkan sekolah asal dari direktori NPSN AConnect. Untuk Instansi Kota (CITY_ADMIN), pilih instansi Dinas Pendidikan atau UPTD PPA.');
$adm->addStep(4, 'Pengaturan Kata Sandi & Reset Aman', 'Pembuatan akun baru menghasilkan kata sandi terenkripsi. Jika staf lupa sandi, Administrator dapat menekan tombol kunci (Reset) untuk mengirimkan tautan reset berbatas waktu 60 menit.');

$adm->addHeading1('BAB III: PENGATURAN SLA & MASTER DATA KATEGORI');
$adm->addParagraph("Administrator bertanggung jawab mengonfigurasi batas waktu pelayanan (SLA Configurations) pada menu '/admin/sla-configurations':");
$adm->addTable(
    ['Tingkat Risiko', 'Target Respons Awal', 'Target Penyelesaian (Resolusi)', 'Ketentuan Waktu Operasional'],
    [
        ['LOW', '24 Jam Kerja', '7 Hari Kerja', 'Dihitung pada jam kerja kedinasan (Senin - Jumat 08:00 - 16:00 WIB)'],
        ['MEDIUM', '24 Jam Kerja', '7 Hari Kerja', 'Dihitung pada jam kerja kedinasan (Senin - Jumat 08:00 - 16:00 WIB)'],
        ['HIGH', '4 Jam Kerja', '3 Hari Kerja', 'Dipercepat untuk mencegah ancaman kekerasan berkelanjutan'],
        ['CRITICAL', '1 Jam (Instant)', '24 Jam', 'Berlaku darurat 24 jam non-stop (termasuk hari libur dan malam hari)'],
    ],
    [2000, 2400, 2400, 2800]
);

$adm->addHeading2('3.1. Pengelolaan Kategori & Subkategori Perundungan');
$adm->addParagraph('Pada versi 2.0, sistem mendukung kategori komprehensif (Fisik, Verbal, Pemalakan/Pengompasan, Sosial, Siber, Seksual, Diskriminasi SARA). Administrator dapat menambahkan subkategori baru, mengatur skor risiko default, dan mengaktifkan/menonaktifkan kategori sesuai dinamika di lapangan.');

$adm->addHeading1('BAB IV: PEMELIHARAAN SISTEM & AUDIT PRIVASI');
$adm->addBullet('Pemantauan Antrean Pekerjaan (Queue Worker):', "Memastikan background service 'php artisan queue:work' aktif untuk menangani notifikasi email seketika.");
$adm->addBullet('Pemindaian Otomatis SLA (Cron Scanner):', "Memastikan perintah terjadwal 'php artisan sahabat:sla-scan' dieksekusi berkala setiap jam.");
$adm->addBullet('Audit Log Trail:', 'Memantau tabel audit log atas setiap tindakan sensitif (pengunduhan bukti kasus, perubahan data murid, atau penutupan kasus).');

$adm->save($outputDir.'/PANDUAN_PENGGUNAAN_ADMINISTRATOR_SISTEM.docx');
echo "Completed: PANDUAN_PENGGUNAAN_ADMINISTRATOR_SISTEM.docx\n\n";

// =========================================================================
// 5. MANUAL SISWA / PELAPOR PUBLIK (STUDENT & REPORTER)
// =========================================================================
echo "Building: PANDUAN_PENGGUNAAN_PELAPOR_DAN_SISWA.docx...\n";
$usr = new DocxBuilder(
    'PANDUAN PENGGUNAAN KANAL PELAPORAN PUBLIK',
    'MODUL PESERTA DIDIK, SAKSI, & ORANG TUA',
    'Pelapor Publik'
);

$usr->addTitle('SAHABAT SEKOLAH — KOTA BUKITTINGGI')
    ->addSubtitle('Panduan Ramah Anak: Cara Melapor dengan Aman, Dikte Suara, Melacak Laporan, dan Chat Rahasia')
    ->addMetaTable([
        'Nama Dokumen' => 'Buku Panduan Pengguna — Kanal Pelapor Publik & Peserta Didik',
        'Platform' => 'SAHABAT SEKOLAH (Ruang Aman Lapor Perundungan & Kekerasan)',
        'Wilayah Implementasi' => 'Seluruh Satuan Pendidikan (SD, SMP, MTs, MIN) Kota Bukittinggi',
        'Peruntukan Pengguna' => 'Siswa (Korban/Saksi), Orang Tua Murid, dan Masyarakat Sekolah',
        'Versi Panduan' => '2.0 (November 2026)',
        'Bahasa & Gaya' => 'Bahasa Indonesia Ramah Anak, Jelas, & Mengedepankan Empati',
    ]);

$usr->addHeading1('BAB I: RUANG AMAN UNTUK BERSUARA');
$usr->addParagraph('Halo Teman-Teman dan Sahabat Sekolah! Apakah kamu pernah melihat temanmu diejek terus-menerus, dipukul, dipalak uang jajannya, dikucilkan, atau diancam di media sosial? Atau mungkin kamu sendiri yang mengalaminya dan merasa bingung harus cerita ke siapa?');
$usr->addParagraph('Sahabat Sekolah dibuat khusus oleh Pemerintah Kota Bukittinggi agar setiap anak di Bukittinggi bisa belajar dengan aman, tenang, dan gembira. Di sini, kamu bisa bersuara tanpa rasa takut karena ceritamu didengar, dilindungi, dan ditangani oleh Guru BK penuh kepedulian.');

$usr->addHeading2('1.1. Tiga Pilihan Kejujuran Identitas Kamu');
$usr->addBullet('1. Lapor Terbuka (Identified):', 'Nama dan kontakmu tercatat jelas agar Guru BK bisa langsung mengajakmu dan orang tuamu berdiskusi bersama.');
$usr->addBullet('2. Lapor Rahasia (Confidential):', 'Nama dan kelasmu HANYA diketahui oleh Guru BK yang menangani. Teman sekelas atau pelaku tidak akan pernah tahu siapa yang melapor.');
$usr->addBullet('3. Lapor Anonim (Anonymous):', "Kamu sama sekali TIDAK PERLU menuliskan nama, email, nomor HP, atau membuat akun! Kamu akan mendapatkan 'Kunci Rahasia 4 Kata' untuk melacak laporanmu.");

$usr->addHeading1('BAB II: CARA MUDAH MEMBUAT LAPORAN');
$usr->addStep(1, 'Buka Form Pelaporan', "Buka website Sahabat Sekolah di HP atau komputermu, lalu klik tombol 'Buat Laporan'. Kamu juga bisa langsung memindai (scan) QR Code yang ditempel di mading sekolahmu.");
$usr->addStep(2, 'Pilih Sekolahmu', 'Pilih nama sekolahmu dari daftar. Jika kamu memindai QR Code di sekolah, nama sekolah akan otomatis terisi.');
$usr->addStep(3, 'Pilih Kategori & Subkategori Kejadian', 'Pilih jenis kejadian yang terjadi (misal: Perundungan Fisik, Verbal, Pemalakan/Pengompasan Uang, Siber, atau Pengucilan Sosial), lalu klik bentuk spesifiknya.');
$usr->addStep(4, 'Ceritakan Kejadiannya (Bisa Mengetik atau Menggunakan Suara)', "Tuliskan apa yang terjadi, kapan, di mana, dan siapa saja yang terlibat. Jika sulit mengetik, klik tombol 'Bicara / Dikte Suara' untuk langsung berbicara ke mikrofon HP-mu dalam Bahasa Indonesia.");
$usr->addStep(5, 'Unggah Bukti Pendukung (Opsional)', 'Kamu bisa melampirkan foto luka, foto lokasi, atau tangkapan layar pesan ancaman hingga 5 file (maksimal 10 MB per file). Lokasi foto kamu otomatis dibersihkan oleh sistem demi privasimu.');
$usr->addStep(6, 'Kirimkan Laporan', "Klik tombol 'Kirim Laporan'. Laporanmu akan langsung sampai di meja Guru BK sekolahmu.");

$usr->addHeading1('BAB III: KUNCI RAHASIA 4 KATA & KARTU BUKTI');
$usr->addParagraph('Setelah berhasil mengirim laporan anonim, layar akan menampilkan Kartu Bukti Lapor yang memuat Nomor Laporan dan Kunci Rahasia 4 Kata (contoh: kancil-cerdas-lembah-merapi).');

$usr->addCallout('PENTING: Simpan Kunci Rahasiamu!', 'Catat atau ambil tangkapan layar (screenshot) Kunci Rahasia 4 Kata tersebut! Kunci ini adalah alat rahasiamu untuk melacak perkembangan penanganan kasus dan mengobrol dengan Guru BK tanpa perlu login atau menggunakan akun.', 'danger');

$usr->addHeading1('BAB IV: CARA MELACAK LAPORAN & CHAT DENGAN GURU BK');
$usr->addStep(1, 'Buka Halaman Lacak (/track)', "Pilih menu 'Lacak Laporan' pada website Sahabat Sekolah.");
$usr->addStep(2, 'Masukkan Kunci Rahasia atau Kode Laporan', "Ketikkan 4 kata rahasiamu pada kolom pelacakan, lalu klik 'Lacak Kasus'.");
$usr->addStep(3, 'Lihat Tahapan Penanganan', 'Kamu bisa melihat sejauh mana laporanmu diproses: Mulai dari Diterima, Diverifikasi, Dalam Penanganan, hingga Kasus Selesai Tuntas.');
$usr->addStep(4, 'Kirim Pesan ke Guru BK', 'Gunakan kotak pesan rahasia di bawah status laporan untuk membalas pertanyaan atau memberikan informasi tambahan kepada Guru BK.');

$usr->addHeading1('BAB V: NOMOR DARURAT KOTA BUKITTINGGI');
$usr->addParagraph('Jika kamu atau temanmu sedang dalam situasi bahaya fisik mendesak yang membutuhkan pertolongan seketika, jangan ragu untuk menghubungi kontak darurat resmi berikut:');
$usr->addTable(
    ['Layanan Bantuan', 'Nomor Kontak', 'Keterangan'],
    [
        ['Panggilan Darurat Bukittinggi', '112 (Bebas Pulsa)', 'Layanan tanggap darurat 24 jam Pemerintah Kota Bukittinggi'],
        ['Layanan SAPA PPPA', '129 / WhatsApp 08111-129-129', 'Layanan Sahabat Perempuan dan Anak Kementerian PPPA & UPTD PPA'],
        ['UPTD PPA Kota Bukittinggi', 'Kantor Dinas DP3APPKB', 'Layanan pendampingan hukum, psikologis, dan safe house ramah anak'],
    ],
    [3200, 3000, 3400]
);

$usr->save($outputDir.'/PANDUAN_PENGGUNAAN_PELAPOR_DAN_SISWA.docx');
echo "Completed: PANDUAN_PENGGUNAAN_PELAPOR_DAN_SISWA.docx\n\n";

echo "=========================================================================\n";
echo "SUCCESS: SEMUA 5 BUKU PANDUAN PER ROLE TELAH BERHASIL DIPERBARUI DI:\n";
echo $outputDir."\n";
echo "=========================================================================\n";
