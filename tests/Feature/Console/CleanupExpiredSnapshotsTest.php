<?php

use App\Models\BackupJob;
use App\Models\DatabaseServer;
use App\Models\Snapshot;

use function Pest\Laravel\artisan;

function createSnapshot(DatabaseServer $server, string $status, \Carbon\Carbon $createdAt): Snapshot
{
    $job = BackupJob::create([
        'status' => $status,
        'started_at' => now(),
        'completed_at' => $status === 'completed' ? now() : null,
    ]);

    $snapshot = Snapshot::create([
        'backup_job_id' => $job->id,
        'database_server_id' => $server->id,
        'backup_id' => $server->backup->id,
        'volume_id' => $server->backup->volume_id,
        'storage_uri' => 'local:///tmp/test-backup-'.uniqid().'.sql.gz',
        'file_size' => 1024,
        'started_at' => now(),
        'database_name' => $server->database_name ?? 'testdb',
        'database_type' => $server->database_type,
        'database_host' => $server->host,
        'database_port' => $server->port,
        'compression_type' => 'gzip',
        'method' => 'scheduled',
    ]);

    $snapshot->forceFill(['created_at' => $createdAt])->saveQuietly();

    return $snapshot->fresh();
}

test('command deletes only expired completed snapshots', function () {
    // Server with 7 days retention
    $server = DatabaseServer::factory()->create();
    $server->backup->update(['retention_days' => 7]);

    // Should be deleted: completed and expired (10 days old)
    $expiredCompleted = createSnapshot($server, 'completed', now()->subDays(10));

    // Should NOT be deleted: completed but not expired (3 days old)
    $recentCompleted = createSnapshot($server, 'completed', now()->subDays(3));

    // Should NOT be deleted: expired but pending (not completed)
    $expiredPending = createSnapshot($server, 'pending', now()->subDays(10));

    // Server without retention - snapshots should never be deleted
    $serverNoRetention = DatabaseServer::factory()->create();
    $serverNoRetention->backup->update(['retention_days' => null]);
    $noRetentionSnapshot = createSnapshot($serverNoRetention, 'completed', now()->subDays(100));

    artisan('snapshots:cleanup')
        ->expectsOutputToContain('1 snapshot(s) deleted')
        ->assertSuccessful();

    expect(Snapshot::find($expiredCompleted->id))->toBeNull()
        ->and(Snapshot::find($recentCompleted->id))->not->toBeNull()
        ->and(Snapshot::find($expiredPending->id))->not->toBeNull()
        ->and(Snapshot::find($noRetentionSnapshot->id))->not->toBeNull();
});

test('command dry-run mode does not delete snapshots', function () {
    $server = DatabaseServer::factory()->create();
    $server->backup->update(['retention_days' => 7]);

    $expiredSnapshot = createSnapshot($server, 'completed', now()->subDays(10));

    artisan('snapshots:cleanup', ['--dry-run' => true])
        ->expectsOutput('Running in dry-run mode. No snapshots will be deleted.')
        ->expectsOutputToContain('1 snapshot(s) would be deleted')
        ->assertSuccessful();

    expect(Snapshot::find($expiredSnapshot->id))->not->toBeNull();
});
