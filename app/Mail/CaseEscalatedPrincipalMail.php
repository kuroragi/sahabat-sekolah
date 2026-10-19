<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CaseEscalatedPrincipalMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param  array<string, mixed>  $caseData
     * @param  array<string, mixed>  $escalationData
     */
    public function __construct(
        public array $caseData,
        public array $escalationData,
        public string $recipientName = 'Kepala Sekolah'
    ) {}

    public function envelope(): Envelope
    {
        $caseNumber = $this->caseData['case_number'] ?? 'Kasus';
        $schoolName = $this->caseData['school_name'] ?? 'Sekolah';

        return new Envelope(
            subject: "[Sahabat Sekolah] Eskalasi Kasus oleh Guru BK: {$caseNumber} - {$schoolName}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.case_escalated_principal',
        );
    }
}
