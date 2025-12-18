-- Migration: Add Work Schedule Module
-- Run this if you already have the portfolio CMS installed and want to add the schedule feature

-- Create schedule table
CREATE TABLE IF NOT EXISTS schedule (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    event_date DATE NOT NULL,
    start_time TIME NULL,
    end_time TIME NULL,
    location VARCHAR(200),
    event_type ENUM('meeting', 'deadline', 'shoot', 'event', 'work', 'reminder', 'other') DEFAULT 'event',
    status ENUM('scheduled', 'completed', 'cancelled') DEFAULT 'scheduled',
    is_private TINYINT(1) DEFAULT 0,
    color VARCHAR(7) DEFAULT '#3788d8',
    notes TEXT,
    created_by INT,
    created_at DATETIME NULL,
    updated_at DATETIME NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- If upgrading from previous schedule version, add new fields
-- Uncomment these lines if you already have a schedule table without these fields:
-- ALTER TABLE schedule MODIFY COLUMN event_type ENUM('meeting', 'deadline', 'shoot', 'event', 'work', 'reminder', 'other') DEFAULT 'event';
-- ALTER TABLE schedule ADD COLUMN is_private TINYINT(1) DEFAULT 0 AFTER status;

-- Insert sample events (optional - remove if not wanted)
-- INSERT INTO schedule (title, description, event_date, start_time, event_type, status, is_private, created_at, updated_at) VALUES
-- ('Client Meeting', 'Discuss new project requirements', '2025-01-15', '14:00:00', 'meeting', 'scheduled', 0, NOW(), NOW()),
-- ('Photo Shoot', 'Product photography session', '2025-01-20', '10:00:00', 'shoot', 'scheduled', 0, NOW(), NOW()),
-- ('Work Day', 'Studio time for editing', '2025-01-22', '09:00:00', 'work', 'scheduled', 0, NOW(), NOW()),
-- ('Private Meeting', 'Confidential client discussion', '2025-01-23', '15:00:00', 'meeting', 'scheduled', 1, NOW(), NOW());

SELECT 'Schedule module added successfully!' as message;
