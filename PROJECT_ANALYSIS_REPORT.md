# Banglay Chinese — Comprehensive Application Report

> **Prepared by:** Senior Laravel Architect / DevOps Analysis
> **Date:** 8 Aug 2026
> **Scope:** Read-only inspection of the entire `banglay-chinese` project. No files were modified except this report.

---

## 1. HIGH LEVEL OVERVIEW

| Aspect | Detail |
|---|---|
| **Framework** | Laravel (modern skeleton — `bootstrap/app.php`, `bootstrap/providers.php`, no `app/Http/Kernel.php`) → Laravel 11/12-era structure |
| **PHP** | PHP 8.2+ required (Laravel 11/12 requirement); deploy target is a standard PHP 8.2+ host (see DEPLOY.md) |
| **Architecture** | Monolithic, server-rendered MVC. No API-first split, no microservices, no headless frontend. |
| **Auth** | Laravel Breeze scaffold (session-based, `routes/auth.php`, `resources/views/profile/*`, `resources/views/auth/*` expected) |
| **Authorization** | Simple boolean `users.is_admin` + custom `AdminMiddleware`. **No** Gate/Policy files exist. |
| **Frontend** | Blade + **Tailwind CSS** + **Alpine.js** (Breeze default), compiled via **Vite**. No Vue, React, or Livewire. |
| **Build tool** | Vite (`vite.config.js`) with Laravel + Tailwind plugins |
| **DB** | SQLite/MySQL via standard Laravel config (migrations in `database/migrations`) |
| **Queue** | ❌ None. No Queue jobs, no `queue` config beyond defaults, no workers. |
| **Cache** | ❌ Default `file` driver only. `SettingsService` performs per-request caching via static property. |
| **Storage** | `public` disk used for uploads (e.g. Study-in-China section images stored at `storage/study-in-china/...` with `public` visibility). No S3. |
| **Localization** | Partial. `__()` used in `layouts/navigation.blade.php`; site copy is hard-coded **Bengali/Bangla** across blades. No `lang/` translations, no locale switching. |
| **Service Providers** | Standard Laravel providers in `bootstrap/providers.php` + `AppServiceProvider`. No custom provider registrations of note. |
| **Packages (composer)** | Laravel Breeze (auth), no heavy third-party packages observed (no Spatie, no Cashier, no Filament, no Nova). |
| **Third-party integrations** | None server-side. Contact/scholarship forms write to DB only; WhatsApp links (`wa.me`) hard-coded in seeder CTAs. |
| **API integrations** | None. No `routes/api.php` consumption; no external API clients found. |
| **Deployment assumptions** | `DEPLOY.md` present — classic VPS/shared-host + `artisan migrate --seed` + Vite build. Uses incrementally-named migrations (e.g. `2026_08_05_*`) indicating ongoing feature development. |

**Key coding patterns**
1. `prefix('admin')` + `name('admin.')` route groups with `['auth','admin']` middleware.
2. Form Request classes for key models (`StorePostRequest`, `UpdatePostRequest`, `StoreEnrollmentRequest`, `StoreContactMessageRequest`, `StoreCourseRequest`, `UpdateCourseRequest`).
3. Public forms throttled (`throttle:3,60` etc.) to prevent spam.
4. Settings/content CMS via key-value tables (`settings`, `about_sections`, `study_in_china_sections`) consumed through `SettingsService::get()`.
5. Blade `x-app-layout` component (Breeze) with `@push('meta')` for per-page SEO.

---

## 2. ROUTES (complete inventory — from `routes/web.php` + `routes/auth.php`)

### Public (guest)
| URI | Controller@Method | Middleware | Name |
|---|---|---|---|
| `/` | HomeController@index | — | `home` |
| `/courses` | CourseController@index | — | `courses.index` |
| `/courses/{slug}` | CourseController@show | — | `courses.show` |
| `/about` | AboutController@index | — | `about` |
| `/about/founder` | closure → redirect about | — | `about.founder` |
| `/founder` | closure → redirect about | — | `founder` |
| `/contact` | ContactController@index | — | `contact` |
| `/contact/send` | ContactController@send | throttle:3,1 | `contact.send` |
| `/blog` | PostController@index | — | `posts.index` |
| `/blog/{post:slug}` | PostController@show | — | `posts.show` |
| `/study-in-china` | **ScholarshipController@index** ⚠️ (passes `$services` only; view needs `$sections` → silently renders hard-coded defaults) | — | `study-in-china` |
| `/study-in-china/services` | ScholarshipController@services | — | `study-in-china.services` |
| `/study-in-china/consultation` | ScholarshipController@consultation | — | `study-in-china.consultation` |
| `/study-in-china/consultation` (POST) | StudyInChinaController@submitConsultation | throttle:3,60 | `study-in-china.consultation.store` |
| `/study-in-china/apply` (POST) | ScholarshipController@store | throttle:3,60 | `study-in-china.apply` |
| `/scholarship` | redirect → `/study-in-china` (301) | — | — |
| `/scholarship/apply` | redirect → `/study-in-china/consultation` (301) | — | — |

### Authenticated (student)
| URI | Controller@Method | Middleware | Name |
|---|---|---|---|
| `/dashboard` | StudentDashboardController@index | auth | `dashboard.index` |
| `/dashboard/courses/{course:slug}/lessons/{lesson:slug}` | LessonController@show | auth | `dashboard.lessons.show` |
| `/dashboard/lessons/{lesson}/complete` (POST) | LessonController@toggleComplete | auth | `dashboard.lessons.complete` |
| `/courses/{course:slug}/enroll` (POST) | CourseController@enroll | auth, throttle:5,60 | `courses.enroll` |
| `/checkout/{course:slug}` | CheckoutController@show | auth | `checkout.show` |
| `/checkout/confirmation` | CheckoutController@confirmation | auth | `checkout.confirmation` |
| `/profile` (GET/PATCH/DELETE) | ProfileController (Breeze) | auth | `profile.edit/update/destroy` |

### Admin (`/admin/*` — middleware `auth` + `admin`)
| URI | Controller@Method | Name |
|---|---|---|
| `/admin` | Admin\DashboardController@index | `admin.dashboard` |
| `/admin/scholarships` | Admin\ScholarshipManagementController@index | `admin.scholarships.index` |
| `/admin/scholarships/{application}` | …@show | `admin.scholarships.show` |
| `/admin/scholarships/{application}/status` (PATCH) | …@updateStatus | `admin.scholarships.status` |
| `/admin/payments` | Admin\PaymentManagementController@index/show/approve/reject | `admin.payments.*` |
| `/admin/courses` + CRUD + toggle-published/featured | Admin\CourseController | `admin.courses.*` |
| `/admin/courses/{course}/modules` + move up/down | Admin\ModuleController | `admin.courses.modules.*` |
| `/admin/courses/{course}/modules/{module}/lessons` + CRUD + move up/down | Admin\LessonController | `admin.courses.modules.lessons.*` |
| `/admin/posts` (resource, no show) | Admin\PostController | `admin.posts.*` |
| `/admin/categories` (POST JSON) | closure (inline) | `admin.categories.store` |
| `/admin/contact-messages` | Admin\ContactMessageController | `admin.contact-messages.*` |
| `/admin/study-in-china` (GET/PUT) | Admin\StudyInChinaPageController@index/update | `admin.study-in-china.index/update` |
| `/admin/crm`, `/admin/crm/{lead}` (GET/PUT) | Admin\CrmController@index/show/update | `admin.crm.*` |
| `/admin/about-page` (GET/PUT) | Admin\AboutPageController@index/update | `admin.about.*` |
| `/admin/settings` (GET/PUT) | Admin\SettingController@edit/update | `admin.settings.edit/update` |

### Auth routes (`routes/auth.php`)
Standard Breeze: login, register, forgot-password, reset-password, verify-email, confirm-password.

### Dynamic routes
- Model-bound slugs: `courses/{course:slug}`, `blog/{post:slug}`, `lessons/{lesson:slug}`.
- Numeric scoped params: `where('application','[0-9]+')` etc.

---

## 3. CONTROLLERS

### Public-side
| Controller | Purpose / Key methods | Business logic | Related models / views |
|---|---|---|---|
| `HomeController` | Landing page (`index`) | Aggregates courses/settings; home hero + roadmap + filters | Course, Setting; `views/home.blade.php` |
| `CourseController` | `index`, `show`, `enroll` | List courses with filters; show course detail incl. modules/lessons; auth-gated enroll w/ throttle | Course, Module, Lesson, Enrollment, User; `courses/*` |
| `CheckoutController` | `show`, `confirmation` | Checkout page for enrolled course; confirmation screen | Enrollment, Course; `checkout/*` |
| `AboutController` | `index` | Reads `about_sections` key-value groups | AboutSection; `about/show.blade.php` |
| `ContactController` | `index`, `send` | Contact form validation + persistence; throttle | ContactMessage, StoreContactMessageRequest; `pages/contact.blade.php` |
| `PostController` | `index`, `show` | Blog listing + single post by slug | Post, Category; `blog/*` |
| `ScholarshipController` | `index`, `services`, `consultation`, `store` | Study-in-China pages + form submit. **⚠️ `index` passes only `$services` — view expects `$sections`** | Course (services), ScholarshipApplication; `study-in-china/*` |
| `StudyInChinaController` | `index` (🧟 **never routed — dead code**), `submitConsultation` | Builds `$sections` from DB; stores consultation into `scholarship_applications` | StudyInChinaSection, ScholarshipApplication |
| `StudentDashboardController` | `index` | Student home w/ enrolled courses, lessons, progress | Enrollment, Course, LessonProgress; `dashboard.blade.php` |
| `LessonController` | `show`, `toggleComplete` | Renders lesson w/ chapter navigation; toggles LessonProgress | Lesson, LessonProgress; `dashboard/lessons/show.blade.php` |
| `ProfileController` | Breeze CRUD | Account/profile edit | User; `profile/*` |

### Admin-side
| Controller | Purpose |
|---|---|
| `Admin\DashboardController` | Stats: activeCourses, pendingScholarships, publishedPosts |
| `Admin\CourseController` | Course CRUD, publish/feature toggles |
| `Admin\ModuleController` | Module CRUD + reorder (up/down) |
| `Admin\LessonController` | Lesson CRUD + reorder (up/down) |
| `Admin\PostController` | Blog post CRUD (resource except `show`) |
| `Admin\PaymentManagementController` | Enrollment/payment review + approve/reject |
| `Admin\ScholarshipManagementController` | Scholarship applications list/counts by status + updateStatus |
| `Admin\ContactMessageController` | Contact messages inbox (index/show/destroy) |
| `Admin\StudyInChinaPageController` | **CMS for the Study-in-China landing page** — edit key-value sections, incl. JSON + image upload (stores to `storage/study-in-china`) |
| `Admin\CrmController` | **CRM over `scholarship_applications`** — lead list w/ status counts, detail, update notes/follow-up |
| `Admin\AboutPageController` | CMS for About page |
| `Admin\SettingController` | Global site settings |

**Dependencies note:** Admin controllers rely on Eloquent directly (no repository layer). Form Requests exist for: Course, Post, ContactMessage, Enrollment. ScholarshipController `store` validates inline (`Request`), not a Form Request.

---

## 4. MODELS

| Model | Fillable | Casts | Traits | Relationships | Scopes / Events / Observers |
|---|---|---|---|---|---|
| `User` | — role, phone, avatar, is_admin | — | Breeze: HasFactory, Notifiable | enrollments/courses (implied) | — |
| `Course` | — incl. category, featured, duration, type, batch_dates | — | HasFactory | modules, lessons (hasManyThrough implied), enrollments | — |
| `Module` | — | — | — | belongsTo Course; hasMany Lessons | — |
| `Lesson` | — incl. slug | — | — | belongsTo Module; progress | — |
| `LessonProgress` | — | — | — | belongsTo Lesson/User | — |
| `Enrollment` | — incl. price, paid, paid_at, sender_number | — | — | belongsTo User/Course | — |
| `Post` | — incl. user_id, excerpt, published_at | — | HasFactory | belongsTo Category/User | — |
| `Category` | name, slug | — | — | hasMany Posts | — |
| `ContactMessage` | topic/name/email/phone/message | — | — | — | — |
| `Setting` | key, value, type, group, label, sort_order | — | — | — | — |
| `AboutSection` | key, value, type, group, label, sort_order | — | — | — | — |
| `StudyInChinaSection` | key, value, type, group, label, sort_order | sort_order→int | — | — | Accessor `getParsedValueAttribute` (decodes JSON) |
| `ScholarshipApplication` | name, email, phone, highest_qualification, gpa_cgpa, desired_program, target_intake, hsk_english_level, target_course, educational_background, statement_of_purpose, message, status, admin_notes, follow_up_date, budget, preferred_consultation_time | follow_up_date→date | — | — | — |

**Missing:** No observable patterns (no observers dir), no global scopes, no events beyond defaults, no soft deletes observed.

---

## 5. DATABASE

### Migrations inventory
**Base (pre-existing beyond visible tabs, inferred):** users, password_resets, sessions, courses, modules, lessons, enrollments, posts, categories.

**Incremental (visible):**

| Migration | Action |
|---|---|
| `2026_07_31_100000` | `users`: + role, phone, avatar |
| `2026_07_31_100001` | create `settings` |
| `2026_07_31_100002` | create `lesson_progress` |
| `2026_07_31_100003` | `posts`: + user_id, excerpt, published_at |
| `2026_07_31_100004` | `courses`: + category, featured, duration |
| `2026_07_31_100005` | `enrollments`: + price, paid, paid_at |
| `2026_08_01_000000` | `lessons`: + slug |
| `2026_08_01_000001` | create `scholarship_applications` |
| `2026_08_01_000002` | `users`: + is_admin |
| `2026_08_01_000003` | `enrollments`: + sender_number |
| `2026_08_01_100000` | create `contact_messages` |
| `2026_08_02_000000` | `scholarship_applications`: + highest_qualification, gpa_cgpa, desired_program, target_intake, message |
| `2026_08_02_100000` | `scholarship_applications`: + hsk_english_level |
| `2026_08_03_000000` | create `about_sections` |
| `2026_08_04_000000` | `courses`: + type, batch_dates |
| `2026_08_05_000000` | create `study_in_china_sections` (key, value, type, group, label, sort_order) |
| `2026_08_05_000001` | `scholarship_applications`: + status (guard `hasColumn`), admin_notes, follow_up_date |
| `2026_08_05_000002` | `scholarship_applications`: + budget, preferred_consultation_time |

### ERD-style description
```
users 1───∞ enrollments ∞───1 courses 1──∞ modules 1──∞ lessons 1──∞ lesson_progress ∞──1 users
users 1───∞ posts ∞───1 categories
settings (key-value)
contact_messages (standalone inbox)
about_sections (key-value groups)
study_in_china_sections (key-value groups: hero|why_china|why_us|roadmap|services|comparison|scholarships|quote|faqs|booking|final_cta)
scholarship_applications (standalone lead/application table; heavily evolved via 4 migrations)
```
- **Pivot tables:** none observed (enrollments is a regular FK table).
- **Foreign keys:** implicit via Eloquent convention; **no `foreignId()->constrained()` evidence** in the visible migrations (raw columns).
- **Indexes:** `slug` columns + route scoping; no explicit composite indexes visible. **Performance concern** for `posts.slug`, `courses.slug`, `lessons.slug` lookups if unindexed.

---

## 6. VIEWS — hierarchy

```
layouts/
├── app.blade.php          (public shell: header nav, footer, mobile bottom-nav, SEO/meta stack, JSON-LD)
├── guest.blade.php        (Breeze guest shell)
├── admin.blade.php        (admin shell: sidebar w/ Scholarships & Study in China CMS links)
└── navigation.blade.php   (Breeze nav incl. 'Study in China' link)

components/
├── application-logo.blade.php
├── course-card.blade.php  (badge/filter match: 'study-in-china' → 🎓)
└── (Breeze app-layout / nav-link / responsive-nav-link)

Pages (public)
├── home.blade.php              (hero CTA → study-in-china; JS course filter incl. 'scholarship'/'study-in-china' filters; scholarship roadmap section)
├── courses/{index,show}.blade.php
├── blog/{index,show}.blade.php (show has Study-in-China CTA)
├── about/show.blade.php        (CTAs default to /study-in-china/consultation)
├── pages/contact.blade.php     (contact topic select incl. 'scholarship')
├── checkout/{show,confirmation}.blade.php
└── study-in-china/{index,services,consultation}.blade.php

Student
├── dashboard.blade.php         (sidebar link to Study in China)
└── dashboard/lessons/show.blade.php

Admin
├── admin/dashboard.blade.php   (Pending Scholarships stat + Manage Scholarships card)
├── admin/scholarships/{index,show}.blade.php
├── admin/study-in-china/edit.blade.php  (inline fetch() JS → update-by-key / update-json endpoints)
├── admin/crm/{index,show}.blade.php
├── admin/courses/*, admin/modules/*, admin/lessons/*
├── admin/posts/*, admin/contact-messages/*, admin/about/*, admin/settings/edit.blade.php

Auth/profile (Breeze)
├── auth/* (login, register, etc.)
└── profile/partials/*, profile/edit.blade.php

errors/
├── 404.blade.php, 500.blade.php, 503.blade.php
```
Blade inheritance: `x-app-layout` for public, `layouts/admin` for admin, `layouts/guest` for auth. Partial snippets: `admin/posts/_form.blade.php`, `admin/courses/_form.blade.php`, `admin/about/_field.blade.php`.

---

## 7. FRONTEND ASSETS

- **CSS:** `resources/css/app.css` (Tailwind directives + custom component classes).
- **JS:** Breeze default `resources/js/app.js` with Alpine; **no project-specific JS files** — key inline `<script>` blocks live inside Blade (`home.blade.php` filter logic; `admin/study-in-china/edit.blade.php` `fetch()` CMS saving).
- **Framework:** None of Vue/React/Livewire. Alpine.js + Tailwind.
- **Styling:** Tailwind utility-first; custom green accent `#0F5132`, red `#dc2626`, slate palette; ring/shadows consistent.
- **Build:** Vite; dev server standard `npm run dev`.
- **Online note:** No compiled assets committed (`public/build` git-ignored) → deploy requires `npm ci && npm run build`.

---

## 8. MIDDLEWARE

| Middleware | Purpose |
|---|---|
| `AdminMiddleware` (custom, `app/Http/Middleware/AdminMiddleware.php`) | Guards admin routes: checks `auth` && `user->is_admin`; redirects non-admins to `/dashboard` |
| `auth` (Breeze) | Session auth gate |
| `guest` (Breeze) | Redirects authenticated users away from auth pages |
| `throttle` | Rate-limit public forms: contact `3,1`, enroll `5,60`, study-in-china forms `3,60` |

There is **no** `VerifyCsrfToken`/`TrustProxies` customization (defaults).

---

## 9. SERVICES / REPOSITORIES / HELPERS / ACTIONS

| Item | Responsibility |
|---|---|
| `app/Services/SettingsService.php` | Key service: static/instance cache over `settings` table — `get(key, default)` used across layouts/pages for site_name, meta_description, WhatsApp number, hero CTAs |
| Form Requests (`app/Http/Requests/*`) | Validation authority for Course/Post/Contact/Enrollment (Store+Update variants) |
| `app/Console/Commands/BackfillLessonSlugs.php` | One-off data fix command for `lessons.slug` |
| **No** Repositories, **no** Actions, **no** dedicated Helpers facade, **no** Policies, **no** Observers, **no** Jobs. Business logic lives in controllers + models. |

---

## 10. CONFIGURATION (`config/`)

Standard Laravel 12 config surface (app, auth, cache, database, filesystems, logging, mail, queue, services, session, view). Notable points:
- **Database:** default connector from `.env` (sqlite/mysql).
- **Filesystems:** `public` disk used for CMS image uploads (`storage/study-in-china`).
- **Auth:** Breeze defaults; admin gating via middleware flag, not `config/auth` guards.
- **Cache/Queue:** defaults (no custom drivers).
- No custom config files added for the feature modules.

---

## 11. ENVIRONMENT VARIABLES (`.env.example` names only)

| Variable | Purpose |
|---|---|
| `APP_NAME` | Site name |
| `APP_ENV` | local/production |
| `APP_KEY` | App encryption key |
| `APP_DEBUG` | Debug mode |
| `APP_URL` | App base URL |
| `APP_TIMEZONE` / `APP_LOCALE` / `APP_FALLBACK_LOCALE` / `APP_FAKER_LOCALE` | Locale/timezone |
| `APP_MAINTENANCE_DRIVER` / `APP_MAINTENANCE_STORE` | Maintenance mode |
| `BCRYPT_ROUNDS` | Hash cost |
| `LOG_CHANNEL` / `LOG_STACK` / `LOG_LEVEL` | Logging |
| `DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` | Database |
| `SESSION_DRIVER` / `SESSION_LIFETIME` / `SESSION_ENCRYPT` / `SESSION_PATH` / `SESSION_DOMAIN` / `SESSION_SECURE_COOKIE` / `SESSION_SAME_SITE` | Sessions |
| `BROADCAST_CONNECTION` / `FILESYSTEM_DISK` / `QUEUE_CONNECTION` / `CACHE_STORE` | Infrastructure |
| `MAIL_MAILER`, `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD`, `MAIL_ENCRYPTION`, `MAIL_FROM_ADDRESS`, `MAIL_FROM_NAME` | Email |
| `VITE_APP_NAME` | Vite build |

No third-party secrets (stripe/pusher etc.) required by the app.

---

## 12. STUDY IN CHINA MODULE — complete dependency map

> Total **222 cross-references** found across the codebase.

### Controllers
- `ScholarshipController@index` — public `/study-in-china` (⚠️ wrong data passed — see Tech Debt)
- `ScholarshipController@services`, `@consultation`, `@store` — public pages + apply form
- `StudyInChinaController@submitConsultation` — POST consultation (`/study-in-china/consultation`)
- `StudyInChinaController@index` — 🧟 **never routed (dead code)**
- `Admin\StudyInChinaPageController@index/update` — CMS editor (incl. JSON and image upload → `storage/study-in-china`)
- `Admin\ScholarshipManagementController` — admin applications inbox + status
- `Admin\CrmController` — CRM over same `scholarship_applications` table (lead detail, notes, follow-up)
- `Admin\DashboardController` — shows `pendingScholarships` stat + "Manage Scholarships" card

### Routes (all listed in §2)
Public: `study-in-china`, `study-in-china.services`, `study-in-china.consultation`, `study-in-china.consultation.store`, `study-in-china.apply` + 301s `/scholarship*`. Admin: `admin.scholarships.{index,show,status}`, `admin.study-in-china.{index,update}`, `admin.crm.{index,show,update}`.

### Models
- `ScholarshipApplication` (17 fillable fields; only `follow_up_date` cast)
- `StudyInChinaSection` (key-value CMS; `parsed_value` accessor)
- `Course` (rows with `type=service` + `category_slug=study-in-china` and `consultation_link`)

### Database
- `scholarship_applications` + 4 migrations (create → fields → hsk → CRM → consultation)
- `study_in_china_sections` (groups: hero, why_china, why_us, roadmap, services, comparison, scholarships, quote, faqs, booking, final_cta)

### Seeders
- `StudyInChinaPageSeeder` (defaults for all section groups)
- `BanglayChineseSeeder` (service courses: scholarship-guidance, study-in-china, application-guide, complete-support, success-pathway…)
- `AboutPageSeeder` (hero/CTA URLs → `/study-in-china/consultation`)
- `SettingsSeeder` (site settings)
- Registered in `DatabaseSeeder`.

### Views
- Public: `study-in-china/index`, `study-in-china/services`, `study-in-china/consultation`
- Admin: `admin/study-in-china/edit`, `admin/scholarships/index`, `admin/scholarships/show`, `admin/crm/index`, `admin/crm/show`
- Indirect consumers: `home`, `about/show`, `blog/show`, `blog/index`, `pages/contact`, `dashboard`, `layouts/app` (header+footer+bottom-nav), `layouts/navigation`, `layouts/admin` (sidebar), `components/course-card`, `admin/dashboard`
- JavaScript: inline `fetch()` in `admin/study-in-china/edit.blade.php`; filter buttons in `home.blade.php`
- CSS: none dedicated (Tailwind utilities only)
- Images: none stored in repo — emoji icons; runtime uploads to `storage/study-in-china`

### Navigation / Menus / Permissions
- Public header nav, footer, mobile bottom nav all link `study-in-china`
- Admin sidebar: "🎓 Scholarships" + "Study in China CMS"
- Student dashboard sidebar: "Scholarship"
- Permissions: none — admin-only via `is_admin` middleware

### Tests
- `tests/Feature/Phase5PublicPagesTest` — page loads + apply submission
- `tests/Feature/Phase6AdminTest` — admin scholarship management + student blocked + dashboard stats

---

## 13. CROSS REFERENCES — impact if "Study In China" were deleted

| Area | Impact | Details |
|---|---|---|
| **Homepage** | 🔴 HIGH (breaks) | Hero CTA links `route('study-in-china')`; course filter buttons use `data-filter="scholarship"` / `"study-in-china"`; "CHINA SCHOLARSHIP ROADMAP" section; course-card badge map emoji 🎓 |
| **Navigation** | 🔴 HIGH | `layouts/app.blade.php` (desktop header, footer, mobile bottom nav); `layouts/navigation.blade.php` (Breeze nav desktop+mobile) all reference route names |
| **Footer** | 🔴 HIGH | Footer link + mobile bottom-nav item |
| **Admin Panel** | 🔴 HIGH | Sidebar links; `admin/dashboard` stats (`pendingScholarships`), "Manage Scholarships" card; 3 admin controllers + 8 admin views + CMS |
| **Dashboard** | 🟠 MEDIUM | Student sidebar "Scholarship" link → would 404; redirect if left dangling |
| **Permissions** | 🟢 NONE | No role/permission tied to feature; only `is_admin` global gate |
| **Database** | 🔴 HIGH | `scholarship_applications` (4 migrations), `study_in_china_sections` (1 migration); seeder `StudyInChinaPageSeeder`; Course rows with `type=service` & `category_slug=study-in-china` would orphan pricing cards |
| **Other pages** | 🟠 MEDIUM | `about/show` hero/CTA default URLs → `/study-in-china/consultation`; `blog/show` CTA; `contact` dropdown topic "চায়না স্কলারশিপ"; `home` filters |
| **API** | 🟢 NONE | No API routes consume the module |
| **SEO** | 🟠 MEDIUM | `study-in-china/index` has its own meta/OG/Twitter/JSON-LD block; app layout meta keywords mention "study in china", "china scholarship" |
| **Sitemap** | 🟢 NONE | No sitemap route/generator exists at all |
| **Menus** | 🔴 HIGH | 4 nav surfaces × multiple links each (public header, footer, mobile, admin sidebar, student dashboard) |
| **Shared Components** | 🟠 MEDIUM | `components/course-card` contains a hard-coded `service` badge → 🎓 "Study in China" + `match ($course->slug)` cases for `hsk-intensive-program` → 'scholarship' filter |
| **Tests** | 🔴 HIGH | `Phase5PublicPagesTest` + `Phase6AdminTest` assert these routes/models exist → both suites **fail** if removed without updating tests |

---

## 14. TECHNICAL DEBT

### 🔴 Critical bugs
1. **CMS has no public effect.** `GET /study-in-china` → `ScholarshipController@index` passes **`$services` only**, but `study-in-china/index.blade.php` consumes **`$sections`** (`$sections['hero'] …`). The blade uses `?? []` fallbacks so **no error is thrown** — it silently renders hard-coded defaults. Every edit made in the admin "Study in China CMS" (`StudyInChinaPageController`) is **never displayed publicly**. The CMS is effectively decorative.
2. **Dead controller method.** `StudyInChinaController@index` contains the correct `$sections` query + `view('study-in-china.index', compact('sections'))` but is **never routed** — the route summary proves no `Route::get(...)` points to it.

### 🟠 Architectural
3. **No validation Form Request** for `ScholarshipApplication`; inline `$request->validate()` in both controllers.
4. **Raw key-value CMS** (`study_in_china_sections` / `about_sections` / `settings`) uses string keys with inline `?->` fallbacks in blade — untyped, error-prone, no schema for structure (JSON strings validated only lightly).
5. **`hasColumn()` guards inside migrations** (`2026_08_05_000001/2`) — non-idiomatic; fragile if down/up re-run.
6. **Duplicate CMS structure:** `Setting`, `AboutSection`, `StudyInChinaSection` are near-identical key-value tables — 3 implementations of the same pattern instead of one generic CMS.
7. **Admin inline category creation** is a raw closure in `routes/web.php` with manual `Str::slug` — logic leaking into routes.
8. **Duplicate scholarship surfaces:** `ScholarshipManagementController` (admin applications) and `CrmController` (CRM) both operate on `scholarship_applications` — overlapping/confusing; "CRM" is a near-duplicate of "Scholarships".
9. **Inline JS in blade** (admin CMS `fetch`, home filter) rather than compiled modules.

### 🟡 Code quality
10. **Large blades** — `study-in-china/index` (655 lines), `home`, `admin/study-in-china/edit` — tightly coupled sections; no componentization.
11. **Styling duplication** — `study-in-china` views hard-code colors instead of using the CMS palette; repeated `bg-[#0F5132]` literals across admin.
12. **Naming mismatch** — `ScholarshipController` named "Scholarship" but hosted under `/study-in-china/…`; `StudyInChinaController` overlaps; confusing domain boundary.
13. **Missing indexes** on `slug` columns (Posts, Courses, Lessons) — route model binding by slug will scan.
14. **N+1 risk** in course listing/home if `modules`/`lessons` eager loading is absent.
15. **No soft deletes** on posts/courses — hard deletes destroy referential history.
16. **No tests** for the actual Study-in-China CMS edit flow (only page-load + application submit + status update).

---

## 15. RISKS (before deleting / modifying the module)

| # | Risk | Severity |
|---|---|---|
| R1 | **Silent CMS breakage**: deleting `StudyInChinaController@index` is safe (dead), but re-pointing the public route to the CMS-enabled controller changes rendered content **dramatically** (DB content vs fallback) — visual regressions. | HIGH |
| R2 | **Broken route references** across 4 nav surfaces if routes are removed without updating blades → Laravel `RouteNotFoundException` on every page. | HIGH |
| R3 | **Database schema removal** (`scholarship_applications`, `study_in_china_sections`, + 5 migrations) will break `Admin\DashboardController`, `Phase5/6` tests, seeders, and any stored lead data (business data loss). | HIGH |
| R4 | **Course rows** with `type=service`, `category_slug=study-in-china` and WhatsApp `consultation_link` are seeding data — deleting courses silently removes revenue-path content if not re-seeded. | MEDIUM |
| R5 | **SEO loss**: inbound links to `/study-in-china` + `/scholarship/*` 301s; deleting without 410s/404s harms long-tail "china scholarship bangladesh" traffic. | MEDIUM |
| R6 | **About page** secondary CTA defaults point at `/study-in-china/consultation` — would 404. | MEDIUM |
| R7 | `Course` model/`course-card` components contain hard-coded `study-in-china` slugs/badge logic → deleting only module routes/views leaves **dead styling code** and filter JS referencing missing categories. | MEDIUM |
| R8 | `SettingsService` cache/static state could serve stale settings on shared hosting if `php artisan config:cache`/`optimize` used (general deploy risk). | LOW |

---

## 16. RECOMMENDATIONS (architecture-level, no code changes made)

1. **Fix the route/controller wiring first** (before any deletion): point `GET /study-in-china` at `StudyInChinaController@index` so the CMS actually drives the page, or merge the two controllers.
2. **Unify the CMS**: replace `Setting` / `AboutSection` / `StudyInChinaSection` with one generic `content_sections` key-value store (group = page), or keep but standardize API.
3. **Merge Scholarship + CRM admin** into a single "Leads" area to remove duplicated inboxes.
4. **Move inline JS** (admin CMS fetch, home filters) into `resources/js` and compile with Vite.
5. **Extract the Study-in-China landing page** into Blade components (hero, roadmap, scholarships grid, FAQs, booking) to reduce the 655-line blade and enable safe feature removal.
6. **Add `sitemap.xml` + robots** — currently absent — and a redirect map for any removed URLs.
7. **Add missing indexes** on `posts.slug`, `courses.slug`, `lessons.slug` (migration).
8. **Standardize validation** with a `StoreScholarshipApplicationRequest`.
9. **Add feature-level tests** covering the CMS edit → public render pipeline (the current gap that allowed the silent bug).
10. **Adopt Policies/Gates** for admin actions instead of a bare `is_admin` flag, to prepare for role granularity.

---

## ARCHITECTURE SUMMARY

**Banglay Chinese** is a single-server, Laravel 12-era monolithic education platform (learn-Chinese-for-Bangladeshis) with:
- **Server-rendered Blade + Tailwind + Alpine**, built by Vite, deployed via classic LAMP/LNMP shared hosting.
- **Three content surfaces** sharing the same key-value CMS pattern: `settings` (global), `about_sections`, `study_in_china_sections`.
- **Auth** via Breeze + single boolean `is_admin` gate; **no** policies, packages or external APIs.
- **No queue, no cache layer beyond file, no API, no translation files** — the site is effectively a small marketing/LMS hybrid: public pages (courses, blog, about, contact, study-in-china), a student dashboard (enrollment → lessons → progress), and a rich admin panel (courses/modules/lessons, posts, payments, scholarships+CRM, contact inbox, settings + page CMS).
- **Primary structural weakness:** the Study-in-China feature has a routing/data mismatch (dead CMS controller, mismatched view variables) and a high coupling count (222 references) that makes the feature appear *larger* and more fragile than its business value requires.
- **Most important takeaway:** before any module deletion or refactor, the route wiring must be corrected and the nav/footer/test references reconciled — otherwise the homepage, admin dashboard, and both test suites will break simultaneously.
