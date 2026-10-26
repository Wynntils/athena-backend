<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\VoiceBan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class VoiceBanController extends Controller
{
    private const array DURATIONS_MS = [
        '1d' => 86_400_000,
        '7d' => 604_800_000,
        '30d' => 2_592_000_000,
    ];

    public function index(): Response
    {
        return response()->view('voice.bans', [
            'bans' => VoiceBan::query()->orderByDesc('id')->paginate(20),
            'now' => now()->getTimestampMs(),
        ]);
    }

    public function create(Request $request): Response
    {
        $userId = (string) $request->input('user', '');

        return response()->view('voice.ban-create', [
            'user' => $userId,
            'username' => $userId !== '' ? User::find($userId)?->username : null,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->validate($request, [
            'user_id' => 'required|uuid',
            'reason' => 'required|string|max:1000',
            'duration' => 'required|in:permanent,'.implode(',', array_keys(self::DURATIONS_MS)),
        ]);

        $now = now()->getTimestampMs();
        $duration = $request->input('duration');

        VoiceBan::create([
            'user_id' => $request->input('user_id'),
            'reason' => $request->input('reason'),
            'banned_by' => $request->user()->username,
            'banned_at' => $now,
            'expires_at' => $duration === 'permanent' ? null : $now + self::DURATIONS_MS[$duration],
            'lifted_at' => null,
        ]);

        return redirect()->route('voice.bans.index')->with('status', 'Ban created.');
    }

    public function lift(VoiceBan $voiceBan): RedirectResponse
    {
        $voiceBan->lifted_at = now()->getTimestampMs();
        $voiceBan->save();

        return redirect()->route('voice.bans.index')->with('status', 'Ban lifted.');
    }
}
