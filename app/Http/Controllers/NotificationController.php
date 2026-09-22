<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = DB::table('notifications')
            ->leftJoin('cases', 'notifications.case_number', '=', 'cases.case_number')
            ->where(function ($q) {
                $q->where('cases.school_npsn', session('school_npsn'))
                  ->orWhereNull('notifications.case_number');
            })
            ->select('notifications.*')
            ->orderByDesc('notifications.created_at')
            ->get();

        return view('notifications.index', [
            'notifications' => $notifications,
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
