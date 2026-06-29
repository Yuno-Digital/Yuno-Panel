<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A Server is a single game server instance running on a Node, owned by a User.
     */
    public function up(): void
    {
        Schema::create('servers', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();               // public identifier
            $table->string('name');
            $table->foreignId('node_id')->constrained()->cascadeOnDelete();
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $table->string('status')->default('offline'); // offline|starting|running|stopping
            $table->unsignedBigInteger('memory_mb')->default(1024);
            $table->unsignedBigInteger('disk_mb')->default(5120);
            $table->unsignedSmallInteger('port')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('servers');
    }
};
