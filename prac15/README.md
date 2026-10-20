# StudentHub Portal - Complete Full-Stack Web Application

## Overview
**StudentHub** is an integrated full-stack student and campus event management portal built for CHARUSAT University. The portal provides end-to-end functionality including student registration with client/server validation, secure session-based authentication with bcrypt hashing, role-based access control (RBAC), administrative student and event management (CRUD + AJAX Fetch API), event image/poster uploads, real-time security audit trails, and responsive design.

---

## System Architecture & Tech Stack

| Layer | Technology | Description |
|---|---|---|
| **Frontend** | HTML5 Semantic, CSS3 Responsive Grid & Flexbox, JavaScript (ES6+ / Fetch API) | Accessible, responsive, university-themed UI (#003366) |
| **Backend** | PHP 8.x (Procedural / Native Functions) | Modular architecture, parameterized MySQLi statements, secure session handling |
| **Database** | MySQL (MariaDB via XAMPP) | Normalized relational schema (3NF) with foreign keys and cascade rules |
| **Server** | Apache 2.4 (XAMPP for Windows) | Local deployment and virtual host / alias support |

---

## Database Schema & Tables

The system operates on the `studenthub` MySQL database:

1. **`students`**: User credentials, student profiles, academic courses, roles (`student`, `admin`), and hashed passwords.
2. **`events`**: Campus events, categories, schedules, venues, capacities, uploaded poster file names, and statuses (`Upcoming`, `Ongoing`, `Completed`, `Cancelled`).
3. **`registrations`**: Junction entity linking students to events with unique compound key `(student_id, event_id)` preventing duplicate signups.
4. **`audit_logs`**: Security and admin action logs (`log_id`, `user_id`, `user_name`, `action`, `details`, `ip_address`, `created_at`).

To initialize or review the full schema:
```sql
mysql -u root -p < schema.sql
```

---

## Key Modules & Features

### 1. Public Pages & Portal Navigation
- `index.html`: Central university landing page with announcements, quick links, and responsive navigation.
- `about.html`, `contact.html`, `faq.html`, `feedback.html`, `profile.html`: Informational pages linked to the active portal endpoints.

### 2. User Registration & Security (`registration.php`)
- **Client-Side Validation**: Immediate feedback on name, email, 10-digit mobile, password length, and required fields.
- **Server-Side Validation**: Sanitization via `htmlspecialchars()`, `filter_var()` email checks, regex checks, and duplicate email prevention.
- **Password Security**: Passwords are encrypted using PHP's native `password_hash($password, PASSWORD_DEFAULT)` using Bcrypt.

### 3. Secure Authentication & Session Lifecycle (`login.php`, `logout.php`, `auth.php`)
- **Password Verification**: Secure verification via `password_verify($password, $hash)`.
- **Session Fixation Prevention**: Calls `session_regenerate_id(true)` upon successful login.
- **Role-Based Access Control (RBAC)**:
  - Students are redirected to `dashboard.php`.
  - Admins are redirected to `admin-dashboard.php`.
  - Unauthorized direct URL access attempts to admin panels trigger immediate redirection and denial notifications.
- **Inactivity Timeout**: Sessions automatically expire after 10 minutes (600 seconds) of inactivity.
- **Remember-Me Tokens**: Optional secure persistent login token stored in database and HTTP-only cookie.

### 4. Student Management CRUD (`admin.php`)
- View all students with search by name/email and filters by Course, Year, and Gender.
- Add new students directly from admin panel with validation.
- Edit/Update student profiles.
- Delete student records with confirmation modal.
- Every create/update/delete operation writes an entry to `audit_logs`.

### 5. Event Management (Fetch API & AJAX) (`admin-events.php`, `api-events.php`)
- Asynchronous CRUD operations without page reloads using the modern browser `fetch()` API.
- Live search by event title/venue and filtering by category and status.
- Poster image upload with validation (MIME-type check, 2MB size limit, extension whitelist) stored in `uploads/`.
- Dynamic DOM updates for create, edit, and delete operations.

### 6. Public Event Browsing & Student Registration (`events.php`)
- Visual event catalog with card layouts and event posters.
- Direct student event registration form with duplicate prevention.

### 7. Administrative Analytics Dashboard (`admin-dashboard.php`)
- Aggregated SQL KPI cards: Total Students, Admin Count, Total Events, Active Events, Total Registrations, Audit Log Count.
- Latest registrations table fetched via SQL `JOIN` across `registrations`, `students`, and `events`.
- Live visual feed of recent administrative and security audit events.

### 8. Security Audit Logs (`audit-logs.php`)
- Complete traceability table logging user IDs, actor names, action types (`LOGIN`, `LOGOUT`, `TIMEOUT`, `ADD_STUDENT`, `UPDATE_STUDENT`, `DELETE_STUDENT`, `ADD_EVENT`, `UPDATE_EVENT`, `DELETE_EVENT`), detailed descriptions, client IP addresses, and timestamps.
- Action-type filter and search bar for quick security audits.

---

## Local Deployment on XAMPP

### Prerequisites
- XAMPP with PHP 8.0+ and MySQL 5.7+ / MariaDB.
- Git (optional, for version control).

### Step-by-Step Installation

1. **Start XAMPP Services**:
   - Open **XAMPP Control Panel**.
   - Start **Apache** and **MySQL**.

2. **Access Project Directory**:
   - Ensure files are in `C:\xampp\htdocs\practical-15\` or linked via directory junction.
   - Alternatively, navigate to your workspace folder: `c:\Users\Admin\OneDrive\Desktop\LAB WORK\student po\PRACTICAL-15`.

3. **Database Setup**:
   - Open phpMyAdmin in browser: `http://localhost/phpmyadmin/`.
   - Create a database named `studenthub` (if not already created).
   - Import `schema.sql` located in this directory.
   - Database credentials can be customized in `db.php` (Default: `localhost`, `root`, blank password).

4. **Verify Application URL**:
   - Open in your browser: `http://localhost/practical-15/index.html` or `http://localhost/practical-15/login.php`.

---

## Default Login Credentials for Testing

| Role | Email | Password | Target Dashboard |
|---|---|---|---|
| **Administrator** | `admin@charusat.edu.in` | `admin123` | `admin-dashboard.php` |
| **Student** | `25dce022@charusat.edu.in` | `password123` | `dashboard.php` |
| **Student** | `25dce020@charusat.edu.in` | `password123` | `dashboard.php` |

---

## Testing & Verification Checklist

- [x] **Page Navigation**: All header, footer, and sidebar links navigate accurately without broken links (404).
- [x] **Responsiveness**: Mobile, tablet, and desktop layouts verified with responsive grid and media queries.
- [x] **Form Validations**: Client-side JS and server-side PHP validation on registration and login.
- [x] **Password Hashing**: Bcrypt encryption checked on `INSERT` and verified via `password_verify()`.
- [x] **RBAC Protection**: Access to `admin-dashboard.php`, `admin.php`, `admin-events.php`, and `audit-logs.php` is strictly restricted to authenticated administrators.
- [x] **Session Timeout**: Inactivity timeout at 10 minutes clears session and redirects to login with alert.
- [x] **AJAX Functionality**: Event CRUD operates asynchronously via Fetch API with JSON responses.
- [x] **Image Upload**: Validates image types (JPG, PNG, WEBP), file size (<= 2MB), and prevents directory traversal.
- [x] **Audit Traceability**: All critical administrative and authentication actions write to `audit_logs`.
- [x] **SQL Injection Defense**: All user inputs bound through parameterized prepared statements (`$stmt->bind_param()`).
- [x] **XSS Prevention**: All database output escaped with `htmlspecialchars()`.

---

## GitHub Repository Setup & Commit Instructions

To commit and push this integrated codebase to a GitHub repository:

```bash
# Navigate to the project directory
cd "c:\Users\Admin\OneDrive\Desktop\LAB WORK\student po\PRACTICAL-15"

# Initialize Git repository (if not already initialized)
git init

# Configure user identity
git config user.name "Your Name"
git config user.email "your.email@example.com"

# Stage all files
git add .

# Commit changes
git commit -m "feat: complete integrated StudentHub portal with RBAC, AJAX CRUD, and audit logging"

# Link to your remote GitHub repository
git remote add origin https://github.com/<your-username>/studenthub-portal.git

# Push to main branch
git branch -M main
git push -u origin main
```
