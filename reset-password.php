<?php
/**
 * Password Generator & Reset Tool
 * 
 * Use this script to:
 * 1. Generate a new password hash
 * 2. Reset the admin password if you're locked out
 * 
 * SECURITY: Delete this file after use!
 */

require_once 'includes/config.php';
require_once 'includes/database.php';

// Password to hash (change this to your desired password)
$newPassword = 'admin123';

// Generate hash
$hash = password_hash($newPassword, PASSWORD_BCRYPT);

echo "=== Password Hash Generator ===\n\n";
echo "Password: {$newPassword}\n";
echo "Hash: {$hash}\n\n";

// Uncomment the lines below to actually update the database
// WARNING: This will change the admin password!

/*
$db = new Database();
$sql = "UPDATE users SET password = ? WHERE username = 'admin'";
if ($db->query($sql, [$hash])) {
    echo "✓ Admin password has been reset to: {$newPassword}\n";
    echo "✓ You can now login with username 'admin' and the password above\n";
} else {
    echo "✗ Failed to update password\n";
}
*/

echo "\n=== Instructions ===\n";
echo "1. Copy the hash above\n";
echo "2. Run this SQL query in phpMyAdmin:\n\n";
echo "UPDATE users SET password = '{$hash}' WHERE username = 'admin';\n\n";
echo "3. Or uncomment the code in this script to update automatically\n";
echo "4. DELETE THIS FILE after resetting your password!\n";
?>
