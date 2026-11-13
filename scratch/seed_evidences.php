<?php

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

$c1 = DB::table('cases')->where('case_number', 'SS-2026-000201')->first();
$c2 = DB::table('cases')->where('case_number', 'SS-2026-000202')->first();

if ($c1) {
    $evPath1 = 'case-documents/SS-2026-000201/Bukti_Tangkapan_Layar_Chat_Ejekan.png';
    Storage::disk('local')->put($evPath1, 'DUMMY BUKTI TANGKAPAN LAYAR CHAT EJEKAN VERBAL');
    try {
        Storage::disk('s3-private')->put($evPath1, 'DUMMY BUKTI TANGKAPAN LAYAR CHAT EJEKAN VERBAL');
    } catch (Throwable $e) {
    }

    DB::table('case_evidences')->updateOrInsert(
        ['case_id' => $c1->id, 'file_name' => 'Bukti_Tangkapan_Layar_Chat_Ejekan.png'],
        [
            'file_path' => $evPath1,
            'mime_type' => 'image/png',
            'file_size' => 345600,
            'uploaded_by' => 'M. Fauzan (Korban)',
            'created_at' => now()->subDays(4),
            'updated_at' => now()->subDays(4),
        ]
    );
    echo "Bukti Kasus 1 tersimpan.\n";
}

if ($c2) {
    $evPath2A = 'case-documents/SS-2026-000202/Foto_Visum_Luka_Lebam_Korban.jpg';
    $evPath2B = 'case-documents/SS-2026-000202/Rekaman_CCTV_Kejadian_Gerbang.mp4';
    $evPath2C = 'case-documents/SS-2026-000202/Surat_Permohonan_Eskalasi_Kasus_PPKSP.pdf';

    Storage::disk('local')->put($evPath2A, 'DUMMY FOTO VISUM LUKA');
    Storage::disk('local')->put($evPath2B, 'DUMMY REKAMAN CCTV GERBANG');
    Storage::disk('local')->put($evPath2C, 'DUMMY SURAT PERMOHONAN ESKALASI KEPSEK');

    try {
        Storage::disk('s3-private')->put($evPath2A, 'DUMMY FOTO VISUM LUKA');
        Storage::disk('s3-private')->put($evPath2B, 'DUMMY REKAMAN CCTV GERBANG');
        Storage::disk('s3-private')->put($evPath2C, 'DUMMY SURAT PERMOHONAN ESKALASI KEPSEK');
    } catch (Throwable $e) {
    }

    DB::table('case_evidences')->updateOrInsert(
        ['case_id' => $c2->id, 'file_name' => 'Foto_Visum_Luka_Lebam_Korban.jpg'],
        [
            'file_path' => $evPath2A,
            'mime_type' => 'image/jpeg',
            'file_size' => 1245000,
            'uploaded_by' => 'Bu Ratna Sari (Guru BK)',
            'created_at' => now()->subHours(16),
            'updated_at' => now()->subHours(16),
        ]
    );

    DB::table('case_evidences')->updateOrInsert(
        ['case_id' => $c2->id, 'file_name' => 'Rekaman_CCTV_Kejadian_Gerbang.mp4'],
        [
            'file_path' => $evPath2B,
            'mime_type' => 'video/mp4',
            'file_size' => 8420000,
            'uploaded_by' => 'Satpam Sekolah',
            'created_at' => now()->subHours(15),
            'updated_at' => now()->subHours(15),
        ]
    );

    DB::table('case_evidences')->updateOrInsert(
        ['case_id' => $c2->id, 'file_name' => 'Surat_Permohonan_Eskalasi_Kasus_PPKSP.pdf'],
        [
            'file_path' => $evPath2C,
            'mime_type' => 'application/pdf',
            'file_size' => 520000,
            'uploaded_by' => 'Kepala Sekolah SMPN 2 Bukittinggi',
            'created_at' => now()->subHours(14),
            'updated_at' => now()->subHours(14),
        ]
    );

    echo "Bukti Kasus 2 tersimpan.\n";
}

echo "SEMUA_BUKTI_BERHASIL_DITAMBAHKAN\n";
