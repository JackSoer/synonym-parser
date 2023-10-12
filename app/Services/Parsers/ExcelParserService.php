<?php

namespace App\Services\Parsers;

use Exception;
use Generator;
use Spatie\SimpleExcel\SimpleExcelReader;

class ExcelParserService
{
    public function getRows(string $filePath): Generator
    {
        if (!is_file($filePath)) {
            throw new Exception('File path is incorrect');
        }

        $reader = SimpleExcelReader::create($filePath);

        foreach ($reader->getRows() as $row) {
            yield $row;
        }
    }
}
