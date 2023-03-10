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

        $traceHash = md5($trace);

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

        return response()->json(['message' => 'Crash report logged successfully.', 'hash' => $crashReport->trace_hash]);
    }

    public function view(Request $request, $hash)
    {
        $crashReport = CrashReport::where('trace_hash', $hash)->firstOrFail();

        return view('crash.view', [
            'crashReport' => $crashReport,
        ]);
    }

    public function delete(Request $request, $hash)
    {
        $crashReport = CrashReport::where('trace_hash', $hash)->firstOrFail();
        $crashReport->delete();

        return redirect()->route('crash.index');
    }

    public function index(Request $request)
    {
        $crashReports = CrashReport::orderBy('created_at', 'desc')->paginate(10);

        return view('crash.index', [
            'crashReports' => $crashReports,
        ]);
    }
}
