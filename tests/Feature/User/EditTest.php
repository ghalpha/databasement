<?php

use App\Livewire\User\Edit;
use App\Models\User;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'admin']);
});

test('guests cannot access edit user page', function () {
    $user = User::factory()->create();

    get(route('users.edit', $user))
        ->assertRedirect(route('login'));
});

test('non-admin users cannot access edit user page', function () {
    $member = User::factory()->create(['role' => 'member']);
    $userToEdit = User::factory()->create();
    actingAs($member);

    get(route('users.edit', $userToEdit))
        ->assertForbidden();
});

test('viewer cannot access edit user page', function () {
    $viewer = User::factory()->create(['role' => 'viewer']);
    $userToEdit = User::factory()->create();
    actingAs($viewer);

    get(route('users.edit', $userToEdit))
        ->assertForbidden();
});

test('admin can edit another user', function () {
    actingAs($this->admin);

    $userToEdit = User::factory()->create([
        'name' => 'Original Name',
        'email' => 'original@example.com',
        'role' => 'member',
    ]);

    Livewire::test(Edit::class, ['user' => $userToEdit])
        ->set('form.name', 'Updated Name')
        ->set('form.email', 'updated@example.com')
        ->set('form.role', 'viewer')
        ->call('save')
        ->assertRedirect(route('users.index'));

    $userToEdit->refresh();
    expect($userToEdit->name)->toBe('Updated Name');
    expect($userToEdit->email)->toBe('updated@example.com');
    expect($userToEdit->role)->toBe('viewer');
});

test('cannot change last admin to non-admin role', function () {
    actingAs($this->admin);

    // Ensure there's only one admin
    expect(User::where('role', 'admin')->count())->toBe(1);

    Livewire::test(Edit::class, ['user' => $this->admin])
        ->set('form.role', 'member')
        ->call('save')
        ->assertHasNoErrors()
        ->assertNoRedirect();

    // Role should not have changed
    $this->admin->refresh();
    expect($this->admin->role)->toBe('admin');
});

test('can change admin to non-admin when multiple admins exist', function () {
    actingAs($this->admin);

    $anotherAdmin = User::factory()->create(['role' => 'admin']);

    expect(User::where('role', 'admin')->count())->toBe(2);

    Livewire::test(Edit::class, ['user' => $anotherAdmin])
        ->set('form.role', 'member')
        ->call('save')
        ->assertRedirect(route('users.index'));

    $anotherAdmin->refresh();
    expect($anotherAdmin->role)->toBe('member');
});

test('email must be unique except for current user', function () {
    actingAs($this->admin);

    User::factory()->create(['email' => 'existing@example.com']);
    $userToEdit = User::factory()->create(['email' => 'original@example.com']);

    // Try to use existing email - should fail validation
    Livewire::test(Edit::class, ['user' => $userToEdit])
        ->set('form.email', 'existing@example.com')
        ->call('save')
        ->assertHasErrors('form.email');

    // Ensure email was not changed
    $userToEdit->refresh();
    expect($userToEdit->email)->toBe('original@example.com');
});

test('form is pre-populated with user data', function () {
    actingAs($this->admin);

    $userToEdit = User::factory()->create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'role' => 'viewer',
    ]);

    Livewire::test(Edit::class, ['user' => $userToEdit])
        ->assertSet('form.name', 'Test User')
        ->assertSet('form.email', 'test@example.com')
        ->assertSet('form.role', 'viewer');
});

test('can promote user to admin', function () {
    actingAs($this->admin);

    $member = User::factory()->create(['role' => 'member']);

    Livewire::test(Edit::class, ['user' => $member])
        ->set('form.role', 'admin')
        ->call('save')
        ->assertRedirect(route('users.index'));

    $member->refresh();
    expect($member->role)->toBe('admin');
});
