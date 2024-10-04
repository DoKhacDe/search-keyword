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

    public ?array $dataExport;

    public function __construct()
    {
        $this->dataExport = [];
    }

    public function view()
    {
        return view('search-keyword');
    }

    public function search(Request $request)
    {
        try {
            $keywords = $request->input('keywords');
            $apiKey = $request->input('api_key');
            $requestId = $request->input('id');
            $request->session()->put('request_id', $requestId);
            Log::info('ID: ' . $requestId);
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
            if (is_array($responseBody)) {
                $existingResults = $request->session()->get('results-'. $requestId, []);
                $mergedResults = array_merge($existingResults, $responseBody);
                $request->session()->put('results-'. $requestId, $mergedResults);
                Log::info('Merged Results: ', $mergedResults);
            } else {
                throw new \Exception('No valid response from the search service');
            }
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
        $requestId = $request->session()->get('request_id', '');
        $results = $request->session()->get('results-'. $requestId, []);
        return Excel::download(new KeywordExport($results), 'results.xlsx');
    }
}
