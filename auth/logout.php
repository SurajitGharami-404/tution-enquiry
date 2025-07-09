<?php
require_once "../includes/init.php";
session_destroy();
$_SESSION = [];
header("Location: " . APP_URL . "/auth/login.php");
exit;