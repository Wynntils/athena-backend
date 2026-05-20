<?php

namespace App\Http\Controllers;

use App\Enums\AccountType;
use App\Http\Resources\CosmAssetResource;
use App\Models\CosmeticAsset;
use App\Services\CosmeticAssetService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CosmeticsController extends Controller
{
    public function __construct(private CosmeticAssetService $service) {}

    public function index(Request $request): JsonResponse
    {
        $query = CosmeticAsset::approvedPublic()->with(['uploader', 'votes']);

        // Search
        if ($q = $request->query('q')) {
            $query->where('name', 'like', '%' . $q . '%');
        }

        // Tag filter — whereJsonContains works for both SQLite and PostgreSQL
        $tags = $request->query('tags', []);
        if (is_array($tags) && count($tags) > 0) {
            foreach ($tags as $tag) {
                $query->whereJsonContains('tags', $tag);
            }
        }

        // Sort
        $sort = $request->query('sort', 'newest');
        match ($sort) {
            'votes' => $query->orderByRaw(
                '(SELECT COUNT(*) FROM cosmetic_votes WHERE cosmetic_id = cosmetic_assets.id AND vote = 1) DESC'
            ),
            'worn'  => $query->orderBy('equip_count', 'desc'),
            default => $query->orderBy('uploaded_at', 'desc'),
        };

        $paginated = $query->paginate(15);

        return response()->json([
            'data'         => CosmAssetResource::collection($paginated->items()),
            'current_page' => $paginated->currentPage(),
            'per_page'     => $paginated->perPage(),
            'total'        => $paginated->total(),
            'last_page'    => $paginated->lastPage(),
        ]);
    }

    public function show(string $sha): JsonResponse
    {
        $asset = CosmeticAsset::approvedPublic()->bySha($sha)->with(['uploader', 'votes'])->first();

        if (! $asset) {
            return response()->json(['message' => 'Not found.'], 404);
        }

        return response()->json(new CosmAssetResource($asset));
    }

    public function vote(Request $request, string $sha): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        if ($user->account_type === AccountType::BANNED) {
            return response()->json(['message' => 'Your account has been banned.'], 403);
        }

        $validated = $request->validate([
            'vote' => 'required|integer|in:1,-1',
        ]);

        try {
            $this->service->vote($user, $sha, (int) $validated['vote']);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 403);
        }

        return response()->json(['message' => 'Vote recorded.']);
    }

    public function unvote(Request $request, string $sha): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        if ($user->account_type === AccountType::BANNED) {
            return response()->json(['message' => 'Your account has been banned.'], 403);
        }

        $this->service->unvote($user, $sha);

        return response()->json(['message' => 'Vote removed.']);
    }

    public function update(Request $request, string $sha): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        if ($user->account_type === AccountType::BANNED) {
            return response()->json(['message' => 'Your account has been banned.'], 403);
        }

        $validated = $request->validate([
            'name'   => 'nullable|string|max:80',
            'visibility' => 'nullable|string|in:public,private',
            'tags'   => 'nullable|array|max:10',
            'tags.*' => 'string|max:32',
        ]);

        try {
            $this->service->submitEdit($user, $sha, $validated);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 403);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 409);
        }

        return response()->json(['message' => 'Edit submitted for review.']);
    }
}
