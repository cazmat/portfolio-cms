# Login Troubleshooting Guide

## Default Login Credentials

**Username:** `admin`  
**Password:** `admin123`

⚠️ **Important:** Both are lowercase, no spaces!

## Common Login Issues

### Issue 1: "Invalid username or password"

**Causes:**
- Typing error (check caps lock, extra spaces)
- Database not imported correctly
- Password hash corruption

**Solutions:**

#### Option A: Reset Password via SQL
Run this in phpMyAdmin SQL tab:
```sql
USE portfolio_cms;
UPDATE users SET password = '$2y$10$7Rq5H4zT4gzI1xGdOlBqxeF5TIUlZWQXOCGKQOKYmNPVQJvLJ9Yv6' WHERE username = 'admin';
```

#### Option B: Verify User Exists
Check if the admin user was created:
```sql
SELECT * FROM users WHERE username = 'admin';
```

If no results, the INSERT didn't work. Run:
```sql
INSERT INTO users (username, email, password, created_at, updated_at) 
VALUES ('admin', 'admin@example.com', '$2y$10$7Rq5H4zT4gzI1xGdOlBqxeF5TIUlZWQXOCGKQOKYmNPVQJvLJ9Yv6', NOW(), NOW());
```

#### Option C: Use reset-password.php
1. Upload `reset-password.php` to your website root
2. Visit: `http://yourdomain.com/reset-password.php`
3. Uncomment the database update code
4. Refresh the page
5. Try logging in
6. **DELETE reset-password.php immediately!**

### Issue 2: Login page keeps refreshing/redirecting

**Cause:** Session issues

**Solutions:**
1. Clear browser cookies and cache
2. Try a different browser or incognito mode
3. Check session configuration in `includes/config.php`
4. Verify PHP sessions are working:
```php
<?php
session_start();
$_SESSION['test'] = 'working';
echo "Sessions are: " . ($_SESSION['test'] === 'working' ? 'WORKING' : 'NOT WORKING');
?>
```

### Issue 3: Blank page after login attempt

**Cause:** PHP error

**Solution:**
1. Enable error reporting in `includes/config.php`:
```php
ini_set('display_errors', 1);
error_reporting(E_ALL);
```
2. Check PHP error logs
3. Verify database connection in `includes/config.php`

### Issue 4: Database connection error

**Cause:** Wrong credentials in config

**Solution:**
Edit `includes/config.php` and verify:
```php
define('DB_HOST', 'localhost');     // Usually localhost
define('DB_USER', 'your_username'); // Your MySQL username
define('DB_PASS', 'your_password'); // Your MySQL password  
define('DB_NAME', 'portfolio_cms'); // Database name
```

Test connection:
```php
<?php
$conn = new mysqli('localhost', 'your_username', 'your_password', 'portfolio_cms');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
echo "Connected successfully";
?>
```

## Manual Password Reset Methods

### Method 1: SQL Query (Fastest)
```sql
UPDATE users 
SET password = '$2y$10$7Rq5H4zT4gzI1xGdOlBqxeF5TIUlZWQXOCGKQOKYmNPVQJvLJ9Yv6' 
WHERE username = 'admin';
```

### Method 2: Import reset-admin-password.sql
Use phpMyAdmin to import the `reset-admin-password.sql` file

### Method 3: PHP Script
Create `test-password.php`:
```php
<?php
$password = 'admin123';
$hash = '$2y$10$7Rq5H4zT4gzI1xGdOlBqxeF5TIUlZWQXOCGKQOKYmNPVQJvLJ9Yv6';

if (password_verify($password, $hash)) {
    echo "✓ Password verification WORKS<br>";
    echo "Username: admin<br>";
    echo "Password: {$password}<br>";
} else {
    echo "✗ Password verification FAILED<br>";
}

// Test database connection and check user
require_once 'includes/config.php';
require_once 'includes/database.php';

$db = new Database();
$user = $db->fetchOne("SELECT * FROM users WHERE username = 'admin'");

if ($user) {
    echo "<br>✓ Admin user found in database<br>";
    echo "Email: {$user['email']}<br>";
    echo "Password hash in DB: " . substr($user['password'], 0, 30) . "...<br>";
    
    if (password_verify($password, $user['password'])) {
        echo "<br>✓✓✓ LOGIN SHOULD WORK! ✓✓✓<br>";
    } else {
        echo "<br>✗ Password hash doesn't match - needs reset!<br>";
    }
} else {
    echo "<br>✗ Admin user NOT found in database!<br>";
}
?>
```

## Verify Installation Checklist

- [ ] Database created successfully
- [ ] All 6 tables exist (users, projects, project_images, about, messages, settings)
- [ ] Admin user exists in users table
- [ ] config.php has correct database credentials
- [ ] uploads/ directory exists and is writable
- [ ] Can access http://yourdomain.com/admin/login.php
- [ ] No PHP errors showing

## Still Can't Login?

1. **Check PHP version:** Must be 7.4 or higher
   ```bash
   php -v
   ```

2. **Check MySQL/MariaDB version:** Must be 5.5 or higher
   ```bash
   mysql --version
   ```

3. **Verify PDO is enabled:**
   ```php
   <?php
   if (extension_loaded('pdo') && extension_loaded('pdo_mysql')) {
       echo "PDO is enabled";
   } else {
       echo "PDO is NOT enabled - install php-pdo package";
   }
   ?>
   ```

4. **Check file permissions:**
   ```bash
   ls -la includes/
   # All files should be readable (644 or 755)
   ```

## Need to Start Fresh?

Complete reset:
```sql
DROP DATABASE IF EXISTS portfolio_cms;
```

Then re-import schema.sql

## Getting Help

If you're still stuck:
1. Check PHP error logs
2. Check MySQL error logs
3. Enable debugging in config.php
4. Test each component individually (database, sessions, PHP version)

## Emergency Access

If completely locked out, create a new admin user manually:
```sql
INSERT INTO users (username, email, password, created_at, updated_at) 
VALUES (
    'newadmin',
    'newadmin@example.com',
    '$2y$10$7Rq5H4zT4gzI1xGdOlBqxeF5TIUlZWQXOCGKQOKYmNPVQJvLJ9Yv6',
    NOW(),
    NOW()
);
```

Login with:
- Username: `newadmin`
- Password: `admin123`
