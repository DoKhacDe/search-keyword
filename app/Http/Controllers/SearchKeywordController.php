<?php

namespace App\Http\Controllers;

use App\Exports\KeywordExport;
use App\Imports\KeywordsImport;
use App\Services\SearchKeywordService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class SearchKeywordController extends Controller
{
    public function view() {
        return view('search-keyword');
    }

    public function search(Request $request)
    {
        try {

           $keywords = $request->input('keywords');
           $apiKey = $request->input('api_key');
           $formatData = [];
           foreach (json_decode($keywords) as $keyword) {
               $formatData[] = [
                 'q' => $keyword->keyword,
                 'domain' => $keyword->domain,
                 'gl' => $keyword->country,
                 'hl' => $keyword->language,
                 'num' => 100,
               ];
           }

           $query = [
               'data' => $formatData,
               'api_key' => $apiKey
           ];

           $responseBody = app(SearchKeywordService::class)->search($query);

            if (!$responseBody) {
                throw new \Exception('No response from the search service');
            }

            return response()->json($responseBody, 201);

        } catch (\Exception $e) {
            Log::error('Error during search: ' . $e->getMessage());
            return response()->json([
                'code' => 400,
                'error' => 'Lỗi khi gửi yêu cầu tới API',
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function export(Request $request)
    {
        $keywords = json_decode($request->input('keywords'), true);
        $testData = [
            [
                'q' => 'test keyword1',
                'domain' => 'http://example.com',
                'position' => 1,
            ],
            [
                'q' => 'test keyword2',
                'domain' => 'http://example.com',
                'position' => 2,
            ]
        ];
        return Excel::download(new KeywordExport($testData), 'keywords.xlsx');
    }
}
