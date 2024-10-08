<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class KeywordExport implements FromArray, WithHeadings
{
    protected $results;

    public function __construct($results)
    {
        $this->results = $results;
    }

    public function array(): array
    {
        return array_map(function($item) {
            return [
                'keyword' => $item['q'] ?? 'not found',
                'domain' => $item['domain'] ?? 'not found',
                'position' => $item['position'] ?? 'not found'
            ];
        }, $this->results);
    }

    public function headings(): array
    {
        return [
            'Từ khóa',
            'Domain',
            'Thứ hạng',
        ];
    }
}
