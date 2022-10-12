<?php

namespace App\Http\Middleware;

use App\Http\Libraries\Notifications;
use Closure;
use DiscordWebhook\EmbedColor;
use Illuminate\Http\Request;

class DebugMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        if (!in_array(\Auth::id(), config('athena.debug.users'))) {
            return $response;
        }


        $path = str_replace([
            config('athena.general.apiKey'),
            config('athena.capes.token')
        ], '{token}', $request->path());
        $method = $request->method();

        if ($request->has('config')) {
            $configs = [
                'config' => collect($request->file('config'))
                    ->filter(function ($config) {
                        return $config instanceof \Illuminate\Http\UploadedFile;
                    })
                    ->map(function (\Illuminate\Http\UploadedFile $config) {
                        return [
                            'name' => $config->getClientOriginalName(),
                            'size' => humanFileSize($config->getSize()),
                            'type' => $config->getMimeType(),
                        ];
                    })
                    ->toArray()
            ];
        }

        $param = json_encode(array_merge($request->all(), ($configs ?? [])), JSON_PRETTY_PRINT);
        $prettyResponse = json_encode(json_decode($response->getContent()), JSON_PRETTY_PRINT);
        if ($prettyResponse === null) {
            $prettyResponse = $response->getContent();
        }
        try {
            Notifications::log(
                title: "Debug Information Request",
                description: substr("`Routes -> " . $method . " -> /" . $path . "`\n**Request:** ```json\n{$request->userAgent()}\n" . $param, 0, 3000) . (strlen($param) > 3000 ? '...' : '') . '```',
                color: EmbedColor::AQUA
            );
            Notifications::log(
                title: "Debug Information Response",
                description: substr("`Routes -> $method -> /$path`\n**Response {$response->getStatusCode()}:**```json\n{$prettyResponse}",
                    0, 3000) . (strlen($prettyResponse) > 3000 ? '...' : '') . "```",
                color: EmbedColor::AQUA
            );
        } catch (Throwable $e) {
            //
        }

        return $response;
    }
}
