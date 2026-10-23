<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class AdminResetPasswordMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Jumlah percobaan maksimal pengiriman email jika terjadi kendala jaringan/SMTP.
     */
    public int $tries = 5;

    /**
     * Waktu tunggu (detik) sebelum pengiriman ulang jika terjadi kegagalan sementara.
     */
    public int $backoff = 60;

    /**
     * @param  array{
     *     name: string,
     *     email: string,
     *     role: string,
     *     school_name: ?string,
     *     password: string,
     *     login_url: string
     * }  $userData
     */
    public function __construct(
        public array $userData,
        public string $recipientName = 'Pengguna'
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[Sahabat Sekolah] Kata Sandi Akun Anda Telah Direset oleh Administrator',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.admin_reset_password',
        );
    }

    /**
     * Catat log jika email gagal dikirim setelah seluruh percobaan habis.
     */
    public function failed(?Throwable $exception): void
    {
        Log::error('Pengiriman notifikasi reset password oleh admin gagal setelah mencapai batas maksimal percobaan.', [
            'recipient' => $this->recipientName,
            'email' => $this->userData['email'] ?? null,
            'error' => $exception?->getMessage(),
        ]);
    }
}
