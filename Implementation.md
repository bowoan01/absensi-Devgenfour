## 0 Compliance Checklist
- [ ] Required: No Node/npm/Vite/Webpack/Mix — CDN assets only.
- [ ] Required: No Tailwind / Alpine / SPA frameworks.
- [ ] Required: No Blade components (`resources/views/components/*` not used).
- [ ] Required: All routes in `routes/web.php` (no additional route files, no URL prefixes).
- [ ] Required: Bootstrap 5 + jQuery AJAX for interactivity.
- [ ] Required: Aligned with PRD scope.

## 1 Scope Recap & Approach
- Core scope: Admin manages intern roster (CRUD + activate/deactivate), Students perform daily check-in with optional check-out and notes, Admin views per-student attendance reports with filters and export.
- Approach: Keep controllers thin (validation + delegation), move reusable rules/queries into models or small helper classes, return JSON for AJAX actions and HTML for full pages, progressive enhancement via jQuery AJAX for mutations and table refreshes.

## 2 High-Level Architecture
- MVC flow: HTTP request → `routes/web.php` → controller method (authz/validation) → model/service-style helpers for business rules → database via Eloquent → Blade views for full pages; AJAX endpoints return JSON fragments consumed by jQuery.
- Full-page renders: login/logout, admin dashboard shell, roster list base page, per-student report shell, student attendance page shell.
- AJAX-driven interactions: roster CRUD submit/update/delete, attendance check-in/out, filtering/pagination for tables (server-side rendered partials or JSON rows), export triggers return download URLs.
- Views share a base layout; partials for navbar, flash/toast container, and table bodies (rendered server-side and swapped via jQuery).

## 3 Environment & Tooling
- Stack: PHP 8.3+, Laravel 10.x (compatible with 8.3 PHP), MySQL/MariaDB.
- Dependencies managed via Composer; `php artisan` for migrations, seeders, auth scaffolding (without frontend scaffolding).
- Local dev: `.env` config, `php artisan serve`, `php artisan migrate --seed`, `php artisan tinker` for quick checks.
- Frontend assets: Bootstrap 5 CSS/JS + jQuery from CDNs; no Node/npm/Vite/Webpack/Mix.

## 4 Database Design
- `users`: id (PK), name, email (unique), password, role enum (`admin`,`student`), status enum (`active`,`inactive`), remember_token, timestamps. Index email, role.
- `students`: id (PK), user_id (FK → users), full_name, student_id_code (unique), department, start_date, end_date (nullable), status enum (`active`,`inactive`), timestamps. Index user_id, student_id_code, status.
- `attendance`: id (PK), student_id (FK → students), date (DATE), check_in_at (nullable DATETIME), check_out_at (nullable DATETIME), status enum (`present`,`absent`,`late`), note (nullable TEXT), created_at, updated_at. Unique index (student_id, date); indexes on date and status.
- Relationships: User hasOne Student; Student belongsTo User; Student hasMany Attendance.
- Constraints: enforce unique daily attendance per student; server-time timestamps; status defaults to `present` on check-in, `absent` via auto job if missed cut-off, `late` when first check-in after cut-off.

## 5 Routing & Controllers (single `routes/web.php`)
- Auth: `GET /login` (LoginController@showLoginForm), `POST /login` (LoginController@login), `POST /logout` (LoginController@logout).
- Dashboard: `GET /` (DashboardController@index) with role-based cards.
- Admin roster: `GET /students` (StudentController@index), `POST /students` (store), `GET /students/{student}/edit`, `PUT /students/{student}` (update), `DELETE /students/{student}` (destroy), `PATCH /students/{student}/status` (activate/deactivate). AJAX responses for create/update/delete/status.
- Attendance (student self-service): `GET /attendance` (AttendanceController@index for today's summary/history), `POST /attendance/checkin`, `POST /attendance/checkout`.
- Admin attendance/reporting: `GET /reports` (ReportController@index filters), `GET /reports/{student}` (per-student report), `GET /reports/{student}/export` (CSV/XLSX based on query params), `GET /reports/table` (AJAX partial for filtered table).
- Middleware: route-level `auth`, `verified` if used, plus `can:admin` or custom middleware for admin-only routes, `can:student` for student page. All routes reside in `routes/web.php`; no prefixes.

## 6 Frontend Implementation (Plain Blade + Partials)
- Layouts: `resources/views/layouts/app.blade.php` with Bootstrap 5 and jQuery CDNs, CSRF meta, navbar include, flash/toast container, and yield sections.
- Partials: `resources/views/partials/navbar.blade.php`, `partials/alerts.blade.php`, `partials/students/table.blade.php`, `partials/reports/table.blade.php`, `partials/attendance/history.blade.php`.
- Pages: `resources/views/auth/login.blade.php`, `admin/dashboard.blade.php`, `admin/students/index.blade.php`, `admin/reports/index.blade.php`, `admin/reports/show.blade.php`, `student/attendance.blade.php`.
- jQuery AJAX: forms submit via `$.ajax` with CSRF header; responses return JSON status + rendered partial HTML for table bodies; success/errors shown via Bootstrap toasts/alerts; modal confirmations for delete/deactivate; inline form validation errors displayed without reload.
- Attendance actions: Check-in/out buttons POST to endpoints, disable during request, update today's status banner and history partial on success. Filters/pagination fetch partial HTML to replace table body.

## 7 Admin UX Details
- Roster: Table with columns (Name, ID Code, Department, Status badge, Start/End, Actions). Search box + filters (status, department). "Add Student" button opens modal form (name, email, student_id_code, dept, dates, status). Edit uses same modal with prefill. Delete/deactivate prompts confirmation; deactivate toggles badge without full reload.
- Dashboard: Summary cards (today check-ins, missing check-ins, late arrivals), recent check-ins table, quick buttons to Roster and Reports.
- Reports: Filters (student select, date range, status). Table with Date, Check-in, Check-out, Status badge, Note. Pagination controls. Export buttons for CSV/XLSX respect filters. Print-friendly link opens server-rendered print view.
- Per-student report: Access via "View Report" action on roster row; preselect student filter; same table with export and print controls.
- Feedback: Success/error toasts; disabled states during requests; sticky headers for tables on desktop; responsive cards on mobile for history/report rows.

## 8 Security & Compliance
- Auth: Laravel auth scaffolding with guards per role; middleware to restrict admin/student areas; logout invalidates session.
- CSRF: Blade `@csrf` on forms; AJAX uses meta token header.
- Validation: FormRequest or controller validation for all forms/AJAX; server-authoritative timestamps on attendance.
- Authorization: Policy/gate checks to ensure admins manage only roster/reporting and students access only their own attendance. Admin-only attendance edits logged via `Log::info` with actor and payload.
- HTTPS assumption in config; passwords hashed with bcrypt/argon2id; throttle login attempts; role checks before exports.

## 9 Content Management Flow
- Onboard intern: Admin clicks "Add Student", submits user + student details; system creates `users` (role student, active) and linked `students` row. Student receives initial credentials via out-of-band.
- Deactivate/reactivate: Admin toggles status via status endpoint; sets user and student status to inactive/active; UI updates badge and disables check-in for inactive students.
- Correct attendance: Admin opens per-student report, selects entry, uses edit modal (controlled form) to adjust check-in/out times/status/note; changes saved via admin-only endpoint and logged.

## 10 Testing Strategy
- Unit tests: Model scopes (active students, attendance by date), attendance status logic (late vs present), uniqueness constraint.
- Feature tests: Auth flow, role-based access (admin vs student), roster CRUD endpoints (AJAX JSON), attendance check-in/out (one per day, duplicate prevention), reports filtering and export responses, CSRF protection.
- Manual QA: Happy paths (check-in/out, roster add/edit/delete, exports), edge cases (duplicate check-in same day, checkout before check-in blocked, late after cut-off), pagination/filter combinations, inactive student attempting check-in, export with empty result set.

## 11 Deployment Checklist
- Set env: `APP_ENV`, `APP_KEY`, DB creds, `SESSION_DRIVER`, `MAIL_*`, `APP_URL`, `FORCE_HTTPS` as needed.
- Run `composer install --no-dev` and `php artisan key:generate` (if new).
- Run `php artisan migrate --seed` (seed initial admin user).
- Set folder permissions: `storage/`, `bootstrap/cache/`.
- Cache config/routes/views: `php artisan config:cache route:cache view:cache`.
- Verify file upload/public assets: publish favicon/logo to `public/`.
- Restart PHP-FPM/queue workers if applicable; ensure HTTPS termination.

## 12 Timeline Alignment (7-Week MVP)
- Week 1: Project setup, auth scaffolding, roles/middleware, base layout.
- Week 2: Student roster CRUD (models, controllers, views, AJAX table).
- Week 3: Attendance endpoints + student self-service page with AJAX check-in/out.
- Week 4: Admin reports list and per-student report views, filters.
- Week 5: Exports (CSV/XLSX), print view, admin edit of attendance.
- Week 6: Hardening (validation, audit logging), caching/index review, QA.
- Week 7: Final testing, seed data, deployment prep, monitoring hooks.

## 13 Open Questions
- Exact cut-off time and timezone for late/absent logic? Is it per-site configurable?
- Should auto-absent be a scheduled job daily, and what time?
- Export formats: CSV only or add XLSX? Any row limits?
- Authentication method for creating student credentials (auto password email vs manual)?
- Multi-office or department-specific cut-off rules needed?
- Is check-out mandatory or optional per policy?

## 14 File & Path Contract (Enforced)
- Controllers: `app/Http/Controllers/Auth/LoginController.php`, `app/Http/Controllers/DashboardController.php`, `app/Http/Controllers/Admin/StudentController.php`, `app/Http/Controllers/Admin/ReportController.php`, `app/Http/Controllers/AttendanceController.php`.
- Models: `app/Models/User.php`, `app/Models/Student.php`, `app/Models/Attendance.php`.
- Routes: all HTTP routes defined in `routes/web.php`.
- Views (Blade): layout `resources/views/layouts/app.blade.php`; partials under `resources/views/partials/`; admin pages under `resources/views/admin/` (`students/index.blade.php`, `reports/index.blade.php`, `reports/show.blade.php`, `dashboard.blade.php`); student page `resources/views/student/attendance.blade.php`; auth views under `resources/views/auth/`.
- Public assets: `public/css/custom.css`, `public/js/app.js` (jQuery helpers only), logos in `public/images/`.
