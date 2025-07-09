<?php
require_once "./includes/init.php";
require_once "./includes/auth-check.php";

$id = isset($_GET["id"]) ? $_GET["id"] : 0;
$sanitized_id = filter_var(sanitize($id), FILTER_SANITIZE_NUMBER_INT);

if ($sanitized_id <= 0) {
    $_SESSION["errors"][] = "Invalid course ID.";
    header("Location: " . APP_URL . "/courses.php");
    exit;
}

$get_course_stmt = $conn->prepare("SELECT id FROM courses WHERE id=?");
$get_course_stmt->bind_param("i", $sanitized_id);

if (!$get_course_stmt->execute()) {
    $_SESSION["errors"][] = "Database error: " . $id_check_stmt->error;
    header("Location: " . APP_URL . "/courses.php");
    exit;
}

$get_course_result = $get_course_stmt->get_result();
if ($get_course_result->num_rows < 1) {
    $_SESSION["errors"][] = "course not found.";
    header("Location: " . APP_URL . "/courses.php");
    exit;
}

$get_course_result->close();

$delete_course_stmt = $conn->prepare("DELETE FROM courses WHERE id=?");
$delete_course_stmt->bind_param("i", $sanitized_id);

if ($delete_course_stmt->execute()) {
    $_SESSION["success"] = "Course deleted successfully.";
} else {
    $_SESSION["errors"][] = "Failed to delete course: " . $delete_course_stmt->error;
}
$delete_course_stmt->close();
$conn->close();

header("Location: " . APP_URL . "/courses.php");
exit;
