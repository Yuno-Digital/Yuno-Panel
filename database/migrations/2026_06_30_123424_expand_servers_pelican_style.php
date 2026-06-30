<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Make servers egg-based like Pelican: an egg, a chosen docker image and
     * startup command, more resource limits and per-server variable values.
     */
    public function up(): void
    {
        Schema::table('servers', function (Blueprint $table) {
            $table->foreignId('egg_id')->nullable()->after('owner_id')->constrained()->nullOnDelete();
            $table->string('docker_image')->nullable()->after('egg_id');
            $table->text('startup')->nullable()->after('docker_image');
            $table->unsignedInteger('cpu')->default(0)->after('disk_mb');   // percent, 0 = unlimited
            $table->unsignedBigInteger('swap_mb')->default(0)->after('cpu');
        });

        Schema::create('server_variables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('server_id')->constrained()->cascadeOnDelete();
            $table->foreignId('egg_variable_id')->constrained()->cascadeOnDelete();
            $table->text('variable_value')->nullable();
            $table->timestamps();

            $table->unique(['server_id', 'egg_variable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('server_variables');

        Schema::table('servers', function (Blueprint $table) {
            $table->dropConstrainedForeignId('egg_id');
            $table->dropColumn(['docker_image', 'startup', 'cpu', 'swap_mb']);
        });
    }
};
