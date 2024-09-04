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

            $apiKey = $request->input('api_key');
            $query = [
                'data' => $keywords,
                'api_key' => $apiKey
            ];
            $responseBody = app(SearchKeywordService::class)->search($query);
            $request->session()->put('results', $responseBody);
            return view('search-keyword', [
                'results' => $responseBody
            ]);

        } catch (\Exception $e) {
            Log::error('Error during search: ' . $e->getMessage());
            return response()->json([
                'error' => 'Lỗi khi gửi yêu cầu tới API',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function export(Request $request)
    {
        $results = $request->session()->get('results', []);

        return Excel::download(new KeywordExport($results), 'results.xlsx');
    }
}
