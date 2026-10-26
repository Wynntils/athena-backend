<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Mirrors HadesServer/src/main/kotlin/com/wynntils/server/voice/VoiceTables.kt.
 * The relay may create these tables first (Exposed createMissingTablesAndColumns), so we only
 * create when absent. The relay never writes discord_notified_at or comments; those are Athena-owned.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('hades_voice_reports')) {
            Schema::create('hades_voice_reports', function (Blueprint $table) {
                $table->increments('id');
                $table->uuid('reporter_id')->index();
                $table->uuid('target_id')->index();
                $table->text('reason');
                $table->text('world');
                $table->text('instance');
                $table->float('reporter_x', 24)->nullable();
                $table->float('reporter_y', 24)->nullable();
                $table->float('reporter_z', 24)->nullable();
                $table->float('target_x', 24)->nullable();
                $table->float('target_y', 24)->nullable();
                $table->float('target_z', 24)->nullable();
                $table->text('witness_ids');
                $table->bigInteger('audio_from')->nullable();
                $table->bigInteger('audio_to')->nullable();
                $table->text('reporter_audio_path')->nullable();
                $table->text('target_audio_path')->nullable();
                $table->bigInteger('created_at');
                $table->boolean('handled')->default(false);
                $table->text('handled_by')->nullable();
                $table->bigInteger('handled_at')->nullable();
            });
        }

        if (! Schema::hasColumn('hades_voice_reports', 'discord_notified_at')) {
            Schema::table('hades_voice_reports', function (Blueprint $table) {
                $table->bigInteger('discord_notified_at')->nullable();
            });
        }

        if (! Schema::hasColumn('hades_voice_reports', 'comments')) {
            Schema::table('hades_voice_reports', function (Blueprint $table) {
                $table->json('comments')->nullable();
            });
        }

        if (! Schema::hasTable('hades_voice_bans')) {
            Schema::create('hades_voice_bans', function (Blueprint $table) {
                $table->increments('id');
                $table->uuid('user_id')->index();
                $table->text('reason');
                $table->text('banned_by');
                $table->bigInteger('banned_at');
                $table->bigInteger('expires_at')->nullable();
                $table->bigInteger('lifted_at')->nullable();
            });
        }

        if (! Schema::hasTable('hades_voice_beta')) {
            Schema::create('hades_voice_beta', function (Blueprint $table) {
                $table->uuid('user_id')->primary();
            });
        }

        if (! Schema::hasTable('hades_voice_blocks')) {
            Schema::create('hades_voice_blocks', function (Blueprint $table) {
                $table->uuid('blocker_id');
                $table->uuid('blocked_id');
                $table->bigInteger('created_at');
                $table->primary(['blocker_id', 'blocked_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::table('hades_voice_reports', function (Blueprint $table) {
            $table->dropColumn(['discord_notified_at', 'comments']);
        });
    }
};
