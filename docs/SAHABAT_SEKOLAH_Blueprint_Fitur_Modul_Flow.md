# SAHABAT SEKOLAH

## Blueprint Fitur, Modul, Flow Bisnis dan Arsitektur Aplikasi

**Platform Pelaporan, Pencegahan, dan Penanganan Perundungan Sekolah
Kota Bukittinggi**\
**Versi Dokumen: Ringkasan Sistem**

------------------------------------------------------------------------

# 1. Gambaran Umum Sistem

SAHABAT SEKOLAH merupakan platform digital untuk pencegahan, pelaporan,
penanganan, pendampingan, dan pemantauan kasus perundungan pada satuan
pendidikan.

Sistem dirancang sebagai **Case Management System**, bukan sekadar
aplikasi pengaduan.

Platform terdiri dari:

1.  Mobile Application
    -   Digunakan siswa, korban, saksi, dan pelapor.
2.  Web Dashboard
    -   Digunakan Guru BK, Kepala Sekolah, dan Administrator.

------------------------------------------------------------------------

# 2. Aktor dan Role Sistem

  Role                  Fungsi
  --------------------- ---------------------------------------------
  SUPER_ADMIN           Pengelolaan platform dan konfigurasi teknis
  CITY_ADMIN            Monitoring tingkat pemerintah kota
  SCHOOL_ADMIN          Pengelolaan sekolah dan pengguna
  COUNSELOR / Guru BK   Penerima laporan dan pengelola kasus
  PRINCIPAL             Monitoring dan pengambilan keputusan
  TEACHER               Membantu pelaporan
  STUDENT               Membuat laporan
  PARENT                Pelibatan orang tua

------------------------------------------------------------------------

# 3. Modul Utama Aplikasi

## MODULE 1 - Authentication & Identity Management

Fitur: - Login - Logout - User Management - Role Management - Permission
Management - School Scope

------------------------------------------------------------------------

## MODULE 2 - School Management

Fitur:

### Master Sekolah

-   Nama sekolah
-   NPSN
-   Jenjang
-   Alamat
-   Status

### Master Kelas

-   Kelas
-   Tahun ajaran
-   Wali kelas

### Master Pengguna

-   Guru
-   Siswa
-   Administrator

------------------------------------------------------------------------

# MODULE 3 - Student Reporting Module

Modul utama untuk pelaporan kejadian.

Flow:

    Create Report
            |
    Pilih Peran Pelapor
            |
    Pilih Mode Identitas
            |
    Kategori Perundungan
            |
    Isi Kronologi
            |
    Upload Bukti
            |
    Submit Report
            |
    Nomor Kasus

Jenis laporan:

1.  IDENTIFIED
2.  CONFIDENTIAL
3.  ANONYMOUS

------------------------------------------------------------------------

# MODULE 4 - Bullying Classification

Kategori utama:

  Kategori                 Risk Awal
  ------------------------ ---------------
  Perundungan Fisik        HIGH
  Perundungan Verbal       LOW
  Sosial/Psikologis        MEDIUM
  Cyberbullying            MEDIUM
  Intimidasi dan Ancaman   HIGH
  Pemerasan                HIGH/CRITICAL
  Kekerasan Serius         CRITICAL
  Indikasi/Kekhawatiran    MEDIUM

------------------------------------------------------------------------

# MODULE 5 - Risk Management Engine

Formula:

    BASE RISK
    +
    RISK MODIFIER
    +
    ESCALATION FACTOR

    =
    FINAL RISK

Level risiko:

  Level      Target Respons
  ---------- ----------------
  LOW        24 jam
  MEDIUM     24 jam
  HIGH       4 jam
  CRITICAL   1 jam

------------------------------------------------------------------------

# MODULE 6 - Case Management

State Machine:

    PENDING_RESPONSE

    ↓

    UNDER_VERIFICATION

    ↓

    IN_HANDLING

    ↓

    RESOLVED

    ↓

    CLOSED

Aktivitas:

-   Verifikasi
-   Konseling
-   Klarifikasi
-   Pengumpulan bukti
-   Mediasi
-   Pelibatan orang tua
-   Eskalasi

------------------------------------------------------------------------

# MODULE 7 - Evidence Management

Mengelola:

-   Foto
-   Video
-   Dokumen
-   Bukti penyelesaian

Menggunakan private storage.

------------------------------------------------------------------------

# MODULE 8 - SLA Engine

Mengatur batas waktu penanganan.

Flow:

    Report Masuk

    ↓

    Risk Engine

    ↓

    Final Risk

    ↓

    SLA Timer Start

Status SLA:

-   ON_TIME
-   WARNING
-   OVERDUE

------------------------------------------------------------------------

# MODULE 9 - Notification Engine

Jenis notifikasi:

  Trigger       Penerima
  ------------- ----------------------------
  Kasus Baru    Guru BK
  SLA Warning   Guru BK
  Overdue       Guru BK dan Kepala Sekolah

Channel:

-   In App Notification
-   Push Notification
-   Email
-   WhatsApp tahap lanjutan

------------------------------------------------------------------------

# MODULE 10 - Escalation Engine

## Automatic Escalation

Terjadi ketika:

    SLA OVERDUE

    ↓

    Notifikasi Kepala Sekolah

## Manual Escalation

Guru BK dapat melakukan eskalasi dengan:

-   alasan
-   kondisi kasus
-   tindakan sebelumnya
-   kebutuhan keputusan

------------------------------------------------------------------------

# MODULE 11 - Dashboard

## Dashboard Guru BK

Fitur:

-   Inbox laporan
-   Detail laporan
-   Filter kasus
-   SLA Countdown
-   Verifikasi
-   Case Management

## Dashboard Kepala Sekolah

Fitur:

-   Statistik kasus
-   Monitoring SLA
-   Tren perundungan
-   Kasus eskalasi

------------------------------------------------------------------------

# MODULE 12 - Analytics

Output:

-   Jumlah kasus
-   Tren perundungan
-   Kategori dominan
-   Kepatuhan SLA
-   Statistik sekolah

------------------------------------------------------------------------

# MODULE 13 - Audit Trail

Mencatat:

-   Aktivitas pengguna
-   Perubahan status
-   Akses data sensitif
-   Notifikasi
-   Eskalasi

------------------------------------------------------------------------

# 4. Flow Besar Sistem

    SISWA / SAKSI

    ↓

    MOBILE APPLICATION

    ↓

    CREATE REPORT

    ↓

    IDENTITY PROTECTION

    ↓

    RISK ENGINE

    ↓

    SLA ENGINE

    ↓

    GURU BK

    ↓

    VERIFICATION

    ↓

    CASE HANDLING

    ↓

    ESCALATION

    ↓

    HEAD SCHOOL MONITORING

    ↓

    RESOLUTION

    ↓

    CLOSED

------------------------------------------------------------------------

# 5. Arsitektur Teknologi

    Flutter Mobile

            |

    HTTPS REST API

            |

    Laravel Backend

            |

    PostgreSQL Database

            |

    Private File Storage

Pendekatan:

**Modular Monolith Architecture**

------------------------------------------------------------------------

# 6. Struktur Modul Laravel

    app/

    Domain/

    ├── Identity
    ├── Organization
    ├── Reporting
    ├── CaseManagement
    ├── RiskManagement
    ├── SLA
    ├── Escalation
    ├── Notification
    └── Audit

------------------------------------------------------------------------

# 7. Urutan Development MVP

1.  Laravel Foundation
2.  Authentication
3.  Role Permission
4.  School Management
5.  Master Data
6.  Reporting Module
7.  Guru BK Dashboard
8.  Case Management
9.  Risk Engine
10. SLA Engine
11. Notification
12. Mobile Application
13. Pilot Project

------------------------------------------------------------------------

# Kesimpulan

SAHABAT SEKOLAH adalah sistem manajemen kasus perundungan berbasis
digital dengan fokus:

-   Pelaporan aman
-   Perlindungan identitas
-   Penanganan terstruktur
-   Monitoring SLA
-   Eskalasi otomatis
-   Dokumentasi lengkap
-   Analitik pencegahan

Sistem siap dikembangkan menuju tahap:

**System Design → Database Final → UI/UX Design → Coding MVP**
