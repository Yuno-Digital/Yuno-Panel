<?php

namespace Database\Seeders;

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
