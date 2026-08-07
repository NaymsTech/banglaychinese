# 📊 PROJECT STATUS — Banglay Chinese

**Generated:** 2026-08-01
**Repo:** `github.com/NaymsTech/banglaychinese` (HEAD `4c10d40`)
**Status:** In Development — MVP partially complete

---

## 1. Tech Stack

| Layer | Technology |
|---|---|
| Backend | Laravel 13.x (PHP ^8.3), Eloquent ORM |
| Database | SQLite (default), queue/cache/session on DB driver |
| Frontend | Blade + Tailwind CSS 4 + Vite 8 + Alpine.js 3.4 |
| Auth | Laravel Breeze (login/register/password reset/verification) |
| Admin Panel | Custom-built (no Filament/Nova) at `/admin` |
| Language | Bengali (bn) UI, hardcoded ৳ prices, Hind Siliguri font |

---

## 2. MVP Feature Completion

| # | Feature | Status |
|---|---------|--------|
| 1 | Authentication (Student/Admin) | 🟡 Partially Complete — student auth done; admin only via seeder |
| 2 | Admin Dashboard | 🟡 Partially Complete — shell with stats + scholarship mgmt only |
| 3 | Student Management | ❌ Not Started |
| 4 | Course Management | ❌ Not Started — seeder-only |
| 5 | Lesson Management | ❌ Not Started — seeder-only |
| 6 | Manual Payment Approval | 🟡 Partially Complete — data captured; no approval UI; gating bypassed |
| 7 | Student Dashboard | ✅ Complete |
| 8 | Progress Tracking | ✅ Complete |
| 9 | Live Class Links | ❌ Not Started |
| 10 | Certificates | ❌ Not Started |
| 11 | Consultation Booking | ❌ Not Started |
| 12 | Blog | 🟡 Partially Complete — public views done; no admin authoring |
| 13 | Basic Settings | 🟡 Partially Complete — table exists; no model/UI |

**Summary:** 2 ✅ Complete · 6 🟡 Partial · 5 ❌ Not Started

---

## 3. Working Now

- Public marketing site (home w/ SEO + JSON-LD, courses, about, contact→WhatsApp, scholarship form)
- Blog public reading view
- Student registration/login/profile
- Enroll in course (creates `pending` enrollment, captures payment method / transaction ID)
- Student dashboard with enrolled courses + progress bars
- Lesson player with video, free-preview gating, mark-complete toggle, prev/next nav
- Admin dashboard (stat cards only)
- Admin scholarship application review/approve/reject
- Seeders: admin user, 4 categories, 4 courses

---

## 4. Known Gaps / Broken

1. **Payment gating bypassed** — `pending` enrollments grant full lesson access; approval UI missing.
2. **IDOR** — lesson complete endpoint doesn't verify enrollment.
3. **Hardcoded admin password** — `admin@banglaychinese.com` / `password` from seeder.
4. **Two conflicting admin middlewares** — `AdminMiddleware` (registered) vs `IsAdmin` (dead code).
5. **Lesson slugs nullable & unseeded** — slug-based lesson routes will 404 for seeded lessons.
6. **Settings table dead** — no model, UI, or seeder.
7. **Raw HTML rendering** — `{!! !!}` for course/lesson content; XSS risk once admin CRUD exists.
8. **No rate limiting** on contact/scholarship/enroll forms.
9. **Email verification disabled** — `MustVerifyEmail` commented out.

---

## 5. Data Model (tables)

- **System:** users, password_reset_tokens, sessions, cache, cache_locks, jobs, job_batches, failed_jobs
- **Domain:** categories, posts, courses, modules, lessons, enrollments, lesson_progress, scholarship_applications, settings
- **Key relations:** Course→Modules→Lessons; Course hasManyThrough Lessons; LessonProgress (user×lesson); Enrollment (user×course); Post→Category/Author

---

## 6. Models

`User`, `Course`, `Module`, `Lesson`, `Category`, `Post`, `Enrollment`, `LessonProgress`, `ScholarshipApplication` — standard Eloquent with relationships.

> ⚠️ `User` uses PHP attribute-style `#[Fillable()]`; all others use classic `$fillable` — inconsistent conventions.

---

## 7. Routes (major)

- **Public:** `/`, `/courses`, `/courses/{slug}`, `/about`, `/contact`, `/scholarship`, `/blog`, `/blog/{slug}`
- **Student (auth):** `/dashboard`, `/dashboard/courses/{course}/lessons/{lesson}`, lesson complete, `/profile/*`
- **Admin (auth+admin):** `/admin`, `/admin/scholarships`, `/admin/scholarships/{app}`, PATCH status
- **Breeze:** register/login/password/logout/verify-email

---

## 8. Security Status

| Severity | Issue |
|---|---|
| 🔴 Critical | Default admin `password`; pending-enrollment content bypass; IDOR on lesson completion; ambiguous admin middleware |
| 🟠 High | Stored-XSS vector (raw HTML); no rate limiting; verification disabled; fake payment data accepted |
| 🟡 Medium | `APP_DEBUG=true` in env example; hardcoded PII collection; inline authorization; admin counted as student |

---

## 9. Code Quality Notes

- Duplicated progress/enrollment-gate logic across controllers
- Magic status strings (no enums/constants)
- Mixed Laravel conventions (attribute vs classic models)
- Hardcoded business values (WhatsApp #s, prices, stats) in views instead of `settings`/config
- No localization files; Bengali hardcoded
- Tests only cover Phase5 (public) + Phase6 (admin scholarship) happy paths; no auth/enrollment/progress/IDOR coverage

---

## 10. Recommended Next Task

**Admin Enrollment / Manual Payment Approval module** — it closes the critical access-bypass bug, the schema already exists, and it is the prerequisite for monetization. Steps:

1. `Admin\EnrollmentManagementController` + `/admin/enrollments` index/detail/status routes
2. Approve (→`active`, stamp `paid_at`) / reject (→`cancelled`) with `Rule::in` validation
3. Fix gating: only `status='active'` grants access (free previews still open)
4. Fix IDOR on `toggleComplete` (require enrollment)
5. Hard-require `payment_method` + `transaction_id` at enrollment

Follow-ups (in order): Course & Lesson admin CRUD → Blog admin → Settings UI → Live Class Links / Certificates / Consultation Booking (new schema).

---

## 11. Immediate Hygiene Fixes

- Change seeder admin password before any prod deploy
- Delete `IsAdmin` middleware; standardize on one
- Migrate `User` model to `$fillable` convention
- Introduce enums/constants for statuses (`EnrollmentStatus`, `ApplicationStatus`)
- Keep `PROJECT_STATUS.md` updated as features ship
