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
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('Invalid CSRF token');
    }

    $password = $_POST['password'];


    // Validate username
    if (empty($_POST['username'])) {
        $usernameErr = 'Username is required';
    } else {
        $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        if (!preg_match('/^[a-zA-Z0-9_-]{3,20}$/', $username)) {
            $usernameErr = 'Username must be 3-20 characters long and contain only letters, numbers, underscores, or hyphens.';
        }
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
    } elseif (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/', $password)) {
        $passwordErr = "Password must contain at least 8 characters, including lowercase, uppercase, numbers, and special characters.";
    } else {
        $password = password_hash($password, PASSWORD_BCRYPT);
    }

    // Check if the email already exists
    if (empty($emailErr)) {
        $sql = "SELECT * FROM users WHERE email = '$email'";
        $result = mysqli_query($conn, $sql);
        if (mysqli_num_rows($result) > 0) {
            $emailErr = 'Email already exists';
        }
    }

    if (empty($usernameErr) && empty($passwordErr) && empty($emailErr)) {
        $sql = "INSERT INTO users (username, email, password) VALUES ('$username', '$email', '$password')";
        if (mysqli_query($conn, $sql)) {
            header('location: login.php');
        } else {
            echo "Error: " . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CodeZilla || Register </title>
    <link rel="stylesheet" href="../assets/css/styles.css" />
    <link rel="stylesheet" href="../assets/css/all.min.css" />
</head>

<body>
    <header class="header">
        <a href="#" class="header__title">CodeZilla</a>
        <div class="header__cta">
            <span class="header__cta--question">Have an account?</span>
            <a href="login.php">
                <button class="header__cta--button">Login </button>
            </a>
        </div>
    </header>
    <div class="container">
        <div class="todo-app">
            <div class="app-title">
                <h2>Register </h2>
            </div>
            <form action="register.php" method="POST">
                <input hidden name="csrf_token" value="<?php echo $csrf_token; ?>">
                <div class="row">
                    <input type="text" name="username" placeholder="username">
                </div>
                <div class="app-title" id="invalid">

                    <?php if (isset($usernameErr)) : ?>
                        <h2>
                            *<?php echo $usernameErr; ?>
                        </h2>
                    <?php endif; ?>
                </div>
                <div class="row">
                    <input type="text" name="email" placeholder="email">
                </div>
                <div class="app-title" id="invalid">

                    <?php if (isset($emailErr)) : ?>
                        <h2>
                            *<?php echo $emailErr; ?>
                        </h2>
                    <?php endif; ?>
                </div>
                <div class="row">
                    <input type="password" name="password" placeholder="Password">
                </div>
                <div class="app-title" id="invalid">

                    <?php if (isset($passwordErr)) : ?>
                        <h2>
                            *<?php echo $passwordErr; ?>
                        </h2>
                    <?php endif; ?>
                </div>
                <div class="btn-add">
                    <button type="submit" name="submit">Register</button>
                </div>
            </form>
        </div>
    </div>
    <?php include('footer.php') ?>
</body>

</html>