# Spam Detection System

The Portfolio CMS includes an automatic spam detection system for the contact form to help identify repeat senders and suspicious emails.

## How It Works

When someone submits a contact form, the system automatically:

1. **Checks if email has been used before**
   - Searches database for previous messages from same email
   - If found: Message marked as **SPAM** 🚨
   - If not found: Continues to next check

2. **Checks for suspicious patterns**
   - Scans email for common spam indicators
   - Temporary email services (10minutemail, guerrillamail, etc.)
   - Keywords: temp, disposable, throwaway, fake, spam
   - If found: Message marked as **SUSPICIOUS** ⚠️

3. **Clean messages**
   - No previous history + no suspicious patterns
   - Marked as **CLEAN** ✓

**Important**: All messages are saved regardless of spam status. Nothing is blocked or deleted automatically.

## Spam Status Indicators

### 🚨 SPAM (Red Badge)
- Email address has submitted messages before
- Likely a repeat sender
- Could be spam or legitimate follow-up

**What to do:**
- Review message carefully
- Check previous messages from this email
- Determine if legitimate follow-up or spam
- Delete if confirmed spam

### ⚠️ SUSPICIOUS (Yellow Badge)
- Email contains suspicious patterns
- Temporary/disposable email service detected
- Common spam keywords found

**What to do:**
- Review message content
- Be cautious - may be spam
- Could be legitimate user with disposable email
- Use judgment based on message content

### ✓ CLEAN (Green Badge)
- First time sender
- No suspicious patterns detected
- Appears legitimate

**What to do:**
- Normal message processing
- Respond as needed

## Viewing Spam Status

### Messages List
- **Spam column** shows status for each message
- Color-coded badges for quick identification
- Filter buttons to view by spam status

### Message Detail Page
- Full spam status explanation
- Details about why message was flagged
- Previous message history (if applicable)

## Filtering Messages

Use the filter buttons on the messages page:

**Status Filters:**
- All - Show all messages
- Unread - New messages
- Read - Viewed messages
- Archived - Archived messages

**Spam Filters:**
- ⚠️ Spam - Only messages from repeat senders
- ⚠ Suspicious - Only suspicious emails
- ✓ Clean - Only clean messages

**Tip**: Start your day by reviewing "Unread + Clean" messages first.

## Managing Spam

### Manual Spam Override

You can manually override the automatic spam detection:

**Mark as Not Spam:**
1. View message or message list
2. Click "✓ Not Spam" button
3. Message spam status changed to "clean"

**Mark as Spam:**
1. View clean message
2. Click "⚠️ Mark Spam" button
3. Message spam status changed to "spam"

**Use cases:**
- False positives (legitimate message marked as spam)
- False negatives (spam not caught by system)
- Manual review and classification

### Whitelist Feature

**What is the whitelist?**
- List of trusted email addresses
- Whitelisted emails are **always** marked as clean
- Overrides repeat sender detection
- Perfect for regular clients

**How to add to whitelist:**

**Method 1: From message**
1. View spam/suspicious message
2. Click "🛡️ Whitelist" button
3. Email added to whitelist
4. Message marked as clean

**Method 2: Whitelist page**
1. Go to Admin → Whitelist
2. Enter email address
3. Add optional name and notes
4. Click "Add to Whitelist"

**Whitelist Management:**
- View all whitelisted emails
- See message count per email
- Add notes (why trusted)
- Remove from whitelist
- Track who added each email

### Review Spam Messages
```
1. Click "⚠️ Spam" filter button
2. Review messages marked as spam
3. Options for each message:
   - ✓ Not Spam - Clear spam flag
   - 🛡️ Whitelist - Trust this sender forever
   - Delete - Remove spam message
4. Respond to legitimate messages
```

### Delete Spam
- Individual: Click "Delete" button on message
- Manual override before deleting if unsure

### Whitelist Best Practices

**DO whitelist:**
- Regular clients who contact often
- Business partners
- Verified contacts
- Suppliers/vendors

**DON'T whitelist:**
- Unknown senders (yet)
- One-time inquiries
- Suspicious emails
- Unverified contacts

**Tip**: Review a sender's message history before whitelisting

## Why Repeat Senders?

The system marks repeat senders as spam because:

1. **Most legitimate users send once**
   - Initial inquiry
   - Wait for response
   - Don't submit multiple times

2. **Spammers send repeatedly**
   - Testing contact forms
   - Mass submissions
   - Automated bots

3. **Easy to verify**
   - Check previous messages
   - Look at content
   - Determine if legitimate

**Exception**: Legitimate clients may send multiple messages. Always review before deleting.

## Customizing Detection

### Adjust Sensitivity

Edit `includes/functions.php`:

```php
function checkSpamEmail($email, $db) {
    // Check for repeat senders
    $count = $db->fetchOne(
        "SELECT COUNT(*) as count FROM messages WHERE email = ?",
        [$email]
    );
    
    if ($count && $count['count'] > 0) {
        return 'spam';
    }
    
    // Your custom patterns here
    $spamPatterns = [
        'temp',
        'disposable',
        // Add more patterns
    ];
    
    // ...
}
```

### Add Custom Patterns

Add to `$spamPatterns` array:

```php
$spamPatterns = [
    'temp',
    'disposable',
    'throwaway',
    'fake',
    'spam',
    'test',           // Add: test emails
    'example',        // Add: example@example.com
    'noreply',        // Add: no-reply addresses
    'yourcompetitor', // Add: competitor domains
];
```

### Change Behavior

**Option 1**: Allow repeat senders
```php
// Comment out repeat sender check
/*
if ($count && $count['count'] > 0) {
    return 'spam';
}
*/
```

**Option 2**: Mark repeat senders as suspicious instead
```php
if ($count && $count['count'] > 0) {
    return 'suspicious'; // Changed from 'spam'
}
```

**Option 3**: Only flag after X messages
```php
if ($count && $count['count'] >= 3) { // Changed from > 0
    return 'spam';
}
```

## Advanced: IP-Based Detection

Future enhancement to track by IP address:

```sql
-- Add IP column to messages table
ALTER TABLE messages ADD COLUMN ip_address VARCHAR(45);
```

```php
// Store IP on submission
$ip = $_SERVER['REMOTE_ADDR'];

// Check for spam by IP
$ipCount = $db->fetchOne(
    "SELECT COUNT(*) as count FROM messages WHERE ip_address = ?",
    [$ip]
);
```

## Statistics

View spam statistics:

```sql
-- Count by spam status
SELECT 
    spam_status,
    COUNT(*) as count
FROM messages
GROUP BY spam_status;

-- Most frequent spam emails
SELECT 
    email,
    COUNT(*) as submission_count
FROM messages
WHERE spam_status = 'spam'
GROUP BY email
ORDER BY submission_count DESC
LIMIT 10;

-- Spam rate over time
SELECT 
    DATE(created_at) as date,
    COUNT(*) as total_messages,
    SUM(CASE WHEN spam_status = 'spam' THEN 1 ELSE 0 END) as spam_count,
    ROUND(SUM(CASE WHEN spam_status = 'spam' THEN 1 ELSE 0 END) / COUNT(*) * 100, 2) as spam_percentage
FROM messages
GROUP BY DATE(created_at)
ORDER BY date DESC
LIMIT 30;
```

## Best Practices

### Daily Review
1. Check "Unread + Clean" messages first
2. Respond to legitimate inquiries
3. Review suspicious messages
4. Delete confirmed spam

### Weekly Maintenance
1. Review all spam messages
2. Delete confirmed spam
3. Verify legitimate messages weren't flagged
4. Check for patterns in spam

### Monthly Analysis
1. Run spam statistics
2. Adjust detection patterns if needed
3. Review false positives
4. Update whitelist/blacklist

## Troubleshooting

### Legitimate Messages Marked as Spam

**Problem**: Client's follow-up message flagged as spam

**Solution**: This is expected behavior - review the message and determine if it's a legitimate follow-up. The system can't differentiate between spam repeats and legitimate follow-ups.

**Future**: Add whitelist feature for known clients

### Too Many False Positives

**Problem**: Many clean messages flagged as suspicious

**Solution**: 
- Remove overly broad patterns from `$spamPatterns`
- Adjust sensitivity
- Add exceptions for common legitimate services

### Missing Spam

**Problem**: Obvious spam not being caught

**Solution**:
- Add more patterns to detection
- Implement IP-based tracking
- Add additional checks (message content analysis)

## Privacy Considerations

**Email Storage**:
- System stores emails for spam checking
- Complies with contact form data retention
- No external services used
- All processing happens locally

**GDPR Compliance**:
- Mention spam detection in privacy policy
- Include in data retention policy
- Allow users to request deletion
- Explain why emails are stored

## Future Enhancements

Potential additions to spam detection:

1. **Blacklist**
   - Block specific emails/domains permanently
   - Automatic deletion of blacklisted emails
   - Import/export blacklist

2. **Content Analysis**
   - Check message text for spam patterns
   - URL detection
   - Length analysis
   - Language detection

3. **Rate Limiting**
   - Limit submissions per IP
   - Time-based restrictions
   - CAPTCHA for suspected spam

4. **Machine Learning**
   - Learn from your spam decisions
   - Adaptive detection
   - Improve over time

5. **External Services**
   - Akismet integration
   - StopForumSpam API
   - Email validation services

## Summary

**What it does:**
- ✅ Automatically detects repeat senders
- ✅ Flags suspicious email patterns
- ✅ Labels messages with spam status
- ✅ Provides filtering and sorting
- ✅ Manual spam override (mark spam/not spam)
- ✅ Whitelist feature for trusted senders
- ✅ All local processing (no external services)

**What it doesn't do:**
- ❌ Block or delete messages automatically
- ❌ Prevent form submission
- ❌ Send responses to submitters
- ❌ Analyze message content

**Best for:**
- Identifying repeat submissions
- Spotting obvious spam patterns
- Organizing inbox
- Quick spam identification

**Not designed for:**
- Complete spam blocking
- Content analysis
- Advanced bot detection
- Behavior analysis
