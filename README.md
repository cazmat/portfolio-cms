# Portfolio CMS

A simple and elegant Content Management System for portfolios, built with PHP and MySQL.

## Features

- **Portfolio Management**: Create, edit, and manage projects with images, descriptions, and details
- **Project Gallery**: Add multiple images to each project
- **About Page**: Customizable about section with profile image and skills
- **Contact Form**: Built-in contact form that saves messages to database
- **Admin Panel**: Secure admin interface for managing all content
- **Responsive Design**: Mobile-friendly layout using Bootstrap 5
- **Image Upload**: Easy image upload and management
- **SEO-Friendly URLs**: Clean, slug-based URLs for projects
- **Status Management**: Draft and publish workflow for projects

## Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher (or MariaDB 10.2+)
- Apache/Nginx web server
- PDO PHP Extension
- GD or Imagick PHP Extension (for image handling)

## Installation

### 1. Upload Files

Upload all files to your web server's document root or a subdirectory.

### 2. Create Database

Run the `schema.sql` file to create the database and tables:

```bash
mysql -u your_username -p < schema.sql
```

Or import it through phpMyAdmin.

### 3. Configure Database Connection

Edit `includes/config.php` and update the database credentials:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');
define('DB_NAME', 'portfolio_cms');
```

Also update the `SITE_URL`:

```php
define('SITE_URL', 'http://yourdomain.com');
```

### 4. Set Permissions

Make sure the `uploads/` directory is writable:

```bash
chmod 755 uploads/
```

### 5. Access the System

Navigate to `http://yourdomain.com/login.php` and login with:

- **Username**: admin
- **Password**: admin123

**IMPORTANT**: Change these credentials immediately after first login!

You'll be redirected to the admin panel at `/admin/` after login.

## Directory Structure

```
portfolio-cms/
├── admin/                  # Admin panel files
│   ├── includes/          # Admin header and sidebar
│   ├── index.php          # Dashboard
│   ├── login.php          # Login page
│   ├── projects.php       # Projects management
│   ├── project-add.php    # Add new project
│   ├── project-edit.php   # Edit project
│   ├── about.php          # About page management
│   ├── messages.php       # View messages
│   └── settings.php       # Site settings
├── assets/                # Frontend assets
│   ├── css/              # Stylesheets
│   └── js/               # JavaScript files
├── includes/              # Core PHP files
│   ├── config.php        # Configuration
│   ├── database.php      # Database class
│   └── functions.php     # Helper functions
├── uploads/               # Uploaded images
├── index.php             # Homepage
├── project.php           # Single project page
├── about.php             # About page
├── contact.php           # Contact page
├── schema.sql            # Database schema
└── README.md             # This file
```

## Usage

### Managing Projects

1. Login to the admin panel
2. Go to "Projects" in the sidebar
3. Click "Add New Project"
4. Fill in the project details:
   - Title (required)
   - Slug (auto-generated if left empty)
   - Description
   - Content
   - Featured Image
   - Category, Tags, Client
   - Project URL
   - Completion Date
   - Display Order (lower numbers appear first)
   - Status (Draft/Published)

### Managing About Page

1. Go to "About Page" in admin
2. Update your bio content
3. Upload profile image
4. Add your skills (comma-separated)
5. Save changes

### Viewing Messages

1. Go to "Messages" in admin
2. View all contact form submissions
3. Mark as read/unread
4. Archive or delete messages

### Site Settings

1. Go to "Settings" in admin
2. Update:
   - Site Name
   - Tagline
   - Contact Email
   - Social Media Links
   - Items per page

## Security Features

- Password hashing with bcrypt
- SQL injection prevention with PDO prepared statements
- XSS protection with input sanitization
- CSRF protection ready for implementation
- Secure file upload validation
- Session-based authentication

## Customization

### Changing Colors

Edit `assets/css/style.css` and modify the color variables in the hero section and other elements.

### Adding New Pages

1. Create a new PHP file in the root directory
2. Include the necessary files:
```php
require_once 'includes/config.php';
require_once 'includes/database.php';
require_once 'includes/functions.php';
```
3. Add navigation link in the header

### Custom Fields

To add custom fields to projects:

1. Add column to database:
```sql
ALTER TABLE projects ADD COLUMN your_field VARCHAR(255);
```

2. Update `project-add.php` and `project-edit.php` forms
3. Update `project.php` display template

## Database Schema

### Tables

- **users**: Admin users
- **projects**: Portfolio projects
- **project_images**: Project gallery images
- **about**: About page content
- **messages**: Contact form messages
- **settings**: Site configuration

See `schema.sql` for detailed schema information.

## Troubleshooting

### Images Not Uploading

- Check `uploads/` directory permissions (755 or 777)
- Verify PHP upload limits in `php.ini`:
  - `upload_max_filesize = 10M`
  - `post_max_size = 10M`

### Database Connection Error

- Verify credentials in `includes/config.php`
- Check if MySQL service is running
- Ensure database exists

### Admin Panel Not Accessible

- Clear browser cache and cookies
- Check `.htaccess` if using Apache
- Verify file permissions

## Backup

Regular backups are recommended:

### Database Backup
```bash
mysqldump -u your_username -p portfolio_cms > backup.sql
```

### Files Backup
```bash
tar -czf portfolio-backup.tar.gz /path/to/portfolio-cms
```

## Updates

To update the CMS:

1. Backup database and files
2. Upload new files (except `includes/config.php`)
3. Run any new SQL migrations
4. Test thoroughly

## Support

For issues or questions:
- Check existing documentation
- Review error logs
- Check PHP error reporting

## License

This project is open source and available for personal and commercial use.

## Credits

Built with:
- PHP
- MySQL
- Bootstrap 5
- Font Awesome (optional)

## Changelog

### Version 1.0.0
- Initial release
- Project management
- Image gallery
- Contact form
- Admin panel
- Responsive design

---

**Note**: Always change default admin credentials and keep your installation up to date!
