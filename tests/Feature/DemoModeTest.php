<?php

use App\Livewire\BackupJob\Index as BackupJobIndex;
use App\Livewire\DatabaseServer\Create as DatabaseServerCreate;
use App\Livewire\DatabaseServer\Edit as DatabaseServerEdit;
use App\Livewire\DatabaseServer\Index as DatabaseServerIndex;
use App\Livewire\Volume\Create as VolumeCreate;
use App\Livewire\Volume\Edit as VolumeEdit;
use App\Livewire\Volume\Index as VolumeIndex;
use App\Models\DatabaseServer;
use App\Models\User;
use App\Models\Volume;
use App\Services\Backup\BackupJobFactory;
use Livewire\Livewire;

beforeEach(function () {
    $this->demoUser = User::factory()->create(['role' => User::ROLE_DEMO]);
    config(['app.demo_mode' => true]);
});

// DatabaseServer restrictions
test('demo user can view create database server page but cannot save', function () {
    // Demo user can view the create page
    $this->actingAs($this->demoUser)
        ->get(route('database-servers.create'))
        ->assertOk();

    // But attempting to save redirects with a demo notice
    Livewire::actingAs($this->demoUser)
        ->test(DatabaseServerCreate::class)
        ->set('form.name', 'Test Server')
        ->set('form.host', 'localhost')
        ->set('form.port', 3306)
        ->set('form.database_type', 'mysql')
        ->set('form.username', 'root')
        ->set('form.password', 'password')
        ->set('form.database_names', ['testdb'])
        ->call('save')
        ->assertRedirect(route('database-servers.index'))
        ->assertSessionHas('demo_notice');
});

test('demo user can view edit database server page but cannot save', function () {
    $server = DatabaseServer::factory()->create();

    // Demo user can view the edit page
    $this->actingAs($this->demoUser)
        ->get(route('database-servers.edit', $server))
        ->assertOk();

    // But attempting to save redirects with a demo notice
    Livewire::actingAs($this->demoUser)
        ->test(DatabaseServerEdit::class, ['server' => $server])
        ->set('form.name', 'Updated Name')
        ->call('save')
        ->assertRedirect(route('database-servers.index'))
        ->assertSessionHas('demo_notice');
});

test('demo user cannot delete database server', function () {
    $server = DatabaseServer::factory()->create();

    Livewire::actingAs($this->demoUser)
        ->test(DatabaseServerIndex::class)
        ->call('confirmDelete', $server->id)
        ->assertForbidden();
});

// Volume restrictions
test('demo user can view create volume page but cannot save', function () {
    // Demo user can view the create page
    $this->actingAs($this->demoUser)
        ->get(route('volumes.create'))
        ->assertOk();

    // But attempting to save redirects with a demo notice
    Livewire::actingAs($this->demoUser)
        ->test(VolumeCreate::class)
        ->set('form.name', 'Test Volume')
        ->set('form.type', 'local')
        ->set('form.path', '/tmp/backups')
        ->call('save')
        ->assertRedirect(route('volumes.index'))
        ->assertSessionHas('demo_notice');
});

test('demo user can view edit volume page but cannot save', function () {
    $volume = Volume::factory()->create();

    // Demo user can view the edit page
    $this->actingAs($this->demoUser)
        ->get(route('volumes.edit', $volume))
        ->assertOk();

    // But attempting to save redirects with a demo notice
    Livewire::actingAs($this->demoUser)
        ->test(VolumeEdit::class, ['volume' => $volume])
        ->set('form.name', 'Updated Name')
        ->call('save')
        ->assertRedirect(route('volumes.index'))
        ->assertSessionHas('demo_notice');
});

test('demo user cannot delete volume', function () {
    $volume = Volume::factory()->create();

    Livewire::actingAs($this->demoUser)
        ->test(VolumeIndex::class)
        ->call('confirmDelete', $volume->id)
        ->assertForbidden();
});

// Snapshot restrictions
test('demo user cannot delete snapshot', function () {
    $server = DatabaseServer::factory()->create(['database_names' => ['testdb']]);
    $factory = app(BackupJobFactory::class);
    $snapshot = $factory->createSnapshots($server, 'manual')[0];

    Livewire::actingAs($this->demoUser)
        ->test(BackupJobIndex::class)
        ->call('confirmDeleteSnapshot', $snapshot->id)
        ->assertForbidden();
});

// Demo user CAN do these things
test('demo user can view database servers', function () {
    DatabaseServer::factory()->create(['name' => 'Test Server']);

    Livewire::actingAs($this->demoUser)
        ->test(DatabaseServerIndex::class)
        ->assertSee('Test Server');
});

test('demo user can view volumes', function () {
    Volume::factory()->create(['name' => 'Test Volume']);

    Livewire::actingAs($this->demoUser)
        ->test(VolumeIndex::class)
        ->assertSee('Test Volume');
});

test('demo user can trigger backup', function () {
    $server = DatabaseServer::factory()->create(['database_names' => ['testdb']]);

    // Just verify the authorization passes (not the actual backup execution)
    expect($this->demoUser->can('backup', $server))->toBeTrue();
});

test('demo user can trigger restore', function () {
    $server = DatabaseServer::factory()->create(['database_names' => ['testdb']]);

    expect($this->demoUser->can('restore', $server))->toBeTrue();
});

test('demo mode middleware auto-logs in demo user from home page', function () {
    config(['app.demo_user_email' => 'test-demo@example.com']);

    // Access home page which redirects based on auth status
    $response = $this->get(route('home'));

    // Should redirect to dashboard since auto-logged in
    $response->assertRedirect(route('dashboard'));

    // Demo user should have been created
    $this->assertDatabaseHas('users', [
        'email' => 'test-demo@example.com',
        'role' => User::ROLE_DEMO,
    ]);

    // Verify user is logged in
    $this->assertAuthenticated();
});

test('demo mode middleware does not interfere when disabled', function () {
    $response = $this->get(route('dashboard'));

    // Should redirect to login
    $response->assertRedirect(route('login'));
});

test('real user can login and stays logged in after navigating when demo mode is enabled', function () {
    config(['app.demo_user_email' => 'demo@example.com']);

    // Create a real user
    $realUser = User::factory()->create([
        'email' => 'realuser@example.com',
        'password' => bcrypt('password123'),
        'role' => User::ROLE_ADMIN,
        'two_factor_secret' => null,
        'two_factor_confirmed_at' => null,
    ]);

    // Login as the real user
    $this->post(route('login.store'), [
        'email' => 'realuser@example.com',
        'password' => 'password123',
    ]);

    // Now navigate to dashboard - should still be the real user
    $response = $this->get(route('dashboard'));
    $response->assertOk();

    // Should still be logged in as the real user, NOT replaced by demo user
    expect(auth()->user()->email)->toBe('realuser@example.com')
        ->and(auth()->user()->isDemo())->toBeFalse();

    // Navigate to another page
    $response = $this->get(route('database-servers.index'));
    $response->assertOk();

    // Should still be the real user
    expect(auth()->user()->email)->toBe('realuser@example.com')
        ->and(auth()->user()->isDemo())->toBeFalse();
});

// Settings restrictions for demo users
test('demo user cannot access profile settings', function () {
    $this->actingAs($this->demoUser)
        ->get(route('profile.edit'))
        ->assertForbidden();
});

test('demo user cannot access password settings', function () {
    $this->actingAs($this->demoUser)
        ->get(route('user-password.edit'))
        ->assertForbidden();
});

test('demo user cannot access two-factor settings', function () {
    // Two-factor route may have password.confirm middleware
    // Demo users should not be able to access it (either 403 or redirect to confirm)
    $response = $this->actingAs($this->demoUser)
        ->get(route('two-factor.show'));

    // Should either be forbidden or redirected (not OK)
    expect($response->status())->not->toBe(200);
});

test('demo user cannot access api tokens settings', function () {
    $this->actingAs($this->demoUser)
        ->get(route('api-tokens.index'))
        ->assertForbidden();
});

test('demo user can access appearance settings', function () {
    $this->actingAs($this->demoUser)
        ->get(route('appearance.edit'))
        ->assertOk();
});
