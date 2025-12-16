# Database Installation Guide

## Important: MySQL Key Length Issue

If you encounter the error: **"Specified key was too long; max key length is 767 bytes"**

This happens because utf8mb4 uses 4 bytes per character, and older MySQL versions have index key limits.

## Solution: Choose the Right Schema File

### Option 1: schema.sql (Recommended for MySQL 5.7+)
- Uses utf8mb4 for full Unicode support (emojis, etc.)
- Reduced VARCHAR lengths on indexed columns (191 instead of 200)
- Works on MySQL 5.7+, MySQL 8.0+, MariaDB 10.2+

**Use this if:**
- You have MySQL 5.7 or higher
- You want full Unicode support including emojis

### Option 2: schema-alternative.sql (For MySQL 5.5/5.6)
- Uses utf8 character set (3 bytes per character)
- Supports all VARCHAR lengths without issues
- Works on MySQL 5.5+

**Use this if:**
- You have MySQL 5.5 or 5.6
- You get the "key too long" error with schema.sql
- You don't need emoji support

## Installation Steps

### 1. Create the Database

**Using phpMyAdmin:**
1. Open phpMyAdmin
2. Click "SQL" tab
3. Copy and paste the **entire contents** of your chosen schema file
4. Click "Go"

**Using MySQL Command Line:**
```bash
# For schema.sql (MySQL 5.7+)
mysql -u your_username -p < schema.sql

# OR for schema-alternative.sql (MySQL 5.5+)
mysql -u your_username -p < schema-alternative.sql
```

### 2. Verify Installation

Run this query to check if all tables were created:

```sql
USE portfolio_cms;
SHOW TABLES;
```

You should see 6 tables:
- users
- projects
- project_images
- about
- messages
- settings

### 3. Test Login

Default admin credentials:
- Username: `admin`
- Password: `admin123`

## Troubleshooting

### Error: "Specified key was too long"
**Solution:** Use `schema-alternative.sql` instead of `schema.sql`

### Error: "Invalid default value for 'created_at'"
**Solution:** This has been fixed - all timestamp columns use NULL defaults and are set manually in PHP

### Error: "Can't DROP DATABASE 'portfolio_cms'; database doesn't exist"
**Solution:** Ignore this - it's just a warning. The database will be created.

### Error: "Duplicate entry 'admin'"
**Solution:** The database already has data. Either:
- Use a fresh database, or
- Comment out the INSERT statements at the end of the schema file

### Can't Login / Wrong Password
**Solution:** 

**Method 1: Reset via SQL (Easiest)**
1. Open phpMyAdmin or your MySQL client
2. Select the `portfolio_cms` database
3. Run this query:
```sql
UPDATE users SET password = '$2y$10$7Rq5H4zT4gzI1xGdOlBqxeF5TIUlZWQXOCGKQOKYmNPVQJvLJ9Yv6' WHERE username = 'admin';
```
4. Login with username: `admin` and password: `admin123`

**Method 2: Use the reset-admin-password.sql file**
1. Import the `reset-admin-password.sql` file
2. This will reset the password to `admin123`

**Method 3: Use the reset-password.php script**
1. Upload `reset-password.php` to your server root
2. Visit it in your browser: `http://yourdomain.com/reset-password.php`
3. Follow the instructions to generate a new password hash
4. **DELETE the file after use!**

**Method 4: Reimport the database**
1. Drop the database: `DROP DATABASE portfolio_cms;`
2. Re-import schema.sql
3. Default credentials will be restored

**Important:** The default password is `admin123` (lowercase, no spaces)

## Schema Differences Explained

### schema.sql (utf8mb4)
```sql
CREATE TABLE projects (
    slug VARCHAR(191) UNIQUE NOT NULL,  -- Reduced from 200 to 191
    ...
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### schema-alternative.sql (utf8)
```sql
CREATE TABLE projects (
    slug VARCHAR(200) UNIQUE NOT NULL,  -- Can use full 200
    ...
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
```

**Performance Impact:** None. Both work identically for English and most languages.

**Unicode Support:**
- utf8mb4: Supports ALL Unicode including emojis 😀
- utf8: Supports most Unicode but not 4-byte characters like emojis

## Recommendation

For most users, **schema-alternative.sql** is the safest choice as it works on all MySQL versions without configuration changes.

Use **schema.sql** only if:
1. You have MySQL 5.7+ or MariaDB 10.2+
2. You specifically need full emoji support
3. You're willing to troubleshoot if needed

## After Installation

1. Configure `includes/config.php` with your database credentials
2. Set uploads directory permissions: `chmod 755 uploads/`
3. Login and **CHANGE THE DEFAULT PASSWORD IMMEDIATELY**
4. Start adding your portfolio projects!
