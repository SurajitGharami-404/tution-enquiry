<?php

// Database configuration
$host = 'localhost';
$db   = 'tuition_enquiry_db';
$user = 'root';
$pwd  = '';
$charset = 'utf8mb4';

// Create MySQLi connection
$conn = new mysqli($host, $user, $pwd, $db);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset
if (!$conn->set_charset($charset)) {
    die("Error loading character set $charset: " . $conn->error);
}
?>
