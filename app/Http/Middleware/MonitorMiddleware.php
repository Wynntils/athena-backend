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
            $path = str_replace([
                config('athena.general.apiKey'),
                config('athena.capes.token')
            ], '{token}', $request->path());
            $method = $request->method();
            if ($time > 2000) {
                Notifications::log(description: "`Routes -> $method -> /$path` took {$time}ms", color: EmbedColor::RED);
            }
            if ($path !== 'user/getInfo' && config('app.debug')) {
                $param = json_encode($request->all(), JSON_PRETTY_PRINT);
                $prettyResponse = json_encode(json_decode($response->getContent()), JSON_PRETTY_PRINT);
                try {
                    Notifications::log(
                        title: "Debug Information",
                        description: substr("`Routes -> $method -> /$path`\n**Request:** ```json\n$param```\n**Response:**```json\n{$prettyResponse}",
                            0, 3000).(strlen($prettyResponse) > 3000 ? '...' : '')."```",
                        color: EmbedColor::AQUA
                    );
                } catch (\Exception $e) {

                }
            }
        }
        return $response;
    }
}
