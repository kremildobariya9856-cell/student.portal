-- ==========================================================
-- Database: studenthub
-- Table: students
-- Supports Password Hashing, Role-Based Access, & Remember-Me
-- ==========================================================

CREATE DATABASE IF NOT EXISTS studenthub;
USE studenthub;

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

-- Seed default students and admin account
-- Students default password: 'password123'
-- Admin default password: 'admin123'
INSERT INTO students (student_id, full_name, email, mobile, password, course, year, gender, role) VALUES
(1, 'Kremil', '25dce022@charusat.edu.in', '9727207234', '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIVFlYg7B77UdFm', 'CE', 2, 'Male', 'student'),
(2, 'Shubham Desai', '25dce020@charusat.edu.in', '9876594934', '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIVFlYg7B77UdFm', 'CE', 2, 'Male', 'student'),
(3, 'Shan Dabhi', '25dce018@charusat.edu.in', '8401792927', '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIVFlYg7B77UdFm', 'CE', 2, 'Male', 'student'),
(4, 'Administrator', 'admin@charusat.edu.in', '9999988888', '$2y$10$f/9nL0MwqzQ7hGzG8i1l4.R9tG5xP5m4aT7oU9vX2yZ8bC1dE3fGi', 'ADMIN', 4, 'Other', 'admin')
ON DUPLICATE KEY UPDATE 
    full_name = VALUES(full_name),
    mobile = VALUES(mobile),
    course = VALUES(course),
    year = VALUES(year),
    password = VALUES(password),
    role = VALUES(role);
