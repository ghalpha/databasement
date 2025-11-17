<?php

namespace App\Services\Backup;

use App\Exceptions\ShellProcessFailed;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;

class ShellProcessor
{
    public function process(Process $process): string
    {
        $process->setTimeout(null);
        $process->run();

        if (! $process->isSuccessful()) {
            Log::error($process->getCommandLine()."\n".$process->getErrorOutput());
            throw new ShellProcessFailed($process->getErrorOutput());
        }

        return $process->getOutput();
    }
}
