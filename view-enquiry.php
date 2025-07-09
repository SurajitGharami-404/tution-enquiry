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
    SELECT enquiries.*, courses.name AS course_name
    FROM enquiries
    LEFT JOIN courses ON enquiries.course_id = courses.id WHERE enquiries.id=?
");
$get_enquiry_stmt->bind_param("i", $id);

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

$get_enquiry_stmt->close();
$conn->close();
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
                <li class="breadcrumb-item active" aria-current="page">View</li>
            </ol>
        </div>
        <!-- Page Heading -->
        <div class="d-flex align-items-center justify-content-between" style="margin-bottom: var(--spacing-lg);">
            <h2 class="page-heading my-0">Enquiry</h2>
            <!-- Enquiry Action Buttons -->
            <div class="d-flex align-items-center">
                <a href="<?= APP_URL ?>/edit-enquiry.php?id=<?= $sanitized_id ?>" class="btn-custom primary me-2" style="gap: var(--spacing-sm); box-shadow: var(--shadow-sm)">
                    <i class="bi bi-pencil-square" style="font-size: var(--text-lg);"></i>
                    Edit
                </a>
                <a href="<?= APP_URL ?>/delete-enquiry.php?id=<?= $sanitized_id ?>" class="btn-custom danger" style="gap: var(--spacing-sm); box-shadow: var(--shadow-sm)">
                    <i class="bi bi-trash" style="font-size: var(--text-lg);"></i>
                    Delete
                </a>
            </div>
        </div>
        <!-- View Enquiry Details Section -->
        <section>
            <div class="card">
                <div class="card-header">
                    <h3 class="fs-6 fw-normal mb-0" style="color: var(--light-text-color)">Enquiry Details</h3>
                </div>
                <!-- Enquiry Details -->
                <div class="card-body">
                    <!-- Name Input -->
                    <div class="row g-3 mb-3">
                        <div class="col-md-6 col-12">
                            <p class="form-label">First name</p>
                            <p class="form-control mb-0"><?= sanitize($enquiry["first_name"]) ?></p>
                        </div>
                        <div class="col-md-6 col-12">
                            <p class="form-label">Last name</p>
                            <p class="form-control mb-0"><?= sanitize($enquiry["last_name"]) ?></p>
                        </div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6 col-12">
                            <p class="form-label">Course</p>
                            <p class="form-control mb-0"><?= sanitize($enquiry["course_name"]) ?></p>
                        </div>
                        <div class="col-md-6 col-12">
                            <p class="form-label">Contact No.</p>
                            <p class="form-control mb-0"><?= sanitize($enquiry["contact"]) ?></p>
                        </div>
                    </div>
                    <!-- Description input -->
                    <div class="mb-3">
                        <p class="form-label">Description</p>
                        <p class="form-control mb-0" style="height: 10rem; overflow-y:auto;"><?= sanitize($enquiry["description"]) ?></p>
                    </div>
                </div>

            </div>
        </section>
    </main>
    <?php include_once APP_PATH . "/partials/footer.php" ?>
</body>

</html>