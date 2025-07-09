<?php
require_once "./includes/init.php";
require_once "./includes/auth-check.php";

$id = isset($_GET["id"]) ? $_GET["id"] : 0;
$sanitized_id = filter_var(sanitize($id), FILTER_SANITIZE_NUMBER_INT);

if ($sanitized_id <= 0) {
    header("Location: " . APP_URL . "/enquiries.php");
    exit;
}

$get_enquiry_stmt = $conn->prepare("
    SELECT enquiries.*, courses.name AS course_name, courses.id AS course_id
    FROM enquiries
    LEFT JOIN courses ON enquiries.course_id = courses.id WHERE enquiries.id=?
");
$get_enquiry_stmt->bind_param("i", $sanitized_id);

if (!$get_enquiry_stmt->execute()) {
    $_SESSION["errors"][] = "Database error: " . $get_enquiry_stmt->error;
    header("Location: " . APP_URL . "/enquiries.php");
    exit;
}
$get_enquiry_results = $get_enquiry_stmt->get_result();

if ($get_enquiry_results->num_rows < 1) {
    $_SESSION["errors"][] = "Invalid enquiry id.";
    header("Location: " . APP_URL . "/enquiries.php");
    exit;
}

$enquiry = $get_enquiry_results->fetch_assoc();

$get_courses_stmt = $conn->prepare("SELECT * FROM courses WHERE 1");

if (!$get_courses_stmt->execute()) {
    $_SERVER["errors"][] = "Database Error: " . $get_courses_stmt->error();
    $get_courses_stmt->close();
    header("Location: " . $_SERVER['REQUEST_URI']);
    exit;
}

$get_courses_result = $get_courses_stmt->get_result();
$courses = $get_courses_result->fetch_all(MYSQLI_ASSOC);
$get_courses_stmt->close();
$get_enquiry_stmt->close();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $sanitized_post = sanitize_post($_POST);
    $firstname = $sanitized_post["firstname"];
    $lastname = $sanitized_post["lastname"];
    $course_id = $sanitized_post["course_id"];
    $contact = $sanitized_post["contact"];
    $status = $sanitized_post["status"];
    $description = $sanitized_post["desc"];

    $allowed_status = ["pending", "confirmed", "cancelled"];
    $errors = [];

    $csrf_token = $sanitized_post["csrf_token"];
    if (!csrf_validate($csrf_token)) {
        $errors[] = "Invalid request.";
    }

    if (empty($firstname)) {
        $errors[] = "First name is required.";
    } else if (strlen($firstname) < 3) {
        $errors[] = "First name should have at least 3 characters.";
    }
    if (empty($lastname)) {
        $errors[] = "Last name is required.";
    } else if (strlen($lastname) < 3) {
        $errors[] = "Last name should have at least 3 characters.";
    }

    if (!preg_match('/^[6-9]\d{9}$/', $contact)) {
        $errors[] = "Invalid contact number.";
    }

    $course_ids = array_column($courses, "id");

    if (!in_array($course_id, $course_ids)) {
        $errors[] = "Invalid course selected.";
    }

    if (!in_array($status, $allowed_status)) {
        $errors[] = "Invalid status selected.";
    }

    if (!empty($errors)) {
        $_SESSION["errors"] = $errors;
        header("Location: " . $_SERVER['REQUEST_URI']);
        exit;
    }

    $update_enquiry_stmt = $conn->prepare("
    UPDATE enquiries 
    SET first_name = ?, last_name = ?, contact = ?, description = ?, status = ?, course_id = ?
    WHERE id = ?
    ");
    $update_enquiry_stmt->bind_param("sssssii", $firstname, $lastname, $contact, $description, $status, $course_id, $sanitized_id);

    if ($update_enquiry_stmt->execute()) {
        $_SESSION["success"] = "Enquiry updated successfully.";
        $update_enquiry_stmt->close();
        $conn->close();
        header("Location: " . $_SERVER['REQUEST_URI']);
        exit;
    } else {
        $_SESSION["errors"][] = "Database error: " . $update_enquiry_stmt->error;
        $update_enquiry_stmt->close();
        $conn->close();
        header("Location: " . $_SERVER['REQUEST_URI']);
        exit;
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php
    $page_title = "Tute.io - Enquiry details page";
    include_once APP_PATH . "/partials/header.php";
    ?>
</head>

<body>
    <?php
    $active_link = "enquiry";
    include_once APP_PATH . "/partials/nav.php";
    ?>
    <?php include_once APP_PATH . "/partials/alert.php" ?>
    <main class="main-content container-lg">
        <!-- Breadcrumb -->
        <div role="navigation" style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= APP_URL ?>/">Home</a></li>
                <li class="breadcrumb-item"><a href="<?= APP_URL ?>/enquiries.php">Enquiries</a></li>
                <li class="breadcrumb-item active" aria-current="page">Edit</li>
            </ol>
        </div>
        <!-- Page Heading -->
        <div class="d-flex align-items-center justify-content-between" style="margin-bottom: var(--spacing-lg);">
            <h2 class="page-heading my-0">Enquiry</h2>
        </div>
        <!-- Edit Enquiry Details Section -->
        <section>
            <div class="card">
                <!-- Form Header -->
                <div class="card-header">
                    <h3 class="fs-6 fw-normal mb-0" style="color: var(--light-text-color)">Edit Enquiry Form</h3>
                </div>
                <!-- Add Course Form -->
                <form action="<?= APP_URL ?>/edit-enquiry.php?id=<?= $sanitized_id ?>" method="POST" class="card-body">
                    <?= csrf_input() ?>
                    <!-- Name Input -->
                    <div class="row g-3 mb-3">
                        <div class="col-md-6 col-12">
                            <label for="firstname" class="form-label">First name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="firstname" name="firstname" placeholder="Enter first name" value="<?= sanitize($enquiry["first_name"]) ?>" required>
                        </div>
                        <div class="col-md-6 col-12">
                            <label for="lastname" class="form-label">Last name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="lastname" name="lastname" placeholder="Enter last name" value="<?= sanitize($enquiry["last_name"]) ?>" required>
                        </div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6 col-12">
                            <label for="courseDropdownMenu" class="form-label">Course <span class="text-danger">*</span></label>
                            <select class="form-select" aria-label="active" name="course_id">
                                <?php if ($get_courses_result->num_rows < 1): ?>
                                    <option value="">Add course</option>
                                <?php endif ?>
                                <?php if ($get_courses_result->num_rows > 0): ?>
                                    <?php foreach ($courses as $course): ?>
                                        <option value="<?= sanitize($course["id"]) ?>" <?= $course["id"] === $enquiry["course_id"] ? 'selected' : '' ?>><?= sanitize($course["name"]) ?></option>
                                    <?php endforeach ?>
                                <?php endif ?>
                            </select>
                        </div>
                        <div class="col-md-6 col-12">
                            <label for="contact" class="form-label">Contact No.<span class="text-danger">*</span></label>
                            <input type="tel" id="contact" name="contact" class="form-control" placeholder="Enter contact" value="<?= sanitize($enquiry["contact"]) ?>" required>
                        </div>
                    </div>
                    <!-- Status input -->
                    <div class="mb-3">
                        <label for="status" class="form-label">Status<span class="text-danger">*</span></label>
                        <select name="status" id="status" class="form-select">
                            <option value="pending" <?= $enquiry["status"] === "pending" ? 'selected' : '' ?>>Pending</option>
                            <option value="confirmed" <?= $enquiry["status"] === "confirmed" ? 'selected' : '' ?>>Confirmed</option>
                            <option value="cancelled" <?= $enquiry["status"] === "cancelled" ? 'selected' : '' ?>>Cancelled</option>
                        </select>
                    </div>
                    <!-- Description input -->
                    <div class="mb-3">
                        <label for="desc" class="form-label">Description <span class="text-danger">*</span></label>
                        <textarea class="form-control" name="desc" id="desc" rows="5" placeholder="Enter description"><?= sanitize($enquiry["description"]) ?></textarea>
                    </div>
                    <!-- Form Submit Button -->
                    <div class="d-flex justify-content-end">
                        <input type="submit" value="Submit" class="btn btn-primary">
                    </div>
                </form>

            </div>
        </section>
    </main>
    <?php include_once APP_PATH . "/partials/footer.php" ?>
</body>

</html>