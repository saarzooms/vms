CREATE DATABASE IF NOT EXISTS visitor_db;

USE visitor_db;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM(
        'admin',
        'manager',
        'receptionist'
    ) NOT NULL
);

CREATE TABLE visitors (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    mobile VARCHAR(15) UNIQUE NOT NULL,
    image_path VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE visits (
    id INT AUTO_INCREMENT PRIMARY KEY,
    visitor_id INT NOT NULL,
    purpose VARCHAR(255) NOT NULL,
    visited_to VARCHAR(100) NOT NULL,
    in_time DATETIME DEFAULT CURRENT_TIMESTAMP,
    out_time DATETIME NULL,
    FOREIGN KEY (visitor_id) REFERENCES visitors (id) ON DELETE CASCADE
);

-- Default Admin User (Password is 'admin123')
INSERT INTO
    users (username, password, role)
VALUES (
        'owner',
        '$2y$10$6GvOJ9PPVWN.TD10f1kVCOg8jAvnte01or3DOjG91am9E.UTAFcsW',
        'admin'
    ),
    (
        'manager',
        '$2y$10$6GvOJ9PPVWN.TD10f1kVCOg8jAvnte01or3DOjG91am9E.UTAFcsW',
        'manager'
    ),
    (
        'desk',
        '$2y$10$6GvOJ9PPVWN.TD10f1kVCOg8jAvnte01or3DOjG91am9E.UTAFcsW',
        'receptionist'
    );