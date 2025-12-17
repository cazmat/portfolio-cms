-- Portfolio CMS Database Schema
-- Alternative version for MySQL 5.5+ with InnoDB large prefix disabled

CREATE DATABASE IF NOT EXISTS portfolio_cms CHARACTER SET utf8 COLLATE utf8_general_ci;

USE portfolio_cms;

-- Admin Users Table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(191) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    first_name VARCHAR(100),
    last_name VARCHAR(100),
    role ENUM('admin', 'client', 'family') DEFAULT 'client',
    status ENUM('active', 'inactive', 'pending') DEFAULT 'active',
    company VARCHAR(200),
    phone VARCHAR(50),
    notes TEXT,
    last_login DATETIME NULL,
    created_at DATETIME NULL,
    updated_at DATETIME NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Portfolio Projects Table
CREATE TABLE IF NOT EXISTS projects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    slug VARCHAR(200) UNIQUE NOT NULL,
    description TEXT,
    content TEXT,
    featured_image VARCHAR(255),
    category VARCHAR(100),
    tags VARCHAR(255),
    project_url VARCHAR(255),
    client VARCHAR(100),
    date_completed DATE,
    status ENUM('draft', 'published') DEFAULT 'draft',
    display_order INT DEFAULT 0,
    created_at DATETIME NULL,
    updated_at DATETIME NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Project Images Table (Gallery)
CREATE TABLE IF NOT EXISTS project_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    project_id INT NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    caption TEXT,
    display_order INT DEFAULT 0,
    created_at DATETIME NULL,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- About/Bio Content
CREATE TABLE IF NOT EXISTS about (
    id INT AUTO_INCREMENT PRIMARY KEY,
    content TEXT,
    profile_image VARCHAR(255),
    skills TEXT,
    updated_at DATETIME NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Contact Messages
CREATE TABLE IF NOT EXISTS messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    subject VARCHAR(200),
    message TEXT NOT NULL,
    status ENUM('unread', 'read', 'archived') DEFAULT 'unread',
    created_at DATETIME NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Site Settings
CREATE TABLE IF NOT EXISTS settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(50) UNIQUE NOT NULL,
    setting_value TEXT,
    updated_at DATETIME NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Project Access Control (links projects to client users)
CREATE TABLE IF NOT EXISTS project_access (
    id INT AUTO_INCREMENT PRIMARY KEY,
    project_id INT NOT NULL,
    user_id INT NOT NULL,
    can_view TINYINT(1) DEFAULT 1,
    can_download TINYINT(1) DEFAULT 0,
    created_at DATETIME NULL,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_access (project_id, user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Work Schedule (visible to Admin and Family only)
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

-- Insert default admin user (password: admin123 - CHANGE THIS!)
-- The password hash below is for 'admin123' - PLEASE CHANGE THIS AFTER FIRST LOGIN!
INSERT INTO users (username, email, password, first_name, last_name, role, status, created_at, updated_at) 
VALUES ('admin', 'admin@example.com', '$2y$10$7Rq5H4zT4gzI1xGdOlBqxeF5TIUlZWQXOCGKQOKYmNPVQJvLJ9Yv6', 'Admin', 'User', 'admin', 'active', NOW(), NOW());

-- Insert default about content
INSERT INTO about (content, skills, updated_at) 
VALUES ('Welcome to my portfolio. Update this content in the admin panel.', 'Web Design, Development, Photography', NOW());

-- Insert default settings
INSERT INTO settings (setting_key, setting_value, updated_at) VALUES 
('site_name', 'My Portfolio', NOW()),
('site_tagline', 'Creative Professional', NOW()),
('contact_email', 'contact@example.com', NOW()),
('social_linkedin', '', NOW()),
('social_github', '', NOW()),
('social_twitter', '', NOW()),
('items_per_page', '9', NOW());
