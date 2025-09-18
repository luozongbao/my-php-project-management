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

### ✅ Advanced Task Management System
- **Hierarchical Tasks** with unlimited subtask nesting and parent-child relationships
- **Task Assignment** to responsible persons and contact persons with notification
- **Progress Tracking** with real-time percentage-based completion updates
- **Automatic Progress Calculation** - parent task completion calculated from subtasks
- **Deadline Management** with expected and actual completion dates
- **Status Management** for comprehensive task lifecycle tracking (Not Started, In Progress, Completed, On Hold)
- **Task Filtering & Search** - filter by project, status, assignee, and search by keywords
- **Interactive Task Detail Pages** with subtask management and activity tracking
- **AJAX Progress Updates** for seamless real-time task progress modification

### 👥 Comprehensive Contact Management
- **Global Contact Database** shared across all projects with centralized management
- **Project-Specific Contacts** with flexible relationship mapping and assignments
- **Multi-Channel Communication Support** (Email, Phone, Mobile, WeChat, LINE, Facebook, LinkedIn)
- **Contact Profiles** with company information, position, and detailed descriptions
- **Contact Statistics** showing project associations and task assignments
- **Communication Integration** - direct email/phone links and social media profiles
- **Contact Assignment to Tasks** for stakeholder involvement and communication
- **Contact Filtering & Search** across all projects with advanced search capabilities
- **Contact Detail Pages** with relationship history and project associations

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
├── ajax/                  # AJAX endpoints for dynamic updates
│   ├── get_project_contacts.php
│   ├── update_task_progress.php
│   └── update_task_status.php
├── pages/                 # Additional page components
├── vendor/                # Composer dependencies
├── composer.json          # Dependency management
├── config.template.php    # Configuration template
├── install.php           # Installation wizard
├── index.php             # Application entry point
├── login.php             # Authentication system
├── register.php
├── forgot_password.php
├── reset_password.php
├── logout.php
├── dashboard.php         # Main dashboard with statistics
├── projects.php          # Project listing and management
├── project_detail.php    # Detailed project view
├── project_edit.php      # Project creation and editing
├── tasks.php             # Task listing with filters
├── task_detail.php       # Detailed task view with subtasks
├── task_edit.php         # Task creation and editing
├── contacts.php          # Contact listing and management
├── contact_detail.php    # Detailed contact view
├── contact_edit.php      # Contact creation and editing
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
1. **Account Registration** - Register your account and verify email address
2. **Project Setup** - Create your first project with scope and timeline
3. **Contact Management** - Add project stakeholders and team members
4. **Task Creation** - Break down work into hierarchical tasks and subtasks
5. **Assignment & Tracking** - Assign tasks to people and track progress in real-time

### Complete Project Management Workflow
1. **Project Initialization**
   - Create project with detailed description and timeline
   - Set responsible person and expected completion date
   - Define project status and milestones

2. **Contact & Team Setup**
   - Add contacts for project stakeholders
   - Assign contacts to relevant projects
   - Maintain contact information and communication channels

3. **Task Structure Development**
   - Create main tasks aligned with project goals
   - Break down complex tasks into manageable subtasks
   - Set task priorities and dependencies

4. **Assignment & Responsibility**
   - Assign tasks to responsible team members
   - Associate contact persons for stakeholder communication
   - Set expected completion dates for each task

5. **Progress Monitoring & Updates**
   - Real-time progress tracking with percentage completion
   - Automatic parent task progress calculation from subtasks
   - Status updates and milestone tracking

6. **Communication & Collaboration**
   - Direct communication links (email, phone) with contacts
   - Task comments and progress notes
   - Project activity feeds and updates

7. **Completion & Closure**
   - Mark tasks as completed with actual completion dates
   - Review project outcomes and completion statistics
   - Archive completed projects for future reference

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
- **Complete Project Management System** with full lifecycle support
- **Advanced User Authentication** with email verification and secure password reset
- **Hierarchical Task Management** with unlimited subtask nesting and automatic progress calculation
- **Comprehensive Contact Management** with multi-channel communication support
- **Real-time Progress Tracking** with AJAX updates and interactive interfaces
- **Responsive Web Interface** optimized for desktop and mobile devices
- **Email Notification System** with SMTP integration and template support
- **One-Click Installation Wizard** with automated database setup
- **Database Optimization** with views, indexes, and query performance tuning
- **Security Features** including CSRF protection, XSS prevention, and secure session management
- **Modern PHP Architecture** with object-oriented design and best practices
