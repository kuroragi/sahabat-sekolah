<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class NotificationController extends Controller
{
    public function index()
    {
        return view('notifications.index', [
            'notifications' => DB::table('notifications')->orderByDesc('created_at')->get(),
        ]);
    }

    public function read(int $id)
    {
        DB::table('notifications')->where('id', $id)->update([
            'read_at' => now(),
            'updated_at' => now(),
        ]);

        return back();
    }
}
