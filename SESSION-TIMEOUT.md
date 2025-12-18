# Session Timeout Configuration

The Portfolio CMS includes automatic session timeout for security. Users are automatically logged out after a period of inactivity.

## Current Settings

**Default Timeout: 8 hours (28,800 seconds)**

This means users will remain logged in for 8 hours of activity, or be logged out after 8 hours of inactivity.

## Changing the Timeout Duration

To change the session timeout, edit the `includes/config.php` file:

```php
// Session Configuration
// Set session lifetime to 8 hours (28800 seconds)
ini_set('session.gc_maxlifetime', 28800);
ini_set('session.cookie_lifetime', 28800);
session_set_cookie_params(28800);

session_start();

// Session timeout constant (8 hours in seconds)
define('SESSION_TIMEOUT', 28800);
```

### Common Timeout Values

Replace `28800` with your desired value in seconds:

| Duration | Seconds | Use Case |
|----------|---------|----------|
| 30 minutes | 1800 | High security |
| 1 hour | 3600 | Default for most sites |
| 2 hours | 7200 | Comfortable for regular use |
| 4 hours | 14400 | Extended work sessions |
| 8 hours | 28800 | Full work day (default) |
| 12 hours | 43200 | Very long sessions |
| 24 hours | 86400 | Stay logged in all day |

### Example: Change to 2 hours

```php
// Set session lifetime to 2 hours (7200 seconds)
ini_set('session.gc_maxlifetime', 7200);
ini_set('session.cookie_lifetime', 7200);
session_set_cookie_params(7200);

session_start();

// Session timeout constant (2 hours in seconds)
define('SESSION_TIMEOUT', 7200);
```

## How It Works

1. **Last Activity Tracking**: Every page load updates the user's last activity timestamp
2. **Automatic Check**: On each page load, the system checks if the timeout period has elapsed
3. **Graceful Logout**: If timeout occurs, user is logged out and redirected to login with a message
4. **Activity Reset**: Any action on the site resets the inactivity timer

## User Experience

- Users see a warning message: "Your session has expired due to inactivity. Please login again."
- No data is lost during timeout (already saved work remains saved)
- Users simply need to log back in to continue

## Server Configuration

**Important**: Some hosting providers have their own PHP session settings that may override these values. If the timeout isn't working as expected:

### Option 1: .htaccess Configuration

Add to your `.htaccess` file:

```apache
# Session timeout (8 hours in seconds)
php_value session.gc_maxlifetime 28800
php_value session.cookie_lifetime 28800
```

### Option 2: php.ini Configuration

If you have access to `php.ini`:

```ini
session.gc_maxlifetime = 28800
session.cookie_lifetime = 28800
```

### Option 3: Contact Your Host

If neither of the above works, contact your hosting provider to increase the PHP session timeout settings.

## Security Considerations

### Shorter Timeouts (30 min - 2 hours):
**Pros:**
- More secure
- Better for shared computers
- Reduces risk of unauthorized access

**Cons:**
- Users must log in more frequently
- Can be disruptive during active work

### Longer Timeouts (8+ hours):
**Pros:**
- Better user experience
- Fewer interruptions
- Good for trusted devices

**Cons:**
- Higher security risk on shared computers
- Sessions persist longer if user forgets to logout

## Recommendations

- **Public/Shared Computers**: 30 minutes to 1 hour
- **Personal Devices**: 4 to 8 hours
- **Secure Office Environment**: 8 to 12 hours
- **Admin Accounts**: Consider shorter timeouts (2-4 hours)
- **Family Accounts**: Can use longer timeouts (8+ hours)

## Testing Your Changes

1. Edit `includes/config.php` with your desired timeout
2. Set timeout to a short value for testing (e.g., 60 seconds)
3. Login to the site
4. Wait for the timeout period
5. Try to access any page
6. You should be redirected to login with a timeout message
7. Once confirmed working, set your actual desired timeout

## Troubleshooting

### Sessions expire too quickly
- Check your hosting provider's PHP settings
- Add .htaccess rules
- Verify file permissions on session save path

### Sessions never expire
- Clear browser cookies
- Check if PHP session garbage collection is running
- Verify the timeout check is not commented out

### Users can't stay logged in
- Increase timeout value
- Check browser cookie settings
- Ensure cookies are enabled

## Additional Security

### Remember Me Feature

The Portfolio CMS includes an optional "Remember Me" feature that allows users to stay logged in for 30 days.

**How it works:**
- User checks "Remember me for 30 days" at login
- A secure token is stored in database and browser cookie
- Token is valid for 30 days
- Automatically logs user back in when they return
- Each user can have multiple active tokens (different devices)

**Security measures:**
- Tokens are securely hashed in database (SHA-256)
- Only unhashed token stored in cookie (can't be reconstructed from database)
- Tokens expire after 30 days
- Tokens deleted on manual logout
- Secure and HttpOnly flags on cookies
- Invalid tokens automatically removed

**To disable Remember Me:**

Comment out the checkbox in `login.php`:

```php
<!-- <div class="mb-3 form-check">
    <input type="checkbox" class="form-check-input" id="remember_me" name="remember_me">
    <label class="form-check-label" for="remember_me">
        Remember me for 30 days
    </label>
</div> -->
```

**To change Remember Me duration:**

Edit the expiry in `includes/functions.php`:

```php
function setRememberMe($user_id, $db) {
    $token = bin2hex(random_bytes(32));
    $expiry = time() + (30 * 24 * 60 * 60); // Change 30 to desired days
    // ...
}
```

For additional security beyond Remember Me, you can implement:

1. **IP Address Validation**: Check if user's IP changes
2. **User Agent Validation**: Detect if browser changes
3. **Remember Me**: Optional checkbox for extended sessions
4. **Two-Factor Authentication**: Add extra security layer

These features can be added in future updates if needed.
