<?php
require_once "./includes/init.php";
require_once "./includes/auth-check.php";

$id = isset($_GET["id"]) ? $_GET["id"] : 0;
$sanitized_id = filter_var(sanitize($id), FILTER_SANITIZE_NUMBER_INT);

if ($sanitized_id <= 0) {
    header("Location: " . APP_URL . "/courses.php");
    exit;
}

$get_course_stmt = $conn->prepare("SELECT * FROM courses WHERE id=?");
$get_course_stmt->bind_param("i", $id);

if (!$get_course_stmt->execute()) {
    $_SESSION["errors"][] = "Database error: " . $get_course_stmt->error;
    header("Location: " . APP_URL . "/courses.php");
    exit;
}
$get_course_results = $get_course_stmt->get_result();

if ($get_course_results->num_rows < 1) {
    $_SESSION["errors"][] = "Invalid enquiry id.";
    header("Location: " . APP_URL . "/courses.php");
    exit;
}

$course = $get_course_results->fetch_assoc();

$get_course_stmt->close();
$conn->close();

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php
    $page_title = "Tute.io - Course details page";
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
                <li class="breadcrumb-item active" aria-current="page">View</li>
            </ol>
        </div>
        <!-- Page Heading -->
        <div class="d-flex align-items-center justify-content-between" style="margin-bottom: var(--spacing-lg);">
            <h2 class="page-heading my-0">Course</h2>
            <!-- Course Action Buttons -->
            <div class="d-flex align-items-center">
                <a href="<?= APP_URL ?>/edit-course.php?id=<?= $sanitized_id ?>" class="btn-custom primary me-2" style="gap: var(--spacing-sm); box-shadow: var(--shadow-sm)">
                    <i class="bi bi-pencil-square" style="font-size: var(--text-lg);"></i>
                    Edit
                </a>
                <a href="<?= APP_URL ?>/delete-course.php?id=<?= $sanitized_id ?>" class="btn-custom danger" style="gap: var(--spacing-sm); box-shadow: var(--shadow-sm)">
                    <i class="bi bi-trash" style="font-size: var(--text-lg);"></i>
                    Delete
                </a>
            </div>
        </div>
        <!-- View Course Details Section -->
        <section>
            <div class="card">
                <!-- Form Header -->
                <div class="card-header">
                    <h3 class="fs-6 fw-normal mb-0" style="color: var(--light-text-color)">Course Details</h3>
                </div>
                <!-- Course Details -->
                <div class="card-body">

                    <!-- Name -->
                    <div class="mb-3">
                        <p class="text-secondary mb-2">Course name</p>
                        <p class="form-control mb-0"><?= sanitize($course["name"]) ?></p>
                    </div>
                    <!-- Duration Input -->
                    <div class="row g-3 mb-3">
                        <div class="col-md-6 col-12">
                            <p class="text-secondary mb-2">Duration</p>
                            <p class="form-control mb-0">
                                <?= beautify_number(sanitize($course["duration"])) ?>
                            </p>
                        </div>

                        <div class="col-md-6 col-12">
                            <p class="text-secondary mb-2">Duration unit</p>
                            <p class="form-control mb-0"><?= ucfirst(sanitize($course["duration_unit"])) ?></p>
                        </div>

                    </div>
                    <!-- Status -->
                    <div class="row g-3 mb-3">
                        <!-- Price -->
                        <div class="col-md-6 col-12">
                            <p class="text-secondary mb-2">Price</p>
                            <p class="form-control mb-0">
                                <?= beautify_number(sanitize($course["price"])) ?></p>
                        </div>
                        <!-- Status Input -->
                        <div class="col-md-6 col-12">
                            <p class="text-secondary mb-2">Status</p>
                            <p class="form-control mb-0"><?= ucfirst(sanitize($course["status"])) ?></p>
                        </div>
                    </div>
                    <!-- Description input -->
                    <div class="mb-3">
                        <p class="text-secondary mb-2">Description</p>
                        <p class="form-control mb-0" style="height: 8rem; overflow-y:auto;"><?= sanitize($course["description"]) ?></p>
                    </div>
                </div>

            </div>
        </section>
    </main>
    <?php include_once APP_PATH . "/partials/footer.php" ?>
</body>

</html>