<?php

namespace Database\Seeders;

use App\Enums\EnrollmentStatus;
use App\Enums\GradeRemarks;
use App\Enums\StudentStatus;
use App\Enums\UserRole;
use App\Models\Enrollment;
use App\Models\Grade;
use App\Models\SchoolYear;
use App\Models\Section;
use App\Models\Semester;
use App\Models\Strand;
use App\Models\Student;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoDataSeeder extends Seeder
{
    /**
     * Common Filipino first names.
     */
    protected array $maleFirstNames = [
        'Juan', 'Jose', 'Carlos', 'Miguel', 'Antonio',
        'Rafael', 'Gabriel', 'Marco', 'Paolo', 'Angelo',
        'Christian', 'Mark', 'John', 'James', 'Kenneth',
        'Michael', 'Daniel', 'Robert', 'Patrick', 'Francis',
        'Andrei', 'Jayson', 'Ryan', 'Kevin', 'Aldrin',
        'Jerome', 'Benedict', 'Ariel', 'Rodel', 'Reynaldo',
        'Ericson', 'Jericho', 'Ronaldo', 'Emmanuel', 'Vincent',
        'Lorenzo', 'Alejandro', 'Diego', 'Mateo', 'Sebastian',
    ];

    protected array $femaleFirstNames = [
        'Maria', 'Ana', 'Rosa', 'Carmen', 'Josefa',
        'Luz', 'Elena', 'Isabel', 'Teresa', 'Patricia',
        'Angela', 'Christine', 'Michelle', 'Jennifer', 'Jessica',
        'Nicole', 'Stephanie', 'Katherine', 'Angelica', 'Grace',
        'Precious', 'Princess', 'Jasmine', 'Althea', 'Rachelle',
        'Maricel', 'Joanne', 'Catherine', 'Sophia', 'Gabrielle',
        'Bianca', 'Camille', 'Denise', 'Erica', 'Faith',
        'Hannah', 'Isabelle', 'Janine', 'Karina', 'Lovely',
    ];

    protected array $middleNames = [
        'Santos', 'Reyes', 'Cruz', 'Garcia', 'Gonzales',
        'Lopez', 'Aquino', 'Ramos', 'Mendoza', 'Rivera',
        'Torres', 'Flores', 'Villanueva', 'De Leon', 'Soriano',
        'Castro', 'Enriquez', 'Bautista', 'Dela Cruz', 'Malabanan',
    ];

    /**
     * Common Filipino surnames.
     */
    protected array $lastNames = [
        'Dela Cruz', 'Santos', 'Reyes', 'Cruz', 'Bautista',
        'Gonzales', 'Lopez', 'Garcia', 'Aquino', 'Ramos',
        'Mendoza', 'Rivera', 'Torres', 'Flores', 'Villanueva',
        'De Leon', 'Soriano', 'Castro', 'Enriquez', 'Malabanan',
        'Dizon', 'Castillo', 'Fernandez', 'Mercado', 'Santiago',
        'Hernandez', 'Manalo', 'Salvador', 'Navarro', 'Pascual',
        'De Guzman', 'Perez', 'Aguilar', 'Tolentino', 'Martinez',
        'Velasco', 'Rosario', 'Morales', 'Concepcion', 'Dimaculangan',
    ];

    protected array $addresses = [
        'Brgy. San Antonio, Lipa City, Batangas',
        'Brgy. Poblacion, Tanauan City, Batangas',
        'Brgy. Tambo, Lipa City, Batangas',
        'Brgy. Balintawak, Lipa City, Batangas',
        'Brgy. Marawoy, Lipa City, Batangas',
        'Brgy. Dagatan, Lipa City, Batangas',
        'Brgy. Mataas na Lupa, Lipa City, Batangas',
        'Brgy. Sabang, Lipa City, Batangas',
        'Brgy. Pinagkawitan, Lipa City, Batangas',
        'Brgy. Maraouy, Lipa City, Batangas',
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->createSchoolYearAndSemesters();
        $this->createTeachersAndRegistrar();
        $students = $this->createStudents();
        $sections = $this->createSections();
        $this->enrollStudents($students, $sections);
    }

    /**
     * Create school year with 2 semesters.
     */
    protected function createSchoolYearAndSemesters(): void
    {
        // Deactivate existing
        SchoolYear::query()->update(['is_active' => false]);
        Semester::query()->update(['is_active' => false, 'enrollment_open' => false]);

        $schoolYear = SchoolYear::create([
            'name' => '2024-2025',
            'is_active' => true,
        ]);

        Semester::create([
            'school_year_id' => $schoolYear->id,
            'number' => 1,
            'is_active' => true,
            'enrollment_open' => true,
        ]);

        Semester::create([
            'school_year_id' => $schoolYear->id,
            'number' => 2,
            'is_active' => false,
            'enrollment_open' => false,
        ]);
    }

    /**
     * Create teacher and registrar users.
     */
    protected function createTeachersAndRegistrar(): void
    {
        $teacherNames = [
            'Maria Santos',
            'Jose Reyes',
            'Ana Garcia',
            'Carlos Mendoza',
            'Rosa Rivera',
        ];

        foreach ($teacherNames as $name) {
            $email = strtolower(str_replace(' ', '.', $name)) . '@school.edu.ph';
            $user = User::create([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make('password'),
                'is_active' => true,
                'email_verified_at' => now(),
            ]);
            $user->assignRole(UserRole::Teacher->value);
        }

        $registrar = User::create([
            'name' => 'Elena Bautista',
            'email' => 'registrar@school.edu.ph',
            'password' => Hash::make('password'),
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
        $registrar->assignRole(UserRole::Registrar->value);
    }

    /**
     * Create 200 students with Filipino names.
     */
    protected function createStudents(): \Illuminate\Support\Collection
    {
        $students = collect();

        for ($i = 0; $i < 200; $i++) {
            $gender = $i % 2 === 0 ? 'male' : 'female';
            $firstName = $gender === 'male'
                ? $this->maleFirstNames[array_rand($this->maleFirstNames)]
                : $this->femaleFirstNames[array_rand($this->femaleFirstNames)];
            $lastName = $this->lastNames[array_rand($this->lastNames)];
            $middleName = $this->middleNames[array_rand($this->middleNames)];

            // Generate unique LRN
            $lrn = str_pad((string) (300000000001 + $i), 12, '0', STR_PAD_LEFT);

            // Random birthdate (15-18 years old)
            $year = rand(2006, 2009);
            $month = rand(1, 12);
            $day = rand(1, 28);
            $birthdate = sprintf('%04d-%02d-%02d', $year, $month, $day);

            $student = Student::create([
                'lrn' => $lrn,
                'last_name' => $lastName,
                'first_name' => $firstName,
                'middle_name' => $middleName,
                'birthdate' => $birthdate,
                'gender' => $gender,
                'address' => $this->addresses[array_rand($this->addresses)],
                'contact_number' => '09' . rand(100000000, 999999999),
                'guardian_name' => $this->lastNames[array_rand($this->lastNames)] . ', ' . ($gender === 'male' ? $this->femaleFirstNames[array_rand($this->femaleFirstNames)] : $this->maleFirstNames[array_rand($this->maleFirstNames)]),
                'guardian_contact' => '09' . rand(100000000, 999999999),
                'guardian_relationship' => ['Mother', 'Father', 'Guardian', 'Grandmother', 'Grandfather'][array_rand(['Mother', 'Father', 'Guardian', 'Grandmother', 'Grandfather'])],
                'status' => StudentStatus::Active->value,
            ]);

            $students->push($student);
        }

        return $students;
    }

    /**
     * Create 14 sections (2 per strand, Grade 11, 1st semester).
     */
    protected function createSections(): \Illuminate\Support\Collection
    {
        $activeSemester = Semester::where('is_active', true)->first();
        $strands = Strand::all();
        $teachers = User::role(UserRole::Teacher->value)->get();
        $sections = collect();
        $teacherIndex = 0;

        $sectionNames = [
            'A', 'B',
        ];

        foreach ($strands as $strand) {
            foreach ($sectionNames as $suffix) {
                $sectionName = $strand->code . ' 11-' . $suffix;

                $section = Section::create([
                    'name' => $sectionName,
                    'strand_id' => $strand->id,
                    'semester_id' => $activeSemester->id,
                    'grade_level' => 11,
                    'max_capacity' => 50,
                    'adviser_id' => $teachers[$teacherIndex % $teachers->count()]->id,
                ]);

                $sections->push($section);
                $teacherIndex++;
            }
        }

        return $sections;
    }

    /**
     * Enroll students, create enrollment subjects, and generate grades.
     */
    protected function enrollStudents($students, $sections): void
    {
        $activeSemester = Semester::where('is_active', true)->first();
        $sectionCount = $sections->count();
        $registrar = User::role(UserRole::Registrar->value)->first()
            ?? User::role(UserRole::Admin->value)->first();

        foreach ($students as $index => $student) {
            // Distribute students evenly across sections
            $section = $sections[$index % $sectionCount];

            $enrollment = Enrollment::create([
                'student_id' => $student->id,
                'section_id' => $section->id,
                'semester_id' => $activeSemester->id,
                'status' => EnrollmentStatus::Enrolled,
                'enrolled_at' => now(),
            ]);

            // Get the subjects for this section's strand, grade 11, semester 1
            $subjects = $section->strand->subjects()
                ->wherePivot('grade_level', 11)
                ->wherePivot('semester', 1)
                ->get();

            // Create grade records for each subject
            foreach ($subjects as $subject) {
                $hasSampleGrades = $index < 100; // ~50% get sample grades

                $midterm = null;
                $finals = null;
                $finalGrade = null;
                $remarks = null;

                if ($hasSampleGrades) {
                    $midterm = rand(70, 99);
                    $finals = rand(70, 99);
                    $finalGrade = round(($midterm + $finals) / 2, 2);
                    $remarks = $finalGrade >= 75 ? GradeRemarks::Passed : GradeRemarks::Failed;
                }

                Grade::create([
                    'enrollment_id' => $enrollment->id,
                    'subject_id' => $subject->id,
                    'midterm' => $midterm,
                    'finals' => $finals,
                    'final_grade' => $finalGrade,
                    'remarks' => $remarks,
                    'is_locked' => false,
                    'encoded_by' => $hasSampleGrades ? $registrar?->id : null,
                ]);
            }
        }
    }
}
