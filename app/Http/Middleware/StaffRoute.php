<?php

namespace App\Http\Middleware;

use App\Enums\AccountType;
use Closure;
use Illuminate\Http\Request;

class StaffRoute
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     *
     */
    public function handle(Request $request, Closure $next)
    {
        if (!\Auth::check()) {
            return response()->json(['error' => 'You are not logged in.']);
        }

        if (!in_array(\Auth::user()->accountType, [AccountType::MODERATOR, AccountType::HELPER], true)) {
            return response()->json(['error' => 'You do not have the required permissions.']);
        }

        return $next($request);
    }
}
