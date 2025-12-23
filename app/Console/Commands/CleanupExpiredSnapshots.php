<?php

namespace App\Console\Commands;

use App\Models\Backup;
use App\Models\Snapshot;
use Illuminate\Console\Command;

class CleanupExpiredSnapshots extends Command
{
    protected $signature = 'snapshots:cleanup {--dry-run : List snapshots that would be deleted without actually deleting them}';

    protected $description = 'Delete snapshots older than the configured retention period';

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->info('Running in dry-run mode. No snapshots will be deleted.');
        }

        // Find all backups with retention_days configured
        $backupsWithRetention = Backup::whereNotNull('retention_days')
            ->with('databaseServer')
            ->get();

        if ($backupsWithRetention->isEmpty()) {
            $this->info('No backups with retention period configured.');

            return self::SUCCESS;
        }

        $totalDeleted = 0;

        foreach ($backupsWithRetention as $backup) {
            $cutoffDate = now()->subDays($backup->retention_days);
            $serverName = $backup->databaseServer->name ?? 'Unknown Server';

            // Find completed snapshots older than retention period
            $expiredSnapshots = Snapshot::where('backup_id', $backup->id)
                ->whereHas('job', fn ($q) => $q->where('status', 'completed'))
                ->where('created_at', '<', $cutoffDate)
                ->get();

            if ($expiredSnapshots->isEmpty()) {
                continue;
            }

            $this->line("Server: {$serverName} (retention: {$backup->retention_days} days)");

            foreach ($expiredSnapshots as $snapshot) {
                $age = $snapshot->created_at->diffInDays(now());
                $database = $snapshot->database_name;

                if ($dryRun) {
                    $this->line("  [DRY-RUN] Would delete: {$database} ({$age} days old)");
                } else {
                    $snapshot->delete();
                    $this->line("  → Deleted: {$database} ({$age} days old)");
                }

                $totalDeleted++;
            }
        }

        if ($totalDeleted === 0) {
            $this->info('No expired snapshots found.');
        } else {
            $action = $dryRun ? 'would be deleted' : 'deleted';
            $this->info("{$totalDeleted} snapshot(s) {$action}.");
        }

        return self::SUCCESS;
    }
}
