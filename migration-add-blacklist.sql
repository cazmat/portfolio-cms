-- Migration: Add Email Blacklist
-- Run this to add blacklist functionality

-- Create blacklist table
CREATE TABLE IF NOT EXISTS email_blacklist (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(191) NOT NULL UNIQUE,
    reason TEXT,
    added_by INT,
    created_at DATETIME NULL,
    FOREIGN KEY (added_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

SELECT 'Blacklist feature added successfully!' as message;
