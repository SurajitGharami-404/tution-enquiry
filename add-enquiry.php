<?php
require_once "./includes/init.php";
require_once "./includes/auth-check.php";   

$get_courses_stmt = $conn->prepare("SELECT * FROM courses WHERE 1");

if ($get_courses_stmt->execute()) {
    $courses_result = $get_courses_stmt->get_result();
    $courses = $courses_result->fetch_all(MYSQLI_ASSOC);
    $get_courses_stmt->close();
} else {
    $_SERVER["errors"][] = "Database Error: " . $get_courses_stmt->error();
    $get_courses_stmt->close();
    header("Location: " . $_SERVER['REQUEST_URI']);
    exit;
}


if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $sanitized_post = sanitize_post($_POST);
    $firstname = $sanitized_post["firstname"];
    $lastname = $sanitized_post["lastname"];
    $course_id = $sanitized_post["course_id"];
    $contact = $sanitized_post["contact"];
    $description = $sanitized_post["desc"];
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

    if (!empty($errors)) {
        $_SESSION["errors"] = $errors;
        header("Location: " . $_SERVER['REQUEST_URI']);
        exit;
    }

    $create_enquiry_stmt = $conn->prepare("INSERT INTO enquiries(first_name, last_name, contact, description, course_id) VALUES(?,?,?,?,?)");
    $create_enquiry_stmt->bind_param("ssssi", $firstname, $lastname, $contact, $description, $course_id);

    if ($create_enquiry_stmt->execute()) {
        $_SESSION["success"] = "Enquiry created successfully.";
        $create_enquiry_stmt->close();
        $conn->close();
        header("Location: " . $_SERVER['REQUEST_URI']);
        exit;
    } else {
        $_SESSION["errors"][] = "Database error: " . $create_enquiry_stmt->error;
        $create_enquiry_stmt->close();
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
    $page_title = "Tute.io - Home";
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
                <li class="breadcrumb-item"><a href="/<?= APP_URL ?>">Home</a></li>
                <li class="breadcrumb-item"><a href="<?= APP_URL ?>/enquiries.php">Enquiries</a></li>
                <li class="breadcrumb-item active" aria-current="page">Add</li>
            </ol>
        </div>
        <div class="d-flex align-items-center justify-content-between" style="margin-bottom: var(--spacing-lg);">
            <h2 class="page-heading my-0">Enquiry</h2>
        </div>
        <section>
            <div class="card">
                <!-- Form Header -->
                <div class="card-header">
                    <h3 class="fs-6 fw-normal mb-0" style="color: var(--light-text-color)">Add Enquiry Form</h3>
                </div>
                <!-- Add Course Form -->
                <form action="<?= APP_URL ?>/add-enquiry.php" method="POST" class="card-body">
                    <?= csrf_input() ?>
                    <!-- Name Input -->
                    <div class="row g-3 mb-3">
                        <div class="col-md-6 col-12">
                            <label for="firstname" class="form-label">First name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="firstname" name="firstname" placeholder="Enter first name" required>
                        </div>
                        <div class="col-md-6 col-12">
                            <label for="lastname" class="form-label">Last name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="lastname" name="lastname" placeholder="Enter last name" required>
                        </div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6 col-12">
                            <label for="courseDropdownMenu" class="form-label">Course <span class="text-danger">*</span></label>
                            <select class="form-select" aria-label="active" name="course_id">
                                <?php if ($courses_result->num_rows < 1): ?>
                                    <option value="">Add course</option>
                                <?php endif ?>
                                <?php if ($courses_result->num_rows > 0): ?>
                                    <?php foreach ($courses as $course): ?>
                                        <option value="<?= sanitize($course["id"]) ?>"><?= sanitize($course["name"]) ?></option>
                                    <?php endforeach ?>
                                <?php endif ?>
                            </select>
                        </div>
                        <div class="col-md-6 col-12">
                            <label for="contact" class="form-label">Contact No.<span class="text-danger">*</span></label>
                            <input type="tel" id="contact" name="contact" class="form-control" placeholder="Enter contact" required>
                        </div>
                    </div>
                    <!-- Description input -->
                    <div class="mb-3">
                        <label for="desc" class="form-label">Description <span class="text-danger">*</span></label>
                        <textarea class="form-control" name="desc" id="desc" rows="5" placeholder="Enter description"></textarea>
                    </div>
                    <!-- Form Submit Button -->
                    <div class="d-flex justify-content-end">
                        <input type="submit" value="Add Enquiry" class="btn btn-primary">
                    </div>
                </form>

            </div>
        </section>
    </main>
    <?php include_once APP_PATH . "/partials/footer.php" ?>
</body>

</html>