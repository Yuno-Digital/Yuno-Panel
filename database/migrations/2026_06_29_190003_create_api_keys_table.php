<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * API keys belonging to a user. "application" keys are admin-level keys for
     * managing the panel; "client" keys act on behalf of the user's own
     * resources. Only a hash of the secret is stored.
     */
    public function up(): void
    {
        Schema::create('api_keys', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('key_type');                 // application | client
            $table->string('identifier')->unique();     // public, non-secret prefix
            $table->string('token');                    // sha256 hash of the secret
            $table->string('memo')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('api_keys');
    }
};
