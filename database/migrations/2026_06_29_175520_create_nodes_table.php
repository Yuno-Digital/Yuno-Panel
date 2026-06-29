<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A Node is a physical/virtual host machine that runs game servers.
     */
    public function up(): void
    {
        Schema::create('nodes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('fqdn');                       // hostname/IP of the daemon
            $table->unsignedSmallInteger('daemon_port')->default(8080);
            $table->boolean('is_online')->default(false);
            $table->unsignedBigInteger('memory_mb')->default(0);   // total RAM available
            $table->unsignedBigInteger('disk_mb')->default(0);     // total disk available
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nodes');
    }
};
