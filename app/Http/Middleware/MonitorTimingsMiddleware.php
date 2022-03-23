<?php

namespace App\Http\Middleware;

use App\Http\Libraries\Notifications;
use Closure;
use DiscordWebhook\EmbedColor;
use Illuminate\Http\Request;

class MonitorTimingsMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);
        if (defined('LARAVEL_START')) {
            $time = round((microtime(true) - LARAVEL_START) * 1000, 2);
            $path = $request->route()?->uri() ?? '';
            $method = $request->method();
            if ($time > 2000) {
                Notifications::log(description: "`Routes -> $method -> /$path` took {$time}ms", color: EmbedColor::RED);
            }
        }
        return $response;
    }
}
