<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Expand eggs into the full Pelican/Pterodactyl-style template: docker
     * image map, tags/features, process configuration, install script and a
     * related table of configurable variables.
     */
    public function up(): void
    {
        Schema::table('eggs', function (Blueprint $table) {
            $table->uuid('uuid')->nullable()->after('id');

            // Metadata
            $table->json('tags')->nullable()->after('author');
            $table->json('features')->nullable()->after('tags');
            $table->json('docker_images')->nullable()->after('docker_image');
            $table->json('file_denylist')->nullable()->after('docker_images');
            $table->string('update_url')->nullable()->after('file_denylist');

            // Copy settings from another egg
            $table->foreignId('config_from')->nullable()->after('startup')
                ->constrained('eggs')->nullOnDelete();

            // Process management (raw JSON blocks)
            $table->text('config_startup')->nullable()->after('config_from');  // start config {}
            $table->string('config_stop')->nullable()->after('config_startup'); // stop command
            $table->text('config_files')->nullable()->after('config_stop');     // configuration files {}
            $table->text('config_logs')->nullable()->after('config_files');     // log configuration {}

            // Install script
            $table->foreignId('copy_script_from')->nullable()->after('config_logs')
                ->constrained('eggs')->nullOnDelete();
            $table->string('script_container')->default('ghcr.io/pelican-eggs/installers:debian')->after('copy_script_from');
            $table->string('script_entry')->default('bash')->after('script_container');
            $table->boolean('script_is_privileged')->default(true)->after('script_entry');
            $table->longText('script_install')->nullable()->after('script_is_privileged');
            // Note: the legacy docker_image column is kept and populated from the
            // first entry of docker_images for display/back-compat.
        });

        Schema::create('egg_variables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('egg_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('env_variable');               // the {{ ENV }} name
            $table->text('default_value')->nullable();
            $table->boolean('user_viewable')->default(true);
            $table->boolean('user_editable')->default(true);
            $table->string('rules')->default('nullable|string'); // validation rules
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('egg_variables');

        Schema::table('eggs', function (Blueprint $table) {
            $table->dropConstrainedForeignId('config_from');
            $table->dropConstrainedForeignId('copy_script_from');
            $table->dropColumn([
                'uuid', 'tags', 'features', 'docker_images', 'file_denylist', 'update_url',
                'config_startup', 'config_stop', 'config_files', 'config_logs',
                'script_container', 'script_entry', 'script_is_privileged', 'script_install',
            ]);
        });
    }
};
