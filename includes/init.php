<?php

// Define required constant variables 
define("APP_URL", "/tuition-enquiry");
define("APP_PATH", dirname(__DIR__));

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set page title if not set
if(!isset($page_title)){
    $page_title = "Tuition - Enquiry";
}

// Set default timezone
date_default_timezone_set("Asia/Kolkata");

// Require All Important Functions 
require_once "functions.php";

// Require DB Connection
require_once "db-connection.php";

