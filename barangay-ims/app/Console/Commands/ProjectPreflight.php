<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

class ProjectPreflight extends Command
{
    protected $signature = 'project:preflight';
    protected $description = 'Run read-only deployment readiness checks without changing application data';

    public function handle(): int
    {
        $this->components->info('Barangay IMS read-only deployment preflight');

        try {
            $driver = DB::getDriverName();
            $database = DB::connection()->getDatabaseName();
            $version = $this->databaseVersion($driver);

            $this->table(['Database check', 'Value'], [
                ['Connection', config('database.default')],
                ['Driver', $driver],
                ['Database', $database],
                ['Engine version', $version],
            ]);

            $this->reportMigrationState();
            $this->reportRoles($driver);
            $this->reportDuplicateNumbers();
            $this->reportRelationshipIntegrity();
            $this->reportFoundationSchema();

            $this->components->info('Preflight completed. No data or schema changes were made.');

            return self::SUCCESS;
        } catch (Throwable $exception) {
            $this->components->error('Preflight could not connect or complete: '.$exception->getMessage());
            $this->components->warn('No data or schema changes were attempted. Credentials are never displayed.');

            return self::FAILURE;
        }
    }

    private function databaseVersion(string $driver): string
    {
        return match ($driver) {
            'sqlite' => (string) (DB::selectOne('select sqlite_version() as version')->version ?? 'unknown'),
            default => (string) (DB::selectOne('select version() as version')->version ?? 'unknown'),
        };
    }

    private function reportMigrationState(): void
    {
        $ran = Schema::hasTable('migrations')
            ? DB::table('migrations')->pluck('migration')->all()
            : [];
        $files = collect(glob(database_path('migrations/*.php')) ?: [])
            ->map(fn (string $path) => pathinfo($path, PATHINFO_FILENAME));
        $pending = $files->diff($ran)->values();

        $this->table(['Migration check', 'Value'], [
            ['Repository migration files', (string) $files->count()],
            ['Applied migrations', (string) count($ran)],
            ['Pending migrations', $pending->isEmpty() ? 'None' : $pending->implode(', ')],
        ]);
    }

    private function reportRoles(string $driver): void
    {
        $roles = Schema::hasTable('users')
            ? DB::table('users')->select('role', DB::raw('count(*) as total'))->groupBy('role')->pluck('total', 'role')
            : collect();

        $captainSupported = $this->captainSchemaSupported($driver);
        $rows = $roles->map(fn ($total, $role) => [$role, (string) $total])->values()->all();
        $rows[] = ['Supported application roles', implode(', ', User::ROLES)];
        $rows[] = ['Captain allowed by schema', $captainSupported ? 'Yes' : 'No / migration pending'];
        $rows[] = ['Captain user count', (string) ($roles[User::ROLE_CAPTAIN] ?? 0)];

        $this->table(['Role check', 'Value'], $rows);
    }

    private function captainSchemaSupported(string $driver): bool
    {
        if (!Schema::hasTable('users') || !Schema::hasColumn('users', 'role')) {
            return false;
        }

        if ($driver === 'mysql') {
            $column = DB::selectOne("SHOW COLUMNS FROM users WHERE Field = 'role'");
            return str_contains(strtolower((string) ($column->Type ?? '')), "'captain'");
        }

        if ($driver === 'sqlite') {
            $schema = DB::selectOne("SELECT sql FROM sqlite_master WHERE type = 'table' AND name = 'users'");
            return str_contains(strtolower((string) ($schema->sql ?? '')), "'captain'");
        }

        return in_array('2026_08_20_000001_add_captain_role_to_users_table',
            Schema::hasTable('migrations') ? DB::table('migrations')->pluck('migration')->all() : [], true);
    }

    private function reportDuplicateNumbers(): void
    {
        $checks = [
            'Duplicate document control numbers' => ['document_requests', 'control_number'],
            'Duplicate blotter numbers' => ['blotters', 'blotter_number'],
        ];
        $rows = [];

        foreach ($checks as $label => [$table, $column]) {
            $count = Schema::hasTable($table) && Schema::hasColumn($table, $column)
                ? DB::table($table)->select($column)->groupBy($column)->havingRaw('COUNT(*) > 1')->get()->count()
                : null;
            $rows[] = [$label, $count === null ? 'Table/column missing' : (string) $count];
        }

        $this->table(['Number integrity check', 'Duplicate groups'], $rows);
    }

    private function reportRelationshipIntegrity(): void
    {
        $rows = [];

        $rows[] = ['Residents referencing missing households', $this->orphanCount('residents', 'household_id', 'households')];
        $rows[] = ['Household heads without a household', Schema::hasTable('residents')
            ? DB::table('residents')->where('is_household_head', true)->whereNull('household_id')->count()
            : 'Table missing'];
        $rows[] = ['Document requests referencing missing residents', $this->orphanCount('document_requests', 'resident_id', 'residents')];
        $rows[] = ['Document requests referencing missing types', $this->orphanCount('document_requests', 'document_type_id', 'document_types')];
        $rows[] = ['Blotters referencing missing complainants', $this->orphanCount('blotters', 'complainant_id', 'residents')];
        $rows[] = ['Blotters referencing missing respondents', $this->orphanCount('blotters', 'respondent_id', 'residents')];

        $this->table(['Relationship check', 'Count'], $rows);
    }

    private function orphanCount(string $source, string $foreignKey, string $target): int|string
    {
        if (!Schema::hasTable($source) || !Schema::hasTable($target) || !Schema::hasColumn($source, $foreignKey)) {
            return 'Table/column missing';
        }

        return DB::table($source)
            ->leftJoin($target, "$source.$foreignKey", '=', "$target.id")
            ->whereNotNull("$source.$foreignKey")
            ->whereNull("$target.id")
            ->count();
    }

    private function reportFoundationSchema(): void
    {
        $checks = [
            ['number_sequences table', Schema::hasTable('number_sequences')],
            ['notifications table', Schema::hasTable('notifications')],
            ['document_types.requirements', Schema::hasColumn('document_types', 'requirements')],
            ['document_types.processing_days', Schema::hasColumn('document_types', 'processing_days')],
            ['residents.photo_path', Schema::hasColumn('residents', 'photo_path')],
            ['blotters.hearing_date (reused)', Schema::hasColumn('blotters', 'hearing_date')],
            ['audit_logs.old_values', Schema::hasColumn('audit_logs', 'old_values')],
            ['audit_logs.new_values', Schema::hasColumn('audit_logs', 'new_values')],
        ];

        $this->table(['Phase 2 schema check', 'Present'], array_map(
            fn (array $check) => [$check[0], $check[1] ? 'Yes' : 'No'],
            $checks,
        ));
    }
}
