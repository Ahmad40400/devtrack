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
- 📊 **Dashboard**: Interactive dashboard with statistics and charts
- 📁 **Projects**: Full CRUD with image upload and status tracking
- ✅ **Tasks**: Complete task management with priorities and deadlines
- 🛠️ **Skills**: Track skills with proficiency levels and progress bars
- 🎯 **Learning**: Set learning goals with progress tracking
- 🐙 **GitHub**: Integrate GitHub profile and repositories
- 👤 **Profile**: Customizable developer profile
- 🌐 **Portfolio**: Public portfolio page for showcasing work
- 📈 **Analytics**: Detailed analytics with charts and insights
- 🔔 **Notifications**: Internal notification system
- 👑 **Admin**: Admin panel for user management

### UI/UX

- 🎨 **Dark/Light Mode**: Toggle between themes
- 📱 **Responsive**: Works on all devices
- 🔍 **Search & Filter**: Find projects and tasks easily
- 📄 **Pagination**: Handle large datasets efficiently

## 🔐 Security Features

- PDO Prepared Statements (SQL Injection Prevention)
- Password Hashing with bcrypt
- CSRF Protection on all forms
- XSS Prevention with htmlspecialchars()
- Session Security with HttpOnly cookies
- Input Validation and Sanitization
- Rate Limiting for login attempts
- Secure File Uploads with validation

## 📁 Project Structure

devtrack/
├── admin/ # Admin panel
├── analytics/ # Analytics page
├── api/ # API endpoints
├── assets/ # CSS, JS, images
├── auth/ # Authentication
├── config/ # Configuration
├── dashboard/ # User dashboard
├── github/ # GitHub integration
├── includes/ # Core includes
├── learning/ # Learning roadmap
├── portfolio/ # Public portfolio
├── profile/ # User profile
├── projects/ # Project management
├── skills/ # Skills management
├── tasks/ # Task management
├── uploads/ # File uploads
├── .htaccess # Security rules
├── config.php # Main configuration
├── index.php # Entry point
├── login.php # Login page
├── register.php # Registration page
├── logout.php # Logout
├── notifications.php # Notifications
├── 404.php # Error page
└── README.md # Documentation

## 🧪 Testing

### Test Credentials

- **Admin**: admin@devtrack.com / admin123
- **User**: Register a new account

### Testing URLs

- Home: `http://localhost/devtrack/`
- Login: `http://localhost/devtrack/login.php`
- Register: `http://localhost/devtrack/register.php`
- Dashboard: `http://localhost/devtrack/dashboard/`
- Portfolio: `http://localhost/devtrack/portfolio/?username=yourusername`

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
- Analytics & Statistics

### Phase 4: Polish & Optimization ✅

- Security Enhancements
- Performance Optimization
- Error Handling
- Documentation
- UI/UX Improvements

## 🔧 Configuration

### Database

Update `config/database.php`:

```php
$db_host = 'localhost';
$db_name = 'devtrack';
$db_user = 'root';
$db_pass = '';
```
