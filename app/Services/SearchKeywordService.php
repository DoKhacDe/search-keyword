<?php

namespace App\Services;

use App\Jobs\DataKeywordJob;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

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
            $item['request_id'] = $query['request_id'];
            foreach ($res as $ser) {
                if ($ser->organic && count($ser->organic) > 0) {
                    $position = array_filter($ser->organic, function($value) use ($item) {
                        return str_contains($value->link, $item['domain']);
                    });

                    $firstPosition = reset($position);

                    if ($firstPosition && isset($firstPosition->position)) {
                        $item['position'] = $firstPosition->position;
                    }
                }
            }
            $data[] = $item;
        }

        DataKeywordJob::dispatch($data);
        return $data;
    }
}
