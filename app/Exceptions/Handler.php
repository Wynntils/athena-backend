<?php

namespace App\Exceptions;

use App\Http\Libraries\Notifications;
use DiscordWebhook\EmbedColor;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            $request = \Request::instance();
            $path = $request->route()?->uri() ?? '';
            $method = $request->method();

            Notifications::log(title: "An exception occured", description: "`Routes -> $method -> /$path`\n**{$e->getMessage()}** ```" . substr($e->getTraceAsString(), 0, 3000) . "```", color: EmbedColor::RED);
        });
    }
}
