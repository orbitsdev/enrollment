# Toast Notification System

This document describes how toast notifications are implemented across the TPT application (backend and frontend).

## Overview

We use **vue-sonner** (`v1.3.2`) as our toast notification library. Toasts can be triggered in two ways:

1. **Backend (Laravel)** — Flash messages via session, automatically displayed by the frontend.
2. **Frontend (Vue)** — Direct `toast()` calls for client-side actions.

---

## Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                        Backend (Laravel)                     │
│                                                              │
│  Controller                                                  │
│  └─ return redirect()->with('success', 'Message');           │
│                         │                                    │
│  HandleInertiaRequests Middleware                             │
│  └─ Shares flash data (success/error/warning/info)           │
│     to every Inertia response                                │
└──────────────────────────┬──────────────────────────────────┘
                           │ Inertia Response
┌──────────────────────────▼──────────────────────────────────┐
│                       Frontend (Vue 3)                       │
│                                                              │
│  AdminLayout.vue                                             │
│  ├─ Watches `page.props.flash` for backend messages          │
│  ├─ Calls toast.success/error/warning/info accordingly       │
│  └─ Mounts <Toaster /> component (top-right, 4s duration)   │
│                                                              │
│  Any Vue Component                                           │
│  └─ import { toast } from '@/components/ui/sonner'           │
│     └─ toast.success('Message', { description: '...' })     │
└─────────────────────────────────────────────────────────────┘
```

---

## Toast Types

| Type      | Color   | Use Case                                      |
|-----------|---------|-----------------------------------------------|
| `success` | Green   | Action completed successfully                 |
| `error`   | Red     | Action failed or validation error             |
| `warning` | Yellow  | Non-critical issue that needs attention        |
| `info`    | Blue    | General information or status update           |

---

## Backend Usage (Laravel Controllers)

Flash messages are set using Laravel's `->with()` method on redirects. The Inertia middleware automatically shares these with the frontend.

### Middleware Setup

**File:** `app/Http/Middleware/HandleInertiaRequests.php`

```php
'flash' => [
    'success' => fn () => $request->session()->get('success'),
    'error'   => fn () => $request->session()->get('error'),
    'warning' => fn () => $request->session()->get('warning'),
    'info'    => fn () => $request->session()->get('info'),
],
```

### Controller Examples

**Success — after creating a record:**
```php
return redirect()
    ->route('admin.campuses.index')
    ->with('success', 'Campus created successfully.');
```

**Error — when an action is not allowed:**
```php
return redirect()
    ->back()
    ->with('error', 'Cannot delete campus with existing users or programs.');
```

**Info — guiding the user to a next step:**
```php
return redirect()
    ->route('admin.examinations.edit', $examination)
    ->with('info', 'Draft examination created. Fill in the details below.');
```

**Warning — non-critical issue:**
```php
return redirect()
    ->back()
    ->with('warning', 'Some records were skipped during import.');
```

### Where Backend Toasts Are Used

| Controller               | Success | Error | Info | Warning |
|--------------------------|---------|-------|------|---------|
| CampusController         | 3       | 1     | —    | —       |
| ExaminationController    | 6       | 4     | 1    | —       |
| PaymentController        | 2       | 2     | —    | —       |
| ExaminationResultController | 2    | 3     | —    | —       |
| UserController           | 3       | 1     | —    | —       |
| ApplicationController    | 2       | 2     | —    | —       |

---

## Frontend Usage (Vue Components)

### Import

```typescript
import { toast } from '@/components/ui/sonner';
```

### Basic Usage

```typescript
// Simple message
toast.success('Details updated');
toast.error('Failed to update details');

// With description
toast.success('Examination cloned', {
    description: 'The examination has been cloned successfully.',
});

toast.error('Delete failed', {
    description: 'Could not delete the examination.',
});
```

### Common Pattern — Inertia Router Callbacks

The most common pattern is calling `toast` inside Inertia's `onSuccess` and `onError` callbacks:

```typescript
import { router } from '@inertiajs/vue3';
import { toast } from '@/components/ui/sonner';

function handleDelete(id: number) {
    router.delete(`/admin/examinations/${id}`, {
        onSuccess: () => {
            toast.success('Examination deleted', {
                description: 'The examination has been permanently deleted.',
            });
        },
        onError: () => {
            toast.error('Delete failed', {
                description: 'Could not delete the examination.',
            });
        },
    });
}
```

### Common Pattern — Inertia Form Submissions

```typescript
import { useForm } from '@inertiajs/vue3';
import { toast } from '@/components/ui/sonner';

const form = useForm({ name: '', code: '' });

function save() {
    form.put(`/admin/examinations/${props.examination.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            toast.success('Settings updated');
        },
        onError: () => {
            toast.error('Failed to update settings');
        },
    });
}
```

### Client-Side Validation Toasts

```typescript
// Validation before submitting
if (form.schedules.length === 0) {
    toast.error('No exam dates', {
        description: 'Please add at least one exam date before adding slots.',
    });
    return;
}
```

### Displaying Backend Validation Errors

```typescript
onError: (errors) => {
    toast.error(Object.values(errors)[0] as string || 'Failed to add slot');
},
```

---

## Component Setup

### Sonner Wrapper

**File:** `resources/js/components/ui/sonner/Sonner.vue`

A Shadcn Vue wrapper around `vue-sonner`'s `<Toaster>` component. It applies theme-aware styling using Tailwind CSS classes for each toast type (success, error, warning, info).

### Mounting in Layout

**File:** `resources/js/layouts/AdminLayout.vue`

```vue
<Toaster position="top-right" :duration="4000" richColors closeButton />
```

| Setting      | Value       | Description                          |
|-------------|-------------|--------------------------------------|
| `position`  | `top-right` | Toasts appear in the top-right corner |
| `duration`  | `4000`      | Auto-dismiss after 4 seconds         |
| `richColors`| `true`      | Colored backgrounds per toast type   |
| `closeButton`| `true`     | Shows a close button on each toast   |

### Flash Message Watcher

The layout watches Inertia's shared `flash` props and automatically triggers toasts:

```typescript
const page = usePage();
const flash = computed(() => page.props.flash as FlashMessages | undefined);

watch(
    () => flash.value,
    (newFlash) => {
        if (newFlash?.success) toast.success(newFlash.success);
        if (newFlash?.error)   toast.error(newFlash.error);
        if (newFlash?.warning) toast.warning(newFlash.warning);
        if (newFlash?.info)    toast.info(newFlash.info);
    },
    { immediate: true },
);
```

---

## TypeScript Types

**File:** `resources/js/types/index.ts`

```typescript
export interface FlashMessages {
    success?: string;
    error?: string;
    warning?: string;
    info?: string;
}
```

---

## Key Files Reference

| File | Purpose |
|------|---------|
| `resources/js/components/ui/sonner/Sonner.vue` | Toast component with themed styling |
| `resources/js/components/ui/sonner/index.ts` | Exports `Sonner` component and `toast` function |
| `resources/js/layouts/AdminLayout.vue` | Mounts toaster, watches flash messages |
| `app/Http/Middleware/HandleInertiaRequests.php` | Shares flash data to frontend |
| `resources/js/types/index.ts` | `FlashMessages` type definition |

---

## Guidelines

1. **Backend actions** — Always use `->with('success'|'error'|'warning'|'info', 'message')` on redirects. The layout handles display automatically.
2. **Frontend-only actions** — Import `toast` from `@/components/ui/sonner` and call the appropriate method.
3. **Keep messages concise** — Use a short title and optional `description` for details.
4. **Always handle errors** — Pair `onSuccess` toasts with `onError` toasts in Inertia callbacks.
5. **Use the right type** — `success` for completed actions, `error` for failures, `info` for guidance, `warning` for caution.
