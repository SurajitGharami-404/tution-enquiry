<?php
require_once "./includes/init.php";
require_once "./includes/auth-check.php";

$name_filter = isset($_GET['name']) ? trim($_GET['name']) : '';
$status_filter = isset($_GET['status']) ? trim($_GET['status']) : '';

$name_filter = sanitize($name_filter);
$status_filter = sanitize($status_filter);

$sql = "SELECT enquiries.*, courses.name AS course_name
        FROM enquiries
        LEFT JOIN courses ON enquiries.course_id = courses.id WHERE 1";
$params = [];
$types = "";

if (!empty($name_filter)) {
    $sql .= " AND (enquiries.first_name LIKE ? OR enquiries.last_name LIKE ?)";
    $params[] = "%" . $name_filter . "%";
    $params[] = "%" . $name_filter . "%";
    $types .= "ss";
}

if (!empty($status_filter)) {
    $sql .= " AND enquiries.status = ?";
    $params[] = $status_filter;
    $types .= "s";
}

$get_enquiries_stmt = $conn->prepare($sql);

if (!empty($params)) {
    $get_enquiries_stmt->bind_param($types, ...$params);
}

if ($get_enquiries_stmt->execute()) {
    $enquiries_result = $get_enquiries_stmt->get_result();
    $enquiries = $enquiries_result->fetch_all(MYSQLI_ASSOC);
    $get_enquiries_stmt->close();
    $conn->close();
} else {
    $_SERVER["errors"][] = "Database Error: " . $get_enquiries_stmt->error();
    $get_enquiries_stmt->close();
    $conn->close();
    header("Location: " . $_SERVER['REQUEST_URI']);
    exit;
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
                <li class="breadcrumb-item"><a href="<?= APP_URL ?>/">Home</a></li>
                <li class="breadcrumb-item active" aria-current="page">Enquiries</li>
            </ol>
        </div>
        <!-- Page Heading  -->
        <div class="d-flex align-items-center justify-content-between" style="margin-bottom: var(--spacing-lg);">
            <h2 class="page-heading my-0">Enquiry</h2>
            <!-- Add Courses Button -->
            <a href="<?= APP_URL ?>/add-enquiry.php" class="btn-custom" style="background-color: var(--primary-color); color:var(--surface-color); gap: var(--spacing-sm); box-shadow: var(--shadow-sm)">
                <i class="bi bi-plus-lg" style="font-size: var(--text-lg);"></i>
                Add Enquiry
            </a>
        </div>
        <!-- Table Section  -->
        <section>
            <div class="card" >
                <!-- Search Form -->
                <div class="card-header">
                    <form action="<?= APP_URL ?>/enquiries.php" method="GET" class="row g-3 align-items-stretch">
                        <!-- Course Name Input -->
                        <div class="col-lg-5 col-md-6 col-12">
                            <input type="text" id="name" name="name" class="form-control text-capitalize" placeholder="Search name" value="<?= !empty($name_filter) ? sanitize($name_filter) : '' ?>">
                        </div>
                        <!-- Status Input with Dropdown -->
                        <div class="col-lg-5 col-md-6 col-12">
                            <select name="status" id="status" class="form-select">
                                <option value="" <?= empty($status_filter) ? "selected" : "" ?>>Select Status</option>
                                <option value="pending" <?= !empty($status_filter) && $status_filter === 'pending' ? "selected" : "" ?>>Pending</option>
                                <option value="confirmed" <?= !empty($status_filter) && $status_filter === 'confirmed' ? "selected" : "" ?>>Confirmed</option>
                                <option value="cancelled" <?= !empty($status_filter) && $status_filter === 'cancelled' ? "selected" : "" ?>>Cancelled</option>
                            </select>
                        </div>

                        <!-- Submit Button -->
                        <div class="col-lg-2 col-md-12 col-12">
                            <input type="submit" value="Search" class="btn btn-secondary h-100 w-100">
                        </div>
                    </form>

                </div>
                <!-- Courses Table -->
                <div class="table-responsive" style="min-height: 50vh;">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th class="fw-medium" style="border-right: 1px solid var(--border-color);">Full Name</th>
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
    <script>
        $('#statusDropdownMenu').click((evt) => {
            const $target = $(evt.target);
            const value = $target.attr("data-value") ?? "";
            $("#statusInput").val(value);
            if (value === "") {
                $("#statusDropdownBtnText").text("Search course status");
            } else {
                $("#statusDropdownBtnText").text(value.replace("_", " "));
            }
        });
    </script>
</body>

</html>