-- Create a simple database schema for the grade program
-- RESET DATABASE
DROP DATABASE IF EXISTS lab3;
CREATE DATABASE lab3;
USE lab3;

-- USERS (ALL ROLES)
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin','professor','student') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- SEMESTERS
CREATE TABLE semesters (
    id INT AUTO_INCREMENT PRIMARY KEY,
    label VARCHAR(20) NOT NULL,
    academic_year VARCHAR(20) NOT NULL,
    is_active BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- COURSES (LINKED TO SEMESTER)
CREATE TABLE courses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    semester_id INT NOT NULL,
    name VARCHAR(150) NOT NULL,
    credits INT NOT NULL,
    FOREIGN KEY (semester_id) REFERENCES semesters(id)
);

-- ENROLLMENTS (STUDENT ↔ SEMESTER)
CREATE TABLE enrollments (
    student_id INT NOT NULL,
    semester_id INT NOT NULL,
    PRIMARY KEY (student_id, semester_id),
    FOREIGN KEY (student_id) REFERENCES users(id),
    FOREIGN KEY (semester_id) REFERENCES semesters(id)
);

-- ASSIGNMENTS (PROFESSOR ↔ COURSE ↔ SEMESTER)
CREATE TABLE assignments (
    professor_id INT NOT NULL,
    course_id INT NOT NULL,
    semester_id INT NOT NULL,
    PRIMARY KEY (professor_id, course_id, semester_id),
    FOREIGN KEY (professor_id) REFERENCES users(id),
    FOREIGN KEY (course_id) REFERENCES courses(id),
    FOREIGN KEY (semester_id) REFERENCES semesters(id)
);

-- GRADES (FIXED)
CREATE TABLE grades (
    student_id INT NOT NULL,
    course_id INT NOT NULL,
    semester_id INT NOT NULL,
    professor_id INT NOT NULL,
    grade DECIMAL(3,1) NOT NULL,
    PRIMARY KEY (student_id, course_id, semester_id),
    FOREIGN KEY (student_id) REFERENCES users(id),
    FOREIGN KEY (course_id) REFERENCES courses(id),
    FOREIGN KEY (semester_id) REFERENCES semesters(id),
    FOREIGN KEY (professor_id) REFERENCES users(id)
);

-- GPA RECORDS
CREATE TABLE gpa_records (
    student_id INT NOT NULL,
    semester_id INT NOT NULL,
    gpa DECIMAL(4,2) NOT NULL,
    PRIMARY KEY (student_id, semester_id),
    FOREIGN KEY (student_id) REFERENCES users(id),
    FOREIGN KEY (semester_id) REFERENCES semesters(id)
);

-- =========================
-- 🔹 TEST DATA (IMPORTANT)
-- =========================

-- USERS
INSERT INTO users (name, email, password, role) VALUES
('Admin User', 'admin@test.com', '$2y$12$8XyOByme.tFdsy2/p1AQL.MQ4Uyn43RPHpExu6jvClm3eHzz00tlm', 'admin'),
('Dr. Smith', 'prof@test.com', '$2y$12$8XyOByme.tFdsy2/p1AQL.MQ4Uyn43RPHpExu6jvClm3eHzz00tlm', 'professor'),
('Alice', 'student@test.com', '$2y$12$8XyOByme.tFdsy2/p1AQL.MQ4Uyn43RPHpExu6jvClm3eHzz00tlm', 'student');

-- SEMESTERS
INSERT INTO semesters (label, academic_year, is_active) VALUES
('S1', '2025-2026', TRUE);

-- COURSES
INSERT INTO courses (semester_id, name, credits) VALUES
(1, 'Mathematics', 3),
(1, 'Physics', 4);

-- ENROLL STUDENT
INSERT INTO enrollments (student_id, semester_id) VALUES
(3, 1);

-- ASSIGN PROFESSOR
INSERT INTO assignments (professor_id, course_id, semester_id) VALUES
(2, 1, 1),
(2, 2, 1);