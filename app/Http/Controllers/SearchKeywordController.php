<?php

namespace App\Http\Controllers;

use App\Exports\KeywordExport;
use App\Imports\KeywordsImport;
use App\Models\Keyword;
use App\Services\SearchKeywordService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
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

            Cache::put('request_id', $requestId);
            $formatData = [];
            foreach (json_decode($keywords) as $keyword) {
                $formatData[] = [
                    'q' => $keyword->keyword,
                    'domain' => $keyword->domain,
                    'gl' => $keyword->country,
                    'hl' => $keyword->language,
                    'location' => 'United States',
                    'num' => 100,
                ];
            }

            $query = [
                'data' => $formatData,
                'api_key' => $apiKey,
                'request_id' => $requestId
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
        $requestId = Cache::get('request_id', '');
        Log::info('Request id: '.$requestId);
        $results = Keyword::where('request_id', $requestId)->get()->toArray();
        if ($results && count($results) > 0) {
            $data = Excel::download(new KeywordExport($results), 'results.xlsx');
            Cache::put('request_id', '');
            return $data;
        } else {
            throw new \Exception('Export lỗi. Vui lòng nhấn F5 để thử lại!');
        }
    }
}
