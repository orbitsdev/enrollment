<?php

namespace Database\Seeders;

use App\Enums\SubjectType;
use App\Models\Strand;
use App\Models\Subject;
use Illuminate\Database\Seeder;

class SubjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Define core subjects by grade level and semester
        $coreSubjects = [
            // Grade 11, Semester 1
            ['code' => 'ORAL-COM', 'name' => 'Oral Communication', 'grade_level' => 11, 'semester' => 1, 'sort_order' => 1],
            ['code' => 'KOM-PAN', 'name' => 'Komunikasyon at Pananaliksik', 'grade_level' => 11, 'semester' => 1, 'sort_order' => 2],
            ['code' => 'GEN-MATH', 'name' => 'General Mathematics', 'grade_level' => 11, 'semester' => 1, 'sort_order' => 3],
            ['code' => 'EARTH-SCI', 'name' => 'Earth and Life Science', 'grade_level' => 11, 'semester' => 1, 'sort_order' => 4],
            ['code' => '21ST-LIT', 'name' => '21st Century Literature', 'grade_level' => 11, 'semester' => 1, 'sort_order' => 5],
            ['code' => 'PE-1', 'name' => 'PE and Health 1', 'grade_level' => 11, 'semester' => 1, 'sort_order' => 6],
            ['code' => 'PERDEV', 'name' => 'Personal Development', 'grade_level' => 11, 'semester' => 1, 'sort_order' => 7],

            // Grade 11, Semester 2
            ['code' => 'READ-WRITE', 'name' => 'Reading and Writing', 'grade_level' => 11, 'semester' => 2, 'sort_order' => 1],
            ['code' => 'PAG-SUR', 'name' => 'Pagbasa at Pagsusuri', 'grade_level' => 11, 'semester' => 2, 'sort_order' => 2],
            ['code' => 'STAT-PROB', 'name' => 'Statistics and Probability', 'grade_level' => 11, 'semester' => 2, 'sort_order' => 3],
            ['code' => 'PHY-SCI', 'name' => 'Physical Science', 'grade_level' => 11, 'semester' => 2, 'sort_order' => 4],
            ['code' => 'CPAR', 'name' => 'Contemporary Philippine Arts', 'grade_level' => 11, 'semester' => 2, 'sort_order' => 5],
            ['code' => 'PE-2', 'name' => 'PE and Health 2', 'grade_level' => 11, 'semester' => 2, 'sort_order' => 6],
            ['code' => 'UCSP', 'name' => 'Understanding Culture Society and Politics', 'grade_level' => 11, 'semester' => 2, 'sort_order' => 7],

            // Grade 12, Semester 1
            ['code' => 'EAPP', 'name' => 'English for Academic and Professional Purposes', 'grade_level' => 12, 'semester' => 1, 'sort_order' => 1],
            ['code' => 'FIL-PIL', 'name' => 'Filipino sa Piling Larangan', 'grade_level' => 12, 'semester' => 1, 'sort_order' => 2],
            ['code' => 'PR-1', 'name' => 'Practical Research 1', 'grade_level' => 12, 'semester' => 1, 'sort_order' => 3],
            ['code' => 'EMPTECH', 'name' => 'Empowerment Technologies', 'grade_level' => 12, 'semester' => 1, 'sort_order' => 4],
            ['code' => 'PHILO', 'name' => 'Introduction to Philosophy', 'grade_level' => 12, 'semester' => 1, 'sort_order' => 5],
            ['code' => 'PE-3', 'name' => 'PE and Health 3', 'grade_level' => 12, 'semester' => 1, 'sort_order' => 6],
            ['code' => 'COMMENG', 'name' => 'Community Engagement', 'grade_level' => 12, 'semester' => 1, 'sort_order' => 7],

            // Grade 12, Semester 2
            ['code' => 'PR-2', 'name' => 'Practical Research 2', 'grade_level' => 12, 'semester' => 2, 'sort_order' => 1],
            ['code' => 'ENTREP', 'name' => 'Entrepreneurship', 'grade_level' => 12, 'semester' => 2, 'sort_order' => 2],
            ['code' => 'III', 'name' => 'Inquiries Investigations and Immersion', 'grade_level' => 12, 'semester' => 2, 'sort_order' => 3],
            ['code' => 'PE-4', 'name' => 'PE and Health 4', 'grade_level' => 12, 'semester' => 2, 'sort_order' => 4],
            ['code' => 'MIL', 'name' => 'Media and Information Literacy', 'grade_level' => 12, 'semester' => 2, 'sort_order' => 5],
        ];

        // Get all strand IDs
        $strandIds = Strand::pluck('id')->toArray();

        // Create subjects and attach to all strands
        foreach ($coreSubjects as $subjectData) {
            $gradeLevel = $subjectData['grade_level'];
            $semester = $subjectData['semester'];
            $sortOrder = $subjectData['sort_order'];

            unset($subjectData['grade_level'], $subjectData['semester'], $subjectData['sort_order']);

            $subject = Subject::create(array_merge($subjectData, [
                'type' => SubjectType::Core->value,
                'hours' => 80,
            ]));

            // Attach to all strands with grade_level, semester, and sort_order
            $pivotData = [];
            foreach ($strandIds as $strandId) {
                $pivotData[$strandId] = [
                    'grade_level' => $gradeLevel,
                    'semester' => $semester,
                    'sort_order' => $sortOrder,
                ];
            }

            $subject->strands()->attach($pivotData);
        }

        // Set prerequisite relationships
        $prerequisites = [
            'PR-2' => 'PR-1',
            'PE-2' => 'PE-1',
            'PE-3' => 'PE-2',
            'PE-4' => 'PE-3',
        ];

        foreach ($prerequisites as $subjectCode => $prerequisiteCode) {
            $subject = Subject::where('code', $subjectCode)->first();
            $prerequisite = Subject::where('code', $prerequisiteCode)->first();

            if ($subject && $prerequisite) {
                $subject->update(['prerequisite_id' => $prerequisite->id]);
            }
        }
    }
}
