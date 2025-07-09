<?php
require_once "./includes/init.php";
require_once "./includes/auth-check.php";

$id = isset($_GET["id"]) ? $_GET["id"] : 0;
$sanitized_id = filter_var(sanitize($id), FILTER_SANITIZE_NUMBER_INT);

if ($sanitized_id <= 0) {
    header("Location: " . APP_URL . "/courses.php");
    exit;
}

//Check if course_id is present
$id_check_stmt = $conn->prepare("SELECT id FROM courses WHERE id=?");
$id_check_stmt->bind_param("i", $id);

if ($id_check_stmt->execute()) {
    $resut = $id_check_stmt->get_result();
    if ($resut->num_rows < 1) {
        $_SESSION["errors"][] = "Invalid course id.";
        header("Location: " . APP_URL . "/courses.php");
        exit;
    }
} else {
    $_SESSION["errors"][] = "Database error: " . $id_check_stmt->error;
    header("Location: " . APP_URL . "/courses.php");
    exit;
}
$id_check_stmt->close();
// Get course details
$stmt = $conn->prepare("SELECT * from courses WHERE id=? LIMIT 1");
$stmt->bind_param("d", $sanitized_id);

if (!$stmt->execute()) {
    $_SESSION["errors"][] = "Error: " . $stmt->error();
    header("Location: " . $_SERVER['REQUEST_URI']);
    exit;
}

$course = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Listen for POST method
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $sanitized_post = sanitize_post($_POST);

    $name = $sanitized_post["name"];
    $duration = $sanitized_post["duration"];
    $duration_unit = $sanitized_post["duration_unit"];
    $status = $sanitized_post["status"];
    $price = $sanitized_post["price"];
    $description = $sanitized_post["desc"];

    $allowed_statuses = ["active", "inactive"];
    $allowed_d_units = ["weeks", "months", "years"];
    $errors = [];

    $csrf_token = $sanitized_post["csrf_token"];
    if (!csrf_validate($csrf_token)) {
        $errors[] = "Invalid request.";
    }

    if (empty($name)) {
        $errors[] = "Course name is required.";
    }
    if (!empty($name) && strlen($name) < 3) {
        $errors[] = "Course name should have at least 3 characters.";
    }

    if (!in_array($duration_unit, $allowed_d_units, true)) {
        $errors[] = "Invalid duration unit.";
    }

    if (!is_numeric($duration) || floatval($duration) < 1) {
        $errors[] = "Invalid duration.";
    }

    if (!in_array($status, $allowed_statuses, true)) {
        $errors[] = "Invalid status.";
    }

    if (!is_numeric($price)) {
        $errors[] = "Invalid price format.";
    } elseif (floatval($price) < 1) {
        $errors[] = "Invalid price.";
    }

    if (empty($description)) {
        $errors[] = "Description is required.";
    }

    if (!empty($errors)) {
        $_SESSION["errors"] = $errors;
        header("Location: " . $_SERVER['REQUEST_URI']);
        exit;
    }

    $stmt = $conn->prepare("UPDATE courses SET name=?, duration=?, duration_unit=?, status=?, price=?, description=? WHERE id=?");
    $stmt->bind_param("sdssdsi", $name, $duration, $duration_unit, $status, $price, $description, $sanitized_id);

    if ($stmt->execute()) {
        $_SESSION["success"] = "Course updated successfully.";
    } else {
        $_SESSION["errors"][] = "Database error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();

    header("Location: " . $_SERVER['REQUEST_URI']);
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php
    $page_title = "Tute.io - edit course page";
    include_once APP_PATH . "/partials/header.php";
    ?>
</head>

<body>
    <?php
    $active_link = "course";
    include_once APP_PATH . "/partials/nav.php";
    ?>
    <?php include_once APP_PATH . "/partials/alert.php" ?>
    <main class="main-content container-lg">
        <!-- Breadcrumb -->
        <div role="navigation" style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= APP_URL ?>/">Home</a></li>
                <li class="breadcrumb-item"><a href="<?= APP_URL ?>/courses.php">Courses</a></li>
                <li class="breadcrumb-item active" aria-current="page">Edit</li>
            </ol>
        </div>
        <!-- Page Heading -->
        <div class="d-flex align-items-center justify-content-between" style="margin-bottom: var(--spacing-lg);">
            <h2 class="page-heading my-0">Course</h2>
        </div>
        <!-- Edit Course Form Section -->
        <section>
            <div class="card">
                <!-- Form Header -->
                <div class="card-header">
                    <h3 class="fs-6 fw-normal mb-0" style="color: var(--light-text-color)">Edit Course Form</h3>
                </div>
                <!-- Add Course Form -->
                <form action="<?= APP_URL ?>/edit-course.php?id=<?= $sanitized_id ?>" method="POST" class="card-body">
                    <?= csrf_input() ?>
                    <!-- Name Input -->
                    <div class="mb-3">
                        <label for="courseName" class="form-label">Course name <span class="text-danger">*</span></label>
                        <input type="text" minlength="3" class="form-control" id="courseName" name="name" placeholder="Enter name" value="<?= sanitize($course["name"]) ?>" required>
                    </div>
                    <!-- Duration Input -->
                    <div class="row g-3 mb-3">
                        <div class="col-md-6 col-12">
                            <label for="duration" class="form-label">Duration <span class="text-danger">*</span></label>
                            <input type="number" min="0" class="form-control" name="duration" id="duration" placeholder="Enter duration" value="<?= beautify_number(sanitize($course["duration"])) ?>" required>
                        </div>

                        <div class="col-md-6 col-12">
                            <label for="durationUnitSelect" class="form-label">Duration unit <span class="text-danger">*</span></label>
                            <select class="form-select" name="duration_unit" id="durationUnitSelect" required>
                                <option value="weeks" <?= $course["duration_unit"] === "weeks" ? "selected" : "" ?>>Weeks</option>
                                <option value="months" <?= $course["duration_unit"] === "months" ? "selected" : "" ?>>Months</option>
                                <option value="years" <?= $course["duration_unit"] === "years" ? "selected" : "" ?>>Years</option>
                            </select>
                        </div>

                    </div>
                    <!-- Status Input -->
                    <div class="row g-3 mb-3">
                        <!-- Fee input -->
                        <div class="col-md-6 col-12">
                            <label for="coursePrice" class="form-label">Price <span class="text-danger">*</span></label>
                            <input type="number" min="0" class="form-control" name="price" id="coursePrice" placeholder="Enter price" value="<?= beautify_number(sanitize($course["price"])) ?>" required>
                        </div>
                        <!-- Status Input -->
                        <div class="col-md-6 col-12">
                            <label for="courseStatusDropdownMenu" class="form-label">Status <span class="text-danger">*</span></label>
                            <select class="form-select" name="status" id="courseStatusDropdownMenu" required>
                                <option value="active" <?= $course["status"] === "active" ? "selected" : "" ?>>Active</option>
                                <option value="inactive" <?= $course["status"] === "inactive" ? "selected" : "" ?>>Inactive</option>
                            </select>
                        </div>
                    </div>
                    <!-- Description input -->
                    <div class="mb-3">
                        <label for="courseDesc" class="form-label">Description <span class="text-danger">*</span></label>
                        <textarea class="form-control" name="desc" id="courseDesc" rows="5" placeholder="Enter description" required><?= sanitize($course["description"]) ?></textarea>
                    </div>
                    <!-- Form Submit Button -->
                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn-custom primary align-items-center justify-content-center" style="font-size:var(--text-base); min-width:10rem; min-height:2.5rem;">Submit</button>
                    </div>
                </form>

            </div>
        </section>
    </main>
    <?php include_once APP_PATH . "/partials/footer.php" ?>
</body>

</html>