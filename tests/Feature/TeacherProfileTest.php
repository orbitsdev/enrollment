<?php

use App\Models\TeacherProfile;
use App\Models\TeacherTraining;
use App\Models\User;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    // Create roles
    Role::create(['name' => 'admin']);
    Role::create(['name' => 'registrar']);
    Role::create(['name' => 'teacher']);
    Role::create(['name' => 'student']);
});

function createAdmin(): User
{
    $user = User::factory()->create();
    $user->assignRole('admin');

    return $user;
}

function createTeacherUser(): User
{
    $user = User::factory()->create();
    $user->assignRole('teacher');

    return $user;
}

// ── Index ─────────────────────────────────────────────────

test('guests cannot access teacher list', function () {
    $this->get('/teachers')->assertRedirect('/login');
});

test('students cannot access teacher list', function () {
    $student = User::factory()->create();
    $student->assignRole('student');

    $this->actingAs($student)
        ->get('/teachers')
        ->assertForbidden();
});

test('admin can view teacher list', function () {
    $admin = createAdmin();
    $teacher = createTeacherUser();

    $this->actingAs($admin)
        ->get('/teachers')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('teachers/Index')
            ->has('teachers.data', 1)
        );
});

test('registrar can view teacher list', function () {
    $registrar = User::factory()->create();
    $registrar->assignRole('registrar');

    $this->actingAs($registrar)
        ->get('/teachers')
        ->assertOk();
});

test('teacher list search works', function () {
    $admin = createAdmin();
    $t1 = User::factory()->create(['name' => 'Maria Santos']);
    $t1->assignRole('teacher');
    $t2 = User::factory()->create(['name' => 'Pedro Reyes']);
    $t2->assignRole('teacher');

    $this->actingAs($admin)
        ->get('/teachers?search=Maria')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('teachers.data', 1)
            ->where('teachers.data.0.name', 'Maria Santos')
        );
});

// ── Show ──────────────────────────────────────────────────

test('admin can view teacher profile', function () {
    $admin = createAdmin();
    $teacher = createTeacherUser();

    TeacherProfile::create([
        'user_id' => $teacher->id,
        'employee_id' => 'EMP-001',
        'position_title' => 'Teacher I',
    ]);

    $this->actingAs($admin)
        ->get("/teachers/{$teacher->id}")
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('teachers/Show')
            ->where('teacher.id', $teacher->id)
            ->where('profile.employee_id', 'EMP-001')
        );
});

test('show page works when teacher has no profile yet', function () {
    $admin = createAdmin();
    $teacher = createTeacherUser();

    $this->actingAs($admin)
        ->get("/teachers/{$teacher->id}")
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('teachers/Show')
            ->where('profile', null)
        );
});

// ── Edit ──────────────────────────────────────────────────

test('admin can view teacher edit form', function () {
    $admin = createAdmin();
    $teacher = createTeacherUser();

    $this->actingAs($admin)
        ->get("/teachers/{$teacher->id}/edit")
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('teachers/Edit')
            ->where('teacher.id', $teacher->id)
        );
});

// ── Update (create/update profile) ───────────────────────

test('admin can create teacher profile', function () {
    $admin = createAdmin();
    $teacher = createTeacherUser();

    $this->actingAs($admin)
        ->put("/teachers/{$teacher->id}", [
            'employee_id' => 'EMP-100',
            'position_title' => 'Teacher III',
            'appointment_status' => 'permanent',
            'sex' => 'Female',
            'specialization' => 'Mathematics',
            'highest_degree' => "Master's",
            'degree_course' => 'MSEd Mathematics',
            'teaching_hours_per_week' => 24,
        ])
        ->assertRedirect("/teachers/{$teacher->id}");

    $this->assertDatabaseHas('teacher_profiles', [
        'user_id' => $teacher->id,
        'employee_id' => 'EMP-100',
        'position_title' => 'Teacher III',
        'appointment_status' => 'permanent',
        'specialization' => 'Mathematics',
        'teaching_hours_per_week' => 24,
    ]);
});

test('admin can update existing teacher profile', function () {
    $admin = createAdmin();
    $teacher = createTeacherUser();

    TeacherProfile::create([
        'user_id' => $teacher->id,
        'employee_id' => 'EMP-001',
        'position_title' => 'Teacher I',
    ]);

    $this->actingAs($admin)
        ->put("/teachers/{$teacher->id}", [
            'employee_id' => 'EMP-001',
            'position_title' => 'Teacher II',
            'specialization' => 'Science',
        ])
        ->assertRedirect("/teachers/{$teacher->id}");

    $this->assertDatabaseHas('teacher_profiles', [
        'user_id' => $teacher->id,
        'position_title' => 'Teacher II',
        'specialization' => 'Science',
    ]);

    // Should only have one profile
    expect(TeacherProfile::where('user_id', $teacher->id)->count())->toBe(1);
});

test('profile validation rejects invalid data', function () {
    $admin = createAdmin();
    $teacher = createTeacherUser();

    $this->actingAs($admin)
        ->put("/teachers/{$teacher->id}", [
            'employee_id' => str_repeat('X', 30), // max 20
            'teaching_hours_per_week' => 100, // max 60
            'year_graduated' => 1900, // min 1950
        ])
        ->assertSessionHasErrors(['employee_id', 'teaching_hours_per_week', 'year_graduated']);
});

// ── Trainings ─────────────────────────────────────────────

test('admin can add training record', function () {
    $admin = createAdmin();
    $teacher = createTeacherUser();

    $this->actingAs($admin)
        ->post("/teachers/{$teacher->id}/trainings", [
            'title' => 'SHS Curriculum Training',
            'type' => 'seminar',
            'sponsor' => 'DepEd Region XII',
            'date_from' => '2025-06-15',
            'date_to' => '2025-06-17',
            'hours' => 24,
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('teacher_trainings', [
        'title' => 'SHS Curriculum Training',
        'type' => 'seminar',
        'sponsor' => 'DepEd Region XII',
        'hours' => 24.0,
    ]);

    // Should have auto-created teacher profile
    expect(TeacherProfile::where('user_id', $teacher->id)->exists())->toBeTrue();
});

test('training title is required', function () {
    $admin = createAdmin();
    $teacher = createTeacherUser();

    $this->actingAs($admin)
        ->post("/teachers/{$teacher->id}/trainings", [
            'type' => 'workshop',
        ])
        ->assertSessionHasErrors('title');
});

test('training date_to must be after date_from', function () {
    $admin = createAdmin();
    $teacher = createTeacherUser();

    $this->actingAs($admin)
        ->post("/teachers/{$teacher->id}/trainings", [
            'title' => 'Some Training',
            'date_from' => '2025-06-17',
            'date_to' => '2025-06-15',
        ])
        ->assertSessionHasErrors('date_to');
});

test('admin can delete training record', function () {
    $admin = createAdmin();
    $teacher = createTeacherUser();

    $profile = TeacherProfile::create(['user_id' => $teacher->id]);
    $training = TeacherTraining::create([
        'teacher_profile_id' => $profile->id,
        'title' => 'Old Training',
    ]);

    $this->actingAs($admin)
        ->delete("/teachers/{$teacher->id}/trainings/{$training->id}")
        ->assertRedirect();

    $this->assertDatabaseMissing('teacher_trainings', ['id' => $training->id]);
});
