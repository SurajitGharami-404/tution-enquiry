<?php
require_once "../includes/init.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $sanitized_post = sanitize_post($_POST);
    $csrf_token = $sanitized_post["csrf_token"];
    $username = $sanitized_post["username"];
    $email = filter_var($sanitized_post["email"], FILTER_SANITIZE_EMAIL);
    $password = $sanitized_post["password"];
    $confirm_password = $sanitized_post["confirm_password"];

    $errors = [];

    if (!csrf_validate($csrf_token)) {
        $errors[] = "Invalid request.";
    }
    
    if (empty($username)) {
        $errors[] = "Username is required.";
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email id";
    }

    if ($password !== $confirm_password) {
        $errors[] = "Password and Confirm Password is differenet";
    }

    if (!empty($errors)) {
        $_SESSION["errors"] = $errors;
        header("Location: " . $_SERVER['REQUEST_URI']);
        exit;
    }

    // Check if email already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $_SESSION["errors"][] = "Email is already registered.";
        $stmt->close();
        $conn->close();
        header("Location: " . $_SERVER['REQUEST_URI']);
        exit;
    }
    $stmt->close();

    // Hash the password
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

    // Insert user into database
    $stmt = $conn->prepare("INSERT INTO users (user_name, email, password) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $username, $email, $hashedPassword);

    if ($stmt->execute()) {
        $_SESSION["success"] = "Registration successful!";
        $stmt->close();
        $conn->close();
        header("Location: " . APP_URL . "/auth/login.php");
        exit;
    } else {
        $_SESSION["errors"][] = "Database error: " . $stmt->error;
        $stmt->close();
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
    $page_title = "Tute.io - Register page";
    include_once APP_PATH . "/partials/header.php";
    ?>
    <style>
        .auth-form {
            width: 100%;
            margin-inline: var(--spacing-sm);
        }

        @media screen and (min-width:576px) {
            .auth-form {
                width: 554px;
            }
        }
    </style>
</head>

<body>

    <?php include_once APP_PATH . "/partials/alert.php" ?>
    <main class="d-flex align-items-center justify-content-center min-vh-100">
        <form class="card shadow auth-form" method="post" action="<?= APP_URL ?>/auth/register.php">
            <?= csrf_input() ?>
            <div class="card-header d-flex align-items-center justify-content-center"><a href="/" class="nav-brand">Tute<span>.</span>io</a></div>
            <div class="card-body">
                <h1 class="h5 mb-3 fw-normal">Register</h1>
                <div class="form-floating mb-3"> <input type="text" name="username" class="form-control" id="floatingInput" placeholder="Jhon Doe" required> <label for="floatingInput">User name</label> </div>
                <div class="form-floating mb-3"> <input type="email" name="email" class="form-control" id="floatingEmail" placeholder="name@example.com" required> <label for="floatingEmail">Email address</label> </div>
                <div class="form-floating mb-3"> <input type="password" name="password" minlength="6" class="form-control" id="floatingPassword" placeholder="Password" required> <label for="floatingPassword">Password</label> </div>
                <div class="form-floating mb-3"> <input type="password" name="confirm_password" minlength="6" class="form-control" id="floatingPassword" placeholder="Password" required> <label for="floatingPassword">Confirm password</label> </div>
                <button class="btn btn-primary w-100 py-2" type="submit">Register</button>
            </div>
            <div class="px-3">
                <p class="text-center">Already have an account? <a href="<?= APP_URL ?>/auth/login.php">Log in</a></p>
            </div>
        </form>
    </main>
    <?php include_once APP_PATH . "/partials/footer.php" ?>
</body>

</html>