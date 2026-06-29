<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The bearer token the panel uses to authenticate against the node's
     * Wings daemon (needed for auto-detecting memory/disk and power actions).
     */
    public function up(): void
    {
        Schema::table('nodes', function (Blueprint $table) {
            $table->string('daemon_token')->nullable()->after('daemon_port');
        });
    }

    public function down(): void
    {
        Schema::table('nodes', function (Blueprint $table) {
            $table->dropColumn('daemon_token');
        });
    }
};
