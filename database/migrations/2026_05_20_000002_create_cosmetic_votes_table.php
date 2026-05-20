<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cosmetic_votes', function (Blueprint $table) {
            $table->id();
            $table->uuid('cosmetic_id')->index();
            $table->uuid('user_id')->index();
            $table->tinyInteger('vote');
            $table->timestamps();

            $table->unique(['cosmetic_id', 'user_id']);
            $table->foreign('cosmetic_id')->references('id')->on('cosmetic_assets')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cosmetic_votes');
    }
};
