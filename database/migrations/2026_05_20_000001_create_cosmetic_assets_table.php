<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cosmetic_assets', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('sha', 40)->unique();
            $table->string('type', 20)->default('texture');
            $table->string('slot', 20)->default('back');
            $table->string('status', 20)->default('queued')->index();
            $table->uuid('uploader_id')->nullable()->index();
            $table->string('name', 80)->nullable();
            $table->string('visibility', 20)->default('public');
            $table->jsonb('tags')->default('[]');
            $table->jsonb('pending_tags')->nullable();
            $table->unsignedSmallInteger('width')->nullable();
            $table->unsignedSmallInteger('height')->nullable();
            $table->unsignedInteger('equip_count')->default(0);
            $table->timestamp('uploaded_at')->nullable();
            $table->string('pending_name', 80)->nullable();
            $table->string('pending_visibility', 20)->nullable();
            $table->timestamps();

            $table->foreign('uploader_id')->references('id')->on('users')->nullOnDelete();
        });

        if (DB::getDriverName() === 'pgsql') {
            DB::statement('CREATE INDEX idx_cosmetic_assets_tags ON cosmetic_assets USING gin(tags)');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('cosmetic_assets');
    }
};
