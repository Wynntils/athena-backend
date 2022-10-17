<?php

require __DIR__ . '/vendor/autoload.php';

$url = 'https://api.minecraftitemids.com/v1/search';

$payload = [
    "query" => '1',
    "version" => '1.12.2',
    "show" => [
        "item_id",
        "numerical_id"
    ]
];

$client = new GuzzleHttp\Client();
// loop through all the items in the database
for ($i = 1; $i < 373; $i++) {
    $payload['query'] = (string) $i;
    try {
        $response = $client->post($url, [
            'json' => $payload
        ]);
    } catch (\GuzzleHttp\Exception\GuzzleException $e) {
        echo $e->getMessage();
        break;
    }
    $body = json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);
    foreach ($body['data'] as $item) {
        if (!isset($itemData[$item['numericalIdString']])) {
            $key = explode(':', $item['numericalIdString']);

            $itemData[$item['numericalIdString']] = [
                'id' => $key[0],
                'meta' => $key[1] ?? null,
                'displayName' => $item['displayName'],
                'name' => $item['name']
            ];
        }
    }
}

$items = collect($itemData)->sortBy(['id', 'meta']);

file_put_contents(__DIR__ . '/storage/app/item-data.json', $items->toJson(JSON_PRETTY_PRINT));
