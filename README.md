# Personal Portfolio CMS

A simple Content Management System (CMS) for my personal portfolio, built with PHP and MySQL.

## 🌟 Features

### Portfolio Management
- **Project Showcase** - Display portfolio projects with images, descriptions, and details
- **Image Galleries** - Upload and manage multiple images per project
- **Categories & Tags** - Organize projects for easy navigation
- **Featured Images** - Set primary images for project cards
- **SEO-Friendly URLs** - Custom slugs for better search engine visibility
- **Publish Control** - Draft/Published status with display ordering

### Client Management System
- **Three-Tier User Roles:**
  - **Admin** - Full system access and management
  - **Client** - View assigned projects only
  - **Family** - Schedule access only (no projects)

- **Client Portal** (`/client/`)
  - Dashboard with assigned projects
  - Project detail views
  - File downloads
  - Profile management
  - Password changes

- **Family Portal** (`/family/`)
  - Dashboard with schedule overview
  - Work schedule calendar
  - Profile management
  - No project access (use public portfolio)

### Work Schedule
- **Schedule Management** (Admin & Family only)
  - Create meetings, deadlines, shoots, events
  - Track event status (scheduled/completed/cancelled)
  - Color-coded event types
  - Location and time tracking
  - Notes and descriptions
  - Calendar view with filtering

### Project File Downloads
- **Secure File Delivery**
  - Upload files to projects (admin)
  - Client download portal
  - Permission-based access
  - Download tracking
  - File descriptions
  - Multiple files per project
  - Protected storage

### Invoice System
- **Professional Invoicing**
  - Create invoices linked to clients and projects
  - Multiple line items with quantities and prices
  - Automatic tax calculation
  - Invoice status tracking (draft/sent/paid/overdue)
  - Invoice number generation
  - Payment tracking
  - Financial overview (paid/unpaid totals)

### Contact Form with Spam Protection
- **Smart Spam Detection**
  - Automatic repeat sender detection
  - Suspicious email pattern recognition
  - Manual spam override
  - Whitelist for trusted senders
  - Blacklist for permanent blocking
  - Silent blocking (spammers don't know)

- **Whitelist System**
  - Trust specific email addresses
  - Override spam detection
  - Add notes for trusted contacts
  - View message history

- **Blacklist System**
  - Permanently block emails
  - Silent rejection (shows success)
  - Manage blocked addresses
  - Track block reasons

### Message Management
- **Inbox Organization**
  - Read/Unread status
  - Archive messages
  - Status filtering
  - Spam filtering (spam/suspicious/clean)
  - Quick actions

### Authentication & Security
- **Secure Login System**
  - Role-based access control
  - Password encryption (bcrypt)
  - Session management (8-hour timeout)
  - Remember Me (30-day option)
  - Secure tokens
  - Activity tracking

- **Remember Me Feature**
  - Optional 30-day login
  - Secure token storage
  - Multi-device support
  - Manual logout clears tokens

### Admin Panel (`/admin/`)
- **Dashboard** - System overview and quick stats
- **Projects** - Full CRUD management
- **Schedule** - Calendar and event management
- **Users** - Create and manage all user types
- **Invoices** - Complete invoicing system
- **Messages** - Contact form submissions
- **Whitelist** - Trusted email management
- **Blacklist** - Blocked email management
- **About Page** - Edit about content
- **Settings** - Site configuration

## 📋 Requirements

- **PHP**: 7.4 or higher
- **MySQL**: 5.5 or higher
- **Web Server**: Apache with mod_rewrite
- **Extensions**: mysqli, gd (for image handling)

## 🚀 Installation

### New Installation

1. **Extract Files**
   ```bash
   unzip portfolio-cms.zip
   cd portfolio-cms
   ```

2. **Create Database**
   ```sql
   CREATE DATABASE portfolio_cms CHARACTER SET utf8 COLLATE utf8_general_ci;
   ```

3. **Import Schema**
   ```bash
   mysql -u username -p portfolio_cms < schema.sql
   ```
   
   Or use the alternative schema if you have MySQL compatibility issues:
   ```bash
   mysql -u username -p portfolio_cms < schema-alternative.sql
   ```

4. **Configure Database**
   
   Edit `includes/config.php`:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'your_username');
   define('DB_PASS', 'your_password');
   define('DB_NAME', 'portfolio_cms');
   define('SITE_URL', 'http://yourdomain.com');
   ```

5. **Set Permissions**
   ```bash
   chmod 755 uploads/
   chmod 755 downloads/
   ```

6. **Access System**
   - Login: `http://yourdomain.com/login.php`
   - Default credentials:
     - Username: `admin`
     - Password: `admin123`
   - **IMPORTANT**: Change password immediately after first login!

### Existing Installation Upgrades

If you have an older version installed, run migrations:

```bash
# Add family role
mysql -u username -p portfolio_cms < migration-add-family-role.sql

# Add schedule system
mysql -u username -p portfolio_cms < migration-add-schedule.sql

# Add remember me
mysql -u username -p portfolio_cms < migration-add-remember-me.sql

# Add spam detection
mysql -u username -p portfolio_cms < migration-add-spam-detection.sql

# Add whitelist
mysql -u username -p portfolio_cms < migration-add-whitelist.sql

# Add blacklist
mysql -u username -p portfolio_cms < migration-add-blacklist.sql

# Add invoices
mysql -u username -p portfolio_cms < migration-add-invoices.sql

# Add downloads
mysql -u username -p portfolio_cms < migration-add-downloads.sql
```

## 📁 Directory Structure

```
portfolio-cms/
├── admin/                  # Admin panel
│   ├── includes/          # Admin navigation
│   ├── *.php             # Admin pages
│   └── ...
├── client/                # Client portal
│   ├── includes/         # Client navigation
│   ├── dashboard.php     # Client dashboard
│   ├── downloads.php     # File downloads
│   └── ...
├── family/                # Family portal
│   ├── includes/         # Family navigation
│   ├── dashboard.php     # Family dashboard
│   ├── schedule.php      # Schedule viewer
│   └── ...
├── assets/                # CSS, JS, images
├── includes/              # Core PHP files
│   ├── config.php        # Configuration
│   ├── database.php      # Database class
│   └── functions.php     # Helper functions
├── uploads/               # Project images (755)
├── downloads/             # Client files (755)
├── index.php              # Public homepage
├── project.php            # Project detail page
├── contact.php            # Contact form
├── login.php              # Login page
└── schema.sql             # Database schema
```

## 🔐 User Roles & Permissions

### Admin
- **Access**: Everything
- **Can**:
  - Manage all projects
  - Upload files
  - Create/edit users
  - View/manage schedule
  - Create invoices
  - Manage messages
  - Configure settings

### Client
- **Access**: Client portal (`/client/`)
- **Can**:
  - View assigned projects
  - Download files (if permitted)
  - Manage profile
  - Change password
- **Cannot**:
  - See schedule
  - Access admin panel
  - View other clients' projects

### Family
- **Access**: Family portal (`/family/`)
- **Can**:
  - View work schedule
  - Manage profile
  - View public portfolio
- **Cannot**:
  - Access projects
  - Access admin panel
  - View client information

## 🎨 Usage Guide

### Creating Projects

1. Login as admin
2. Navigate to **Projects** → **Add New**
3. Enter project details:
   - Title, description, content
   - Category and tags
   - Client name
   - Completion date
   - Status (draft/published)
4. Upload featured image
5. Add gallery images
6. Set display order
7. Save project

### Assigning Projects to Clients

1. Edit project
2. Scroll to **Client Access** section
3. Check boxes for:
   - **Can View** - Client can see project
   - **Can Download** - Client can download files
4. Save access settings

### Uploading Client Files

1. Edit project
2. Click **📁 Files** button
3. Upload file
4. Add description
5. File now available in client downloads

### Creating Invoices

1. Navigate to **Invoices** → **Create Invoice**
2. Select client and optional project
3. Add line items:
   - Description
   - Quantity
   - Unit price
4. Set tax rate (if applicable)
5. Add notes and payment terms
6. Save as draft or mark as sent

### Managing Schedule

1. Navigate to **Schedule** → **Add Event**
2. Enter event details:
   - Title, description
   - Date and time
   - Location
   - Event type
   - Status
3. Save event
4. Family members can view in their portal

### Spam Management

**Automatic Detection:**
- System flags repeat senders
- Detects suspicious email patterns
- Shows spam status badges

**Manual Actions:**
- Mark as spam/not spam
- Add to whitelist (trust forever)
- Add to blacklist (block forever)

**Whitelist:**
- Admin → Whitelist
- Add trusted email addresses
- Add notes for reference

**Blacklist:**
- Admin → Blacklist
- Block unwanted emails
- Silent rejection (spammer doesn't know)

## ⚙️ Configuration

### Session Timeout

Default: 8 hours

To change, edit `includes/config.php`:
```php
define('SESSION_TIMEOUT', 28800); // 8 hours in seconds
```

Common values:
- 1 hour: `3600`
- 2 hours: `7200`
- 4 hours: `14400`
- 8 hours: `28800`
- 24 hours: `86400`

### Remember Me Duration

Default: 30 days

To change, edit `includes/functions.php`:
```php
$expiry = time() + (30 * 24 * 60 * 60); // Change 30 to desired days
```

### Upload Limits

Controlled by PHP configuration:
- `upload_max_filesize` - Maximum file size
- `post_max_size` - Maximum POST size
- `max_file_uploads` - Maximum files per upload

Edit `php.ini` or `.htaccess`:
```apache
php_value upload_max_filesize 20M
php_value post_max_size 20M
```

## 🔒 Security Features

- **Password Hashing**: bcrypt encryption
- **SQL Injection Protection**: Prepared statements
- **XSS Prevention**: Input sanitization
- **CSRF Protection**: Session validation
- **File Upload Validation**: Type and size checks
- **Role-Based Access Control**: Permission checks
- **Secure File Storage**: Protected downloads directory
- **Session Security**: Timeout and activity tracking
- **Remember Me Tokens**: SHA-256 hashed storage

## 📖 Documentation

- **INSTALL.md** - Detailed installation guide
- **USER-MANAGEMENT.md** - User roles and permissions
- **SESSION-TIMEOUT.md** - Session configuration
- **REMEMBER-ME.md** - Remember me feature details
- **SPAM-DETECTION.md** - Spam system documentation
- **DATABASE.md** - Database schema reference

## 🐛 Troubleshooting

### Login Issues

**Problem**: Can't login with default credentials

**Solution**:
```sql
-- Reset admin password
UPDATE users SET password = '$2y$10$7Rq5H4zT4gzI1xGdOlBqxeF5TIUlZWQXOCGKQOKYmNPVQJvLJ9Yv6' 
WHERE username = 'admin';
-- Password is: admin123
```

### Upload Errors

**Problem**: Can't upload images/files

**Solution**:
```bash
# Check permissions
chmod 755 uploads/
chmod 755 downloads/

# Check PHP limits
php -i | grep upload_max_filesize
php -i | grep post_max_size
```

### Database Connection Errors

**Problem**: Can't connect to database

**Solution**:
1. Verify credentials in `includes/config.php`
2. Check MySQL is running
3. Verify user has permissions:
   ```sql
   GRANT ALL PRIVILEGES ON portfolio_cms.* TO 'username'@'localhost';
   FLUSH PRIVILEGES;
   ```

### Session Timeout Issues

**Problem**: Logged out too quickly

**Solution**:
- Increase `SESSION_TIMEOUT` in `includes/config.php`
- Check server session settings
- Add to `.htaccess`:
  ```apache
  php_value session.gc_maxlifetime 28800
  ```

### File Download Issues

**Problem**: Clients can't download files

**Solution**:
1. Check download permissions in project access
2. Verify file exists in `/downloads/` directory
3. Check `.htaccess` in downloads directory
4. Verify client has `can_download = 1`

## 🔄 Updates & Migrations

Always backup before updating:
```bash
# Backup database
mysqldump -u username -p portfolio_cms > backup_$(date +%Y%m%d).sql

# Backup files
tar -czf files_backup_$(date +%Y%m%d).tar.gz uploads/ downloads/
```

Run migrations in order:
1. Test on development/staging first
2. Backup production
3. Run migration SQL files
4. Test functionality
5. Monitor for errors

## 🆘 Support

For issues and questions:
1. Check documentation files
2. Review troubleshooting section
3. Check file permissions
4. Verify PHP/MySQL versions
5. Review error logs

## 📝 Default Credentials

**CRITICAL**: Change these immediately after installation!

- **Username**: `admin`
- **Password**: `admin123`

Change password:
1. Login with default credentials
2. Navigate to Profile
3. Change password
4. Save changes

## 🎯 Best Practices

### Security
- Change default admin password immediately
- Use strong passwords
- Keep PHP/MySQL updated
- Regular backups
- Limit admin accounts
- Monitor user activity

### Performance
- Optimize images before upload
- Use appropriate image sizes
- Regular database maintenance
- Clean up old messages
- Archive completed projects

### Organization
- Use consistent naming conventions
- Add descriptions to files
- Tag projects appropriately
- Update project statuses
- Document custom changes

## 📊 Database Tables

Core tables:
- `users` - User accounts (admin/client/family)
- `projects` - Portfolio projects
- `project_images` - Project galleries
- `project_access` - Client permissions
- `project_files` - Downloadable files
- `schedule` - Work schedule events
- `invoices` - Invoice records
- `invoice_items` - Invoice line items
- `messages` - Contact form submissions
- `email_whitelist` - Trusted senders
- `email_blacklist` - Blocked senders
- `remember_tokens` - Persistent login
- `about` - About page content
- `settings` - Site configuration

## 🌐 URLs

**Public Pages:**
- `/` - Homepage
- `/project.php?slug=project-name` - Project detail
- `/about.php` - About page
- `/contact.php` - Contact form
- `/login.php` - Login page

**Admin Panel:**
- `/admin/` - Dashboard
- Requires admin role

**Client Portal:**
- `/client/` - Client dashboard
- Requires client role

**Family Portal:**
- `/family/` - Family dashboard
- Requires family role

## 🎨 Customization

### Styling
Edit `assets/css/style.css` for custom styles

### Branding
1. Update site name in Settings
2. Replace logo/favicon
3. Customize colors in CSS
4. Edit footer content

### Email Templates
Modify email content in respective PHP files

## 📦 What's Included

- ✅ Complete PHP/MySQL application
- ✅ Responsive Bootstrap design
- ✅ Admin panel
- ✅ Client portal
- ✅ Family portal
- ✅ Full documentation
- ✅ Database schema
- ✅ Migration files
- ✅ Sample data
- ✅ Security features
- ✅ File upload handling

## 🚀 Production Deployment

Before going live:
1. ✅ Change default admin password
2. ✅ Update `SITE_URL` in config
3. ✅ Set proper file permissions
4. ✅ Enable HTTPS
5. ✅ Configure email settings
6. ✅ Test all functionality
7. ✅ Set up regular backups
8. ✅ Review security settings
9. ✅ Monitor error logs
10. ✅ Create initial content

## 📄 License

This is a custom-built portfolio CMS. All rights reserved.

## 🎉 Getting Started

1. Install following instructions above
2. Login with default credentials
3. **Change admin password**
4. Update site settings
5. Create your first project
6. Add users
7. Customize styling
8. Launch your portfolio!

---

**Version**: 2.0  
**Last Updated**: December 2024  
**PHP**: 7.4+  
**MySQL**: 5.5+

For detailed information on specific features, see the respective documentation files included in the package.