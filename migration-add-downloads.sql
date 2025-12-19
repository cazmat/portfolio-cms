-- Migration: Add Project Files/Downloads System
-- Run this to add file download functionality for clients

-- Create project_files table
CREATE TABLE IF NOT EXISTS project_files (
    id INT AUTO_INCREMENT PRIMARY KEY,
    project_id INT NOT NULL,
    filename VARCHAR(255) NOT NULL,
    original_filename VARCHAR(255) NOT NULL,
    file_size INT NOT NULL,
    file_type VARCHAR(100),
    description TEXT,
    uploaded_by INT,
    uploaded_at DATETIME NULL,
    download_count INT DEFAULT 0,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_project (project_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

SELECT 'Project files/downloads system added successfully!' as message;
SELECT 'Remember to create the downloads/ directory with proper permissions!' as reminder;
