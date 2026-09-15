<?php

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

$bkUser = DB::table('users')->where('email', 'bk@sahabat.test')->first();

echo "=== AUDIT DAN MONITORING MENU SIDEBAR GURU BK ===\n";

$menus = [
    'Dashboard' => '/dashboard',
    'Laporan Masuk' => '/reports/inbox',
    'Manajemen Kasus' => '/reports/inbox?status=IN_HANDLING',
    'Statistik' => '/statistics',
    'Notifikasi' => '/notifications',
];

foreach ($menus as $menuName => $path) {
    $request = Request::create($path, 'GET');
    $session = $app->make('session')->driver();
    $session->put([
        'user_id' => $bkUser->id,
        'user_name' => $bkUser->name,
        'user_role' => $bkUser->role,
        'school_id' => $bkUser->school_id,
    ]);
    $request->setLaravelSession($session);

    $response = $kernel->handle($request);
    $status = $response->getStatusCode();
    $content = $response->getContent();

    $hasDashboard = str_contains($content, 'Dashboard');
    $hasLaporan = str_contains($content, 'Laporan Masuk');
    $hasKasus = str_contains($content, 'Manajemen Kasus');
    $hasStatistik = str_contains($content, 'Statistik');
    $hasNotifikasi = str_contains($content, 'Notifikasi');

    if ($status === 200 && $hasDashboard && $hasLaporan && $hasKasus && $hasStatistik && $hasNotifikasi) {
        echo "[OK] Menu {$menuName} ({$path}) -> Status 200 | 5 Menu Sidebar Utuh & Terbaca Sempurna\n";
    } else {
        echo "[FAIL] Menu {$menuName} ({$path}) -> Status {$status}\n";
    }
}

echo "\n=== AUDIT COMPLETE ===\n";
