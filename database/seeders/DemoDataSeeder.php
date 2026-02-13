<?php

namespace Database\Seeders;

use App\Enums\EnrollmentStatus;
use App\Enums\GradeRemarks;
use App\Enums\StudentStatus;
use App\Enums\UserRole;
use App\Models\AuditLog;
use App\Models\Enrollment;
use App\Models\Grade;
use App\Models\SchoolYear;
use App\Models\Section;
use App\Models\Semester;
use App\Models\Strand;
use App\Models\Student;
use App\Models\Subject;
use App\Models\TeacherProfile;
use App\Models\TeacherTraining;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoDataSeeder extends Seeder
{
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
        'Brgy. Poblacion, Lake Sebu, South Cotabato',
        'Brgy. Lamdalag, Lake Sebu, South Cotabato',
        'Brgy. Tasiman, Lake Sebu, South Cotabato',
        'Brgy. Halilan, Lake Sebu, South Cotabato',
        'Brgy. Klubi, Lake Sebu, South Cotabato',
        'Brgy. Bacdulong, Lake Sebu, South Cotabato',
        'Brgy. Ned, Lake Sebu, South Cotabato',
        'Brgy. Takunel, Lake Sebu, South Cotabato',
        'Brgy. Seloton, Lake Sebu, South Cotabato',
        'Brgy. Lahit, Lake Sebu, South Cotabato',
    ];

    protected array $religions = [
        'Roman Catholic', 'Roman Catholic', 'Roman Catholic', 'Roman Catholic',
        'Iglesia ni Cristo', 'Born Again Christian', 'Islam',
        'Seventh Day Adventist', 'Baptist', 'Methodist',
    ];

    protected array $previousSchools = [
        'Lake Sebu National High School',
        'Surallah National High School',
        'Koronadal National High School',
        'Polomolok National High School',
        'T\'boli National High School',
        'General Santos City National High School',
        'Tupi National High School',
        'Tantangan National High School',
        'Norala National High School',
        'Banga National High School',
    ];

    protected array $suffixes = ['', '', '', '', '', '', '', 'Jr.', 'III', 'IV'];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $schoolYears = $this->createSchoolYearsAndSemesters();
        $teachers = $this->createTeachersAndRegistrar();
        $this->createTeacherProfiles($teachers);
        $students = $this->createStudents();

        // Past school year (2023-2024) — completed enrollments with full grades
        $pastSections = $this->createSections($schoolYears['past']['sem1'], 'past');
        $this->enrollStudents($students->take(150), $pastSections, $schoolYears['past']['sem1'], completed: true);

        $pastSections2 = $this->createSections($schoolYears['past']['sem2'], 'past2');
        $this->enrollStudents($students->take(150), $pastSections2, $schoolYears['past']['sem2'], completed: true);

        // Current school year (2024-2025) — active enrollments, partial grades
        $currentSections = $this->createSections($schoolYears['current']['sem1'], 'current');
        $this->enrollStudents($students, $currentSections, $schoolYears['current']['sem1'], completed: false);

        $this->createAuditLogs($students, $teachers);
    }

    /**
     * Create two school years with semesters each.
     */
    protected function createSchoolYearsAndSemesters(): array
    {
        SchoolYear::query()->update(['is_active' => false]);
        Semester::query()->update(['is_active' => false, 'enrollment_open' => false]);

        // Past school year
        $pastSY = SchoolYear::create([
            'name' => '2024-2025',
            'is_active' => false,
        ]);

        $pastSem1 = Semester::create([
            'school_year_id' => $pastSY->id,
            'number' => 1,
            'is_active' => false,
            'enrollment_open' => false,
        ]);

        $pastSem2 = Semester::create([
            'school_year_id' => $pastSY->id,
            'number' => 2,
            'is_active' => false,
            'enrollment_open' => false,
        ]);

        // Current school year
        $currentSY = SchoolYear::create([
            'name' => '2025-2026',
            'is_active' => true,
        ]);

        $currentSem1 = Semester::create([
            'school_year_id' => $currentSY->id,
            'number' => 1,
            'is_active' => true,
            'enrollment_open' => true,
        ]);

        Semester::create([
            'school_year_id' => $currentSY->id,
            'number' => 2,
            'is_active' => false,
            'enrollment_open' => false,
        ]);

        return [
            'past' => ['sy' => $pastSY, 'sem1' => $pastSem1, 'sem2' => $pastSem2],
            'current' => ['sy' => $currentSY, 'sem1' => $currentSem1],
        ];
    }

    /**
     * Create teacher and registrar users.
     */
    protected function createTeachersAndRegistrar(): \Illuminate\Support\Collection
    {
        $teacherData = [
            ['name' => 'Maria Santos', 'sex' => 'Female'],
            ['name' => 'Jose Reyes', 'sex' => 'Male'],
            ['name' => 'Ana Garcia', 'sex' => 'Female'],
            ['name' => 'Carlos Mendoza', 'sex' => 'Male'],
            ['name' => 'Rosa Rivera', 'sex' => 'Female'],
        ];

        $teachers = collect();

        foreach ($teacherData as $data) {
            $email = strtolower(str_replace(' ', '.', $data['name'])) . '@school.edu.ph';
            $user = User::create([
                'name' => $data['name'],
                'email' => $email,
                'password' => Hash::make('password'),
                'is_active' => true,
                'email_verified_at' => now(),
            ]);
            $user->assignRole(UserRole::Teacher->value);
            $user->sex = $data['sex'];
            $teachers->push($user);
        }

        $registrar = User::create([
            'name' => 'Elena Bautista',
            'email' => 'registrar@school.edu.ph',
            'password' => Hash::make('password'),
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
        $registrar->assignRole(UserRole::Registrar->value);

        return $teachers;
    }

    /**
     * Create teacher profiles and trainings for each teacher.
     */
    protected function createTeacherProfiles(\Illuminate\Support\Collection $teachers): void
    {
        $positions = ['Teacher I', 'Teacher II', 'Teacher III', 'Master Teacher I', 'Head Teacher I'];
        $appointments = ['Permanent', 'Permanent', 'Permanent', 'Contractual', 'Provisional'];
        $degrees = ["Bachelor's", "Bachelor's", "Master's", "Master's", "Doctorate"];
        $courses = [
            'Bachelor of Secondary Education',
            'Bachelor of Science in Education',
            'Master of Arts in Education',
            'Master of Arts in Teaching',
            'Doctor of Education',
        ];
        $majors = ['Mathematics', 'English', 'Science', 'Filipino', 'Social Studies'];
        $schools = [
            'University of Southern Mindanao',
            'Notre Dame of Marbel University',
            'Ramon Magsaysay Memorial Colleges',
            'Mindanao State University - General Santos',
            'Philippine Normal University - Mindanao',
        ];
        $specializations = ['Mathematics', 'English', 'Science', 'Filipino', 'Social Studies'];

        $trainingTitles = [
            'DepEd Regional Training on K-12 Curriculum',
            'National Educators Academy of the Philippines (NEAP) Seminar',
            'Division Learning Action Cell (LAC) Session',
            'INSET on Differentiated Instruction',
            'Webinar on Assessment in the New Normal',
            'Training on Learning Delivery Modalities',
            'Workshop on Action Research',
            'Seminar on Child Protection Policy',
            'Training on Results-Based Performance Management',
            'Capacity Building on ICT Integration',
        ];
        $trainingTypes = ['Seminar', 'Workshop', 'Training', 'Webinar', 'Conference'];
        $sponsors = ['DepEd', 'NEAP', 'Division Office', 'Regional Office', 'School-Based'];

        foreach ($teachers as $i => $teacher) {
            $profile = TeacherProfile::create([
                'user_id' => $teacher->id,
                'employee_id' => 'EMP-' . str_pad((string) ($i + 1), 5, '0', STR_PAD_LEFT),
                'position_title' => $positions[$i],
                'appointment_status' => $appointments[$i],
                'sex' => $teacher->sex,
                'birthdate' => now()->subYears(rand(28, 50))->subDays(rand(0, 365))->format('Y-m-d'),
                'contact_number' => '09' . rand(100000000, 999999999),
                'address' => $this->addresses[array_rand($this->addresses)],
                'highest_degree' => $degrees[$i],
                'degree_course' => $courses[$i],
                'degree_major' => $majors[$i],
                'school_graduated' => $schools[$i],
                'year_graduated' => rand(2000, 2018),
                'prc_license_number' => str_pad((string) rand(100000, 999999), 7, '0', STR_PAD_LEFT),
                'prc_validity' => now()->addYears(rand(1, 3))->format('Y-m-d'),
                'eligibility' => 'LET Passer',
                'specialization' => $specializations[$i],
                'date_hired' => now()->subYears(rand(2, 15))->subDays(rand(0, 365))->format('Y-m-d'),
                'teaching_hours_per_week' => rand(20, 30),
            ]);

            // Create 2-4 trainings per teacher
            $trainingCount = rand(2, 4);
            $usedTitles = [];
            for ($t = 0; $t < $trainingCount; $t++) {
                do {
                    $titleIndex = array_rand($trainingTitles);
                } while (in_array($titleIndex, $usedTitles));
                $usedTitles[] = $titleIndex;

                $dateFrom = now()->subMonths(rand(1, 24))->subDays(rand(0, 30));
                $days = rand(1, 5);

                TeacherTraining::create([
                    'teacher_profile_id' => $profile->id,
                    'title' => $trainingTitles[$titleIndex],
                    'type' => $trainingTypes[array_rand($trainingTypes)],
                    'sponsor' => $sponsors[array_rand($sponsors)],
                    'date_from' => $dateFrom->format('Y-m-d'),
                    'date_to' => $dateFrom->copy()->addDays($days)->format('Y-m-d'),
                    'hours' => $days * 8,
                ]);
            }
        }
    }

    /**
     * Create 200 students with full Filipino data.
     */
    protected function createStudents(): \Illuminate\Support\Collection
    {
        $students = collect();
        $modalities = ['Face to Face', 'Face to Face', 'Face to Face', 'Blended', 'Modular'];

        for ($i = 0; $i < 200; $i++) {
            $gender = $i % 2 === 0 ? 'male' : 'female';
            $firstName = $gender === 'male'
                ? $this->maleFirstNames[array_rand($this->maleFirstNames)]
                : $this->femaleFirstNames[array_rand($this->femaleFirstNames)];
            $lastName = $this->lastNames[array_rand($this->lastNames)];
            $middleName = $this->middleNames[array_rand($this->middleNames)];

            $lrn = str_pad((string) (300000000001 + $i), 12, '0', STR_PAD_LEFT);

            $year = rand(2006, 2009);
            $month = rand(1, 12);
            $day = rand(1, 28);
            $birthdate = sprintf('%04d-%02d-%02d', $year, $month, $day);

            // Assign varied statuses: 180 active, 8 transferred, 7 dropped, 5 graduated
            $status = match (true) {
                $i >= 195 => StudentStatus::Graduated,
                $i >= 188 => StudentStatus::Dropped,
                $i >= 180 => StudentStatus::Transferred,
                default => StudentStatus::Active,
            };

            $fatherLastName = $lastName;
            $motherLastName = $this->lastNames[array_rand($this->lastNames)];

            $student = Student::create([
                'lrn' => $lrn,
                'last_name' => $lastName,
                'first_name' => $firstName,
                'middle_name' => $middleName,
                'suffix' => $this->suffixes[array_rand($this->suffixes)],
                'birthdate' => $birthdate,
                'gender' => $gender,
                'religion' => $this->religions[array_rand($this->religions)],
                'learning_modality' => $modalities[array_rand($modalities)],
                'address' => $this->addresses[array_rand($this->addresses)],
                'contact_number' => '09' . rand(100000000, 999999999),
                'father_name' => $fatherLastName . ', ' . $this->maleFirstNames[array_rand($this->maleFirstNames)],
                'mother_name' => $motherLastName . ', ' . $this->femaleFirstNames[array_rand($this->femaleFirstNames)],
                'guardian_name' => $this->lastNames[array_rand($this->lastNames)] . ', ' . ($gender === 'male'
                    ? $this->femaleFirstNames[array_rand($this->femaleFirstNames)]
                    : $this->maleFirstNames[array_rand($this->maleFirstNames)]),
                'guardian_contact' => '09' . rand(100000000, 999999999),
                'guardian_relationship' => ['Mother', 'Father', 'Guardian', 'Grandmother', 'Grandfather'][array_rand(['Mother', 'Father', 'Guardian', 'Grandmother', 'Grandfather'])],
                'previous_school' => $i < 150 ? $this->previousSchools[array_rand($this->previousSchools)] : null,
                'status' => $status->value,
            ]);

            // Create user accounts for the first 3 students for testing
            if ($i < 3) {
                $user = User::create([
                    'name' => $firstName . ' ' . $lastName,
                    'email' => 'student' . ($i + 1) . '@school.edu.ph',
                    'password' => Hash::make('password'),
                    'is_active' => true,
                    'email_verified_at' => now(),
                ]);
                $user->assignRole(UserRole::Student->value);
                $student->update(['user_id' => $user->id]);
            }

            $students->push($student);
        }

        return $students;
    }

    /**
     * Create sections for a given semester.
     */
    protected function createSections(Semester $semester, string $prefix): \Illuminate\Support\Collection
    {
        $strands = Strand::all();
        $teachers = User::role(UserRole::Teacher->value)->get();
        $sections = collect();
        $teacherIndex = 0;

        $gradeLevel = match ($prefix) {
            'past', 'past2' => 11,
            default => 12,
        };

        $sectionNames = ['A', 'B'];

        foreach ($strands as $strand) {
            foreach ($sectionNames as $suffix) {
                $sectionName = $strand->code . ' ' . $gradeLevel . '-' . $suffix;

                $section = Section::create([
                    'name' => $sectionName,
                    'strand_id' => $strand->id,
                    'semester_id' => $semester->id,
                    'grade_level' => $gradeLevel,
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
     * Enroll students and generate grades.
     */
    protected function enrollStudents($students, $sections, Semester $semester, bool $completed): void
    {
        $sectionCount = $sections->count();
        $registrar = User::role(UserRole::Registrar->value)->first()
            ?? User::role(UserRole::Admin->value)->first();

        $semesterNumber = $semester->number;
        $gradeLevel = $sections->first()->grade_level;

        foreach ($students as $index => $student) {
            $section = $sections[$index % $sectionCount];

            // Vary enrollment statuses for current semester
            $enrollmentStatus = EnrollmentStatus::Enrolled;
            if (! $completed && $index >= 180) {
                $enrollmentStatus = match (true) {
                    $index >= 195 => EnrollmentStatus::Dropped,
                    $index >= 190 => EnrollmentStatus::Transferred,
                    $index >= 185 => EnrollmentStatus::Pending,
                    default => EnrollmentStatus::Enrolled,
                };
            }

            $enrollment = Enrollment::create([
                'student_id' => $student->id,
                'section_id' => $section->id,
                'semester_id' => $semester->id,
                'status' => $enrollmentStatus,
                'enrolled_at' => $enrollmentStatus === EnrollmentStatus::Pending
                    ? null
                    : ($completed ? now()->subMonths(rand(6, 10)) : now()->subDays(rand(1, 30))),
            ]);

            // Get subjects for this strand/grade/semester
            $subjects = $section->strand->subjects()
                ->wherePivot('grade_level', $gradeLevel)
                ->wherePivot('semester', $semesterNumber)
                ->get();

            foreach ($subjects as $subject) {
                $hasGrades = $completed || ($index < 100 && ! $completed);

                $midterm = null;
                $finals = null;
                $finalGrade = null;
                $remarks = null;

                if ($hasGrades && $enrollmentStatus === EnrollmentStatus::Enrolled) {
                    $midterm = rand(70, 99);

                    if ($completed) {
                        $finals = rand(70, 99);
                        $finalGrade = round(($midterm + $finals) / 2, 2);
                        $remarks = $finalGrade >= 75 ? GradeRemarks::Passed : GradeRemarks::Failed;
                    }
                }

                Grade::create([
                    'enrollment_id' => $enrollment->id,
                    'subject_id' => $subject->id,
                    'midterm' => $midterm,
                    'finals' => $finals,
                    'final_grade' => $finalGrade,
                    'remarks' => $remarks,
                    'is_locked' => $completed,
                    'encoded_by' => $hasGrades ? $registrar?->id : null,
                ]);
            }
        }
    }

    /**
     * Create sample audit logs.
     */
    protected function createAuditLogs($students, $teachers): void
    {
        $admin = User::role(UserRole::Admin->value)->first();
        $registrar = User::role(UserRole::Registrar->value)->first();

        $actors = collect([$admin, $registrar])->filter();

        // Student creation logs
        foreach ($students->take(20) as $student) {
            AuditLog::create([
                'user_id' => $actors->random()->id,
                'action' => 'created',
                'model_type' => Student::class,
                'model_id' => $student->id,
                'old_values' => null,
                'new_values' => ['lrn' => $student->lrn, 'last_name' => $student->last_name, 'first_name' => $student->first_name],
                'ip_address' => '127.0.0.1',
                'created_at' => now()->subDays(rand(1, 30)),
            ]);
        }

        // Enrollment logs
        $enrollments = Enrollment::take(10)->get();
        foreach ($enrollments as $enrollment) {
            AuditLog::create([
                'user_id' => $actors->random()->id,
                'action' => 'created',
                'model_type' => Enrollment::class,
                'model_id' => $enrollment->id,
                'old_values' => null,
                'new_values' => ['student_id' => $enrollment->student_id, 'status' => 'enrolled'],
                'ip_address' => '127.0.0.1',
                'created_at' => now()->subDays(rand(1, 15)),
            ]);
        }

        // Grade update logs
        $grades = Grade::whereNotNull('final_grade')->take(10)->get();
        foreach ($grades as $grade) {
            AuditLog::create([
                'user_id' => $actors->random()->id,
                'action' => 'updated',
                'model_type' => Grade::class,
                'model_id' => $grade->id,
                'old_values' => ['midterm' => null, 'finals' => null],
                'new_values' => ['midterm' => $grade->midterm, 'finals' => $grade->finals],
                'ip_address' => '127.0.0.1',
                'created_at' => now()->subDays(rand(1, 10)),
            ]);
        }

        // Student status change logs
        $statusChangedStudents = Student::whereIn('status', ['dropped', 'transferred'])->take(5)->get();
        foreach ($statusChangedStudents as $student) {
            AuditLog::create([
                'user_id' => $actors->random()->id,
                'action' => 'updated',
                'model_type' => Student::class,
                'model_id' => $student->id,
                'old_values' => ['status' => 'active'],
                'new_values' => ['status' => $student->status],
                'ip_address' => '127.0.0.1',
                'created_at' => now()->subDays(rand(1, 7)),
            ]);
        }
    }
}
