# Remember Me Feature

The Portfolio CMS includes a secure "Remember Me" feature that keeps users logged in for extended periods.

## Overview

When users check "Remember me for 30 days" at login, they won't need to log in again for 30 days, even if they close their browser or restart their computer.

## How It Works

### User Experience
1. User enters username and password
2. User checks "Remember me for 30 days" checkbox
3. User clicks Login
4. User is logged in normally
5. **Next visit:** User automatically logged in (no credentials needed)
6. Works for 30 days or until manual logout

### Technical Implementation

**Token Generation:**
- Secure 64-character random token generated
- Token stored in browser cookie (unhashed)
- Token SHA-256 hash stored in database
- Expiry timestamp set to 30 days from creation

**Automatic Login:**
- On page load, system checks for remember token cookie
- If found, looks up hashed token in database
- Validates expiry hasn't passed
- Verifies user account is still active
- Logs user in automatically if valid

**Security:**
- Tokens are long and random (extremely hard to guess)
- Database stores hash, not actual token
- Cookie has HttpOnly flag (JavaScript can't access)
- Cookie has Secure flag (HTTPS only)
- Expired tokens automatically cleaned up
- Tokens deleted on logout

## Database Table

```sql
CREATE TABLE remember_tokens (
    id INT PRIMARY KEY,
    user_id INT NOT NULL,
    token VARCHAR(64) NOT NULL,      -- SHA-256 hash
    expiry INT NOT NULL,              -- Unix timestamp
    created_at DATETIME,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

**Features:**
- Foreign key cascade delete (tokens deleted when user deleted)
- Indexed token field for fast lookups
- Indexed expiry for cleanup queries

## Configuration

### Change Duration

Edit `includes/functions.php`:

```php
function setRememberMe($user_id, $db) {
    $token = bin2hex(random_bytes(32));
    
    // Current: 30 days
    $expiry = time() + (30 * 24 * 60 * 60);
    
    // 7 days:
    // $expiry = time() + (7 * 24 * 60 * 60);
    
    // 90 days:
    // $expiry = time() + (90 * 24 * 60 * 60);
    
    // 1 year:
    // $expiry = time() + (365 * 24 * 60 * 60);
    
    // ...
}
```

Don't forget to update the checkbox label in `login.php`:

```php
<label class="form-check-label" for="remember_me">
    Remember me for 7 days  <!-- Update this -->
</label>
```

### Disable Feature

To completely remove Remember Me, comment out the checkbox in `login.php`:

```php
<!-- Remember Me Checkbox - Disabled
<div class="mb-3 form-check">
    <input type="checkbox" class="form-check-input" id="remember_me" name="remember_me">
    <label class="form-check-label" for="remember_me">
        Remember me for 30 days
    </label>
</div>
-->
```

The backend will still work but users won't be able to enable it.

## Security Considerations

### Why It's Secure

**Token Generation:**
- Uses `random_bytes()` - cryptographically secure
- 64 hex characters = 256 bits of entropy
- Virtually impossible to guess or brute force

**Token Storage:**
- Database stores SHA-256 hash only
- Even with database access, can't reconstruct token
- Cookie stores actual token (needed for validation)

**Cookie Security:**
- `HttpOnly`: JavaScript cannot read cookie
- `Secure`: Only sent over HTTPS
- `SameSite`: Prevents CSRF attacks

**Token Lifecycle:**
- Tokens expire automatically
- Deleted on logout
- Database cleanup for expired tokens

### Potential Risks

**Shared Computers:**
- Anyone using the same browser can access account
- **Solution**: Don't use Remember Me on public/shared computers

**Stolen Cookies:**
- If attacker gets cookie, they can access account
- **Mitigation**: HTTPS required, HttpOnly flag set
- **Additional**: Could add IP validation (see below)

**Lost/Stolen Device:**
- Device with Remember Me cookie can access account
- **Solution**: User can logout remotely (feature could be added)
- **Current**: Tokens expire after 30 days automatically

## Best Practices

### For Users

**DO use Remember Me when:**
- On your personal computer
- On your personal phone/tablet
- In a secure environment
- You're the only user of the device

**DON'T use Remember Me when:**
- On public computers (library, internet cafe)
- On shared computers (work, family)
- On someone else's device
- In untrusted locations

### For Administrators

**Recommended durations by role:**
- **Admin**: 7-14 days (higher security)
- **Client**: 30 days (convenient for project reviews)
- **Family**: 30-90 days (casual use)

**For high-security environments:**
- Disable Remember Me entirely
- Require strong passwords
- Consider two-factor authentication
- Use shorter session timeouts

## Maintenance

### Cleanup Old Tokens

While expired tokens are ignored, you can manually clean them up:

```sql
-- Delete expired tokens
DELETE FROM remember_tokens WHERE expiry < UNIX_TIMESTAMP();

-- Delete tokens older than 60 days
DELETE FROM remember_tokens WHERE created_at < DATE_SUB(NOW(), INTERVAL 60 DAY);
```

Add to a cron job for automatic cleanup:

```bash
# Run daily at 3 AM
0 3 * * * mysql -u username -p password_here database_name -e "DELETE FROM remember_tokens WHERE expiry < UNIX_TIMESTAMP();"
```

### View Active Tokens

```sql
-- See all active remember me tokens
SELECT 
    rt.id,
    u.username,
    u.role,
    FROM_UNIXTIME(rt.expiry) as expires_at,
    rt.created_at
FROM remember_tokens rt
INNER JOIN users u ON rt.user_id = u.id
WHERE rt.expiry > UNIX_TIMESTAMP()
ORDER BY rt.created_at DESC;

-- Count tokens per user
SELECT 
    u.username,
    COUNT(*) as active_tokens
FROM remember_tokens rt
INNER JOIN users u ON rt.user_id = u.id
WHERE rt.expiry > UNIX_TIMESTAMP()
GROUP BY u.username;
```

## Advanced Security (Optional Enhancements)

### Add IP Validation

Store and validate IP address with token:

```php
// In setRememberMe()
$ip = $_SERVER['REMOTE_ADDR'];
// Store $ip in remember_tokens table

// In checkRememberMe()
if ($result['ip_address'] !== $_SERVER['REMOTE_ADDR']) {
    // IP changed - possible token theft
    clearRememberMe(...);
    return false;
}
```

**Note**: May cause issues with dynamic IPs or mobile users.

### Add User Agent Validation

Similar to IP validation but checks browser:

```php
$user_agent = $_SERVER['HTTP_USER_AGENT'];
```

### Add "Active Sessions" Page

Let users see and revoke remember tokens:

```php
// In user profile page
$tokens = $db->fetchAll(
    "SELECT id, created_at, expiry FROM remember_tokens 
     WHERE user_id = ? AND expiry > UNIX_TIMESTAMP()",
    [$_SESSION['user_id']]
);

// Show list with "Revoke" buttons
```

### Add "Logout All Devices"

```php
// Delete all tokens for user
$db->query("DELETE FROM remember_tokens WHERE user_id = ?", [$user_id]);
```

## Troubleshooting

### Remember Me Not Working

**Check cookie settings:**
```php
// Make sure cookies are enabled
var_dump($_COOKIE);
```

**Check database:**
```sql
SELECT * FROM remember_tokens WHERE user_id = 1;
```

**Check token expiry:**
```sql
SELECT *, FROM_UNIXTIME(expiry) as expires_at 
FROM remember_tokens 
WHERE user_id = 1;
```

### Users Stay Logged In Too Long

**Reduce expiry duration:**
- Edit `setRememberMe()` function
- Change days from 30 to lower number

**Force logout all tokens:**
```sql
TRUNCATE TABLE remember_tokens;
```

### Cookies Not Being Set

**Check HTTPS:**
- Secure flag requires HTTPS
- Test on HTTP by temporarily removing secure flag
- **Production**: Always use HTTPS

**Check domain:**
- Cookie domain must match site domain
- Check `setcookie()` domain parameter

**Check PHP settings:**
```php
phpinfo(); // Check session.cookie_* settings
```

## Migration from Existing Installation

If you have an existing Portfolio CMS installation:

1. Run the migration SQL:
```bash
mysql -u username -p database_name < migration-add-remember-me.sql
```

2. Update files:
   - `includes/config.php` - Already updated
   - `includes/functions.php` - Already updated
   - `login.php` - Already updated

3. Test the feature:
   - Login with Remember Me checked
   - Close browser
   - Reopen and visit site
   - Should be automatically logged in

## Summary

**Benefits:**
- ✅ Convenient for users
- ✅ Secure implementation
- ✅ Works across devices
- ✅ Easy to configure
- ✅ Automatic cleanup

**Security:**
- ✅ Cryptographically secure tokens
- ✅ Hashed in database
- ✅ HttpOnly cookies
- ✅ Automatic expiration
- ✅ Cleared on logout

**Recommendations:**
- Use on personal devices only
- Consider shorter durations for admin accounts
- Educate users about security
- Monitor active tokens periodically
