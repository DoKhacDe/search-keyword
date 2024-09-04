<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToArray;

class KeywordsImport implements ToArray
{
    protected $keywords = [];

    public function array(array $array)
    {
        $keywords = [];
        foreach ($array as $index => $row) {
            if ($index === 0) {
                continue;
            }

            if (isset($row[0], $row[1], $row[2], $row[3])) {
                $this->keywords[] = [
                    'q' => $row[0],
                    'num' => 100,
                    'domain' => $row[1],
                    'gl' => $row[2],
                    'hl' => $row[3],
                ];
            }
        }
    }

    public function getKeywords()
    {
        return $this->keywords;
    }
}

