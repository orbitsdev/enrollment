<?php

namespace Database\Seeders;

use App\Models\SchoolSetting;
use Illuminate\Database\Seeder;

class SchoolSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
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
        ];

        foreach ($settings as $key => $value) {
            SchoolSetting::set($key, $value);
        }
    }
}
