<?php

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';

$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

$user = DB::table('users')->where('email', 'admin@sahabat.test')->first();

$sessionData = [
    'user_id' => $user->id,
    'user_name' => $user->name,
    'user_role' => $user->role,
    'school_id' => $user->school_id,
];

$routesToTest = [
    'Admin Index' => '/admin',
    'Admin Users' => '/admin/users',
    'Admin Schools' => '/admin/schools',
    'Admin Master Data' => '/admin/master-data',
];

foreach ($routesToTest as $name => $uri) {
    $request = Request::create($uri, 'GET');
    $request->setLaravelSession($app['session']->driver());
    $request->session()->put($sessionData);

    $response = $app->handle($request);
    $status = $response->getStatusCode();
    echo sprintf("[%s] %s -> Status %d\n", $status === 200 ? 'OK' : 'FAIL', $name, $status);
}
