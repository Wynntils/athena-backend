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
        Schema::create('crash_reports', function (Blueprint $table) {
            $table->id();
            $table->string('trace_hash')->unique();
            $table->text('trace');
            $table->jsonb('occurrences')->nullable();
            $table->jsonb('comments')->nullable();
            $table->integer('count')->default(0);
            $table->boolean('handled')->default(false);
            $table->timestamps();

            $table->index('trace_hash');
            $table->index('handled');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('crash_reports');
    }
};

