<?php

namespace App\Http\Controllers;

use App\Helpers\AConnect;
use Illuminate\Support\Facades\DB;

class PageController extends Controller
{
    public function landing()
    {
        return view('landing');
    }

    public function dashboard()
    {
        $schoolsMap = (new AConnect)->getSekolahMap();
        $cases = DB::table('cases')
            ->join('reports', 'cases.report_id', '=', 'reports.id')
            ->leftJoin('case_slas', 'cases.id', '=', 'case_slas.case_id')
            ->select('cases.*', 'reports.reporter_role', 'reports.description', 'case_slas.status as sla_status')
            ->where('cases.school_npsn', session('school_npsn'))
            ->orderByDesc('cases.created_at')
            ->get()
            ->map(function ($case) use ($schoolsMap) {
                $case->school_name = $schoolsMap[$case->school_npsn] ?? '-';

                return $case;
            });

        return view('dashboard', [
            'cases' => $cases,
            'stats' => [
                'reports' => DB::table('reports')->count(),
                'handling' => DB::table('cases')->where('status', 'IN_HANDLING')->count(),
                'highRisk' => DB::table('cases')->whereIn('risk_level', ['HIGH', 'CRITICAL'])->count(),
                'overdue' => DB::table('case_slas')->where('status', 'OVERDUE')->count(),
            ],
        ]);
    }

    public function principal()
    {
        $schools = (new AConnect)->getSekolahList();
        $schoolNpsn = session('school_npsn');
        $selectedSchool = $schools->firstWhere('npsn', $schoolNpsn);

        $cases = DB::table('cases')
            ->join('case_slas', 'cases.id', '=', 'case_slas.case_id')
            ->where('cases.school_npsn', $schoolNpsn)
            ->select('cases.*', 'case_slas.status as sla_status')
            ->orderByDesc('cases.updated_at')
            ->get();

        return view('principal.index', [
            'schools' => $schools,
            'selectedSchool' => $selectedSchool,
            'cases' => $cases,
            'stats' => [
                'total' => $cases->count(),
                'active' => $cases->whereNotIn('status', ['CLOSED', 'RESOLVED'])->count(),
                'highRisk' => $cases->whereIn('risk_level', ['HIGH', 'CRITICAL'])->count(),
                'overdue' => $cases->where('sla_status', 'OVERDUE')->count(),
            ],
        ]);
    }
}
