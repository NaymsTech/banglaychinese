# Project Status

Last Updated: 2026-09-02 17:18
Project: Chinese Learning + Study in China + Mentorship Platform
Stack: Laravel + Filament migration in progress
Status: Discovery / Audit

## Recent fixes applied (2026-09-02)

1. **Study-in-China admin CMS routes fixed** — `routes/web.php` now maps `GET /admin/study-in-china` → `StudyInChinaPageController@edit` and registers the `POST /admin/study-in-china/update-key|update-json` routes the editor view (`resources/views/admin/study-in-china/edit.blade.php`) actually calls. The dead `PUT /` route to a nonexistent `update()` was removed.
2. **Admin password hygiene** — `ADMIN_PASSWORD` added to `.env.example`; `database/seeders/AdminUserSeeder.php` now aborts in production when it is unset.
3. **Status vocabulary split** — new migration `database/migrations/2026_09_02_000000_add_application_status_to_scholarship_applications_table.php` adds nullable `application_status`; `status` is now exclusively the CRM pipeline (`new…closed`), `application_status` carries the Scholarships outcome (`approved`/`rejected`/`null`=pending). Legacy rows remapped; ScholarshipManagement controller + views, Admin dashboard count, and `ScholarshipController@store` updated.
4. **Test harness made runnable** — `tests/TestCase.php` forces `APP_ENV=testing` before boot (CSRF was active in tests); stale Breeze route names (`dashboard` → `dashboard.index`) fixed in 3 auth tests; `ExampleTest` now migrates; email-verification tests explicitly skipped (feature intentionally not enabled). Suite: **30 passed, 3 skipped, 0 failed**; Pint clean on changed files.

---

Scope: discovery/audit of `/Users/naymur/Desktop/banglay-chinese` (branch `feature/study-in-china-rebuild`) followed by the critical fixes listed above. Facts cite file paths. "Missing / Unknown / Needs confirmation" is stated where evidence is absent.

---

## 1. Executive Summary

**What it is:** A monolithic, server-rendered Laravel marketing + learning + lead-gen platform ("Banglay Chinese") for Bangladeshi students wanting to learn Chinese (HSK 1–4) and study in China. UI copy is Bengali (`resources/views/*`, e.g. `layouts/app.blade.php`).

**Already built (works today):**
- Bilingual marketing site: homepage with SEO/JSON-LD (`HomeController.php`), courses listing/detail (`resources/views/courses/*`), Study-in-China landing + services pages, about, blog public views, contact→WhatsApp redirect.
- Auth via Laravel Breeze (`routes/auth.php`, `app/Http/Controllers/Auth/*`) — register/login/logout/password reset/profile. Email verification **disabled**.
- Learning module: published courses → modules → lessons (`database/migrations/2026_07_31_0958*.php`), free-preview gating, enrollment + **manual bKash/Nagad payment approval** (`Admin/PaymentManagementController.php`), student dashboard w/ progress bars, lesson player w/ prev/next + complete toggle (`LessonController.php`).
- Custom Blade admin panel at `/admin` (`layouts/admin.blade.php`): stats dashboard, course/module/lesson CRUD + reordering, services CRUD, blog post CRUD, payment approve/reject, scholarship-applications management, **CRM lead management**, contact messages, settings, CMS editors for Study-in-China & About pages.
- CMS-ish key/value content tables (`settings`, `about_sections`, `study_in_china_sections`) served via `App\Services\SettingsService.php` (cache-backed).

**What is incomplete / broken:**
- **Broken admin route**: `routes/web.php:179-181` route `GET|PUT /admin/study-in-china` → `Admin\StudyInChinaPageController@index` / `@update`, but that controller (read in full, 128 lines) only defines `edit`, `update(StudyInChinaSection)`, `updateByKey`, `updateJson`, `uploadImage`. The sidebar link (`layouts/admin.blade.php` → `route('admin.study-in-china.index')`) therefore hits a nonexistent method → error page. The `admin/study-in-china/edit.blade.php` view + AJAX methods exist but are unreachable. *Needs confirmation this is mid-refactor, but as committed it is broken.*
- Two contradictory status vocabularies on the same table `scholarship_applications`: admin Scholarships screen filters `pending|approved|rejected` (`Admin/ScholarshipManagementController.php`) while CRM uses `new|contacted|consultation_scheduled|application_started|converted|closed` (`Admin/CrmController.php`); `StudyInChinaController.php` inserts `status='new'`, legacy seed/default is `'pending'`. Admin dashboard counts only `pending` (`Admin/DashboardController.php`).
- Mentorship = **marketing copy only**; no schema/UI (no mentor, booking, payment-gateway, messaging tables).
- No quizzes, vocabulary, assignments, live-class scheduling, certificates, or content-access logging tables.
- `public/` (incl. `public/assets/my-picc/*` — dozens of raw personal photos) is **untracked in git**; `public/build` and `public/storage` do not exist → `npm run build` + `storage:link` are deploy prerequisites.
- No queue workers/scheduler config, no Docker/CI, no API routes, email only via `log` mailer.

**Deployable?** Barely — DEPLOY.md documents a manual VPS path and the app runs locally, but several blockers above (broken admin route, dual statuses, untracked `public/`, no built assets, seeded admin password default `change-me-in-prod` via `AdminUserSeeder.php`) must be fixed first.

**Does Filament migration make sense? Yes, but staged.** The custom Blade admin already reimplements many "Filament resource" screens (courses, modules, lessons, posts, services, payments, CRM leads, settings, contact messages). Converting these to Filament reduces hand-rolled CRUD substantially. Caveats: **Laravel `^13.8`** is newer than the L10–L12 that Filament v3/v4 historically target — **Filament-vs-Laravel-13 support is "Needs confirmation"** (network fetch to Packagist timed out during this session); verify before installing.

## 2. Technical Stack

| Item | Value | Evidence |
|---|---|---|
| Laravel | `^13.8` (streamlined skeleton, `bootstrap/app.php`) | `composer.json`; `bootstrap/app.php` |
| PHP | `^8.3` required | `composer.json`; DEPLOY.md "PHP 8.3+" |
| Livewire | **Missing** (no package, no `app/Livewire`) | `composer.json`; grep `filament\|livewire` across `*.json/*.lock` → no matches |
| Filament | **Missing** | same grep; `PROJECT_ANALYSIS_REPORT.md` line 26 |
| Alpine.js | `^3.4.2`, bootstrapped globally in `resources/js/app.js` | `package.json` |
| Tailwind | `^4.0.0` CSS-first (no `tailwind.config.js`) via `@tailwindcss/vite` | `package.json`, `resources/css/app.css` (`@import 'tailwindcss'`) |
| Vite | `^8.0.0` + `laravel-vite-plugin ^3.1` | `package.json`, `vite.config.js` |
| DB driver | default `sqlite`; MySQL/PostgreSQL commented in env | `.env.example` |
| Session / Cache / Queue | `database` / `database` / `database` | `.env.example` |
| Mail | `log` mailer; Mailtrap-style SMTP placeholders | `.env.example` |
| Frontend | Blade + Alpine + Tailwind; **no Vue/React/Inertia/Livewire** | `resources/css/app.css`, `resources/js/app.js`, `package.json` |
| Auth | Laravel Breeze `^2.4` (dev), session-based; email verification dormant (see §6) | `composer.json`, `routes/auth.php`, `app/Models/User.php` (MustVerifyEmail commented out) |
| Roles/permissions | **None** (no spatie). Boolean `is_admin` + string enum `role` | `database/migrations/2026_08_01_000002_add_is_admin_to_users_table.php`, `2026_07_31_100000_add_role_phone_avatar_to_users_table.php` |
| Misc dev deps | `laravel/pail`, `laravel/pao ^1.0.6` *(unfamiliar — Needs confirmation: run `composer show laravel/pao`)*, pint, phpunit `^12.5.12`, collision | `composer.json` |
| Fonts | Google Fonts CDN (Inter/Poppins/Anek Bangla/Hind Siliguri/Noto Sans SC) | `resources/views/layouts/app.blade.php` |
| Version pinning | Exact resolved versions live in `composer.lock` / `package-lock.json` (both present; not re-listed here) | repo root |

## 3. Feature Inventory

Status legend: Done / Partial / Planned / Missing / Unknown. Evidence = file paths.

| Feature | Status | Evidence / Notes |
|---|---|---|
| **Auth** register | Done | `routes/auth.php`, `Auth/RegisteredUserController.php` |
| login / logout | Done | same file; `layouts/admin.blade.php` logout form |
| password reset | Done | `forgot-password`/`reset-password` routes, `Auth/NewPasswordController.php` |
| email verification | Missing | `auth.php` registers **no** `verification.*` routes; `User.php` has `MustVerifyEmail` commented out; controller classes are dead code |
| profile management | Done | `/profile` GET/PATCH/DELETE (`routes/web.php`), Breeze profile views |
| **Roles** admin | Done | `is_admin` bool + `role='admin'`; `AdminMiddleware.php` allows either |
| student | Done | default `role='student'` |
| mentor / consultant / agent / staff | Missing | no columns/tables; `role` is a fixed MySQL-style enum `['admin','student']` |
| **Chinese learning** courses | Done | `Course` model, public + admin CRUD, `StoreCourseRequest.php` |
| lessons | Done | `Lesson` model; admin nested CRUD; slug route (`getRouteKeyName`) |
| levels / HSK levels | Partial | `courses.hsk_level` tinyint + category "HSK Preparation" (`BanglayChineseSeeder.php`); not a formal levels table |
| enrollment | Partial | `Enrollment` + manual approval flow; no unique(user,course) constraint, dedupe done in controller (`CourseController@enroll`) |
| progress tracking | Done | `lesson_progress` unique(user,lesson); dashboard % (`StudentDashboardController.php`) |
| quizzes / vocabulary / flashcards / assignments | Missing | no tables/models |
| live classes | Missing | marketing copy only ("লাইভ ব্যাচ"), no scheduling table |
| content access control | Partial | `LessonController@show` enforces free-preview / pending / active; good IDOR guards present (`toggleComplete` checks enrollment) |
| **Study in China** consultation booking | Partial | forms write to `scholarship_applications` (`StudyInChinaController.php`); no calendar/booking slots |
| student application | Partial | multi-step admission form (`ScholarshipController@store`) w/ field aliasing |
| university / program database | Missing | only free-text `desired_program` |
| scholarship information | Partial | CMS content + landing page; no structured scholarship list |
| document checklist / upload | Missing | no document tables/upload |
| application status | Partial | string `status`; two conflicting vocabularies (see §1, §4) |
| visa guidance | Missing | CMS copy only |
| CRM lead tracking | Partial | `Admin/CrmController.php` search/filter/sort/status/notes/follow-up on `scholarship_applications` |
| **Mentorship** (profiles/approval/search/matching/booking/calendar/messaging/reviews/dashboard) | Missing | nothing beyond marketing copy; `Service` "Elite Success Program" is a package |
| **Admin panel** dashboard | Done | `Admin/DashboardController.php` + stat cards |
| user management | Missing | sidebar has disabled "Students" placeholder (`layouts/admin.blade.php`) |
| content mgmt (blog) | Done | `admin.posts` resource + categories inline route (`routes/web.php`) |
| course management | Done | course/module/lesson CRUD + reorder |
| application management | Done | scholarships + CRM (see vocabulary conflict) |
| mentor/booking/payment-gateway mgmt | Missing | — |
| settings | Done | `Admin/SettingController.php` + `settings` table |
| reports | Missing | — |
| **Payments** gateways (Stripe/PayPal/Flutterwave/Paystack) | Missing | manual only |
| manual payments | Done | bKash/Nagad fields + approve/reject (`Admin/PaymentManagementController.php`) |
| payment records / invoices / subscriptions | Partial/Missing | `enrollments` is the record; no invoice/subscription |
| **Communication** contact form | Done | `ContactController.php` → DB + `wa.me` redirect |
| notifications / email templates / SMS | Missing | no Notifications/Mail classes (no `app/Notifications`, `app/Mail`) |
| messaging | Missing | — |
| WhatsApp links | Done | settings-driven `wa.me` links throughout layout + contact |
| **Public website** homepage/about/services/courses/study-in-china/blog/contact | Done | views under `resources/views/{home,about,courses,study-in-china,blog,pages}` |
| pricing | Partial | prices in course/service records; FAQ copy hardcodes ৳ prices (`HomeController.php`) |
| mentorship page | Missing | homepage hero copy mentions mentorship; no dedicated page |
| testimonials / FAQ | Partial | hardcoded FAQ JSON-LD (`HomeController`); CMS FAQ groups in `study_in_china_sections` |
| **Deployment** env example | Partial | `.env.example` present but missing `ADMIN_PASSWORD`, `APP_URL` commented |
| queue config | Partial | `QUEUE_CONNECTION=database`; no jobs, no worker in prod guide beyond composer `dev` script |
| scheduler | Missing | `routes/console.php` only `inspire`; `app/Console` only `BackfillLessonSlugs` command |
| storage link | Missing | `public/storage` absent (must run `php artisan storage:link`) |
| tests | Partial | see §12 |
| Docker / CI/CD / server config files | Missing | no Dockerfile/docker-compose/.github/nginx conf (glob found none); nginx snippet is inline in `DEPLOY.md` |

## 4. Database Structure

21 tables across 34 migrations (system + domain). Summary:

| Table | Purpose | Important Columns | Relations | Status |
|---|---|---|---|---|
| users | accounts | name, email uq, password, role enum('admin','student'), phone, avatar, is_admin bool, email_verified_at | hasMany enrollments, lesson_progress | Done — dual role/is_admin redundant |
| password_reset_tokens / sessions | Breeze infra | standard | — | Done |
| cache, cache_locks | cache store | standard | — | Done (`2026_*_000001`) |
| jobs, job_batches, failed_jobs | queue | standard | — | Done, unused |
| categories | blog/course taxonomy | name, slug uq, description | posts; courses.category_id nullable | Done |
| posts | blog articles | title, slug uq, category_id (cascade), user_id (nullOnDelete), content, excerpt, hsk_level, featured_image, is_published, published_at | Post→Category/Author | Done — dual publish flags |
| courses | language courses | title, slug uq, description, hsk_level, price dec, thumbnail, is_published, is_featured, category_id, duration_weeks, batch_start/end_date | modules, enrollments, category, lessons (hasManyThrough) | Done — `type`/`consultation_link` dropped (2026_08_11_000000) |
| modules | course sections | course_id (cascade), title, order | →lessons | Done |
| lessons | content units | module_id, title, slug uq **nullable**, video_url, content, order, is_free_preview | →progress | Done — slug backfill via `app/Console/Commands/BackfillLessonSlugs.php` |
| enrollments | manual-payment enroll | user_id, course_id (cascade), status string, payment_method, transaction_id, sender_number, price_paid, paid_at | →user/course | Done — no unique(user,course); status free-string |
| lesson_progress | completion | user_id, lesson_id, completed_at, **unique(user,lesson)** | →user/lesson | Done |
| settings | key/value config | key uq, value text | — | Done |
| scholarship_applications | leads/applications/CRM | name,email,phone, highest_qualification,gpa_cgpa,desired_program,interested_service_id(FK services nullOnDelete),target_intake,hsk_english_level, **legacy NOT-NULL**: target_course, educational_background, statement_of_purpose; message, status string default 'pending', admin_notes, follow_up_date, budget, preferred_consultation_time | →Service | Done but **overloaded** (lead + application + consultation in one row) |
| contact_messages | inquiries | name, phone, email, topic, message, is_read | — | Done |
| about_sections | About CMS | key uq, value, type, group, label, sort_order | — | Done |
| study_in_china_sections | SIC CMS | same shape, longText value, group enum-ish | — | Done |
| services | SIC packages | name, slug uq, short/desc, features json, price, cta_label, duration, status, sort_order | active/ordered scopes | Done (recent rebuild) |

**Schema issues found:**
- **Missing** formal FK: `posts.category_id` cascades but `courses.category_id` is nullOnDelete — acceptable; but there is **no `scholarship_applications.user_id`** (leads unlinked to registered users) and no status-history table (repeatedly needed for CRM audit).
- **Inconsistent naming**: dual auth flags `role` + `is_admin`; dual publish `is_published`+`published_at` on posts; `Lesson` table plural but `LessonProgress`→`lesson_progress` (fine, only note `$table` override in model).
- **Column rename candidates**: `scholarship_applications.target_course/educational_background/statement_of_purpose` are legacy NOT-NULLs that new forms must fake-fill (see comment in `StudyInChinaController.php`) — they should become nullable and semantically clean; `interested_service` label vs `interested_service_id` ok.
- **Nullable-should-not-be**: those three legacy columns above are `string`/`text` **NOT NULL** yet the modern consultation form doesn't naturally populate them (controller duplicates values: `'educational_background' => highest_qualification` etc.).
- **No soft deletes** anywhere (course delete manually cascades progress→lessons→modules→enrollments in `Admin/CourseController@destroy`).
- **No indexes** beyond FKs/unique on frequently-filtered `enrollments.status`, `scholarship_applications.status/created_at` (small data today; fine at this scale).
- Migrations `2026_08_05_000001/000002` defensively wrap `Schema::hasColumn` guards and `2026_08_11_*` are data ops guarded by `Schema::hasTable` — reasonable pattern, though they make schema state only predictable when run in order.

## 5. Routes and Pages

No `routes/api.php` exists. All routes in `routes/web.php` (incl. `require auth.php`), plus `routes/console.php` (inspire only) and health `/up` (`bootstrap/app.php`).

| Method | URL | Controller / Action | Auth | Role | Purpose |
|---|---|---|---|---|---|
| GET | `/` | `HomeController@index` | – | – | Home (SEO) |
| GET | `/courses`, `/courses/{slug}` | `CourseController` index/show | – | – | course catalog/detail |
| POST | `/courses/{course:slug}/enroll` | `CourseController@enroll` | auth+throttle | student | enroll |
| GET | `/checkout/{course}`, `/checkout/confirmation` | `CheckoutController` | auth | student | manual payment pages |
| GET | `/about`, `/about/founder`, `/founder` | `AboutController@index` + redirects | – | – | about |
| GET/POST | `/contact`, `/contact/send` | `ContactController` | throttle | – | contact |
| GET | `/study-in-china` | `ScholarshipController@index` | – | – | SIC landing |
| GET | `/study-in-china/services` + `/{service:slug}` | `ServiceController` index/show | – | – | package pages |
| GET | `/study-in-china/consultation` | `ScholarshipController@consultation` | – | – | form page |
| POST | `/study-in-china/consultation` | `StudyInChinaController@submitConsultation` | throttle | – | lead capture |
| POST | `/study-in-china/apply` | `ScholarshipController@store` | throttle | – | admission form |
| GET | `/blog`, `/blog/{post:slug}` | `PostController` | – | – | blog public |
| 301s | `/product/*`, legacy service slugs → canonical | `Route::redirect` | – | – | SEO migration |
| GET | `/dashboard` | `StudentDashboardController@index` | auth | student | student dashboard |
| GET | `/dashboard/courses/{course}/lessons/{lesson}` | `LessonController@show` | auth | student | lesson player |
| POST | `/dashboard/lessons/{lesson}/complete` | `LessonController@toggleComplete` | auth | student | progress toggle |
| GET/PATCH/DELETE | `/profile` | `ProfileController` | auth | student | profile |
| `/admin` (all below) | group `['auth','admin']` prefix `admin` | — | auth | **admin** | custom admin |
| GET | `/admin` | `DashboardController@index` | | | stats |
| GET, GET, PATCH | `/admin/scholarships[.../status]` | `ScholarshipManagementController` | | | applications mgmt |
| GET, GET, POST, POST | `/admin/payments[.../approve, /reject]` | `PaymentManagementController` | | | manual payments |
| GET/POST/PUT/DELETE/PATCH | `/admin/courses[...]` + nested `/modules` + `/lessons` | Admin `CourseController`/`ModuleController`/`LessonController` | | | LMS CRUD + reorder |
| GET/POST/PUT/DELETE/PATCH | `/admin/services[...]` | `Admin\ServiceController` | | | package CRUD |
| resource | `/admin/posts` | `Admin\PostController` | | | blog CRUD |
| POST | `/admin/categories` | inline closure → `Category` | | | quick category |
| GET/GET/DELETE | `/admin/contact-messages[...]` | `ContactMessageController` | | | inbox |
| GET, PUT | `/admin/study-in-china` | `StudyInChinaPageController@index/@update` — **methods don't exist** | | | 🔴 broken (§1) |
| GET/PUT | `/admin/crm/{lead}` | `CrmController` index/show/update | | | CRM |
| GET/PUT | `/admin/about-page` | `AboutPageController` | | | About CMS |
| GET/PUT | `/admin/settings` | `SettingController` | | | settings |
| — | Breeze auth routes | `Auth\*` | guest/auth | — | login/register/reset/logout |

Public pages: all `/`-level above. Student pages: `/dashboard*`, `/profile*`, `/checkout*`. Admin pages: `/admin*`. Mentor pages: none. API: none.

## 6. Authentication and Authorization

- **Registration/login**: Breeze session flow. `RegisteredUserController.php` + `Auth/LoginRequest.php`; `routes/auth.php`. Guest middleware guards those.
- **Roles**: stored **on `users`** as enum `role` (`admin`,`student`) **and** boolean `is_admin` (`2026_07_31_100000`, `2026_08_01_000002`). No roles table, no spatie.
- **Authorization**: one custom middleware `AdminMiddleware` (`app/Http/Middleware/AdminMiddleware.php`, aliased `'admin'` in `bootstrap/app.php`) that passes if `is_admin || role==='admin'`, else redirects to `/dashboard` with error. Route groups apply `['auth','admin']`.
- **Policies/Gates**: none (`app/Policies` doesn't exist). Authorization is inline in controllers (e.g., `LessonController` aborts, `PaymentManagementController` `abort_unless`).
- **Can Filament use the User model?** Yes — `User extends Authenticatable` implements standard contracts (`Authenticatable`, `Notifiable`). Filament needs an `isAdmin()`-style check → existing `AdminMiddleware` logic must become a Filament access check (`canAccessPanel`) reusing `$user->is_admin || $user->role === 'admin'`.
- **Fix before Filament**: (1) decide single source of truth for role (recommend extending `role` enum → `admin|staff|student` later, drop or keep `is_admin` as cached flag); (2) expose email verification if Filament wants verified-only access; (3) resolve the two conflicting status vocabularies (§1) before building application resources; (4) note `User::$fillable` currently includes `role`/`is_admin` (mass-assignment risk if any bulk-update path ever uses `$request->all()` — today none does; keep it that way or use a proper admin-only update path).

## 7. Current Admin Area

- **Type**: fully **custom Blade** admin (`layouts/admin.blade.php` + `resources/views/admin/**`, ~28 views), route group `['auth','admin']` under `/admin`. No Filament, no Livewire, no API-only admin.
- **What exists** (sidebar order, `layouts/admin.blade.php`): Dashboard, (Students = **disabled placeholder**), Courses (+nested modules/lessons), Services, Payments, Blog, Scholarships, Study in China (link **broken**, see §1), CRM, About Page, Settings, Messages.
- **Recommendations**:
  - **→ Filament resources**: Courses (+ Modules/Lessons relation managers), Services, Posts/Categories, Scholarship Applications + CRM leads, Enrollments/Payments (status actions), Contact Messages, Users/Students, Settings → `Filament\Settings\Pages` or simple resource.
  - **→ Remain custom**: the **public site views only**; the SIC/About "section editor" UIs should be rebuilt as Filament custom pages **or** kept as-is once routes are fixed — decide once; don't keep both.
  - **→ Delete/dead**: `EmailVerification*` controllers (no routes), the disabled Students sidebar item (or wire it up), duplicate controller methods unreachable (e.g., `StudyInChinaPageController@updateByKey/updateJson/uploadImage` have **no routes**), `errors/` variants if unused.
  - **→ Rebuild**: the Study-in-China CMS admin area (currently inconsistent), status handling (single enum + history), any future "Reports".

## 8. Filament Migration Assessment

- **Laravel version compatible?** Laravel is `^13.8` (`composer.json`). Filament v3 targets L10/11; v4 (2025 era) targets L10–L12 + Livewire 3. **Whether current Filament supports Laravel 13 = Needs confirmation** (Packagist fetch timed out). Action: run `composer require filament/filament` on a branch and check solver output, or inspect `vendor` constraints against `illuminate/*: ^13`.
- **PHP version compatible?** `^8.3` — satisfies Filament (needs ≥8.1/8.2).
- **Livewire required?** Yes — Filament v3/v4 bundle Livewire 3 as a dependency. None present today; composer will pull it. No conflicts (no existing Livewire).
- **Tailwind required?** Filament ships its **own** compiled CSS + publishes vendor assets. Your `resources/css/app.css` (Tailwind v4) is for the public site. Risk: running **two Tailwind builds** and duplicate Alpine instances. Filament v4 + your Alpine 3.4 (`resources/js/app.js`) generally coexist, but you must keep Filament pages out of the public layout and vice-versa. Recommended: leave `layouts/app.blade.php` for the storefront; give Filament its own panel provider (`/admin`) and retire `layouts/admin.blade.php` progressively.
- **Conflicts?** (a) Custom `admin` middleware alias vs Filament panel auth — Filament has its own middleware; your `AdminMiddleware` check must be mirrored in `canAccessPanel()`. (b) Two status vocabularies must be unified before building the applications resource. (c) `categories` route vs Filament resources naming — fine.
- **Version recommendation**: install latest stable (v4 line as of writing), **only if** its `composer.json` allows Laravel 13 — otherwise pin to a release whose changelog lists L13 support or plan an LTS Laravel 12 downgrade. Do **not** install until verified.
- **Fresh vs upgrade**: fresh install (nothing installed).
- **Safest path**: (1) fix the current-code blockers (§1) on this branch; (2) feature-test current behavior (PHPUnit suite green) as a baseline; (3) `composer require filament/filament` on a **feature branch** and verify the solver + `/admin` panel boots with `php artisan about`, `route:list`, and a smoke test; (4) migrate one resource (e.g. Services) end-to-end before mass conversion; (5) convert admin in dependency order; keep public routes/views untouched.

## 9. Frontend / UI Assessment

- **Layouts**: `layouts/app.blade.php` (public, `x-app-layout` via `App/View/Components`), `layouts/admin.blade.php` (custom sidebar admin shell using `@yield`), `layouts/guest.blade.php` (auth). Student dashboard is a bespoke `x-app-layout` page (`resources/views/dashboard.blade.php`) with its own sidebar; lesson player at `dashboard/lessons/show.blade.php`.
- **Components**: Breeze set (`dropdown`, `modal`, `text-input`, `input-label`, buttons…) + `components/course-card.blade.php`, `application-logo`. Anonymous `x-` components plus class-based `AppLayout`/`GuestLayout` (`app/View/Components/`).
- **CSS**: Tailwind **v4** with rich `@theme` tokens in `resources/css/app.css` (emerald `primary` + crimson `accent`, Bangla/Chinese font stacks, line-height rules). **However**, views heavily hardcode hex greens like `#0F5132`/`#25D366` (e.g. `layouts/app.blade.php`, `dashboard.blade.php`) instead of the tokens — inconsistent theming; `vite.config.js` also auto-injects Bunny font CSS for "Instrument Sans", which isn't in the declared stacks (minor smell).
- **JS**: single `resources/js/app.js` → Alpine only.
- **Vite**: inputs `app.css` + `app.js` (`vite.config.js`); `public/build` currently **absent** → assets must be built before serving.
- **No CDN runtime libs** (grep for bootstrap/jquery/cdnjs/unpkg across views → none; only Google Fonts).
- **Admin theme**: reuses the public Tailwind build + custom sidebar. Switching to Filament means this layout is retired for Filament pages.
- **Mobile responsiveness**: public layout has sticky mobile bottom-nav + responsive breakpoints; dashboard has mobile logout but sidebar hidden on <lg (fine). Risks are moderate: hardcoded color duplication, very large single-page views (`home`, `study-in-china/index`) that will be painful to restyle, no dark mode, and no consolidated UI kit (buttons repeated per page).

## 10. Business Logic and Services

- **Enrollment/payment**: `CourseController@enroll` (transaction, price from DB, dup check) + `Admin/PaymentManagementController` (approve/reject, `abort_unless` state guards) — decent; statuses are magic strings.
- **Lesson access**: `LessonController` — good enforcement (free preview → pending → active), prev/next + progress, cross-course check; solid.
- **Lead capture/application**: `StudyInChinaController@submitConsultation` + `ScholarshipController@store` — **duplicated field normalization & mapping** (two near-identical validation blocks) — candidate for one `StoreConsultationRequest` + shared service/action. Legacy NOT-NULL column faking inline (documented in a code comment).
- **CMS**: key/value section editors; `SettingsService` (cache-backed get/set/flush) is the only "service" class; page controllers re-implement the same group-by `StudyInChinaSection` mapping in 2–3 places (`ScholarshipController`, `StudyInChinaController`) — extract to a service/action or model method.
- **Too thin**: nothing formal for status workflows, notifications (none), or reporting; progress % computed in dashboard only.
- **Missing**: no service/action classes for payment confirmation notifications, lead dedupe/merge, application timeline/history, uploads (only settings/course thumbnails use `Storage`), and no form request for consultation (inline `validate()`).
- **Should become Actions/Services**: lead submission (normalize + persist + notify), enrollment checkout (already 90% there), payment approval side-effects (unlock + notify), SIC section grouping (`Service`/`Action` + cached).

## 11. Integrations

| Integration | Status | Notes / Evidence |
|---|---|---|
| Payment gateways | Missing | manual bKash/Nagad confirmation only (`enrollments.payment_method` in `2026_07_31_095809`, settings numbers in `SettingsSeeder.php`) |
| Email | Missing in use | `MAIL_MAILER=log`; no Mailable/Notification classes (`app/Mail`, `app/Notifications` absent) |
| SMS | Missing | — |
| WhatsApp | Present (outbound links only) | `wa.me` links from settings; `ContactController` redirects to WhatsApp w/ prefilled text |
| Video | Field-level only | `lessons.video_url` string (YouTube-style embeds expected) |
| Zoom/Meet/Tencent | Missing | no integration |
| AI APIs | Missing | marketing copy mentions "AI word map" only |
| Storage | Local `public` disk | uploads via `Storage::disk('public')->store(...)` (`Admin/CourseController`, `SettingController`, `StudyInChinaPageController@uploadImage`); AWS env vars unused |
| OAuth / WeChat social login | Missing | — |
| Analytics | Config-ready | GA4 + FB Pixel injected from settings (`layouts/app.blade.php`) |

## 12. Testing and Quality

- **Existing tests** (all under `tests/`): Breeze-generated `Feature/Auth/*` (auth, registration, password, verification) + `ProfileTest`; `Feature/Phase5PublicPagesTest` (public page 200s); `Feature/Phase6AdminTest` (guest/student blocked from `/admin`, admin dashboard access, scholarship management access + create/status happy paths — 116 lines). `tests/Unit/ExampleTest` is a stub. Runner: PHPUnit via `phpunit.xml` (sqlite `:memory:`, array drivers).
- **Missing coverage**: enrollment→checkout→payment approve/reject gating; lesson access matrix (guest/free/pending/active/other-course/IDOR); progress toggle ownership; consultation & admission form validation and normalization; service pages + redirects; CRM update rules; contact form; settings persistence. `Phase5/6` files are smoke-level.
- **Quality issues**: duplicated validation in the two SIC submission controllers; duplicated section-grouping map; magic status strings across 3 files (no enums — `app/Enums` doesn't exist); dual role flags; inline closures in routes (`categories.store`) vs controllers; very large view files; hardcoded hex colors; stale/dead code (`EmailVerification*`, unreachable SIC controller methods, broken SIC admin routes).
- **Risky areas**: the `scholarship_applications` schema juggling (legacy NOT NULL columns + two status sets) and the **uncommitted** `public/` assets + the whole in-progress "Study in China rebuild" branch state.

## 13. Security Review

- **Mass assignment**: all models use explicit `$fillable` (good). Risk: `User::$fillable` includes `role`,`is_admin` — only safe while no admin path does `User::update($request->all())` (none today; keep guarded).
- **Missing authorization**: admin routes all sit behind `['auth','admin']` ✓; student endpoints check enrollment ✓ (`LessonController`). **No policies**, and the `admin/study-in-china` broken routes are the only gate anomaly.
- **Exposed secrets**: `.env` not inspected (by design). `.env.example` has `SESSION_SECURE_COOKIE=true`, `APP_DEBUG=false` (good) but **no `ADMIN_PASSWORD` entry** even though `AdminUserSeeder` reads `env('ADMIN_PASSWORD','change-me-in-prod')` → a fresh deploy seeds a **publicly-known admin password** unless the env var is added. High priority.
- **Unsafe uploads**: image uploads validated (`image|max:2048|mimes:jpeg,png,jpg,webp`) ✓; `StudyInChinaPageController@uploadImage` has `image` + mimes ✓, but deletes by `file_exists(public_path($section->value))` on a DB-stored path — path is server-written, acceptable; no user-facing uploads exist.
- **Validation**: mixed FormRequests (courses/services/posts/enroll/contact) and inline `validate()` (consultation, CRM, settings) — all rules present and typed; SIC `updateByKey` uses `'data'=>'required'` fine.
- **Content XSS**: `courses/lessons` content saved after `strip_tags` allowlist (Store/UpdateCourseRequest, StoreServiceRequest) ✓; views render with `{!! !!}` where intended (SIC CMS values are admin-authored). `home.blade`/`layouts/app.blade.php` use escaped `{{ }}` for settings + `{!! !!}` only for curated JSON-LD. Residual risk is low but audit `{!!` occurrences during Filament migration.
- **IDOR**: `toggleComplete` + lesson `show` guard cross-course and enrollment (good — fixed since the 2026-08-01 status report).
- **Unprotected API**: none exists. Health `/up` public (intended).
- **Other**: `public/assets/my-picc/*` contains dozens of raw personal photos in a **public web dir** and the entire `public/` tree is **untracked** — decide: gitignore or move to storage; never commit private photos. `throttle` present on forms (3/min–5/60s). No CSRF bypasses observed (all POSTs use forms/`@csrf`).

## 14. Deployment Readiness

| Check | State | Evidence |
|---|---|---|
| Runs locally | Likely yes | standard skeleton; `composer` dev script runs server+queue+mail; sqlite default |
| Migrations complete | Yes, cohesive | 34 ordered migrations; last 6 are the service refactor |
| Seeders | Yes | `DatabaseSeeder` → Settings/Admin/BanglayChinese/Service/About + test user |
| Admin user creation | Partial | `AdminUserSeeder` — default password `change-me-in-prod`, env-overridable but `ADMIN_PASSWORD` **not in `.env.example`** |
| Queue/scheduler | No | no jobs; `routes/console.php` default; nothing schedules |
| Production build | Needs action | `public/build` absent → `npm run build` required; `DEPLOY.md` covers it |
| Storage link | Missing | `public/storage` absent → `php artisan storage:link` required |
| `.env.example` complete | Partial | missing `ADMIN_PASSWORD`; `APP_URL` commented; production DB lines commented (intended) |
| Broken surface | Present | Study-in-China admin CMS routes fatal; untracked `public/` must be deployed separately |
| Docs | Present | `DEPLOY.md` (nginx snippet, steps), `README.md` is stock Laravel |

## 15. Missing Pieces for a Deployable MVP (prioritized)

| Priority | Missing Item | Why It Matters | Suggested Fix |
|---|---|---|---|
| Critical | Working Study-in-China admin CMS (routes vs controller methods mismatch, `routes/web.php:179-181`) | Admin sidebar link 500s; content can't be edited | Align routes to `edit`/`updateByKey`/`updateJson`/`uploadImage` (or add `index`), then feature-test |
| Critical | Admin default password exposure (`AdminUserSeeder`) | default creds if `ADMIN_PASSWORD` unset | add `ADMIN_PASSWORD` to `.env.example` + force-set in seeder |
| Critical | Single status vocabulary for `scholarship_applications` | Scholarships screen vs CRM disagree → misrouted leads | enum/const + migration mapping old values + history table |
| High | Untracked `public/` + personal photos in `public/assets/my-picc/` | assets lost on clone/deploy; privacy | move to storage or gitignore; rebuild build outputs in CI |
| High | Legacy NOT NULL columns faked by new forms (`StudyInChinaController.php`) | fragile data duplication | make nullable in migration; drop column aliasing |
| High | No queue/notifications | approvals/leads invisible to staff, no email | DB queue already set; add notifications/mailables on key events |
| Medium | No tests for money/access flows | regressions in gating = revenue loss | feature tests: enroll→approve→unlock, IDOR, consultation submit |
| Medium | Duplicate form logic + missing `StoreConsultationRequest` | drift between the two submission forms | extract shared FormRequest + action |
| Medium | Student/User admin missing | no way to manage users | Filament Users resource (phase 5) |
| Low | Magic strings/colors, dead code (EmailVerification, unreachable methods) | maintenance burden | enums, `resources/css` tokens, cleanup pass |

## 16. Recommended Filament Resources (for the target plan)

| Resource | Action | Notes |
|---|---|---|
| Users / Students | Use existing `User` model + table | admin-only; add `role` column; note Students menu placeholder today |
| Roles | Create **new** roles table (or rely on `role` enum + spatie later) | admin-only; decide after status unification |
| Courses / Modules / Lessons | Use existing models; Modules+Lessons as **RelationManagers** under Course | replays `Admin/CourseController`+nested UI |
| Categories | Existing model | used by posts & courses |
| Services | Existing model | already CRUD'd in Blade — easiest first resource |
| Enrollments / Payments | Existing model | status actions approve/reject + filters; port `PaymentManagementController` rules |
| Posts (Blog) | Existing model | keep `category_id`, `published_at` |
| Scholarship Applications / Leads (CRM) | Existing model | one resource w/ consistent status enum; notes + follow-up fields |
| Consultation Bookings | Existing (same table) or split new `consultations` table | decide after §15 row 3 |
| Contact Messages | Existing model | read/unread + delete |
| Contact form / Settings | Existing `settings` table | Filament custom page |
| Universities / Programs / Scholarships (DB) | **Create new tables** if productized | out of MVP scope |
| Mentors / Sessions / Bookings / Reviews / Messages / Payments-gateway / Invoices / Coupons | **Create new tables** | mentorship module = new build |
| Reports / Activity logs | New | later phase |

All above are admin/staff-only unless noted. **Do not** create parallel "pages" resources that duplicate `about_sections`/`study_in_china_sections` key/value editors — rebuild those as one Filament settings-style page.

## 17. Recommended Migration Plan (10 phases)

- **P1 Stabilize**: fix broken SIC admin routes, admin password env, unify application statuses, make legacy SIC columns nullable, move/ignore `my-picc`. Files: `routes/web.php`, `Admin/StudyInChinaPageController.php`, seeders, new status migration. Test: admin smoke + seed. Not yet: any Filament install.
- **P2 DB/models**: add `UserStatus`-style enums as PHP enums, optional `application_status_history`, unique index on `enrollments(user_id,course_id)` if desired, relation cleanups on `User`/`ScholarshipApplication`. Test: `php artisan migrate:fresh --seed`.
- **P3 Install/configure Filament**: **verify L13 compat first**; `composer require filament/filament` on feature branch; create panel provider at `/admin`, `canAccessPanel()` mirroring `AdminMiddleware`; publish assets; smoke boot. Do not delete custom admin yet.
- **P4 Admin resources**: Users, Services, Courses+Modules+Lessons, Posts, Categories, Settings page; migrate one at a time; retire matching Blade pages as parity is proven. Keep old routes until each is green.
- **P5 Student portal**: decide whether dashboard remains Blade or becomes Filament pages outside panel; likely keep Blade (public) and only move admin.
- **P6 Study-in-China**: one ScholarshipApplication/CRM resource with unified statuses + notes/follow-up; consultations + interested service relation; replace `Admin/CrmController` & `ScholarshipManagementController`.
- **P7 Mentorship**: new schema + resources (mentor profiles, session requests, bookings) — new product phase, after MVP.
- **P8 Payments/notifications**: keep manual bKash/Nagad first; add Notifications/Mail on approve/reject & new-lead; invoices later.
- **P9 Testing/QA**: fill the §12 gaps; run `php artisan test`, Pint, `npm run build`.
- **P10 Deploy**: storage:link, build assets, queue worker for database queue, `.env` hygiene, remove custom admin layout when Filament parity is complete.

## 18. Immediate Next Actions

1. **Reproduce the admin break**: open `/admin/study-in-china` — confirm the `index()` missing-method error; reason: routes at `routes/web.php:179-181` vs controller methods (evidence above).
2. **Add `ADMIN_PASSWORD` to `.env.example`** — and change the seeder default (`database/seeders/AdminUserSeeder.php`).
3. **Map both status vocabularies** — list distinct `status` values in `scholarship_applications` (`ScholarshipManagementController` vs `CrmController` vs insert sites), then design one enum.
4. **Make legacy SIC columns nullable** — new migration for `target_course`, `educational_background`, `statement_of_purpose`; remove faking in `StudyInChinaController@submitConsultation`.
5. **Confirm Filament↔Laravel 13 support**: `composer show` / changelog check, or `composer require filament/filament --dry-run` on a branch.
6. **Deal with `public/`**: `git status` for `public`, review `.gitignore`, move `my-picc` photos out of the webroot.
7. **Run the existing suite** as a baseline: `php artisan test` (expect Phase5/6 + Breeze tests).
8. **Run `composer show laravel/pao`** to learn what it is (unknown dev dep).
9. **Audit `{!! !!}` usages** across views before any admin rewrite (XSS boundary).
10. **Regenerate/keep `PROJECT_STATUS.md` current** with this report (this file).

## 19. Questions for Me

1. Is the "Study in China admin CMS" currently mid-refactor on purpose (route/controller mismatch), and should I align routes to the existing `edit`/`updateByKey` methods — or finish an intended new `index`/`update` design?
2. Which status vocabulary is canonical for leads: the **CRM pipeline** (`new→…→converted/closed`) or the **Scholarships** `pending/approved/rejected`?
3. Do you want email verification **enabled** before or after the Filament move?
4. Are the raw photos under `public/assets/my-picc/` meant to be public site content, or stray personal files to remove/ignore?
5. What is the production database target — MySQL (DEPLOY.md) or SQLite, and do you have a host in mind (this affects queue/worker + Filament deploy)?
6. For mentorship: should it become a real module soon (new tables), or is it still just a marketing promise in the MVP?
7. Do you want the Filament panel at the **same `/admin` URL** (replacing the custom admin) or a new URL while the old admin stays?
8. Is bKash/Nagad manual verification the only payment method for the MVP, or should an API gateway (e.g., Stripe/ShurjoPay/SSLCommerz) be on the roadmap?
9. Who besides the main admin needs panel access (staff/consultant accounts), i.e., do we need roles beyond `admin`/`student` before Filament?
10. May I update `PROJECT_STATUS.md` (and optionally fold in `PROJECT_ANALYSIS_REPORT.md`) once you approve leaving plan mode — or is that file owned by another process?

## 20. Manual Notes

<!-- Reserved for human/owner notes. No manual notes were present in the previous version of this file; nothing was overwritten. -->
