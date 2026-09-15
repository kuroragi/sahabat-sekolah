<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class RequirePermission
{
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $role = $request->session()->get('user_role');
        $allowed = $role && DB::table('role_permissions')
            ->join('roles', 'role_permissions.role_id', '=', 'roles.id')
            ->join('permissions', 'role_permissions.permission_id', '=', 'permissions.id')
            ->where('roles.code', $role)
            ->where('permissions.code', $permission)
            ->exists();

        abort_unless($allowed, 403, 'Permission tidak mencukupi.');

        return $next($request);
    }
}
