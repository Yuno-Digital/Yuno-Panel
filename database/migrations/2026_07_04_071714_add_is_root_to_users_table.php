<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The protected main admin: cannot be edited/deleted by other admins.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_root')->default(false)->after('is_admin');
        });

        // Mark the first (lowest-id) user as the main admin for existing installs.
        $firstId = DB::table('users')->orderBy('id')->value('id');
        if ($firstId) {
            DB::table('users')->where('id', $firstId)->update(['is_root' => true]);
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('is_root');
        });
    }
};
