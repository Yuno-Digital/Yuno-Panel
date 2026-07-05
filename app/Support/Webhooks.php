<?php

namespace App\Support;

use App\Models\Server;
use App\Models\Webhook;
use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * Delivers panel events to configured webhook endpoints.
 */
class Webhooks
{
    /**
     * Fire an event to every active webhook subscribed to it (global webhooks,
     * plus the given server's own webhooks). Delivery happens after the response
     * is sent so it never blocks the request; each payload is signed with the
     * webhook's secret (HMAC-SHA256).
     *
     * @param  array<string, mixed>  $data
     */
    public static function dispatch(string $event, array $data = [], ?Server $server = null): void
    {
        $hooks = Webhook::where('is_active', true)->get()
            ->filter(fn (Webhook $hook) => $hook->subscribesTo($event));

        if ($server !== null) {
            $serverHooks = $server->webhooks()->where('is_active', true)->get()
                ->filter(fn ($hook) => $hook->subscribesTo($event));
            $hooks = $hooks->concat($serverHooks);
        }

        if ($hooks->isEmpty()) {
            return;
        }

        $body = json_encode([
            'event' => $event,
            'timestamp' => now()->toIso8601String(),
            'data' => $data,
        ]);

        defer(function () use ($hooks, $event, $body) {
            foreach ($hooks as $hook) {
                try {
                    Http::timeout(5)
                        ->withHeaders([
                            'X-Yuno-Event' => $event,
                            'X-Yuno-Signature' => 'sha256='.hash_hmac('sha256', (string) $body, (string) $hook->secret),
                        ])
                        ->withBody((string) $body, 'application/json')
                        ->post($hook->url);
                } catch (Throwable) {
                    // Best effort: a failing endpoint must not affect the panel.
                }
            }
        });
    }
}
