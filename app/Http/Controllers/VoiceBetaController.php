<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\VoiceBeta;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

class VoiceBetaController extends Controller
{
    public function index(): Response
    {
        $userIds = VoiceBeta::query()->pluck('user_id');
        $usernames = User::query()->whereIn('id', $userIds)->pluck('username', 'id');

        $rows = $userIds->map(fn (string $id) => [
            'user_id' => $id,
            'username' => $usernames[$id] ?? '(unknown)',
        ])->sortBy('username')->values();

        return response()->view('voice.beta', ['rows' => $rows]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->validate($request, ['identifier' => 'required|string']);
        $identifier = $request->input('identifier');

        $user = Str::isUuid($identifier)
            ? User::find($identifier)
            : User::query()->where('username', $identifier)->first();

        if (! $user) {
            return back()->withInput()->withErrors(['identifier' => 'No such user.']);
        }

        VoiceBeta::firstOrCreate(['user_id' => $user->id]);

        return redirect()->route('voice.beta.index')->with('status', "Added {$user->username} to the voice beta.");
    }

    public function destroy(string $userId): RedirectResponse
    {
        VoiceBeta::whereKey($userId)->delete();

        return redirect()->route('voice.beta.index')->with('status', 'Removed from the voice beta.');
    }
}
