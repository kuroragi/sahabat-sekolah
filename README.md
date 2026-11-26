# SAHABAT SEKOLAH

<p align="center">
  <strong>Platform Pelaporan, Pencegahan, dan Penanganan Perundungan Terpadu Satuan Pendidikan Kota Bukittinggi</strong><br>
  <em>“Dengarkan, Lindungi, Dampingi — Sekolah Aman, Semua Nyaman”</em>
</p>

---

## 📌 Tentang Sahabat Sekolah

**SAHABAT SEKOLAH** merupakan platform *Case Management System (CMS)* terpadu berbasis web dan API mobile yang dikembangkan untuk pencegahan, pelaporan aman, penanganan terukur, pendampingan, serta pengawasan kasus perundungan (*bullying*) dan kekerasan di lingkungan satuan pendidikan (SD/SMP/sederajat) se-Kota Bukittinggi.

Sistem ini bukan sekadar kotak pengaduan digital biasa, melainkan tata kelola penanganan kasus komprehensif dari hulu ke hilir (*end-to-end*): mulai dari laporan masuk, penilaian risiko (*risk assessment*), respons awal terikat batas waktu (*SLA Engine*), eskalasi bertingkat ke pimpinan sekolah & Satgas PPKSP kota (Dinas Pendidikan & UPTD PPA), hingga penerbitan Berita Acara Mediasi Damai resmi untuk penutupan kasus (*closed*).

---

## 🏛️ Arsitektur & Peran Pengguna (Multi-Tenant & Role-Based)

Sistem mengadopsi isolasi multi-tenant berbasis **Nomor Pokok Sekolah Nasional (NPSN)** terintegrasi dengan data pokok pendidikan Kota Bukittinggi (AConnect API).

```mermaid
flowchart TD
    Pelapor["Pelapor Publik (Siswa / Korban / Saksi / Ortu)"] -->|Form Publik / QR Code / Speech-to-Text| Sistem["Platform Sahabat Sekolah"]
    Sistem -->|Antrean Kasus & Chat Anonim 2-Arah| GuruBK["Guru BK / TPPK (First Responder)"]
    GuruBK -->|Eskalasi Internal Sekolah| Kepsek["Kepala Sekolah (Principal)"]
    Kepsek -->|Eskalasi Jenjang 3 (Satgas PPKSP)| Disdik["Dinas Pendidikan & Kebudayaan"]
    Kepsek -->|Rujukan Psikologis & Hukum| PPA["UPTD PPA (DP3APPKB)"]
    Disdik & PPA -->|Intervensi & Monitoring Kota| GuruBK
    Admin["Administrator Sistem (TI)"] -->|Kelola Akun, SLA, Kategori| Sistem
```

### 👥 5 Peran Pengguna Utama:
1. **Pelapor Publik (Peserta Didik, Saksi, Orang Tua):**
   - Mengirim laporan kejadian dengan memilih sekolah secara manual atau otomatis via scan **QR Code poster sekolah**.
   - Dilengkapi fitur pengetikan kronologi dan **Bicara / Dikte Suara (*Speech-to-Text* Bahasa Indonesia)**.
   - Melacak tahapan penanganan secara transparan menggunakan **Kunci Rahasia 4 Kata** (*word-code*) atau nomor tiket tanpa perlu membuat akun.
2. **Guru Bimbingan Konseling (COUNSELOR / First Responder):**
   - Mengelola antrean laporan masuk, memulai verifikasi untuk menghentikan penghitung waktu SLA.
   - Melakukan obrolan rahasia 2 arah (*two-way messaging*) dengan pelapor anonim.
   - Melakukan penilaian risiko (skor 0–100), pencatatan kronologis/konseling, eskalasi kasus, dan pengunggahan Berita Acara Mediasi Damai.
3. **Kepala Sekolah (PRINCIPAL):**
   - Memantau metrik eksekutif sekolah melalui portal pimpinan (`/principal`), rasio kepatuhan SLA, serta riwayat eskalasi.
   - Memberikan arahan disposisi, memimpin mediasi pimpinan, mengotorisasi eskalasi tingkat kota, dan mengunduh berkas rekapitulasi ber-watermark.
4. **Instansi Tingkat Kota & Satgas PPKSP (CITY_ADMIN):**
   - Menyatukan **Dinas Pendidikan & Kebudayaan** (`dinas@sahabat.test`) dan **UPTD PPA DP3APPKB** (`ppa@sahabat.test`).
   - Memantau grafik tren perundungan kota lintas SD/SMP se-Bukittinggi (`school_npsn = null`).
   - Menerima dan mengintervensi kasus eskalasi tingkat kota (pemanggilan pimpinan sekolah, pendampingan visum medis, bantuan psikolog klinis, dan perlindungan saksi/korban).
5. **Administrator Sistem (ADMIN):**
   - Mengelola akun staf se-kota, reset kata sandi instan berbasis token aman 60 menit.
   - Mengonfigurasi matriks waktu pelayanan (SLA Configurations), master data kategori/subkategori, serta pemantauan audit trail.

---

## ✨ Fitur-Fitur Unggulan

### 1. Tiga Mode Perlindungan Identitas (Zero Fear Reporting)
- **Identified (Terbuka):** Identitas pelapor tercatat jelas untuk mempermudah komunikasi tatap muka.
- **Confidential (Rahasia):** Identitas hanya dapat diakses oleh Guru BK yang menangani; dirahasiakan dari publik, teman, dan terduga pelaku.
- **Anonymous (Anonim Kriptografis):** Pelapor tidak perlu login/memasukkan data pribadi. Sistem menerbitkan **Token Rahasia & Kunci 4 Kata Unik** yang memungkinkan komunikasi dua arah dengan Guru BK tanpa membocorkan identitas maupun IP pelapor.

### 2. Kategori & Subkategori Perundungan Komprehensif
Sistem memetakan spektrum perundungan modern dengan penentuan tingkat risiko bawaan (*default risk level*) yang terhubung langsung ke mesin SLA:
- 🥊 **Perundungan Fisik (`PHYSICAL`):** Mendorong, memukul/menendang (`HIGH`), pengeroyokan berkelompok (`CRITICAL`), penyerangan senjata (`CRITICAL`).
- 🗣️ **Perundungan Verbal (`VERBAL`):** Ejekan/julukan merendahkan (`LOW`), mempermalukan di depan umum (`MEDIUM`), ancaman lisan (`HIGH`).
- 💰 **Pemalakan & Pengompasan (`EXTORTION`):** Pemalakan uang jajan (`HIGH`), pemaksaan tugas/piket (`MEDIUM`), penyitaan paksa barang (`HIGH`), pemerasan rutin ancaman teror (`CRITICAL`).
- 🌐 **Cyberbullying (`CYBER`):** Komentar kebencian medsos (`MEDIUM`), penyebaran data pribadi / Doxxing (`HIGH`), impersonation akun palsu (`HIGH`), ancaman digital ekstrem (`CRITICAL`).
- 👥 **Perundungan Sosial & Relasional (`SOCIAL`):** Pengucilan/boikot sosial (`MEDIUM`), penyebaran rumor/fitnah (`MEDIUM`), penghasutan pertemanan (`MEDIUM`).
- ⚠️ **Perundungan Bernuansa Seksual (`SEXUAL`):** Catcalling/komentar seksual (`HIGH`), sentuhan tanpa persetujuan (`CRITICAL`), eksploitasi konten intim (`CRITICAL`).
- ⚖️ **Diskriminasi SARA & Disabilitas (`DISCRIMINATION`):** Ejekan ekonomi (`MEDIUM`), pelecehan suku/agama/ras (`HIGH`), perundungan murid disabilitas (`HIGH`).

### 3. Service Level Agreement (SLA Engine)
Pemantauan waktu tanggap otomatis yang disesuaikan dengan tingkat keparahan insiden:
- **CRITICAL:** Target respons awal **1 Jam** (Darurat 24 Jam non-stop) · Target resolusi 24 Jam.
- **HIGH:** Target respons awal **4 Jam Kerja** · Target resolusi 3 Hari Kerja.
- **MEDIUM / LOW:** Target respons awal **24 Jam Kerja** · Target resolusi 7 Hari Kerja.
*Scheduler otomatis (`php artisan sahabat:sla-scan`) mendeteksi dan memberi status `WARNING` jika sisa waktu mendekati batas, serta menerbitkan peringatan `OVERDUE` jika terlambat.*

### 4. Siklus Hidup Kasus yang Terstandarisasi
$$\text{PENDING\_RESPONSE} \longrightarrow \text{UNDER\_VERIFICATION} \longrightarrow \text{IN\_HANDLING} \longrightarrow \text{RESOLVED} \longrightarrow \text{CLOSED}$$
*Kasus hanya diizinkan ditutup (**CLOSED**) apabila Guru BK telah mengunggah Berita Acara Mediasi Damai atau Surat Rekomendasi Resmi.*

### 5. Keamanan Berkas & Privasi (Privacy-Safe Storage)
- Mendukung unggahan bukti foto, video, rekaman audio, dan berkas PDF hingga **10 MB per file**.
- Sistem secara otomatis **membersihkan metadata EXIF (termasuk koordinat GPS)** dari foto kamera ponsel demi melindungi privasi tempat tinggal/lokasi anak.
- Penyimpanan berkas terisolasi menggunakan MinIO S3-Compatible / Private Disk Storage dengan izin unduh ber-audit log.

### 6. Fitur Bicara / Dikte Suara (*Speech-to-Text*)
- Terintegrasi langsung dengan Web Speech API (Bahasa Indonesia `id-ID`) untuk mempermudah murid yang kesulitan mengetik teks panjang.
- Dilengkapi penanganan izin mikrofon browser yang aman (`Permissions-Policy: microphone=(self)`).

### 7. Pusat Dokumentasi & Penampil PDF Panduan Terintegrasi
- Menu **Panduan Penggunaan** langsung tersedia di sidebar antarmuka.
- Menampilkan pratinjau buku panduan format PDF berstandar A4 kedinasan secara interaktif (*embedded viewer*) yang disaring otomatis sesuai peran pengguna (Role-Based Access), disertai tombol unduh format dokumen Word (`.docx`).

### 8. Modul Portofolio Prestasi Siswa
- Wadah pencatatan capaian positif murid di bidang Akademik, Olahraga, Seni Budaya, dan Keagamaan untuk mengimbangi pendekatan penanganan kasus dengan apresiasi iklim positif sekolah.

---

## 🛠️ Persyaratan Lingkungan (Requirements)

- **PHP:** >= 8.3 (dengan ekstensi `pdo_pgsql`/`pdo_mysql`, `mbstring`, `fileinfo`, `gd`, `zip`)
- **Composer:** >= 2.x
- **Node.js & NPM:** Node.js >= 20.x & NPM >= 10.x
- **Database:** PostgreSQL (Direkomendasikan) atau MySQL / MariaDB
- **Web Server:** Nginx / Apache / PHP Built-in Server

---

## 🚀 Panduan Instalasi & Menjalankan Aplikasi

### 1. Clone Repositori
```bash
git clone https://github.com/username/sahabat-sekolah.git
cd sahabat-sekolah
```

### 2. Pasang Dependensi
```bash
composer install
npm install
```

### 3. Konfigurasi Lingkungan (`.env`)
Salin file konfigurasi lingkungan:
```bash
cp .env.example .env
```
Sesuaikan parameter database dan aplikasi pada `.env`:
```ini
APP_NAME="Sahabat Sekolah"
APP_ENV=local
APP_URL=http://localhost:8000

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=sahabat_sekolah
DB_USERNAME=postgres
DB_PASSWORD=secret

# Storage Driver (local atau s3-private MinIO)
FILESYSTEM_DISK=local
PRIVATE_FILESYSTEM_DISK=local
```

Generate kunci aplikasi:
```bash
php artisan key:generate
```

### 4. Migrasi & Seeding Basis Data
Jalankan migrasi tabel dan pengisian data awal (kategori perundungan komprehensif, akun dinas, akun UPTD PPA, dan akun sekolah se-Bukittinggi):
```bash
php artisan migrate --seed
```
*(Atau jalankan seeder spesifik bila diperlukan: `php artisan db:seed --class=BullyingCategorySeeder` & `php artisan db:seed --class=SchoolAccountsSeeder`)*

### 5. Kompilasi Aset Frontend
```bash
npm run build
# Atau untuk mode pengembang (hot-reload):
# npm run dev
```

### 6. Menjalankan Server Aplikasi
Jalankan server pengembangan Laravel:
```bash
php artisan serve
```
Aplikasi web kini dapat diakses melalui peramban di: **`http://127.0.0.1:8000`**

---

## 🔑 Kredensial Akun Bawaan (Default Seed Accounts)

| Peran (Role) | Email Login | Password Default | Keterangan Akses |
| :--- | :--- | :--- | :--- |
| **Administrator** | `admin@sahabat.test` | `Admin#Sahabat2026!Secure` | Hak penuh konfigurasi sistem, SLA, user |
| **Dinas Pendidikan** | `dinas@sahabat.test` | `Dinas#Bkt2026!Secure` | Satgas PPKSP / Monitoring kota se-Bukittinggi |
| **UPTD PPA Kota** | `ppa@sahabat.test` | `Ppa#Bkt2026!Secure` | Pendampingan psikolog, hukum, safe house |
| **Guru BK (SMPN 2)** | `bk@sahabat.test` | `GuruBK#SMPN2Bkt2026!` | First responder penanganan kasus SMPN 2 |
| **Kepala Sekolah (SMPN 2)** | `kepsek@sahabat.test` | `Kepsek#SMPN2Bkt2026!` | Portal pengawasan pimpinan & eskalasi sekolah |

---

## 🧪 Pengujian Otomatis (Automated Testing) & Standar Kode

Proyek ini dilengkapi dengan rangkaian pengujian fitur (*feature tests*) dan aturan bisnis ketat (90 pengujian komprehensif):

```bash
# Menjalankan pengujian PHPUnit
php artisan test --compact

# Menjalankan pengecekan format kode Laravel Pint
vendor/bin/pint --format agent
```

---

## 📖 Buku Panduan Penggunaan Resmi (Docs)

Seluruh buku panduan penggunaan terperinci per peran tersedia di folder [`docs/panduan/`](docs/panduan/):
- 📘 [Panduan Penggunaan Guru BK (.docx)](docs/panduan/PANDUAN_PENGGUNAAN_GURU_BK.docx)
- 🏫 [Panduan Penggunaan Kepala Sekolah (.docx)](docs/panduan/PANDUAN_PENGGUNAAN_KEPALA_SEKOLAH.docx)
- 🏛️ [Panduan Penggunaan Instansi Kota & Satgas PPKSP (.docx)](docs/panduan/PANDUAN_PENGGUNAAN_INSTANSI_KOTA_SATGAS_PPKSP.docx)
- 💻 [Panduan Penggunaan Administrator Sistem (.docx)](docs/panduan/PANDUAN_PENGGUNAAN_ADMINISTRATOR_SISTEM.docx)
- 🎒 [Panduan Penggunaan Peserta Didik & Pelapor Publik (.docx)](docs/panduan/PANDUAN_PENGGUNAAN_PELAPOR_DAN_SISWA.docx)

---

## 📄 Lisensi & Hak Cipta

© 2026 **Pemerintah Kota Bukittinggi** · Dinas Pendidikan dan Kebudayaan bekerjasama dengan UPTD PPA Kota Bukittinggi. Seluruh hak cipta dilindungi undang-undang.
