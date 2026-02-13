<?php

namespace Database\Seeders;

use App\Enums\StudentStatus;
use App\Enums\SubjectType;
use App\Enums\UserRole;
use App\Models\SchoolYear;
use App\Models\Section;
use App\Models\Semester;
use App\Models\Strand;
use App\Models\Student;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Seeder;

class EnrollmentTestSeeder extends Seeder
{
    /**
     * Seed test data for enrollment wizard testing — Grade 11 & 12.
     *
     * Run with: php artisan db:seed --class=EnrollmentTestSeeder
     */
    public function run(): void
    {
        $semester = $this->ensureActiveSemester();
        $this->createSectionsForBothGrades($semester);
        $this->createSpecializedSubjects();
        $this->createTestStudents();

        $this->command->info('Enrollment test data seeded for Grade 11 & 12.');
    }

    /**
     * Ensure there is an active school year with an open semester.
     */
    protected function ensureActiveSemester(): Semester
    {
        $semester = Semester::where('is_active', true)
            ->where('enrollment_open', true)
            ->first();

        if ($semester) {
            $this->command->info("Using existing active semester: {$semester->schoolYear->name} - Semester {$semester->number}");

            return $semester;
        }

        // Create one if missing
        $sy = SchoolYear::where('is_active', true)->first();

        if (! $sy) {
            $sy = SchoolYear::create(['name' => '2025-2026', 'is_active' => true]);
        }

        $semester = Semester::create([
            'school_year_id' => $sy->id,
            'number' => 1,
            'is_active' => true,
            'enrollment_open' => true,
        ]);

        $this->command->info("Created active semester: {$sy->name} - Semester 1");

        return $semester;
    }

    /**
     * Create sections for BOTH Grade 11 and Grade 12 in the active semester.
     */
    protected function createSectionsForBothGrades(Semester $semester): void
    {
        $strands = Strand::where('is_active', true)->get();
        $teachers = User::role(UserRole::Teacher->value)->get();
        $teacherIndex = 0;
        $created = 0;

        foreach ([11, 12] as $gradeLevel) {
            foreach ($strands as $strand) {
                foreach (['A', 'B'] as $suffix) {
                    $name = "{$strand->code} {$gradeLevel}-{$suffix}";

                    $exists = Section::where('name', $name)
                        ->where('semester_id', $semester->id)
                        ->exists();

                    if ($exists) {
                        continue;
                    }

                    $adviserId = $teachers->isNotEmpty()
                        ? $teachers[$teacherIndex % $teachers->count()]->id
                        : null;

                    Section::create([
                        'name' => $name,
                        'strand_id' => $strand->id,
                        'semester_id' => $semester->id,
                        'grade_level' => $gradeLevel,
                        'max_capacity' => 50,
                        'adviser_id' => $adviserId,
                    ]);

                    $teacherIndex++;
                    $created++;
                }
            }
        }

        $this->command->info("Sections created: {$created} (skipped existing).");
    }

    /**
     * Create specialized and applied subjects per strand.
     */
    protected function createSpecializedSubjects(): void
    {
        $strandSubjects = [
            // STEM specialized
            'STEM' => [
                ['code' => 'PRE-CAL', 'name' => 'Pre-Calculus', 'type' => SubjectType::Specialized, 'hours' => 80, 'grade_level' => 11, 'semester' => 1],
                ['code' => 'BAS-CAL', 'name' => 'Basic Calculus', 'type' => SubjectType::Specialized, 'hours' => 80, 'grade_level' => 11, 'semester' => 2],
                ['code' => 'GEN-BIO1', 'name' => 'General Biology 1', 'type' => SubjectType::Specialized, 'hours' => 80, 'grade_level' => 11, 'semester' => 1],
                ['code' => 'GEN-BIO2', 'name' => 'General Biology 2', 'type' => SubjectType::Specialized, 'hours' => 80, 'grade_level' => 11, 'semester' => 2],
                ['code' => 'GEN-PHY1', 'name' => 'General Physics 1', 'type' => SubjectType::Specialized, 'hours' => 80, 'grade_level' => 12, 'semester' => 1],
                ['code' => 'GEN-PHY2', 'name' => 'General Physics 2', 'type' => SubjectType::Specialized, 'hours' => 80, 'grade_level' => 12, 'semester' => 2],
                ['code' => 'GEN-CHEM1', 'name' => 'General Chemistry 1', 'type' => SubjectType::Specialized, 'hours' => 80, 'grade_level' => 11, 'semester' => 1],
                ['code' => 'GEN-CHEM2', 'name' => 'General Chemistry 2', 'type' => SubjectType::Specialized, 'hours' => 80, 'grade_level' => 11, 'semester' => 2],
                ['code' => 'STEM-RES', 'name' => 'Research in STEM', 'type' => SubjectType::Specialized, 'hours' => 80, 'grade_level' => 12, 'semester' => 1],
                ['code' => 'STEM-CAP', 'name' => 'STEM Capstone Project', 'type' => SubjectType::Specialized, 'hours' => 80, 'grade_level' => 12, 'semester' => 2],
            ],

            // ABM specialized
            'ABM' => [
                ['code' => 'ABM-BM', 'name' => 'Business Mathematics', 'type' => SubjectType::Specialized, 'hours' => 80, 'grade_level' => 11, 'semester' => 1],
                ['code' => 'ABM-FM', 'name' => 'Fundamentals of ABM 1', 'type' => SubjectType::Specialized, 'hours' => 80, 'grade_level' => 11, 'semester' => 1],
                ['code' => 'ABM-FM2', 'name' => 'Fundamentals of ABM 2', 'type' => SubjectType::Specialized, 'hours' => 80, 'grade_level' => 11, 'semester' => 2],
                ['code' => 'ABM-ACCT1', 'name' => 'Principles of Accounting', 'type' => SubjectType::Specialized, 'hours' => 80, 'grade_level' => 11, 'semester' => 2],
                ['code' => 'ABM-OrgM', 'name' => 'Organization and Management', 'type' => SubjectType::Specialized, 'hours' => 80, 'grade_level' => 12, 'semester' => 1],
                ['code' => 'ABM-BE', 'name' => 'Business Ethics', 'type' => SubjectType::Specialized, 'hours' => 80, 'grade_level' => 12, 'semester' => 1],
                ['code' => 'ABM-BFIN', 'name' => 'Business Finance', 'type' => SubjectType::Specialized, 'hours' => 80, 'grade_level' => 12, 'semester' => 2],
                ['code' => 'ABM-BMKT', 'name' => 'Principles of Marketing', 'type' => SubjectType::Specialized, 'hours' => 80, 'grade_level' => 12, 'semester' => 2],
            ],

            // HUMSS specialized
            'HUMSS' => [
                ['code' => 'HMS-DIASS', 'name' => 'Disciplines and Ideas in Social Sciences', 'type' => SubjectType::Specialized, 'hours' => 80, 'grade_level' => 11, 'semester' => 1],
                ['code' => 'HMS-CW', 'name' => 'Creative Writing', 'type' => SubjectType::Specialized, 'hours' => 80, 'grade_level' => 11, 'semester' => 1],
                ['code' => 'HMS-CNF', 'name' => 'Creative Nonfiction', 'type' => SubjectType::Specialized, 'hours' => 80, 'grade_level' => 11, 'semester' => 2],
                ['code' => 'HMS-WRP', 'name' => 'World Religions and Belief Systems', 'type' => SubjectType::Specialized, 'hours' => 80, 'grade_level' => 11, 'semester' => 2],
                ['code' => 'HMS-TRENDS', 'name' => 'Trends Networks and Critical Thinking', 'type' => SubjectType::Specialized, 'hours' => 80, 'grade_level' => 12, 'semester' => 1],
                ['code' => 'HMS-PG', 'name' => 'Philippine Politics and Governance', 'type' => SubjectType::Specialized, 'hours' => 80, 'grade_level' => 12, 'semester' => 1],
                ['code' => 'HMS-COMRES', 'name' => 'Community Based Research', 'type' => SubjectType::Specialized, 'hours' => 80, 'grade_level' => 12, 'semester' => 2],
                ['code' => 'HMS-CULLIT', 'name' => 'Philippine Popular Culture', 'type' => SubjectType::Specialized, 'hours' => 80, 'grade_level' => 12, 'semester' => 2],
            ],

            // GAS specialized
            'GAS' => [
                ['code' => 'GAS-HUM1', 'name' => 'Humanities 1', 'type' => SubjectType::Specialized, 'hours' => 80, 'grade_level' => 11, 'semester' => 1],
                ['code' => 'GAS-HUM2', 'name' => 'Humanities 2', 'type' => SubjectType::Specialized, 'hours' => 80, 'grade_level' => 11, 'semester' => 2],
                ['code' => 'GAS-SS1', 'name' => 'Social Science 1', 'type' => SubjectType::Specialized, 'hours' => 80, 'grade_level' => 11, 'semester' => 1],
                ['code' => 'GAS-SS2', 'name' => 'Social Science 2', 'type' => SubjectType::Specialized, 'hours' => 80, 'grade_level' => 11, 'semester' => 2],
                ['code' => 'GAS-ELEC1', 'name' => 'GAS Elective 1', 'type' => SubjectType::Applied, 'hours' => 80, 'grade_level' => 12, 'semester' => 1],
                ['code' => 'GAS-ELEC2', 'name' => 'GAS Elective 2', 'type' => SubjectType::Applied, 'hours' => 80, 'grade_level' => 12, 'semester' => 2],
            ],

            // ICT specialized
            'ICT' => [
                ['code' => 'ICT-CSS1', 'name' => 'Computer System Servicing 1', 'type' => SubjectType::Specialized, 'hours' => 80, 'grade_level' => 11, 'semester' => 1],
                ['code' => 'ICT-CSS2', 'name' => 'Computer System Servicing 2', 'type' => SubjectType::Specialized, 'hours' => 80, 'grade_level' => 11, 'semester' => 2],
                ['code' => 'ICT-NET1', 'name' => 'Computer Networks 1', 'type' => SubjectType::Specialized, 'hours' => 80, 'grade_level' => 12, 'semester' => 1],
                ['code' => 'ICT-NET2', 'name' => 'Computer Networks 2', 'type' => SubjectType::Specialized, 'hours' => 80, 'grade_level' => 12, 'semester' => 2],
                ['code' => 'ICT-WD', 'name' => 'Web Development', 'type' => SubjectType::Specialized, 'hours' => 80, 'grade_level' => 11, 'semester' => 1],
                ['code' => 'ICT-PROG', 'name' => 'Programming', 'type' => SubjectType::Specialized, 'hours' => 80, 'grade_level' => 12, 'semester' => 1],
            ],

            // HE specialized
            'HE' => [
                ['code' => 'HE-BPP1', 'name' => 'Bread and Pastry Production 1', 'type' => SubjectType::Specialized, 'hours' => 80, 'grade_level' => 11, 'semester' => 1],
                ['code' => 'HE-BPP2', 'name' => 'Bread and Pastry Production 2', 'type' => SubjectType::Specialized, 'hours' => 80, 'grade_level' => 11, 'semester' => 2],
                ['code' => 'HE-COOK1', 'name' => 'Cookery 1', 'type' => SubjectType::Specialized, 'hours' => 80, 'grade_level' => 12, 'semester' => 1],
                ['code' => 'HE-COOK2', 'name' => 'Cookery 2', 'type' => SubjectType::Specialized, 'hours' => 80, 'grade_level' => 12, 'semester' => 2],
                ['code' => 'HE-FNB', 'name' => 'Food and Beverage Services', 'type' => SubjectType::Specialized, 'hours' => 80, 'grade_level' => 11, 'semester' => 2],
                ['code' => 'HE-HK', 'name' => 'Housekeeping', 'type' => SubjectType::Specialized, 'hours' => 80, 'grade_level' => 12, 'semester' => 1],
            ],

            // AFA specialized
            'AFA' => [
                ['code' => 'AFA-AP1', 'name' => 'Agricultural Crops Production 1', 'type' => SubjectType::Specialized, 'hours' => 80, 'grade_level' => 11, 'semester' => 1],
                ['code' => 'AFA-AP2', 'name' => 'Agricultural Crops Production 2', 'type' => SubjectType::Specialized, 'hours' => 80, 'grade_level' => 11, 'semester' => 2],
                ['code' => 'AFA-ANP1', 'name' => 'Animal Production 1', 'type' => SubjectType::Specialized, 'hours' => 80, 'grade_level' => 12, 'semester' => 1],
                ['code' => 'AFA-ANP2', 'name' => 'Animal Production 2', 'type' => SubjectType::Specialized, 'hours' => 80, 'grade_level' => 12, 'semester' => 2],
                ['code' => 'AFA-FISH', 'name' => 'Fishery Arts', 'type' => SubjectType::Specialized, 'hours' => 80, 'grade_level' => 11, 'semester' => 1],
                ['code' => 'AFA-LAND', 'name' => 'Landscape Installation', 'type' => SubjectType::Specialized, 'hours' => 80, 'grade_level' => 12, 'semester' => 2],
            ],
        ];

        $created = 0;

        foreach ($strandSubjects as $strandCode => $subjects) {
            $strand = Strand::where('code', $strandCode)->first();

            if (! $strand) {
                $this->command->warn("Strand '{$strandCode}' not found, skipping.");

                continue;
            }

            foreach ($subjects as $subjectData) {
                $gradeLevel = $subjectData['grade_level'];
                $semester = $subjectData['semester'];
                unset($subjectData['grade_level'], $subjectData['semester']);

                // Skip if subject code already exists
                $subject = Subject::where('code', $subjectData['code'])->first();

                if (! $subject) {
                    $subject = Subject::create(array_merge($subjectData, [
                        'is_active' => true,
                    ]));
                    $created++;
                }

                // Attach to strand if not already attached for this grade/semester
                $alreadyAttached = $strand->subjects()
                    ->where('subjects.id', $subject->id)
                    ->wherePivot('grade_level', $gradeLevel)
                    ->wherePivot('semester', $semester)
                    ->exists();

                if (! $alreadyAttached) {
                    $strand->subjects()->attach($subject->id, [
                        'grade_level' => $gradeLevel,
                        'semester' => $semester,
                        'sort_order' => 10 + $created,
                    ]);
                }
            }
        }

        // Set prerequisite: GEN-BIO2 requires GEN-BIO1, GEN-PHY2 requires GEN-PHY1, etc.
        $prerequisites = [
            'GEN-BIO2' => 'GEN-BIO1',
            'GEN-PHY2' => 'GEN-PHY1',
            'GEN-CHEM2' => 'GEN-CHEM1',
            'BAS-CAL' => 'PRE-CAL',
            'ICT-CSS2' => 'ICT-CSS1',
            'ICT-NET2' => 'ICT-NET1',
            'HE-BPP2' => 'HE-BPP1',
            'HE-COOK2' => 'HE-COOK1',
            'AFA-AP2' => 'AFA-AP1',
            'AFA-ANP2' => 'AFA-ANP1',
            'ABM-FM2' => 'ABM-FM',
        ];

        foreach ($prerequisites as $subjectCode => $prereqCode) {
            $subject = Subject::where('code', $subjectCode)->first();
            $prereq = Subject::where('code', $prereqCode)->first();

            if ($subject && $prereq && ! $subject->prerequisite_id) {
                $subject->update(['prerequisite_id' => $prereq->id]);
            }
        }

        $this->command->info("Specialized subjects created: {$created}.");
    }

    /**
     * Create test students that are NOT enrolled (ready for enrollment testing).
     */
    protected function createTestStudents(): void
    {
        $testStudents = [
            [
                'lrn' => '400000000001',
                'last_name' => 'Test',
                'first_name' => 'Grade Eleven',
                'middle_name' => 'Demo',
                'gender' => 'male',
                'birthdate' => '2008-05-15',
                'address' => 'Test Address, Lake Sebu',
                'status' => StudentStatus::Active,
            ],
            [
                'lrn' => '400000000002',
                'last_name' => 'Test',
                'first_name' => 'Grade Twelve',
                'middle_name' => 'Demo',
                'gender' => 'female',
                'birthdate' => '2007-08-22',
                'address' => 'Test Address, Lake Sebu',
                'status' => StudentStatus::Active,
            ],
            [
                'lrn' => '400000000003',
                'last_name' => 'Santos',
                'first_name' => 'Maria Clara',
                'middle_name' => 'Reyes',
                'gender' => 'female',
                'birthdate' => '2008-01-10',
                'address' => 'Brgy. Poblacion, Lake Sebu',
                'status' => StudentStatus::Active,
            ],
            [
                'lrn' => '400000000004',
                'last_name' => 'Dela Cruz',
                'first_name' => 'Juan Carlos',
                'middle_name' => 'Garcia',
                'gender' => 'male',
                'birthdate' => '2007-11-03',
                'address' => 'Brgy. San Antonio, Lake Sebu',
                'status' => StudentStatus::Active,
            ],
            [
                'lrn' => '400000000005',
                'last_name' => 'Bautista',
                'first_name' => 'Angela Rose',
                'middle_name' => 'Mendoza',
                'gender' => 'female',
                'birthdate' => '2008-03-28',
                'address' => 'Brgy. Lamdalag, Lake Sebu',
                'status' => StudentStatus::Active,
            ],
        ];

        $created = 0;

        foreach ($testStudents as $data) {
            if (Student::where('lrn', $data['lrn'])->exists()) {
                continue;
            }

            Student::create($data);
            $created++;
        }

        $this->command->info("Test students created: {$created} (skipped existing).");
        $this->command->info('Test LRNs: 400000000001 - 400000000005 (search for "Test" or "400" in wizard).');
    }
}
