<?php
// Database connection using XAMPP defaults
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'smart_timetable';

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die('Database connection failed: ' . $conn->connect_error);
}

// Create teachers table if not exists
$conn->query("CREATE TABLE IF NOT EXISTS teachers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL
)");

// Create rooms table if not exists
$conn->query("CREATE TABLE IF NOT EXISTS rooms (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL
)");

// Create modules table if not exists
$conn->query("CREATE TABLE IF NOT EXISTS modules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    module_code VARCHAR(50),
    periods_per_week INT NOT NULL,
    preferred_time ENUM('Any','Morning','Afternoon') DEFAULT 'Any',
    teacher_id INT NULL,
    class_ids VARCHAR(255) NULL
)");
?> 