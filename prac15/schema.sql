-- ==========================================================
-- Database: studenthub
-- Tables: students, events, registrations
-- Supports Password Hashing, RBAC, CRUD, & Poster Upload
-- ==========================================================

CREATE DATABASE IF NOT EXISTS studenthub;
USE studenthub;

-- 1. Students Table
CREATE TABLE IF NOT EXISTS students (
    student_id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    mobile VARCHAR(15) NOT NULL,
    password VARCHAR(255) NOT NULL,
    course VARCHAR(50) NOT NULL,
    year INT NOT NULL,
    gender ENUM('Male', 'Female', 'Other') NOT NULL,
    role ENUM('student', 'admin') NOT NULL DEFAULT 'student',
    remember_token VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Events Table (with Poster and Status)
CREATE TABLE IF NOT EXISTS events (
    event_id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(150) NOT NULL,
    category VARCHAR(50) NOT NULL,
    description TEXT,
    event_date DATE NOT NULL,
    event_time VARCHAR(20) NOT NULL,
    venue VARCHAR(100) NOT NULL,
    capacity INT DEFAULT 100,
    poster VARCHAR(255) DEFAULT NULL,
    status ENUM('Upcoming', 'Ongoing', 'Completed', 'Cancelled') NOT NULL DEFAULT 'Upcoming',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Registrations Table
CREATE TABLE IF NOT EXISTS registrations (
    registration_id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    event_id INT NOT NULL,
    registration_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('Confirmed', 'Pending', 'Cancelled') DEFAULT 'Confirmed',
    UNIQUE KEY unique_student_event (student_id, event_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Audit Logs Table (Admin Actions, RBAC Traceability)
CREATE TABLE IF NOT EXISTS audit_logs (
    log_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT DEFAULT NULL,
    user_name VARCHAR(100) DEFAULT NULL,
    action VARCHAR(50) NOT NULL,
    details TEXT,
    ip_address VARCHAR(45) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed default students and admin account
-- Student Password: password123 | Admin Password: admin123
INSERT INTO students (student_id, full_name, email, mobile, password, course, year, gender, role) VALUES
(1, 'Kremil', '25dce022@charusat.edu.in', '9727207234', '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIVFlYg7B77UdFm', 'CE', 2, 'Male', 'student'),
(2, 'Shubham Desai', '25dce020@charusat.edu.in', '9876594934', '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIVFlYg7B77UdFm', 'CE', 2, 'Male', 'student'),
(3, 'Shan Dabhi', '25dce018@charusat.edu.in', '8401792927', '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIVFlYg7B77UdFm', 'CE', 2, 'Male', 'student'),
(4, 'Administrator', 'admin@charusat.edu.in', '9999988888', '$2y$10$f/9nL0MwqzQ7hGzG8i1l4.R9tG5xP5m4aT7oU9vX2yZ8bC1dE3fGi', 'ADMIN', 4, 'Other', 'admin')
ON DUPLICATE KEY UPDATE full_name = VALUES(full_name), role = VALUES(role);

-- Seed default events
INSERT INTO events (event_id, title, category, description, event_date, event_time, venue, capacity, poster, status) VALUES
(1, 'Tech Innovate 2026', 'Technical', 'Annual university hackathon and coding contest.', '2026-11-15', '10:00 AM', 'Seminar Hall 1', 120, NULL, 'Upcoming'),
(2, 'Spoural Annual Sports Fest', 'Sports', 'Inter-department sports tournament including Cricket, Football, and Badminton.', '2026-11-20', '08:30 AM', 'University Ground', 300, 'spoural.jpg', 'Upcoming'),
(3, 'Web Dev Workshop', 'Academic', 'Hands-on workshop on full-stack web development with PHP and MySQL.', '2026-12-05', '02:00 PM', 'Lab 204', 60, NULL, 'Ongoing'),
(4, 'Cognizance Cultural Night', 'Cultural', 'Music, dance, drama and cultural performances by students.', '2026-12-18', '06:00 PM', 'Auditorium', 500, NULL, 'Upcoming')
ON DUPLICATE KEY UPDATE title = VALUES(title), status = VALUES(status);

-- Seed sample registrations
INSERT INTO registrations (registration_id, student_id, event_id, status) VALUES
(1, 1, 1, 'Confirmed'),
(2, 2, 1, 'Confirmed'),
(3, 3, 2, 'Confirmed')
ON DUPLICATE KEY UPDATE status = VALUES(status);

-- Seed sample audit logs
INSERT INTO audit_logs (user_id, user_name, action, details, ip_address) VALUES
(4, 'Administrator', 'LOGIN', 'Admin authenticated successfully via login page', '127.0.0.1'),
(4, 'Administrator', 'ADD_EVENT', 'Admin added event: Tech Innovate 2026 (Technical)', '127.0.0.1'),
(4, 'Administrator', 'ADD_STUDENT', 'Admin enrolled student #1: Kremil (25dce022@charusat.edu.in, CE)', '127.0.0.1');
