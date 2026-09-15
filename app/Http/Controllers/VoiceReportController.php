<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\VoiceReport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class VoiceReportController extends Controller
{
    private const array AUDIO_SIDES = ['reporter', 'target'];

    private const array ALLOWED_COMMENT_TAGS = ['b', 'i', 'u', 's', 'a', 'br', 'p', 'ul', 'ol', 'li', 'pre', 'code', 'blockquote'];

    public function index(Request $request): Response
    {
        $showHandled = in_array($request->input('showHandled'), ['on', '1'], true);

        $reports = VoiceReport::query();

        if (! $showHandled) {
            $reports->unhandled();
        }

        if ($search = $request->input('search')) {
            $reports->where(function ($q) use ($search) {
                $q->where('reason', 'like', "%{$search}%")
                    ->orWhere('world', 'like', "%{$search}%")
                    ->orWhere('reporter_id', 'like', "%{$search}%")
                    ->orWhere('target_id', 'like', "%{$search}%");
            });
        }

        $reports = $reports->orderByDesc('id')->paginate(15);

        $userIds = $reports->getCollection()
            ->flatMap(fn (VoiceReport $r) => [$r->reporter_id, $r->target_id])
            ->unique()
            ->all();

        return response()
            ->view('voice.index', [
                'reports' => $reports,
                'showHandled' => $showHandled,
                'usernames' => $this->usernames($userIds),
            ])
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate');
    }

    public function show(VoiceReport $voiceReport): Response
    {
        $usernames = $this->usernames([
            $voiceReport->reporter_id,
            $voiceReport->target_id,
            ...$voiceReport->witnessIdArray(),
        ]);

        return response()
            ->view('voice.show', [
                'report' => $voiceReport,
                'reporterName' => $usernames[$voiceReport->reporter_id],
                'targetName' => $usernames[$voiceReport->target_id],
                'witnessNames' => array_map(fn (string $id) => $usernames[$id], $voiceReport->witnessIdArray()),
            ])
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate');
    }

    public function audio(VoiceReport $voiceReport, string $side): BinaryFileResponse
    {
        abort_unless(in_array($side, self::AUDIO_SIDES, true), 404);
        abort_unless($side === 'reporter' ? $voiceReport->hasReporterAudio() : $voiceReport->hasTargetAudio(), 404);

        $path = rtrim(config('athena.voice.report_dir'), '/')."/{$voiceReport->id}/{$side}.opus";
        abort_unless(is_file($path), 404);

        return response()->file($path, ['Content-Type' => 'audio/ogg']);
    }

    public function setHandled(Request $request, VoiceReport $voiceReport): JsonResponse
    {
        $handled = $request->input('handled') === 'true';

        $voiceReport->handled = $handled;
        $voiceReport->handled_by = $handled ? $request->user()->username : null;
        $voiceReport->handled_at = $handled ? now()->getTimestampMs() : null;
        $voiceReport->save();

        return response()->json(['success' => true]);
    }

    public function addComment(Request $request, VoiceReport $voiceReport): JsonResponse
    {
        $this->validate($request, ['comment' => 'required|string']);

        $clean = str(strip_tags($request->input('comment'), self::ALLOWED_COMMENT_TAGS))
            ->replaceMatches('/\son[A-z]+="[^"]*"/', '')
            ->toString();

        $now = now();
        $comment = [
            'id' => $now->timestamp,
            'user' => $request->user()->username,
            'user_id' => $request->user()->id,
            'time' => $now->toDateTimeString(),
            'comment' => $clean,
        ];

        $voiceReport->comments = [...($voiceReport->comments ?? []), $comment];
        $voiceReport->save();

        return response()->json(['success' => true, 'comment' => $comment]);
    }

    public function deleteComment(Request $request, VoiceReport $voiceReport): JsonResponse
    {
        $this->validate($request, ['commentId' => 'required|integer']);

        $commentId = (int) $request->input('commentId');

        $voiceReport->comments = array_values(array_filter(
            $voiceReport->comments ?? [],
            fn (array $c) => (int) ($c['id'] ?? 0) !== $commentId
        ));
        $voiceReport->save();

        return response()->json(['success' => true, 'comments' => $voiceReport->comments]);
    }

    /**
     * @param  string[]  $ids
     * @return array<string, string> uuid => username, falling back to the uuid itself
     */
    private function usernames(array $ids): array
    {
        $ids = array_values(array_unique($ids));
        $found = User::query()->whereIn('id', $ids)->pluck('username', 'id')->all();

        return array_combine($ids, array_map(fn (string $id) => $found[$id] ?? $id, $ids));
    }
}
