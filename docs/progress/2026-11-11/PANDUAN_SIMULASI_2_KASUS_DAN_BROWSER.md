# Laporan & Hasil Simulasi Browser Otomatis (2 Kasus Perundungan)
**Platform Sahabat Sekolah - Pemerintah Kota Bukittinggi**  
**Tanggal Eksekusi Browser:** 11 November 2026  
**Status Simulasi:** **100% SUCCESSFUL (7 Tangkapan Layar Browser Berhasil Diambil)**

---

## 1. Ikhtisar Simulasi Browser

Simulasi otomatis dijalankan menggunakan browser Google Chrome secara langsung (*headless automation*) terhadap lingkungan lokal `http://sahabat-sekolah.test` untuk memvalidasi interaksi formulir dan perjalanan kedua skenario kasus:
- **Kasus 1**: Perundungan Verbal ringan &rarr; Ditangani Guru BK &rarr; Mediasi Restoratif & Berita Acara &rarr; **Selesai Tuntas di Sekolah (`CLOSED`)**.
- **Kasus 2**: Pengeroyokan Fisik Berat Lintas Sekolah &rarr; Ditangani Guru BK &rarr; Eskalasi ke Kepala Sekolah &rarr; **Eskalasi ke Satgas PPKSP (Dinas Pendidikan & UPTD PPA Kota Bukittinggi) (`IN_HANDLING`)**.

---

## 2. Bukti Tangkapan Layar Browser (Screenshots)

### Tahap 1: Formulir Pelaporan Kasus 1 (Publik Siswa SMPN 2 Bukittinggi)
* **Nomor Kasus**: `SS-2026-000201`
* **Kategori Terpilih**: `Perundungan Verbal` (*Hinaan atau julukan merendahkan*)
* **Tangkapan Layar**: `docs/progress/2026-11-11/screenshots/01_form_pelaporan_kasus1.png`
* **Hasil Verifikasi UI**: Pemilihan kategori 2 kolom dan subkategori dinamis berfungsi dengan mulus.

---

### Tahap 2: Guru BK Login & Menerima Kasus 1
* **Aktor**: Bu Ratna Sari (Guru BK SMPN 2 Bukittinggi)
* **Tangkapan Layar Dashboard**: `docs/progress/2026-11-11/screenshots/02_dashboard_guru_bk.png`
* **Tangkapan Layar Detail Kasus Selesai**: `docs/progress/2026-11-11/screenshots/03_detail_kasus1_selesai_sekolah.png`
* **Hasil Verifikasi**: Kasus memiliki histori status lengkap (`PENDING_RESPONSE` &rarr; `UNDER_VERIFICATION` &rarr; `IN_HANDLING` &rarr; `RESOLVED` &rarr; `CLOSED`), dokumen `Berita_Acara_Mediasi_Damai_Fauzan.pdf` berstatus `VALID`, dan SLA berstatus `COMPLETED`.

---

### Tahap 3: Formulir Pelaporan Kasus 2 (Kekerasan Fisik Berat Lintas Sekolah)
* **Nomor Kasus**: `SS-2026-000202`
* **Kategori Terpilih**: `Perundungan Fisik` (*Memukul atau menendang*) — Risiko `HIGH`
* **Tangkapan Layar**: `docs/progress/2026-11-11/screenshots/04_form_pelaporan_kasus2.png`

---

### Tahap 4: Kepala Sekolah Login & Meninjau Kasus 2
* **Aktor**: Kepala Sekolah SMP Negeri 2 Bukittinggi
* **Tangkapan Layar Portal Pimpinan**: `docs/progress/2026-11-11/screenshots/05_portal_kepala_sekolah.png`
* **Hasil Verifikasi**: Kepala Sekolah menerima eskalasi dari Guru BK dan meneruskan eskalasi lintas sektoral ke **Satgas PPKSP Kota Bukittinggi (Disdik & UPTD PPA)**.

---

### Tahap 5: Dinas Pendidikan Kota Bukittinggi Menerima Eskalasi Kasus 2
* **Aktor**: Administrator Dinas Pendidikan Kota Bukittinggi (`dinas@sahabat.test`)
* **Tangkapan Layar Detail Kasus Eskalasi**: `docs/progress/2026-11-11/screenshots/06_detail_kasus2_eskalasi_disdik.png`
* **Hasil Verifikasi**: Kasus terdata dengan badge eskalasi, SLA responsif, dan tercatat disposisi resmi pemanggilan pimpinan sekolah asal pelaku luar.

---

### Tahap 6: UPTD PPA Kota Bukittinggi Melakukan Intervensi Perlindungan Anak
* **Aktor**: Petugas UPTD PPA - DP3APPKB Kota Bukittinggi (`ppa@sahabat.test`)
* **Tangkapan Layar Portal & Notifikasi**: `docs/progress/2026-11-11/screenshots/07_dashboard_uptd_ppa_notifikasi.png`
* **Hasil Verifikasi**: Notifikasi `URGENT` masuk ke UPTD PPA, tercatat penugasan psikolog klinis pendamping trauma anak dan koordinasi perlindungan korban.

---

## 3. Matriks Kredensial untuk Akses Langsung Pengguna

| Peran | Akun Login | Kata Sandi | Halaman Utama |
| :--- | :--- | :--- | :--- |
| **Guru BK SMPN 2** | `bk@sahabat.test` | `GuruBK#SMPN2Bkt2026!` | `http://sahabat-sekolah.test/cases/SS-2026-000201` |
| **Kepala Sekolah** | `kepala@sahabat.test` | `Kepsek#SMPN2Bkt2026!` | `http://sahabat-sekolah.test/principal` |
| **Dinas Pendidikan**| `dinas@sahabat.test` | `Dinas#Bkt2026!Secure` | `http://sahabat-sekolah.test/cases/SS-2026-000202` |
| **UPTD PPA Kota** | `ppa@sahabat.test` | `Ppa#Bkt2026!Secure` | `http://sahabat-sekolah.test/admin` |
