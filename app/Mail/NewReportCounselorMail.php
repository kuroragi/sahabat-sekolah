<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewReportCounselorMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param  array<string, mixed>  $reportData
     */
    public function __construct(
        public array $reportData,
        public string $recipientName = 'Guru BK'
    ) {}

    public function envelope(): Envelope
    {
        $caseNumber = $this->reportData['case_number'] ?? 'Kasus';
        $schoolName = $this->reportData['school_name'] ?? 'Sekolah';

        return new Envelope(
            subject: "[Sahabat Sekolah] Laporan Baru Masuk: {$caseNumber} - {$schoolName}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.new_report_counselor',
        );
    }
}
