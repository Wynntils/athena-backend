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
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        if (! \Auth::check()) {
            return response()->json(['error' => 'You are not logged in.']);
        }

        /** @var \App\Models\User $authUser */
        $authUser = \Auth::user();

        if (! in_array($authUser->account_type, [AccountType::MODERATOR, AccountType::HELPER], true)) {
            return response()->json(['error' => 'You do not have the required permissions.']);
        }

        return $next($request);
    }
}
