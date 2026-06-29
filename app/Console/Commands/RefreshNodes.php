<?php

namespace App\Console\Commands;

use App\Models\Node;
use App\Services\WingsClient;
use App\Support\Format;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('nodes:refresh')]
#[Description('Query each node\'s Wings daemon and update its online status, memory and disk')]
class RefreshNodes extends Command
{
    /**
     * Poll every node and persist its detected state.
     */
    public function handle(WingsClient $wings): int
    {
        $nodes = Node::all();

        if ($nodes->isEmpty()) {
            $this->info('No nodes to refresh.');

            return self::SUCCESS;
        }

        foreach ($nodes as $node) {
            if ($wings->refresh($node)) {
                $this->line(sprintf(
                    '<info>online</info>  %s — %s RAM, %s disk',
                    $node->name,
                    Format::size($node->memory_mb),
                    Format::size($node->disk_mb),
                ));
            } else {
                $this->line(sprintf('<comment>offline</comment> %s (%s:%d)', $node->name, $node->fqdn, $node->daemon_port));
            }
        }

        return self::SUCCESS;
    }
}
