# PHP Project Management System

A comprehensive web-based project management application built with PHP, designed for personal and small team project tracking with a focus on task management, contact organization, and progress monitoring.

## Features

### 🔐 User Authentication & Security
- **User Registration** with email verification
- **Secure Login** with username or email
- **Password Reset** via email with time-limited tokens
- **Session Management** with configurable timeouts
- **Password Hashing** using Argon2ID for maximum security

### 📊 Dashboard & Analytics
- **Project Overview** with completion percentages
- **Task Statistics** showing pending and completed items
- **Upcoming Deadlines** tracking with visual indicators
- **Progress Visualization** through interactive charts and progress bars
- **Recent Activity** feed for quick project updates

### 📁 Project Management
- **Project Creation & Editing** with detailed descriptions
- **Project Status Tracking** (Not Started, In Progress, Completed, On Hold)
- **Completion Date Management** with expected vs actual completion
- **Responsible Person Assignment** for accountability
- **Project Statistics** including task counts and completion rates

### ✅ Task Management System
- **Hierarchical Tasks** with unlimited subtask nesting
- **Task Assignment** to responsible persons and contact persons
- **Progress Tracking** with percentage-based completion
- **Deadline Management** with expected and actual completion dates
- **Status Management** for comprehensive task lifecycle tracking
- **Automatic Completion Calculation** based on subtask progress

### 👥 Contact Management
- **Global Contact Database** shared across all projects
- **Project-Specific Contacts** with relationship mapping
- **Multi-Channel Communication** (Email, Phone, WeChat, LINE, Facebook, LinkedIn)
- **Contact Descriptions** for detailed relationship context
- **Contact Assignment** to tasks and projects

### 🌐 Advanced Features
- **Timezone Management** with UTC storage and user-local display
- **Email Notifications** using PHPMailer with SMTP support
- **Responsive Design** optimized for desktop and mobile devices
- **Database Views** for optimized query performance
- **AJAX Interactions** for seamless user experience
- **Form Validation** with real-time feedback

## Technology Stack

### Backend
- **PHP 8.3+** - Modern PHP with type declarations and performance improvements
- **MySQL/MariaDB** - Robust relational database with ACID compliance
- **PDO** - Secure database abstraction layer with prepared statements

### Frontend
- **HTML5** - Semantic markup for accessibility
- **CSS3** - Modern styling with Flexbox and Grid layouts
- **JavaScript (ES6+)** - Progressive enhancement and interactivity
- **Font Awesome** - Professional icon library

### Email & Communication
- **PHPMailer** - Enterprise-grade email handling with SMTP support
- **SMTP Protocol** - Reliable email delivery with authentication

### Security & Performance
- **Password Hashing** - Argon2ID algorithm for password security
- **CSRF Protection** - Session-based request validation
- **SQL Injection Prevention** - Parameterized queries throughout
- **XSS Protection** - Output escaping and input sanitization

## Installation

### Prerequisites
- PHP 8.3 or higher with extensions:
  - PDO MySQL
  - OpenSSL
  - cURL
  - JSON
- MySQL 5.7+ or MariaDB 10.2+
- Web server (Nginx recommended, Apache supported)
- SMTP server access for email functionality

### Quick Installation

1. **Download and Extract**
   ```bash
   # Clone or download the project files
   git clone <repository-url> project-management
   cd project-management
   ```

2. **Set Permissions**
   ```bash
   # Ensure web server can write to the application directory
   chmod 755 -R .
   chmod 777 . # For config.php creation during installation
   ```

3. **Install Dependencies**
   ```bash
   # Install PHPMailer via Composer (recommended)
   composer install
   
   # OR download PHPMailer manually to vendor/ directory
   ```

4. **Web Server Configuration**

   **For Nginx:**
   ```nginx
   server {
       listen 80;
       server_name your-domain.com;
       root /path/to/project-management;
       index index.php;
       
       location / {
           try_files $uri $uri/ /index.php?$query_string;
       }
       
       location ~ \.php$ {
           fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
           fastcgi_index index.php;
           fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
           include fastcgi_params;
       }
       
       # Security headers
       add_header X-Content-Type-Options nosniff;
       add_header X-Frame-Options DENY;
       add_header X-XSS-Protection "1; mode=block";
   }
   ```

   **For Apache (.htaccess is included):**
   ```apache
   # Ensure mod_rewrite is enabled
   a2enmod rewrite
   systemctl restart apache2
   ```

5. **Run Installation Wizard**
   - Navigate to `http://your-domain.com` in your web browser
   - The installation wizard will automatically start
   - Follow the 3-step installation process:
     - **Step 1:** Welcome and requirements check
     - **Step 2:** Database configuration and setup
     - **Step 3:** SMTP email configuration

6. **Complete Setup**
   - Delete `install.php` after successful installation
   - Create your first user account
   - Start managing your projects!

### Manual Configuration

If you prefer manual configuration, copy `config.template.php` to `config.php` and configure:

```php
// Database Configuration
const DB_HOST = 'localhost';
const DB_NAME = 'project_management';
const DB_USER = 'your_db_user';
const DB_PASS = 'your_db_password';

// SMTP Configuration
const SMTP_HOST = 'smtp.gmail.com';
const SMTP_PORT = 587;
const SMTP_PROTOCOL = 'tls';
const SMTP_USER = 'your_email@gmail.com';
const SMTP_PASS = 'your_app_password';
```

## File Structure

```
project-management/
├── api/                    # API endpoints (future expansion)
├── assets/
│   ├── css/
│   │   └── style.css      # Main stylesheet
│   └── js/
│       └── script.js      # Interactive JavaScript
├── database/
│   └── schema.sql         # Database structure
├── docs/
│   └── requirements.md    # Detailed requirements
├── includes/
│   ├── Database.php       # Database connection class
│   ├── EmailService.php   # Email handling
│   ├── functions.php      # Utility functions
│   ├── header.php         # Page header template
│   └── footer.php         # Page footer template
├── pages/                 # Additional page components
├── vendor/                # Composer dependencies
├── composer.json          # Dependency management
├── config.template.php    # Configuration template
├── install.php           # Installation wizard
├── index.php             # Application entry point
├── login.php             # Authentication pages
├── register.php
├── forgot_password.php
├── reset_password.php
├── logout.php
├── dashboard.php         # Main dashboard
├── projects.php          # Project listing
└── README.md
```

## Database Schema

The application uses a well-structured relational database with the following core tables:

- **users** - User accounts and authentication
- **projects** - Project information and metadata
- **tasks** - Hierarchical task structure with subtask support
- **contacts** - Global contact database
- **project_contacts** - Project-contact relationships
- **password_reset_tokens** - Secure password reset functionality

The schema includes optimized indexes, foreign key constraints, and database views for enhanced performance.

## Usage Guide

### Getting Started
1. **First Login** - Register your account and verify email
2. **Create Project** - Set up your first project with details
3. **Add Contacts** - Import or create project stakeholders
4. **Create Tasks** - Break down work into manageable tasks
5. **Track Progress** - Update completion percentages and status

### Project Management Workflow
1. **Project Creation** - Define scope, timeline, and responsibilities
2. **Task Breakdown** - Create hierarchical task structure
3. **Team Assignment** - Assign tasks to team members and contacts
4. **Progress Monitoring** - Regular updates and status reviews
5. **Completion Tracking** - Mark milestones and final delivery

### Best Practices
- **Regular Updates** - Keep task progress current for accurate reporting
- **Clear Descriptions** - Use detailed task and project descriptions
- **Realistic Deadlines** - Set achievable expected completion dates
- **Contact Management** - Maintain up-to-date contact information
- **Status Tracking** - Use status changes to reflect project lifecycle

## Security Features

- **Password Security** - Argon2ID hashing with salt
- **Session Management** - Secure session handling with timeouts
- **CSRF Protection** - Cross-site request forgery prevention
- **Input Validation** - Comprehensive server-side validation
- **SQL Injection Prevention** - Parameterized queries throughout
- **XSS Protection** - Output escaping and sanitization
- **Email Security** - Token-based password resets with expiration

## Contributing

This is a personal project management system. For feature requests or bug reports, please create an issue with detailed information including:
- Environment details (PHP version, database version)
- Steps to reproduce the issue
- Expected vs actual behavior
- Screenshots if applicable

## License

This project is released under the MIT License. See LICENSE file for details.

## Support

For technical support or questions:
- Review the documentation in `/docs/requirements.md`
- Check the installation wizard for configuration issues
- Verify PHP and database requirements are met
- Ensure proper file permissions are set

## Version History

### v1.0.0 (Current)
- Complete project management system
- User authentication and security
- Project, task, and contact management
- Responsive web interface
- Email notification system
- Installation wizard
- Database optimization with views and indexes
