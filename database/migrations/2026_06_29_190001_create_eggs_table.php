<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * An Egg is a reusable server template: which Docker image to run and how
     * to start it. Servers are created from an egg.
     */
    public function up(): void
    {
        Schema::create('eggs', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('author')->nullable();
            $table->text('description')->nullable();
            $table->string('docker_image');
            $table->text('startup');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('eggs');
    }
};
