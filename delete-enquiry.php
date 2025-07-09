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

$get_enquiry_stmt = $conn->prepare("SELECT id FROM enquiries WHERE id=?");
$get_enquiry_stmt->bind_param("i", $sanitized_id);

if (!$get_enquiry_stmt->execute()) {
    $_SESSION["errors"][] = "Database error: " . $id_check_stmt->error;
    header("Location: " . APP_URL . "/enquiries.php");
    exit;
}

$get_enquiry_result = $get_enquiry_stmt->get_result();
if ($get_enquiry_result->num_rows < 1) {
    $_SESSION["errors"][] = "Enquiry not found.";
    header("Location: " . APP_URL . "/enquiries.php");
    exit;
}

$get_enquiry_result->close();

$delete_enquiry_stmt = $conn->prepare("DELETE FROM enquiries WHERE id=?");
$delete_enquiry_stmt->bind_param("i", $sanitized_id);

if ($delete_enquiry_stmt->execute()) {
    $_SESSION["success"] = "Enquiry deleted successfully.";
} else {
    $_SESSION["errors"][] = "Failed to delete course: " . $delete_enquiry_stmt->error;
}
$delete_enquiry_stmt->close();
$conn->close();

header("Location: " . APP_URL . "/enquiries.php");
exit;
