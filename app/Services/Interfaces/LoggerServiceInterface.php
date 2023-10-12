<?php

namespace App\Services\Interfaces;

interface LoggerServiceInterface
{
    public static function log(array $options): void;
    public function getLoggerMode(): string;
    public function getLogFilePath(): string;
    public function setLogFilePath(string $filePath): void;
    public function setLogOptions(mixed $stream = null, int $level = null, string $message = null, array $context = null): void;
    public function logMessage(string $message, int $level = 200, array $context = []): void;
    public function logError(string $error): void;
}
