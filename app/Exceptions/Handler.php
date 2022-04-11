<?php

namespace App\Exceptions;

use App\Http\Libraries\Notifications;
use DiscordWebhook\EmbedColor;
use Illuminate\Database\Eloquent\ModelNotFoundException;
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
            try {
                Notifications::log(
                    title: "An exception occured",
                    description: sprintf(
                        "`Routes -> %s -> /%s`\n**%s** ```%s```",
                        $method,
                        $path,
                        $e->getMessage(),
                        substr($e->getTraceAsString(), 0, 3000)
                    ),
                    color: EmbedColor::RED
                );
            } catch (Throwable $e) {
                //
            }
        });
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $exception
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Throwable
     */
    public function render($request, Throwable $exception)
    {
        if($exception instanceof ModelNotFoundException) {
            return match ($exception->getModel()) {
                \App\Models\User::class => response()->json(['message' => 'User not found'], 404),
                \App\Models\Guild::class => response()->json(['message' => 'Guild not found'], 404),
            };
        }

        return parent::render($request, $exception);
    }
}
