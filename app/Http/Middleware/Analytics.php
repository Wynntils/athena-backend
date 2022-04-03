<?php

namespace App\Http\Middleware;

use Closure;
use GAMP;
use Illuminate\Http\Request;
use TheIconic\Tracking\GoogleAnalytics\Analytics as GoogleAnalytics;


class Analytics
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
        async(function () use ($request) {
            $clientId = $this->getClientId($request);
            /** @var GoogleAnalytics $gamp */
            $gamp = GAMP::setClientId($clientId);
            $gamp->setDocumentPath('/'.$request->path());
            $gamp->setDocumentReferrer($request->server('HTTP_REFERER', ''));
            $gamp->setUserAgentOverride($request->server('HTTP_USER_AGENT', 'Missing/1.0'));

            // Override the sent IP with the IP from the current request.
            // Otherwhise the servers IP would be sent.
            $gamp->setIpOverride($request->ip());

            $gamp->sendPageview();
        });

        return $next($request);
    }

    private function getClientId(Request $request)
    {
        $clientId = null;
        if($request->hasHeader('authToken')) {
            $authToken = $request->header('authToken');
            $user = \App\Models\User::where('authToken', $authToken)->first();
            $clientId = $user?->id;
        }

        return $clientId ?? $request->route('apiKey') ?? config('athena.general.apiKey');
    }
}
