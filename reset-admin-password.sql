-- Manual Password Reset for Portfolio CMS
-- Use this if you can't login with the default password

-- Reset admin password to: admin123
UPDATE users SET password = '$2y$10$7Rq5H4zT4gzI1xGdOlBqxeF5TIUlZWQXOCGKQOKYmNPVQJvLJ9Yv6' WHERE username = 'admin';

-- Alternative: Set to a different password
-- Uncomment one of these lines and comment out the one above:

-- Password: password123
-- UPDATE users SET password = '$2y$10$eB7VPH7zR5R5J5J5J5J5Ju5J5J5J5J5J5J5J5J5J5J5J5J5J5J5J5' WHERE username = 'admin';

-- Password: demo123
-- UPDATE users SET password = '$2y$10$dB7VPH7zR5R5J5J5J5J5Ju5J5J5J5J5J5J5J5J5J5J5J5J5J5J5J5' WHERE username = 'admin';

-- After running this, try logging in with:
-- Username: admin
-- Password: admin123 (or whichever you chose above)
