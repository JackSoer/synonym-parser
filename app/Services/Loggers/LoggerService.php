<?php

namespace App\Services\Loggers;

use App\Services\Interfaces\LoggerServiceInterface;
use Exception;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class LoggerService implements LoggerServiceInterface
{
    protected $loggerMode;
    protected $logFilePath;
    private $logOptions;

    public function __construct()
    {
        $this->loggerMode = config('parser')['logger_mode'];

        $this->logOptions = [
            'loggerName' => 'Logger',
            'stream' => STDOUT,
            'level' => 100,
            'string' => '',
            'context' => [],
        ];

        $logFilePath = storage_path('app/logs/synonym-parser.log');

        $this->logFilePath = $logFilePath;
    }

    public function setLogFilePath(string $filePath): void
    {
        if (is_file($filePath)) {
            $this->logFilePath = $filePath;
        } else {
            throw new Exception('File path is incorrect');
        }
    }

    public function setLogOptions(mixed $stream = null, int $level = null, string $message = null, array $context = null): void
    {
        $this->logOptions['stream'] = $stream ?? $this->logOptions['stream'];
        $this->logOptions['level'] = $level ?? $this->logOptions['level'];
        $this->logOptions['string'] = $message ?? $this->logOptions['string'];
        $this->logOptions['context'] = $context ?? $this->logOptions['context'];
    }

    public function getLogFilePath(): string
    {
        return $this->logFilePath;
    }

    public function getLoggerMode(): string
    {
        return $this->loggerMode;
    }

    public static function log(array $options): void
    {
        $logToConsole = new Logger($options['loggerName']);
        $logToConsole->pushHandler(new StreamHandler($options['stream'], $options['level']));

        if ($options['level'] === 100) {
            $logToConsole->debug($options['string'], $options['context']);
        } else if ($options['level'] === 200) {
            $logToConsole->info($options['string'], $options['context']);
        } else if ($options['level'] === 400) {
            $logToConsole->error($options['string'], $options['context']);
        }
    }

    public function logError(string $error): void
    {
        $message = "Error: $error";

        $this->logMessage($message, level: 400);
    }

    public function logMessage(string $message, int $level = 200, array $context = []): void
    {
        $this->setLogOptions(message: $message, context: $context);

        if ($this->loggerMode === 'debug') {
            $this->setLogOptions(stream: STDOUT, level: $level);
            LoggerService::log($this->logOptions);
        } else if ($this->loggerMode === 'file') {
            $this->setLogOptions(stream: $this->logFilePath, level: $level);
            LoggerService::log($this->logOptions);
        }
    }
}
