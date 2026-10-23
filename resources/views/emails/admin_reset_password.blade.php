@extends('emails.layout', ['title' => 'Reset Kata Sandi Akun - Sahabat Sekolah'])

@section('content')
    <h2 style="font-size: 18px; color: #12386a; margin: 0 0 10px; font-weight: 800;">
        Halo, {{ $recipientName }}
    </h2>
    <p style="font-size: 13px; color: #526b84; line-height: 1.6; margin: 0 0 18px;">
        Kata sandi untuk akun platform <strong>Sahabat Sekolah</strong> Anda telah diatur ulang (reset) oleh Administrator. Berikut adalah rincian informasi akun dan kata sandi baru Anda:
    </p>

    <div class="info-card">
        <table width="100%" cellpadding="0" cellspacing="0" role="presentation">
            <tr class="info-row">
                <td class="info-label">Nama Pengguna</td>
                <td class="info-value">{{ $userData['name'] }}</td>
            </tr>
            <tr class="info-row">
                <td class="info-label">Alamat Email</td>
                <td class="info-value">
                    <span style="font-family: monospace; font-size: 13px; color: #1e3a58;">
                        {{ $userData['email'] }}
                    </span>
                </td>
            </tr>
            <tr class="info-row">
                <td class="info-label">Peran (Role)</td>
                <td class="info-value">
                    @php
                        $roleLabels = [
                            'ADMIN' => 'Administrator',
                            'PRINCIPAL' => 'Kepala Sekolah',
                            'COUNSELOR' => 'Guru BK',
                        ];
                        $roleBadgeClasses = [
                            'ADMIN' => 'badge-info',
                            'PRINCIPAL' => 'badge-warning',
                            'COUNSELOR' => 'badge-success',
                        ];
                        $userRole = strtoupper($userData['role'] ?? '');
                    @endphp
                    <span class="badge {{ $roleBadgeClasses[$userRole] ?? 'badge-info' }}">
                        {{ $roleLabels[$userRole] ?? $userRole }}
                    </span>
                </td>
            </tr>
            <tr class="info-row">
                <td class="info-label">Sekolah / Instansi</td>
                <td class="info-value">
                    {{ !empty($userData['school_name']) && $userData['school_name'] !== '-' ? $userData['school_name'] : 'Dinas / Pusat Kota Bukittinggi' }}
                </td>
            </tr>
        </table>
    </div>

    {{-- Highlighted Password Card --}}
    <div style="background: linear-gradient(135deg, #0f274a 0%, #153e75 100%); border-radius: 10px; padding: 22px 24px; margin: 24px 0; text-align: center; color: #ffffff; box-shadow: 0 4px 14px rgba(15, 39, 74, 0.12);">
        <p style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.12em; color: #93c5fd; margin: 0 0 8px; font-weight: 700;">
            KATA SANDI BARU SEMENTARA
        </p>
        <div style="display: inline-block; background: rgba(255, 255, 255, 0.12); border: 1px dashed rgba(255, 255, 255, 0.35); border-radius: 8px; padding: 10px 24px; margin: 6px 0;">
            <span style="font-family: 'SFMono-Regular', Consolas, 'Liberation Mono', Menlo, Courier, monospace; font-size: 22px; font-weight: 800; letter-spacing: 4px; color: #ffffff;">
                {{ $userData['password'] }}
            </span>
        </div>
        <p style="font-size: 11px; color: #bfdbfe; margin: 10px 0 0; line-height: 1.4;">
            Kata sandi acak ini telah diaktifkan untuk akun Anda.
        </p>
    </div>

    <div style="text-align: center; margin: 28px 0 20px;">
        <a href="{{ $userData['login_url'] }}" class="btn-primary" style="font-size: 14px; padding: 13px 32px; letter-spacing: 0.02em;">
            Masuk ke Sahabat Sekolah &rarr;
        </a>
    </div>

    <div class="alert-box alert-warning">
        <strong>🔒 Perhatian Keamanan Akun:</strong><br>
        Demi menjaga keamanan akun dan kerahasiaan data siswa Kota Bukittinggi, <strong>segera ganti kata sandi ini</strong> setelah berhasil masuk ke sistem melalui fitur Ubah Kata Sandi di pojok kanan atas portal.
    </div>

    <p style="font-size: 11px; color: #8fa0b3; line-height: 1.5; margin: 22px 0 0; word-break: break-all;">
        Jika tombol di atas tidak dapat diklik, salin tautan berikut ke peramban (browser) Anda:<br>
        <a href="{{ $userData['login_url'] }}" style="color: #1878e3; text-decoration: underline;">{{ $userData['login_url'] }}</a>
    </p>
@endsection
