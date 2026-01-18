<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('auth_token')->unique()->index();
            $table->string('username', 16)->index();
            $table->string('password', 60)->nullable(); // Exact bcrypt length
            $table->string('account_type', 50)->default('NORMAL')->index();
            $table->string('donator_type', 50)->nullable();
            $table->bigInteger('last_activity')->nullable()->index();
            $table->string('latest_version', 30)->nullable();

            // Flexible data as JSONB
            $table->jsonb('discord_info')->nullable();
            $table->jsonb('cosmetic_info')->nullable();
            $table->jsonb('used_versions')->nullable();

            $table->rememberToken();
            $table->timestamps();
        });

        // Create indexes for nested JSON fields
        DB::statement("CREATE INDEX idx_discord_id ON users ((discord_info->>'id'))");
        DB::statement("CREATE INDEX idx_cape_texture ON users ((cosmetic_info->>'capeTexture'))");

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('sessions');
    }
};
