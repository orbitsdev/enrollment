# Database, API & Navigation — Final Specification

## Lake Sebu NHS — Enrollment & Student Records System

---

## 1. Database Migrations

### 1.1 Migration Order (dependencies matter)

```
01 — users (Laravel default + modifications)
02 — school_years
03 — semesters
04 — tracks
05 — strands
06 — subjects
07 — subject_strand (pivot)
08 — students
09 — sections
10 — enrollments
11 — enrollment_subject (pivot)
12 — grades
13 — school_settings
14 — audit_logs
15 — Spatie permission tables (auto-published)
16 — Spatie media library tables (auto-published)
```

---

### 1.2 Full Migration Definitions

#### `users`

```php
Schema::create('users', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('email')->unique();
    $table->timestamp('email_verified_at')->nullable();
    $table->string('password');
    $table->boolean('is_active')->default(true);
    $table->rememberToken();
    $table->timestamps();
});
```

> Roles handled by Spatie Permission — no `role` column on users table.

---

#### `school_years`

```php
Schema::create('school_years', function (Blueprint $table) {
    $table->id();
    $table->string('name');                    // "2026-2027"
    $table->boolean('is_active')->default(false);
    $table->timestamps();

    $table->unique('name');
});
```

---

#### `semesters`

```php
Schema::create('semesters', function (Blueprint $table) {
    $table->id();
    $table->foreignId('school_year_id')->constrained()->cascadeOnDelete();
    $table->tinyInteger('number');             // 1 or 2
    $table->boolean('is_active')->default(false);
    $table->boolean('enrollment_open')->default(false);
    $table->timestamps();

    $table->unique(['school_year_id', 'number']);
});
```

---

#### `tracks`

```php
Schema::create('tracks', function (Blueprint $table) {
    $table->id();
    $table->string('name');                    // "Academic", "TVL"
    $table->string('code', 10);                // "ACAD", "TVL"
    $table->boolean('is_active')->default(true);
    $table->integer('sort_order')->default(0);
    $table->timestamps();
});
```

---

#### `strands`

```php
Schema::create('strands', function (Blueprint $table) {
    $table->id();
    $table->foreignId('track_id')->constrained()->cascadeOnDelete();
    $table->string('name');                    // "STEM", "ABM"
    $table->string('code', 10);                // "STEM", "ABM"
    $table->boolean('is_active')->default(true);
    $table->integer('sort_order')->default(0);
    $table->timestamps();

    $table->unique(['track_id', 'code']);
});
```

---

#### `subjects`

```php
Schema::create('subjects', function (Blueprint $table) {
    $table->id();
    $table->string('code', 20)->unique();      // "GM-101"
    $table->string('name');                    // "General Mathematics"
    $table->string('type');                    // core, specialized, applied
    $table->decimal('hours', 4, 1)->default(80); // total hours
    $table->foreignId('prerequisite_id')
          ->nullable()
          ->constrained('subjects')
          ->nullOnDelete();
    $table->boolean('is_active')->default(true);
    $table->timestamps();

    $table->index('type');
});
```

---

#### `subject_strand` (pivot — the subject load template)

```php
Schema::create('subject_strand', function (Blueprint $table) {
    $table->id();
    $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
    $table->foreignId('strand_id')->constrained()->cascadeOnDelete();
    $table->tinyInteger('grade_level');        // 11 or 12
    $table->tinyInteger('semester');            // 1 or 2
    $table->integer('sort_order')->default(0);
    $table->timestamps();

    $table->unique(['subject_id', 'strand_id', 'grade_level', 'semester'], 'subject_strand_unique');
    $table->index(['strand_id', 'grade_level', 'semester']);
});
```

---

#### `students`

```php
Schema::create('students', function (Blueprint $table) {
    $table->id();
    $table->string('lrn', 12)->unique();       // Learner Reference Number
    $table->string('last_name');
    $table->string('first_name');
    $table->string('middle_name')->nullable();
    $table->string('suffix')->nullable();       // Jr., III, etc.
    $table->date('birthdate');
    $table->string('gender');                   // male, female
    $table->text('address')->nullable();        // single free-text address field
    $table->string('contact_number')->nullable();
    $table->string('guardian_name')->nullable();
    $table->string('guardian_contact')->nullable();
    $table->string('guardian_relationship')->nullable();
    $table->string('previous_school')->nullable();
    $table->string('status')->default('active'); // active, transferred, dropped, graduated
    $table->timestamps();
});
```

---

#### `sections`

```php
Schema::create('sections', function (Blueprint $table) {
    $table->id();
    $table->string('name', 50);                // "STEM-A"
    $table->foreignId('strand_id')->constrained()->cascadeOnDelete();
    $table->foreignId('semester_id')->constrained()->cascadeOnDelete();
    $table->tinyInteger('grade_level');         // 11 or 12
    $table->integer('max_capacity')->default(50);
    $table->foreignId('adviser_id')
          ->nullable()
          ->constrained('users')
          ->nullOnDelete();
    $table->timestamps();

    $table->unique(['name', 'semester_id', 'grade_level']);
    $table->index(['strand_id', 'semester_id', 'grade_level']);
});
```

---

#### `enrollments`

```php
Schema::create('enrollments', function (Blueprint $table) {
    $table->id();
    $table->foreignId('student_id')->constrained()->cascadeOnDelete();
    $table->foreignId('section_id')->constrained()->cascadeOnDelete();
    $table->foreignId('semester_id')->constrained()->cascadeOnDelete();
    $table->string('status')->default('enrolled'); // pending, enrolled, dropped, transferred
    $table->text('remarks')->nullable();        // reason for drop/transfer
    $table->timestamp('enrolled_at')->nullable();
    $table->timestamps();

    $table->unique(['student_id', 'semester_id']); // one enrollment per student per semester
    $table->index('status');
    $table->index(['section_id', 'status']);
    $table->index(['semester_id', 'status']);
});
```

---

#### `enrollment_subject` (pivot)

```php
Schema::create('enrollment_subject', function (Blueprint $table) {
    $table->id();
    $table->foreignId('enrollment_id')->constrained()->cascadeOnDelete();
    $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
    $table->timestamps();

    $table->unique(['enrollment_id', 'subject_id']);
});
```

---

#### `grades`

```php
Schema::create('grades', function (Blueprint $table) {
    $table->id();
    $table->foreignId('enrollment_id')->constrained()->cascadeOnDelete();
    $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
    $table->decimal('midterm', 5, 2)->nullable();
    $table->decimal('finals', 5, 2)->nullable();
    $table->decimal('final_grade', 5, 2)->nullable();
    $table->string('remarks', 20)->nullable(); // passed, failed
    $table->boolean('is_locked')->default(false);
    $table->foreignId('encoded_by')
          ->nullable()
          ->constrained('users')
          ->nullOnDelete();
    $table->timestamps();

    $table->unique(['enrollment_id', 'subject_id']);
    $table->index(['subject_id', 'remarks']);
});
```

---

#### `school_settings`

```php
Schema::create('school_settings', function (Blueprint $table) {
    $table->id();
    $table->string('key')->unique();
    $table->text('value')->nullable();
    $table->timestamps();
});
```

---

#### `audit_logs`

```php
Schema::create('audit_logs', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
    $table->string('action', 20);              // created, updated, deleted
    $table->string('model');                   // "App\Models\Student"
    $table->unsignedBigInteger('model_id');
    $table->json('old_values')->nullable();
    $table->json('new_values')->nullable();
    $table->string('ip_address', 45)->nullable();
    $table->timestamp('created_at');

    $table->index(['model', 'model_id']);
    $table->index('user_id');
    $table->index('created_at');
});
```

---

#### `teacher_profiles` (SF7 — Teacher Profiling) — *Planned / Not Yet Implemented*

> DepEd School Form 7 (SF7) requires detailed teacher profiles. This table extends the `users` table for teachers.

```php
Schema::create('teacher_profiles', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->string('employee_id', 20)->nullable();        // DepEd Employee ID
    $table->string('position_title')->nullable();          // "Teacher I", "Teacher III", "Master Teacher I"
    $table->string('appointment_status')->nullable();      // permanent, contractual, part-time, job_order
    $table->string('sex', 10)->nullable();                 // male, female
    $table->date('birthdate')->nullable();
    $table->string('contact_number', 20)->nullable();
    $table->string('address')->nullable();

    // Educational Background
    $table->string('highest_degree')->nullable();          // "Bachelor's", "Master's", "Doctorate"
    $table->string('degree_course')->nullable();           // "BSEd Mathematics", "BS Chemistry"
    $table->string('degree_major')->nullable();            // "Mathematics", "Chemistry", "English"
    $table->string('school_graduated')->nullable();        // "Eastern Visayas State University"
    $table->year('year_graduated')->nullable();

    // Professional License
    $table->string('prc_license_number', 20)->nullable();  // PRC License No.
    $table->date('prc_validity')->nullable();              // License expiry date
    $table->string('eligibility')->nullable();             // "LET Passer", "CSC Professional"

    // Teaching Info
    $table->string('specialization')->nullable();          // "Science", "Mathematics", "English"
    $table->date('date_hired')->nullable();                // Date first hired
    $table->integer('teaching_hours_per_week')->nullable();

    $table->timestamps();

    $table->unique('user_id');
    $table->index('employee_id');
});
```

---

#### `teacher_trainings` (SF7 supplementary — trainings/seminars attended) — *Planned / Not Yet Implemented*

```php
Schema::create('teacher_trainings', function (Blueprint $table) {
    $table->id();
    $table->foreignId('teacher_profile_id')->constrained()->cascadeOnDelete();
    $table->string('title');                               // "Senior High School Training"
    $table->string('type')->nullable();                    // seminar, workshop, training, conference
    $table->string('sponsor')->nullable();                 // "DepEd Region XII", "CHED"
    $table->date('date_from')->nullable();
    $table->date('date_to')->nullable();
    $table->decimal('hours', 5, 1)->nullable();            // Total training hours
    $table->timestamps();

    $table->index('teacher_profile_id');
});
```

---

## 2. Entity Relationship Diagram

```
┌──────────────┐       ┌──────────────┐       ┌──────────────┐
│ school_years │       │  semesters   │       │   tracks     │
├──────────────┤       ├──────────────┤       ├──────────────┤
│ id           │──┐    │ id           │       │ id           │
│ name         │  │    │ school_year_id│◄──┘   │ name         │
│ is_active    │  └───►│ number       │       │ code         │
└──────────────┘       │ is_active    │       │ is_active    │
                       │ enrollment_  │       └──────┬───────┘
                       │   open       │              │
                       └──────┬───────┘              │ 1:N
                              │                      │
                              │               ┌──────▼───────┐
                              │               │   strands    │
                              │               ├──────────────┤
                              │               │ id           │
                              │               │ track_id  FK │
                              │               │ name         │
                              │               │ code         │
                              │               └──────┬───────┘
                              │                      │
                              │          ┌───────────┼───────────┐
                              │          │ 1:N       │ 1:N       │
                              │          │           │           │
                              │   ┌──────▼──────┐    │    ┌──────▼──────────┐
                              │   │  sections   │    │    │ subject_strand  │
                              │   ├─────────────┤    │    │    (pivot)      │
                              │   │ id          │    │    ├─────────────────┤
                              │   │ name        │    │    │ subject_id  FK  │
                              │   │ strand_id FK│    │    │ strand_id   FK  │
                              │   │ semester_id │◄───┤    │ grade_level     │
                              │   │ grade_level │    │    │ semester        │
                              │   │ max_capacity│    │    └────────┬────────┘
                              │   │ adviser_id  │──► users        │
                              │   └──────┬──────┘                 │
                              │          │ 1:N                    │
                              │          │                        │
                              │   ┌──────▼──────┐         ┌──────▼───────┐
                              │   │ enrollments │         │  subjects    │
                              │   ├─────────────┤         ├──────────────┤
                              │   │ id          │         │ id           │
┌──────────────┐              │   │ student_id  │◄──┐     │ code         │
│  students    │              │   │ section_id  │   │     │ name         │
├──────────────┤              │   │ semester_id │◄──┘     │ type         │
│ id           │              │   │ status      │   │     │ hours        │
│ lrn (unique) │──────────────┼──►│ enrolled_at │   │     │ prerequisite │──► self
│ first_name   │              │   │ remarks     │   │     │   _id  FK    │
│ last_name    │              │   └──────┬──────┘   │     └──────┬───────┘
│ birthdate    │              │          │          │            │
│ gender       │              │          │ 1:N      │            │
│ address...   │              │          │          │            │
│ guardian...  │              │   ┌──────▼──────┐   │     ┌──────▼───────────┐
│ status       │              │   │   grades    │   │     │enrollment_subject│
└──────────────┘              │   ├─────────────┤   │     │    (pivot)       │
                              │   │ enrollment_ │   │     ├──────────────────┤
                              │   │   id  FK    │   │     │ enrollment_id FK │
┌──────────────┐              │   │ subject_id  │───┘     │ subject_id   FK  │
│    users     │              │   │ midterm     │         └──────────────────┘
├──────────────┤              │   │ finals      │
│ id           │              │   │ final_grade │
│ name         │              │   │ remarks     │
│ email        │              │   │ is_locked   │
│ password     │              │   │ encoded_by  │──► users
│ is_active    │              │   └─────────────┘
└──────────────┘              │
      │                       │
      │  Spatie Permission    │
      │  (roles table)        │
      ▼                       │
┌──────────────┐              │
│ model_has_   │              │
│   roles      │              │
├──────────────┤              │
│ role_id      │              │
│ model_id     │──► users     │
└──────────────┘              │
                              │
┌──────────────┐              │
│ school_      │              │
│   settings   │              │
├──────────────┤              │
│ key (unique) │              │
│ value        │              │
└──────────────┘              │
                              │
┌──────────────┐              │
│ audit_logs   │              │
├──────────────┤              │
│ user_id  FK  │──► users     │
│ action       │              │
│ model        │              │
│ model_id     │              │
│ old_values   │              │
│ new_values   │              │
└──────────────┘
```

### Relationship Summary

| Relationship | Type | Foreign Key |
|---|---|---|
| SchoolYear → Semesters | 1:N | `semesters.school_year_id` |
| Track → Strands | 1:N | `strands.track_id` |
| Strand → Sections | 1:N | `sections.strand_id` |
| Strand → Subjects | N:N | via `subject_strand` pivot |
| Semester → Sections | 1:N | `sections.semester_id` |
| Semester → Enrollments | 1:N | `enrollments.semester_id` |
| Student → Enrollments | 1:N | `enrollments.student_id` |
| Section → Enrollments | 1:N | `enrollments.section_id` |
| Enrollment → Subjects | N:N | via `enrollment_subject` pivot |
| Enrollment → Grades | 1:N | `grades.enrollment_id` |
| Subject → Subject (prereq) | self 1:1 | `subjects.prerequisite_id` |
| User → Sections (adviser) | 1:N | `sections.adviser_id` |
| User → Grades (encoded) | 1:N | `grades.encoded_by` |
| User → AuditLogs | 1:N | `audit_logs.user_id` |

---

## 3. Seeder Data

### 3.1 Roles (RoleSeeder)

```php
$roles = ['admin', 'registrar', 'teacher', 'student'];
```

### 3.2 Admin Account (AdminUserSeeder)

```php
User::create([
    'name' => 'System Administrator',
    'email' => 'admin@lsnhs.edu.ph',
    'password' => Hash::make('password'),
])->assignRole('admin');
```

### 3.3 Tracks & Strands (TrackStrandSeeder)

```php
$tracks = [
    'Academic' => ['STEM', 'ABM', 'HUMSS', 'GAS'],
    'TVL'      => ['Home Economics', 'ICT', 'Agri-Fishery Arts'],
    'Sports'   => ['Sports'],
    'Arts & Design' => ['Arts & Design'],
];
// Sports and Arts & Design created as is_active = false (not offered)
```

### 3.4 Subjects (SubjectSeeder) — Based on Actual DepEd SHS Curriculum

**Core Subjects (all strands take these):**

```
Grade 11, 1st Semester:
  OC-111    Oral Communication
  CM-111    Komunikasyon at Pananaliksik sa Wika at Kulturang Pilipino
  PE1-111   Physical Education and Health 1
  GM-111    General Mathematics
  ELS-111   Earth and Life Science
  PPGS-111  Personal Development / Pansariling Kaunlaran
  UC-111    Understanding Culture, Society, and Politics

Grade 11, 2nd Semester:
  RW-112    Reading and Writing Skills
  PS-112    Pagbasa at Pagsusuri ng Iba't Ibang Teksto
  PE2-112   Physical Education and Health 2
  SP-112    Statistics and Probability
  PP-112    Physical Science
  ICF-112   Introduction to the Philosophy of the Human Person
  CC-112    Contemporary Philippine Arts from the Regions

Grade 12, 1st Semester:
  CL-121    21st Century Literature from the Philippines and the World
  PE3-121   Physical Education and Health 3
  MIL-121   Media and Information Literacy
  ET-121    Empowerment Technologies
  IRP-121   Inquiries, Investigations, and Immersion

Grade 12, 2nd Semester:
  EP-122    English for Academic and Professional Purposes
  FIL-122   Filipino sa Piling Larangan
  PE4-122   Physical Education and Health 4
  PR1-122   Practical Research 1
  PR2-122   Practical Research 2 (prerequisite: PR1)
```

**STEM Specialized Subjects:**

```
Grade 11, 1st Semester:
  PC-111    Pre-Calculus
  GB1-111   General Biology 1

Grade 11, 2nd Semester:
  BC-112    Basic Calculus (prerequisite: Pre-Calculus)
  GB2-112   General Biology 2 (prerequisite: General Biology 1)

Grade 12, 1st Semester:
  GC1-121   General Chemistry 1
  GP1-121   General Physics 1

Grade 12, 2nd Semester:
  GC2-122   General Chemistry 2 (prerequisite: General Chemistry 1)
  GP2-122   General Physics 2 (prerequisite: General Physics 1)
  RP-122    Research/Capstone Project (prerequisite: PR2)
```

**ABM Specialized Subjects:**

```
Grade 11, 1st Semester:
  ABM1-111  Applied Economics
  ABM2-111  Business Ethics and Social Responsibility

Grade 11, 2nd Semester:
  ABM3-112  Fundamentals of Accountancy, Business, and Management 1
  ABM4-112  Business Math

Grade 12, 1st Semester:
  ABM5-121  Fundamentals of ABM 2 (prerequisite: ABM 1)
  ABM6-121  Business Finance

Grade 12, 2nd Semester:
  ABM7-122  Principles of Marketing
  ABM8-122  Culminating Activity (prerequisite: PR2)
```

**HUMSS Specialized Subjects:**

```
Grade 11, 1st Semester:
  HM1-111   Introduction to World Religions and Belief Systems
  HM2-111   Creative Writing / Malikhaing Pagsulat

Grade 11, 2nd Semester:
  HM3-112   Creative Nonfiction
  HM4-112   Philippine Politics and Governance

Grade 12, 1st Semester:
  HM5-121   Disciplines and Ideas in the Social Sciences
  HM6-121   Community Engagement, Solidarity, and Citizenship

Grade 12, 2nd Semester:
  HM7-122   Trends, Networks, and Critical Thinking in the 21st Century
  HM8-122   Culminating Activity (prerequisite: PR2)
```

**GAS Specialized Subjects:**

```
Grade 11, 1st Semester:
  GA1-111   Humanities 1
  GA2-111   Social Science 1

Grade 11, 2nd Semester:
  GA3-112   Humanities 2
  GA4-112   Applied Economics

Grade 12, 1st Semester:
  GA5-121   Organization and Management
  GA6-121   Disaster Readiness and Risk Reduction

Grade 12, 2nd Semester:
  GA7-122   Elective 1
  GA8-122   Elective 2 / Culminating Activity
```

### 3.5 School Settings (SchoolSettingSeeder)

```php
$settings = [
    'school_name'        => 'Lake Sebu National High School',
    'school_id'          => '305678',
    'division'           => 'South Cotabato',
    'region'             => 'Region XII - SOCCSKSARGEN',
    'address'            => 'Poblacion, Lake Sebu, South Cotabato',
    'passing_grade'      => '75',
    'midterm_weight'     => '50',
    'finals_weight'      => '50',
    'default_capacity'   => '50',
    'allow_prereq_override' => 'true',
];
```

### 3.6 Demo Data (DemoDataSeeder — dev only)

```php
// Teachers
$teachers = [
    'Maria Cruz', 'Pedro Reyes', 'Ana Santos', 'Ricardo Luna',
    'Elena Diaz', 'Jose Ramos', 'Carmen Tan', 'Roberto Villanueva',
];

// Students — 200 realistic Filipino names
$firstNames = [
    'Juan', 'Maria', 'Jose', 'Ana', 'Pedro', 'Rosa', 'Carlos', 'Elena',
    'Miguel', 'Carmen', 'Antonio', 'Luz', 'Fernando', 'Patricia', 'Rafael',
    'Josephine', 'Manuel', 'Grace', 'Roberto', 'Cristina', 'Andres',
    'Michelle', 'Eduardo', 'Jennifer', 'Francisco', 'Mary Jane', 'Ricardo',
    'Angela', 'Marco', 'Diana', 'Paolo', 'Katrina', 'Christian', 'Nicole',
    'Mark', 'Jasmine', 'John Paul', 'Princess', 'Rodel', 'Maribel',
];

$lastNames = [
    'Dela Cruz', 'Santos', 'Reyes', 'Garcia', 'Ramos', 'Cruz', 'Bautista',
    'Aquino', 'Villanueva', 'Fernandez', 'Lopez', 'Gonzales', 'Torres',
    'Mendoza', 'Rivera', 'Castillo', 'Flores', 'Navarro', 'Domingo',
    'Salvador', 'Magallanes', 'Estacio', 'Hernandez', 'Pascual',
    'Mercado', 'Tan', 'Lim', 'Chua', 'Sy', 'Ong',
];

$barangays = [
    'Poblacion', 'Lamdalag', 'Bacdulong', 'Halilan', 'Klubi',
    'Lake Lahit', 'Lamcade', 'Lamba', 'Lamfugon', 'Lamlahak',
    'Lower Maculan', 'Ned', 'Siluton', 'Takunel', 'Upper Maculan',
];

// Generates:
// - 8 teacher accounts
// - 200 student records with realistic data
// - School Year 2025-2026 (completed, with grades)
// - School Year 2026-2027 (active, enrollment open)
// - 8 sections (2 per strand: STEM, ABM, HUMSS, GAS)
// - 150 enrollments distributed across sections
// - Sample grades for SY 2025-2026
```

---

## 4. API Design — Controller Return Data

> Every controller follows the same pattern. Here's exactly what each one returns.

### 4.1 DashboardController

```php
// GET /
public function index()
{
    return Inertia::render('Dashboard', [
        'stats' => Inertia::defer(fn () => [
            'total_enrolled' => Enrollment::currentSemester()->enrolled()->count(),
            'total_sections' => Section::currentSemester()->count(),
            'total_students' => Student::active()->count(),
            'pending'        => Enrollment::currentSemester()->pending()->count(),
        ]),
        'enrollment_by_track' => Inertia::defer(fn () =>
            Enrollment::currentSemester()->enrolled()
                ->selectRaw('tracks.name as track, COUNT(*) as total')
                ->join('sections', 'enrollments.section_id', '=', 'sections.id')
                ->join('strands', 'sections.strand_id', '=', 'strands.id')
                ->join('tracks', 'strands.track_id', '=', 'tracks.id')
                ->groupBy('tracks.name')
                ->get(),
            'charts'
        ),
        'section_capacity' => Inertia::defer(fn () =>
            Section::currentSemester()
                ->withCount(['enrollments as enrolled_count' => fn ($q) => $q->where('status', 'enrolled')])
                ->with('strand')
                ->get(),
            'charts'
        ),
        'recent_enrollments' => Inertia::defer(fn () =>
            Enrollment::currentSemester()
                ->with('student:id,first_name,last_name,lrn', 'section:id,name')
                ->latest('enrolled_at')
                ->take(10)
                ->get()
        ),
    ]);
}
```

### 4.2 StudentController

```php
// GET /students
public function index(Request $request)
{
    return Inertia::render('Students/Index', [
        'students' => fn () => Student::query()
            ->search($request->search)
            ->byStrand($request->strand_id)
            ->byGradeLevel($request->grade_level)
            ->byStatus($request->status)
            ->withCurrentSection()
            ->orderBy('last_name')
            ->paginate(25)
            ->withQueryString(),
        'strands' => fn () => Strand::active()->with('track')->get(),
        'filters' => $request->only(['search', 'strand_id', 'grade_level', 'status']),
    ]);
}

// GET /students/create
public function create()
{
    return Inertia::render('Students/Create');
}

// POST /students
public function store(StoreStudentRequest $request)
{
    $student = Student::create($request->validated());
    return redirect()->route('students.show', $student)->with('success', 'Student added.');
}

// GET /students/{student}
public function show(Student $student)
{
    return Inertia::render('Students/Show', [
        'student' => $student,
        'enrollments' => Inertia::optional(fn () =>
            $student->enrollments()
                ->with('section.strand.track', 'semester.schoolYear', 'subjects')
                ->latest()
                ->get()
        ),
        'grades' => Inertia::optional(fn () =>
            $student->grades()
                ->with('subject', 'enrollment.semester.schoolYear')
                ->get()
                ->groupBy('enrollment.semester.schoolYear.name')
        ),
    ]);
}

// GET /students/{student}/edit
public function edit(Student $student)
{
    return Inertia::render('Students/Edit', [
        'student' => $student,
    ]);
}

// PUT /students/{student}
public function update(UpdateStudentRequest $request, Student $student)
{
    $student->update($request->validated());
    return redirect()->back()->with('success', 'Student updated.');
}
```

### 4.3 EnrollmentController

```php
// GET /enrollment
public function index(Request $request)
{
    return Inertia::render('Enrollment/Index', [
        'enrollments' => fn () => Enrollment::query()
            ->currentSemester()
            ->search($request->search)
            ->byStrand($request->strand_id)
            ->bySectionId($request->section_id)
            ->byStatus($request->status)
            ->with('student:id,lrn,first_name,last_name', 'section:id,name', 'section.strand:id,name')
            ->latest('enrolled_at')
            ->paginate(25)
            ->withQueryString(),
        'sections' => fn () => Section::currentSemester()->with('strand')->get(),
        'filters' => $request->only(['search', 'strand_id', 'section_id', 'status']),
    ]);
}

// GET /enrollment/create
public function create(Request $request)
{
    return Inertia::render('Enrollment/Create', [
        'tracks' => fn () => Track::active()->with('strands.subjects')->get(),
        'activeSemester' => Semester::active()->with('schoolYear')->first(),
        // If student_id passed (from search), preload student
        'preselected_student' => $request->student_id
            ? Student::with('latestEnrollment.section.strand')->find($request->student_id)
            : null,
    ]);
}

// POST /enrollment (called from wizard Step 5: Confirm)
public function store(StoreEnrollmentRequest $request, EnrollStudent $action)
{
    $enrollment = $action->execute($request->validated());

    return redirect()->route('enrollment.show', $enrollment)
        ->with('success', 'Student enrolled successfully.');
}

// GET /enrollment/{enrollment}
public function show(Enrollment $enrollment)
{
    return Inertia::render('Enrollment/Show', [
        'enrollment' => $enrollment->load(
            'student', 'section.strand.track', 'semester.schoolYear', 'subjects'
        ),
    ]);
}

// PATCH /enrollment/{enrollment}/status
public function updateStatus(Request $request, Enrollment $enrollment)
{
    $request->validate([
        'status' => 'required|in:enrolled,dropped,transferred',
        'remarks' => 'required_if:status,dropped,transferred|string|max:500',
    ]);

    $enrollment->update($request->only('status', 'remarks'));

    return redirect()->back()->with('success', 'Enrollment status updated.');
}

// GET /enrollment/{enrollment}/slip
public function printSlip(Enrollment $enrollment)
{
    $enrollment->load('student', 'section.strand.track', 'semester.schoolYear', 'subjects');
    $pdf = Pdf::loadView('pdf.enrollment-slip', compact('enrollment'));
    return $pdf->download("enrollment-slip-{$enrollment->student->lrn}.pdf");
}
```

### 4.4 Enrollment Wizard — AJAX Endpoints

```php
// These are called by the Vue wizard steps via Inertia partial reloads or axios

// GET /api/students/search?q={query}
// Returns: student matches for Step 1
public function searchStudents(Request $request)
{
    return Student::search($request->q)->take(10)->get(['id', 'lrn', 'first_name', 'last_name', 'birthdate']);
}

// GET /api/enrollment/subjects?strand_id=1&grade_level=12&semester=1
// Returns: subject load for Step 3
public function getSubjectLoad(Request $request)
{
    return Subject::whereHas('strands', fn ($q) =>
        $q->where('strand_id', $request->strand_id)
          ->where('grade_level', $request->grade_level)
          ->where('semester', $request->semester)
    )->with('prerequisite')->get();
}

// GET /api/enrollment/prerequisite-check?student_id=1&subject_ids[]=5&subject_ids[]=6
// Returns: prerequisite status for each subject
public function checkPrerequisites(Request $request)
{
    // For each subject that has a prerequisite, check if student has passing grade
    $results = [];
    foreach ($request->subject_ids as $subjectId) {
        $subject = Subject::with('prerequisite')->find($subjectId);
        if (!$subject->prerequisite_id) {
            $results[$subjectId] = ['status' => 'ok'];
            continue;
        }
        $grade = Grade::whereHas('enrollment', fn ($q) => $q->where('student_id', $request->student_id))
            ->where('subject_id', $subject->prerequisite_id)
            ->first();

        $results[$subjectId] = [
            'status' => $grade && $grade->final_grade >= 75 ? 'passed' : 'not_passed',
            'prerequisite' => $subject->prerequisite->name,
            'grade' => $grade?->final_grade,
        ];
    }
    return $results;
}

// GET /api/sections/available?strand_id=1&grade_level=12
// Returns: sections with capacity for Step 4
public function getAvailableSections(Request $request)
{
    return Section::currentSemester()
        ->where('strand_id', $request->strand_id)
        ->where('grade_level', $request->grade_level)
        ->withCount(['enrollments as enrolled_count' => fn ($q) => $q->where('status', 'enrolled')])
        ->get();
}
```

### 4.5 SectionController

```php
// GET /sections
public function index()
{
    return Inertia::render('Sections/Index', [
        'sections' => fn () => Section::currentSemester()
            ->with('strand.track', 'adviser:id,name')
            ->withCount(['enrollments as enrolled_count' => fn ($q) => $q->where('status', 'enrolled')])
            ->orderBy('grade_level')
            ->orderBy('name')
            ->get(),
    ]);
}

// GET /sections/{section}
public function show(Section $section)
{
    return Inertia::render('Sections/Show', [
        'section' => $section->load('strand.track', 'adviser:id,name'),
        'students' => fn () => $section->enrollments()
            ->where('status', 'enrolled')
            ->with('student')
            ->get()
            ->pluck('student')
            ->sortBy('last_name'),
    ]);
}
```

### 4.6 GradeController

```php
// GET /grades
public function index()
{
    $user = auth()->user();

    return Inertia::render('Grades/Index', [
        'sections' => fn () => $user->hasRole('teacher')
            ? $user->advisedSections()->currentSemester()->with('strand')->get()
            : Section::currentSemester()->with('strand')->get(),
    ]);
}

// GET /grades/{section}/{subject}
public function entry(Section $section, Subject $subject)
{
    return Inertia::render('Grades/Entry', [
        'section' => $section->load('strand'),
        'subject' => $subject,
        'students' => fn () => $section->enrollments()
            ->where('status', 'enrolled')
            ->with([
                'student:id,first_name,last_name,lrn',
                'grades' => fn ($q) => $q->where('subject_id', $subject->id),
            ])
            ->get()
            ->sortBy('student.last_name'),
    ]);
}

// POST /grades/{section}/{subject}
public function store(StoreBatchGradeRequest $request, Section $section, Subject $subject, SaveBatchGrades $action)
{
    $action->execute($section, $subject, $request->validated()['grades']);

    return redirect()->back()->with('success', 'Grades saved.');
}
```

### 4.7 ReportController

```php
// GET /reports
public function index()
{
    return Inertia::render('Reports/Index');
}

// GET /reports/enrollment-summary
public function enrollmentSummary(Request $request)
{
    return Inertia::render('Reports/EnrollmentSummary', [
        'summary' => Inertia::defer(fn () => [
            'by_track' => Enrollment::summaryByTrack($request->semester_id),
            'by_strand' => Enrollment::summaryByStrand($request->semester_id),
            'by_section' => Enrollment::summaryBySection($request->semester_id),
            'by_gender' => Enrollment::summaryByGender($request->semester_id),
            'total' => Enrollment::currentSemester()->enrolled()->count(),
        ]),
        'semesters' => fn () => Semester::with('schoolYear')->latest()->get(),
        'filters' => $request->only(['semester_id']),
    ]);
}

// GET /reports/school-forms/{type}/generate
public function generate(string $type, Request $request)
{
    return match($type) {
        'sf1'  => (new GenerateSF1)->download($request->semester_id),
        'sf5'  => (new GenerateSF5)->download($request->section_id),
        'sf9'  => (new GenerateSF9)->download($request->student_id),
        'sf10' => (new GenerateSF10)->download($request->student_id),
    };
}
```

---

## 5. Sidebar Navigation — Per Role

### 5.1 Navigation Structure

```php
// HandleInertiaRequests.php — shared data
'navigation' => fn () => $this->getNavigation(auth()->user()),
'activeSemester' => fn () => Semester::active()->with('schoolYear')->first(),
```

### 5.2 Admin / Registrar Menu

```
┌─────────────────────────┐
│  LSNHS                  │
│  SY 2026-2027 | 1st Sem │
├─────────────────────────┤
│                         │
│  📊 Dashboard           │
│                         │
│  ENROLLMENT             │
│  📝 Enroll Student      │
│  📋 Enrollment List     │
│                         │
│  RECORDS                │
│  👤 Students            │
│  🏫 Sections            │
│                         │
│  ACADEMICS              │
│  📝 Grades              │
│  📚 Curriculum          │
│                         │
│  REPORTS                │
│  📊 Reports             │
│  📄 School Forms        │
│                         │
│  ADMIN  (admin only)    │
│  👥 Users               │
│  📥 Import Data         │
│  ⚙️ Settings            │
│                         │
├─────────────────────────┤
│  👤 Ma'am Lorna         │
│  Registrar              │
│  [Profile] [Logout]     │
└─────────────────────────┘
```

### 5.3 Teacher Menu

```
┌─────────────────────────┐
│  LSNHS                  │
│  SY 2026-2027 | 1st Sem │
├─────────────────────────┤
│                         │
│  📊 Dashboard           │
│                         │
│  MY SECTIONS            │
│  🏫 STEM-A (Grade 11)   │
│                         │
│  ACADEMICS              │
│  📝 Grade Entry         │
│                         │
│  RECORDS                │
│  👤 Students (view only)│
│                         │
├─────────────────────────┤
│  👤 Ms. Cruz            │
│  Teacher                │
│  [Profile] [Logout]     │
└─────────────────────────┘
```

### 5.4 Student Menu

```
┌─────────────────────────┐
│  LSNHS                  │
│  SY 2026-2027 | 1st Sem │
├─────────────────────────┤
│                         │
│  📄 My Profile          │
│  📚 My Subjects         │
│  📊 My Grades           │
│                         │
├─────────────────────────┤
│  👤 Juan Dela Cruz      │
│  Grade 12 - STEM        │
│  [Profile] [Logout]     │
└─────────────────────────┘
```

### 5.5 Navigation Config (Code)

```php
// app/Helpers/NavigationHelper.php

public static function getNavigation(User $user): array
{
    $nav = [];

    // Everyone gets dashboard
    $nav[] = ['label' => 'Dashboard', 'route' => 'dashboard', 'icon' => 'LayoutDashboard'];

    if ($user->hasAnyRole(['admin', 'registrar'])) {
        $nav[] = ['type' => 'divider', 'label' => 'Enrollment'];
        $nav[] = ['label' => 'Enroll Student', 'route' => 'enrollment.create', 'icon' => 'UserPlus'];
        $nav[] = ['label' => 'Enrollment List', 'route' => 'enrollment.index', 'icon' => 'ClipboardList'];

        $nav[] = ['type' => 'divider', 'label' => 'Records'];
        $nav[] = ['label' => 'Students', 'route' => 'students.index', 'icon' => 'Users'];
        $nav[] = ['label' => 'Sections', 'route' => 'sections.index', 'icon' => 'School'];

        $nav[] = ['type' => 'divider', 'label' => 'Academics'];
        $nav[] = ['label' => 'Grades', 'route' => 'grades.index', 'icon' => 'GraduationCap'];
        $nav[] = ['label' => 'Curriculum', 'route' => 'curriculum.tracks.index', 'icon' => 'BookOpen'];

        $nav[] = ['type' => 'divider', 'label' => 'Reports'];
        $nav[] = ['label' => 'Reports', 'route' => 'reports.index', 'icon' => 'BarChart3'];
        $nav[] = ['label' => 'School Forms', 'route' => 'reports.school-forms', 'icon' => 'FileText'];
    }

    if ($user->hasRole('admin')) {
        $nav[] = ['type' => 'divider', 'label' => 'Admin'];
        $nav[] = ['label' => 'Users', 'route' => 'users.index', 'icon' => 'Shield'];
        $nav[] = ['label' => 'Import Data', 'route' => 'import.index', 'icon' => 'Upload'];
        $nav[] = ['label' => 'Settings', 'route' => 'settings.index', 'icon' => 'Settings'];
    }

    if ($user->hasRole('teacher')) {
        $nav[] = ['type' => 'divider', 'label' => 'My Sections'];
        foreach ($user->advisedSections()->currentSemester()->with('strand')->get() as $section) {
            $nav[] = [
                'label' => "{$section->name} (Grade {$section->grade_level})",
                'route' => 'sections.show',
                'params' => ['section' => $section->id],
                'icon' => 'School',
            ];
        }
        $nav[] = ['type' => 'divider', 'label' => 'Academics'];
        $nav[] = ['label' => 'Grade Entry', 'route' => 'grades.index', 'icon' => 'GraduationCap'];
        $nav[] = ['type' => 'divider', 'label' => 'Records'];
        $nav[] = ['label' => 'Students', 'route' => 'students.index', 'icon' => 'Users'];
    }

    if ($user->hasRole('student')) {
        $nav[] = ['label' => 'My Profile', 'route' => 'profile.edit', 'icon' => 'User'];
        $nav[] = ['label' => 'My Subjects', 'route' => 'student.subjects', 'icon' => 'BookOpen'];
        $nav[] = ['label' => 'My Grades', 'route' => 'student.grades', 'icon' => 'BarChart3'];
    }

    return $nav;
}
```

### 5.6 Sidebar Vue Component — Prefetched Links

```vue
<!-- Layouts/Partials/Sidebar.vue -->
<script setup>
import { usePage, Link } from '@inertiajs/vue3'
import { computed } from 'vue'
import * as icons from 'lucide-vue-next'

const navigation = computed(() => usePage().props.navigation)
const activeSemester = computed(() => usePage().props.activeSemester)

const isActive = (routeName) => route().current(routeName)
</script>

<template>
  <aside class="w-64 bg-white border-r min-h-screen flex flex-col">
    <!-- School + Semester Header -->
    <div class="p-4 border-b">
      <h1 class="font-bold text-sm">LSNHS</h1>
      <p class="text-xs text-muted-foreground">
        SY {{ activeSemester?.school_year?.name }} |
        {{ activeSemester?.number === 1 ? '1st' : '2nd' }} Semester
      </p>
    </div>

    <!-- Nav Links -->
    <nav class="flex-1 p-3 space-y-1">
      <template v-for="item in navigation" :key="item.label">
        <!-- Divider -->
        <div v-if="item.type === 'divider'" class="pt-4 pb-1">
          <span class="text-xs font-medium text-muted-foreground uppercase tracking-wider">
            {{ item.label }}
          </span>
        </div>

        <!-- Link with prefetch -->
        <Link
          v-else
          :href="route(item.route, item.params)"
          prefetch
          :cache-tags="[item.route]"
          class="flex items-center gap-3 px-3 py-2 rounded-md text-sm transition-colors"
          :class="isActive(item.route)
            ? 'bg-primary/10 text-primary font-medium'
            : 'text-muted-foreground hover:bg-muted'"
        >
          <component :is="icons[item.icon]" class="h-4 w-4" />
          {{ item.label }}
        </Link>
      </template>
    </nav>

    <!-- User Footer -->
    <div class="p-4 border-t">
      <div class="text-sm font-medium">{{ $page.props.auth.user.name }}</div>
      <div class="text-xs text-muted-foreground capitalize">
        {{ $page.props.auth.user.roles[0]?.name }}
      </div>
    </div>
  </aside>
</template>
```

---

## 7. Model Traits — Complete Code

> Every model uses `use XRelations` and `use XScopes` traits. Models stay under 50 lines.

### 7.1 User

```php
// app/Models/User.php
class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;
    use UserRelations, UserScopes;

    protected $fillable = ['name', 'email', 'password', 'is_active'];
    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }
}
```

```php
// app/Traits/Model/UserRelations.php
namespace App\Traits\Model;

use App\Models\Section;
use App\Models\AuditLog;
use App\Models\Grade;

trait UserRelations
{
    public function advisedSections()
    {
        return $this->hasMany(Section::class, 'adviser_id');
    }

    public function encodedGrades()
    {
        return $this->hasMany(Grade::class, 'encoded_by');
    }

    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class);
    }
}
```

```php
// app/Traits/Model/UserScopes.php
namespace App\Traits\Model;

trait UserScopes
{
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeTeachers($query)
    {
        return $query->role('teacher');
    }

    public function scopeSearch($query, ?string $search)
    {
        return $query->when($search, fn ($q) =>
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%")
        );
    }
}
```

---

### 7.2 Student

```php
// app/Models/Student.php
class Student extends Model
{
    use HasFactory;
    use StudentRelations, StudentScopes;

    protected $fillable = [
        'lrn', 'last_name', 'first_name', 'middle_name', 'suffix',
        'birthdate', 'gender', 'address', 'contact_number',
        'guardian_name', 'guardian_contact', 'guardian_relationship',
        'previous_school', 'status',
    ];

    protected function casts(): array
    {
        return [
            'birthdate' => 'date',
            'status' => \App\Enums\StudentStatus::class,
        ];
    }

    public function getFullNameAttribute(): string
    {
        return trim("{$this->last_name}, {$this->first_name} {$this->middle_name}");
    }
}
```

```php
// app/Traits/Model/StudentRelations.php
namespace App\Traits\Model;

use App\Models\Enrollment;
use App\Models\Grade;

trait StudentRelations
{
    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    public function currentEnrollment()
    {
        return $this->hasOne(Enrollment::class)->latestOfMany();
    }

    public function latestEnrollment()
    {
        return $this->hasOne(Enrollment::class)->latestOfMany('enrolled_at');
    }

    public function grades()
    {
        return $this->hasManyThrough(Grade::class, Enrollment::class);
    }
}
```

```php
// app/Traits/Model/StudentScopes.php
namespace App\Traits\Model;

use App\Enums\StudentStatus;

trait StudentScopes
{
    public function scopeSearch($query, ?string $search)
    {
        return $query->when($search, fn ($q) =>
            $q->where('lrn', 'like', "%{$search}%")
              ->orWhere('last_name', 'like', "%{$search}%")
              ->orWhere('first_name', 'like', "%{$search}%")
        );
    }

    public function scopeActive($query)
    {
        return $query->where('status', StudentStatus::Active);
    }

    public function scopeByStatus($query, ?string $status)
    {
        return $query->when($status, fn ($q) => $q->where('status', $status));
    }

    public function scopeByStrand($query, ?int $strandId)
    {
        return $query->when($strandId, fn ($q) =>
            $q->whereHas('currentEnrollment.section', fn ($s) =>
                $s->where('strand_id', $strandId)
            )
        );
    }

    public function scopeByGradeLevel($query, ?int $gradeLevel)
    {
        return $query->when($gradeLevel, fn ($q) =>
            $q->whereHas('currentEnrollment.section', fn ($s) =>
                $s->where('grade_level', $gradeLevel)
            )
        );
    }

    public function scopeWithCurrentSection($query)
    {
        return $query->with(['currentEnrollment.section.strand']);
    }
}
```

---

### 7.3 SchoolYear

```php
// app/Models/SchoolYear.php
class SchoolYear extends Model
{
    use HasFactory;
    use SchoolYearRelations, SchoolYearScopes;

    protected $fillable = ['name', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }
}
```

```php
// app/Traits/Model/SchoolYearRelations.php
namespace App\Traits\Model;

use App\Models\Semester;

trait SchoolYearRelations
{
    public function semesters()
    {
        return $this->hasMany(Semester::class)->orderBy('number');
    }

    public function firstSemester()
    {
        return $this->hasOne(Semester::class)->where('number', 1);
    }

    public function secondSemester()
    {
        return $this->hasOne(Semester::class)->where('number', 2);
    }
}
```

```php
// app/Traits/Model/SchoolYearScopes.php
namespace App\Traits\Model;

trait SchoolYearScopes
{
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
```

---

### 7.4 Semester

```php
// app/Models/Semester.php
class Semester extends Model
{
    use HasFactory;
    use SemesterRelations, SemesterScopes;

    protected $fillable = ['school_year_id', 'number', 'is_active', 'enrollment_open'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'enrollment_open' => 'boolean',
        ];
    }

    public function getLabelAttribute(): string
    {
        return ($this->number === 1 ? '1st' : '2nd') . ' Semester';
    }
}
```

```php
// app/Traits/Model/SemesterRelations.php
namespace App\Traits\Model;

use App\Models\SchoolYear;
use App\Models\Section;
use App\Models\Enrollment;

trait SemesterRelations
{
    public function schoolYear()
    {
        return $this->belongsTo(SchoolYear::class);
    }

    public function sections()
    {
        return $this->hasMany(Section::class);
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }
}
```

```php
// app/Traits/Model/SemesterScopes.php
namespace App\Traits\Model;

trait SemesterScopes
{
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public static function activeSemester()
    {
        return static::where('is_active', true)->with('schoolYear')->first();
    }
}
```

---

### 7.5 Track

```php
// app/Models/Track.php
class Track extends Model
{
    use HasFactory;
    use TrackRelations;

    protected $fillable = ['name', 'code', 'is_active', 'sort_order'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }
}
```

```php
// app/Traits/Model/TrackRelations.php
namespace App\Traits\Model;

use App\Models\Strand;

trait TrackRelations
{
    public function strands()
    {
        return $this->hasMany(Strand::class)->orderBy('sort_order');
    }

    public function activeStrands()
    {
        return $this->hasMany(Strand::class)->where('is_active', true)->orderBy('sort_order');
    }
}
```

---

### 7.6 Strand

```php
// app/Models/Strand.php
class Strand extends Model
{
    use HasFactory;
    use StrandRelations;

    protected $fillable = ['track_id', 'name', 'code', 'is_active', 'sort_order'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }
}
```

```php
// app/Traits/Model/StrandRelations.php
namespace App\Traits\Model;

use App\Models\Track;
use App\Models\Subject;
use App\Models\Section;

trait StrandRelations
{
    public function track()
    {
        return $this->belongsTo(Track::class);
    }

    public function subjects()
    {
        return $this->belongsToMany(Subject::class, 'subject_strand')
            ->withPivot('grade_level', 'semester', 'sort_order')
            ->orderByPivot('sort_order');
    }

    public function subjectsForGradeAndSemester(int $gradeLevel, int $semester)
    {
        return $this->subjects()
            ->wherePivot('grade_level', $gradeLevel)
            ->wherePivot('semester', $semester);
    }

    public function sections()
    {
        return $this->hasMany(Section::class);
    }
}
```

---

### 7.7 Subject

```php
// app/Models/Subject.php
class Subject extends Model
{
    use HasFactory;
    use SubjectRelations, SubjectScopes;

    protected $fillable = ['code', 'name', 'type', 'hours', 'prerequisite_id', 'is_active'];

    protected function casts(): array
    {
        return [
            'type' => \App\Enums\SubjectType::class,
            'is_active' => 'boolean',
            'hours' => 'decimal:1',
        ];
    }
}
```

```php
// app/Traits/Model/SubjectRelations.php
namespace App\Traits\Model;

use App\Models\Strand;
use App\Models\Grade;
use App\Models\Enrollment;

trait SubjectRelations
{
    public function prerequisite()
    {
        return $this->belongsTo(self::class, 'prerequisite_id');
    }

    public function dependents()
    {
        return $this->hasMany(self::class, 'prerequisite_id');
    }

    public function strands()
    {
        return $this->belongsToMany(Strand::class, 'subject_strand')
            ->withPivot('grade_level', 'semester', 'sort_order');
    }

    public function grades()
    {
        return $this->hasMany(Grade::class);
    }

    public function enrollments()
    {
        return $this->belongsToMany(Enrollment::class, 'enrollment_subject');
    }
}
```

```php
// app/Traits/Model/SubjectScopes.php
namespace App\Traits\Model;

trait SubjectScopes
{
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeSearch($query, ?string $search)
    {
        return $query->when($search, fn ($q) =>
            $q->where('code', 'like', "%{$search}%")
              ->orWhere('name', 'like', "%{$search}%")
        );
    }

    public function scopeByType($query, ?string $type)
    {
        return $query->when($type, fn ($q) => $q->where('type', $type));
    }

    public function scopeForStrand($query, int $strandId, int $gradeLevel, int $semester)
    {
        return $query->whereHas('strands', fn ($q) =>
            $q->where('strand_id', $strandId)
              ->where('subject_strand.grade_level', $gradeLevel)
              ->where('subject_strand.semester', $semester)
        );
    }
}
```

---

### 7.8 Section

```php
// app/Models/Section.php
class Section extends Model
{
    use HasFactory;
    use SectionRelations, SectionScopes;

    protected $fillable = ['name', 'strand_id', 'semester_id', 'grade_level', 'max_capacity', 'adviser_id'];
}
```

```php
// app/Traits/Model/SectionRelations.php
namespace App\Traits\Model;

use App\Models\Strand;
use App\Models\Semester;
use App\Models\User;
use App\Models\Enrollment;

trait SectionRelations
{
    public function strand()
    {
        return $this->belongsTo(Strand::class);
    }

    public function semester()
    {
        return $this->belongsTo(Semester::class);
    }

    public function adviser()
    {
        return $this->belongsTo(User::class, 'adviser_id');
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    public function activeEnrollments()
    {
        return $this->hasMany(Enrollment::class)->where('status', 'enrolled');
    }
}
```

```php
// app/Traits/Model/SectionScopes.php
namespace App\Traits\Model;

use App\Models\Semester;

trait SectionScopes
{
    public function scopeCurrentSemester($query)
    {
        $activeSemester = Semester::where('is_active', true)->first();
        return $query->when($activeSemester, fn ($q) =>
            $q->where('semester_id', $activeSemester->id)
        );
    }

    public function scopeByStrand($query, ?int $strandId)
    {
        return $query->when($strandId, fn ($q) => $q->where('strand_id', $strandId));
    }

    public function scopeByGradeLevel($query, ?int $gradeLevel)
    {
        return $query->when($gradeLevel, fn ($q) => $q->where('grade_level', $gradeLevel));
    }

    public function getEnrolledCountAttribute(): int
    {
        return $this->activeEnrollments()->count();
    }

    public function getIsFull(): bool
    {
        return $this->enrolled_count >= $this->max_capacity;
    }

    public function getCapacityPercentAttribute(): int
    {
        return $this->max_capacity > 0
            ? round(($this->enrolled_count / $this->max_capacity) * 100)
            : 0;
    }
}
```

---

### 7.9 Enrollment

```php
// app/Models/Enrollment.php
class Enrollment extends Model
{
    use HasFactory;
    use EnrollmentRelations, EnrollmentScopes;

    protected $fillable = ['student_id', 'section_id', 'semester_id', 'status', 'remarks', 'enrolled_at'];

    protected function casts(): array
    {
        return [
            'status' => \App\Enums\EnrollmentStatus::class,
            'enrolled_at' => 'datetime',
        ];
    }
}
```

```php
// app/Traits/Model/EnrollmentRelations.php
namespace App\Traits\Model;

use App\Models\Student;
use App\Models\Section;
use App\Models\Semester;
use App\Models\Subject;
use App\Models\Grade;

trait EnrollmentRelations
{
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    public function semester()
    {
        return $this->belongsTo(Semester::class);
    }

    public function subjects()
    {
        return $this->belongsToMany(Subject::class, 'enrollment_subject');
    }

    public function grades()
    {
        return $this->hasMany(Grade::class);
    }

    public function gradeFor(int $subjectId)
    {
        return $this->grades()->where('subject_id', $subjectId)->first();
    }
}
```

```php
// app/Traits/Model/EnrollmentScopes.php
namespace App\Traits\Model;

use App\Models\Semester;
use App\Enums\EnrollmentStatus;

trait EnrollmentScopes
{
    public function scopeCurrentSemester($query)
    {
        $activeSemester = Semester::where('is_active', true)->first();
        return $query->when($activeSemester, fn ($q) =>
            $q->where('semester_id', $activeSemester->id)
        );
    }

    public function scopeEnrolled($query)
    {
        return $query->where('status', EnrollmentStatus::Enrolled);
    }

    public function scopePending($query)
    {
        return $query->where('status', EnrollmentStatus::Pending);
    }

    public function scopeSearch($query, ?string $search)
    {
        return $query->when($search, fn ($q) =>
            $q->whereHas('student', fn ($s) =>
                $s->where('lrn', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('first_name', 'like', "%{$search}%")
            )
        );
    }

    public function scopeByStrand($query, ?int $strandId)
    {
        return $query->when($strandId, fn ($q) =>
            $q->whereHas('section', fn ($s) => $s->where('strand_id', $strandId))
        );
    }

    public function scopeBySectionId($query, ?int $sectionId)
    {
        return $query->when($sectionId, fn ($q) => $q->where('section_id', $sectionId));
    }

    public function scopeByStatus($query, ?string $status)
    {
        return $query->when($status, fn ($q) => $q->where('status', $status));
    }

    // Report scopes
    public static function summaryByTrack(?int $semesterId = null)
    {
        return static::query()
            ->when($semesterId, fn ($q) => $q->where('semester_id', $semesterId), fn ($q) => $q->currentSemester())
            ->enrolled()
            ->join('sections', 'enrollments.section_id', '=', 'sections.id')
            ->join('strands', 'sections.strand_id', '=', 'strands.id')
            ->join('tracks', 'strands.track_id', '=', 'tracks.id')
            ->selectRaw('tracks.name as track, COUNT(*) as total')
            ->groupBy('tracks.name')
            ->get();
    }

    public static function summaryByStrand(?int $semesterId = null)
    {
        return static::query()
            ->when($semesterId, fn ($q) => $q->where('semester_id', $semesterId), fn ($q) => $q->currentSemester())
            ->enrolled()
            ->join('sections', 'enrollments.section_id', '=', 'sections.id')
            ->join('strands', 'sections.strand_id', '=', 'strands.id')
            ->selectRaw('strands.name as strand, COUNT(*) as total')
            ->groupBy('strands.name')
            ->get();
    }

    public static function summaryBySection(?int $semesterId = null)
    {
        return static::query()
            ->when($semesterId, fn ($q) => $q->where('semester_id', $semesterId), fn ($q) => $q->currentSemester())
            ->enrolled()
            ->join('sections', 'enrollments.section_id', '=', 'sections.id')
            ->join('strands', 'sections.strand_id', '=', 'strands.id')
            ->selectRaw('sections.id, sections.name, strands.name as strand, sections.grade_level, sections.max_capacity, COUNT(*) as enrolled_count')
            ->groupBy('sections.id', 'sections.name', 'strands.name', 'sections.grade_level', 'sections.max_capacity')
            ->get();
    }

    public static function summaryByGender(?int $semesterId = null)
    {
        return static::query()
            ->when($semesterId, fn ($q) => $q->where('semester_id', $semesterId), fn ($q) => $q->currentSemester())
            ->enrolled()
            ->join('students', 'enrollments.student_id', '=', 'students.id')
            ->selectRaw('students.gender, COUNT(*) as total')
            ->groupBy('students.gender')
            ->get();
    }
}
```

---

### 7.10 Grade

```php
// app/Models/Grade.php
class Grade extends Model
{
    use HasFactory;
    use GradeRelations, GradeScopes;

    protected $fillable = ['enrollment_id', 'subject_id', 'midterm', 'finals', 'final_grade', 'remarks', 'is_locked', 'encoded_by'];

    protected function casts(): array
    {
        return [
            'midterm' => 'decimal:2',
            'finals' => 'decimal:2',
            'final_grade' => 'decimal:2',
            'is_locked' => 'boolean',
            'remarks' => \App\Enums\GradeRemarks::class,
        ];
    }
}
```

```php
// app/Traits/Model/GradeRelations.php
namespace App\Traits\Model;

use App\Models\Enrollment;
use App\Models\Subject;
use App\Models\User;

trait GradeRelations
{
    public function enrollment()
    {
        return $this->belongsTo(Enrollment::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function encoder()
    {
        return $this->belongsTo(User::class, 'encoded_by');
    }
}
```

```php
// app/Traits/Model/GradeScopes.php
namespace App\Traits\Model;

trait GradeScopes
{
    public function scopePassed($query)
    {
        return $query->where('remarks', 'passed');
    }

    public function scopeFailed($query)
    {
        return $query->where('remarks', 'failed');
    }

    public function scopeLocked($query)
    {
        return $query->where('is_locked', true);
    }

    public function scopeUnlocked($query)
    {
        return $query->where('is_locked', false);
    }
}
```

---

### 7.11 AuditLog

```php
// app/Models/AuditLog.php
class AuditLog extends Model
{
    use HasFactory;
    use AuditLogRelations;

    public $timestamps = false;
    protected $fillable = ['user_id', 'action', 'model', 'model_id', 'old_values', 'new_values', 'ip_address', 'created_at'];

    protected function casts(): array
    {
        return [
            'old_values' => 'array',
            'new_values' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function getModelShortNameAttribute(): string
    {
        return class_basename($this->model);
    }
}
```

```php
// app/Traits/Model/AuditLogRelations.php
namespace App\Traits\Model;

use App\Models\User;

trait AuditLogRelations
{
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeRecent($query, int $limit = 10)
    {
        return $query->with('user:id,name')
            ->latest('created_at')
            ->take($limit);
    }
}
```

---

### 7.12 TeacherProfile (SF7 — Teacher Profiling)

```php
// app/Models/TeacherProfile.php
class TeacherProfile extends Model
{
    use HasFactory;
    use TeacherProfileRelations;

    protected $fillable = [
        'user_id', 'employee_id', 'position_title', 'appointment_status',
        'sex', 'birthdate', 'contact_number', 'address',
        'highest_degree', 'degree_course', 'degree_major', 'school_graduated', 'year_graduated',
        'prc_license_number', 'prc_validity', 'eligibility',
        'specialization', 'date_hired', 'teaching_hours_per_week',
    ];

    protected function casts(): array
    {
        return [
            'birthdate' => 'date',
            'prc_validity' => 'date',
            'date_hired' => 'date',
        ];
    }

    public function getYearsInServiceAttribute(): ?int
    {
        return $this->date_hired ? $this->date_hired->diffInYears(now()) : null;
    }

    public function getIsPrcExpiredAttribute(): bool
    {
        return $this->prc_validity && $this->prc_validity->isPast();
    }
}
```

```php
// app/Traits/Model/TeacherProfileRelations.php
namespace App\Traits\Model;

use App\Models\User;
use App\Models\TeacherTraining;

trait TeacherProfileRelations
{
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function trainings()
    {
        return $this->hasMany(TeacherTraining::class)->orderByDesc('date_from');
    }

    public function totalTrainingHours(): float
    {
        return $this->trainings()->sum('hours') ?? 0;
    }
}
```

---

### 7.13 TeacherTraining

```php
// app/Models/TeacherTraining.php
class TeacherTraining extends Model
{
    use HasFactory;

    protected $fillable = [
        'teacher_profile_id', 'title', 'type', 'sponsor',
        'date_from', 'date_to', 'hours',
    ];

    protected function casts(): array
    {
        return [
            'date_from' => 'date',
            'date_to' => 'date',
            'hours' => 'decimal:1',
        ];
    }

    public function teacherProfile()
    {
        return $this->belongsTo(TeacherProfile::class);
    }
}
```

---

### 7.14 Updated User Model (with TeacherProfile relation)

```php
// Add to app/Traits/Model/UserRelations.php
public function teacherProfile()
{
    return $this->hasOne(\App\Models\TeacherProfile::class);
}

public function hasTeacherProfile(): bool
{
    return $this->teacherProfile()->exists();
}
```

---

### 7.12 SchoolSetting

```php
// app/Models/SchoolSetting.php
class SchoolSetting extends Model
{
    use HasFactory;

    protected $fillable = ['key', 'value'];

    // Helper: get a setting value with fallback
    public static function get(string $key, mixed $default = null): mixed
    {
        return static::where('key', $key)->value('value') ?? $default;
    }

    // Helper: set a setting value
    public static function set(string $key, mixed $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
    }

    // Helper: get all settings as key-value array
    public static function allAsArray(): array
    {
        return static::pluck('value', 'key')->toArray();
    }
}
```

---

### 7.13 Trait File Summary

| Trait File | Used By | Contains |
|---|---|---|
| `UserRelations.php` | User | advisedSections, encodedGrades, auditLogs |
| `UserScopes.php` | User | active, teachers, search |
| `StudentRelations.php` | Student | enrollments, currentEnrollment, latestEnrollment, grades |
| `StudentScopes.php` | Student | search, active, byStatus, byStrand, byGradeLevel, withCurrentSection |
| `SchoolYearRelations.php` | SchoolYear | semesters, firstSemester, secondSemester |
| `SchoolYearScopes.php` | SchoolYear | active |
| `SemesterRelations.php` | Semester | schoolYear, sections, enrollments |
| `SemesterScopes.php` | Semester | active, activeSemester |
| `TrackRelations.php` | Track | strands, activeStrands |
| `StrandRelations.php` | Strand | track, subjects, subjectsForGradeAndSemester, sections |
| `SubjectRelations.php` | Subject | prerequisite, dependents, strands, grades, enrollments |
| `SubjectScopes.php` | Subject | active, search, byType, forStrand |
| `SectionRelations.php` | Section | strand, semester, adviser, enrollments, activeEnrollments |
| `SectionScopes.php` | Section | currentSemester, byStrand, byGradeLevel, enrolledCount, isFull, capacityPercent |
| `EnrollmentRelations.php` | Enrollment | student, section, semester, subjects, grades, gradeFor |
| `EnrollmentScopes.php` | Enrollment | currentSemester, enrolled, pending, search, byStrand, bySectionId, byStatus, summaryByTrack/Strand/Section/Gender |
| `GradeRelations.php` | Grade | enrollment, subject, encoder |
| `GradeScopes.php` | Grade | passed, failed, locked, unlocked |
| `AuditLogRelations.php` | AuditLog | user, recent |
| `TeacherProfileRelations.php` | TeacherProfile | user, trainings, totalTrainingHours |

**Total: 21 trait files across 13 models. Every relationship and query scope is extracted. Models are clean.**

---

### 7.16 TeacherProfile Controller & Pages

```php
// app/Http/Controllers/TeacherProfileController.php

// GET /teachers
public function index(Request $request)
{
    return Inertia::render('Teachers/Index', [
        'teachers' => fn () => User::role('teacher')
            ->with('teacherProfile')
            ->withCount('advisedSections')
            ->search($request->search)
            ->active()
            ->orderBy('name')
            ->paginate(25)
            ->withQueryString(),
        'filters' => $request->only(['search']),
    ]);
}

// GET /teachers/{user}
public function show(User $user)
{
    return Inertia::render('Teachers/Show', [
        'teacher' => $user->load('teacherProfile.trainings', 'advisedSections.strand'),
    ]);
}

// GET /teachers/{user}/edit
public function edit(User $user)
{
    return Inertia::render('Teachers/Edit', [
        'teacher' => $user->load('teacherProfile.trainings'),
    ]);
}

// PUT /teachers/{user}
public function update(UpdateTeacherProfileRequest $request, User $user)
{
    $user->teacherProfile()->updateOrCreate(
        ['user_id' => $user->id],
        $request->validated()
    );

    return redirect()->back()->with('success', 'Teacher profile updated.');
}

// POST /teachers/{user}/trainings
public function addTraining(Request $request, User $user)
{
    $request->validate([
        'title' => 'required|string|max:255',
        'type' => 'nullable|string|max:50',
        'sponsor' => 'nullable|string|max:255',
        'date_from' => 'nullable|date',
        'date_to' => 'nullable|date|after_or_equal:date_from',
        'hours' => 'nullable|numeric|min:0',
    ]);

    $user->teacherProfile->trainings()->create($request->all());

    return redirect()->back()->with('success', 'Training added.');
}

// DELETE /teachers/{user}/trainings/{training}
public function removeTraining(User $user, TeacherTraining $training)
{
    $training->delete();
    return redirect()->back()->with('success', 'Training removed.');
}

// GET /reports/school-forms/sf7 — Generate SF7
public function generateSF7()
{
    return (new GenerateSF7)->download();
}
```

**Teacher Profile Page (what it looks like):**

```
┌────────────────────────────────────────────────────────────────┐
│  TEACHER PROFILE — Ms. Maria Cruz                              │
│                                                                │
│  ┌──────────────┐ ┌──────────────────┐ ┌────────────────────┐  │
│  │ Personal Info│ │ Qualifications   │ │ Trainings          │  │
│  └──────┬───────┘ └──────────────────┘ └────────────────────┘  │
│         │                                                      │
│  ┌──────▼──────────────────────────────────────────────────┐   │
│  │  Employee ID:     T-2024-001                            │   │
│  │  Position:        Teacher III                           │   │
│  │  Status:          Permanent                             │   │
│  │  Specialization:  Mathematics                           │   │
│  │  Date Hired:      June 2018 (6 years in service)        │   │
│  │                                                         │   │
│  │  Degree:          BSEd Mathematics                      │   │
│  │  School:          Notre Dame of Marbel University       │   │
│  │  Year:            2017                                  │   │
│  │                                                         │   │
│  │  PRC License:     0987654  (Valid until Dec 2026) ✅    │   │
│  │  Eligibility:     LET Passer                            │   │
│  │                                                         │   │
│  │  Advised Sections:                                      │   │
│  │    • STEM-A (Grade 11) — 47 students                    │   │
│  │                                                         │   │
│  │  Teaching Load:   30 hrs/week                           │   │
│  └─────────────────────────────────────────────────────────┘   │
│                                                                │
│  Trainings/Seminars:                                           │
│  ┌──────────────────────────────┬──────────┬────────┬───────┐  │
│  │ Title                        │ Sponsor  │ Date   │ Hours │  │
│  ├──────────────────────────────┼──────────┼────────┼───────┤  │
│  │ SHS Curriculum Training      │ DepEd XII│ 2024   │  40   │  │
│  │ Research Writing Workshop    │ CHED     │ 2023   │  24   │  │
│  │ IPCRF Orientation            │ DepEd    │ 2023   │   8   │  │
│  │ Gender & Development Seminar │ CSC      │ 2022   │  16   │  │
│  └──────────────────────────────┴──────────┴────────┴───────┘  │
│  Total Training Hours: 88                                      │
│                                                                │
│  [✏️ Edit Profile]  [+ Add Training]                           │
└────────────────────────────────────────────────────────────────┘
```

**Routes to add:**

```php
// Teachers (admin/registrar access)
Route::resource('teachers', TeacherProfileController::class)->only(['index', 'show', 'edit', 'update']);
Route::post('teachers/{user}/trainings', [TeacherProfileController::class, 'addTraining'])->name('teachers.trainings.store');
Route::delete('teachers/{user}/trainings/{training}', [TeacherProfileController::class, 'removeTraining'])->name('teachers.trainings.destroy');
```

**Sidebar update — add under Records:**

```
│  RECORDS                │
│  👤 Students            │
│  👩‍🏫 Teachers            │   ← NEW
│  🏫 Sections            │
```

**DepEd School Forms update — add SF7:**

```
│  📄 SF7 — Personnel Profile       [Generate Excel]  │   ← NEW
```

### Before Code (All Done ✅)

- [x] PRD v2 — What the system does, who uses it
- [x] Feature List v2 — 45 features with difficulty ratings
- [x] System Journey — 10 complete user flows
- [x] Architecture — Packages, folder structure, coding conventions
- [x] Inertia Performance Guide — useForm, deferred, partial reloads
- [x] Database Schema — 14 tables with all columns, indexes, FKs
- [x] ERD — Visual relationship map
- [x] Seeder Data — Roles, curriculum, real Filipino names, DepEd subjects
- [x] API Design — What every controller returns
- [x] Sidebar Navigation — Per-role menu structure with code

### Build Order

| Phase | What | Status |
|---|---|---|
| 0 | Project setup + packages + folder structure | ✅ Done |
| 1 | Auth + Users + School Year + Settings | ✅ Done |
| 2 | Curriculum (Tracks, Strands, Subjects) | ✅ Done |
| 3 | Students + Sections | ✅ Done |
| 4 | Enrollment Pipeline (5-step wizard) | ✅ Done |
| 5 | Grades | ✅ Done |
| 6 | Dashboard + Reports + DepEd Forms | ✅ Done |
| 7 | Data Import | ✅ Done |
| 8 | Testing + Polish + Deploy | ⬜ |

---

## 8. Complete Project Checklist

### Before Code (All Done ✅)

- [x] PRD v2 — What the system does, who uses it
- [x] Feature List v2 — 45 features with difficulty ratings
- [x] System Journey — 10 complete user flows
- [x] Architecture — Packages, folder structure, coding conventions
- [x] Inertia Performance Guide — useForm, deferred, partial reloads
- [x] Database Schema — 14 tables with all columns, indexes, FKs
- [x] ERD — Visual relationship map
- [x] Seeder Data — Roles, curriculum, real Filipino names, DepEd subjects
- [x] API Design — What every controller returns
- [x] Sidebar Navigation — Per-role menu structure with code
- [x] Model Traits — 19 trait files, every relationship and scope extracted

### Build Order

| Phase | What | Status |
|---|---|---|
| 0 | Project setup + packages + folder structure | ✅ Done |
| 1 | Auth + Users + School Year + Settings | ✅ Done |
| 2 | Curriculum (Tracks, Strands, Subjects) | ✅ Done |
| 3 | Students + Sections | ✅ Done |
| 4 | Enrollment Pipeline (5-step wizard) | ✅ Done |
| 5 | Grades | ✅ Done |
| 6 | Dashboard + Reports + DepEd Forms | ✅ Done |
| 7 | Data Import | ✅ Done |
| 8 | Testing + Polish + Deploy | ⬜ |
