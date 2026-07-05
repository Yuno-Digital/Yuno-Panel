<?php

namespace App\Models;

use App\Services\WingsClient;
use Carbon\Carbon;
use Cron\CronExpression;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Throwable;

/**
 * A time-based task on a server: a power action or console command run on a
 * cron schedule by the schedules:run command.
 */
#[Fillable(['name', 'action', 'payload', 'cron', 'is_active', 'last_run_at', 'next_run_at'])]
class ScheduledTask extends Model
{
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'last_run_at' => 'datetime',
            'next_run_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Server, $this>
     */
    public function server(): BelongsTo
    {
        return $this->belongsTo(Server::class);
    }

    /**
     * The next time this task should run per its cron expression, or null if the
     * expression is invalid.
     */
    public function computeNextRun(): ?Carbon
    {
        try {
            return Carbon::instance((new CronExpression($this->cron))->getNextRunDate(now()));
        } catch (Throwable) {
            return null;
        }
    }

    /**
     * Execute the task against its server's daemon, then reschedule it.
     */
    public function run(WingsClient $wings): void
    {
        try {
            $server = $this->server;
            if ($server !== null) {
                match ($this->action) {
                    'power' => $wings->power($server, $this->payload),
                    'command' => $wings->command($server, $this->payload),
                    default => null,
                };
            }
        } catch (Throwable $e) {
            report($e);
        }

        $this->forceFill([
            'last_run_at' => now(),
            'next_run_at' => $this->computeNextRun(),
        ])->save();
    }

    /**
     * A short human description of what this task does.
     */
    public function actionLabel(): string
    {
        return $this->action === 'power'
            ? __('Power: :a', ['a' => $this->payload])
            : __('Command: :c', ['c' => $this->payload]);
    }
}
