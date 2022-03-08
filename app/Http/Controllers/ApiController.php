<?php

namespace App\Http\Controllers;

use App\Models\ApiKey;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Ramsey\Uuid\Uuid;

class ApiController extends Controller
{

    public function createApiKey(Request $request): JsonResponse
    {
        $body = $request->json();

        if (!$body->has('name') || !$body->has('description') || !$body->has('adminContact') || !$body->has('maxLimit')) {
            return response()->json(['error' => "Invalid body: expecting 'name', 'description', 'adminContact' and 'maxLimit'."],
                400);
        }

        ApiKey::create([
            '_id' => Uuid::uuid4()->toString(),
            'name' => $body->get('name'),
            'description' => $body->get('description'),
            'adminContact' => $body->get('adminContact'),
            'maxLimit' => $body->get('maxLimit')
        ])->save();

        return response()->json(['message' => 'API Key created successfully!'], 201);
    }

    public function changeApiKey(Request $request): JsonResponse
    {
        $body = $request->json();

        if (!$body->has('key') || !$body->has('maxLimit')) {
            return response()->json(['error' => "Invalid body: expecting 'key' and 'maxLimit'."], 400);
        }

        $key = ApiKey::find($body->get('key'));
        if (!$key) {
            return response()->json(['error' => "The provided API key does not exist."], 400);
        }

        $key->maxLimit = $body->get('maxLimit');
        $key->save();

        return response()->json(['message' => 'Succesfully changed API Key!', "key" => $body->get('key')]);
    }

}
