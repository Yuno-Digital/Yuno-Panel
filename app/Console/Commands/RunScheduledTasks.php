<?php

namespace App\Console\Commands;

use App\Models\ScheduledTask;
use App\Services\WingsClient;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('schedules:run')]
#[Description('Run any server scheduled tasks that are due')]
class RunScheduledTasks extends Command
{
    /**
     * Execute every active task whose next run time has passed.
     */
    public function handle(WingsClient $wings): int
    {
        $due = ScheduledTask::with('server.node')
            ->where('is_active', true)
            ->whereNotNull('next_run_at')
            ->where('next_run_at', '<=', now())
            ->get();

        foreach ($due as $task) {
            $task->run($wings);
            $this->line(sprintf('ran #%d "%s" on server %s', $task->id, $task->name, $task->server?->name ?? '?'));
        }

        $this->info($due->isEmpty() ? 'No scheduled tasks were due.' : 'Ran '.$due->count().' scheduled task(s).');

        return self::SUCCESS;
    }
}
