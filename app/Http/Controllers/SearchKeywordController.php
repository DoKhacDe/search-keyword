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
            $request->validate([
                'file' => 'required|mimes:xlsx,xls'
            ]);
            $import = new KeywordsImport();

            Excel::import($import, $request->file('file'));

            $keywords = $import->getKeywords();
            $chunks = array_chunk($keywords, 100);
            $apiKey = $request->input('api_key');
            $combinedResponseBody = [];
            foreach ($chunks as $chunk) {
                $query = [
                    'data' => $chunk,
                    'api_key' => $apiKey
                ];

                $responseBody = app(SearchKeywordService::class)->search($query);

                $combinedResponseBody = array_merge($combinedResponseBody, $responseBody);
            }
            $request->session()->put('results', $combinedResponseBody);
            return view('search-keyword', [
                'results' => $combinedResponseBody
            ]);

        } catch (\Exception $e) {
            Log::error('Error during search: ' . $e->getMessage());
            return view('search-keyword', [
                'error' => 'Lỗi khi gửi yêu cầu tới API',
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function export(Request $request)
    {
        $results = $request->session()->get('results', []);

        return Excel::download(new KeywordExport($results), 'results.xlsx');
    }
}
