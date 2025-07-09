<?php
require_once "./includes/init.php";
require_once "./includes/auth-check.php";

//GET total courses count
$get_courses_count_stmt = $conn->prepare("SELECT COUNT(*) AS total FROM courses");

if (!$get_courses_count_stmt->execute()) {
    $_SERVER["errors"][] = "Database Error: " . $get_courses_count_stmt->error();
    $get_courses_count_stmt->close();
    $conn->close();
    header("Location: " . $_SERVER['REQUEST_URI']);
    exit;
}

$get_courses_count_result = $get_courses_count_stmt->get_result();
$courses_count_row = $get_courses_count_result->fetch_assoc();
$courses_count = $courses_count_row["total"];
$get_courses_count_stmt->close();

//GET current month total enquiries count
$get_enquiries_count_stmt = $conn->prepare(
    "SELECT COUNT(*) AS total 
    FROM enquiries 
    WHERE created_at >= NOW() - INTERVAL 30 DAY"
);

if (!$get_enquiries_count_stmt->execute()) {
    $_SERVER["errors"][] = "Database Error: " . $get_enquiries_count_stmt->error();
    $get_enquiries_count_stmt->close();
    $conn->close();
    header("Location: " . $_SERVER['REQUEST_URI']);
    exit;
}

$get_enquiries_count_result = $get_enquiries_count_stmt->get_result();
$enquiries_count_row = $get_enquiries_count_result->fetch_assoc();
$enquiries_count = $enquiries_count_row["total"];
$get_enquiries_count_stmt->close();

//GET current month total enquiries count with pending status
$get_pending_enquiries_count_stmt = $conn->prepare(
    "SELECT COUNT(*) AS total 
    FROM enquiries 
    WHERE created_at >= NOW() - INTERVAL 30 DAY
    AND status='pending'"
);

if (!$get_pending_enquiries_count_stmt->execute()) {
    $_SERVER["errors"][] = "Database Error: " . $get_pending_enquiries_count_stmt->error();
    $get_pending_enquiries_count_stmt->close();
    $conn->close();
    header("Location: " . $_SERVER['REQUEST_URI']);
    exit;
}

$get_pending_enquiries_count_result = $get_pending_enquiries_count_stmt->get_result();
$pending_enquiries_count_row = $get_pending_enquiries_count_result->fetch_assoc();
$pending_enquiries_count = $pending_enquiries_count_row["total"];
$get_pending_enquiries_count_stmt->close();

//GET current month total enquiries count with confirmed status
$get_confirmed_enquiries_count_stmt = $conn->prepare(
    "SELECT COUNT(*) AS total 
    FROM enquiries 
    WHERE created_at >= NOW() - INTERVAL 30 DAY
    AND status='confirmed'"
);

if (!$get_confirmed_enquiries_count_stmt->execute()) {
    $_SERVER["errors"][] = "Database Error: " . $get_confirmed_enquiries_count_stmt->error();
    $get_confirmed_enquiries_count_stmt->close();
    $conn->close();
    header("Location: " . $_SERVER['REQUEST_URI']);
    exit;
}

$get_confirmed_enquiries_count_result = $get_confirmed_enquiries_count_stmt->get_result();
$confirmed_enquiries_count_row = $get_confirmed_enquiries_count_result->fetch_assoc();
$confirmed_enquiries_count = $confirmed_enquiries_count_row["total"];
$get_confirmed_enquiries_count_stmt->close();

// GET enquiries from DB
$get_enquiries_stmt = $conn->prepare(
    "SELECT enquiries.*, courses.name AS course_name
        FROM enquiries
        LEFT JOIN courses ON enquiries.course_id = courses.id WHERE 1=1 
        ORDER BY enquiries.created_at DESC 
        LIMIT 10"
);


if (!$get_enquiries_stmt->execute()) {
    $_SERVER["errors"][] = "Database Error: " . $get_enquiries_stmt->error();
    $get_enquiries_stmt->close();
    $conn->close();
    header("Location: " . $_SERVER['REQUEST_URI']);
    exit;
}
$enquiries_result = $get_enquiries_stmt->get_result();
$enquiries = $enquiries_result->fetch_all(MYSQLI_ASSOC);
$get_enquiries_stmt->close();


?>

<!DOCTYPE html>
<html lang="en">
<?php
$page_title = "Tute.io - Home";
include_once APP_PATH . "/partials/header.php";
?>

<body>
    <?php include_once APP_PATH . "/partials/alert.php" ?>
    <?php
    $active_link = "home";
    include_once APP_PATH . "/partials/nav.php";
    ?>
    <main class="main-content container-lg">
        <h2 class="page-heading">Dashboard</h2>
        <!-- KPI Section-->
        <section class="row" style="margin-bottom: var(--spacing-lg);">
            <div class="col-xl-3 col-lg-4 col-sm-6 col-12">
                <div class="card-custom">
                    <div class="card-custom-body kpi">
                        <div class="kpi-icon" style="--kpi-icon-color: #687FE5;"><i class="bi bi-journal-check"></i></div>
                        <div class="kpi-info">
                            <h4 class="kpi-data"><?= number_format(sanitize($courses_count)) ?></h4>
                            <p class="kpi-label" style="font-size: var(--text-sm);">Total Courses</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-lg-4 col-sm-6 col-12">
                <div class="card-custom">
                    <div class="card-custom-body kpi">
                        <div class="kpi-icon" style="--kpi-icon-color: #60B5FF;"><i class="bi bi-question-diamond"></i></div>
                        <div class="kpi-info">
                            <h4 class="kpi-data"><?= number_format(sanitize($enquiries_count)) ?></h4>
                            <p class="kpi-label" style="font-size: var(--text-sm);">Enquiries <span class="text-primary">(30 days)</span></p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-lg-4 col-sm-6 col-12">
                <div class="card-custom">
                    <div class="card-custom-body kpi">
                        <div class="kpi-icon" style="--kpi-icon-color: #F75A5A;"><i class="bi bi-hourglass-split"></i></div>
                        <div class="kpi-info">
                            <h4 class="kpi-data"><?= number_format(sanitize($pending_enquiries_count)) ?></h4>
                            <p class="kpi-label" style="font-size: var(--text-sm);">Pending <span class="text-danger">(30 days)</span></p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-lg-4 col-sm-6 col-12">
                <div class="card-custom">
                    <div class="card-custom-body kpi">
                        <div class="kpi-icon" style="--kpi-icon-color: #1DCD9F;"><i class="bi bi-check-square"></i></div>
                        <div class="kpi-info">
                            <h4 class="kpi-data"><?= number_format(sanitize($confirmed_enquiries_count)) ?></h4>
                            <p class="kpi-label" style="font-size: var(--text-sm);">Confirmed <span class="text-success">(30 days)</span></p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- Recent Enquiries Table Section -->
        <section>
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title fs-6 fw-medium mb-0">
                        Recent Enquiries
                    </h5>
                </div>
                <div class="table-responsive" style="min-height: 50vh;">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th class="fw-medium" style="border-right: 1px solid var(--border-color);">Name</th>
                                <th class="fw-medium" style="border-right: 1px solid var(--border-color);">Course</th>
                                <th class="fw-medium" style="border-right: 1px solid var(--border-color);">Contact</th>
                                <th class="fw-medium" style="border-right: 1px solid var(--border-color);">Status</th>
                                <th class="fw-medium">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($enquiries_result) || $enquiries_result->num_rows < 1): ?>
                                <tr>
                                    <td colspan="5" class="text-center">0 results showing</td>
                                </tr>
                            <?php endif ?>
                            <?php if (!empty($enquiries_result) && $enquiries_result->num_rows > 0): ?>
                                <?php foreach ($enquiries as $enquiry): ?>
                                    <tr>
                                        <td>
                                            <?= ucfirst(sanitize($enquiry["first_name"])) . " " . ucfirst(sanitize($enquiry["last_name"])) ?>
                                        </td>

                                        <td>
                                            <?= sanitize($enquiry["course_name"]) ?>
                                        </td>

                                        <td>
                                            <a href="tel:<?= sanitize($enquiry["contact"]) ?>"><?= sanitize($enquiry["contact"]) ?></a>
                                        </td>
                                        <td>
                                            <?php
                                            $status = sanitize($enquiry["status"]);

                                            if ($status === 'pending') {
                                                echo "<span class='badge-custom warning'>" . ucfirst($status) . "</span>";
                                            } else if ($status === 'confirmed') {
                                                echo "<span class='badge-custom success'>" . ucfirst($status) . "</span>";
                                            } else {
                                                echo "<span class='badge-custom danger'>" . ucfirst($status) . "</span>";
                                            }
                                            ?>

                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <a href="<?= APP_URL ?>/view-enquiry.php?id=<?= sanitize($enquiry["id"]) ?>" class="btn-custom primary me-2"><i class="bi bi-eye"></i></a>
                                                <a href="<?= APP_URL ?>/edit-enquiry.php?id=<?= sanitize($enquiry["id"]) ?>" class="btn-custom success"><i class="bi bi-pencil-square"></i></a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach ?>
                            <?php endif ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </main>
    <?php include_once APP_PATH . "/partials/footer.php" ?>
</body>

</html>