# System Guide — Lake Sebu NHS Enrollment System

> A complete walkthrough of every module, role, and page in the system.

---

## What This System Does

A web-based system for Lake Sebu National High School that handles **student enrollment, record management, grade entry, and DepEd report generation**. It replaces paper forms and Excel files with a centralized, multi-user digital system.

**Tech Stack:** Laravel 12 + Vue 3 + Inertia.js v2 + shadcn-vue + Tailwind CSS v4
**Auth:** Laravel Fortify (via Vue + Inertia Starter Kit)
**Roles:** Spatie Laravel Permission

---

## The 4 Roles

| Role | Who | Primary Task |
|------|-----|-------------|
| **Admin** | Principal / SHS Coordinator | Full system access, settings, user management |
| **Registrar** | Enrollment Officer | Enrolls students, manages records, generates reports |
| **Teacher** | Subject teacher / Adviser | Enters grades for assigned sections |
| **Student** | Enrolled student | Views own profile, subjects, and grades |

---

## Sidebar Navigation by Role

### Admin

| Group | Menu Item | Icon | Route |
|-------|-----------|------|-------|
| Main | Dashboard | LayoutGrid | `/dashboard` |
| Management | Users | Users | `/users` |
| Management | School Settings | Settings | `/school-settings` |
| Academic | Curriculum | BookOpen | `/curriculum` |
| Academic | Students | GraduationCap | `/students` |
| Academic | Teachers | UserCheck | `/teachers` |
| Academic | Sections | LayoutList | `/sections` |
| Academic | Enrollment | ClipboardList | `/enrollment` |
| Grades | Grade Entry | FileSpreadsheet | `/grades` |
| Reports | Reports | BarChart3 | `/reports` |
| Reports | Import Data | Upload | `/import` |

### Registrar

Same as Admin **except**: no Users, no School Settings.

### Teacher

| Group | Menu Item | Route |
|-------|-----------|-------|
| Main | Dashboard | `/dashboard` |
| Grades | Grade Entry | `/grades` |

### Student

| Group | Menu Item | Route |
|-------|-----------|-------|
| My Records | My Profile | `/my/profile` |
| My Records | My Subjects | `/my/subjects` |
| My Records | My Grades | `/my/grades` |

---

## Modules in Detail

### Module 1: Authentication & Users

**Who can access:** Admin manages users; all users can log in and edit own profile.

| Page | Route | What It Does |
|------|-------|-------------|
| Login | `/login` | Email + password login with "remember me" |
| User List | `/users` | Table of all staff accounts with role badges |
| Create User | `/users/create` | Admin creates account, assigns role |
| Edit User | `/users/{id}/edit` | Edit name, email, role; toggle active/inactive |
| Profile | `/settings/profile` | User edits own name, email |
| Password | `/settings/password` | Change own password |
| Two-Factor | `/settings/two-factor` | Enable/disable 2FA |
| Appearance | `/settings/appearance` | Light/dark mode toggle |

**Key features:**
- No self-registration (admin creates all accounts)
- Soft deactivation via `is_active` boolean
- Two-factor authentication support via Fortify

---

### Module 2: School Year & Semester

**Who can access:** Admin only.

| Page | Route | What It Does |
|------|-------|-------------|
| School Years | `/school-years` | Create/edit school years, manage semesters |

**Key features:**
- Create school years (e.g., "2026-2027")
- Set one school year as globally active (`is_active`)
- Each school year has 1st and 2nd semester
- Set one semester as active (controls what data shows system-wide)
- Open/close enrollment per semester (`enrollment_open` boolean)

**Why it matters:** Every other module (enrollment, sections, grades) filters by the active school year and semester.

---

### Module 3: School Settings

**Who can access:** Admin only.

| Page | Route | What It Does |
|------|-------|-------------|
| Settings | `/school-settings` | Configure school info and grading rules |

**Settings available:**

| Setting | Purpose |
|---------|---------|
| School Name | Used in report headers (e.g., "Lake Sebu National High School") |
| School ID | DepEd school identifier (e.g., "304550") |
| School Address | Used in report headers |
| District | DepEd district (e.g., "Lake Sebu East") |
| Division | DepEd division (e.g., "South Cotabato") |
| Region | DepEd region (e.g., "Region XII") |
| Passing Grade | Minimum grade to pass (default: 75) |
| Midterm Weight | Weight for midterm (default: 50%) |
| Finals Weight | Weight for finals (default: 50%) |
| Default Capacity | Default max students per section (default: 50) |

---

### Module 4: Curriculum (Tracks, Strands, Subjects)

**Who can access:** Admin, Registrar.

| Page | Route | What It Does |
|------|-------|-------------|
| Curriculum | `/curriculum` | Manage tracks and strands (nested view) |
| Subject List | `/subjects` | List all subjects with search/filter |
| Create Subject | `/subjects/create` | Add new subject |
| Edit Subject | `/subjects/{id}/edit` | Edit subject, set prerequisite |

**Structure:**

```
Track (e.g., Academic, TVL)
  └── Strand (e.g., STEM, ABM, ICT)
       └── Subjects (via pivot: subject_strand)
            └── Mapped per grade_level + semester
```

**Tracks:** Academic, TVL, Sports, Arts & Design
**Strands per Track:**
- Academic: STEM, ABM, HUMSS, GAS
- TVL: Home Economics, ICT (with course field for TESDA courses, e.g., "Computer System Servicing NC II"), Agri-Fishery Arts

**Subjects have:**
- Code (e.g., "GM-101"), Name, Type (core/specialized/applied), Hours
- Optional prerequisite (another subject that must be passed first)

**Subject-Strand Mapping:** Defines which subjects a student takes for a given strand + grade level + semester. This is what auto-populates during enrollment.

---

### Module 5: Student Records

**Who can access:** Admin, Registrar (full CRUD); Teacher (view only).

| Page | Route | What It Does |
|------|-------|-------------|
| Student List | `/students` | Search by LRN/name, filter by grade level/strand/status |
| Create Student | `/students/create` | Multi-step form: personal info, address, guardian, SF1 fields |
| Student Profile | `/students/{id}` | Three tabs: Personal Info, Enrollment History, Grades |
| Edit Student | `/students/{id}/edit` | Update student information |

**Student fields include:**
- LRN (unique), name, birthdate, gender, address, contact
- Guardian info (name, contact, relationship)
- SF1 fields: religion, learning modality, father name, mother name
- Status: Active, Transferred, Dropped, Graduated

**Duplicate detection:** When creating a student, the system checks if name + birthdate matches an existing record and shows a warning.

---

### Module 6: Teacher Profiling (SF7)

**Who can access:** Admin, Registrar.

| Page | Route | What It Does |
|------|-------|-------------|
| Teacher List | `/teachers` | List teachers with profile status (Complete/Incomplete) |
| Teacher Profile | `/teachers/{id}` | View personal info, employment, education, trainings |
| Edit Profile | `/teachers/{id}/edit` | Update teacher profile fields |

**Profile fields (based on DepEd SF7):**
- Personal: employee ID, sex, birthdate, contact, address
- Employment: position title, appointment status, date hired, teaching hours/week
- Education: highest degree, course, major, school graduated, year
- Credentials: PRC license number, PRC validity, eligibility, specialization
- Trainings: title, type, sponsor, dates, hours (separate table, CRUD)

---

### Module 7: Sections

**Who can access:** Admin, Registrar (full CRUD); Teacher (view own sections).

| Page | Route | What It Does |
|------|-------|-------------|
| Section List | `/sections` | Cards/table showing capacity indicators |
| Create Section | `/sections/create` | Name, grade level, strand, adviser, capacity |
| Section Detail | `/sections/{id}` | Class roster (student list) |
| Edit Section | `/sections/{id}/edit` | Update section details |

**Key features:**
- Capacity tracking: color-coded (green <80%, yellow 80-99%, red full)
- Adviser assignment (links to a teacher)
- Unique constraint: section name per semester + grade level

---

### Module 8: Enrollment (Core Module)

**Who can access:** Admin, Registrar.

| Page | Route | What It Does |
|------|-------|-------------|
| Enrollment List | `/enrollment` | All enrollments with search/filter/status |
| Enroll Student | `/enrollment/create` | **5-step enrollment wizard** |
| Enrollment Detail | `/enrollment/{id}` | View enrollment info, change status |
| Print Slip | `/enrollment/{id}/print` | PDF enrollment slip |

**The 5-Step Enrollment Wizard:**

| Step | What Happens |
|------|-------------|
| **1. Find Student** | Search by LRN or name (AJAX). Option to add new student if not found. |
| **2. Track & Strand** | Select track, strand auto-filters. Grade level auto-filled for returning students. |
| **3. Subjects** | Auto-loaded from subject-strand mapping. Prerequisites checked against student's grade history. Registrar can override warnings. |
| **4. Section** | Available sections shown with enrolled/capacity counts. Full sections disabled. |
| **5. Confirm** | Summary review. Option to print slip after saving. |

**Validation rules:**
- One enrollment per student per semester (unique constraint)
- Prerequisite check with override capability
- Section capacity check
- Enrollment period must be open

**API endpoints used by wizard:**
- `GET /api/students/search` — Step 1 autocomplete
- `POST /api/students/duplicate-check` — New student duplicate detection
- `GET /api/enrollment/subjects` — Step 3 subject load
- `POST /api/enrollment/prerequisites` — Step 3 prerequisite check
- `GET /api/enrollment/sections` — Step 4 available sections

---

### Module 9: Grades

**Who can access:** Admin, Registrar, Teacher (own sections only).

| Page | Route | What It Does |
|------|-------|-------------|
| Grade Index | `/grades` | Select section + subject |
| Grade Entry | `/grades/{section}/{subject}` | Batch grade entry form |

**How grading works:**
1. Teacher selects their section and a subject
2. All enrolled students shown in a table
3. Enter midterm and finals grades (0-100)
4. Final grade auto-calculates: `(midterm + finals) / 2`
5. Remarks auto-set: "Passed" if >= 75, "Failed" if < 75
6. Batch save (all rows at once)

**Grade locking:**
- Admin can lock grades after deadline (`PUT /grades/{section}/{subject}/lock`)
- Locked grades cannot be edited by anyone
- Only admin can unlock (`PUT /grades/{section}/{subject}/unlock`)

---

### Module 10: Reports & DepEd School Forms

**Who can access:** Admin, Registrar.

| Page | Route | What It Does |
|------|-------|-------------|
| Reports Hub | `/reports` | Menu of available reports |
| Enrollment Summary | `/reports/enrollment-summary` | Totals by track/strand/section with gender breakdown |
| Class List | `/reports/class-list` | Students in a selected section |
| Masterlist | `/reports/masterlist` | All enrolled students |
| Grade Summary | `/reports/grade-summary` | Per-section grade stats |
| School Forms | `/reports/school-forms` | DepEd form generator |

**DepEd School Forms:**

| Form | What It Generates | Export |
|------|------------------|--------|
| **SF1** (School Register) | Student list per section with personal info | Excel |
| **SF5** (Report on Promotion) | Per section: promoted, retained, transferred, dropped | Excel |
| **SF9** (Report Card) | Individual student: grades per subject per semester | PDF |
| **SF10** (Permanent Record) | Individual student: full academic history | PDF |

**Export endpoints:**
- `GET /reports/export/enrollment-summary` — Excel
- `GET /reports/export/class-list/{section}` — Excel
- `GET /reports/export/masterlist` — Excel
- `GET /reports/generate/sf1/{section}` — Excel
- `GET /reports/generate/sf5/{section}` — Excel
- `GET /reports/generate/sf9/{enrollment}` — PDF
- `GET /reports/generate/sf10/{student}` — PDF

---

### Module 11: Data Import

**Who can access:** Admin, Registrar.

| Page | Route | What It Does |
|------|-------|-------------|
| Import Hub | `/import` | Choose what to import |

**Import workflow:**
1. Download blank template (`GET /import/template/{type}`)
2. Fill in Excel/CSV with student or grade data
3. Upload file — system validates and shows preview
4. Valid rows in green, invalid in red with error messages
5. Confirm to import valid rows, skip invalid ones

**Endpoints:**
- `POST /import/students/upload` — Upload + validate
- `POST /import/students/confirm` — Import valid rows
- `POST /import/grades/upload` — Upload + validate
- `POST /import/grades/confirm` — Import valid rows

---

### Module 12: Student Portal

**Who can access:** Student only.

| Page | Route | What It Does |
|------|-------|-------------|
| My Profile | `/my/profile` | View own personal info, enrollment status |
| My Subjects | `/my/subjects` | View subjects for current semester |
| My Grades | `/my/grades` | View grades across all semesters |

Students cannot enroll themselves, edit their info, or view other students.

---

### Module 13: Dashboard

**Who can access:** All authenticated users (content varies by role).

| Route | What It Shows |
|-------|-------------|
| `/dashboard` | Stats cards, enrollment charts, section capacity, recent activity |

**Admin/Registrar see:** Total enrolled, total sections, enrollment by track chart, section capacity bars, recent enrollments.

**Teacher sees:** Their assigned sections and recent activity.

**Student sees:** Redirected to My Profile.

---

## Role Capability Matrix

| Feature | Admin | Registrar | Teacher | Student |
|---------|:-----:|:---------:|:-------:|:-------:|
| Manage Users | Yes | - | - | - |
| School Settings | Yes | - | - | - |
| School Years | Yes | - | - | - |
| Curriculum CRUD | Yes | Yes | - | - |
| Student CRUD | Yes | Yes | - | - |
| Teacher Profiles | Yes | Yes | - | - |
| Section CRUD | Yes | Yes | - | - |
| Enroll Students | Yes | Yes | - | - |
| Enter Grades | Yes | Yes | Own sections | - |
| Lock/Unlock Grades | Yes | - | - | - |
| View Reports | Yes | Yes | - | - |
| Generate DepEd Forms | Yes | Yes | - | - |
| Import Data | Yes | Yes | - | - |
| View Own Grades | Yes | Yes | Yes | Yes |
| Student Portal | - | - | - | Yes |

---

## Database Tables

| Table | Purpose |
|-------|---------|
| `users` | Staff and student accounts |
| `school_years` | Academic years (e.g., 2026-2027) |
| `semesters` | 1st/2nd semester per school year |
| `school_settings` | Key-value config (school name, passing grade, etc.) |
| `tracks` | SHS tracks (Academic, TVL, etc.) |
| `strands` | Specializations within tracks (STEM, ABM, ICT, etc.) |
| `subjects` | Individual courses with optional prerequisites |
| `subject_strand` | Maps subjects to strands per grade level + semester |
| `students` | Student personal records (LRN, name, guardian, SF1 fields) |
| `teacher_profiles` | Teacher SF7 data (employment, education, credentials) |
| `teacher_trainings` | Training/seminar records per teacher |
| `sections` | Class sections with strand, adviser, capacity |
| `enrollments` | Student enrollment per semester |
| `enrollment_subject` | Subjects taken per enrollment |
| `grades` | Midterm, finals, final grade per subject per enrollment |

---

## Testing Accounts

| Role | Email | Password |
|------|-------|----------|
| Admin | admin@lsnhs.edu.ph | password |

Additional test accounts are created via seeders.
