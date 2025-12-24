<?php

use App\Exceptions\ShellProcessFailed;
use App\Models\BackupJob;
use App\Services\Backup\ShellProcessor;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('process returns command output', function () {
    $processor = new ShellProcessor;

    $output = $processor->process('echo "hello world"');

    expect(trim($output))->toBe('hello world');
});

test('process throws exception on failed command', function () {
    $processor = new ShellProcessor;

    $processor->process('exit 1');
})->throws(ShellProcessFailed::class);

test('process logs command execution lifecycle', function () {
    $backupJob = BackupJob::create([
        'status' => 'running',
    ]);

    $processor = new ShellProcessor;
    $processor->setLogger($backupJob);

    $processor->process('echo "test output"');

    $backupJob->refresh();
    $logs = $backupJob->getLogs();

    expect($logs)->toHaveCount(1);

    $commandLog = $logs[0];
    expect($commandLog['type'])->toBe('command')
        ->and($commandLog['command'])->toBe('echo "test output"')
        ->and($commandLog['status'])->toBe('completed')
        ->and($commandLog['exit_code'])->toBe(0)
        ->and($commandLog['output'])->toContain('test output')
        ->and($commandLog['duration_ms'])->toBeGreaterThan(0);
});

test('process logs failed command with error status', function () {
    $backupJob = BackupJob::create([
        'status' => 'running',
    ]);

    $processor = new ShellProcessor;
    $processor->setLogger($backupJob);

    try {
        $processor->process('echo "error" >&2 && exit 1');
    } catch (ShellProcessFailed) {
        // Expected exception
    }

    $backupJob->refresh();
    $logs = $backupJob->getLogs();

    expect($logs)->toHaveCount(1);

    $commandLog = $logs[0];
    expect($commandLog['status'])->toBe('failed')
        ->and($commandLog['exit_code'])->toBe(1)
        ->and($commandLog['output'])->toContain('error');
});

test('process sanitizes mysql password in logs', function () {
    $backupJob = BackupJob::create([
        'status' => 'running',
    ]);

    $processor = new ShellProcessor;
    $processor->setLogger($backupJob);

    // Use short form -p to test password sanitization
    $processor->process('echo -psecret123');

    $backupJob->refresh();
    $logs = $backupJob->getLogs();

    expect($logs[0]['command'])->toContain('-p***')
        ->and($logs[0]['command'])->not->toContain('secret123');
});

test('process sanitizes postgres password in logs', function () {
    $backupJob = BackupJob::create([
        'status' => 'running',
    ]);

    $processor = new ShellProcessor;
    $processor->setLogger($backupJob);

    $processor->process('echo PGPASSWORD=secret123');

    $backupJob->refresh();
    $logs = $backupJob->getLogs();

    expect($logs[0]['command'])->toContain('PGPASSWORD=***')
        ->and($logs[0]['command'])->not->toContain('secret123');
});

test('process works without logger', function () {
    $processor = new ShellProcessor;

    $output = $processor->process('echo "no logger"');

    expect(trim($output))->toBe('no logger');
});

test('process creates log entry before command starts', function () {
    $backupJob = BackupJob::create([
        'status' => 'running',
    ]);

    $processor = new ShellProcessor;
    $processor->setLogger($backupJob);

    // Run a command that takes a moment
    $processor->process('sleep 0.1 && echo "done"');

    $backupJob->refresh();
    $logs = $backupJob->getLogs();

    // The log should exist and have a timestamp
    expect($logs)->toHaveCount(1)
        ->and($logs[0]['timestamp'])->not->toBeNull();
});
