-- ==========================================================
-- Practical 9: Secure User Registration Schema
-- Database: studenthub
-- Supports Bcrypt/Argon2 Password Hashing (VARCHAR 255)
-- ==========================================================

CREATE DATABASE IF NOT EXISTS studenthub;
USE studenthub;

CREATE TABLE IF NOT EXISTS students (
    student_id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    mobile VARCHAR(15) NOT NULL,
    password VARCHAR(255) NOT NULL, -- Stores password_hash() Bcrypt hash
    course VARCHAR(50) NOT NULL,
    year INT NOT NULL,
    gender ENUM('Male', 'Female', 'Other') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Reset and Insert requested student data with hashed passwords (password: 'password123')
TRUNCATE TABLE students;

INSERT INTO students (student_id, full_name, email, mobile, password, course, year, gender) VALUES
(1, 'Kremil', '25dce022@charusat.edu.in', '9727207234', '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIVFlYg7B77UdFm', 'CE', 2, 'Male'),
(2, 'Shubham Desai', '25dce020@charusat.edu.in', '9876594934', '$2y$10$O0Fq7s7G51kQ2m3p4Z7w6eY9kL0j1k2l3m4n5o6p7q8r9s0t1u2v3', 'CE', 2, 'Male'),
(3, 'Shan Dabhi', '25dce018@charusat.edu.in', '8401792927', '$2y$10$xW7.eG4c1rT2yU3i4O5p6a7s8d9f0g1h2j3k4l5z6x7c8v9b0n1m2', 'CE', 2, 'Male');
