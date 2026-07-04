<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * Only creates the initial admin account. Nodes and servers are added by
     * the admin through the panel (connect real Wings nodes, create real
     * servers) — no demo data is seeded.
     */
    public function run(): void
    {
        Role::firstOrCreate(['name' => 'Administrator'], ['permissions' => ['administrator']]);
        Role::firstOrCreate(['name' => 'Support'], ['permissions' => ['servers.manage', 'users.manage']]);

        User::firstOrCreate(
            ['email' => 'admin@yuno.local'],
            [
                'name' => 'Admin',
                'password' => Hash::make('password'),
                'is_admin' => true,
            ],
        );
    }
}
