<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Mobile dashboard overview statistics for Counselor / Principal.
     */
    public function overview(Request $request): JsonResponse
    {
        $user = $request->attributes->get('api_user');
        $schoolNpsn = $user->school_npsn;

        $casesQuery = DB::table('cases')->where('school_npsn', $schoolNpsn);

        $totalCases = (clone $casesQuery)->count();
        $pendingResponse = (clone $casesQuery)->where('status', 'PENDING_RESPONSE')->count();
        $underVerification = (clone $casesQuery)->where('status', 'UNDER_VERIFICATION')->count();
        $inHandling = (clone $casesQuery)->where('status', 'IN_HANDLING')->count();
        $resolved = (clone $casesQuery)->where('status', 'RESOLVED')->count();
        $closed = (clone $casesQuery)->where('status', 'CLOSED')->count();

        // SLA warnings and overdue counts
        $overdueCount = DB::table('cases')
            ->join('case_slas', 'cases.id', '=', 'case_slas.case_id')
            ->where('cases.school_npsn', $schoolNpsn)
            ->where(function ($q) {
                $q->where('case_slas.status', 'OVERDUE')
                    ->orWhere(function ($sub) {
                        $sub->where('cases.status', 'PENDING_RESPONSE')
                            ->where('case_slas.response_deadline', '<', now());
                    });
            })
            ->count();

        $warningCount = DB::table('cases')
            ->join('case_slas', 'cases.id', '=', 'case_slas.case_id')
            ->where('cases.school_npsn', $schoolNpsn)
            ->where('cases.status', 'PENDING_RESPONSE')
            ->where('case_slas.status', '!=', 'OVERDUE')
            ->whereBetween('case_slas.response_deadline', [now(), now()->addHours(6)])
            ->count();

        // Category breakdown
        $categories = (clone $casesQuery)
            ->select('category', DB::raw('count(*) as count'))
            ->groupBy('category')
            ->orderByDesc('count')
            ->get();

        // Recent 5 cases
        $recentCases = DB::table('cases')
            ->leftJoin('case_slas', 'cases.id', '=', 'case_slas.case_id')
            ->where('cases.school_npsn', $schoolNpsn)
            ->select('cases.id', 'cases.case_number', 'cases.category', 'cases.risk_level', 'cases.status', 'cases.created_at', 'case_slas.status as sla_status', 'case_slas.response_deadline')
            ->orderByDesc('cases.created_at')
            ->limit(5)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'counts' => [
                    'total' => $totalCases,
                    'pending_response' => $pendingResponse,
                    'under_verification' => $underVerification,
                    'in_handling' => $inHandling,
                    'resolved' => $resolved,
                    'closed' => $closed,
                    'active_cases' => $pendingResponse + $underVerification + $inHandling,
                ],
                'sla' => [
                    'overdue' => $overdueCount,
                    'warning' => $warningCount,
                ],
                'categories' => $categories,
                'recent_cases' => $recentCases,
            ],
        ]);
    }

    /**
     * Detailed statistics for mobile charts and reports.
     */
    public function statistics(Request $request): JsonResponse
    {
        $user = $request->attributes->get('api_user');
        $schoolNpsn = $user->school_npsn;

        // Risk distribution
        $riskDistribution = DB::table('cases')
            ->where('school_npsn', $schoolNpsn)
            ->select('risk_level', DB::raw('count(*) as count'))
            ->groupBy('risk_level')
            ->get()
            ->pluck('count', 'risk_level');

        // Status distribution
        $statusDistribution = DB::table('cases')
            ->where('school_npsn', $schoolNpsn)
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get()
            ->pluck('count', 'status');

        // Monthly trend (last 6 months)
        $sixMonthsAgo = Carbon::now()->subMonths(5)->startOfMonth();
        $monthlyCases = DB::table('cases')
            ->where('school_npsn', $schoolNpsn)
            ->where('created_at', '>=', $sixMonthsAgo)
            ->select(DB::raw("to_char(created_at, 'YYYY-MM') as month, count(*) as count"))
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // SLA compliance rate
        $totalSlaCases = DB::table('cases')
            ->join('case_slas', 'cases.id', '=', 'case_slas.case_id')
            ->where('cases.school_npsn', $schoolNpsn)
            ->count();

        $onTimeCount = DB::table('cases')
            ->join('case_slas', 'cases.id', '=', 'case_slas.case_id')
            ->where('cases.school_npsn', $schoolNpsn)
            ->where('case_slas.status', '!=', 'OVERDUE')
            ->count();

        $complianceRate = $totalSlaCases > 0 ? round(($onTimeCount / $totalSlaCases) * 100, 1) : 100.0;

        return response()->json([
            'success' => true,
            'data' => [
                'risk_distribution' => [
                    'LOW' => $riskDistribution['LOW'] ?? 0,
                    'MEDIUM' => $riskDistribution['MEDIUM'] ?? 0,
                    'HIGH' => $riskDistribution['HIGH'] ?? 0,
                    'CRITICAL' => $riskDistribution['CRITICAL'] ?? 0,
                ],
                'status_distribution' => $statusDistribution,
                'monthly_trend' => $monthlyCases,
                'sla_compliance_rate' => $complianceRate,
            ],
        ]);
    }

    /**
     * Dedicated monitoring summary for School Principal.
     */
    public function principalMonitoring(Request $request): JsonResponse
    {
        $user = $request->attributes->get('api_user');
        $schoolNpsn = $user->school_npsn;

        $casesQuery = DB::table('cases')->where('school_npsn', $schoolNpsn);

        $totalCases = (clone $casesQuery)->count();
        $activeCases = (clone $casesQuery)->whereNotIn('status', ['RESOLVED', 'CLOSED'])->count();
        $resolvedCases = (clone $casesQuery)->whereIn('status', ['RESOLVED', 'CLOSED'])->count();
        $highRiskCases = (clone $casesQuery)->whereIn('risk_level', ['HIGH', 'CRITICAL'])->count();

        $escalatedCount = DB::table('cases')
            ->join('case_escalations', 'cases.id', '=', 'case_escalations.case_id')
            ->where('cases.school_npsn', $schoolNpsn)
            ->whereNotIn('cases.status', ['RESOLVED', 'CLOSED'])
            ->distinct('cases.id')
            ->count('cases.id');

        $overdueCount = DB::table('cases')
            ->join('case_slas', 'cases.id', '=', 'case_slas.case_id')
            ->where('cases.school_npsn', $schoolNpsn)
            ->where(function ($q) {
                $q->where('case_slas.status', 'OVERDUE')
                    ->orWhere(function ($sub) {
                        $sub->where('cases.status', 'PENDING_RESPONSE')
                            ->where('case_slas.response_deadline', '<', now());
                    });
            })
            ->count();

        $warningCount = DB::table('cases')
            ->join('case_slas', 'cases.id', '=', 'case_slas.case_id')
            ->where('cases.school_npsn', $schoolNpsn)
            ->where('cases.status', 'PENDING_RESPONSE')
            ->where('case_slas.status', '!=', 'OVERDUE')
            ->whereBetween('case_slas.response_deadline', [now(), now()->addHours(6)])
            ->count();

        $workload = DB::table('cases')
            ->where('school_npsn', $schoolNpsn)
            ->whereNotIn('status', ['RESOLVED', 'CLOSED'])
            ->select('assigned_to', DB::raw('count(*) as active_cases'))
            ->groupBy('assigned_to')
            ->orderByDesc('active_cases')
            ->get()
            ->map(fn ($row) => [
                'counselor_name' => $row->assigned_to ?: 'Belum Ditugaskan',
                'active_cases' => (int) $row->active_cases,
            ]);

        $recentEscalations = DB::table('case_escalations')
            ->join('cases', 'case_escalations.case_id', '=', 'cases.id')
            ->where('cases.school_npsn', $schoolNpsn)
            ->select(
                'cases.case_number',
                'cases.category',
                'cases.risk_level',
                'cases.status',
                'case_escalations.escalation_type',
                'case_escalations.reason',
                'case_escalations.escalated_by',
                'case_escalations.created_at'
            )
            ->orderByDesc('case_escalations.created_at')
            ->limit(10)
            ->get();

        $slaAlerts = DB::table('cases')
            ->join('case_slas', 'cases.id', '=', 'case_slas.case_id')
            ->where('cases.school_npsn', $schoolNpsn)
            ->where(function ($q) {
                $q->where('case_slas.status', 'OVERDUE')
                    ->orWhere(function ($sub) {
                        $sub->where('cases.status', 'PENDING_RESPONSE')
                            ->where('case_slas.response_deadline', '<=', now()->addHours(6));
                    });
            })
            ->select(
                'cases.case_number',
                'cases.category',
                'cases.risk_level',
                'cases.status',
                'cases.assigned_to',
                'case_slas.status as sla_status',
                'case_slas.response_deadline'
            )
            ->orderByDesc('case_slas.response_deadline')
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'overview' => [
                    'total_cases' => $totalCases,
                    'active_cases' => $activeCases,
                    'resolved_cases' => $resolvedCases,
                    'high_risk_cases' => $highRiskCases,
                    'escalated_cases' => $escalatedCount,
                    'sla_overdue_cases' => $overdueCount,
                    'sla_warning_cases' => $warningCount,
                ],
                'counselor_workload' => $workload,
                'recent_escalations' => $recentEscalations,
                'sla_alerts' => $slaAlerts,
            ],
        ]);
    }
}
