<?php

namespace App\Console\Commands;

use App\Services\Parsers\ParserService;
use Config;
use Illuminate\Console\Command;

class ParserStart extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'parser:start {file} {loggerMode=debug} {processAmount=100}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Start parser';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $filePath = storage_path("app/samples/{$this->argument('file')}.xlsx");

        $loggerMode = $this->argument('loggerMode');
        $processAmount = (int) $this->argument('processAmount');

        Config::set('parser.logger_mode', $loggerMode);

        $parser = new ParserService($processAmount);

        $parser->run($filePath);
    }
}
