<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Server icon: a URL or data:image value; falls back to the egg's icon.
     */
    public function up(): void
    {
        Schema::table('servers', function (Blueprint $table) {
            $table->longText('icon')->nullable()->after('name');
        });
    }

    public function down(): void
    {
        Schema::table('servers', function (Blueprint $table) {
            $table->dropColumn('icon');
        });
    }
};
