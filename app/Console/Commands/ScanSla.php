<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ScanSla extends Command
{
    protected $signature = 'sahabat:sla-scan';

    protected $description = 'Refreshes response SLA statuses and creates overdue alerts';

    public function handle(): int
    {
        $now = now();
        $warningLimit = $now->copy()->addHours(6);
        $updated = 0;

        $slas = DB::table('case_slas')
            ->join('cases', 'case_slas.case_id', '=', 'cases.id')
            ->whereNull('case_slas.initial_response_at')
            ->where('cases.status', 'PENDING_RESPONSE')
            ->select('case_slas.*', 'cases.case_number')
            ->get();

        foreach ($slas as $sla) {
            $nextStatus = null;
            $priority = 'NORMAL';
            $title = null;
            $message = null;

            if ($sla->response_deadline <= $now && $sla->status !== 'OVERDUE') {
                $nextStatus = 'OVERDUE';
                $priority = 'URGENT';
                $title = 'SLA respons terlewati';
                $message = $sla->case_number . ' belum menerima respons awal dalam 1 × 24 jam.';
            } elseif ($sla->response_deadline <= $warningLimit && $sla->status === 'ON_TIME') {
                $nextStatus = 'WARNING';
                $priority = 'HIGH';
                $title = 'SLA mendekati batas waktu';
                $message = $sla->case_number . ' membutuhkan respons awal dalam 6 jam.';
            }

            if ($nextStatus) {
                $reminderType = $nextStatus === 'OVERDUE' ? 'OVERDUE' : 'WARNING';
                DB::table('case_slas')->where('id', $sla->id)->update([
                    'status' => $nextStatus,
                    'warning_at' => $nextStatus === 'WARNING' ? $now : $sla->warning_at,
                    'overdue_at' => $nextStatus === 'OVERDUE' ? $now : null,
                    'updated_at' => $now,
                ]);
                DB::table('case_reminders')->insertOrIgnore([
                    'case_id' => $sla->case_id, 'reminder_type' => $reminderType,
                    'scheduled_at' => $now, 'sent_at' => $now,
                    'recipient_name' => 'Bu Ratna Sari', 'status' => 'SENT',
                    'created_at' => $now, 'updated_at' => $now,
                ]);
                DB::table('notifications')->insert([
                    'recipient_name' => 'Bu Ratna Sari', 'type' => 'SLA_' . $nextStatus,
                    'title' => $title, 'message' => $message, 'priority' => $priority,
                    'case_number' => $sla->case_number, 'created_at' => $now, 'updated_at' => $now,
                ]);
                if ($nextStatus === 'OVERDUE') {
                    DB::table('case_escalations')->insert([
                        'case_id' => $sla->case_id, 'escalation_type' => 'AUTOMATIC',
                        'reason' => 'SLA respons awal terlewati.', 'escalated_by' => 'Sistem SLA',
                        'created_at' => $now, 'updated_at' => $now,
                    ]);
                    DB::table('notifications')->insert([
                        'recipient_name' => 'Kepala Sekolah', 'type' => 'SLA_ESCALATION',
                        'title' => 'SLA overdue memerlukan perhatian', 'message' => $message,
                        'priority' => 'URGENT', 'case_number' => $sla->case_number,
                        'created_at' => $now, 'updated_at' => $now,
                    ]);
                }
                $updated++;
            }
        }

        $this->info("SLA scan selesai: {$updated} status diperbarui.");

        return self::SUCCESS;
    }
}
