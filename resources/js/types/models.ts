export interface Student {
    id: number;
    lrn: string;
    last_name: string;
    first_name: string;
    middle_name: string | null;
    suffix: string | null;
    birthdate: string;
    gender: 'male' | 'female';
    religion: string | null;
    learning_modality: string;
    address: string | null;
    contact_number: string | null;
    father_name: string | null;
    mother_name: string | null;
    guardian_name: string | null;
    guardian_contact: string | null;
    guardian_relationship: string | null;
    previous_school: string | null;
    status: 'active' | 'transferred' | 'dropped' | 'graduated';
    created_at: string;
    updated_at: string;
    full_name?: string;
    enrollments?: Enrollment[];
    grades?: Grade[];
}

export interface SchoolYear {
    id: number;
    name: string;
    is_active: boolean;
    created_at: string;
    updated_at: string;
    semesters?: Semester[];
}

export interface Semester {
    id: number;
    school_year_id: number;
    number: 1 | 2;
    is_active: boolean;
    enrollment_open: boolean;
    created_at: string;
    updated_at: string;
    school_year?: SchoolYear;
    label?: string;
}

export interface Track {
    id: number;
    name: string;
    code: string;
    is_active: boolean;
    sort_order: number;
    created_at: string;
    updated_at: string;
    strands?: Strand[];
}

export interface Strand {
    id: number;
    track_id: number;
    name: string;
    code: string;
    course: string | null;
    is_active: boolean;
    sort_order: number;
    created_at: string;
    updated_at: string;
    track?: Track;
    subjects?: Subject[];
}

export interface Subject {
    id: number;
    code: string;
    name: string;
    type: 'core' | 'specialized' | 'applied';
    hours: number;
    prerequisite_id: number | null;
    is_active: boolean;
    created_at: string;
    updated_at: string;
    prerequisite?: Subject;
    dependents?: Subject[];
    strands?: Strand[];
    pivot?: {
        grade_level: number;
        semester: number;
        sort_order: number;
    };
}

export interface Section {
    id: number;
    name: string;
    strand_id: number;
    semester_id: number;
    grade_level: number;
    max_capacity: number;
    adviser_id: number | null;
    created_at: string;
    updated_at: string;
    strand?: Strand;
    semester?: Semester;
    adviser?: import('./auth').User;
    enrollments?: Enrollment[];
    enrolled_count?: number;
}

export interface Enrollment {
    id: number;
    student_id: number;
    section_id: number;
    semester_id: number;
    status: 'pending' | 'enrolled' | 'dropped' | 'transferred';
    remarks: string | null;
    enrolled_at: string | null;
    created_at: string;
    updated_at: string;
    student?: Student;
    section?: Section;
    semester?: Semester;
    subjects?: Subject[];
    grades?: Grade[];
}

export interface Grade {
    id: number;
    enrollment_id: number;
    subject_id: number;
    midterm: number | null;
    finals: number | null;
    final_grade: number | null;
    remarks: 'passed' | 'failed' | null;
    is_locked: boolean;
    encoded_by: number | null;
    created_at: string;
    updated_at: string;
    enrollment?: Enrollment;
    subject?: Subject;
    encoder?: import('./auth').User;
}

export interface TeacherProfile {
    id: number;
    user_id: number;
    employee_id: string | null;
    position_title: string | null;
    appointment_status: string | null;
    sex: string | null;
    birthdate: string | null;
    contact_number: string | null;
    address: string | null;
    highest_degree: string | null;
    degree_course: string | null;
    degree_major: string | null;
    school_graduated: string | null;
    year_graduated: number | null;
    prc_license_number: string | null;
    prc_validity: string | null;
    eligibility: string | null;
    specialization: string | null;
    date_hired: string | null;
    teaching_hours_per_week: number | null;
    created_at: string;
    updated_at: string;
    user?: import('./auth').User;
    trainings?: TeacherTraining[];
}

export interface TeacherTraining {
    id: number;
    teacher_profile_id: number;
    title: string;
    type: string | null;
    sponsor: string | null;
    date_from: string | null;
    date_to: string | null;
    hours: number | null;
    created_at: string;
    updated_at: string;
}

export interface SchoolSetting {
    id: number;
    key: string;
    value: string;
}

export interface AuditLog {
    id: number;
    user_id: number | null;
    action: string;
    model_type: string;
    model_id: number;
    old_values: Record<string, unknown> | null;
    new_values: Record<string, unknown> | null;
    created_at: string;
    user?: import('./auth').User;
}

export interface PaginatedData<T> {
    current_page: number;
    data: T[];
    first_page_url: string | null;
    from: number | null;
    last_page: number;
    last_page_url: string | null;
    links: Array<{
        url: string | null;
        label: string;
        active: boolean;
    }>;
    next_page_url: string | null;
    path: string;
    per_page: number;
    prev_page_url: string | null;
    to: number | null;
    total: number;
}

export interface NavigationItem {
    title: string;
    href: string;
    icon: string;
    children?: NavigationItem[];
}

export interface NavigationGroup {
    label: string;
    items: NavigationItem[];
}

export interface FlashMessages {
    success?: string;
    error?: string;
    warning?: string;
    info?: string;
}
