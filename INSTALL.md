# Portfolio CMS - Quick Installation Guide

## Step-by-Step Installation

### 1. Prerequisites
- Web server (Apache/Nginx)
- PHP 7.4 or higher
- MySQL 5.7 or higher
- phpMyAdmin (optional, for easier database management)

### 2. Upload Files
Upload all files from the `portfolio-cms` folder to your web server:
- Via FTP/SFTP to your hosting
- Or place in your local server directory (e.g., `/var/www/html/portfolio-cms`)

### 3. Create Database
Open phpMyAdmin or MySQL command line and run:

```sql
CREATE DATABASE portfolio_cms CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Then import the schema:
- In phpMyAdmin: Select the database → Import → Choose `schema.sql`
- Or via command line:
```bash
mysql -u your_username -p portfolio_cms < schema.sql
```

### 4. Configure Database Connection
Edit `includes/config.php`:

```php
define('DB_HOST', 'localhost');        // Usually 'localhost'
define('DB_USER', 'your_username');    // Your MySQL username
define('DB_PASS', 'your_password');    // Your MySQL password
define('DB_NAME', 'portfolio_cms');    // Database name

define('SITE_URL', 'http://yourdomain.com'); // Your website URL
```

### 5. Set File Permissions
Make the uploads directory writable:

```bash
chmod 755 uploads/
# Or if needed:
chmod 777 uploads/
```

### 6. Test Installation
1. Visit your website: `http://yourdomain.com`
2. You should see the portfolio homepage (currently empty)

### 7. Login to Admin Panel
1. Go to: `http://yourdomain.com/admin/`
2. Use default credentials:
   - Username: `admin`
   - Password: `admin123`
3. **IMPORTANT**: Change these credentials immediately!

### 8. Configure Your Site
1. Go to **Settings** and update:
   - Site name
   - Tagline
   - Contact email
   - Social media links

2. Go to **About Page** and add:
   - Your bio
   - Profile picture
   - Skills

3. Go to **Projects** and add your portfolio items

## Troubleshooting

### "Connection failed" Error
- Check database credentials in `includes/config.php`
- Verify MySQL is running
- Ensure database exists

### Images Not Uploading
- Check uploads directory exists and is writable
- Verify PHP upload settings:
  ```ini
  upload_max_filesize = 10M
  post_max_size = 10M
  ```

### Admin Panel Not Loading
- Check file permissions
- Clear browser cache
- Verify .htaccess is properly uploaded

### Blank Page
- Enable PHP error reporting in `includes/config.php`:
  ```php
  ini_set('display_errors', 1);
  error_reporting(E_ALL);
  ```

## Security Checklist

After installation:
- [ ] Change admin password
- [ ] Update database credentials
- [ ] Remove or secure schema.sql
- [ ] Enable HTTPS if available
- [ ] Restrict admin folder by IP (optional)
- [ ] Keep PHP and MySQL updated

## Next Steps

1. **Add Content**: Start adding your portfolio projects
2. **Customize Design**: Edit `assets/css/style.css` for custom styling
3. **Test**: Check all pages and forms work correctly
4. **Launch**: Share your portfolio with the world!

## Support

For issues:
1. Check error logs
2. Review README.md for detailed documentation
3. Verify all installation steps were followed

## File Structure

```
portfolio-cms/
├── admin/              # Admin panel
├── assets/             # CSS/JS files
├── includes/           # Core PHP files
├── uploads/            # Image uploads
├── index.php           # Homepage
├── project.php         # Project details
├── about.php           # About page
├── contact.php         # Contact form
└── schema.sql          # Database schema
```

Enjoy your new portfolio CMS!
