-- ==========================================================
-- Practical 8: MySQL Schema for CHARUSAT Student Hub
-- Database: studenthub
-- Normalized 3NF Schema with Primary & Foreign Keys
-- ==========================================================

-- 1. Create Database
CREATE DATABASE IF NOT EXISTS studenthub;
USE studenthub;

-- 2. Drop existing tables if they exist (in order of dependencies)
DROP TABLE IF EXISTS registrations;
DROP TABLE IF EXISTS events;
DROP TABLE IF EXISTS students;

-- 3. Students Table (Primary Entity)
CREATE TABLE IF NOT EXISTS students (
    student_id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    mobile VARCHAR(15) NOT NULL,
    password VARCHAR(255) NOT NULL,
    course VARCHAR(50) NOT NULL,
    year INT NOT NULL,
    gender ENUM('Male', 'Female', 'Other') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Events Table (Primary Entity)
CREATE TABLE IF NOT EXISTS events (
    event_id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(150) NOT NULL,
    category VARCHAR(50) NOT NULL,
    description TEXT,
    event_date DATE NOT NULL,
    event_time VARCHAR(20) NOT NULL,
    venue VARCHAR(100) NOT NULL,
    capacity INT DEFAULT 100,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. Registrations Table (Associative Entity for Many-to-Many Relationship)
CREATE TABLE IF NOT EXISTS registrations (
    registration_id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    event_id INT NOT NULL,
    registration_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('Confirmed', 'Pending', 'Cancelled') DEFAULT 'Confirmed',
    
    -- Foreign Key Constraints with Referential Integrity
    CONSTRAINT fk_registration_student FOREIGN KEY (student_id) 
        REFERENCES students(student_id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_registration_event FOREIGN KEY (event_id) 
        REFERENCES events(event_id) ON DELETE CASCADE ON UPDATE CASCADE,
        
    -- Ensure a student cannot register twice for the same event
    UNIQUE KEY unique_student_event (student_id, event_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ==========================================================
-- Sample Seed Data
-- ==========================================================

-- Insert sample events
INSERT INTO events (title, category, description, event_date, event_time, venue, capacity) VALUES
('Tech Innovate 2026', 'Technical', 'Annual university hackathon and coding contest.', '2026-10-15', '10:00 AM', 'Seminar Hall 1', 120),
('Spoural Annual Sports Fest', 'Sports', 'Inter-department sports tournament including Cricket, Football, and Badminton.', '2026-10-22', '08:30 AM', 'University Ground', 300),
('Web Dev Workshop', 'Academic', 'Hands-on workshop on full-stack web development with PHP and MySQL.', '2026-11-05', '02:00 PM', 'Lab 204', 60),
('Cognizance Cultural Night', 'Cultural', 'Music, dance, drama and cultural performances by students.', '2026-11-18', '06:00 PM', 'Auditorium', 500);

-- Insert sample students
INSERT INTO students (full_name, email, mobile, password, course, year, gender) VALUES
('Rahul Sharma', 'rahul.sharma@example.com', '9876543210', 'password123', 'CE', 3, 'Male'),
('Priya Patel', 'priya.patel@example.com', '9823456789', 'password123', 'IT', 2, 'Female'),
('Aman Verma', 'aman.verma@example.com', '9811223344', 'password123', 'ME', 4, 'Male');

-- Insert sample event registrations
INSERT INTO registrations (student_id, event_id, status) VALUES
(1, 1, 'Confirmed'),
(1, 3, 'Confirmed'),
(2, 1, 'Confirmed'),
(2, 4, 'Confirmed'),
(3, 2, 'Confirmed');
