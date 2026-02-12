# Role Guide

## Roles

The system uses 4 roles defined in `App\Enums\UserRole`:

| Role | Slug | Description |
|------|------|-------------|
| Admin | `admin` | Full system access |
| Registrar | `registrar` | Academic management and reporting |
| Teacher | `teacher` | Grade entry for assigned sections |
| Student | `student` | Read-only portal for own records |

## Test Credentials

All test accounts use the password: **`password`**

| Role | Email | Name |
|------|-------|------|
| Admin | `admin@lsnhs.edu.ph` | Admin |
| Registrar | `registrar@school.edu.ph` | Elena Bautista |
| Teacher | `maria.santos@school.edu.ph` | Maria Santos |
| Teacher | `jose.reyes@school.edu.ph` | Jose Reyes |
| Teacher | `ana.garcia@school.edu.ph` | Ana Garcia |
| Teacher | `carlos.mendoza@school.edu.ph` | Carlos Mendoza |
| Teacher | `rosa.rivera@school.edu.ph` | Rosa Rivera |
| Student | _(no login — portal access via student role)_ | — |

## Page Access by Role

### Admin (Full Access)

- **Dashboard** `/dashboard`
- **Management**
  - Users `/users`
  - School Settings `/school-settings`
- **Academic**
  - Curriculum (Tracks) `/curriculum/tracks`
  - Subjects `/curriculum/subjects`
  - Students `/students`
  - Sections `/sections`
  - Enrollment `/enrollment`
- **Grades** `/grades`
- **Reports**
  - Enrollment Summary `/reports/enrollment-summary`
  - Class List `/reports/class-list`
  - Masterlist `/reports/masterlist`
  - Grade Summary `/reports/grade-summary`
  - School Forms `/reports/school-forms`
- **Import** `/import`

### Registrar

Same as Admin **except**:
- No access to Users management
- No access to School Settings

### Teacher

- **Dashboard** `/dashboard`
- **Grades** `/grades` (only their assigned sections)

### Student

- **Dashboard** `/dashboard`
- **My Profile** `/my/profile`
- **My Subjects** `/my/subjects`
- **My Grades** `/my/grades`

## Route Middleware

| Route Group | Middleware | Roles |
|-------------|-----------|-------|
| Dashboard | `auth, verified` | All |
| Admin Management | `role:admin` | Admin |
| Academic | `role:admin\|registrar` | Admin, Registrar |
| Grades | `role:admin\|registrar\|teacher` | Admin, Registrar, Teacher |
| Reports & Import | `role:admin\|registrar` | Admin, Registrar |
| Student Portal | `role:student` | Student |

## Key Files

- Role enum: `app/Enums/UserRole.php`
- Navigation config: `app/Helpers/NavigationHelper.php`
- Route definitions: `routes/web.php`
- Seeder (admin): `database/seeders/AdminUserSeeder.php`
- Seeder (demo data): `database/seeders/DemoDataSeeder.php`
