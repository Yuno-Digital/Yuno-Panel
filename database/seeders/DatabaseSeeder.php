<?php

namespace Database\Seeders;

use App\Models\Node;
use App\Models\Server;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@yuno.local',
            'password' => Hash::make('password'),
            'is_admin' => true,
        ]);

        $node = Node::create([
            'name' => 'Node-01',
            'fqdn' => 'node01.yuno.local',
            'daemon_port' => 8080,
            'is_online' => true,
            'memory_mb' => 32768,
            'disk_mb' => 512000,
            'description' => 'Demo node for local development.',
        ]);

        Server::create([
            'name' => 'Minecraft Survival',
            'node_id' => $node->id,
            'owner_id' => $admin->id,
            'status' => 'running',
            'memory_mb' => 4096,
            'disk_mb' => 10240,
            'port' => 25565,
        ]);

        Server::create([
            'name' => 'CS2 Competitive',
            'node_id' => $node->id,
            'owner_id' => $admin->id,
            'status' => 'offline',
            'memory_mb' => 2048,
            'disk_mb' => 30720,
            'port' => 27015,
        ]);
    }
}
