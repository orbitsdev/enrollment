# Feature List v2 — Lake Sebu NHS Enrollment System

> **Principle:** Every feature exists because the registrar, teacher, or admin needs it. If they don't use it weekly, it doesn't belong here.

---

## Module 1: Authentication & Users 🟢 Easy

| # | Feature | What It Does | Difficulty |
|---|---|---|---|
| 1.1 | Login / Logout | Email + password login, "remember me", logout | 🟢 Easy — Laravel Breeze gives this for free |
| 1.2 | User Accounts | Admin creates accounts for staff, edit profile, deactivate | 🟢 Easy — standard CRUD |
| 1.3 | 4 Roles | Admin, Registrar, Teacher, Student — assigned on account creation | 🟢 Easy — single `role` column on `users` table |
| 1.4 | Role-Based Access | Each role sees only their pages, Laravel Policies on every controller | 🟢 Easy — `Gate::define` + `$this->authorize()` |

**Pages:** `/login`, `/users`, `/users/create`, `/users/{id}/edit`, `/profile`

**Total: 4 features — all easy. Breeze does 70% of the work.**

---

## Module 2: School Year & Semester 🟢 Easy

| # | Feature | What It Does | Difficulty |
|---|---|---|---|
| 2.1 | School Year | Create "2026-2027", set one as active | 🟢 Easy — CRUD + `is_active` boolean |
| 2.2 | Semester | 1st / 2nd per school year, set active | 🟢 Easy — belongs to school year |
| 2.3 | Enrollment Period | Open/close toggle — when closed, enrollment form is disabled | 🟢 Easy — `enrollment_open` boolean on semester |

**Pages:** `/settings/school-year` (one page, manages all three)

**Total: 3 features — all easy. One settings page.**

---

## Module 3: Curriculum & Subjects 🟡 Medium

| # | Feature | What It Does | Difficulty |
|---|---|---|---|
| 3.1 | Tracks | List Academic, TVL, Sports, Arts & Design — enable/disable | 🟢 Easy — CRUD with `is_active` toggle |
| 3.2 | Strands | STEM, ABM, HUMSS, etc. — linked to parent track | 🟢 Easy — CRUD with `track_id` FK |
| 3.3 | Subjects | Code, name, type (core/specialized/applied), hours | 🟢 Easy — standard CRUD |
| 3.4 | Subject-Strand Mapping | Which subjects belong to which strand + grade level + semester | 🟡 Medium — pivot table `subject_strand` with grade_level and semester columns. This is the "subject load template" that auto-populates during enrollment |
| 3.5 | Prerequisites | Subject B requires Subject A (one-to-one, self-referencing FK) | 🟢 Easy — nullable `prerequisite_id` on subjects table |

**Pages:** `/curriculum/tracks`, `/curriculum/strands/{id}`, `/curriculum/subjects`, `/curriculum/subjects/create`, `/curriculum/subjects/{id}/edit`

**Total: 5 features. Medium only because the subject-strand mapping needs a clean UI for a 3-way relationship (subject × strand × grade level × semester). The data model itself is simple.**

---

## Module 4: Student Records 🟢 Easy

| # | Feature | What It Does | Difficulty |
|---|---|---|---|
| 4.1 | Add Student | Form: LRN, name, birthdate, gender, address, guardian info | 🟢 Easy — single form, one table insert |
| 4.2 | Search & Filter | Search by LRN or name, filter by grade level / strand / section / status | 🟢 Easy — query scopes + Inertia partial reload |
| 4.3 | Student Profile | One page with tabs: Personal Info, Enrollment History, Grades | 🟢 Easy — read-only display pulling from related tables |
| 4.4 | Edit Student | Update personal info, change status (with reason) | 🟢 Easy — same form as create, pre-filled |
| 4.5 | Duplicate Detection | Warning when name + birthdate matches existing record | 🟢 Easy — query on `onBlur` of name + birthdate fields |

**Pages:** `/students`, `/students/create`, `/students/{id}` (tabbed), `/students/{id}/edit`

**Total: 5 features — all easy. This is the "single source of truth" that replaces scattered Excel files.**

---

## Module 5: Sections 🟢 Easy

| # | Feature | What It Does | Difficulty |
|---|---|---|---|
| 5.1 | Create Section | Name, grade level, strand, school year, max capacity | 🟢 Easy — CRUD |
| 5.2 | Assign Adviser | Pick a teacher from dropdown | 🟢 Easy — `adviser_id` FK to users |
| 5.3 | Section List | All sections with enrolled count / capacity, color-coded | 🟢 Easy — `withCount('enrollments')` |
| 5.4 | Section Roster | Student list in a section — this IS the class list | 🟢 Easy — query enrollments where section_id = X |
| 5.5 | Export Class List | PDF or Excel download of roster | 🟢 Easy — DomPDF or Maatwebsite, one button |

**Pages:** `/sections`, `/sections/create`, `/sections/{id}`, `/sections/{id}/edit`

**Total: 5 features — all easy. The most satisfying module to build because it's pure CRUD with instant visible results.**

---

## Module 6: Enrollment 🟡 Medium (Core Module)

> This was 🔴 Hard in v1. It's actually 🟡 Medium when you strip away the over-engineering.

| # | Feature | What It Does | Real Difficulty |
|---|---|---|---|
| 6.1 | Enrollment Wizard | 5-step form (see breakdown below) | 🟡 Medium |
| 6.2 | Enrollment List | Table of all enrollments with search + filter | 🟢 Easy — same pattern as student list |
| 6.3 | Change Status | Enrolled → Dropped / Transferred, with reason | 🟢 Easy — status update + reason field |
| 6.4 | Print Enrollment Slip | Simple PDF with student info + section + subjects | 🟢 Easy — DomPDF template |

### The 5-Step Wizard Broken Down:

| Step | What It Does | How It Works | Difficulty |
|---|---|---|---|
| Step 1: Find Student | Search by LRN or name | Text input → AJAX search → select from results. "Not found? Add new" button opens student create form | 🟢 Easy |
| Step 2: Track & Strand | Two dependent dropdowns | Select Track → filters Strand dropdown. If returning student, pre-filled from last enrollment | 🟢 Easy |
| Step 3: Load Subjects | Auto-populate subject list | Query `subject_strand` WHERE strand + grade_level + semester. For each subject, check if `prerequisite_id` exists, if so check if student has passing grade in `grades` table | 🟡 Medium |
| Step 4: Pick Section | Dropdown of sections with capacity | Query sections WHERE strand + grade_level + semester, show `enrolled_count / max_capacity` | 🟢 Easy |
| Step 5: Confirm & Save | Summary → save | Insert into `enrollments` + `enrollment_subjects`. Print slip if checkbox checked | 🟢 Easy |

**The only medium part is Step 3** — and here's exactly what it does:

```
For each subject in the subject load:
  IF subject has a prerequisite:
    Check: does the student have a grade for that prerequisite?
      IF grade exists AND final_grade >= 75:
        ✅ Show subject as "OK"
      ELSE:
        ⚠️ Show warning — "Prerequisite not passed"
        Registrar can still include it (override checkbox + reason)
  ELSE:
    ✅ Show subject as "OK" (no prerequisite)
```

That's one query to `grades` table with a WHERE clause. Not hard.

**Validation rules (all simple checks):**

| Rule | Implementation |
|---|---|
| Student not already enrolled this semester | `WHERE student_id = X AND semester_id = Y` → if exists, block |
| Section not full | `enrollments.count WHERE section_id = X` < `sections.max_capacity` |
| Enrollment period is open | Check `semesters.enrollment_open` boolean |
| LRN is unique | Unique constraint on `students.lrn` column |
| Prerequisite passed | Query `grades` WHERE student + prerequisite subject, check ≥ 75 |

**Pages:** `/enrollment`, `/enrollment/create`, `/enrollment/{id}`

**Total: 4 features. The wizard is the only medium piece — and it's really just a multi-step form with one prerequisite query.**

---

## Module 7: Grades 🟡 Medium

| # | Feature | What It Does | Difficulty |
|---|---|---|---|
| 7.1 | Grade Entry | Teacher picks section + subject → sees student list → inputs midterm + finals per student | 🟡 Medium — batch form (many rows), but each row is just two number inputs |
| 7.2 | Auto-Compute | Final grade = (midterm + finals) / 2, Remarks = Passed if ≥ 75 | 🟢 Easy — computed on save, two lines of logic |
| 7.3 | Grade Viewing | Teachers see own sections, students see own grades, admin sees all | 🟢 Easy — scoped queries based on role |
| 7.4 | Grade Lock | Admin locks grades after deadline, only admin can unlock | 🟢 Easy — `locked` boolean on grades, check before allowing edit |

**Why 7.1 is medium:** It's a form with 30-50 rows (one per student), each with two inputs. You need to handle batch validation (all grades between 0-100), batch save, and show which rows have errors. It's not complex logic — it's just more UI work than a single-record form.

**Grade entry is basically a spreadsheet-style form:**

```
POST /grades/batch
{
  section_id: 5,
  subject_id: 12,
  grades: [
    { student_id: 1, midterm: 88, finals: 91 },
    { student_id: 2, midterm: 75, finals: 72 },
    { student_id: 3, midterm: 92, finals: 88 },
    ...
  ]
}
```

**Pages:** `/grades`, `/grades/{section}/{subject}`

**Total: 4 features. Medium because of the batch form UI, not because of complex logic.**

---

## Module 8: Reports & Dashboard 🟡 Medium

| # | Feature | What It Does | Difficulty |
|---|---|---|---|
| 8.1 | Dashboard | Enrollment count cards, bar chart by track, section capacity bars, recent enrollments list | 🟡 Medium — multiple queries + chart components, but each one is simple |
| 8.2 | Enrollment Summary | Total enrolled by track / strand / grade level / section, with filters + Excel export | 🟢 Easy — GROUP BY queries + Maatwebsite export |
| 8.3 | Class List | Select section → student list → export PDF/Excel | 🟢 Easy — one query + export |
| 8.4 | Student Masterlist | All enrolled students → export Excel | 🟢 Easy — one query + export |
| 8.5 | Grade Summary | Per section per subject: student names + grades + pass/fail count | 🟢 Easy — one query with aggregation |
| 8.6 | SF1 — School Register | Excel export matching DepEd template | 🟡 Medium — need to match DepEd's exact column layout |
| 8.7 | SF5 — Promotion Report | Excel export: promoted/retained/transferred per section | 🟡 Medium — same, exact template matching |
| 8.8 | SF9 — Report Card | PDF per student: grades per subject per semester | 🟡 Medium — PDF layout matching DepEd format |
| 8.9 | SF10 — Permanent Record | PDF per student: full academic history | 🟡 Medium — most complex report, pulls data across multiple school years |

**Why the DepEd forms are medium:** The queries are simple. The hard part is making the output **look exactly like DepEd's template** — specific column widths, merged cells, specific header text. It's tedious layout work, not complex logic.

**Pages:** `/dashboard`, `/reports`, `/reports/enrollment-summary`, `/reports/class-list`, `/reports/masterlist`, `/reports/grade-summary`, `/reports/school-forms`

**Total: 9 features. Dashboard is medium because it combines multiple data sources. DepEd forms are medium because of layout matching. Everything else is easy.**

---

## Module 9: Data Import 🟡 Medium

| # | Feature | What It Does | Difficulty |
|---|---|---|---|
| 9.1 | Student Import | Upload Excel/CSV → preview table → valid rows green, invalid rows red with error messages → import valid, skip invalid | 🟡 Medium — Maatwebsite import + validation + preview UI |
| 9.2 | Grade Import | Same flow for grades | 🟡 Medium — same pattern as above |
| 9.3 | Download Templates | Blank Excel files with correct headers + sample row | 🟢 Easy — static file download |

**Why medium:** The import itself is straightforward with Maatwebsite. The medium part is building the **preview screen** that shows validation results before committing — green rows, red rows with specific error messages. That's more UI work.

**Pages:** `/import`, `/import/students`, `/import/grades`

**Total: 3 features. The preview + validate + import pattern is reusable across both student and grade imports.**

---

## Module 10: System Settings 🟢 Easy

| # | Feature | What It Does | Difficulty |
|---|---|---|---|
| 10.1 | School Info | School name, ID, address, division, region — used in report headers | 🟢 Easy — key-value settings table |
| 10.2 | Grading Config | Passing grade (default 75), midterm/finals weight | 🟢 Easy — two settings |
| 10.3 | Enrollment Config | Default section capacity, prerequisite override allowed yes/no | 🟢 Easy — two settings |

**Pages:** `/settings` (single page with sections)

**Total: 3 features — all easy. One page, one form, saves to `school_settings` table.**

---

## Module 11: Graph Analytics (Capstone Only) 🟡 Medium

> Downgraded from 🔴 Hard to 🟡 Medium. It's medium because of the ONgDB learning curve, not because the logic is complex. The queries themselves are simple once you learn Cypher syntax.

| # | Feature | What It Does | Difficulty |
|---|---|---|---|
| 11.1 | Data Sync | When enrollment is saved, mirror nodes + relationships to ONgDB | 🟡 Medium — Laravel event listener that writes to ONgDB via HTTP API |
| 11.2 | Graph Queries | 3 Cypher queries: curriculum path, prerequisite chain, section load | 🟡 Medium — learning Cypher is the only new thing |
| 11.3 | Graph Visualization | One page with a D3.js graph diagram showing student → subject → strand relationships | 🟡 Medium — D3.js force-directed graph, feed it JSON from ONgDB query |

**Pages:** `/graph-analytics` (admin only, one page)

**Total: 3 features. This is an analytics overlay — remove it entirely and the system still works. It exists for the thesis defense.**

---

## Feature Count Summary v2

| Module | Features | v1 Difficulty | v2 Difficulty (Honest) | Why Changed |
|---|---|---|---|---|
| Authentication & Users | 4 | 🟢 Easy | 🟢 Easy | Same — Breeze handles it |
| School Year & Semester | 3 | 🟢 Easy | 🟢 Easy | Same |
| Curriculum & Subjects | 5 | 🟡 Medium | 🟡 Medium | Same — pivot table UI needs care |
| Student Records | 5 | 🟢 Easy | 🟢 Easy | Added duplicate detection, still easy |
| Sections | 5 | 🟢 Easy | 🟢 Easy | Added export, still easy |
| **Enrollment** | **4** | **🔴 Hard** | **🟡 Medium** | **Stripped graph traversals and auto-balancing. It's a 5-step form with one prerequisite check.** |
| Grades | 4 | 🟡 Medium | 🟡 Medium | Same — batch form is the only medium part |
| Reports & Dashboard | 9 | 🟡 Medium | 🟡 Medium | Expanded to list each DepEd form separately |
| Data Import | 3 | 🟡 Medium | 🟡 Medium | Same — preview UI is the work |
| System Settings | 3 | 🟢 Easy | 🟢 Easy | Same |
| Graph Analytics | 3 | 🔴 Hard | 🟡 Medium | It's 3 Cypher queries + one D3.js page, not a full graph engine |
| **TOTAL** | **48** | | | |

---

## Difficulty Distribution

```
🟢 Easy:    24 features  (50%)  — standard Laravel CRUD
🟡 Medium:  24 features  (50%)  — needs extra UI work or data logic
🔴 Hard:     0 features  ( 0%)  — nothing is actually hard when you don't over-engineer
```

---

## Build Priority

**Build easy modules first → gain momentum → tackle medium modules with confidence.**

| Priority | Module | Features | Why |
|---|---|---|---|
| 1st | Auth & Users | 4 | Foundation — everything needs login |
| 2nd | School Year & Semester | 3 | Global context for everything |
| 3rd | System Settings | 3 | Quick win, needed for reports later |
| 4th | Curriculum & Subjects | 5 | Must exist before enrollment |
| 5th | Student Records | 5 | Must exist before enrollment |
| 6th | Sections | 5 | Must exist before enrollment |
| 7th | **Enrollment** | **4** | **The core — now everything is ready for it** |
| 8th | Grades | 4 | Needed for reports and prerequisite checking |
| 9th | Reports & Dashboard | 9 | Consumes all the data from above |
| 10th | Data Import | 3 | For migrating existing records |
| 11th | Graph Analytics | 3 | Capstone layer — last because it's optional |
