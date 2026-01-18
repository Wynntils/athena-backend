<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MigrateFromMongoDB extends Command
{
    protected $signature = 'mongo:migrate
                            {table? : Specific table to migrate (users, guilds, gathering_spots, api_keys, servers, crash_reports, patreon_api)}
                            {--batch-size=500 : Number of records per batch}
                            {--skip=0 : Number of records to skip (for resuming)}
                            {--verify : Verify data after migration}
                            {--force : Skip confirmation prompts}';

    protected $description = 'Migrate data from MongoDB to PostgreSQL';

    private array $stats = [];
    private array $errors = [];

    public function handle(): int
    {
        if (! $this->option('force')) {
            if (! $this->confirm('This will migrate data from MongoDB to PostgreSQL. Continue?')) {
                $this->info('Migration cancelled.');
                return 1;
            }
        }

        $table = $this->argument('table');
        $batchSize = (int) $this->option('batch-size');
        $skip = (int) $this->option('skip');

        $this->info('🚀 Starting MongoDB to PostgreSQL migration...');
        $this->info('Batch size: ' . $batchSize);
        if ($skip > 0) {
            $this->info('Skipping first: ' . number_format($skip) . ' records');
        }
        $this->newLine();

        // Test connections
        if (!$this->testConnections()) {
            return 1;
        }

        if ($table) {
            $this->migrateTable($table, $batchSize, $skip);
        } else {
            if ($skip > 0) {
                $this->warn('⚠️  Skip option is only available when migrating a specific table');
                $this->newLine();
            }

            // Migrate in dependency order
            $tables = [
                'users',
                'guilds',
                'gathering_spots',
                'api_keys',
                'servers',
                'crash_reports',
                'patreon_api',
            ];

            foreach ($tables as $tableName) {
                $this->migrateTable($tableName, $batchSize, 0);
                $this->newLine();
            }
        }

        $this->displayStats();

        if (! empty($this->errors)) {
            $this->displayErrors();
        }

        if ($this->option('verify')) {
            $this->newLine();
            $this->verify();
        }

        $this->newLine();
        $this->info('✅ Migration completed! ');

        return 0;
    }

    private function testConnections(): bool
    {
        $this->info('Testing database connections...');

        try {
            $mongoCount = DB::connection('mongodb')->table('users')->count();
            $this->info("  ✅ MongoDB connected (found {$mongoCount} users)");
        } catch (\Exception $e) {
            $this->error('  ❌ MongoDB connection failed:  ' . $e->getMessage());
            return false;
        }

        try {
            DB::connection('pgsql')->select('SELECT 1');
            $this->info('  ✅ PostgreSQL connected');
        } catch (\Exception $e) {
            $this->error('  ❌ PostgreSQL connection failed: ' . $e->getMessage());
            return false;
        }

        $this->newLine();
        return true;
    }

    private function migrateTable(string $table, int $batchSize, int $skip = 0): void
    {
        $this->info("📦 Migrating {$table}...");

        $method = 'migrate' . Str::studly($table);

        if (! method_exists($this, $method)) {
            $this->error("  ❌ No migration method found for {$table}");
            $this->errors[] = "No migration method for {$table}";
            return;
        }

        $startTime = microtime(true);

        try {
            $count = $this->$method($batchSize, $skip);
            $duration = round(microtime(true) - $startTime, 2);

            $this->stats[$table] = [
                'count' => $count,
                'duration' => $duration,
                'status' => 'success',
            ];

            $this->info("  ✅ Migrated {$count} records in {$duration}s");
        } catch (\Exception $e) {
            $duration = round(microtime(true) - $startTime, 2);
            $errorMsg = $e->getMessage();

            $this->stats[$table] = [
                'count' => 0,
                'duration' => $duration,
                'status' => 'failed',
                'error' => $errorMsg,
            ];

            $this->error("  ❌ Failed:  " . $errorMsg);
            $this->errors[] = "{$table}:  {$errorMsg}";
        }
    }

    private function migrateUsers(int $batchSize, int $skip = 0): int
    {
        $mongodb = DB::connection('mongodb');
        $pgsql = DB::connection('pgsql');

        $total = $mongodb->table('users')->count();

        if ($total === 0) {
            $this->warn('  ⚠️  No users found in MongoDB');
            return 0;
        }

        $migrated = 0;
        $skipped = 0;
        $remaining = $total - $skip;
        $bar = $this->output->createProgressBar($remaining);

        if ($skip > 0) {
            $this->info("  Resuming from record " . number_format($skip) . " of " . number_format($total));
        }

        $bar->start();

        $mongodb->table('users')->orderBy('_id')->skip($skip)->chunk($batchSize, function ($users) use ($pgsql, &$migrated, &$skipped, $bar) {
            $data = [];

            foreach ($users as $user) {
                $user = (array) $user;

                // Validate user ID is a valid UUID
                $userId = $user['id'] ?? null;
                if (!$userId || !Str::isUuid($userId)) {
                    $this->errors[] = "users: Skipped user with invalid ID: {$userId} (username: " . ($user['username'] ?? 'Unknown') . ")";
                    $skipped++;
                    $bar->advance(1);
                    continue;
                }

                // Ensure auth_token is a valid UUID
                $authToken = $user['authToken'] ?? null;
                if (!$authToken || !Str::isUuid($authToken)) {
                    $authToken = Str::uuid()->toString();
                }

                // Map account type
                $accountType = $this->mapAccountType($user['accountType'] ?? 'NORMAL');
                $donatorType = $this->mapDonatorType($user['donatorType'] ?? null);

                $data[] = [
                    'id' => $userId,
                    'auth_token' => $authToken,
                    'username' => $user['username'] ?? 'Unknown',
                    'password' => $user['password'] ??  null,
                    'account_type' => $accountType,
                    'donator_type' => $donatorType,
                    'last_activity' => $user['lastActivity'] ?? null,
                    'latest_version' => $user['latestVersion'] ?? null,
                    'discord_info' => isset($user['discordInfo']) ? json_encode($user['discordInfo']) : null,
                    'cosmetic_info' => isset($user['cosmeticInfo']) ? json_encode($user['cosmeticInfo']) : null,
                    'used_versions' => isset($user['usedVersions']) ? json_encode($user['usedVersions']) : null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            // Use upsert for idempotency
            foreach ($data as $row) {
                try {
                    $pgsql->table('users')->updateOrInsert(
                        ['id' => $row['id']],
                        $row
                    );
                    $migrated++;
                    $bar->advance(1);
                } catch (\Exception $e) {
                    $this->errors[] = "users: Failed to insert user {$row['id']} ({$row['username']}): " . $e->getMessage();
                    $skipped++;
                    $bar->advance(1);
                }
            }
        });

        $bar->finish();
        $this->newLine();

        if ($skipped > 0) {
            $this->warn("  ⚠️  Skipped {$skipped} invalid records");
        }

        return $migrated;
    }

    private function migrateGuilds(int $batchSize, int $skip = 0): int
    {
        $mongodb = DB::connection('mongodb');
        $pgsql = DB::connection('pgsql');

        $total = $mongodb->table('guilds')->count();

        if ($total === 0) {
            $this->warn('  ⚠️  No guilds found in MongoDB');
            return 0;
        }

        $migrated = 0;
        $skipped = 0;
        $remaining = $total - $skip;
        $bar = $this->output->createProgressBar($remaining);

        if ($skip > 0) {
            $this->info("  Resuming from record " . number_format($skip) . " of " . number_format($total));
        }

        $bar->start();

        $mongodb->table('guilds')->orderBy('_id')->skip($skip)->chunk($batchSize, function ($guilds) use ($pgsql, &$migrated, &$skipped, $bar) {
            $data = [];

            foreach ($guilds as $guild) {
                $guild = (array) $guild;

                // Validate guild ID is not null
                $guildId = $guild['id'] ?? null;
                if (!$guildId || empty($guildId)) {
                    $this->errors[] = "guilds: Skipped guild with invalid ID: {$guildId} (prefix: " . ($guild['prefix'] ?? 'Unknown') . ")";
                    $skipped++;
                    $bar->advance(1);
                    continue;
                }

                $data[] = [
                    'id' => $guildId,
                    'prefix' => $guild['prefix'] ??  'NONE',
                    'color' => $guild['color'] ?? null,
                ];
            }

            foreach ($data as $row) {
                try {
                    $pgsql->table('guilds')->updateOrInsert(
                        ['id' => $row['id']],
                        $row
                    );
                    $migrated++;
                    $bar->advance(1);
                } catch (\Exception $e) {
                    $this->errors[] = "guilds: Failed to insert guild {$row['id']} ({$row['prefix']}): " . $e->getMessage();
                    $skipped++;
                    $bar->advance(1);
                }
            }
        });

        $bar->finish();
        $this->newLine();

        if ($skipped > 0) {
            $this->warn("  ⚠️  Skipped {$skipped} invalid records");
        }

        return $migrated;
    }

    private function migrateGatheringSpots(int $batchSize, int $skip = 0): int
    {
        $mongodb = DB::connection('mongodb');
        $pgsql = DB::connection('pgsql');

        $total = $mongodb->table('gathering_spots')->count();

        if ($total === 0) {
            $this->warn('  ⚠️  No gathering spots found in MongoDB');
            return 0;
        }

        $migrated = 0;
        $skipped = 0;
        $remaining = $total - $skip;
        $bar = $this->output->createProgressBar($remaining);

        if ($skip > 0) {
            $this->info("  Resuming from record " . number_format($skip) . " of " . number_format($total));
        }

        $bar->start();

        $mongodb->table('gathering_spots')->orderBy('_id')->skip($skip)->chunk($batchSize, function ($spots) use ($pgsql, &$migrated, &$skipped, $bar) {
            $data = [];

            foreach ($spots as $spot) {
                $spot = (array) $spot;

                // Validate spot ID is not null
                $spotId = $spot['id'] ?? null;
                if (!$spotId || empty($spotId)) {
                    $this->errors[] = "gathering_spots: Skipped spot with invalid ID: {$spotId}";
                    $skipped++;
                    $bar->advance(1);
                    continue;
                }

                $data[] = [
                    'id' => $spotId,
                    'type' => $spot['type'] ??  '',
                    'material' => $spot['material'] ??  '',
                    'last_seen' => $spot['lastSeen'] ?? 0,
                    'users' => isset($spot['users']) ? json_encode($spot['users']) : json_encode([]),
                ];
            }

            foreach ($data as $row) {
                try {
                    $pgsql->table('gathering_spots')->updateOrInsert(
                        ['id' => $row['id']],
                        $row
                    );
                    $migrated++;
                    $bar->advance(1);
                } catch (\Exception $e) {
                    $this->errors[] = "gathering_spots: Failed to insert spot {$row['id']}: " . $e->getMessage();
                    $skipped++;
                    $bar->advance(1);
                }
            }
        });

        $bar->finish();
        $this->newLine();

        if ($skipped > 0) {
            $this->warn("  ⚠️  Skipped {$skipped} invalid records");
        }

        return $migrated;
    }

    private function migrateApiKeys(int $batchSize, int $skip = 0): int
    {
        $mongodb = DB::connection('mongodb');
        $pgsql = DB::connection('pgsql');

        $total = $mongodb->table('api_keys')->count();

        if ($total === 0) {
            $this->warn('  ⚠️  No API keys found in MongoDB');
            return 0;
        }

        $migrated = 0;
        $skipped = 0;
        $remaining = $total - $skip;
        $bar = $this->output->createProgressBar($remaining);

        if ($skip > 0) {
            $this->info("  Resuming from record " . number_format($skip) . " of " . number_format($total));
        }

        $bar->start();

        $mongodb->table('api_keys')->orderBy('_id')->skip($skip)->chunk($batchSize, function ($apiKeys) use ($pgsql, &$migrated, &$skipped, $bar) {
            $data = [];

            foreach ($apiKeys as $key) {
                $key = (array) $key;

                // Validate API key ID is not null
                $keyId = $key['id'] ?? null;
                if (!$keyId || empty($keyId)) {
                    $this->errors[] = "api_keys: Skipped API key with invalid ID: {$keyId} (name: " . ($key['name'] ?? 'Unknown') . ")";
                    $skipped++;
                    $bar->advance(1);
                    continue;
                }

                $data[] = [
                    'id' => $keyId,
                    'name' => $key['name'] ??  'Unknown',
                    'description' => $key['description'] ??  null,
                    'admin_contact' => isset($key['adminContact']) ? json_encode($key['adminContact']) : null,
                    'max_limit' => $key['maxLimit'] ?? 0,
                    'daily_requests' => isset($key['dailyRequests']) ? json_encode($key['dailyRequests']) : null,
                ];
            }

            foreach ($data as $row) {
                try {
                    $pgsql->table('api_keys')->updateOrInsert(
                        ['id' => $row['id']],
                        $row
                    );
                    $migrated++;
                    $bar->advance(1);
                } catch (\Exception $e) {
                    $this->errors[] = "api_keys: Failed to insert API key {$row['id']} ({$row['name']}): " . $e->getMessage();
                    $skipped++;
                    $bar->advance(1);
                }
            }
        });

        $bar->finish();
        $this->newLine();

        if ($skipped > 0) {
            $this->warn("  ⚠️  Skipped {$skipped} invalid records");
        }

        return $migrated;
    }

    private function migrateServers(int $batchSize, int $skip = 0): int
    {
        $mongodb = DB::connection('mongodb');
        $pgsql = DB::connection('pgsql');

        $total = $mongodb->table('servers')->count();

        if ($total === 0) {
            $this->warn('  ⚠️  No servers found in MongoDB');
            return 0;
        }

        $migrated = 0;
        $skipped = 0;
        $remaining = $total - $skip;
        $bar = $this->output->createProgressBar($remaining);

        if ($skip > 0) {
            $this->info("  Resuming from record " . number_format($skip) . " of " . number_format($total));
        }

        $bar->start();

        $mongodb->table('servers')->orderBy('_id')->skip($skip)->chunk($batchSize, function ($servers) use ($pgsql, &$migrated, &$skipped, $bar) {
            $data = [];

            foreach ($servers as $server) {
                $server = (array) $server;

                // Validate server ID is not null
                $serverId = $server['id'] ?? null;
                if (!$serverId || empty($serverId)) {
                    $this->errors[] = "servers: Skipped server with invalid ID: {$serverId}";
                    $skipped++;
                    $bar->advance(1);
                    continue;
                }

                $data[] = [
                    'id' => $serverId,
                    'first_seen' => $server['firstSeen'] ?? 0,
                ];
            }

            foreach ($data as $row) {
                try {
                    $pgsql->table('servers')->updateOrInsert(
                        ['id' => $row['id']],
                        $row
                    );
                    $migrated++;
                    $bar->advance(1);
                } catch (\Exception $e) {
                    $this->errors[] = "servers: Failed to insert server {$row['id']}: " . $e->getMessage();
                    $skipped++;
                    $bar->advance(1);
                }
            }
        });

        $bar->finish();
        $this->newLine();

        if ($skipped > 0) {
            $this->warn("  ⚠️  Skipped {$skipped} invalid records");
        }

        return $migrated;
    }

    private function migrateCrashReports(int $batchSize, int $skip = 0): int
    {
        $mongodb = DB::connection('mongodb');
        $pgsql = DB::connection('pgsql');

        $total = $mongodb->table('crash_reports')->count();

        if ($total === 0) {
            $this->warn('  ⚠️  No crash reports found in MongoDB');
            return 0;
        }

        $migrated = 0;
        $remaining = $total - $skip;
        $bar = $this->output->createProgressBar($remaining);

        if ($skip > 0) {
            $this->info("  Resuming from record " . number_format($skip) . " of " . number_format($total));
        }

        $bar->start();

        $mongodb->table('crash_reports')->orderBy('_id')->skip($skip)->chunk($batchSize, function ($reports) use ($pgsql, &$migrated, $bar) {
            foreach ($reports as $report) {
                $report = (array) $report;

                $traceHash = $report['trace_hash'] ?? md5($report['trace'] ?? uniqid());

                $data = [
                    'trace_hash' => $traceHash,
                    'trace' => $report['trace'] ??  '',
                    'occurrences' => isset($report['occurrences']) ? json_encode($report['occurrences']) : json_encode([]),
                    'comments' => isset($report['comments']) ? json_encode($report['comments']) : null,
                    'count' => $report['count'] ??  0,
                    'handled' => $report['handled'] ??  false,
                    'created_at' => isset($report['created_at']) ? $report['created_at'] : now(),
                    'updated_at' => isset($report['updated_at']) ? $report['updated_at'] : now(),
                ];

                // Check if exists
                $existing = $pgsql->table('crash_reports')
                    ->where('trace_hash', $traceHash)
                    ->first();

                if ($existing) {
                    $pgsql->table('crash_reports')
                        ->where('trace_hash', $traceHash)
                        ->update($data);
                } else {
                    $pgsql->table('crash_reports')->insert($data);
                }

                $migrated++;
            }

            $bar->advance(count($reports));
        });

        $bar->finish();
        $this->newLine();

        return $migrated;
    }

    private function migratePatreonApi(int $batchSize, int $skip = 0): int
    {
        $mongodb = DB::connection('mongodb');
        $pgsql = DB::connection('pgsql');

        $total = $mongodb->table('patreon_api')->count();

        if ($total === 0) {
            $this->warn('  ⚠️  No Patreon API data found in MongoDB');
            return 0;
        }

        $migrated = 0;
        $remaining = $total - $skip;
        $bar = $this->output->createProgressBar($remaining);

        if ($skip > 0) {
            $this->info("  Resuming from record " . number_format($skip) . " of " . number_format($total));
        }

        $bar->start();

        $mongodb->table('patreon_api')->orderBy('_id')->skip($skip)->chunk($batchSize, function ($tokens) use ($pgsql, &$migrated, $bar) {
            $data = [];

            foreach ($tokens as $token) {
                $token = (array) $token;

                $data[] = [
                    'access_token' => $token['access_token'] ?? '',
                    'refresh_token' => $token['refresh_token'] ?? '',
                    'expires_in' => $token['expires_in'] ?? 0,
                    'scope' => $token['scope'] ?? null,
                    'token_type' => $token['token_type'] ?? 'Bearer',
                    'created_at' => isset($token['created_at']) ? $token['created_at'] : now(),
                    'updated_at' => isset($token['updated_at']) ? $token['updated_at'] : now(),
                ];
            }

            $pgsql->table('patreon_api')->insert($data);

            $migrated += count($data);
            $bar->advance(count($data));
        });

        $bar->finish();
        $this->newLine();

        return $migrated;
    }

    private function mapAccountType(? string $type): string
    {
        if (! $type) {
            return 'NORMAL';
        }

        return match(strtoupper($type)) {
            'NORMAL', 'USER' => 'NORMAL',
            'DONATOR', 'DONOR', 'PATRON' => 'DONATOR',
            'STAFF', 'DEVELOPER', 'DEV' => 'STAFF',
            'MODERATOR', 'MOD' => 'MODERATOR',
            'ADMIN', 'ADMINISTRATOR' => 'ADMIN',
            default => 'NORMAL',
        };
    }

    private function mapDonatorType(?string $type): ?string
    {
        if (!$type || strtoupper($type) === 'NONE') {
            return null;
        }

        return match(strtoupper($type)) {
            'VIP' => 'VIP',
            'VIP_PLUS', 'VIPPLUS', 'VIP+' => 'VIP_PLUS',
            'CHAMPION' => 'CHAMPION',
            'HERO' => 'HERO',
            default => null,
        };
    }

    private function displayStats(): void
    {
        $this->newLine();
        $this->info('📊 Migration Statistics:');
        $this->newLine();

        $headers = ['Table', 'Records', 'Duration', 'Status'];
        $rows = [];

        foreach ($this->stats as $table => $stat) {
            $status = $stat['status'] === 'success'
                ? '✅ Success'
                : '❌ Failed';

            $rows[] = [
                $table,
                number_format($stat['count']),
                $stat['duration'] .  's',
                $status,
            ];
        }

        $this->table($headers, $rows);
    }

    private function displayErrors(): void
    {
        $this->newLine();
        $this->error('❌ Errors encountered during migration:');
        $this->newLine();

        foreach ($this->errors as $error) {
            $this->error('  • ' . $error);
        }
    }

    private function verify(): void
    {
        $this->info('🔍 Verifying migration.. .');
        $this->newLine();

        $mongodb = DB::connection('mongodb');
        $pgsql = DB:: connection('pgsql');

        $collections = [
            'users' => 'users',
            'guilds' => 'guilds',
            'gatheringSpot' => 'gathering_spots',
            'apiKeys' => 'api_keys',
            'servers' => 'servers',
            'crash_reports' => 'crash_reports',
            'patreon_api' => 'patreon_api',
        ];

        $headers = ['Collection/Table', 'MongoDB Count', 'PostgreSQL Count', 'Status'];
        $rows = [];
        $allMatch = true;

        foreach ($collections as $mongoCollection => $pgsqlTable) {
            try {
                $mongoCount = $mongodb->table($mongoCollection)->count();
                $pgsqlCount = $pgsql->table($pgsqlTable)->count();

                $match = $mongoCount === $pgsqlCount;
                $status = $match ? '✅ Match' : '⚠️  Mismatch';

                if (!$match) {
                    $allMatch = false;
                }

                $rows[] = [
                    "{$mongoCollection} → {$pgsqlTable}",
                    number_format($mongoCount),
                    number_format($pgsqlCount),
                    $status,
                ];
            } catch (\Exception $e) {
                $rows[] = [
                    "{$mongoCollection} → {$pgsqlTable}",
                    'Error',
                    'Error',
                    '❌ Failed',
                ];
                $allMatch = false;
            }
        }

        $this->table($headers, $rows);

        if ($allMatch) {
            $this->newLine();
            $this->info('✅ All record counts match!');
        } else {
            $this->newLine();
            $this->warn('⚠️  Some counts do not match. Review the data manually.');
        }

        // Sample data verification
        $this->newLine();
        $this->info('🔍 Verifying sample user data...');

        $mongoUser = $mongodb->table('users')->first();
        if ($mongoUser) {
            $pgsqlUser = $pgsql->table('users')
                ->where('id', $mongoUser['id'])
                ->first();

            if ($pgsqlUser) {
                $this->info('  ✅ Sample user migrated successfully');
                $this->info("     MongoDB: {$mongoUser['username']} ({$mongoUser['id']})");
                $this->info("     PostgreSQL: {$pgsqlUser->username} ({$pgsqlUser->id})");

                // Verify JSON fields
                if (isset($mongoUser['discordInfo'])) {
                    $mongoDiscord = $mongoUser['discordInfo'];
                    $pgsqlDiscord = json_decode($pgsqlUser->discord_info, true);

                    if ($mongoDiscord == $pgsqlDiscord) {
                        $this->info('     ✅ Discord info matches');
                    } else {
                        $this->warn('     ⚠️  Discord info mismatch');
                    }
                }
            } else {
                $this->error('  ❌ Sample user not found in PostgreSQL');
            }
        }
    }
}
