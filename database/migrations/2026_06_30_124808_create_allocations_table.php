<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * An allocation is an IP:port on a node that a server can be bound to.
     * Like Pelican, allocations are created on the node first, then assigned.
     */
    public function up(): void
    {
        Schema::create('allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('node_id')->constrained()->cascadeOnDelete();
            $table->string('ip')->default('0.0.0.0');
            $table->unsignedInteger('port');
            $table->foreignId('server_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();

            $table->unique(['node_id', 'ip', 'port']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('allocations');
    }
};
