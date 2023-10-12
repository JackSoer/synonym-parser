<?php

namespace App\Services\Interfaces;

interface ParserServiceInterface
{
    public function run(string $filePath): void;
}
