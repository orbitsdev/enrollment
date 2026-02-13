<?php

use App\Models\Student;
use App\Models\User;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::create(['name' => 'admin']);
    Role::create(['name' => 'registrar']);
    Role::create(['name' => 'teacher']);
    Role::create(['name' => 'student']);
});

// ── Model ─────────────────────────────────────────────────

test('student can be created with new SF1 fields', function () {
    $student = Student::create([
        'lrn' => '123456789012',
        'last_name' => 'Dela Cruz',
        'first_name' => 'Juan',
        'birthdate' => '2008-03-15',
        'gender' => 'male',
        'religion' => 'Christianity',
        'learning_modality' => 'Face to Face',
        'father_name' => 'DELA CRUZ, PEDRO',
        'mother_name' => 'SANTOS, MARIA',
    ]);

    expect($student->religion)->toBe('Christianity');
    expect($student->learning_modality)->toBe('Face to Face');
    expect($student->father_name)->toBe('DELA CRUZ, PEDRO');
    expect($student->mother_name)->toBe('SANTOS, MARIA');
});

test('learning_modality defaults to Face to Face', function () {
    $student = Student::create([
        'lrn' => '123456789012',
        'last_name' => 'Reyes',
        'first_name' => 'Maria',
        'birthdate' => '2008-05-20',
        'gender' => 'female',
    ]);

    expect($student->fresh()->learning_modality)->toBe('Face to Face');
});

test('religion and parent names are nullable', function () {
    $student = Student::create([
        'lrn' => '123456789012',
        'last_name' => 'Santos',
        'first_name' => 'Pedro',
        'birthdate' => '2008-01-10',
        'gender' => 'male',
    ]);

    $fresh = $student->fresh();
    expect($fresh->religion)->toBeNull();
    expect($fresh->father_name)->toBeNull();
    expect($fresh->mother_name)->toBeNull();
});

// ── Store validation ──────────────────────────────────────

test('store student accepts new fields', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $this->actingAs($admin)
        ->post('/students', [
            'lrn' => '111222333444',
            'last_name' => 'Garcia',
            'first_name' => 'Ana',
            'birthdate' => '2008-06-15',
            'gender' => 'female',
            'religion' => 'Islam',
            'learning_modality' => 'Blended',
            'father_name' => 'GARCIA, JOSE',
            'mother_name' => 'REYES, LINA',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('students', [
        'lrn' => '111222333444',
        'religion' => 'Islam',
        'learning_modality' => 'Blended',
        'father_name' => 'GARCIA, JOSE',
        'mother_name' => 'REYES, LINA',
    ]);
});

test('store student validates new field lengths', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $this->actingAs($admin)
        ->post('/students', [
            'lrn' => '111222333444',
            'last_name' => 'Test',
            'first_name' => 'Test',
            'birthdate' => '2008-01-01',
            'gender' => 'male',
            'religion' => str_repeat('X', 60), // max 50
            'learning_modality' => str_repeat('X', 40), // max 30
            'father_name' => str_repeat('X', 210), // max 200
        ])
        ->assertSessionHasErrors(['religion', 'learning_modality', 'father_name']);
});

// ── Update validation ─────────────────────────────────────

test('update student accepts new fields', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $student = Student::create([
        'lrn' => '111222333444',
        'last_name' => 'Test',
        'first_name' => 'Student',
        'birthdate' => '2008-01-01',
        'gender' => 'male',
    ]);

    $this->actingAs($admin)
        ->put("/students/{$student->id}", [
            'lrn' => '111222333444',
            'last_name' => 'Test',
            'first_name' => 'Student',
            'birthdate' => '2008-01-01',
            'gender' => 'male',
            'religion' => 'Christianity',
            'learning_modality' => 'Modular',
            'father_name' => 'TEST, FATHER',
            'mother_name' => 'TEST, MOTHER',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('students', [
        'id' => $student->id,
        'religion' => 'Christianity',
        'learning_modality' => 'Modular',
        'father_name' => 'TEST, FATHER',
        'mother_name' => 'TEST, MOTHER',
    ]);
});
