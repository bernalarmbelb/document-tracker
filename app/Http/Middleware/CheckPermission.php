<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckPermission
{
    /**
     * Allow the request through if the user is an ADMINISTRATOR/SUPER ADMIN,
     * or holds at least one of the given privileges via hasPermission().
     */
    public function handle(Request $request, Closure $next, string ...$permissions)
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        if ($user->account_type === "ADMINISTRATOR" || $user->account_type === "SUPER ADMIN") {
            return $next($request);
        }

        foreach ($permissions as $permission) {
            if ($user->hasPermission($permission)) {
                return $next($request);
            }
        }

        return redirect('/dashboard');
    }
}
