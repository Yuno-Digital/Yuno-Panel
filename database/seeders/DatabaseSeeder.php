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
                'is_root' => true,
                'role_id' => $admin->id,
            ],
        );

        // Ensure the admin account carries the Admin role and, if no main admin
        // exists yet, mark it as the protected main admin.
        $user->update([
            'role_id' => $admin->id,
            'is_root' => $user->is_root || ! User::where('is_root', true)->exists(),
        ]);
    }
}
