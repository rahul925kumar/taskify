# Taskify - Custom Task Management System

A full-featured task management system built with **Laravel 12** and **KaiAdmin Lite** dashboard template. Designed for organizations to manage employees, projects, tasks, and team productivity.

**Developed by [TechnoByte Developers](https://github.com/rahul925kumar)**

---

## Features

### Authentication
- Admin login with email & password
- Employee login via **OTP** (sent to admin email, valid for 10 minutes)
- Role-based access control (Admin / Employee)

### Admin Panel
- **Dashboard** — stats cards, task status charts, employee performance bar chart, today's logins, on-leave alerts, pending leave requests
- **Employee Management** — create, edit, view, soft delete with profile image upload
- **Client Management** — CRUD for client records
- **Project Management** — create projects with client association, date ranges, and status tracking
- **Task Management** — full CRUD with filters (project, status, priority, assignee, search)
  - Assign / reassign tasks to employees
  - Task comments with nested replies
  - File attachments
  - Complete audit trail (task history)
  - Status, priority, type, and category classification
- **Kanban Board** — drag-and-drop task management with real-time status updates
- **Leave Management** — approve/reject employee leave requests, view on-leave employees with pending tasks, bulk reassign tasks
- **Reports** — employee performance table with completion rates, tasks by priority/type/category charts, monthly task trends
- **Attendance** — track employee login/logout times, filter by date and employee
- **Notifications** — in-app notification system with mark as read

### Employee Panel
- **Dashboard** — pending & overdue task alerts, task stats, recent tasks
- **My Tasks** — filtered list of assigned tasks
- **Task Detail** — update status, add comments/replies, upload attachments, view history
- **Kanban Board** — drag-and-drop for own tasks
- **Leave Management** — apply for leave, view leave history and status

---

## Tech Stack

| Layer | Technology |
|-------|-----------|
| Backend | Laravel 12 (PHP 8.2+) |
| Database | MySQL 8.0 |
| Frontend | KaiAdmin Lite 1.2.0, Bootstrap 5 |
| Charts | Chart.js |
| Notifications | Bootstrap Notify |
| Alerts | SweetAlert |

---

## Setup Instructions

### Prerequisites
- PHP 8.2 or higher
- Composer
- MySQL 8.0
- Git

### 1. Clone the Repository

```bash
git clone https://github.com/rahul925kumar/taskify.git
cd taskify
```

### 2. Install Dependencies

```bash
composer install
```

### 3. Environment Configuration

```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env` and set your database credentials:

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### 4. Create the Database

```bash
mysql -u root -p -e "CREATE DATABASE your_database_name;"
```

### 5. Run Migrations & Seed Data

```bash
php artisan migrate
php artisan db:seed
```

This will create:
- Admin user with the configured email
- 8 sample employees, 5 clients, 6 projects, 30 tasks with comments, histories, and 14 days of attendance data

### 6. Create Storage Link

```bash
php artisan storage:link
```

### 7. Start the Server

```bash
php artisan serve
```

Visit `http://127.0.0.1:8000`

---

## Default Login Credentials

### Admin
- **Email:** `rahulpassi925@gmail.com`
- **Password:** `admin123`

### Employee (OTP Login)
Use any seeded employee email (e.g., `amit.sharma@company.com`). The OTP will be sent to the admin email.

---

## Configuration

### Admin Email
The admin email is configured in `config/constants.php`:

```php
'admin_email' => 'rahulpassi925@gmail.com',
```

### Mail Setup (for OTP)
Configure SMTP settings in `.env` for OTP emails to work:

```
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your_email@gmail.com
MAIL_PASSWORD=your_app_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your_email@gmail.com
```

### Timezone
The application is configured for IST (Asia/Kolkata) in `config/app.php`.

---

## Project Structure

```
app/
├── Http/Controllers/
│   ├── Admin/          # Admin controllers (Dashboard, Employee, Client, Project, Task, Report, Attendance, Leave, Notification)
│   ├── Auth/           # Login controller (Admin + Employee OTP)
│   └── Employee/       # Employee controllers (Dashboard, Task, Leave)
├── Mail/               # OTP mailer
├── Models/             # Eloquent models (User, Client, Project, Task, TaskComment, TaskAttachment, TaskHistory, Attendance, Leave)
├── Middleware/          # Admin & Employee middleware
└── Notifications/      # Task notification
database/
├── migrations/         # All table migrations
└── seeders/            # Admin + dummy data seeders
resources/views/
├── admin/              # Admin panel views
├── employee/           # Employee panel views
├── auth/               # Login & OTP views
├── layouts/            # App & auth layouts
├── partials/           # Sidebar, navbar, footer
└── emails/             # OTP email template
```

---

## License

This project is proprietary software developed by **TechnoByte Developers**.
