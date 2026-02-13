<?php

use App\Models\SchoolSetting;
use App\Models\User;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::create(['name' => 'admin']);
    Role::create(['name' => 'registrar']);
    Role::create(['name' => 'teacher']);
    Role::create(['name' => 'student']);
});

// ── Index ─────────────────────────────────────────────────

test('admin can view settings page', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    SchoolSetting::set('school_name', 'Test School');
    SchoolSetting::set('district', 'Lake Sebu East');

    $this->actingAs($admin)
        ->get('/school-settings')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('school-settings/Index')
            ->where('settings.school_name', 'Test School')
            ->where('settings.district', 'Lake Sebu East')
        );
});

test('non-admin cannot view settings', function () {
    $teacher = User::factory()->create();
    $teacher->assignRole('teacher');

    $this->actingAs($teacher)
        ->get('/school-settings')
        ->assertForbidden();
});

// ── Update ────────────────────────────────────────────────

test('admin can update all settings including new fields', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $this->actingAs($admin)
        ->put('/school-settings', [
            'school_name' => 'Lake Sebu National High School',
            'school_id' => '304550',
            'school_address' => 'Lake Sebu, South Cotabato',
            'district' => 'Lake Sebu East',
            'division' => 'South Cotabato',
            'region' => 'Region XII',
            'passing_grade' => '75',
            'midterm_weight' => '50',
            'finals_weight' => '50',
            'default_capacity' => '50',
        ])
        ->assertRedirect();

    expect(SchoolSetting::get('school_id'))->toBe('304550');
    expect(SchoolSetting::get('district'))->toBe('Lake Sebu East');
    expect(SchoolSetting::get('division'))->toBe('South Cotabato');
    expect(SchoolSetting::get('region'))->toBe('Region XII');
});

test('settings update ignores unknown keys', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $this->actingAs($admin)
        ->put('/school-settings', [
            'school_name' => 'Test',
            'malicious_key' => 'should not be saved',
        ])
        ->assertRedirect();

    expect(SchoolSetting::get('malicious_key'))->toBeNull();
    expect(SchoolSetting::get('school_name'))->toBe('Test');
});
