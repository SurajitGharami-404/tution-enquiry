<?php

require_once "init.php";

if (!isset($_SESSION["user"]) || empty($_SESSION["user"])) {
    header("Location: " . APP_URL . "/auth/login.php");
    exit;
}
