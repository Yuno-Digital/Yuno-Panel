<?php

namespace App\Console\Commands;

use App\Services\Upgrader;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('yuno:upgrade {--dry-run : List the steps without running them}')]
#[Description('Upgrade the panel: pull the latest code, dependencies, migrations and assets')]
class Upgrade extends Command
{
    public function handle(Upgrader $upgrader): int
    {
        if ($this->option('dry-run')) {
            foreach ($upgrader->steps() as $step) {
                $this->line('$ '.implode(' ', $step['cmd']));
            }

            return self::SUCCESS;
        }

        $this->warn('Upgrading Yuno Panel…');

        [$ok] = $upgrader->run(fn (string $line) => $this->line($line));

        $this->newLine();
        $ok ? $this->info('Upgrade complete.') : $this->error('Upgrade failed — see output above.');

        return $ok ? self::SUCCESS : self::FAILURE;
    }
}
