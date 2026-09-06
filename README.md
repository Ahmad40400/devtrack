# DevTrack - Developer Productivity & Portfolio Platform

## 🚀 Quick Start

1. Clone or download to `htdocs/devtrack/`
2. Import `database.sql` in phpMyAdmin
3. Configure `config/database.php` with your MySQL credentials
4. Access `http://localhost/devtrack/`
5. Default admin: `admin@devtrack.com` / `admin123`

## 📚 Features

### Core Features

- 🔐 **Authentication**: Secure registration, login, logout with password hashing
- ✉️ **OTP Email Verification**: 6-digit OTP verification on registration
- 🔑 **Forgot Password**: Email-based password reset with secure tokens
- 🛡️ **Rate Limiting**: Brute force protection on login (5 attempts / 15 minutes)
- 📊 **Dashboard**: Interactive dashboard with statistics and charts
- 🤖 **AI Assistant**: Natural language AI assistant for managing projects, tasks, skills
- 📁 **Projects**: Full CRUD with image upload, status tracking, file download, and star system
- ⭐ **Project Stars**: GitHub-style star system to show project popularity
- ✅ **Tasks**: Complete task management with priorities and deadlines
- 🛠️ **Skills**: Track skills with proficiency levels and progress bars
- 🎯 **Learning**: Set learning goals with progress tracking
- 🐙 **GitHub**: Integrate GitHub profile and repositories
- 👤 **Profile**: Customizable developer profile
- 🌐 **Portfolio**: Modern public portfolio page for showcasing work
- 📈 **Analytics**: Detailed analytics with charts and insights
- 🔔 **Notifications**: Internal notification system with announcements
- 📢 **Announcements**: Admin can post announcements visible to all users
- 👑 **Admin**: Admin panel for user management

### UI/UX

- 🎨 **Dark/Light Mode**: Toggle between themes
- 📱 **Responsive**: Works on all devices
- 🔍 **Search & Filter**: Find projects and tasks easily
- 📄 **Pagination**: Handle large datasets efficiently
- 🎯 **Clickable Projects**: View project details from profiles and portfolios
- ⭐ **Star/Unstar Projects**: Show appreciation for others' work

## 🔐 Security Features

- PDO Prepared Statements (SQL Injection Prevention)
- Password Hashing with bcrypt
- CSRF Protection on all forms
- XSS Prevention with htmlspecialchars()
- Session Security with HttpOnly cookies
- Input Validation and Sanitization
- **Rate Limiting** for login attempts (5 attempts / 15 minutes)
- Secure File Uploads with validation
- **OTP Email Verification** for registration
- **Password Reset Tokens** (30 minutes expiry)

## 📁 Project Structure
devtrack/
├── admin/ # Admin panel
│ ├── announcement.php # Create announcement
│ ├── dashboard.php # Admin dashboard
│ ├── manage-announcements.php # Manage announcements
│ ├── users.php # User management
│ ├── system-activity.php # Activity logs
│ └── create_admin.php # Create admin user
│
├── analytics/ # Analytics page
│ └── index.php # Charts & statistics
│
├── assets/
│ ├── css/
│ │ ├── style.css # Main styles
│ │ └── dark-mode.css # Dark mode styles
│ └── js/
│ ├── main.js # Global JS
│ └── dark-mode.js # Theme toggle
│
├── config/
│ ├── database.php # DB config (Live)
│ ├── database_local.php # DB config (Localhost)
│ └── mail.php # PHPMailer configuration
│
├── dashboard/ # User dashboard
│ ├── index.php # Main dashboard + AI Assistant UI
│ └── ai_assistant.php # AI backend logic
│
├── includes/
│ ├── phpmailer/ # PHPMailer library
│ │ ├── PHPMailer.php
│ │ ├── SMTP.php
│ │ └── Exception.php
│ ├── auth.php # Authentication + Rate Limiting + Password Reset
│ ├── functions.php # Helper functions + Notifications + Stars
│ ├── header.php # Header + Navbar + Sidebar
│ ├── footer.php # Footer
│ └── security.php # CSRF, validation, hashing
│
├── learning/ # Learning roadmap
│ ├── index.php # List goals
│ ├── add.php # Add goal
│ ├── edit.php # Edit goal
│ ├── delete.php # Delete goal
│ └── complete.php # Complete goal
│
├── projects/ # Project management
│ ├── index.php # List projects
│ ├── add.php # Add project
│ ├── edit.php # Edit project
│ ├── view.php # View project
│ ├── view-public.php # View public project (with download)
│ ├── delete.php # Delete project
│ └── download.php # Download files
│
├── skills/ # Skills management
│ └── index.php # List & add skills
│
├── tasks/ # Task management
│ ├── index.php # List tasks
│ ├── add.php # Add task
│ ├── edit.php # Edit task
│ ├── delete.php # Delete task
│ └── complete.php # Complete task
│
├── users/ # Developers community
│ ├── index.php # Browse developers
│ └── view.php # View developer profile (with projects, stars)
│
├── github/ # GitHub integration
│ └── index.php # Connect & sync GitHub
│
├── profile/ # User profile
│ ├── index.php # View profile
│ ├── edit.php # Edit profile
│ └── change-password.php # Change password
│
├── portfolio/ # Public portfolio
│ └── index.php # Modern portfolio page
│
├── uploads/ # File uploads
│ ├── profile/ # User avatars
│ └── projects/ # Project images & files
│
├── .htaccess # Security rules
├── config.php # Main configuration
├── index.php # Entry point (Landing page)
├── login.php # Login page (Rate Limiting)
├── register.php # Registration page (OTP Verification)
├── forgot-password.php # Forgot password page
├── reset-password.php # Reset password page
├── logout.php # Logout
├── notifications.php # Notifications
├── 404.php # Custom 404 page
├── error.php # Error page
└── README.md # Documentation

text

## 🧪 Testing

### Test Credentials

- **Admin**: admin@devtrack.com / admin123
- **User**: Register a new account (OTP verification required)

### Testing URLs

- Home: `http://localhost/devtrack/`
- Login: `http://localhost/devtrack/login.php`
- Register: `http://localhost/devtrack/register.php`
- Forgot Password: `http://localhost/devtrack/forgot-password.php`
- Reset Password: `http://localhost/devtrack/reset-password.php`
- Dashboard: `http://localhost/devtrack/dashboard/`
- Portfolio: `http://localhost/devtrack/portfolio/?username=yourusername`
- Developers: `http://localhost/devtrack/users/`
- Admin Panel: `http://localhost/devtrack/admin/dashboard.php`
- Notifications: `http://localhost/devtrack/notifications.php`

## 📝 Development Roadmap

### Phase 1: Foundation ✅

- Database Schema & Setup
- Authentication System
- Dashboard
- Project Management
- Task Management
- Skills Management

### Phase 2: Advanced Features ✅

- Learning Roadmap
- GitHub Integration
- Developer Profile
- Public Portfolio
- Search & Filtering
- Dark/Light Mode

### Phase 3: Admin & Analytics ✅

- Admin Panel
- User Management
- System Activity
- Notifications System
- Announcements
- Analytics & Statistics

### Phase 4: Security & AI ✅

- OTP Email Verification
- Password Reset System
- Rate Limiting (Brute Force Protection)
- AI Assistant (Natural Language Commands)
- Security Enhancements
- Documentation

### Phase 5: Polish & Optimization ✅

- Performance Optimization
- Error Handling
- UI/UX Improvements
- Mobile Responsive AI Panel
- Multi-Language AI Support
- Star System for Projects
- Clickable Projects in Profiles
- Modern Portfolio Design

## 🔧 Configuration

### Database

Update `config/database.php`:

```php
$db_host = 'localhost';
$db_name = 'devtrack';
$db_user = 'root';
$db_pass = '';
Email (PHPMailer)
Update config/mail.php:

php
define('MAIL_HOST', 'smtp.gmail.com');
define('MAIL_PORT', 587);
define('MAIL_USERNAME', 'your-email@gmail.com');
define('MAIL_PASSWORD', 'your-app-password');
define('SITE_URL', 'http://localhost/devtrack/');
GitHub Repository
bash
git clone https://github.com/Ahmad40400/devtrack.git
cd devtrack
🤖 AI Assistant Commands
Projects
create project MyApp by 15 january

delete project MyApp

complete project MyApp

show projects

Tasks
add task Fix bug with high priority by next week

mark task named Fix bug completed

mark task named Fix bug in progress

delete task named Fix bug

show tasks

Skills
add skill Python with 80%

update skill named PHP to 90%

show skills

Learning Goals
create goal Learn React with 20%

update goal named Learn React to 50%

complete goal named Learn React

show goals

General
show my stats

hello

help

📄 License
This project is for educational purposes. Feel free to use and modify.

📞 Contact
Developer: Ahmad

Email: ahmieditz@gmail.com

Website: https://devtracker.free.nf

