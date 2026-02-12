# Architecture & Development Conventions

## Lake Sebu NHS — Enrollment & Student Records System

---

## 1. Tech Stack (Final)

### 1.1 Backend (Laravel)

| Package | Purpose | Install |
|---|---|---|
| **Laravel 11** | Framework | `laravel new lsnhs-enrollment` |
| **Laravel Breeze** | Auth scaffolding (Inertia + Vue) | `composer require laravel/breeze --dev` |
| **Spatie Laravel Permission** | Role & permission management (Admin, Registrar, Teacher, Student) | `composer require spatie/laravel-permission` |
| **Spatie Laravel MediaLibrary** | File uploads (student docs, import files) | `composer require spatie/laravel-medialibrary` |
| **Maatwebsite Laravel Excel** | Excel import/export (SF1, SF5, student import) | `composer require maatwebsite/excel` |
| **barryvdh/laravel-dompdf** | PDF generation (SF9, SF10, enrollment slips, class lists) | `composer require barryvdh/laravel-dompdf` |
| **Laravel IDE Helper** | Better IDE autocompletion for models, facades, etc. | `composer require barryvdh/laravel-ide-helper --dev` |
| **Laravel Debugbar** | Debug toolbar for development | `composer require barryvdh/laravel-debugbar --dev` |
| **Pest** | Testing framework | `composer require pestphp/pest --dev` |
| **Laravel Pint** | Code style fixer (PSR-12) | Ships with Laravel |

### 1.2 Frontend (Vue + Inertia)

| Package | Purpose | Install |
|---|---|---|
| **Vue 3** | Frontend framework (Composition API + `<script setup>`) | Ships with Breeze |
| **Inertia.js v2** | SPA bridge — no separate API | Ships with Breeze |
| **@inertiaui/modal-vue** | Route-based modals — `ModalLink` + `Modal` components | `npm i @inertiaui/modal-vue` |
| **shadcn-vue** | UI component library (Button, Input, Select, Dialog, Table, etc.) | `npx shadcn-vue@latest init` |
| **Tailwind CSS v4** | Utility-first CSS | Ships with Breeze |
| **vue-chartjs + chart.js** | Dashboard charts (enrollment by track, section capacity) | `npm i vue-chartjs chart.js` |
| **@vueuse/core** | Utility composables (useDebounce, useIntersectionObserver, etc.) | `npm i @vueuse/core` |
| **Vite** | Build tool + HMR | Ships with Laravel |

### 1.3 Not Using

| Removed | Why |
|---|---|
| ONgDB / Graph Database | Not needed — MySQL handles everything. Dropped from capstone scope. |
| Ziggy (Laravel routes in JS) | Inertia `route()` helper via Breeze is sufficient |
| Pinia | Inertia shared data + `usePage()` covers our state needs |

---

## 2. Folder & File Structure

```
lsnhs-enrollment/
├── app/
│   ├── Actions/                          # Single-purpose action classes
│   │   ├── Enrollment/
│   │   │   ├── EnrollStudent.php         # Main enrollment logic
│   │   │   ├── ValidatePrerequisites.php
│   │   │   └── AssignSection.php
│   │   ├── Grade/
│   │   │   ├── SaveBatchGrades.php
│   │   │   └── ComputeFinalGrade.php
│   │   ├── Import/
│   │   │   ├── ImportStudents.php
│   │   │   └── ImportGrades.php
│   │   └── Report/
│   │       ├── GenerateSF1.php
│   │       ├── GenerateSF5.php
│   │       ├── GenerateSF9.php
│   │       └── GenerateSF10.php
│   │
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Auth/                     # Breeze controllers (login, register, etc.)
│   │   │   ├── DashboardController.php
│   │   │   ├── StudentController.php
│   │   │   ├── EnrollmentController.php
│   │   │   ├── SectionController.php
│   │   │   ├── GradeController.php
│   │   │   ├── CurriculumController.php
│   │   │   ├── SubjectController.php
│   │   │   ├── ReportController.php
│   │   │   ├── ImportController.php
│   │   │   ├── SchoolYearController.php
│   │   │   ├── UserController.php
│   │   │   └── SettingController.php
│   │   │
│   │   ├── Requests/                     # Form Request validation
│   │   │   ├── Student/
│   │   │   │   ├── StoreStudentRequest.php
│   │   │   │   └── UpdateStudentRequest.php
│   │   │   ├── Enrollment/
│   │   │   │   └── StoreEnrollmentRequest.php
│   │   │   ├── Grade/
│   │   │   │   └── StoreBatchGradeRequest.php
│   │   │   ├── Section/
│   │   │   │   ├── StoreSectionRequest.php
│   │   │   │   └── UpdateSectionRequest.php
│   │   │   └── Subject/
│   │   │       ├── StoreSubjectRequest.php
│   │   │       └── UpdateSubjectRequest.php
│   │   │
│   │   └── Middleware/
│   │       └── HandleInertiaRequests.php  # Shared data (auth, roles, flash, settings)
│   │
│   ├── Models/
│   │   ├── User.php
│   │   ├── Student.php
│   │   ├── SchoolYear.php
│   │   ├── Semester.php
│   │   ├── Track.php
│   │   ├── Strand.php
│   │   ├── Subject.php
│   │   ├── Section.php
│   │   ├── Enrollment.php
│   │   ├── Grade.php
│   │   ├── AuditLog.php
│   │   └── SchoolSetting.php
│   │
│   ├── Traits/
│   │   └── Model/                        # Model relationship traits
│   │       ├── UserRelations.php
│   │       ├── UserScopes.php
│   │       ├── StudentRelations.php
│   │       ├── StudentScopes.php
│   │       ├── EnrollmentRelations.php
│   │       ├── EnrollmentScopes.php
│   │       ├── SectionRelations.php
│   │       ├── SectionScopes.php
│   │       ├── SubjectRelations.php
│   │       └── GradeScopes.php
│   │
│   ├── Enums/
│   │   ├── UserRole.php                  # admin, registrar, teacher, student
│   │   ├── EnrollmentStatus.php          # pending, enrolled, dropped, transferred
│   │   ├── StudentStatus.php             # active, transferred, dropped, graduated
│   │   ├── SubjectType.php               # core, specialized, applied
│   │   └── GradeRemarks.php              # passed, failed
│   │
│   ├── Exports/                          # Maatwebsite Excel exports
│   │   ├── SF1Export.php
│   │   ├── SF5Export.php
│   │   ├── ClassListExport.php
│   │   ├── StudentMasterlistExport.php
│   │   └── EnrollmentSummaryExport.php
│   │
│   ├── Imports/                          # Maatwebsite Excel imports
│   │   ├── StudentImport.php
│   │   └── GradeImport.php
│   │
│   ├── Observers/
│   │   └── AuditObserver.php             # Auto-log create/update/delete on models
│   │
│   └── Policies/
│       ├── StudentPolicy.php
│       ├── EnrollmentPolicy.php
│       ├── SectionPolicy.php
│       ├── GradePolicy.php
│       └── UserPolicy.php
│
├── resources/
│   └── js/
│       ├── app.js                        # Inertia app setup + modal plugin
│       ├── types/                        # TypeScript-like prop type definitions
│       │   └── index.d.ts
│       │
│       ├── Components/                   # Reusable Vue components
│       │   ├── ui/                       # shadcn-vue components (auto-generated)
│       │   │   ├── button/
│       │   │   ├── input/
│       │   │   ├── select/
│       │   │   ├── dialog/
│       │   │   ├── table/
│       │   │   ├── card/
│       │   │   ├── badge/
│       │   │   ├── skeleton/
│       │   │   ├── tabs/
│       │   │   ├── dropdown-menu/
│       │   │   └── ...
│       │   │
│       │   ├── App/                      # App-level shared components
│       │   │   ├── DataTable.vue         # Reusable filterable table
│       │   │   ├── SearchInput.vue       # Debounced search with Inertia reload
│       │   │   ├── StatusBadge.vue       # Colored badge for status fields
│       │   │   ├── CapacityBar.vue       # Section capacity progress bar
│       │   │   ├── StatCard.vue          # Dashboard stat card (number + label)
│       │   │   ├── PageHeader.vue        # Page title + breadcrumb + action button
│       │   │   ├── ConfirmDialog.vue     # "Are you sure?" confirmation
│       │   │   ├── EmptyState.vue        # "No data found" placeholder
│       │   │   ├── FormSection.vue       # Form card wrapper with title
│       │   │   ├── FilterBar.vue         # Dropdown filters row
│       │   │   └── ExportButton.vue      # PDF/Excel export dropdown
│       │   │
│       │   └── Charts/
│       │       ├── EnrollmentByTrackChart.vue
│       │       └── SectionCapacityChart.vue
│       │
│       ├── Layouts/
│       │   ├── AuthenticatedLayout.vue   # Main layout (sidebar + navbar + content)
│       │   ├── GuestLayout.vue           # Login page layout
│       │   └── Partials/
│       │       ├── Sidebar.vue           # Navigation sidebar with prefetched links
│       │       ├── Navbar.vue            # Top bar with user menu
│       │       └── FlashMessage.vue      # Success/error toast notifications
│       │
│       └── Pages/                        # Inertia pages (one per route)
│           ├── Auth/
│           │   ├── Login.vue
│           │   └── ...
│           ├── Dashboard.vue
│           ├── Students/
│           │   ├── Index.vue             # Student list
│           │   ├── Create.vue            # Add student (modal or full page)
│           │   ├── Show.vue              # Student profile (tabbed)
│           │   └── Edit.vue              # Edit student (modal or full page)
│           ├── Enrollment/
│           │   ├── Index.vue             # Enrollment list
│           │   ├── Create.vue            # 5-step enrollment wizard (full page)
│           │   └── Show.vue              # Enrollment detail
│           ├── Sections/
│           │   ├── Index.vue             # Section list
│           │   ├── Create.vue            # Add section (modal)
│           │   ├── Show.vue              # Section roster
│           │   └── Edit.vue              # Edit section (modal)
│           ├── Grades/
│           │   ├── Index.vue             # Select section + subject
│           │   └── Entry.vue             # Grade input table
│           ├── Reports/
│           │   ├── Index.vue             # Report menu
│           │   ├── EnrollmentSummary.vue
│           │   ├── ClassList.vue
│           │   ├── Masterlist.vue
│           │   ├── GradeSummary.vue
│           │   └── SchoolForms.vue
│           ├── Curriculum/
│           │   ├── Tracks.vue            # Tracks + strands management
│           │   ├── Subjects/
│           │   │   ├── Index.vue
│           │   │   ├── Create.vue        # (modal)
│           │   │   └── Edit.vue          # (modal)
│           ├── Import/
│           │   ├── Index.vue
│           │   ├── Students.vue
│           │   └── Grades.vue
│           ├── Settings/
│           │   ├── Index.vue             # System settings
│           │   └── SchoolYear.vue        # School year & semester management
│           └── Users/
│               ├── Index.vue
│               ├── Create.vue            # (modal)
│               └── Edit.vue              # (modal)
│
├── database/
│   ├── migrations/                       # All table migrations
│   ├── seeders/
│   │   ├── DatabaseSeeder.php
│   │   ├── RoleSeeder.php               # Admin, Registrar, Teacher, Student
│   │   ├── TrackStrandSeeder.php        # Academic + TVL tracks with strands
│   │   ├── SubjectSeeder.php            # Sample subjects per strand
│   │   ├── SchoolSettingSeeder.php      # Default settings
│   │   └── DemoDataSeeder.php           # Fake students, sections, enrollments for dev
│   └── factories/
│       ├── StudentFactory.php
│       ├── EnrollmentFactory.php
│       └── ...
│
├── routes/
│   └── web.php                           # All routes (grouped by module with middleware)
│
└── tests/
    ├── Feature/
    │   ├── Enrollment/
    │   │   ├── EnrollStudentTest.php
    │   │   └── PrerequisiteCheckTest.php
    │   ├── Grade/
    │   │   └── BatchGradeTest.php
    │   └── ...
    └── Unit/
        ├── Actions/
        │   └── ComputeFinalGradeTest.php
        └── ...
```

---

## 3. Coding Conventions

### 3.1 Model Traits — Keep Models Clean

Models should be slim. Move relationships and scopes into traits.

**Example: User Model**

```php
// app/Models/User.php
class User extends Authenticatable
{
    use HasFactory, Notifiable;
    use HasRoles;                    // Spatie Permission
    use UserRelations;               // app/Traits/Model/UserRelations.php
    use UserScopes;                  // app/Traits/Model/UserScopes.php

    protected $fillable = ['name', 'email', 'password'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
```

```php
// app/Traits/Model/UserRelations.php
namespace App\Traits\Model;

use App\Models\Section;

trait UserRelations
{
    public function advisedSections()
    {
        return $this->hasMany(Section::class, 'adviser_id');
    }

    public function student()
    {
        return $this->belongsTo(Student::class, 'linked_id');
    }
}
```

```php
// app/Traits/Model/UserScopes.php
namespace App\Traits\Model;

trait UserScopes
{
    public function scopeTeachers($query)
    {
        return $query->role('teacher');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
```

**Example: Student Model**

```php
// app/Models/Student.php
class Student extends Model
{
    use HasFactory;
    use StudentRelations;
    use StudentScopes;

    protected $fillable = [
        'lrn', 'first_name', 'middle_name', 'last_name', 'suffix',
        'birthdate', 'gender', 'barangay', 'municipality', 'province',
        'contact', 'guardian_name', 'guardian_relationship',
        'guardian_contact', 'previous_school', 'status',
    ];

    protected function casts(): array
    {
        return [
            'birthdate' => 'date',
            'status' => StudentStatus::class,
        ];
    }

    // Accessor — computed, not stored
    public function getFullNameAttribute(): string
    {
        return trim("{$this->last_name}, {$this->first_name} {$this->middle_name}");
    }
}
```

```php
// app/Traits/Model/StudentRelations.php
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

    public function grades()
    {
        return $this->hasManyThrough(Grade::class, Enrollment::class);
    }
}
```

```php
// app/Traits/Model/StudentScopes.php
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

    public function scopeByStrand($query, ?int $strandId)
    {
        return $query->when($strandId, fn ($q) =>
            $q->whereHas('currentEnrollment.section', fn ($s) =>
                $s->where('strand_id', $strandId)
            )
        );
    }

    public function scopeActive($query)
    {
        return $query->where('status', StudentStatus::Active);
    }
}
```

### 3.2 Action Classes — One Job, One Class

Complex business logic goes in Action classes, NOT in controllers. Controllers stay thin.

```php
// app/Actions/Enrollment/EnrollStudent.php
namespace App\Actions\Enrollment;

use App\Models\Student;
use App\Models\Enrollment;

class EnrollStudent
{
    public function execute(array $data): Enrollment
    {
        $enrollment = Enrollment::create([
            'student_id' => $data['student_id'],
            'section_id' => $data['section_id'],
            'semester_id' => $data['semester_id'],
            'status' => EnrollmentStatus::Enrolled,
            'enrolled_at' => now(),
        ]);

        // Attach subjects
        $enrollment->subjects()->attach($data['subject_ids']);

        return $enrollment;
    }
}
```

```php
// Controller stays thin
class EnrollmentController extends Controller
{
    public function store(StoreEnrollmentRequest $request, EnrollStudent $action)
    {
        $enrollment = $action->execute($request->validated());

        return redirect()->route('enrollment.show', $enrollment)
            ->with('success', 'Student enrolled successfully.');
    }
}
```

### 3.3 Controller Pattern — Consistent Across All Modules

Every resource controller follows the same pattern:

```php
class StudentController extends Controller
{
    public function index(Request $request)
    {
        return Inertia::render('Students/Index', [
            'students' => fn () => Student::query()
                ->search($request->search)
                ->byStrand($request->strand_id)
                ->active()
                ->paginate(25)
                ->withQueryString(),
            'strands' => fn () => Strand::with('track')->get(),
            'filters' => $request->only(['search', 'strand_id', 'status']),
        ]);
    }

    public function create()
    {
        return Inertia::render('Students/Create');
    }

    public function store(StoreStudentRequest $request)
    {
        Student::create($request->validated());

        return redirect()->route('students.index')
            ->with('success', 'Student added.');
    }

    public function show(Student $student)
    {
        return Inertia::render('Students/Show', [
            'student' => $student->load('currentEnrollment.section.strand'),
            'enrollments' => Inertia::optional(fn () => $student->enrollments()
                ->with('section.strand', 'semester.schoolYear')
                ->latest()
                ->get()),
            'grades' => Inertia::optional(fn () => $student->grades()
                ->with('subject', 'enrollment.semester')
                ->get()),
        ]);
    }
}
```

### 3.4 Enums — Type Safety

```php
// app/Enums/EnrollmentStatus.php
namespace App\Enums;

enum EnrollmentStatus: string
{
    case Pending = 'pending';
    case Enrolled = 'enrolled';
    case Dropped = 'dropped';
    case Transferred = 'transferred';

    public function label(): string
    {
        return match($this) {
            self::Pending => 'Pending',
            self::Enrolled => 'Enrolled',
            self::Dropped => 'Dropped',
            self::Transferred => 'Transferred',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::Pending => 'yellow',
            self::Enrolled => 'green',
            self::Dropped => 'red',
            self::Transferred => 'gray',
        };
    }
}
```

### 3.5 Form Requests — All Validation Server-Side

```php
// app/Http/Requests/Student/StoreStudentRequest.php
class StoreStudentRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'lrn' => ['required', 'string', 'max:12', 'unique:students,lrn'],
            'first_name' => ['required', 'string', 'max:100'],
            'middle_name' => ['nullable', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'birthdate' => ['required', 'date', 'before:today'],
            'gender' => ['required', 'in:male,female'],
            'barangay' => ['required', 'string'],
            'contact' => ['nullable', 'string', 'max:20'],
            'guardian_name' => ['required', 'string', 'max:200'],
            'guardian_relationship' => ['required', 'string'],
            'guardian_contact' => ['required', 'string', 'max:20'],
        ];
    }
}
```

---

## 4. Modal vs Full Page — Decision Guide

Using `@inertiaui/modal-vue` — the `ModalLink` component opens any Inertia route as a modal without changing controller logic.

### 4.1 When to Use Modal

| Use Modal When | Examples |
|---|---|
| Simple CRUD forms (1-5 fields) | Create/edit section, create/edit user, create subject |
| Quick actions from a list | Change enrollment status, add track/strand |
| Confirmations | Delete confirmation, grade lock confirmation |
| Detail previews from a list | Quick view student info without leaving the list page |

### 4.2 When to Use Full Page

| Use Full Page When | Examples |
|---|---|
| Complex multi-step forms | Enrollment wizard (5 steps) |
| Data-heavy pages with tabs | Student profile (Info + History + Grades tabs) |
| Pages with tables/lists | Student list, enrollment list, grade entry |
| Pages with charts/dashboards | Dashboard, enrollment summary report |

### 4.3 Modal Implementation

**On the list page (parent):**
```vue
<script setup>
import { ModalLink } from '@inertiaui/modal-vue'
</script>

<template>
  <!-- Opens /sections/create as a modal overlay -->
  <ModalLink href="/sections/create">
    <Button>+ Add Section</Button>
  </ModalLink>

  <!-- Opens /sections/5/edit as a modal -->
  <ModalLink :href="`/sections/${section.id}/edit`">
    Edit
  </ModalLink>
</template>
```

**The modal page itself:**
```vue
<!-- Pages/Sections/Create.vue -->
<script setup>
import { Modal } from '@inertiaui/modal-vue'
import { useForm } from '@inertiajs/vue3'

const form = useForm('CreateSection', {
  name: '', strand_id: null, grade_level: 11, max_capacity: 50, adviser_id: null,
})

const submit = () => {
  form.post('/sections', {
    preserveScroll: true,
    onSuccess: () => form.reset(),
  })
}
</script>

<template>
  <!-- Works as modal when opened via ModalLink, works as full page when visited directly -->
  <Modal>
    <form @submit.prevent="submit">
      <h2 class="text-lg font-semibold">Create Section</h2>
      <!-- form fields -->
      <Button type="submit" :disabled="form.processing">
        {{ form.processing ? 'Saving...' : 'Create Section' }}
      </Button>
    </form>
  </Modal>
</template>
```

**Key benefit:** The same page component works both as a modal AND as a full page. If someone visits `/sections/create` directly (e.g., via URL), it renders as a full page. If opened via `ModalLink`, it renders inside a modal. Zero controller changes needed.

### 4.4 Our System — Modal vs Full Page Map

| Page | Type | Why |
|---|---|---|
| `/students` | Full Page | Table with search + filters |
| `/students/create` | **Modal** | Simple form, quick add from list |
| `/students/{id}` | Full Page | Tabbed profile with heavy data |
| `/students/{id}/edit` | **Modal** | Same form as create, pre-filled |
| `/enrollment` | Full Page | Table with search + filters |
| `/enrollment/create` | **Full Page** | 5-step wizard — too complex for modal |
| `/enrollment/{id}` | **Modal** | Quick view enrollment detail |
| `/sections` | Full Page | Section cards with capacity |
| `/sections/create` | **Modal** | Simple form |
| `/sections/{id}` | Full Page | Roster table |
| `/sections/{id}/edit` | **Modal** | Simple form |
| `/grades` | Full Page | Select section + subject |
| `/grades/{section}/{subject}` | Full Page | Batch input table (30-50 rows) |
| `/curriculum/subjects/create` | **Modal** | Simple form |
| `/curriculum/subjects/{id}/edit` | **Modal** | Simple form |
| `/users/create` | **Modal** | Simple form |
| `/users/{id}/edit` | **Modal** | Simple form |
| `/reports/*` | Full Page | Reports with charts + export |
| `/settings` | Full Page | Multiple settings sections |

---

## 5. Vue Transitions & Animations

### 5.1 Page Transitions (Inertia)

Smooth fade transition between pages:

```vue
<!-- Layouts/AuthenticatedLayout.vue -->
<script setup>
import { Transition } from 'vue'
</script>

<template>
  <div class="min-h-screen flex">
    <Sidebar />
    <main class="flex-1">
      <Navbar />
      <Transition
        name="page"
        mode="out-in"
        appear
      >
        <div :key="$page.url" class="p-6">
          <slot />
        </div>
      </Transition>
    </main>
  </div>
</template>

<style>
.page-enter-active,
.page-leave-active {
  transition: opacity 0.15s ease, transform 0.15s ease;
}
.page-enter-from {
  opacity: 0;
  transform: translateY(4px);
}
.page-leave-to {
  opacity: 0;
  transform: translateY(-4px);
}
</style>
```

### 5.2 List Animations (TransitionGroup)

Smooth add/remove for student lists, enrollment rows:

```vue
<TransitionGroup name="list" tag="div">
  <div v-for="student in students.data" :key="student.id">
    <StudentRow :student="student" />
  </div>
</TransitionGroup>

<style>
.list-enter-active,
.list-leave-active {
  transition: all 0.2s ease;
}
.list-enter-from,
.list-leave-to {
  opacity: 0;
  transform: translateX(-10px);
}
.list-move {
  transition: transform 0.2s ease;
}
</style>
```

### 5.3 Flash Message Animation

Toast notifications for success/error feedback:

```vue
<!-- Layouts/Partials/FlashMessage.vue -->
<script setup>
import { usePage } from '@inertiajs/vue3'
import { computed, ref, watch } from 'vue'

const flash = computed(() => usePage().props.flash)
const show = ref(false)

watch(() => flash.value?.success, (val) => {
  if (val) {
    show.value = true
    setTimeout(() => show.value = false, 3000)
  }
})
</script>

<template>
  <Transition name="toast">
    <div v-if="show && flash?.success"
      class="fixed top-4 right-4 z-50 bg-green-500 text-white px-4 py-3 rounded-lg shadow-lg">
      {{ flash.success }}
    </div>
  </Transition>
</template>

<style>
.toast-enter-active { transition: all 0.3s ease; }
.toast-leave-active { transition: all 0.3s ease; }
.toast-enter-from { opacity: 0; transform: translateY(-10px) translateX(10px); }
.toast-leave-to { opacity: 0; transform: translateY(-10px) translateX(10px); }
</style>
```

### 5.4 Skeleton Loading (Deferred Props)

```vue
<!-- Components/App/SkeletonChart.vue -->
<template>
  <div class="animate-pulse">
    <div class="h-4 bg-gray-200 rounded w-1/3 mb-4"></div>
    <div class="h-48 bg-gray-200 rounded"></div>
  </div>
</template>
```

### 5.5 Animation Rules

| Where | Animation | Duration |
|---|---|---|
| Page transitions | Fade + slight translateY | 150ms |
| List items (add/remove) | Fade + translateX | 200ms |
| Modals (open/close) | Handled by `@inertiaui/modal-vue` | Default |
| Flash messages | Slide in from top-right | 300ms in, 3s visible, 300ms out |
| Skeleton loaders | Tailwind `animate-pulse` | Continuous |
| Button loading | `:disabled` + text change | Instant |
| Dropdown menus | Handled by shadcn-vue | Default |

**Rule: Keep animations under 300ms. Users at Lake Sebu are on slow connections — animations should feel snappy, not sluggish.**

---

## 6. Reusable Component List

### 6.1 shadcn-vue Components to Install

```bash
# Core UI components we'll use across the system
npx shadcn-vue@latest add button
npx shadcn-vue@latest add input
npx shadcn-vue@latest add label
npx shadcn-vue@latest add select
npx shadcn-vue@latest add textarea
npx shadcn-vue@latest add checkbox
npx shadcn-vue@latest add radio-group
npx shadcn-vue@latest add dialog
npx shadcn-vue@latest add card
npx shadcn-vue@latest add table
npx shadcn-vue@latest add badge
npx shadcn-vue@latest add tabs
npx shadcn-vue@latest add dropdown-menu
npx shadcn-vue@latest add separator
npx shadcn-vue@latest add skeleton
npx shadcn-vue@latest add toast
npx shadcn-vue@latest add alert
npx shadcn-vue@latest add progress
npx shadcn-vue@latest add avatar
npx shadcn-vue@latest add tooltip
npx shadcn-vue@latest add switch
npx shadcn-vue@latest add popover
npx shadcn-vue@latest add command          # For search/combobox
npx shadcn-vue@latest add pagination
```

### 6.2 Custom App Components (We Build These)

| Component | Used In | Purpose |
|---|---|---|
| `PageHeader.vue` | Every page | Page title + optional action button |
| `DataTable.vue` | Students, Enrollments, Sections | Reusable table with sorting, search prop |
| `SearchInput.vue` | Any list page | Debounced search that triggers `router.reload` |
| `FilterBar.vue` | Students, Enrollments | Row of dropdown filters |
| `StatusBadge.vue` | Students, Enrollments | Colored badge (green/yellow/red) for status |
| `CapacityBar.vue` | Sections | Progress bar showing 38/50 with color |
| `StatCard.vue` | Dashboard | Big number + label card |
| `FormSection.vue` | Any form | Card wrapper with title for form sections |
| `ConfirmDialog.vue` | Delete actions, status changes | "Are you sure?" dialog |
| `EmptyState.vue` | Any list when empty | "No students found" placeholder |
| `ExportButton.vue` | Reports, section roster | Dropdown with PDF / Excel options |
| `SkeletonTable.vue` | Deferred table props | Pulsing placeholder table |
| `SkeletonChart.vue` | Dashboard deferred charts | Pulsing placeholder chart |
| `StepIndicator.vue` | Enrollment wizard | Step 1 → 2 → 3 → 4 → 5 indicator |

---

## 7. Route Structure

```php
// routes/web.php

Route::middleware('guest')->group(function () {
    Route::get('login', [AuthController::class, 'create'])->name('login');
    Route::post('login', [AuthController::class, 'store']);
});

Route::middleware('auth')->group(function () {
    Route::post('logout', [AuthController::class, 'destroy'])->name('logout');
    Route::get('profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('profile', [ProfileController::class, 'update'])->name('profile.update');

    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Students
    Route::resource('students', StudentController::class);

    // Enrollment
    Route::resource('enrollment', EnrollmentController::class);
    Route::patch('enrollment/{enrollment}/status', [EnrollmentController::class, 'updateStatus'])
        ->name('enrollment.status');
    Route::get('enrollment/{enrollment}/slip', [EnrollmentController::class, 'printSlip'])
        ->name('enrollment.slip');

    // Sections
    Route::resource('sections', SectionController::class);
    Route::get('sections/{section}/export', [SectionController::class, 'export'])
        ->name('sections.export');

    // Grades
    Route::get('grades', [GradeController::class, 'index'])->name('grades.index');
    Route::get('grades/{section}/{subject}', [GradeController::class, 'entry'])
        ->name('grades.entry');
    Route::post('grades/{section}/{subject}', [GradeController::class, 'store'])
        ->name('grades.store');

    // Reports
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::get('enrollment-summary', [ReportController::class, 'enrollmentSummary'])->name('enrollment-summary');
        Route::get('class-list', [ReportController::class, 'classList'])->name('class-list');
        Route::get('masterlist', [ReportController::class, 'masterlist'])->name('masterlist');
        Route::get('grade-summary', [ReportController::class, 'gradeSummary'])->name('grade-summary');
        Route::get('school-forms', [ReportController::class, 'schoolForms'])->name('school-forms');
        Route::get('school-forms/{type}/generate', [ReportController::class, 'generate'])->name('school-forms.generate');
    });

    // Admin-only routes
    Route::middleware('role:admin')->group(function () {
        // Curriculum
        Route::resource('curriculum/tracks', TrackController::class)->only(['index', 'store', 'update']);
        Route::resource('curriculum/strands', StrandController::class)->only(['store', 'update']);
        Route::resource('curriculum/subjects', SubjectController::class);

        // Users
        Route::resource('users', UserController::class)->except('show');

        // Settings
        Route::get('settings', [SettingController::class, 'index'])->name('settings.index');
        Route::patch('settings', [SettingController::class, 'update'])->name('settings.update');

        // School Year
        Route::resource('settings/school-years', SchoolYearController::class)->only(['index', 'store', 'update']);
        Route::patch('settings/semesters/{semester}/toggle', [SchoolYearController::class, 'toggleEnrollment'])
            ->name('semesters.toggle');

        // Import
        Route::get('import', [ImportController::class, 'index'])->name('import.index');
        Route::get('import/students', [ImportController::class, 'students'])->name('import.students');
        Route::post('import/students', [ImportController::class, 'importStudents'])->name('import.students.store');
        Route::get('import/grades', [ImportController::class, 'grades'])->name('import.grades');
        Route::post('import/grades', [ImportController::class, 'importGrades'])->name('import.grades.store');
        Route::get('import/template/{type}', [ImportController::class, 'downloadTemplate'])->name('import.template');
    });
});
```

---

## 8. Code Quality Rules

### 8.1 PHP Rules

| Rule | Standard |
|---|---|
| Code style | Laravel Pint (PSR-12) — run before every commit |
| Max controller method | 15 lines — if longer, extract to Action class |
| Max model file | ~50 lines — use Traits for relations and scopes |
| Validation | Always in Form Request classes, never inline in controllers |
| Business logic | In Action classes (`app/Actions/`), never in controllers |
| Database queries | Use Eloquent scopes, never raw queries in controllers |
| Enums | Use PHP 8.1 Enums for all status/type fields |
| Return types | Always declare return types on methods |
| Naming | Controllers: singular (StudentController), Models: singular (Student), Tables: plural (students) |

### 8.2 Vue Rules

| Rule | Standard |
|---|---|
| Script style | Always `<script setup>` (Composition API) |
| Component size | Max ~100 lines template — extract sub-components if larger |
| Props | Define with `defineProps` + types |
| Forms | Always `useForm()` — never raw `ref()` + `router.post()` |
| State | Props from Inertia controllers; `usePage()` for shared data; local `ref()` for UI state only |
| Naming | Pages: PascalCase folders + files (`Students/Index.vue`), Components: PascalCase (`DataTable.vue`) |
| Imports | shadcn-vue components imported per-file, not globally |

### 8.3 Git Commit Convention

```
feat(enrollment): add 5-step enrollment wizard
fix(grades): fix batch grade computation
refactor(students): extract search scope to trait
style: run Laravel Pint
chore: add IDE helper config
docs: update README with setup instructions
```

---

## 9. Seeder Plan

### 9.1 Required Seeders (Always Run)

| Seeder | Data |
|---|---|
| `RoleSeeder` | 4 roles: admin, registrar, teacher, student |
| `TrackStrandSeeder` | Academic (STEM, ABM, HUMSS, GAS), TVL (HE, ICT) |
| `SchoolSettingSeeder` | School name, passing grade (75), section capacity (50) |
| `AdminUserSeeder` | Default admin account |

### 9.2 Dev/Demo Seeders (Development Only)

| Seeder | Data |
|---|---|
| `DemoDataSeeder` | 5 teachers, 200 students, 8 sections, 150 enrollments, sample grades |
| `SubjectSeeder` | 30+ subjects mapped to strands with prerequisites |

Run in dev: `php artisan db:seed`
Run in production: `php artisan db:seed --class=RoleSeeder` (only essentials)

---

## 10. Environment Setup Commands

```bash
# Initial project setup
composer create-project laravel/laravel lsnhs-enrollment
cd lsnhs-enrollment

# Install Breeze with Inertia + Vue
composer require laravel/breeze --dev
php artisan breeze:install vue --ssr

# Install PHP packages
composer require spatie/laravel-permission
composer require spatie/laravel-medialibrary
composer require maatwebsite/excel
composer require barryvdh/laravel-dompdf
composer require barryvdh/laravel-ide-helper --dev
composer require barryvdh/laravel-debugbar --dev
composer require pestphp/pest --dev

# Publish configs
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
php artisan vendor:publish --provider="Spatie\MediaLibrary\MediaLibraryServiceProvider"

# Install frontend packages
npm i @inertiaui/modal-vue
npm i vue-chartjs chart.js
npm i @vueuse/core

# Install shadcn-vue
npx shadcn-vue@latest init

# Generate IDE helper
php artisan ide-helper:generate
php artisan ide-helper:models --nowrite
php artisan ide-helper:meta

# Run migrations and seeders
php artisan migrate
php artisan db:seed

# Start development
npm run dev
php artisan serve
```
