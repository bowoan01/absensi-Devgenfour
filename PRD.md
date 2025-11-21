## 1. Overview
- Web-based internship attendance system with two roles: Admin and Student.
- Core job: capture daily attendance at the office (check-in with optional check-out) with timestamp and notes, replacing manual sheets.
- Admin manages intern roster (full CRUD) and monitors attendance; students self-serve for daily check-in/out and history.

## 2. Background & Goals
- Business context: Internship programs require auditable attendance for stipends, compliance, and performance tracking; manual spreadsheets cause errors and slow reconciliation.
- Goals: (1) Accurate, tamper-resistant attendance capture; (2) Faster admin workflows for roster maintenance and reporting; (3) Clear visibility for students into their own history; (4) Exportable data for payroll/HR review.
- Success posture: Mobile-friendly, low-friction check-in flow; reliable timestamps; easy reporting by student and date range.

## 3. Target Audience
- Admin (Supervisors/HR): Maintains intern list, monitors attendance, generates/export reports.
- Students (Interns): Perform daily attendance (check-in, optional check-out) and review personal history.

## 4. Website Structure & Main Pages
- Login Page: Role-based access; email/username + password; error prompts; remember-me optional.
- Admin Dashboard: Summary cards (today's check-ins, missing check-ins, late arrivals), quick links to Roster and Reports.
- Intern Students List (CRUD + View Report): Table with search/filter, add/edit/delete student, activate/deactivate, and "View Report" action per student leading to per-student report.
- Student Attendance Page: Today status, check-in/out buttons, notes field, current time indicator, last action recap.
- Per-Student Attendance Report Page: Date range filters, table of entries (date, check-in, check-out, status, notes), export (CSV/XLSX), print-friendly view.

## 5. Design Requirements
- Simple, modern, responsive UI usable on desktop and mobile; finger-friendly controls and clear states.
- Consistent theming (light, high contrast), readable typography, minimal modals, inline validation.
- Tables with sticky headers on desktop; card/list pattern on mobile for attendance history.
- Clear affordances for primary actions (check-in/out) and destructive actions (delete student).
- ### UI/UX Descriptions
- Login: Simple form with brand header; validation hints; remember-me checkbox; password reset link placeholder.
- Admin Dashboard: Cards showing today's stats; table/list of recent check-ins; quick access buttons to Roster and Reports.
- Intern Students List: Data table with filters; inline or modal add/edit forms; delete confirmation; status badges; "View Report" button per row.
- Student Attendance Page: Prominent check-in/out buttons; current time display; note textarea; status banner for today; last action timestamp.
- Per-Student Attendance Report: Date range picker; summary chips (present/absent/late); table with pagination; export and print buttons; responsive cards on mobile.

## 6. Technical Requirements
- Layered stack:

| Layer    | Technology                |
|----------|---------------------------|
| Backend  | Laravel (PHP 8.3)         |
| Frontend | Bootstrap 5, jQuery AJAX  |

- Architecture: MVC with RESTful controllers; stateless JSON endpoints for attendance, roster CRUD, and reporting; AJAX used for in-page operations to avoid full reloads; CSRF protection enabled.
- Auth and sessions: Laravel auth guards per role; password hashing (bcrypt/argon2id); session invalidation on logout.
- Data: MySQL/MariaDB; migrations define schema; Eloquent models with relationships and validation rules.
- Caching: Query/result caching for frequent reports and student list filters where safe.
- Logging/monitoring: Centralized Laravel logging; audit trail for attendance changes by admin.
- Error handling: Consistent JSON error codes/messages for AJAX; friendly UI toasts for failures.
- 🔧 Features & Implementation:
  - Authentication: Laravel auth scaffolding; middleware for role-based routes; remember-me cookie optional; lockout on repeated failed logins.
  - Intern student management: Eloquent `Student` model; CRUD via REST endpoints; soft delete or status flag; server-side validation; AJAX table updates.
  - Daily attendance: `Attendance` model; endpoints for check-in/out with server-side time stamps; optional notes; enforce one check-in and one check-out per day; prevent duplicate submissions; handle late/absent flags.
  - Reporting: Per-student and date-range filters; aggregated counts (present, absent, late); export endpoints generating CSV/XLSX; downloadable via secure, role-checked links.
  - History and visibility: Students see only their records; admins can view all; pagination and sorting server-side.
- ### Functional Requirements
- FR-1 Auth: Users can log in with email/username and password; sessions persisted with CSRF protection.
- FR-2 Role Access: Admin pages restricted to Admin; Student pages restricted to Student.
- FR-3 Manage Students: Admin can create, read, update, delete, and activate/deactivate intern records.
- FR-4 View Roster: Admin can search/sort/filter the intern list.
- FR-5 View Student Report: Admin can open per-student attendance report and filter by date range.
- FR-6 Check-in: Student can perform one check-in per day with timestamp auto-captured and optional note.
- FR-7 Check-out: Student can perform one check-out per day (optional) with timestamp auto-captured and optional note.
- FR-8 Prevent Duplicates: System blocks multiple check-ins or check-outs on the same day.
- FR-9 Attendance History (Student): Student can view their own attendance history with pagination.
- FR-10 Attendance Viewing (Admin): Admin can view attendance entries for any student and date range.
- FR-11 Status Rules: If no check-in by configurable cut-off (for example 10:00 AM), mark as Absent (auto) unless admin overrides.
- FR-12 Notes: Optional free-text notes stored with attendance actions.
- FR-13 Reporting: Admin can generate attendance reports per student and per date range with totals (present, absent, late).
- FR-14 Export: Admin can export reports to CSV/XLSX for the selected filters.
- FR-15 Audit Trail: System logs attendance changes made by Admin (edits/corrections).
- FR-16 Logout: Users can log out, invalidating the session.
- ### Non-Functional Requirements
- Security: Enforce HTTPS, input validation/sanitization, CSRF tokens, password hashing, role-based authorization, audit logging.
- Usability: Mobile-friendly UI, clear error/success feedback, accessible controls (ARIA labels, focus states).
- Reliability: Graceful error handling; retries for transient DB issues; backup/restore procedures.
- Scalability: Efficient DB indexing; stateless APIs to allow horizontal scaling; caching for frequent queries.
- Maintainability: Modular MVC codebase; RESTful endpoints; migrations and seeders; linting and code style checks; documentation for admins.
- ### Data Structures (high level)
- users: id, name, email, password_hash, role (admin/student), status, created_at, updated_at.
- students: id, user_id (FK), full_name, student_id_code, department, start_date, end_date, status, created_at, updated_at.
- attendance: id, student_id (FK), date, check_in_at, check_out_at, status (present/absent/late), note, created_at, updated_at.
- Relationships: user hasOne student; student belongsTo user; student hasMany attendance records.
- ### Business Rules
- One check-in per student per calendar day; one check-out per student per calendar day.
- Check-in/check-out timestamps come from server time; notes optional.
- Students cannot modify or delete attendance records; only Admin can correct entries.
- Auto-absent can be set if no check-in by cut-off time; Admin can override status.
- Admin edits are logged with actor and timestamp.
- Inactive students cannot perform attendance.

## 7. Performance Goals
- Response time: under 500 ms server processing for 95th percentile of standard requests (attendance actions, list retrieval) under normal load.
- Concurrency: Support 200 concurrent student sessions and 10 admin sessions without degradation on target infrastructure.
- Query efficiency: O(1) queries per attendance action; paginated roster/report queries with indexed columns (student_id, date).
- Uptime: Target 99.5% for MVP.

## 8. KPIs (Success Metrics)
- >= 90% of attendance captured through system vs manual within first 2 months.
- Admin time spent on attendance consolidation reduced by >= 50%.
- >= 95% completeness of daily attendance records (no missing check-ins for active interns).
- Export/report success rate >= 99% without manual correction.
- Daily active student usage rate >= 85% of active interns during internship period.

## 9. Development Timeline (MVP)
- Planning and Requirements: 3 days - finalize scope, flows, and data rules.
- Design: 3 days - wireframes and responsive layouts for key pages.
- Implementation: 10 days - auth, roster CRUD, attendance endpoints/UI, reports, exports.
- Testing: 4 days - functional tests, validation, role-based access, basic load checks.
- Deployment and Stabilization: 2 days - release to staging, smoke tests, production deploy, monitoring.

## 10. Risks & Mitigation
- Server downtime: Use health checks and rolling deploys; maintain backups; monitor uptime.
- Data loss: Nightly DB backups; test restore drills; transaction-safe writes for attendance.
- Security issues: Enforce HTTPS, CSRF tokens, input validation, role guards; rate-limit logins; audit admin edits.
- User resistance/adoption: Provide quickstart guide and in-product tips; simplify forms; collect feedback in pilot.
- Performance bottlenecks: Add DB indexes on student/date; paginate lists; cache frequent report queries.
- Export failures: Validate date ranges and limits; queue large exports; provide retry with logging.
