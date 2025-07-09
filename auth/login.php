<?php
require_once "../includes/init.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $sanitized_post = sanitize_post($_POST);
    $csrf_token = $sanitized_post["csrf_token"];
    $email = filter_var($sanitized_post["email"], FILTER_SANITIZE_EMAIL);
    $password = $sanitized_post["password"];

    $errors = [];

    if (!csrf_validate($csrf_token)) {
        $errors[] = "Invalid request.";
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email id";
    }

    if (empty($password)) {
        $errors[] = "Invalid password";
    }

    if (!empty($errors)) {
        $_SESSION["errors"] = $errors;
        header("Location: " . $_SERVER['REQUEST_URI']);
        exit;
    }

    // Prepare statement to get user by email
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        $_SESSION["errors"][] = "User not found: $email";
        $stmt->close();
        $conn->close();
        header("Location: " . $_SERVER['REQUEST_URI']);
        exit;
    }

    $userData = $result->fetch_assoc();

    if (!password_verify($password, $userData["password"])) {
        $_SESSION["errors"][] = "Wrong password";
        $stmt->close();
        $conn->close();
        header("Location: " . $_SERVER['REQUEST_URI']);
        exit;
    }

    // Login successful
    $_SESSION["user"] = [
        "username" => $userData["user_name"],
        "email" => $userData["email"]
    ];
    $_SESSION["success"] = "Log in successful!";

    $stmt->close();
    $conn->close();

    header("Location: " . APP_URL . "/");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php
    $page_title = "Tute.io - Log in page";
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
        <form class="card shadow auth-form" method="post" action="<?= APP_URL ?>/auth/login.php">
            <?= csrf_input() ?>
            <div class="card-header d-flex align-items-center justify-content-center"><a href="/" class="nav-brand">Tute<span>.</span>io</a></div>
            <div class="card-body">
                <h1 class="h5 mb-3 fw-normal">Log in</h1>
                <div class="form-floating mb-3"> <input type="email" name="email" class="form-control" id="floatingInput" placeholder="name@example.com"> <label for="floatingInput">Email address</label> </div>
                <div class="form-floating mb-3"> <input type="password" name="password" class="form-control" id="floatingPassword" placeholder="Password"> <label for="floatingPassword">Password</label> </div>
                <button class="btn btn-primary w-100 py-2" type="submit">Log in</button>
            </div>
            <div class="px-3">
                <p class="text-center">Don&apos;t have an account? <a href="<?= APP_URL ?>/auth/register.php">Register</a></p>
            </div>
        </form>
    </main>
    <?php include_once APP_PATH . "/partials/footer.php" ?>
</body>

</html>