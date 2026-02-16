-- Database: E_Learning
-- This script creates the database, tables, and inserts initial data.

-- 1. Create the database
CREATE DATABASE IF NOT EXISTS E_Learning;
USE E_Learning;

-- 2. Create the Admins table for instructor login
-- NOTE: We are NOT using password_hash as requested, but this is a security risk in a real application.
CREATE TABLE IF NOT EXISTS admins (
    admin_id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL, -- Storing plaintext password as requested, but highly discouraged.
    email VARCHAR(100) NOT NULL,
    full_name VARCHAR(100) NOT NULL
);

-- 3. Create the Courses table
CREATE TABLE IF NOT EXISTS courses (
    course_id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    course_name VARCHAR(255) NOT NULL,
    course_description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 4. Create the Classes table (Specific schedule/instance of a course)
CREATE TABLE IF NOT EXISTS classes (
    class_id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    course_id INT(11) UNSIGNED NOT NULL,
    class_name VARCHAR(255) NOT NULL,
    class_date DATE NOT NULL,
    class_time TIME NOT NULL,
    class_link VARCHAR(255), -- Could be a meeting link
    FOREIGN KEY (course_id) REFERENCES courses(course_id) ON DELETE CASCADE
);

-- 5. Create the Course Videos table
CREATE TABLE IF NOT EXISTS course_videos (
    video_id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    course_id INT(11) UNSIGNED NOT NULL,
    video_title VARCHAR(255) NOT NULL,
    video_url VARCHAR(255) NOT NULL, -- URL to the video (e.g., YouTube embed link)
    FOREIGN KEY (course_id) REFERENCES courses(course_id) ON DELETE CASCADE
);

-- 6. Create the Users table (for students who enroll)
-- Students do not need to sign in, their enrollment data is sufficient
CREATE TABLE IF NOT EXISTS users (
    user_id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    phone VARCHAR(20)
);

-- 7. Create the Enrollments table (links users to classes)
CREATE TABLE IF NOT EXISTS enrollments (
    enrollment_id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT(11) UNSIGNED NOT NULL,
    class_id INT(11) UNSIGNED NOT NULL,
    enrollment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_enrollment (user_id, class_id), -- Prevent duplicate enrollment
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (class_id) REFERENCES classes(class_id) ON DELETE CASCADE
);

-- 8. Create the Contacts table (for user questions)
CREATE TABLE IF NOT EXISTS contacts (
    contact_id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    sender_name VARCHAR(100) NOT NULL,
    sender_email VARCHAR(100) NOT NULL,
    subject VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 9. Insert initial data (including an admin that is "already exist in my database")

-- Insert default admin (Instructor)
INSERT INTO admins (username, password, email, full_name) VALUES
('admin101', 'securepass', 'admin@elearn.edu', 'Professor Alice');

-- Insert sample courses
INSERT INTO courses (course_name, course_description) VALUES
('Web Development Fundamentals', 'Learn the basics of HTML5, CSS3, and JavaScript.'),
('Database Design with MySQL', 'An introduction to relational database management and SQL.'),
('PHP Backend Development', 'Building dynamic web applications using PHP.');

-- Insert sample classes for Web Development Fundamentals (course_id=1)
INSERT INTO classes (course_id, class_name, class_date, class_time) VALUES
(1, 'HTML & CSS Layouts', '2025-12-01', '10:00:00'),
(1, 'JavaScript DOM Manipulation', '2025-12-03', '14:00:00');

-- Insert sample videos for Web Development Fundamentals (course_id=1)
INSERT INTO course_videos (course_id, video_title, video_url) VALUES
(1, 'Intro to HTML Structure', 'https://www.youtube.com/embed/dQw4w9WgXcQ'),
(1, 'CSS Flexbox Tutorial', 'https://www.youtube.com/embed/Yykjyrk2_tY');

-- Insert sample classes for Database Design (course_id=2)
INSERT INTO classes (course_id, class_name, class_date, class_time) VALUES
(2, 'SQL Queries Basic', '2025-12-05', '09:00:00');

-- Insert a sample user and enrollment to test the system
INSERT INTO users (full_name, email, phone) VALUES
('John Doe', 'john.doe@test.com', '1234567890');

INSERT INTO enrollments (user_id, class_id) VALUES
(1, 1); -- John Doe enrolled in 'HTML & CSS Layouts' (class_id=1)