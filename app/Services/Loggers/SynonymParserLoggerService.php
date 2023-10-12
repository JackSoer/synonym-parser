<?php

namespace App\Services\Loggers;

class SynonymParserLoggerService extends LoggerService
{
    public function logWord(string $word, string $uppercaseWord): void
    {
        $message = "Saving synonym word - $word...";
        $context = ['Word' => $word, 'Uppercase word' => $uppercaseWord];

        $this->logMessage($message, context: $context);
    }

    public function logVerbForm(string $title, string $type, string $content): void
    {
        $message = "Saving verb form - $title...";
        $context = ['Title' => $title, 'Type' => $type, 'Content' => $content];

        $this->logMessage($message, context: $context);
    }
}
