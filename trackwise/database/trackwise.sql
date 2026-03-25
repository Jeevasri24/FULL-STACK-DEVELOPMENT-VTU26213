-- ============================================
-- TRACKWISE DATABASE SETUP
-- Run this in phpMyAdmin SQL tab
-- ============================================

CREATE DATABASE IF NOT EXISTS trackwise;
USE trackwise;

-- USERS TABLE
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    account_type ENUM('personal', 'business') DEFAULT 'personal',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- CATEGORIES TABLE
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    icon VARCHAR(50) DEFAULT '💰',
    color VARCHAR(20) DEFAULT '#6C63FF',
    type ENUM('personal', 'business') DEFAULT 'personal',
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- EXPENSES TABLE
CREATE TABLE expenses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    category_id INT,
    title VARCHAR(200) NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    type ENUM('personal', 'business') DEFAULT 'personal',
    note TEXT,
    expense_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);

-- DEFAULT CATEGORIES (inserted after user registers — handled in PHP)
-- These are just examples for reference:
-- Food & Dining, Transport, Shopping, Health, Entertainment
-- Office Supplies, Travel, Marketing, Utilities, Salary