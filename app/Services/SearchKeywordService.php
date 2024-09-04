<?php

namespace App\Services;

use GuzzleHttp\Client;

class SearchKeywordService
{
    public function search($query)
    {
        $client = new Client();

        $response = $client->post('https://google.serper.dev/search', [
            'headers' => [
                'X-API-KEY' => $query['api_key'],
                'Content-Type' => 'application/json',
            ],
            'json' => $query['data'],
        ]);

        $res = json_decode($response->getBody()->getContents());

        $data = [];
        foreach ($query['data'] as $item) {
            foreach ($res as $ser) {
                $position = array_filter($ser->organic, function($value) use ($item) {
                    return str_contains($value->link, $item['domain']);
                });

                $firstPosition = reset($position);

                if ($firstPosition && isset($firstPosition->position)) {
                    $item['position'] = $firstPosition->position;
                }
            }
            $data[] = $item;
        }
        return $data;
    }
}
