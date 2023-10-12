<?php

namespace App\Services\Parsers;

use App\Models\Synonym;
use App\Models\SynonymGroup;
use App\Models\SynonymWord;
use App\Models\VerbForm;
use App\Services\Interfaces\ParserServiceInterface;
use App\Services\Loggers\SynonymParserLoggerService;
use App\Services\Utils\TaskManagerService;
use Exception;

class ParserService implements ParserServiceInterface
{
    private $synonymParserLoggerService;
    private $taskManager;

    public function __construct(int $processAmount)
    {
        $this->taskManager = new TaskManagerService($processAmount);
        $this->synonymParserLoggerService = new SynonymParserLoggerService();
    }

    public function run(string $filePath): void
    {
        try {
            $excelParser = new ExcelParserService();

            $rows = $excelParser->getRows($filePath);

            $this->echoParsingStartMessages();

            $this->taskManager->processTasks($rows, function ($synonymData) {
                $this->saveSynonymDataToDB($synonymData);
            });

            echo 'Parsing ended...' . PHP_EOL;
        } catch (Exception $err) {
            $this->synonymParserLoggerService->logError($err->getMessage());
        }
    }

    private function echoParsingStartMessages(): void
    {
        echo 'Receiving file data for parsing...' . PHP_EOL . "It's may take a few minutes..." . PHP_EOL;
        echo 'Parsing started...' . PHP_EOL;

        if ($this->synonymParserLoggerService->getLoggerMode() === 'file') {
            $logFilePath = $this->synonymParserLoggerService->getLogFilePath();
            echo "You can check the parsing process in the log file here - $logFilePath" . PHP_EOL;
        };
    }

    private function saveSynonymDataToDB(array $synonymData): void
    {
        try {
            $synonymFormattedData = $this->getSynonymFormattedData($synonymData);

            foreach ($synonymFormattedData['synonymWords'] as $synonymWordData) {
                $this->synonymParserLoggerService->logWord($synonymWordData['text'], $synonymWordData['uppercase']);
                $synonymWord = SynonymWord::create($synonymWordData);

                $this->saveSynonymGroups($synonymWord, $synonymFormattedData['synonymGroupWords']);
                $this->saveSynonyms($synonymWord, $synonymFormattedData['synonymsWords']);
                $this->saveVerbForms($synonymWord, $synonymFormattedData['verbForms']);
            }
        } catch (Exception $err) {
            $this->synonymParserLoggerService->logError($err->getMessage());
        }
    }

    private function saveSynonymGroups($mainSynonymWord, $synonymGroupWords)
    {
        foreach ($synonymGroupWords as $synonymGroupWord) {
            $this->synonymParserLoggerService->logWord($synonymGroupWord['text'], $synonymGroupWord['uppercase']);
            $synonymGroupWord = SynonymWord::create($synonymGroupWord);

            if ($synonymGroupWord) {
                $synonymGroup = new SynonymGroup();
                $synonymGroup->word_id = $mainSynonymWord->id;
                $synonymGroup->group_id = $synonymGroupWord->id;
                $synonymGroup->save();
            }
        }
    }

    private function saveSynonyms($mainSynonymWord, $synonymsWords)
    {
        foreach ($synonymsWords as $synonymWordData) {
            $this->synonymParserLoggerService->logWord($synonymWordData['text'], $synonymWordData['uppercase']);
            $synonymWord = SynonymWord::create($synonymWordData);

            if ($synonymWord) {
                $synonym = new Synonym();
                $synonym->word_id = $mainSynonymWord->id;
                $synonym->synonym_id = $synonymWord->id;
                $synonym->save();
            }
        }
    }

    private function saveVerbForms($mainSynonymWord, $verbForms)
    {
        foreach ($verbForms as $verbFormData) {
            $this->synonymParserLoggerService->logVerbForm($verbFormData['title'], $verbFormData['type'], $verbFormData['content']);

            $verbFormData['word_id'] = $mainSynonymWord->id;

            VerbForm::create($verbFormData);
        }
    }

    private function getSynonymFormattedData(array $synonymData): array
    {
        $synonymWords = $this->getSynonymWords($synonymData, 'Word');
        $synonymGroupWords = $this->getSynonymWords($synonymData, 'Synonym Group');
        $synonymsWords = $this->getSynonymWords($synonymData, 'Synonyms');
        $verbForms = $this->getVerbForms($synonymData);

        $synonymFormattedData = [
            'synonymWords' => $synonymWords,
            'synonymGroupWords' => $synonymGroupWords,
            'synonymsWords' => $synonymsWords,
            'verbForms' => $verbForms,
        ];

        return $synonymFormattedData;
    }

    private function getSynonymWords(array $synonymData, string $wordsField): array
    {
        $synonymWords = [];
        $languageCombination = $synonymData['Language'];
        $langISOCode = $this->getISOCode($languageCombination);

        $words = explode(';', $synonymData[$wordsField]);

        foreach ($words as $word) {
            $trimmedWord = trim($word);
            $upperCaseWord = mb_strtoupper($trimmedWord, 'UTF-8');

            $synonymWord = [
                'lang' => $langISOCode,
                'text' => $trimmedWord,
                'uppercase' => $upperCaseWord,
            ];

            $synonymWords[] = $synonymWord;
        }

        return $synonymWords;
    }

    private function getISOCode(string $languageCombination): string
    {
        $words = explode('-', $languageCombination);

        $languages = config('languages');

        $isoCode = strtolower(array_search($words[0], $languages));

        return $isoCode;
    }

    private function getVerbForm(string $conjugations): array
    {
        $type = strtolower(explode(' ', $conjugations)[0]);
        $conjugationsExlodedByColons = explode(':', $conjugations);
        $title = $conjugationsExlodedByColons[0];
        $content = trim($conjugationsExlodedByColons[1]);

        $verbForm = [
            'type' => $type,
            'title' => $title,
            'content' => $content,
        ];

        return $verbForm;
    }

    private function getVerbForms(array $synonymData): array
    {
        $conjugations = $this->getConjugations($synonymData);

        $verbForms = [];

        foreach ($conjugations as $conjugation) {
            if ($conjugation) {
                $verbForm = $this->getVerbForm($conjugation);

                $verbForms[] = $verbForm;
            }
        }

        return $verbForms;
    }

    private function getConjugations(array $synonymData): array
    {
        $conjugations = [];

        foreach ($synonymData as $key => $value) {
            if (strpos($key, 'conjugations') === 0) {
                $conjugations[$key] = $value;
            }
        }

        return $conjugations;
    }
}
