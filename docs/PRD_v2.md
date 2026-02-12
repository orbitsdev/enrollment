# Product Requirements Document (PRD) v2

## Lake Sebu NHS — Enrollment & Student Records System

---

**Author:** Marivic P. Estember
**Course:** MIT 265 – Advanced Software Engineering
**Date:** February 2026
**Version:** 2.0

---

## 1. What This System Does

A web-based system that lets the registrar and staff of Lake Sebu National High School **enroll students, manage their records, and generate reports** — all in one place, replacing paper forms, Excel files, and disconnected folders.

That's it. No more, no less.

### 1.1 The 4 Problems We're Solving

| # | Problem (from Problem Statement) | What Happens Today | What the System Does |
|---|---|---|---|
| 1 | Manual enrollment using paper and separate files | Registrar fills out paper forms, encodes into Excel later. Slow during peak enrollment. | Digital enrollment form — search student, select strand, assign section, done. |
| 2 | Cannot quickly view or analyze enrollment data | Staff manually counts rows in Excel to get totals. Takes hours. | One-click reports: enrollment counts, class lists, student summaries. |
| 3 | Duplicate and inconsistent student records | Same student exists in 3 different Excel files with slightly different info. | One database. One student record. LRN as the unique key. |
| 4 | System slows down with multiple users | Only one person can edit an Excel file at a time. Others wait. | Web-based — multiple staff can work simultaneously from different computers. |

### 1.2 Who Uses It

| User | What They Do | How Often |
|---|---|---|
| **Registrar** | Enrolls students, manages records, prints reports and school forms | Daily during enrollment, weekly otherwise |
| **Admin (Principal / SHS Coordinator)** | Views enrollment dashboard, monitors section capacity, reviews reports | Weekly |
| **Teacher / Adviser** | Views own section roster, inputs grades at end of semester | End of semester mainly |
| **Student** | Views own enrollment status and grades | Occasionally |

---

## 2. Tech Stack

| Layer | Technology |
|---|---|
| Backend | Laravel 11 (PHP 8.2+) |
| Frontend | Vue 3 (Composition API + `<script setup>`) |
| SPA Bridge | Inertia.js v2 |
| UI Components | shadcn-vue + Tailwind CSS v4 |
| Auth | Laravel Breeze (Inertia + Vue) |
| Roles & Permissions | Spatie Laravel Permission |
| Database | MySQL 8 |
| PDF Export | barryvdh/laravel-dompdf |
| Excel Import/Export | Maatwebsite Laravel Excel |
| File Uploads | Spatie Laravel MediaLibrary |
| Modal System | @inertiaui/modal-vue (ModalLink + Modal) |
| Charts | vue-chartjs + chart.js |
| Utilities | @vueuse/core |
| Build Tool | Vite |
| Dev Tools | Laravel IDE Helper, Debugbar, Pint, Pest |

> **Full package list, folder structure, coding conventions, modal vs full page decisions, Vue transitions, and component library** → see **ARCHITECTURE.md**

---

## 3. Core System — The Features That Solve the Problems

### Module 1: Authentication & Users 🟢

**Features:**

1. **Login / Logout** — email + password, "remember me"
2. **User Accounts** — admin creates accounts for staff (no self-registration)
3. **4 Roles** — Admin, Registrar, Teacher, Student
4. **Role-Based Access** — each role sees only what they need (Laravel Policies)

**Pages:**
- `/login` — login form
- `/users` — user list (admin only)
- `/users/create` — add user
- `/users/{id}/edit` — edit user
- `/profile` — edit own profile, change password

---

### Module 2: School Year & Semester 🟢

**Features:**

1. **School Year** — create (e.g., "2026-2027"), set one as active
2. **Semester** — 1st or 2nd, set active semester
3. **Enrollment Period** — open/close toggle (when closed, enrollment form is disabled)

**Pages:**
- `/settings/school-year` — manage school years and semesters
- No separate page needed — this lives in system settings

**Why this exists:** Every other module filters by school year + semester. This is the global context.

---

### Module 3: Curriculum Setup 🟡

> This is the "configuration" module. Set it up once at the start of the school year, rarely touch it after.

**Features:**

1. **Tracks** — Academic, TVL, Sports, Arts & Design (enable/disable what the school offers)
2. **Strands** — STEM, ABM, HUMSS, GAS, etc. linked to their track
3. **Subjects** — code, name, type (core/specialized/applied), semester, grade level
4. **Subject-Strand Mapping** — which subjects belong to which strand for which grade level + semester (this is the subject load template)
5. **Prerequisites** — simple: Subject B requires Subject A (one-to-one only)

**Pages:**
- `/curriculum/tracks` — list tracks with strands nested
- `/curriculum/strands/{id}` — strand detail with its subjects
- `/curriculum/subjects` — subject list with filters
- `/curriculum/subjects/create` — add subject
- `/curriculum/subjects/{id}/edit` — edit subject, set prerequisite

**Data example — what the registrar configures:**
```
Track: Academic
  └── Strand: STEM
        └── Grade 11, 1st Semester:
              - Oral Communication (Core)
              - General Mathematics (Core)
              - Pre-Calculus (Specialized)
              - General Biology 1 (Specialized)
              ...
        └── Grade 11, 2nd Semester:
              - Komunikasyon at Pananaliksik (Core)
              - Statistics and Probability (Core)
              - Basic Calculus (Specialized)
              - General Biology 2 (Specialized)
              ...
```

---

### Module 4: Student Records 🟢

> This is the **centralized student database** — the single source of truth that replaces all those disconnected Excel files.

**Features:**

1. **Add Student** — LRN, name, birthdate, gender, address (barangay/municipality), contact, guardian info
2. **Search Student** — by LRN (exact) or by name (partial match)
3. **Filter Students** — by grade level, strand, section, status
4. **Student Profile Page** — all info in one place: personal info, enrollment history, grades
5. **Edit Student** — update personal info (registrar/admin only)
6. **Student Status** — Active, Transferred Out, Dropped, Graduated
7. **Duplicate Detection** — warning when name + birthdate matches an existing record

**Pages:**
- `/students` — student list with search bar and filters
- `/students/create` — add new student
- `/students/{id}` — student profile (tabs: Info, Enrollment History, Grades)
- `/students/{id}/edit` — edit personal info

**What the student profile looks like:**
```
┌─────────────────────────────────────────────────────┐
│  Juan Dela Cruz                         LRN: 1234567│
│  Grade 12 | STEM | Section: STEM-A                  │
│  Status: Active                                     │
├─────────────────────────────────────────────────────┤
│  [Personal Info]  [Enrollment History]  [Grades]     │
├─────────────────────────────────────────────────────┤
│  Personal Info tab:                                  │
│    Birthdate: March 15, 2008                        │
│    Gender: Male                                     │
│    Address: Barangay Poblacion, Lake Sebu            │
│    Contact: 0917-xxx-xxxx                           │
│    Guardian: Maria Dela Cruz (Mother)               │
│    Guardian Contact: 0918-xxx-xxxx                  │
├─────────────────────────────────────────────────────┤
│  Enrollment History tab:                             │
│    SY 2025-2026, 1st Sem — Grade 11, STEM, STEM-A  │
│    SY 2025-2026, 2nd Sem — Grade 11, STEM, STEM-A  │
│    SY 2026-2027, 1st Sem — Grade 12, STEM, STEM-A  │
├─────────────────────────────────────────────────────┤
│  Grades tab:                                        │
│    SY 2025-2026, 1st Semester:                      │
│      Oral Communication ............ 88             │
│      General Mathematics ........... 91             │
│      Pre-Calculus .................. 85             │
│      ...                                            │
└─────────────────────────────────────────────────────┘
```

---

### Module 5: Sections 🟢

**Features:**

1. **Create Section** — name (e.g., "STEM-A"), grade level, strand, school year, max capacity
2. **Assign Adviser** — pick a teacher
3. **Section List** — shows all sections with enrolled count / capacity
4. **Section Roster** — list of students in a section (this is the class list)
5. **Export Class List** — PDF or Excel

**Pages:**
- `/sections` — section list for active school year (cards or table)
- `/sections/create` — add section
- `/sections/{id}` — section detail with student roster
- `/sections/{id}/edit` — edit section info

**Section list view:**
```
┌──────────────────────────────────────────────────┐
│  Sections — SY 2026-2027                         │
├──────────┬────────┬──────────┬────────┬──────────┤
│ Section  │ Strand │ Adviser  │ Count  │ Status   │
├──────────┼────────┼──────────┼────────┼──────────┤
│ STEM-A   │ STEM   │ Mr. Reyes│ 47/50  │ 🟡 94%  │
│ STEM-B   │ STEM   │ Ms. Cruz │ 38/50  │ 🟢 76%  │
│ ABM-A    │ ABM    │ Mr. Santos│ 50/50 │ 🔴 Full │
│ HUMSS-A  │ HUMSS  │ Ms. Diaz │ 42/50  │ 🟢 84%  │
└──────────┴────────┴──────────┴────────┴──────────┘
```

---

### Module 6: Enrollment 🔴 (Core Module)

> This is the heart of the system. This is what the registrar uses all day during enrollment week.

**Features:**

1. **Enroll Student** — the main workflow (see below)
2. **Enrollment List** — all enrollments for active school year with status
3. **Search & Filter Enrollments** — by student name, strand, section, status
4. **Change Enrollment Status** — Enrolled → Dropped / Transferred (with reason)
5. **Print Enrollment Slip** — simple PDF confirmation

**The Enrollment Workflow (single page, step-by-step):**

```
STEP 1: Find Student
┌─────────────────────────────────────────┐
│  Search by LRN: [___________] [Search]  │
│  Search by Name: [___________] [Search] │
│                                         │
│  ✅ Found: Juan Dela Cruz (LRN: 1234567)│
│     Grade 11 last year, STEM            │
│                                         │
│  [Select This Student]                  │
│                                         │
│  Student not found? [+ Add New Student] │
└─────────────────────────────────────────┘
                    ↓
STEP 2: Track & Strand
┌─────────────────────────────────────────┐
│  Track:  [Academic ▼]                   │
│  Strand: [STEM ▼]                       │
│  Grade Level: [Grade 12] (auto-filled)  │
│                                         │
│  ℹ️ Returning student — strand carried  │
│    over from Grade 11 enrollment.       │
└─────────────────────────────────────────┘
                    ↓
STEP 3: Subjects (Auto-Loaded)
┌─────────────────────────────────────────┐
│  Subjects for STEM, Grade 12, 1st Sem:  │
│                                         │
│  ✅ 21st Century Literature (Core)      │
│  ✅ Contemporary Philippine Arts (Core) │
│  ✅ Physical Science (Specialized)      │
│  ✅ Research in Daily Life (Applied)    │
│  ⚠️ Practical Research 2 (Specialized) │
│     └─ Prereq: Practical Research 1     │
│        Status: PASSED (82) ✅           │
│                                         │
│  Total: 9 subjects                      │
└─────────────────────────────────────────┘
                    ↓
STEP 4: Section Assignment
┌─────────────────────────────────────────┐
│  Suggested Section: STEM-A (47/50)      │
│                                         │
│  Or choose: [STEM-A (47/50) ▼]         │
│             [STEM-B (38/50)  ]          │
│                                         │
│  [Accept & Continue]                    │
└─────────────────────────────────────────┘
                    ↓
STEP 5: Confirm
┌─────────────────────────────────────────┐
│  ENROLLMENT SUMMARY                     │
│                                         │
│  Student: Juan Dela Cruz (LRN: 1234567) │
│  SY: 2026-2027 | Semester: 1st         │
│  Track: Academic | Strand: STEM         │
│  Grade Level: 12                        │
│  Section: STEM-A                        │
│  Subjects: 9                            │
│                                         │
│  [◀ Back]         [✅ Confirm Enrollment]│
│                                         │
│  □ Print enrollment slip after saving   │
└─────────────────────────────────────────┘
```

**Enrollment List Page:**
- `/enrollment` — table of all enrollments for active SY
- Columns: Student Name, LRN, Strand, Section, Date Enrolled, Status
- Filters: strand, section, status
- Search by student name or LRN

**Validation Rules (all automatic):**

| Rule | What Happens |
|---|---|
| Student already enrolled this semester | ❌ Blocked — "Student is already enrolled" |
| Subject prerequisite not passed | ⚠️ Warning — registrar can override with reason |
| Section is full | ⚠️ Warning — suggest another section |
| Enrollment period is closed | ❌ Form disabled — "Enrollment is currently closed" |
| Duplicate LRN | ❌ Blocked on student creation |

---

### Module 7: Grades 🟡

**Features:**

1. **Grade Entry** — teacher selects section + subject → inputs grades per student
2. **Auto-Compute** — final grade + remarks (Passed ≥ 75, Failed < 75)
3. **Grade Viewing** — teachers see own sections, students see own grades, admin sees all
4. **Grade Lock** — admin locks grades after deadline, unlock requires admin action

**Pages:**
- `/grades` — teacher selects section → subject → sees student list with grade inputs
- `/grades/{section}/{subject}` — grade entry form
- Student grades appear on `/students/{id}` under the Grades tab

**Grade entry view:**
```
┌───────────────────────────────────────────────────────┐
│  Grade Entry — STEM-A | General Mathematics           │
│  SY 2026-2027, 1st Semester                          │
│  Teacher: Mr. Reyes                                   │
├─────┬──────────────────┬─────────┬────────┬──────────┤
│  #  │ Student Name     │ Midterm │ Final  │ Grade    │
├─────┼──────────────────┼─────────┼────────┼──────────┤
│  1  │ Dela Cruz, Juan  │ [88]    │ [91]   │ 89.5 ✅  │
│  2  │ Santos, Maria    │ [75]    │ [72]   │ 73.5 ❌  │
│  3  │ Reyes, Pedro     │ [92]    │ [88]   │ 90.0 ✅  │
│  4  │ Garcia, Ana      │ [ ]     │ [ ]    │ —        │
│ ... │                  │         │        │          │
├─────┴──────────────────┴─────────┴────────┴──────────┤
│  [Save Grades]                              47 / 47  │
│  ✅ Saved successfully                               │
└───────────────────────────────────────────────────────┘
```

---

### Module 8: Reports & Dashboard 🟡

**Features:**

1. **Dashboard** — enrollment counts, section capacity, recent activity (home page for admin/registrar)
2. **Enrollment Summary Report** — totals by track, strand, grade level, section
3. **Class List** — per section, exportable to PDF/Excel
4. **Student Masterlist** — all enrolled students, exportable
5. **Grade Summary** — per section per subject, with pass/fail counts
6. **DepEd School Forms:**
   - **SF1** — School Register (Excel export)
   - **SF5** — Promotion Report (Excel export)
   - **SF9** — Report Card (PDF per student)
   - **SF10** — Permanent Record (PDF per student)

**Pages:**
- `/dashboard` — home page with charts and quick stats
- `/reports` — report menu
- `/reports/enrollment-summary` — enrollment report with filters + export
- `/reports/class-list` — select section → export
- `/reports/masterlist` — export all students
- `/reports/grade-summary` — select section + subject → view + export
- `/reports/school-forms` — select form type → generate

**Dashboard layout:**
```
┌─────────────────────────────────────────────────────────────┐
│  Dashboard — SY 2026-2027, 1st Semester                     │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│  ┌──────────┐  ┌──────────┐  ┌──────────┐  ┌──────────┐   │
│  │   487    │  │    12    │  │   10     │  │    3     │   │
│  │ Enrolled │  │ Sections │  │ Strands  │  │ Pending  │   │
│  └──────────┘  └──────────┘  └──────────┘  └──────────┘   │
│                                                             │
│  ┌──────────────────────────┐  ┌──────────────────────────┐│
│  │  Enrollment by Track     │  │  Section Capacity        ││
│  │  ████████████ Academic   │  │  STEM-A   ████████░░ 94% ││
│  │  ██████░░░░░░ TVL        │  │  STEM-B   ██████░░░░ 76% ││
│  │  ██░░░░░░░░░░ Sports     │  │  ABM-A    ██████████ FULL││
│  └──────────────────────────┘  │  HUMSS-A  ████████░░ 84% ││
│                                 └──────────────────────────┘│
│  ┌──────────────────────────────────────────────────────┐  │
│  │  Recent Enrollments                                   │  │
│  │  10:32 AM — Juan Dela Cruz enrolled in STEM-A         │  │
│  │  10:28 AM — Maria Santos enrolled in ABM-A            │  │
│  │  10:15 AM — Pedro Reyes enrolled in HUMSS-A           │  │
│  └──────────────────────────────────────────────────────┘  │
└─────────────────────────────────────────────────────────────┘
```

---

### Module 9: Data Import 🟡

> Used once during initial setup to migrate existing records, and occasionally for bulk operations.

**Features:**

1. **Student Import** — upload Excel/CSV → preview → validate → import
2. **Grade Import** — upload Excel → preview → validate → import
3. **Download Templates** — blank Excel templates with correct column headers

**Pages:**
- `/import` — import hub
- `/import/students` — student import with upload + preview
- `/import/grades` — grade import with upload + preview

**Import flow:**
```
Upload File → Preview Table → Errors Highlighted in Red → Fix or Skip → Import Valid Rows
```

---

### Module 10: System Settings 🟢

**Features:**

1. **School Info** — school name, ID, address, division, region (used in report headers)
2. **Grading Config** — passing grade (default 75), midterm/finals weight (default 50/50)
3. **Enrollment Config** — default section capacity, enrollment open/close

**Pages:**
- `/settings` — single settings page with sections

---

## 4. System Journeys

> Complete user journeys showing every click, every screen → see **SYSTEM_JOURNEY.md**

**10 journeys documented:**
1. Admin — First-time system setup (settings, curriculum, users, sections)
2. Registrar — Enrollment day (5-step wizard, repeated 50-80 times)
3. Registrar — Managing enrollment list (search, filter, change status)
4. Registrar — Student records lookup (profile tabs, print SF9/SF10)
5. Teacher — Grade entry (pick section → subject → input grades)
6. Teacher — View section roster (class list + export)
7. Student — Check enrollment status and view grades
8. Principal — Dashboard and reports
9. Registrar — Data import (upload Excel → preview → validate → import)
10. Registrar — End of semester DepEd form generation

---

## 5. Inertia.js v2 Performance Standards

> These patterns are mandatory on every page — especially important for Lake Sebu's low-bandwidth environment.

### 5.1 Forms — Always `useForm()`

```js
const form = useForm('EnrollStudent', {
  student_id: null, track_id: null, strand_id: null, section_id: null,
})

const submit = () => {
  form.post('/enrollment', {
    preserveScroll: true,
    onSuccess: () => form.reset(),
  })
}
```

**Every form must have:**
- `:disabled="form.processing"` on submit button
- `form.errors.<field>` displayed below every input
- `preserveScroll: true` on every submission
- `form.recentlySuccessful` for success feedback

### 5.2 Controllers — Always Lazy Closures

```php
return Inertia::render('Students/Index', [
    'students' => fn () => Student::query()->filter($request)->paginate(25), // Lazy
    'strands'  => fn () => Strand::with('track')->get(),                    // Lazy
    'filters'  => $request->only(['search', 'strand', 'status']),           // Always (small)
]);
```

### 5.3 Deferred Props — Dashboard & Heavy Pages

```php
return Inertia::render('Dashboard', [
    'user'              => $user,                                             // Immediate
    'enrollment_counts' => Inertia::defer(fn () => Stats::byTrack()),        // After paint
    'section_capacity'  => Inertia::defer(fn () => Stats::capacity()),       // After paint
    'recent_activity'   => Inertia::defer(fn () => AuditLog::recent(10)),    // After paint
]);
```

```vue
<Deferred data="enrollment_counts">
  <template #fallback><SkeletonChart /></template>
  <EnrollmentChart :data="enrollment_counts" />
</Deferred>
```

### 5.4 Partial Reloads — Filtering & Sorting

```js
// When user filters student list — reload only students, not everything
router.reload({ only: ['students'] })
```

### 5.5 Prefetching — Navigation Links

```vue
<Link href="/students" prefetch :cache-tags="['students']">Students</Link>
<Link href="/enrollment" prefetch :cache-tags="['enrollment']">Enrollment</Link>
```

Invalidate after mutations:
```js
form.post('/enrollment', {
  invalidateCacheTags: ['enrollment', 'dashboard', 'students'],
})
```

### 5.6 WhenVisible — Student Profile Heavy Tabs

```php
return Inertia::render('Students/Show', [
    'student'     => $student,                                                    // Immediate
    'grades'      => Inertia::optional(fn () => $student->grades()->get()),       // On scroll
    'enrollments' => Inertia::optional(fn () => $student->enrollments()->get()),  // On scroll
]);
```

### 5.7 Page Checklist

- [ ] Forms use `useForm` with `preserveScroll: true`
- [ ] Submit buttons disabled during `form.processing`
- [ ] Errors shown below every input
- [ ] Heavy data uses `Inertia::defer()` with skeleton fallback
- [ ] Filters use `router.reload({ only: [...] })`
- [ ] Nav links use `prefetch` with cache tags
- [ ] Cache tags invalidated on form submissions

---

## 6. Database, API & Navigation

> Full specification with all migration code, ERD, seeder data, controller return values, and sidebar navigation → see **DATABASE_API_NAV.md**

**14 tables:** users, school_years, semesters, tracks, strands, subjects, subject_strand, students, sections, enrollments, enrollment_subject, grades, school_settings, audit_logs

**Key constraints:**
- `students.lrn` — unique (prevents duplicates)
- `enrollments` — unique on `[student_id, semester_id]` (one enrollment per student per semester)
- `grades` — unique on `[enrollment_id, subject_id]` (one grade per subject per enrollment)
- `subjects.prerequisite_id` — self-referencing FK (prerequisite chain)

### Tables

```
users
  - id, name, email, password, role (admin/registrar/teacher/student), linked_id, timestamps

school_years
  - id, name (e.g., "2026-2027"), is_active, timestamps

semesters
  - id, school_year_id, name (1st/2nd), is_active, enrollment_open, timestamps

tracks
  - id, name, is_active, timestamps

strands
  - id, track_id, name, is_active, timestamps

subjects
  - id, code, name, type (core/specialized/applied), hours, prerequisite_id (nullable, self-ref), timestamps

subject_strand (pivot)
  - id, subject_id, strand_id, grade_level (11/12), semester (1/2)

students
  - id, lrn (unique), first_name, middle_name, last_name, suffix, birthdate, gender,
    barangay, municipality, province, contact, guardian_name, guardian_relationship,
    guardian_contact, previous_school, status (active/transferred/dropped/graduated), timestamps

sections
  - id, name, strand_id, semester_id, grade_level, max_capacity, adviser_id (FK users), timestamps

enrollments
  - id, student_id, section_id, semester_id, status (pending/enrolled/dropped/transferred),
    enrolled_at, remarks, timestamps

enrollment_subjects (pivot)
  - id, enrollment_id, subject_id

grades
  - id, enrollment_id, subject_id, midterm, finals, final_grade, remarks (passed/failed),
    locked, timestamps

audit_logs
  - id, user_id, action, model, model_id, changes (JSON), timestamps

school_settings
  - id, key, value, timestamps
```

### Key Relationships

```
Track        → has many → Strands
Strand       → has many → Subjects (via subject_strand pivot)
Strand       → has many → Sections
Section      → has many → Enrollments
Section      → belongs to → Semester
Section      → belongs to → User (adviser)
Student      → has many → Enrollments
Enrollment   → has many → Subjects (via enrollment_subjects pivot)
Enrollment   → has many → Grades
Enrollment   → belongs to → Section
Enrollment   → belongs to → Semester
Subject      → has one → Subject (prerequisite, self-referencing)
User         → has many → Sections (as adviser)
```

---

## 7. Page Map (All Routes)

| Route | Page | Access |
|---|---|---|
| `/login` | Login | Public |
| `/dashboard` | Dashboard with stats and charts | Admin, Registrar |
| `/students` | Student list with search and filters | Admin, Registrar, Teacher (own section) |
| `/students/create` | Add student form | Admin, Registrar |
| `/students/{id}` | Student profile (tabs: Info, Enrollments, Grades) | Admin, Registrar, Teacher (own), Student (self) |
| `/students/{id}/edit` | Edit student info | Admin, Registrar |
| `/enrollment` | Enrollment list for active school year | Admin, Registrar |
| `/enrollment/create` | Enrollment wizard (the 5-step form) | Admin, Registrar |
| `/enrollment/{id}` | Enrollment detail | Admin, Registrar |
| `/sections` | Section list with capacity indicators | Admin, Registrar, Teacher |
| `/sections/create` | Add section | Admin, Registrar |
| `/sections/{id}` | Section roster (class list) | Admin, Registrar, Teacher (own) |
| `/sections/{id}/edit` | Edit section | Admin, Registrar |
| `/grades` | Grade entry — select section + subject | Teacher, Admin |
| `/grades/{section}/{subject}` | Grade input form for students | Teacher (own), Admin |
| `/reports` | Report menu | Admin, Registrar |
| `/reports/enrollment-summary` | Enrollment summary with export | Admin, Registrar |
| `/reports/class-list` | Class list generator with export | Admin, Registrar, Teacher |
| `/reports/masterlist` | Full student masterlist export | Admin, Registrar |
| `/reports/grade-summary` | Grade summary per section/subject | Admin, Registrar, Teacher |
| `/reports/school-forms` | DepEd SF1, SF5, SF9, SF10 generator | Admin, Registrar |
| `/curriculum/tracks` | Track and strand management | Admin |
| `/curriculum/subjects` | Subject list and management | Admin |
| `/curriculum/subjects/create` | Add subject | Admin |
| `/curriculum/subjects/{id}/edit` | Edit subject, set prerequisite | Admin |
| `/import` | Import hub | Admin, Registrar |
| `/import/students` | Student bulk import | Admin, Registrar |
| `/import/grades` | Grade bulk import | Admin, Registrar |
| `/settings` | System settings | Admin |
| `/users` | User management | Admin |
| `/users/create` | Add user | Admin |
| `/users/{id}/edit` | Edit user | Admin |
| `/profile` | Own profile and password change | All |

**Total: 32 pages**

---

## 8. What's NOT Included

| Feature | Why |
|---|---|
| Student self-enrollment portal | Students come to school to enroll in person |
| Mobile app | Responsive web works on any device |
| SMS / email notifications | No budget, unreliable in rural area |
| Payment / fees | Separate school process |
| Daily attendance (SF2) | Phase 2 if needed |
| Parent portal | Not essential |
| Chat / messaging | Staff talk in person |
| Complex class timetable scheduling | Out of scope — only section assignment |
| DepEd LIS integration | No API access — export-only |
| AI features | Thesis "Future Work" chapter |

---

## 9. Build Order

| Phase | What | Weeks | Why This Order |
|---|---|---|---|
| **0** | Project setup: Laravel + Breeze + packages + folder structure + seeders | Week 1 | Foundation — see ARCHITECTURE.md for setup commands |
| **1** | Auth + Users + School Year/Semester + Settings | Week 2-3 | Everything depends on auth and school year context |
| **2** | Curriculum setup (Tracks, Strands, Subjects) | Week 4-5 | Must exist before enrollment works |
| **3** | Student CRUD + Section CRUD | Week 6-7 | Must exist before enrollment works |
| **4** | **Enrollment pipeline** (the 5-step wizard) | Week 8-10 | The core feature — this is why the system exists |
| **5** | Grade entry + Grade viewing | Week 11-12 | Needed for prerequisite checks and reports |
| **6** | Dashboard + Reports + DepEd School Forms | Week 13-15 | Consumes data from phases 1-5 |
| **7** | Data import tools | Week 16 | For migrating existing records |
| **8** | Testing + Polish + Deployment | Week 17-18 | Final cleanup |

---

## 10. Success = Registrar's Life Gets Easier

The system is successful if:

- Enrolling one student takes **under 3 minutes** (vs 15+ minutes manual)
- Generating a class list takes **one click** (vs 30+ minutes in Excel)
- Zero duplicate student records
- Multiple staff can enroll students simultaneously
- SF1 report is generated in **seconds** (vs days of manual compilation)
- Registrar says: **"This is easier than what we had before"**

That's the only metric that matters.
