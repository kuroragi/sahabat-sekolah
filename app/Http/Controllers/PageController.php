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
            ->select('cases.*', 'reports.reporter_role', 'reports.description', 'case_slas.status as sla_status', 'case_slas.resolution_status')
            ->where('cases.school_npsn', session('school_npsn'))
            ->orderByDesc('cases.created_at')
            ->get()
            ->map(function ($case) use ($schoolsMap) {
                $case->school_name = $schoolsMap[$case->school_npsn] ?? '-';

                return $case;
            });

        $slaStats = [
            'CRITICAL' => ['on_time' => 0, 'overdue' => 0],
            'HIGH' =>     ['on_time' => 0, 'overdue' => 0],
            'MEDIUM' =>   ['on_time' => 0, 'overdue' => 0],
            'LOW' =>      ['on_time' => 0, 'overdue' => 0],
        ];

        foreach ($cases as $case) {
            $risk = $case->risk_level;
            if (isset($slaStats[$risk])) {
                // If the response is overdue OR the resolution is overdue, count as overdue
                if ($case->sla_status === 'OVERDUE' || $case->resolution_status === 'OVERDUE') {
                    $slaStats[$risk]['overdue']++;
                } else {
                    $slaStats[$risk]['on_time']++;
                }
            }
        }

        $schoolNpsn = session('school_npsn');

        // Dynamic stats calculations
        $thisMonthStart = now()->startOfMonth();
        $lastMonthStart = now()->subMonth()->startOfMonth();
        $lastMonthEnd = now()->subMonth()->endOfMonth();

        $reportsThisMonth = DB::table('reports')->where('school_npsn', $schoolNpsn)->where('created_at', '>=', $thisMonthStart)->count();
        $reportsLastMonth = DB::table('reports')->where('school_npsn', $schoolNpsn)->whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])->count();
        $reportsTrend = $reportsLastMonth > 0 ? round((($reportsThisMonth - $reportsLastMonth) / $reportsLastMonth) * 100) : ($reportsThisMonth > 0 ? 100 : 0);
        $reportsTrendSign = $reportsTrend >= 0 ? '↑' : '↓';

        $handlingThisMonth = DB::table('cases')->where('school_npsn', $schoolNpsn)->where('status', 'IN_HANDLING')->where('created_at', '>=', $thisMonthStart)->count();
        $handlingLastMonth = DB::table('cases')->where('school_npsn', $schoolNpsn)->where('status', 'IN_HANDLING')->whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])->count();
        $handlingTrend = $handlingLastMonth > 0 ? round((($handlingThisMonth - $handlingLastMonth) / $handlingLastMonth) * 100) : ($handlingThisMonth > 0 ? 100 : 0);
        $handlingTrendSign = $handlingTrend >= 0 ? '↑' : '↓';

        $highRiskNewThisWeek = DB::table('cases')->where('school_npsn', $schoolNpsn)->whereIn('risk_level', ['HIGH', 'CRITICAL'])->where('created_at', '>=', now()->startOfWeek())->count();

        // Trend Data (Last 6 months)
        $trendMonths = collect();
        $trendData = collect();
        for ($i = 5; $i >= 0; $i--) {
            $monthStart = now()->subMonths($i)->startOfMonth();
            $monthEnd = now()->subMonths($i)->endOfMonth();
            $trendMonths->push($monthStart->translatedFormat('M'));
            $count = DB::table('reports')->where('school_npsn', $schoolNpsn)->whereBetween('created_at', [$monthStart, $monthEnd])->count();
            $trendData->push($count);
        }
        $maxTrend = max(20, $trendData->max() + 5);

        // Category Data
        $categories = DB::table('reports')
            ->where('school_npsn', $schoolNpsn)
            ->select('category', DB::raw('count(*) as total'))
            ->groupBy('category')
            ->get();
            
        $totalReportsCount = $categories->sum('total') ?: 1;
        $categoryData = $categories->map(function ($cat) use ($totalReportsCount) {
            $cat->percentage = round(($cat->total / $totalReportsCount) * 100);
            return $cat;
        })->sortByDesc('total')->values();

        // Colors for donut
        $colors = ['#35aee6', '#277de2', '#ffc85a', '#f47e78', '#e9a0ba', '#a0aec0'];
        $donutGradient = [];
        $currentPercent = 0;
        foreach ($categoryData as $idx => $cat) {
            $color = $colors[$idx % count($colors)];
            $cat->color = $color;
            $nextPercent = $currentPercent + $cat->percentage;
            $donutGradient[] = "{$color} {$currentPercent}% {$nextPercent}%";
            $currentPercent = $nextPercent;
        }
        $donutStyle = 'conic-gradient(' . implode(',', $donutGradient) . ')';
        if (empty($donutGradient)) {
            $donutStyle = 'conic-gradient(#e6eef7 0% 100%)';
        }

        return view('dashboard', [
            'cases' => $cases,
            'slaStats' => $slaStats,
            'stats' => [
                'reports' => DB::table('reports')->count(),
                'handling' => DB::table('cases')->where('status', 'IN_HANDLING')->count(),
                'highRisk' => DB::table('cases')->whereIn('risk_level', ['HIGH', 'CRITICAL'])->count(),
                'overdue' => DB::table('case_slas')->where('status', 'OVERDUE')->orWhere('resolution_status', 'OVERDUE')->count(),
                
                'reportsTrend' => $reportsTrend,
                'reportsTrendSign' => $reportsTrendSign,
                'handlingTrend' => $handlingTrend,
                'handlingTrendSign' => $handlingTrendSign,
                'highRiskNewThisWeek' => $highRiskNewThisWeek,
                
                'trendMonths' => $trendMonths,
                'trendData' => $trendData,
                'maxTrend' => $maxTrend,
                'categoryData' => $categoryData,
                'donutStyle' => $donutStyle,
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
