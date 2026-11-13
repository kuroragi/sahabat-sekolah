<?php

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

$ev1 = DB::table('case_evidences')
    ->join('cases', 'cases.id', '=', 'case_evidences.case_id')
    ->where('cases.case_number', 'SS-2026-000201')
    ->select('case_evidences.file_name', 'case_evidences.file_size', 'case_evidences.uploaded_by')
    ->get();

$ev2 = DB::table('case_evidences')
    ->join('cases', 'cases.id', '=', 'case_evidences.case_id')
    ->where('cases.case_number', 'SS-2026-000202')
    ->select('case_evidences.file_name', 'case_evidences.file_size', 'case_evidences.uploaded_by')
    ->get();

$res1 = DB::table('case_resolution_documents')
    ->join('cases', 'cases.id', '=', 'case_resolution_documents.case_id')
    ->where('cases.case_number', 'SS-2026-000201')
    ->select('case_resolution_documents.file_name', 'case_resolution_documents.verification_status')
    ->get();

echo "--- KASUS 1 (SS-2026-000201) ---\n";
echo "Bukti Kasus:\n";
foreach ($ev1 as $e) {
    echo "- {$e->file_name} (".round($e->file_size / 1024, 1)." KB) oleh {$e->uploaded_by}\n";
}
echo "Dokumen Resolusi:\n";
foreach ($res1 as $r) {
    echo "- {$r->file_name} [Status: {$r->verification_status}]\n";
}

echo "\n--- KASUS 2 (SS-2026-000202) ---\n";
echo "Bukti Kasus:\n";
foreach ($ev2 as $e) {
    echo "- {$e->file_name} (".round($e->file_size / 1024, 1)." KB) oleh {$e->uploaded_by}\n";
}
