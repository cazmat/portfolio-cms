-- Migration: Add Whitelist and Manual Spam Override
-- Run this if you already have the spam detection system

-- Create email whitelist table
CREATE TABLE IF NOT EXISTS email_whitelist (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(191) NOT NULL UNIQUE,
    name VARCHAR(100),
    notes TEXT,
    added_by INT,
    created_at DATETIME NULL,
    FOREIGN KEY (added_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

SELECT 'Whitelist and spam override features added successfully!' as message;
