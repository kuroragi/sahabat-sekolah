<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

if (! function_exists('notifyCounselor')) {
function notifyCounselor(string $type, string $title, string $message, ?string $caseNumber = null, string $priority = 'NORMAL'): void
{
    DB::table('notifications')->insert([
        'recipient_name' => 'Bu Ratna Sari', 'type' => $type, 'title' => $title,
        'message' => $message, 'priority' => $priority, 'case_number' => $caseNumber,
        'created_at' => now(), 'updated_at' => now(),
    ]);
}
}

if (! function_exists('auditAction')) {
function auditAction(string $action, string $resourceType, string|int|null $resourceId = null, ?array $newValues = null, ?array $oldValues = null): void
{
    DB::table('audit_logs')->insert([
        'user_id' => session('user_id'), 'user_name' => session('user_name'), 'action' => $action,
        'resource_type' => $resourceType, 'resource_id' => $resourceId === null ? null : (string) $resourceId,
        'old_values' => $oldValues ? json_encode($oldValues) : null, 'new_values' => $newValues ? json_encode($newValues) : null,
        'ip_address' => request()->ip(), 'user_agent' => request()->userAgent(), 'created_at' => now(), 'updated_at' => now(),
    ]);
}
}

if (! function_exists('scopedCase')) {
function scopedCase(string $caseNumber): object
{
    return DB::table('cases')
        ->where('case_number', $caseNumber)
        ->where('school_id', session('school_id'))
        ->firstOrFail();
}
}

Route::get('/login', fn () => view('auth.login'))->name('login');

Route::post('/login', function () {
    $data = request()->validate(['email' => ['required', 'email'], 'password' => ['required']]);
    $user = DB::table('users')->where('email', $data['email'])->first();
    if (! $user || ! Hash::check($data['password'], $user->password)) {
        return back()->withInput()->with('error', 'Email atau password tidak sesuai.');
    }
    request()->session()->regenerate();
    request()->session()->put(['user_id' => $user->id, 'user_name' => $user->name, 'user_role' => $user->role, 'school_id' => $user->school_id]);
    $destination = match ($user->role) {
        'ADMIN' => route('admin.index'),
        'PRINCIPAL' => route('principal.index'),
        default => route('dashboard'),
    };

    return redirect($destination);
})->middleware('throttle:5,1')->name('login.store');

Route::post('/logout', function () {
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect()->route('login');
})->name('logout');

Route::get('/health/details', function () {
    try {
        DB::select('select 1');
        $database = 'ok';
    } catch (Throwable) {
        $database = 'failed';
    }
    return response()->json(['status' => $database === 'ok' && is_writable(storage_path()) ? 'ok' : 'degraded', 'checks' => ['database' => $database, 'storage' => is_writable(storage_path()) ? 'ok' : 'failed'], 'timestamp' => now()->toIso8601String()], $database === 'ok' ? 200 : 503);
})->name('health.details');

Route::get('/admin', function () {
    return view('admin.index', [
        'schools' => DB::table('schools')->orderBy('name')->get(),
        'users' => DB::table('users')->leftJoin('schools', 'users.school_id', '=', 'schools.id')->select('users.*', 'schools.name as school_name')->orderBy('users.name')->get(),
    ]);
})->middleware(['role:ADMIN', 'permission:USER_MANAGE'])->name('admin.index');

Route::post('/admin/schools', function () {
    $data = request()->validate(['name' => ['required', 'string', 'max:255'], 'education_level' => ['required', 'in:SD,SDI,MIN,SMP,MTS']]);
    DB::table('schools')->insert($data + ['created_at' => now(), 'updated_at' => now()]);
    auditAction('CREATE_SCHOOL', 'SCHOOL', null, $data);
    return back()->with('success', 'Sekolah berhasil ditambahkan.');
})->middleware(['role:ADMIN', 'permission:SCHOOL_MANAGE'])->name('admin.schools.store');

Route::post('/admin/users', function () {
    $data = request()->validate(['name' => ['required', 'string', 'max:255'], 'email' => ['required', 'email', 'unique:users,email'], 'role' => ['required', 'in:COUNSELOR,PRINCIPAL,ADMIN'], 'school_id' => ['nullable', 'integer', 'exists:schools,id']]);
    DB::table('users')->insert($data + ['password' => Hash::make('password'), 'created_at' => now(), 'updated_at' => now()]);
    auditAction('CREATE_USER', 'USER', $data['email'], ['role' => $data['role'], 'school_id' => $data['school_id']]);
    return back()->with('success', 'Pengguna berhasil dibuat. Password awal: password.');
})->middleware(['role:ADMIN', 'permission:USER_MANAGE'])->name('admin.users.store');

Route::get('/admin/users', function () {
    return view('admin.users', ['users' => DB::table('users')->leftJoin('schools', 'users.school_id', '=', 'schools.id')->select('users.*', 'schools.name as school_name')->orderBy('users.name')->get(), 'schools' => DB::table('schools')->orderBy('name')->get()]);
})->middleware(['role:ADMIN', 'permission:USER_MANAGE'])->name('admin.users');

Route::post('/admin/users/{id}', function (int $id) {
    $data = request()->validate(['name' => ['required', 'string', 'max:255'], 'role' => ['required', 'in:COUNSELOR,PRINCIPAL,ADMIN'], 'school_id' => ['nullable', 'integer', 'exists:schools,id'], 'status' => ['required', 'in:ACTIVE,INACTIVE']]);
    DB::table('users')->where('id', $id)->update($data + ['updated_at' => now()]);
    auditAction('UPDATE_USER', 'USER', $id, $data);
    return back()->with('success', 'Pengguna berhasil diperbarui.');
})->middleware(['role:ADMIN', 'permission:USER_MANAGE'])->name('admin.users.update');

Route::get('/admin/schools', function () {
    return view('admin.schools', ['schools' => DB::table('schools')->orderBy('name')->get()]);
})->middleware(['role:ADMIN', 'permission:SCHOOL_MANAGE'])->name('admin.schools');

Route::post('/admin/schools/{id}', function (int $id) {
    $data = request()->validate(['name' => ['required', 'string', 'max:255'], 'education_level' => ['required', 'in:SD,SDI,MIN,SMP,MTS'], 'status' => ['required', 'in:1,0']]);
    DB::table('schools')->where('id', $id)->update(['name' => $data['name'], 'education_level' => $data['education_level'], 'status' => (bool) $data['status'], 'updated_at' => now()]);
    auditAction('UPDATE_SCHOOL', 'SCHOOL', $id, $data);
    return back()->with('success', 'Sekolah berhasil diperbarui.');
})->middleware(['role:ADMIN', 'permission:SCHOOL_MANAGE'])->name('admin.schools.update');

Route::get('/admin/master-data', function () {
    return view('admin.master-data', [
        'categories' => DB::table('bullying_categories')->orderBy('name')->get()->map(function ($category) {
            $category->subcategories = DB::table('bullying_subcategories')->where('category_id', $category->id)->orderBy('name')->get();
            return $category;
        }),
    ]);
})->middleware(['role:ADMIN', 'permission:MASTER_DATA_MANAGE'])->name('admin.master-data');

Route::post('/admin/master-data/categories', function () {
    $data = request()->validate(['name' => ['required', 'string', 'max:255'], 'description' => ['nullable', 'string', 'max:500']]);
    DB::table('bullying_categories')->insert(['code' => strtoupper(str_replace(' ', '_', $data['name'])), 'name' => $data['name'], 'description' => $data['description'] ?? null, 'status' => true, 'created_at' => now(), 'updated_at' => now()]);
    auditAction('CREATE_CATEGORY', 'BULLYING_CATEGORY', null, $data);
    return back()->with('success', 'Kategori berhasil ditambahkan.');
})->middleware(['role:ADMIN', 'permission:MASTER_DATA_MANAGE'])->name('admin.categories.store');

Route::post('/admin/master-data/subcategories', function () {
    $data = request()->validate(['category_id' => ['required', 'integer', 'exists:bullying_categories,id'], 'name' => ['required', 'string', 'max:255'], 'default_risk_level' => ['required', 'in:LOW,MEDIUM,HIGH,CRITICAL'], 'risk_score' => ['required', 'integer', 'min:0', 'max:100']]);
    DB::table('bullying_subcategories')->insert(['category_id' => $data['category_id'], 'code' => strtoupper(str_replace(' ', '_', $data['name'])), 'name' => $data['name'], 'default_risk_level' => $data['default_risk_level'], 'risk_score' => $data['risk_score'], 'status' => true, 'created_at' => now(), 'updated_at' => now()]);
    auditAction('CREATE_SUBCATEGORY', 'BULLYING_SUBCATEGORY', null, $data);
    return back()->with('success', 'Subkategori berhasil ditambahkan.');
})->middleware(['role:ADMIN', 'permission:MASTER_DATA_MANAGE'])->name('admin.subcategories.store');

Route::get('/', function () {
    return view('landing');
})->name('landing');

Route::get('/dashboard', function () {
    $cases = DB::table('cases')
        ->join('reports', 'cases.report_id', '=', 'reports.id')
        ->join('schools', 'cases.school_id', '=', 'schools.id')
        ->leftJoin('case_slas', 'cases.id', '=', 'case_slas.case_id')
        ->select('cases.*', 'reports.reporter_role', 'reports.description', 'schools.name as school_name', 'case_slas.status as sla_status')
        ->where('cases.school_id', session('school_id'))
        ->orderByDesc('cases.created_at')
        ->get();

    return view('dashboard', [
        'cases' => $cases,
        'stats' => [
            'reports' => DB::table('reports')->count(),
            'handling' => DB::table('cases')->where('status', 'IN_HANDLING')->count(),
            'highRisk' => DB::table('cases')->whereIn('risk_level', ['HIGH', 'CRITICAL'])->count(),
            'overdue' => DB::table('case_slas')->where('status', 'OVERDUE')->count(),
        ],
    ]);
})->middleware(['role:COUNSELOR,PRINCIPAL', 'permission:REPORT_VIEW'])->name('dashboard');

Route::get('/reports/create', function () {
    return view('reports.create', [
        'schools' => DB::table('schools')->orderBy('name')->get(),
        'categories' => DB::table('bullying_categories')->where('status', true)->orderBy('name')->get()->map(function ($category) {
            $category->subcategories = DB::table('bullying_subcategories')->where('category_id', $category->id)->where('status', true)->orderBy('name')->get();
            return $category;
        }),
    ]);
})->name('reports.create');

Route::get('/reports/inbox', function () {
    $query = DB::table('cases')
        ->join('reports', 'cases.report_id', '=', 'reports.id')
        ->leftJoin('case_slas', 'cases.id', '=', 'case_slas.case_id')
        ->where('cases.school_id', session('school_id'))
        ->select('cases.*', 'reports.description', 'reports.reporter_role', 'reports.identity_mode', 'case_slas.status as sla_status');
    if ($status = request('status')) $query->where('cases.status', $status);
    if ($risk = request('risk_level')) $query->where('cases.risk_level', $risk);
    if ($sla = request('sla_status')) $query->where('case_slas.status', $sla);
    if ($search = trim((string) request('search'))) $query->where(function ($builder) use ($search) { $builder->where('cases.case_number', 'like', '%' . $search . '%')->orWhere('cases.category', 'like', '%' . $search . '%')->orWhere('reports.description', 'like', '%' . $search . '%'); });

    return view('reports.inbox', ['cases' => $query->orderByDesc('cases.updated_at')->get(), 'filters' => request()->only(['status', 'risk_level', 'sla_status', 'search'])]);
})->middleware(['role:COUNSELOR,PRINCIPAL', 'permission:REPORT_VIEW'])->name('reports.inbox');

Route::get('/statistics', function () {
    $schoolId = session('school_id');
    $base = DB::table('cases')->where('school_id', $schoolId);
    $cases = $base->get();
    $sla = DB::table('case_slas')->join('cases', 'case_slas.case_id', '=', 'cases.id')->where('cases.school_id', $schoolId)->get();
    return view('statistics.index', [
        'stats' => ['total' => $cases->count(), 'resolved' => $cases->whereIn('status', ['RESOLVED', 'CLOSED'])->count(), 'active' => $cases->whereNotIn('status', ['RESOLVED', 'CLOSED'])->count(), 'highRisk' => $cases->whereIn('risk_level', ['HIGH', 'CRITICAL'])->count(), 'overdue' => $sla->where('status', 'OVERDUE')->count()],
        'categories' => $cases->groupBy('category')->map->count()->sortDesc(),
        'statuses' => $cases->groupBy('status')->map->count(),
        'risks' => $cases->groupBy('risk_level')->map->count(),
        'slas' => $sla->groupBy('status')->map->count(),
    ]);
})->middleware(['role:COUNSELOR,PRINCIPAL', 'permission:STATISTICS_VIEW'])->name('statistics.index');

Route::get('/statistics/export', function () {
    $schoolId = session('school_id');
    $rows = DB::table('cases')->join('case_slas', 'cases.id', '=', 'case_slas.case_id')->where('cases.school_id', $schoolId)->select('cases.case_number', 'cases.category', 'cases.risk_level', 'cases.status', 'case_slas.status as sla_status', 'cases.opened_at', 'cases.resolved_at', 'cases.closed_at')->orderBy('cases.opened_at')->get();
    auditAction('EXPORT_STATISTICS', 'REPORT', null, ['row_count' => $rows->count()]);
    return response()->streamDownload(function () use ($rows) { $handle = fopen('php://output', 'w'); fputcsv($handle, ['Case Number', 'Category', 'Risk Level', 'Case Status', 'SLA Status', 'Opened At', 'Resolved At', 'Closed At']); foreach ($rows as $row) fputcsv($handle, (array) $row); fclose($handle); }, 'sahabat-sekolah-statistics.csv', ['Content-Type' => 'text/csv']);
})->middleware(['role:COUNSELOR,PRINCIPAL', 'permission:STATISTICS_VIEW'])->name('statistics.export');

Route::post('/reports', function () {
    $data = request()->validate([
        'school_id' => ['required', 'integer', 'exists:schools,id'],
        'reporter_role' => ['required', 'in:VICTIM,WITNESS,CONCERNED_PERSON'],
        'identity_mode' => ['required', 'in:IDENTIFIED,CONFIDENTIAL,ANONYMOUS'],
        'reporter_name' => ['nullable', 'string', 'max:255', 'required_unless:identity_mode,ANONYMOUS'],
        'reporter_contact' => ['nullable', 'string', 'max:100'],
        'category' => ['required', 'string', 'max:255', 'exists:bullying_categories,name'],
        'subcategory' => ['nullable', 'string', 'max:255', 'exists:bullying_subcategories,name'],
        'description' => ['required', 'string', 'min:20'],
    ]);

    $caseNumber = DB::transaction(function () use ($data) {
        $number = 'SS-' . now()->format('Y') . '-' . str_pad((string) ((DB::table('reports')->max('id') ?? 0) + 1), 6, '0', STR_PAD_LEFT);
        $now = now();
        $reportData = $data;
        unset($reportData['reporter_name'], $reportData['reporter_contact']);
        $reportId = DB::table('reports')->insertGetId([
            ...$reportData, 'report_number' => $number, 'submitted_at' => $now,
            'created_at' => $now, 'updated_at' => $now,
        ]);
        $risk = in_array($data['category'], ['Cyberbullying', 'Fisik', 'Perundungan Bernuansa Seksual'], true) ? 'HIGH' : 'MEDIUM';
        $caseId = DB::table('cases')->insertGetId([
            'school_id' => $data['school_id'], 'report_id' => $reportId, 'case_number' => $number,
            'category' => $data['category'], 'risk_level' => $risk, 'status' => 'PENDING_RESPONSE',
            'opened_at' => $now, 'created_at' => $now, 'updated_at' => $now,
        ]);
        DB::table('case_slas')->insert([
            'case_id' => $caseId, 'status' => 'ON_TIME', 'response_deadline' => $now->copy()->addDay(),
            'created_at' => $now, 'updated_at' => $now,
        ]);
        if ($data['identity_mode'] !== 'ANONYMOUS') {
            DB::table('reporter_identities')->insert([
                'report_id' => $reportId,
                'full_name' => $data['reporter_name'] ?? null,
                'contact' => $data['reporter_contact'] ?? null,
                'access_level' => $data['identity_mode'] === 'IDENTIFIED' ? 'CASE_FULL' : 'CASE_RESTRICTED',
                'created_at' => $now, 'updated_at' => $now,
            ]);
        }
        notifyCounselor('NEW_REPORT', 'Laporan baru masuk', $number . ' menunggu respons awal dalam 1 × 24 jam.', $number, 'HIGH');

        $anonymousToken = null;
        if ($data['identity_mode'] === 'ANONYMOUS') {
            $anonymousToken = Str::upper(Str::random(12));
            DB::table('anonymous_report_tokens')->insert([
                'report_id' => $reportId, 'token_hash' => hash('sha256', $anonymousToken),
                'expires_at' => $now->copy()->addMonths(6), 'created_at' => $now, 'updated_at' => $now,
            ]);
        }

        return ['number' => $number, 'token' => $anonymousToken];
    });

    if ($caseNumber['token']) {
        return redirect()->route('reports.receipt')->with(['report_number' => $caseNumber['number'], 'anonymous_token' => $caseNumber['token']]);
    }

    return redirect()->route('cases.show', $caseNumber['number'])
        ->with('success', 'Laporan berhasil dibuat dan masuk ke antrean Guru BK.');
})->middleware('throttle:10,1')->name('reports.store');

Route::get('/reports/receipt', function () {
    abort_unless(session()->has('anonymous_token'), 404);
    return view('reports.receipt', ['reportNumber' => session('report_number'), 'anonymousToken' => session('anonymous_token')]);
})->name('reports.receipt');

Route::get('/anonymous-channel', function () {
    $token = request('token');
    $report = null;
    $messages = collect();
    if ($token) {
        $report = DB::table('anonymous_report_tokens')
            ->join('reports', 'anonymous_report_tokens.report_id', '=', 'reports.id')
            ->where('anonymous_report_tokens.token_hash', hash('sha256', strtoupper($token)))
            ->where('anonymous_report_tokens.status', true)
            ->where(function ($query) {
                $query->whereNull('anonymous_report_tokens.expires_at')->orWhere('anonymous_report_tokens.expires_at', '>', now());
            })
            ->select('reports.id', 'reports.report_number')
            ->first();
        if ($report) {
            DB::table('anonymous_report_tokens')->where('report_id', $report->id)->update(['last_access_at' => now(), 'updated_at' => now()]);
            $messages = DB::table('anonymous_messages')->where('report_id', $report->id)->orderBy('created_at')->get();
        }
    }
    return view('anonymous.channel', compact('token', 'report', 'messages'));
})->name('anonymous.channel');

Route::post('/anonymous-channel/message', function () {
    $data = request()->validate(['token' => ['required', 'string', 'size:12'], 'message' => ['required', 'string', 'min:5', 'max:2000']]);
    $report = DB::table('anonymous_report_tokens')->where('token_hash', hash('sha256', strtoupper($data['token'])))->where('status', true)->first();
    abort_unless($report && (!$report->expires_at || now()->lessThan($report->expires_at)), 403, 'Token anonim tidak valid atau sudah kedaluwarsa.');
    DB::table('anonymous_messages')->insert(['report_id' => $report->report_id, 'sender_type' => 'REPORTER', 'message' => $data['message'], 'created_at' => now(), 'updated_at' => now()]);
    notifyCounselor('ANONYMOUS_MESSAGE', 'Pesan anonim baru', 'Pelapor mengirim informasi tambahan pada laporan.', null, 'HIGH');
    return redirect()->route('anonymous.channel', ['token' => strtoupper($data['token'])])->with('success', 'Pesan anonim berhasil dikirim.');
})->middleware('throttle:5,1')->name('anonymous.message');

Route::get('/notifications', function () {
    return view('notifications.index', [
        'notifications' => DB::table('notifications')->orderByDesc('created_at')->get(),
    ]);
})->middleware(['role:COUNSELOR,PRINCIPAL', 'permission:NOTIFICATION_VIEW'])->name('notifications.index');

Route::get('/principal', function () {
    $schools = DB::table('schools')->orderBy('name')->get();
    $schoolId = session('school_id');
    $selectedSchool = $schools->firstWhere('id', $schoolId);
    $cases = DB::table('cases')
        ->join('case_slas', 'cases.id', '=', 'case_slas.case_id')
        ->where('cases.school_id', $schoolId)
        ->select('cases.*', 'case_slas.status as sla_status')
        ->orderByDesc('cases.updated_at')
        ->get();

    return view('principal.index', [
        'schools' => $schools, 'selectedSchool' => $selectedSchool, 'cases' => $cases,
        'stats' => [
            'total' => $cases->count(),
            'active' => $cases->whereNotIn('status', ['CLOSED', 'RESOLVED'])->count(),
            'highRisk' => $cases->whereIn('risk_level', ['HIGH', 'CRITICAL'])->count(),
            'overdue' => $cases->where('sla_status', 'OVERDUE')->count(),
        ],
    ]);
})->middleware(['role:PRINCIPAL', 'permission:MONITORING_VIEW'])->name('principal.index');

Route::post('/notifications/{id}/read', function (int $id) {
    DB::table('notifications')->where('id', $id)->update(['read_at' => now(), 'updated_at' => now()]);
    return back();
})->middleware(['role:COUNSELOR,PRINCIPAL', 'permission:NOTIFICATION_VIEW'])->name('notifications.read');

Route::get('/cases/{caseNumber}', function (string $caseNumber) {
    $case = DB::table('cases')
        ->join('reports', 'cases.report_id', '=', 'reports.id')
        ->join('schools', 'cases.school_id', '=', 'schools.id')
        ->leftJoin('case_slas', 'cases.id', '=', 'case_slas.case_id')
        ->where('cases.case_number', $caseNumber)
        ->where('cases.school_id', session('school_id'))
        ->leftJoin('reporter_identities', 'reports.id', '=', 'reporter_identities.report_id')
        ->select('cases.*', 'reports.reporter_role', 'reports.identity_mode', 'reports.description', 'reports.submitted_at', 'reporter_identities.full_name as reporter_name', 'reporter_identities.contact as reporter_contact', 'reporter_identities.access_level as reporter_access_level', 'schools.name as school_name', 'case_slas.status as sla_status', 'case_slas.response_deadline')
        ->firstOrFail();
    DB::table('case_access_logs')->insert([
        'user_id' => session('user_id'), 'user_name' => session('user_name'), 'case_id' => $case->id,
        'action' => 'VIEW_CASE', 'access_level' => session('user_role') === 'PRINCIPAL' ? 'CASE_SUMMARY' : 'CASE_FULL',
        'reason' => 'Case detail dibuka', 'ip_address' => request()->ip(), 'created_at' => now(), 'updated_at' => now(),
    ]);
    auditAction('VIEW_CASE', 'CASE', $case->case_number);

    $history = DB::table('case_status_histories')
        ->where('case_id', $case->id)
        ->orderBy('created_at')
        ->get();
    $actions = DB::table('case_actions')
        ->where('case_id', $case->id)
        ->orderByDesc('created_at')
        ->get();
    $evidences = DB::table('case_evidences')->where('case_id', $case->id)->latest()->get();
    $resolutionDocuments = DB::table('case_resolution_documents')->where('case_id', $case->id)->latest()->get();
    $riskAssessment = DB::table('case_risk_assessments')->where('case_id', $case->id)->latest()->first();
    $riskHistory = DB::table('case_risk_histories')->where('case_id', $case->id)->latest()->get();
    $notes = DB::table('case_notes')->where('case_id', $case->id)->latest()->get();
    $escalations = DB::table('case_escalations')->where('case_id', $case->id)->latest()->get();
    $participants = DB::table('case_participants')->where('case_id', $case->id)->where('status', 'ACTIVE')->orderBy('participant_type')->get();
    $parentInvolvements = DB::table('case_parent_involvements')->join('parents', 'case_parent_involvements.parent_id', '=', 'parents.id')->where('case_parent_involvements.case_id', $case->id)->select('case_parent_involvements.*', 'parents.full_name as parent_name', 'parents.contact as parent_contact')->latest()->get();

    return view('cases.show', compact('case', 'history', 'actions', 'evidences', 'resolutionDocuments', 'riskAssessment', 'riskHistory', 'notes', 'escalations', 'participants', 'parentInvolvements'));
})->middleware(['role:COUNSELOR,PRINCIPAL', 'permission:CASE_VIEW'])->name('cases.show');

Route::post('/cases/{caseNumber}/parent-involvement', function (string $caseNumber) {
    $data = request()->validate([
        'parent_name' => ['required', 'string', 'min:2', 'max:255'],
        'parent_contact' => ['nullable', 'string', 'max:100'],
        'status' => ['required', 'in:NOT_REQUIRED,PENDING,APPROVED,CONTACTED,COMPLETED'],
        'reason' => ['nullable', 'string', 'max:500'],
    ]);
    $case = scopedCase($caseNumber);
    $parent = DB::table('parents')->where('school_id', $case->school_id)->where('full_name', $data['parent_name'])->first();
    $parentId = $parent?->id ?: DB::table('parents')->insertGetId(['school_id' => $case->school_id, 'full_name' => $data['parent_name'], 'contact' => $data['parent_contact'], 'created_at' => now(), 'updated_at' => now()]);
    $now = now();
    DB::table('case_parent_involvements')->updateOrInsert(['case_id' => $case->id, 'parent_id' => $parentId], ['status' => $data['status'], 'reason' => $data['reason'] ?? null, 'contacted_at' => in_array($data['status'], ['CONTACTED', 'COMPLETED'], true) ? $now : null, 'completed_at' => $data['status'] === 'COMPLETED' ? $now : null, 'updated_at' => $now, 'created_at' => $now]);
    auditAction('UPDATE_PARENT_INVOLVEMENT', 'CASE', $case->case_number, ['parent' => $data['parent_name'], 'status' => $data['status']]);
    notifyCounselor('PARENT_INVOLVEMENT', 'Pelibatan orang tua diperbarui', 'Status pelibatan orang tua pada ' . $case->case_number . ' menjadi ' . $data['status'] . '.', $case->case_number);

    return redirect()->route('cases.show', $caseNumber)->with('success', 'Pelibatan orang tua berhasil diperbarui.');
})->middleware(['role:COUNSELOR', 'permission:CASE_UPDATE'])->name('cases.parent-involvement');

Route::post('/cases/{caseNumber}/participants', function (string $caseNumber) {
    $data = request()->validate([
        'participant_type' => ['required', 'in:REPORTER,VICTIM,ALLEGED_PERPETRATOR,WITNESS,OTHER'],
        'display_name' => ['required', 'string', 'min:2', 'max:255'],
        'identity_visibility' => ['required', 'in:CASE_RESTRICTED,CASE_FULL,CASE_SENSITIVE'],
    ]);
    $case = scopedCase($caseNumber);
    DB::table('case_participants')->insert([
        'case_id' => $case->id, 'participant_type' => $data['participant_type'], 'display_name' => $data['display_name'],
        'identity_visibility' => $data['identity_visibility'], 'created_at' => now(), 'updated_at' => now(),
    ]);
    auditAction('ADD_PARTICIPANT', 'CASE', $case->case_number, ['participant_type' => $data['participant_type'], 'visibility' => $data['identity_visibility']]);

    return redirect()->route('cases.show', $caseNumber)->with('success', 'Pihak terkait berhasil ditambahkan.');
})->middleware(['role:COUNSELOR', 'permission:CASE_UPDATE'])->name('cases.participants');

Route::post('/cases/{caseNumber}/escalate', function (string $caseNumber) {
    $data = request()->validate(['reason' => ['required', 'string', 'min:5', 'max:500']]);
    $case = scopedCase($caseNumber);
    DB::table('case_escalations')->insert([
        'case_id' => $case->id, 'escalation_type' => 'MANUAL', 'reason' => $data['reason'],
        'escalated_by' => session('user_name', 'Bu Ratna Sari'), 'created_at' => now(), 'updated_at' => now(),
    ]);
    DB::table('notifications')->insert([
        'recipient_name' => 'Kepala Sekolah', 'type' => 'MANUAL_ESCALATION',
        'title' => 'Kasus dieskalasikan', 'message' => $case->case_number . ' membutuhkan perhatian Kepala Sekolah.',
        'priority' => 'URGENT', 'case_number' => $case->case_number, 'created_at' => now(), 'updated_at' => now(),
    ]);
    auditAction('ESCALATE_CASE', 'CASE', $case->case_number, ['reason' => $data['reason']]);

    return redirect()->route('cases.show', $caseNumber)->with('success', 'Kasus berhasil dieskalasikan kepada Kepala Sekolah.');
})->middleware(['role:COUNSELOR', 'permission:CASE_ESCALATE'])->name('cases.escalate');

Route::post('/cases/{caseNumber}/notes', function (string $caseNumber) {
    $data = request()->validate([
        'note_type' => ['required', 'in:VERIFICATION,HANDLING,COUNSELING,FOLLOW_UP,INTERNAL'],
        'content' => ['required', 'string', 'min:5', 'max:2000'],
    ]);
    $case = scopedCase($caseNumber);
    DB::table('case_notes')->insert([
        'case_id' => $case->id, 'note_type' => $data['note_type'], 'content' => $data['content'],
        'visibility' => 'PRIVATE_BK', 'created_by' => 'Bu Ratna Sari', 'created_at' => now(), 'updated_at' => now(),
    ]);
    auditAction('CREATE_NOTE', 'CASE', $case->case_number, ['note_type' => $data['note_type']]);
    notifyCounselor('NOTE_ADDED', 'Catatan kasus ditambahkan', 'Catatan ' . strtolower($data['note_type']) . ' ditambahkan ke ' . $caseNumber . '.', $caseNumber);

    return redirect()->route('cases.show', $caseNumber)->with('success', 'Catatan kasus berhasil disimpan.');
})->middleware(['role:COUNSELOR', 'permission:CASE_NOTE_CREATE'])->name('cases.notes');

Route::post('/cases/{caseNumber}/risk', function (string $caseNumber) {
    $data = request()->validate([
        'category_score' => ['required', 'integer', 'min:0', 'max:25'],
        'safety_score' => ['required', 'integer', 'min:0', 'max:25'],
        'repetition_score' => ['required', 'integer', 'min:0', 'max:25'],
        'impact_score' => ['required', 'integer', 'min:0', 'max:25'],
        'reason' => ['nullable', 'string', 'max:300'],
    ]);
    $case = scopedCase($caseNumber);
    $score = $data['category_score'] + $data['safety_score'] + $data['repetition_score'] + $data['impact_score'];
    $risk = $score >= 76 ? 'CRITICAL' : ($score >= 51 ? 'HIGH' : ($score >= 26 ? 'MEDIUM' : 'LOW'));
    $now = now();

    DB::transaction(function () use ($case, $data, $score, $risk, $now) {
        DB::table('case_risk_assessments')->insert([
            'case_id' => $case->id, 'category_score' => $data['category_score'], 'safety_score' => $data['safety_score'],
            'repetition_score' => $data['repetition_score'], 'impact_score' => $data['impact_score'], 'final_score' => $score,
            'risk_level' => $risk, 'assessed_by' => 'Bu Ratna Sari', 'created_at' => $now, 'updated_at' => $now,
        ]);
        DB::table('case_risk_histories')->insert([
            'case_id' => $case->id, 'old_risk_level' => $case->risk_level, 'new_risk_level' => $risk,
            'score' => $score, 'reason' => $data['reason'] ?? null, 'changed_by' => 'Bu Ratna Sari',
            'created_at' => $now, 'updated_at' => $now,
        ]);
        DB::table('cases')->where('id', $case->id)->update(['risk_level' => $risk, 'updated_at' => $now]);
        auditAction('ASSESS_RISK', 'CASE', $case->case_number, ['risk_level' => $risk, 'score' => $score]);
        notifyCounselor('RISK_ASSESSED', 'Penilaian risiko tersimpan', $case->case_number . ' memiliki level risiko ' . $risk . ' dengan skor ' . $score . '.', $case->case_number, in_array($risk, ['HIGH', 'CRITICAL'], true) ? 'URGENT' : 'NORMAL');
    });

    return redirect()->route('cases.show', $caseNumber)->with('success', 'Penilaian risiko berhasil disimpan.');
})->middleware(['role:COUNSELOR', 'permission:RISK_ASSESS'])->name('cases.risk');

Route::post('/cases/{caseNumber}/documents', function (string $caseNumber) {
    $data = request()->validate([
        'document_type' => ['required', 'in:EVIDENCE,RESOLUTION'],
        'file' => ['required', 'file', 'max:10240', 'mimes:jpg,jpeg,png,webp,pdf,doc,docx,mp4,mov,mp3,wav'],
    ]);
    $case = scopedCase($caseNumber);
    $file = request()->file('file');
    $path = $file->store('case-documents', 'local');
    $now = now();
    $payload = [
        'case_id' => $case->id, 'file_name' => $file->getClientOriginalName(), 'file_path' => $path,
        'uploaded_by' => 'Bu Ratna Sari', 'created_at' => $now, 'updated_at' => $now,
    ];
    if ($data['document_type'] === 'RESOLUTION') {
        DB::table('case_resolution_documents')->insert($payload + ['verification_status' => 'VALID']);
    } else {
        DB::table('case_evidences')->insert($payload + ['mime_type' => $file->getMimeType(), 'file_size' => $file->getSize()]);
    }
    auditAction('UPLOAD_DOCUMENT', 'CASE', $case->case_number, ['file_name' => $file->getClientOriginalName(), 'document_type' => $data['document_type']]);
    notifyCounselor('DOCUMENT_UPLOADED', 'Dokumen kasus diunggah', $file->getClientOriginalName() . ' ditambahkan ke ' . $caseNumber . '.', $caseNumber);

    return redirect()->route('cases.show', $caseNumber)->with('success', 'Dokumen berhasil diunggah.');
})->middleware(['role:COUNSELOR', 'permission:EVIDENCE_UPLOAD'])->name('cases.documents');

Route::get('/cases/{caseNumber}/documents/{documentType}/{id}', function (string $caseNumber, string $documentType, int $id) {
    $case = scopedCase($caseNumber);
    abort_unless(in_array($documentType, ['evidence', 'resolution'], true), 404);
    $table = $documentType === 'evidence' ? 'case_evidences' : 'case_resolution_documents';
    $document = DB::table($table)->where('id', $id)->where('case_id', $case->id)->firstOrFail();
    abort_unless(Storage::disk('local')->exists($document->file_path), 404, 'File tidak ditemukan.');
    auditAction('DOWNLOAD_DOCUMENT', 'CASE', $case->case_number, ['document_id' => $id, 'document_type' => $documentType]);
    DB::table('case_access_logs')->insert(['user_id' => session('user_id'), 'user_name' => session('user_name'), 'case_id' => $case->id, 'action' => 'DOWNLOAD_DOCUMENT', 'access_level' => 'CASE_SENSITIVE', 'reason' => 'Dokumen kasus diunduh', 'ip_address' => request()->ip(), 'created_at' => now(), 'updated_at' => now()]);

    return Storage::disk('local')->download($document->file_path, $document->file_name);
})->middleware(['role:COUNSELOR', 'permission:EVIDENCE_UPLOAD'])->name('cases.documents.download');

Route::post('/cases/{caseNumber}/status', function (string $caseNumber) {
    $data = request()->validate([
        'status' => ['required', 'in:UNDER_VERIFICATION,IN_HANDLING,RESOLVED,CLOSED'],
        'reason' => ['nullable', 'string', 'max:500'],
    ]);
    $allowed = [
        'PENDING_RESPONSE' => ['UNDER_VERIFICATION'],
        'UNDER_VERIFICATION' => ['IN_HANDLING', 'RESOLVED'],
        'IN_HANDLING' => ['RESOLVED'],
        'RESOLVED' => ['CLOSED'],
    ];

    $case = scopedCase($caseNumber);
    abort_unless(in_array($data['status'], $allowed[$case->status] ?? [], true), 422, 'Transisi status tidak diizinkan.');
    if ($data['status'] === 'CLOSED') {
        abort_unless(DB::table('case_resolution_documents')->where('case_id', $case->id)->where('verification_status', 'VALID')->exists(), 422, 'Kasus belum memiliki dokumen penyelesaian yang valid.');
    }

    DB::transaction(function () use ($case, $data) {
        $now = now();
        DB::table('cases')->where('id', $case->id)->update([
            'status' => $data['status'],
            'updated_at' => $now,
            'resolved_at' => $data['status'] === 'RESOLVED' ? $now : null,
            'closed_at' => $data['status'] === 'CLOSED' ? $now : null,
        ]);
        DB::table('case_status_histories')->insert([
            'case_id' => $case->id, 'old_status' => $case->status, 'new_status' => $data['status'],
            'reason' => $data['reason'] ?? null, 'changed_by' => 'Bu Ratna Sari',
            'created_at' => $now, 'updated_at' => $now,
        ]);
        DB::table('case_actions')->insert([
            'case_id' => $case->id,
            'action_type' => $data['status'] === 'UNDER_VERIFICATION' ? 'VERIFICATION' : ($data['status'] === 'IN_HANDLING' ? 'HANDLING' : 'RESOLUTION'),
            'description' => $data['reason'] ?: 'Status kasus diperbarui menjadi ' . str_replace('_', ' ', $data['status']),
            'performed_by' => 'Bu Ratna Sari', 'created_at' => $now, 'updated_at' => $now,
        ]);
        if ($case->status === 'PENDING_RESPONSE') {
            DB::table('case_slas')->where('case_id', $case->id)->update([
                'status' => 'COMPLETED', 'initial_response_at' => $now, 'updated_at' => $now,
            ]);
        }
        notifyCounselor('STATUS_CHANGED', 'Status kasus diperbarui', $case->case_number . ' sekarang ' . str_replace('_', ' ', $data['status']) . '.', $case->case_number, 'HIGH');
        auditAction('CHANGE_STATUS', 'CASE', $case->case_number, ['status' => $data['status']], ['status' => $case->status]);
    });

    return redirect()->route('cases.show', $caseNumber)->with('success', 'Status kasus berhasil diperbarui.');
})->middleware(['role:COUNSELOR', 'permission:CASE_UPDATE'])->name('cases.status');
