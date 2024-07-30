<?php
include('../config/DB.php');
session_start();

// Generate a CSRF token if one isn't already present
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Store the CSRF token in a variable to use it in the form
$csrf_token = $_SESSION['csrf_token'];



if (isset($_POST['submit'])) {
    // Verify CSRF token
    // Token is valid, proceed with form processing if not die !
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('Invalid CSRF token');
    }
    // Validate email
    if (empty($_POST['email'])) {
        $emailErr = 'Email is required';
    } else {
        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $emailErr = 'Invalid email format';
        }
    }

    // Validate password
    if (empty($_POST['password'])) {
        $passwordErr = 'Password is required';
    } else {
        $password = $_POST['password'];
    }

    if (empty($emailErr) && empty($passwordErr)) {
        $sql = "SELECT * FROM users WHERE email = '$email'";
        $result = mysqli_query($conn, $sql);
        $user = mysqli_fetch_assoc($result);

        if ($user) {
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                header("Location: index.php");
            } else {
                $error_message = "Invalid password.";
            }
        } else {
            $error_message = "Email not found. Please register first.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CodeZilla || Login</title>
    <link rel="stylesheet" href="../assets/css/styles.css" />
    <link rel="stylesheet" href="../assets/css/all.min.css" />
</head>

<body>
    <header class="header">
        <a href="#" class="header__title">CodeZilla</a>
        <div class="header__cta">
            <span class="header__cta--question">Not a member?</span>
            <a href="register.php">
                <button class="header__cta--button">Create Account</button>
            </a>
        </div>
    </header>
    <div class="container">
        <div class="todo-app">
            <div class="app-title">
                <h2>Login</h2>
            </div>
            <form action="login.php" method="POST">
                <!-- //csrf_token -->
                <input hidden name="csrf_token" value="<?php echo $csrf_token; ?>">
                <div class="row">
                    <input type="text" name="email" placeholder="Email">
                </div>
                <div class="app-title" id="invalid">

                    <?php if (isset($emailErr)) : ?>
                    <h2>*<?php echo $emailErr; ?></h2>
                    <?php endif; ?>
                </div>
                <div class="row">
                    <input type="password" name="password" placeholder="Password">
                </div>

                <div class="app-title" id="invalid">

                    <?php if (isset($passwordErr)) : ?>
                    <h2>*<?php echo $passwordErr; ?></h2>
                    <?php endif; ?>
                </div>
                <div class="app-title" id="invalid">
                    <?php if (isset($error_message)) : ?>
                    <h2>*<?php echo $error_message; ?></h2>
                    <?php endif; ?>
                </div>
                <div class="btn-add">
                    <button type="submit" name="submit">Login</button>
                </div>
            </form>
        </div>
    </div>
    <?php include('footer.php') ?>
</body>

</html>