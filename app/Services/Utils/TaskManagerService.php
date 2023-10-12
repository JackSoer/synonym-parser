<?php

namespace App\Services\Utils;

class TaskManagerService
{
    private $maxConcurrentTasks;

    public function __construct($maxConcurrentTasks)
    {
        $this->maxConcurrentTasks = $maxConcurrentTasks;
    }

    public function processTasks(mixed $tasks, callable $taskCallback): void
    {
        $this->setupSignalHandler();

        $activeProcesses = 0;

        foreach ($tasks as $task) {
            while ($activeProcesses >= $this->maxConcurrentTasks) {
                pcntl_wait($status, WUNTRACED);
                $activeProcesses--;
            }

            $pid = pcntl_fork();

            if ($pid == -1) {
                die('Could not fork.');
            } elseif ($pid) {
                $activeProcesses++;
            } else {
                $this->setupSignalHandler();

                $taskCallback($task);
                exit(0);
            }
        }

        while ($activeProcesses > 0) {
            pcntl_wait($status, WUNTRACED);
            $activeProcesses--;
        }
    }

    private function setupSignalHandler(): void
    {
        pcntl_signal(SIGINT, function ($signo) {
            exit(0);
        });
    }
}
