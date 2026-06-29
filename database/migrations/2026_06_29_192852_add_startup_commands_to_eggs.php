<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Eggs can offer several startup commands to pick from; `startup` keeps the
     * default (first) one for back-compat.
     */
    public function up(): void
    {
        Schema::table('eggs', function (Blueprint $table) {
            $table->json('startup_commands')->nullable()->after('startup');
        });
    }

    public function down(): void
    {
        Schema::table('eggs', function (Blueprint $table) {
            $table->dropColumn('startup_commands');
        });
    }
};
