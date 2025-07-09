<?php
require_once "./includes/init.php";
require_once "./includes/auth-check.php";

$name_filter = isset($_GET['name']) ? trim($_GET['name']) : '';
$status_filter = isset($_GET['status']) ? trim($_GET['status']) : '';

$name_filter = sanitize($name_filter);
$status_filter = sanitize($status_filter);

$sql = "SELECT * FROM courses WHERE 1";
$params = [];
$types = "";

if (!empty($name_filter)) {
    $sql .= " AND name LIKE ?";
    $params[] = "%" . $name_filter . "%";
    $types .= "s";
}

if (!empty($status_filter)) {
    $sql .= " AND status = ?";
    $params[] = $status_filter;
    $types .= "s";
}

$stmt = $conn->prepare($sql);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$courses_result = $stmt->get_result();

$courses = $courses_result->fetch_all(MYSQLI_ASSOC);

$stmt->close();
$conn->close();
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <?php
    $page_title = "Tute.io - Courses page";
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
                <li class="breadcrumb-item active" aria-current="page">Courses</li>
            </ol>
        </div>
        <!-- Page Heading  -->
        <div class="d-flex align-items-center justify-content-between" style="margin-bottom: var(--spacing-lg);">
            <h2 class="page-heading my-0">Course</h2>
            <!-- Add Courses Button -->
            <a href="<?= APP_URL ?>/add-course.php" class="btn-custom primary align-items-center justify-content-center" style="gap: var(--spacing-sm); box-shadow: var(--shadow-sm);">
                <i class="bi bi-plus-lg" style="font-size: var(--text-lg);"></i>
                Add Course
            </a>
        </div>
        <!-- Table Section  -->
        <section>
            <div class="card">
                <!-- Search Form -->
                <div class="card-header">
                    <form action="<?= APP_URL ?>/courses.php" method="GET" class="row g-3 align-items-stretch">
                        <!-- Course Name Input -->
                        <div class="col-lg-5 col-md-6 col-12">
                            <input type="text" id="name" name="name" class="form-control" placeholder="Search Name" value="<?= !empty($name_filter) ? sanitize($name_filter) : '' ?>">
                        </div>

                        <!-- Status Input with Dropdown -->
                        <div class="col-lg-5 col-md-6 col-12">
                            <select class="form-select" name="status" value="<?= !empty($status_filter) ? $status_filter : '' ?>" id="courseStatusDropdownMenu">
                                <option value="" <?= empty($status_filter) ? "selected" : "" ?>>Select Status</option>
                                <option value="active" <?= !empty($status_filter) && $status_filter === 'active' ? "selected" : "" ?>>Active</option>
                                <option value="inactive" <?= !empty($status_filter) && $status_filter === 'inactive' ? "selected" : "" ?>>Inactive</option>
                            </select>
                        </div>

                        <!-- Submit Button -->
                        <div class="col-lg-2 col-md-12 col-12">
                            <input type="submit" value="Search" class="btn btn-secondary h-100 w-100">
                        </div>
                    </form>
                </div>

                <!-- Courses Table -->
                <div class="card-body p-0">
                    <div class="table-responsive" style="min-height: 50vh;">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th class="fw-medium" style="border-right: 1px solid var(--border-color);">Course Name</th>
                                    <th class="fw-medium" style="border-right: 1px solid var(--border-color);">Duration</th>
                                    <th class="fw-medium" style="border-right: 1px solid var(--border-color);">Price</th>
                                    <th class="fw-medium" style="border-right: 1px solid var(--border-color);">Status</th>
                                    <th class="fw-medium">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($courses_result->num_rows < 1): ?>
                                    <tr>
                                        <td colspan="5" class="text-center">0 results showing</td>
                                    </tr>
                                <?php endif ?>
                                <?php if ($courses_result->num_rows > 0): ?>
                                    <?php foreach ($courses as $course): ?>
                                        <tr>
                                            <td>
                                                <?= sanitize($course["name"]) ?>
                                            </td>
                                            <td>
                                                <?php
                                                $duration = sanitize($course["duration"]);
                                                $unit = ucfirst(sanitize($course["duration_unit"]));

                                                if ($duration % 1 != 0) {
                                                    echo number_format($duration, 2) . " " . $unit;
                                                } else {
                                                    echo intval($duration) . " " . $unit;
                                                }
                                                ?>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <i class="bi bi-currency-rupee"></i>
                                                    <p class="my-0">
                                                        <?php
                                                        $duration = sanitize($course["price"]);

                                                        if ($duration % 1 != 0) {
                                                            echo number_format($duration, 2);
                                                        } else {
                                                            echo intval($duration);
                                                        }
                                                        ?></p>
                                                </div>
                                            </td>
                                            <td>
                                                <?php
                                                $status = sanitize($course["status"]);

                                                if ($status === 'active') {
                                                    echo "<span class='badge-custom success'>" . ucfirst($status) . "</span>";
                                                } else {
                                                    echo "<span class='badge-custom danger'>" . ucfirst($status) . "</span>";
                                                }
                                                ?>

                                            </td>

                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <a href="<?= APP_URL ?>/view-course.php?id=<?= sanitize($course["id"]) ?>" class="btn-custom primary me-2"><i class="bi bi-eye"></i></a>
                                                    <a href="<?= APP_URL ?>/edit-course.php?id=<?= sanitize($course["id"]) ?>" class="btn-custom success"><i class="bi bi-pencil-square"></i></a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach ?>
                                <?php endif ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>
    </main>
    <?php include_once APP_PATH . "/partials/footer.php" ?>
</body>

</html>