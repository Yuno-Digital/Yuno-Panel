<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Egg icon: a URL or a data:image URI, shown next to the egg.
     */
    public function up(): void
    {
        Schema::table('eggs', function (Blueprint $table) {
            $table->longText('icon')->nullable()->after('description');
        });
    }

    public function down(): void
    {
        Schema::table('eggs', function (Blueprint $table) {
            $table->dropColumn('icon');
        });
    }
};
