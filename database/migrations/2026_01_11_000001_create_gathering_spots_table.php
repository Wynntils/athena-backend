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
        Schema::create('gathering_spots', function (Blueprint $table) {
            $table->string('id')->primary(); // "x:y:z" format
            $table->string('type', 50);
            $table->string('material', 50);
            $table->bigInteger('last_seen');
            $table->jsonb('users'); // Array of UUIDs

            // Indexes for common queries
            $table->index(['type', 'material']);
            $table->index('last_seen');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gathering_spots');
    }
};

