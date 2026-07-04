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
        // The single default role: full access, and it cannot be deleted.
        $admin = Role::firstOrCreate(['name' => 'Admin'], ['permissions' => ['administrator'], 'is_default' => true]);
        $admin->update(['permissions' => ['administrator'], 'is_default' => true]);

        $user = User::firstOrCreate(
            ['email' => 'admin@yuno.local'],
            [
                'name' => 'Admin',
                'password' => Hash::make('password'),
                'is_admin' => true,
                'role_id' => $admin->id,
            ],
        );

        // Ensure the admin account always carries the Admin role.
        if ($user->role_id !== $admin->id) {
            $user->update(['role_id' => $admin->id]);
        }
    }
}
