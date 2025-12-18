-- Migration: Add Spam Detection to Contact Form
-- Run this if you already have the portfolio CMS installed

-- Add spam_status column to messages table
ALTER TABLE messages 
ADD COLUMN spam_status ENUM('clean', 'spam', 'suspicious') DEFAULT 'clean' AFTER status,
ADD INDEX idx_spam_status (spam_status);

-- Set all existing messages to 'clean' status
UPDATE messages SET spam_status = 'clean' WHERE spam_status IS NULL;

SELECT 'Spam detection feature added successfully!' as message;
