<?php
require_once "../includes/init.php";
// CSRF Token
csrf_token();

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $sanitized_post = sanitize_post($_POST);
    $csrf_token = $sanitized_post["csrf_token"];
    $email = sanitize($_SESSION["user"]["email"]);
    $old_password = $sanitized_post["old_password"];
    $new_password = $sanitized_post["new_password"];
    $new_confirm_password = $sanitized_post["new_confirm_password"];

    $errors = [];

    if (!csrf_validate($csrf_token)) {
        $errors[] = "Invalid request.";
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email id";
    }

    if (empty($old_password)) {
        $errors[] = "Old password is required.";
    }

    if (empty($new_password)) {
        $errors[] = "New password is required.";
    }

    if (empty($new_confirm_password)) {
        $errors[] = "New confirm password is required.";
    }

    if (strlen($new_password) < 3) {
        $errors[] = "New password length should be atleast 3 characters.";
    }

    if ($new_password !== $new_confirm_password) {
        $errors[] = "New password and Confirm password is different.";
    }


    if (!empty($errors)) {
        $_SESSION["errors"] = $errors;
        header("Location: " . $_SERVER['REQUEST_URI']);
        exit;
    }

    $get_user_stmt = $conn->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
    $get_user_stmt->bind_param("s", $email);

    if (!$get_user_stmt->execute()) {
        $_SESSION["errors"] = "Database error: " . $get_user_stmt->error();
        header("Location: " . $_SERVER['REQUEST_URI']);
        exit;
    }

    $get_user_result = $get_user_stmt->get_result();
    $user = $get_user_result->fetch_assoc();
    $get_user_stmt->close();

    $user_id = $user["id"];
    $user_password = $user["password"];
    $new_password = password_hash($new_password, PASSWORD_BCRYPT);

    if (!password_verify($old_password, $user_password)) {
        $_SESSION["errors"][] = "Wrong old password.";
        header("Location: " . $_SERVER['REQUEST_URI']);
        exit;
    }

    $update_password_stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
    $update_password_stmt->bind_param("si", $new_password, $user_id);

    if ($update_password_stmt->execute()) {
        $_SESSION["success"] = "Password changed successfully.";
    } else {
        $_SESSION["errors"] = "Database error: " . $update_password_stmt->error();
    }

    $update_password_stmt->close();
    $conn->close();
    header("Location: " . $_SERVER['REQUEST_URI']);
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php
    $page_title = "Tute.io - User profile";
    include_once APP_PATH . "/partials/header.php";
    ?>
</head>

<body>
    <?php
    $active_link = "home";
    include_once APP_PATH . "/partials/nav.php";
    ?>
    <?php include_once APP_PATH . "/partials/alert.php"; ?>
    <main class="main-content container-lg">
        <h2 class="page-heading">User Profile</h2>
        <!-- Profile details -->
        <div class="card mb-5">
            <div class="card-header">
                <p class="card-title">
                    User details
                </p>
            </div>
            <div class="card-body px-0 pt-0">
                <div class="table-responsive">
                    <table class="table">
                        <tbody>
                            <tr class="px-2">
                                <td class="border-end">Username</td>
                                <td><?= sanitize($_SESSION["user"]["username"]) ?></td>
                            </tr>
                            <tr class="px-2">
                                <td class="border-end">Email</td>
                                <td><?= sanitize($_SESSION["user"]["email"]) ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <!-- Change password -->
        <div class="card">
            <div class="card-header">
                <p class="card-title">Change password</p>
            </div>
            <div class="card-body">
                <form action="<?= APP_URL ?>/user/profile.php" method="post">
                    <?= csrf_input() ?>
                    <div class="row mb-3">
                        <div class="col-md-4 col-12 mb-3">
                            <label for="oldPassword" class="form-label">Old password <span class="text-danger">*</span></label>
                            <input class="form-control" type="password" name="old_password" id="oldPassword" required>
                        </div>
                        <div class="col-md-4 col-12 mb-3">
                            <label for="newPassword" class="form-label">New password <span class="text-danger">*</span></label>
                            <input class="form-control" type="password" name="new_password" id="newPassword" required>
                        </div>
                        <div class="col-md-4 col-12">
                            <label for="newConfirmPassword" class="form-label">New confirm password <span class="text-danger">*</span></label>
                            <input class="form-control" type="password" name="new_confirm_password" id="newConfirmPassword" required>
                        </div>
                    </div>
                    <div class="d-flex align-items-center justify-content-end">
                        <button type="submit" class="btn-custom primary">Change password</button>
                    </div>
                </form>
            </div>
        </div>
    </main>
    <?php include_once APP_PATH . "/partials/footer.php" ?>
</body>

</html>