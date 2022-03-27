<?php

namespace App\Http\Middleware;

use App\Http\Libraries\Notifications;
use Closure;
use DiscordWebhook\EmbedColor;
use Illuminate\Http\Request;

class MonitorMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);
        if (defined('LARAVEL_START')) {
            $time = round((microtime(true) - LARAVEL_START) * 1000, 2);
            $param = json_encode(\Illuminate\Support\Facades\Request::all());
            $path = str_replace([
                config('athena.general.apiKey'),
                config('athena.capes.token')
            ], '{token}', $request->path());
            $method = $request->method();
            if ($time > 2000) {
                Notifications::log(description: "`Routes -> $method -> /$path` took {$time}ms", color: EmbedColor::RED);
            }
            if ($path !== 'user/getInfo' && config('app.debug')) {
                Notifications::log(title: "Debug Information", description: substr("`Routes -> $method -> /$path`\n**Request:** ```$param```\n**Response:**```{$response->getContent()}", 0, 3000) . (strlen($response->getContent()) > 3000 ? '...' : '') . "```", color: EmbedColor::AQUA);
            }
        }
        return $response;
    }
}
