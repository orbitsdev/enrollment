<?php

use App\Models\Strand;
use App\Models\Track;
use App\Models\User;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::create(['name' => 'admin']);
    Role::create(['name' => 'registrar']);
    Role::create(['name' => 'teacher']);
    Role::create(['name' => 'student']);
});

// ── Model ─────────────────────────────────────────────────

test('strand can be created with course field', function () {
    $track = Track::create(['name' => 'TVL Track', 'code' => 'TVL']);

    $strand = Strand::create([
        'track_id' => $track->id,
        'name' => 'ICT',
        'code' => 'ICT',
        'course' => 'Computer System Servicing (NC II)',
    ]);

    expect($strand->course)->toBe('Computer System Servicing (NC II)');
});

test('strand course is nullable for academic strands', function () {
    $track = Track::create(['name' => 'Academic Track', 'code' => 'ACAD']);

    $strand = Strand::create([
        'track_id' => $track->id,
        'name' => 'STEM',
        'code' => 'STEM',
    ]);

    expect($strand->fresh()->course)->toBeNull();
});

// ── Controller ────────────────────────────────────────────

test('admin can create strand with course', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $track = Track::create(['name' => 'TVL Track', 'code' => 'TVL']);

    $this->actingAs($admin)
        ->post('/curriculum/strands', [
            'track_id' => $track->id,
            'name' => 'ICT',
            'code' => 'ICT',
            'course' => 'Computer System Servicing (NC II)',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('strands', [
        'code' => 'ICT',
        'course' => 'Computer System Servicing (NC II)',
    ]);
});

test('admin can update strand course', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $track = Track::create(['name' => 'TVL Track', 'code' => 'TVL']);
    $strand = Strand::create([
        'track_id' => $track->id,
        'name' => 'ICT',
        'code' => 'ICT',
        'course' => null,
    ]);

    $this->actingAs($admin)
        ->put("/curriculum/strands/{$strand->id}", [
            'name' => 'ICT',
            'code' => 'ICT',
            'course' => 'Bread and Pastry Production (NC II)',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('strands', [
        'id' => $strand->id,
        'course' => 'Bread and Pastry Production (NC II)',
    ]);
});

test('admin can clear strand course by setting null', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $track = Track::create(['name' => 'TVL Track', 'code' => 'TVL']);
    $strand = Strand::create([
        'track_id' => $track->id,
        'name' => 'HE',
        'code' => 'HE',
        'course' => 'Some Course',
    ]);

    $this->actingAs($admin)
        ->put("/curriculum/strands/{$strand->id}", [
            'name' => 'HE',
            'code' => 'HE',
            'course' => null,
        ])
        ->assertRedirect();

    expect($strand->fresh()->course)->toBeNull();
});
