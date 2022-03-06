<?php

namespace App\Http\Middleware;

use Closure;
use GAMP;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class TrackThroughMeasurementProtocol
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            return $next($request);
        }

        // Create a new UUID which is used as the Client ID
        $uuid = Auth::id();

        $gamp = GAMP::setClientId($uuid);
        $gamp->setDocumentPath('/' . $request->path());
        $gamp->setDocumentReferrer($request->server('HTTP_REFERER', ''));
        $gamp->setUserAgentOverride($request->server('HTTP_USER_AGENT'));

        // Override the sent IP with the IP from the current request.
        // Otherwhise the servers IP would be sent.
        $gamp->setIpOverride($request->ip());

        $gamp->sendPageview();

        return $next($request);
    }
}
