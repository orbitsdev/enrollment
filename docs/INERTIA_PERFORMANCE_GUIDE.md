# Inertia.js v2 — Performance & Data Flow Guide

> **Purpose:** This document instructs any AI coding agent working on this Laravel 12 + Vue 3 + Inertia.js v2 project to maximize Inertia's built-in features for performance, smooth data flow, and clean UX. Every page and form you build **must** consider these patterns before writing code.

---

## 1. useForm — Maximize the Form Helper

`useForm` is the primary way to handle form submissions. **Never use raw `router.post()` with manual `ref()` / `reactive()` for forms.** Always use `useForm` so you get built-in error handling, processing state, progress tracking, dirty detection, and history persistence for free.

### 1.1 Standard Pattern (Vue 3)

```vue
<script setup>
import { useForm } from '@inertiajs/vue3'

const form = useForm({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
})

const submit = () => {
  form.post('/users', {
    preserveScroll: true,
    onSuccess: () => form.reset('password', 'password_confirmation'),
  })
}
</script>

<template>
  <form @submit.prevent="submit">
    <input v-model="form.name" type="text" />
    <div v-if="form.errors.name">{{ form.errors.name }}</div>

    <button type="submit" :disabled="form.processing">
      {{ form.processing ? 'Saving...' : 'Save' }}
    </button>

    <div v-if="form.recentlySuccessful">Saved!</div>
  </form>
</template>
```

### 1.2 Mandatory Properties to Use

| Property / Method | When to Use |
|---|---|
| `form.processing` | **Always** bind to submit button `:disabled` to prevent double-submit |
| `form.errors.<field>` | **Always** display below every input |
| `form.hasErrors` | Use for top-level error banners |
| `form.isDirty` | Use for unsaved-changes warnings and conditional "Save" button enabling |
| `form.recentlySuccessful` | Use for temporary success toasts/badges (auto-resets after 2s) |
| `form.progress` | **Always** use when the form has file uploads to show upload % |
| `form.reset(...)` | Call in `onSuccess` for password fields and one-time inputs |
| `form.clearErrors(...)` | Call when the user starts correcting a field (e.g., `@input`) |
| `form.transform(data => ...)` | Use to inject computed data before submission (timestamps, formatted values) |

### 1.3 History State Persistence

For any form where the user may navigate away and return (multi-step wizards, long forms), **always** provide a form key:

```js
const form = useForm('CreateApplicant', {
  first_name: '',
  last_name: '',
  email: '',
})
```

Exclude sensitive fields from history:

```js
const form = useForm('LoginForm', {
  email: '',
  password: '',
}).dontRemember('password')
```

### 1.4 Data Transformation

Use `transform()` to keep the template clean and inject derived data right before submission:

```js
form
  .transform((data) => ({
    ...data,
    full_name: `${data.first_name} ${data.last_name}`,
    submitted_at: new Date().toISOString(),
  }))
  .post('/applicants')
```

### 1.5 Submission Options Checklist

Every `form.post()` / `form.put()` / etc. call should explicitly consider these options:

```js
form.post('/endpoint', {
  preserveScroll: true,          // Almost always true — prevents scroll jump
  preserveState: true,           // Keep component state on validation errors
  onSuccess: () => {},           // Reset fields, show toast, redirect
  onError: (errors) => {},       // Focus first error field
  only: ['flash'],               // Partial reload — fetch only what changed
  except: ['heavy_prop'],        // Exclude heavy data on redirect-back
  reset: ['paginated_prop'],     // Reset merged/infinite-scroll props
  invalidateCacheTags: ['users'],// Bust prefetch cache for related pages
})
```

### 1.6 Precognition (Real-Time Server Validation)

For any form where UX matters (registration, application forms), enable Precognition to validate fields on blur without duplicating rules client-side:

```js
const form = useForm({
  email: '',
  name: '',
}).withPrecognition('post', '/applicants')
```

```vue
<input v-model="form.email" @change="form.validate('email')" />
<p v-if="form.invalid('email')">{{ form.errors.email }}</p>
<p v-if="form.valid('email')" class="text-green-600">Looks good!</p>
<p v-if="form.validating">Checking...</p>
```

Rules:
- Validate on `@change` or `@blur`, **never** on every keystroke (it's debounced at 1500ms by default but still wasteful).
- Use `form.touch('field')` on `@blur` for fields you want to batch-validate later with `form.validate()`.
- For wizard-style multi-step forms, validate a step's fields together: `form.validate({ only: ['name', 'email'], onSuccess: () => nextStep() })`.

---

## 2. Partial Reloads — Fetch Only What You Need

When revisiting the same page (filtering, sorting, tab switching), **never reload all props**. Use `only` or `except` to request just the data that changed.

### 2.1 Server Side — Always Use Lazy Closures

```php
return Inertia::render('Users/Index', [
    'users'     => fn () => User::query()->paginate(),     // Lazy — only evaluated when requested
    'companies' => fn () => Company::all(),                // Lazy
    'filters'   => $request->only(['search', 'role']),     // Always included (small, no closure needed)
]);
```

Use `Inertia::optional()` for data that should **never** load on the initial visit and only when explicitly requested:

```php
'export_data' => Inertia::optional(fn () => $this->generateExport()),
```

Use `Inertia::always()` for data that must be fresh on every request, even partial reloads:

```php
'notifications_count' => Inertia::always(fn () => auth()->user()->unreadNotifications()->count()),
```

### 2.2 Client Side

```js
import { router } from '@inertiajs/vue3'

// Reload only the users prop (e.g., after filtering)
router.reload({ only: ['users'] })

// Or with a Link component
<Link href="/users?role=admin" :only="['users']">Admins</Link>
```

### 2.3 Decision Table

| Scenario | Approach |
|---|---|
| User filters a table | `router.reload({ only: ['users'] })` |
| User changes a tab on the same page | `router.reload({ only: ['tab_data'] })` |
| Form redirects back with validation errors | `except: ['heavy_chart_data']` in form options |
| Data never changes (static lookups) | Wrap in `Inertia::optional(fn () => ...)->once()` |

---

## 3. Deferred Props — Fast Initial Render, Load Heavy Data After

Use `Inertia::defer()` for any prop that is expensive to compute and **not needed for the initial paint**. The page renders instantly with critical data, then heavy data loads in a follow-up request.

### 3.1 Server Side

```php
return Inertia::render('Dashboard', [
    'user'        => $user,                                           // Immediate
    'stats'       => Inertia::defer(fn () => Stats::generate()),      // Deferred
    'permissions' => Inertia::defer(fn () => Permission::all()),      // Deferred
    'audit_log'   => Inertia::defer(fn () => AuditLog::recent()),     // Deferred
]);
```

**Group related deferred props** into parallel requests to reduce waterfall:

```php
'teams'    => Inertia::defer(fn () => Team::all(), 'sidebar'),
'projects' => Inertia::defer(fn () => Project::all(), 'sidebar'),
// ↑ Both fetched in ONE parallel request (group name: 'sidebar')

'analytics' => Inertia::defer(fn () => Analytics::compute()),
// ↑ Fetched in a SEPARATE parallel request (default group)
```

Chain `.once()` for data that only needs to be resolved once across navigations:

```php
'stats' => Inertia::defer(fn () => Stats::generate())->once(),
```

### 3.2 Client Side

```vue
<script setup>
import { Deferred } from '@inertiajs/vue3'
</script>

<template>
  <!-- Critical content renders immediately -->
  <h1>Welcome, {{ user.name }}</h1>

  <!-- Heavy content loads after initial render -->
  <Deferred data="stats">
    <template #fallback>
      <SkeletonLoader />
    </template>

    <StatsPanel :stats="stats" />
  </Deferred>

  <!-- Wait for multiple deferred props -->
  <Deferred :data="['teams', 'projects']">
    <template #fallback>
      <div class="animate-pulse">Loading sidebar...</div>
    </template>

    <Sidebar :teams="teams" :projects="projects" />
  </Deferred>
</template>
```

### 3.3 Rules

- **Always** provide a `#fallback` slot with a skeleton/spinner — never leave a blank space.
- Group deferred props that are rendered in the same UI section so they arrive together.
- Use `.once()` for reference data (roles, permissions, categories) that doesn't change between navigations.
- Never defer props that are needed above the fold or for SEO.

---

## 4. Prefetching — Anticipate the Next Page

Prefetching loads page data **before the user clicks**, making navigation feel instant.

### 4.1 Link-Based Prefetching

```vue
<!-- Prefetch on hover (default, 75ms delay) -->
<Link href="/applicants" prefetch>Applicants</Link>

<!-- Prefetch on mousedown (for less confident predictions) -->
<Link href="/settings" prefetch="click">Settings</Link>

<!-- Prefetch on mount (for highly likely next pages) -->
<Link href="/exam/start" prefetch="mount">Start Exam</Link>

<!-- Combine strategies -->
<Link href="/results" :prefetch="['mount', 'hover']">Results</Link>

<!-- Custom cache duration -->
<Link href="/users" prefetch cache-for="1m">Users</Link>

<!-- Stale-while-revalidate: serve stale for 30s, revalidate up to 1m -->
<Link href="/dashboard" prefetch :cache-for="['30s', '1m']">Dashboard</Link>
```

### 4.2 Programmatic Prefetching

```js
import { router } from '@inertiajs/vue3'

// Prefetch the next page of results when user is on current page
router.prefetch('/applicants?page=2', { method: 'get' }, { cacheFor: '1m' })
```

### 4.3 Cache Tags & Invalidation

Tag your prefetched data so it can be invalidated when mutations happen:

```vue
<Link href="/applicants" prefetch :cache-tags="['applicants']">Applicants</Link>
```

Invalidate on form submission:

```js
form.post('/applicants', {
  invalidateCacheTags: ['applicants', 'dashboard'],
})
```

Or manually:

```js
router.flushByCacheTags('applicants')  // Flush specific tags
router.flushAll()                       // Nuclear option
```

### 4.4 Rules

- **Always** prefetch navigation links in the sidebar/navbar with `prefetch` (hover is fine for most).
- Use `prefetch="mount"` for "next step" links in wizards or sequential flows.
- Tag all prefetched data and invalidate tags on any mutation that affects that data.
- Use stale-while-revalidate `[:cache-for="['30s', '1m']"]` for dashboards and lists that update frequently.

---

## 5. WhenVisible — Lazy Load Below-the-Fold Content

Use `WhenVisible` to load data **only when the user scrolls to it**. This uses IntersectionObserver under the hood.

### 5.1 Server Side

Mark the prop as optional so it's never loaded on the initial request:

```php
return Inertia::render('Applicant/Show', [
    'applicant'  => $applicant,                                    // Immediate
    'exam_history' => Inertia::optional(fn () => $applicant->examHistory()),  // Only when visible
]);
```

### 5.2 Client Side

```vue
<script setup>
import { WhenVisible } from '@inertiajs/vue3'
</script>

<template>
  <!-- Above the fold — renders immediately -->
  <ApplicantProfile :applicant="applicant" />

  <!-- Below the fold — loads when scrolled into view -->
  <WhenVisible data="exam_history" :buffer="300">
    <template #fallback>
      <SkeletonTable :rows="5" />
    </template>

    <ExamHistoryTable :history="exam_history" />
  </WhenVisible>
</template>
```

### 5.3 With Forms — Exclude WhenVisible Props

When submitting a form on a page that also has `WhenVisible` sections, exclude those props to prevent unnecessary reloading on redirect-back:

```js
form.post('/applicants', {
  except: ['exam_history', 'activity_log'],
})
```

### 5.4 Always-Reload Pattern

For data that should refresh every time it comes into view (e.g., a live status section):

```vue
<WhenVisible data="live_stats" always>
  <template #default="{ fetching }">
    <LiveStatsPanel />
    <span v-if="fetching" class="text-xs text-gray-400">Refreshing...</span>
  </template>
  <template #fallback>
    <SkeletonLoader />
  </template>
</WhenVisible>
```

---

## 6. Infinite Scroll — Replace Pagination Where Appropriate

Use `InfiniteScroll` for long lists (applicant lists, exam logs, activity feeds) instead of traditional pagination for a smoother UX.

### 6.1 Server Side

```php
return Inertia::render('Applicants/Index', [
    'applicants' => Inertia::scroll(
        fn () => Applicant::query()
            ->filter($request->only(['search', 'status', 'campus']))
            ->paginate(25)
    ),
    'campuses' => fn () => Campus::all(),
]);
```

For multiple paginated lists on the same page, use custom `pageName`:

```php
'applicants' => Inertia::scroll(fn () => Applicant::paginate(25, pageName: 'applicants')),
'logs'       => Inertia::scroll(fn () => ActivityLog::paginate(50, pageName: 'logs')),
```

### 6.2 Client Side

```vue
<script setup>
import { InfiniteScroll } from '@inertiajs/vue3'

defineProps(['applicants'])
</script>

<template>
  <InfiniteScroll data="applicants" :buffer="400">
    <div v-for="applicant in applicants.data" :key="applicant.id">
      <ApplicantCard :applicant="applicant" />
    </div>

    <template #loading>
      <SkeletonCard v-for="i in 3" :key="i" />
    </template>
  </InfiniteScroll>
</template>
```

### 6.3 Resetting on Filter Change

When the user changes filters, **always** reset the infinite scroll data:

```js
const applyFilter = (filters) => {
  router.visit(route('applicants.index'), {
    data: filters,
    only: ['applicants'],
    reset: ['applicants'],  // ← Critical: clears merged data
  })
}
```

### 6.4 Chat / Reverse Pattern

For chat-like interfaces (support tickets, messaging):

```vue
<InfiniteScroll data="messages" reverse>
  <div v-for="msg in messages.data" :key="msg.id">
    <ChatBubble :message="msg" />
  </div>
</InfiniteScroll>
```

### 6.5 Manual Mode After N Pages

Prevent infinite loading for huge datasets — switch to manual "Load More" after 3 auto-loaded pages:

```vue
<InfiniteScroll data="applicants" :manual-after="3">
  <!-- content -->

  <template #next="{ loading, fetch, hasMore, manualMode }">
    <button v-if="manualMode && hasMore" @click="fetch" :disabled="loading"
      class="btn btn-outline">
      {{ loading ? 'Loading...' : 'Load More Applicants' }}
    </button>
  </template>
</InfiniteScroll>
```

---

## 7. Combined Patterns — Decision Matrix

Use this table to decide which feature(s) to apply for each scenario:

| Scenario | useForm | Partial Reload | Deferred | Prefetch | WhenVisible | InfiniteScroll |
|---|---|---|---|---|---|---|
| Login / Registration form | ✅ + Precognition | | | | | |
| Dashboard overview | | ✅ | ✅ (charts, stats) | ✅ (nav links) | ✅ (activity feed) | |
| Applicant list page | ✅ (filter form) | ✅ (on filter) | | ✅ (detail links) | | ✅ |
| Applicant detail page | ✅ (edit form) | ✅ (tab switch) | ✅ (heavy sections) | ✅ (next/prev nav) | ✅ (history, logs) | |
| Exam scheduling | ✅ | ✅ (slot refresh) | ✅ (available slots) | | | |
| Multi-step wizard | ✅ + history key | | | ✅ (next step on mount) | | |
| Support ticket chat | ✅ (reply form) | | | | | ✅ (reverse) |
| Payment processing | ✅ | ✅ | | | | |
| Admin settings page | ✅ | ✅ (per section) | ✅ (audit log) | | ✅ (heavy tables) | |

---

## 8. Anti-Patterns — What NOT to Do

1. **Never use `reactive()` + `router.post()` for forms** — you lose error handling, processing state, dirty tracking, and progress. Always use `useForm`.
2. **Never return all props eagerly** — wrap optional/heavy data in `fn () =>` closures or `Inertia::defer()`.
3. **Never reload the entire page when only one prop changed** — use `router.reload({ only: [...] })`.
4. **Never forget `preserveScroll: true`** on form submissions — scroll jumping is terrible UX.
5. **Never show blank space while deferred/lazy data loads** — always provide skeleton fallbacks.
6. **Never forget to `reset` infinite scroll data on filter changes** — stale merged data will corrupt results.
7. **Never prefetch without cache tags** — stale prefetched data after a mutation leads to inconsistency.
8. **Never validate on every keystroke with Precognition** — use `@change` or `@blur`, not `@input`.
9. **Never forget `except` on forms that share a page with `WhenVisible`** — it prevents unnecessary re-fetching of lazy-loaded sections when the form redirects back.

---

## 9. Checklist Before Shipping Any Page

- [ ] All forms use `useForm` (not raw reactive + router)
- [ ] Submit buttons are `:disabled="form.processing"`
- [ ] Validation errors displayed for every input field
- [ ] `preserveScroll: true` on all form submissions
- [ ] Heavy/optional data wrapped in closures or `Inertia::defer()`
- [ ] Deferred sections have skeleton fallbacks
- [ ] Related deferred props are grouped for parallel loading
- [ ] Sidebar/nav links have `prefetch` enabled
- [ ] Prefetch cache tags set and invalidated on mutations
- [ ] Paginated lists use `InfiniteScroll` or at minimum partial reloads
- [ ] Filter changes `reset` infinite scroll data
- [ ] Below-the-fold sections use `WhenVisible` with `Inertia::optional()`
- [ ] Long forms use history key (`useForm('FormName', data)`)
- [ ] Password/sensitive fields excluded from history (`.dontRemember()`)
- [ ] Multi-step forms use Precognition for per-step validation
