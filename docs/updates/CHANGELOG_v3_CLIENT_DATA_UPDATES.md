# LSNHS Enrollment System — Changelog v3

## Client Data Review Updates

**Date:** February 13, 2026
**Updated by:** Brian (Developer)
**Trigger:** Client (School Registrar) provided actual DepEd school forms from Lake Sebu National High School — SF1, SF2, and SF3 for SY 2025-2026.

> This document summarizes all changes made to the system design after reviewing the client's actual school data. These updates ensure the system matches real DepEd requirements. **All previously submitted documents (PRD v2, Feature List v2, System Journey, Architecture, Database & API) remain valid** — the changes below are additions, not replacements.

---

## Files Reviewed from Client

| File | DepEd Form | Description |
|------|-----------|-------------|
| School_Form_1_(SF1).xls | SF1 — School Register | Master enrollment list with student profiles, per section |
| School_Form_2_(SF2).xls | SF2 — Daily Attendance | Monthly attendance grid per section |
| School_Form_3_(SF3).xls | SF3 — Books Issued & Returned | Textbook tracking per section |

**Sample Data:** Grade 11, TVL Track — ICT Strand, Section "Estember", Computer System Servicing (NC II), First Semester SY 2025-2026, 31 students (19 male, 12 female).

---

## Confirmed School Information

Previously we used placeholder values. The client's SF1 confirmed the following real data, now reflected in our system:

| Field | Old (Assumed) | New (Confirmed from SF1) |
|-------|--------------|--------------------------|
| School ID | 305678 | **304550** |
| District | *(not set)* | **Lake Sebu East** |
| Division | South Cotabato | South Cotabato *(confirmed)* |
| Region | Region XII - SOCCSKSARGEN | Region XII *(confirmed)* |

---

## Schema Changes (4 additions)

### 1. Students Table — Added `religion` and `learning_modality`

**Why:** SF1 requires these fields per student. Every student in the file had "Christianity" and "Face to Face" recorded.

```
NEW COLUMNS:
  religion            VARCHAR(50)   NULLABLE    — e.g., "Christianity", "Islam"
  learning_modality   VARCHAR(30)   DEFAULT "Face to Face" — e.g., "Face to Face", "Blended", "Modular"
```

### 2. Students Table — Added `father_name` and `mother_name`

**Why:** SF1 records father and mother as separate fields, not a single guardian. Our original design had only `guardian_name` and `guardian_relationship`. The SF1 data shows that guardian fields are used only when the student is NOT living with parents.

```
NEW COLUMNS:
  father_name   VARCHAR(200)   NULLABLE   — e.g., "ANGKOY, ODOS TIWAN"
  mother_name   VARCHAR(200)   NULLABLE   — e.g., "SANDAY, LANDING BOKONG"

KEPT (unchanged):
  guardian_name          — Used when student lives with someone other than parents
  guardian_relationship  — "PARENT", "RELATIVE", etc.
  guardian_contact       — Contact number
```

### 3. Strands Table — Added `course`

**Why:** TVL Track strands have a specific TESDA course name that appears on SF1. The client's file shows "Computer System Servicing (NC II)" for the ICT strand. Academic strands don't use this field.

```
NEW COLUMN:
  course   VARCHAR(255)   NULLABLE   — e.g., "Computer System Servicing (NC II)"
```

### 4. SF1 Export — Already Planned

SF1 generation was already in our feature list (Reports module). No change needed — just confirmed the exact format to follow.

---

## New Module: Teacher Profiling (SF7)

**Why:** Client confirmed the system should include teacher profiling. This maps to DepEd School Form 7 (SF7) — School Personnel Assignment List and Basic Profile. This is a standard form every school submits at the beginning of each school year.

### New Tables Added (2)

**teacher_profiles** — extends the users table for teachers:

| Column | Type | Description |
|--------|------|-------------|
| employee_id | VARCHAR(20) | DepEd Employee ID |
| position_title | VARCHAR | "Teacher I", "Teacher III", "Master Teacher I" |
| appointment_status | VARCHAR | permanent, contractual, part-time, job_order |
| sex, birthdate | — | Personal info |
| contact_number, address | — | Contact info |
| highest_degree | VARCHAR | "Bachelor's", "Master's", "Doctorate" |
| degree_course | VARCHAR | "BSEd Mathematics", "BS Chemistry" |
| degree_major | VARCHAR | "Mathematics", "English" |
| school_graduated | VARCHAR | University name |
| year_graduated | YEAR | Year graduated |
| prc_license_number | VARCHAR(20) | PRC License Number |
| prc_validity | DATE | License expiry date |
| eligibility | VARCHAR | "LET Passer", "CSC Professional" |
| specialization | VARCHAR | Teaching specialization |
| date_hired | DATE | Date first hired |
| teaching_hours_per_week | INT | Weekly teaching load |

**teacher_trainings** — seminars/trainings attended:

| Column | Type | Description |
|--------|------|-------------|
| title | VARCHAR | "SHS Curriculum Training" |
| type | VARCHAR | seminar, workshop, training, conference |
| sponsor | VARCHAR | "DepEd Region XII", "CHED" |
| date_from, date_to | DATE | Training period |
| hours | DECIMAL(5,1) | Total training hours |

### New Features (4)

| Feature | Description | Difficulty |
|---------|-------------|------------|
| Teacher List page | View all teachers with profile status | Easy |
| Teacher Profile page | View/edit full SF7 profile with trainings | Easy |
| Add/Remove Trainings | Manage training records per teacher | Easy |
| SF7 Export | Generate DepEd School Form 7 as Excel | Medium |

### New Sidebar Entry

"Teachers" link added under the Records section for admin and registrar roles.

---

## Scope Decisions

### Kept Out of Scope (Phase 2)

| Form | Reason |
|------|--------|
| SF2 — Daily Attendance | Requires complex calendar grid UI, daily data entry by teachers. Not related to enrollment/records management. Significant scope addition. |
| SF3 — Books Issued & Returned | Textbook inventory tracking is a separate system concern. Not part of enrollment or grading workflow. |

These can be added in a future version without changing any existing tables or features.

---

## Updated Counts

| Component | Before | After | Change |
|-----------|--------|-------|--------|
| Database tables | 14 | 16 | +2 (teacher_profiles, teacher_trainings) |
| Student fields | 16 | 20 | +4 (religion, learning_modality, father_name, mother_name) |
| Strand fields | 5 | 6 | +1 (course) |
| Model files | 12 | 14 | +2 (TeacherProfile, TeacherTraining) |
| Trait files | 19 | 20 | +1 (TeacherProfileRelations) |
| Total features | 48 | 52 | +4 (teacher profiling features) |
| DepEd Forms supported | SF1, SF5, SF9, SF10 | SF1, SF5, SF7, SF9, SF10 | +1 (SF7) |

---

## Impact Assessment

| Area | Impact |
|------|--------|
| Existing features | **No changes.** All 48 original features remain exactly as designed. |
| Database schema | **Additive only.** 2 new tables + 5 new columns on existing tables. No columns removed or renamed. |
| Architecture | **No changes.** Same tech stack, same folder structure, same coding conventions. |
| System Journey | **No changes.** All 10 user flows remain valid. |
| Build timeline | **+1–2 days** for teacher profiling module. Schema changes are trivial (add columns). |

---

## Summary

The client's files confirmed our design was already on the right track. The changes are minor — a few extra student fields for SF1 compliance, a course field for TVL strands, and a new teacher profiling module based on DepEd SF7. No existing work was invalidated. The system now covers 5 out of 10 DepEd school forms (SF1, SF5, SF7, SF9, SF10), with SF2 and SF3 documented as Phase 2 candidates.
