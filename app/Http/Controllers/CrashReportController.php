<?php

namespace App\Http\Controllers;

use App\Http\Libraries\Notifications;
use App\Models\CrashReport;
use DiscordWebhook\EmbedColor;
use Illuminate\Http\Request;

class CrashReportController extends Controller
{
    public function report(Request $request)
    {
        $this->validate($request, [
            'trace' => 'required|string',
        ]);

        $trace = $request->post('trace');
        // Get version from user agent
        $versionString = str($request->userAgent());

        if ($versionString->contains('\\')) {
            $version = $versionString->replace(['Wynntils', ' Artemis', '\\'], '');
        } else {
            $version = $versionString->toString();
        }

        // Replace mixin names with a generic name for the hash
        $replacementPattern = '/\$[a-z]{3}\d+(\$wynntils)?\$[A-z]+/';

        $traceStringToMd5 = str($trace)->replaceMatches($replacementPattern, 'MIXIN')->toString();

        $traceHash = md5($traceStringToMd5);

        // Find or create the error report with the same hash
        $crashReport = CrashReport::firstOrCreate([
            'trace_hash' => $traceHash,
        ], [
            'trace' => $trace,
            'occurrences' => [
                [
                    'version' => $version,
                    'time' => now(),
                    'user_agent' => $request->userAgent(),
                ],
            ],
        ]);

        // If the error report already existed, update its attributes
        if (!$crashReport->wasRecentlyCreated) {
            $crashReport->occurrences = array_merge($crashReport->occurrences, [
                [
                    'version' => $version,
                    'time' => now(),
                    'user_agent' => $request->userAgent(),
                ],
            ]);
            $crashReport->save();
        } else {
            if (app()->environment('production')) {
                // If the error report was just created, log it
                Notifications::crash(
                    title: "A new crash report was logged",
                    description: sprintf(
                        "**[%s](%s)**\n ```%s```",
                        $crashReport->trace_hash,
                        route('crash.view', $crashReport->trace_hash),
                        // limit the length of the trace to 500 characters
                        str($crashReport->trace)->limit(500)->toString()
                    ),
                    color: EmbedColor::RED
                );
            }
        }

        return response()->json(['message' => 'Crash report logged successfully.', 'hash' => $crashReport->trace_hash]);
    }

    public function setHandled(Request $request, CrashReport $crashReport)
    {
        $crashReport->handled = $request->input('handled') === 'true';
        $crashReport->save();

        return response()->json(['success' => true]);
    }

    public function addComment(Request $request, CrashReport $crashReport)
    {
        $this->validate($request, [
            'comment' => 'required|string',
        ]);

        // Comment is going to be html, so we need to sanitize it, remove any javascript
        $request->merge(['comment' => strip_tags($request->input('comment'), ['b', 'i', 'u', 's', 'a', 'br', 'p', 'ul', 'ol', 'li', 'img', 'pre', 'code', 'blockquote', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'hr'])]);

        // remove any on handler attributes
        $request->merge(['comment' => str($request->input('comment'))->replaceMatches('/on[A-z]+="[^"]+"/', '')]);

        if (!isset($crashReport->comments)) {
            $crashReport->comments = [];
        }

        $now = now();

        $newComment = [
            'id' => $now->timestamp,
            'user' => $request->user()->username,
            'user_id' => $request->user()->id,
            'time' => $now,
            'comment' => $request->input('comment'),
        ];

        $crashReport->comments = array_merge($crashReport->comments, [$newComment]);
        $crashReport->save();

        return response()->json(['success' => true, 'comment' => array_merge($newComment, ['time' => $now->toDateTimeString()])]);
    }

    public function deleteComment(Request $request, CrashReport $crashReport)
    {
        $this->validate($request, [
            'commentId' => 'required|integer',
        ]);

        $commentId = $request->input('commentId');

        if (!isset($crashReport->comments)) {
            $crashReport->comments = [];
        }

        $crashReport->comments = array_values(array_filter($crashReport->comments, static function ($comment) use ($commentId) {
            return (string) $comment->id !== $commentId;
        }));

        $crashReport->save();

        return response()->json(['success' => true, 'comments' => $crashReport->comments]);
    }

    public function view(Request $request, CrashReport $crashReport)
    {
        return response()
            ->view('crash.view', [
                'crashReport' => $crashReport,
            ])
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }

    public function index(Request $request)
    {
        // get showHandled parameter will be either "on" or "1" or "0"
        $showHandled = $request->input('showHandled') === 'on' || $request->input('showHandled') === '1';

        if ($showHandled) {
            $crashReports = CrashReport::orderByDesc('updated_at');
        } else {
            $crashReports = CrashReport::where('handled', false)->orWhere('handled', 'exists', false);
        }

        if ($search = $request->input('search')) {
            $crashReports = $crashReports->where('trace', 'like', "%$search%")
                ->orWhere('trace_hash', 'like', "%$search%");
        }

        $crashReports = $crashReports->orderByDesc('updated_at')->paginate(10);

        return response()
            ->view('crash.index', [
                'crashReports' => $crashReports,
                'showHandled' => $showHandled,
            ])
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }
}
