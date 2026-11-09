<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequirePermission
{
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = $request->user();

        abort_unless($user, 401);
        abort_unless($user->status === 'active', 403);
        abort_unless($user->hasPermission($permission), 403, 'شما مجوز انجام این عملیات را ندارید.');

        return $next($request);
    }
}
