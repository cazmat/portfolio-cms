-- Migration: Add Family Role
-- Run this if you already have the portfolio CMS installed and want to add the Family role

-- Add 'family' to the role enum
ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'client', 'family') DEFAULT 'client';

-- Optional: Convert existing users to family role
-- Uncomment the line below to convert specific users by username
-- UPDATE users SET role = 'family' WHERE username IN ('username1', 'username2');

-- Or convert by email
-- UPDATE users SET role = 'family' WHERE email IN ('email1@example.com', 'email2@example.com');

SELECT 'Migration completed successfully!' as message;
