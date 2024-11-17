<?php

namespace App\Exceptions;

use App\Http\Libraries\Notifications;
use Composer\Semver\Comparator;
use DiscordWebhook\EmbedColor;
use Sentry\Laravel\Integration;
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
            Integration::captureUnhandledException($e);
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
        if ($exception instanceof ModelNotFoundException) {
            return match ($exception->getModel()) {
                \App\Models\User::class => response()->json(['message' => 'User not found'], 404),
                \App\Models\Guild::class => response()->json(['message' => 'Guild not found'], 404),
                default => response()->json($exception->getMessage(), 404),
            };
        }

        if ($exception instanceof \Illuminate\Http\Client\ConnectionException) {
            return response()->json([
                'message' => 'Could not connect to External API Provider.',
                'error' => $exception->getMessage()
            ], 500);
        }

        if ($exception instanceof \InvalidArgumentException) {
            return response()->json([
                'message' => 'Invalid argument provided.',
                'error' => $exception->getMessage()
            ], 400);
        }

        return parent::render($request, $exception);
    }
}
