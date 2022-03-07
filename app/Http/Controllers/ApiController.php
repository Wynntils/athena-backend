<?php

namespace App\Http\Controllers;

use \App\Models\ApiKey;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Ramsey\Uuid\Uuid;

class ApiController extends Controller
{

    public function createApiKey(Request $request): JsonResponse
    {
        $body = json_decode($request->getContent(), true);

        if(!array_key_exists('name', $body) || !array_key_exists('description', $body) || !array_key_exists('adminContact', $body) || !array_key_exists('maxLimit', $body)) {
            return response()->json(['error' => "Invalid body: expecting 'name', 'description', 'adminContact' and 'maxLimit'."], 400);
        }

        ApiKey::create([
            '_id' => Uuid::uuid4()->toString(),
            'name' => $body['name'],
            'description' => $body['description'],
            'adminContact' => $body['adminContact'],
            'maxLimit' => $body['maxLimit']
        ])->save();

        return response()->json(['message' => 'API Key created successfully!'], 201);
    }

    public function changeApiKey(Request $request): JsonResponse
    {
        $body = json_decode($request->getContent(), true);

        if(!array_key_exists('key', $body) || !array_key_exists('maxLimit', $body))
            return response()->json(['error' => "Invalid body: expecting 'key' and 'maxLimit'."], 400);

        $key = ApiKey::find($body['key']);
        if(!$key)
            return response()->json(['error' => "The provided API key does not exist."], 400);

        $key->maxLimit = $body['maxLimit'];
        $key->save();

        return response()->json(['message' => 'Succesfully changed API Key!', "key" => $body['key']]);
    }

}
