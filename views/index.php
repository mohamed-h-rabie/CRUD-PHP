    <?php
    session_start();
    include("../config/DB.php");

    // Generate a CSRF token if one isn't already present
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    // Store the CSRF token in a variable to use it in the form
    $csrf_token = $_SESSION['csrf_token'];

    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
    }

    // User ID and username
    $user_id = $_SESSION['user_id'];
    $username = $_SESSION['username'];

    // Pagination variables
    $limit = 2;
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $offset = ($page - 1) * $limit;

    // Fetching data from database with pagination
    $sql = "SELECT * FROM todolist WHERE user_id = $user_id LIMIT $limit OFFSET $offset";
    $result = mysqli_query($conn, $sql);
    $list = mysqli_fetch_all($result, MYSQLI_ASSOC);

    // Total number of items
    $total_sql = "SELECT COUNT(*) from todolist WHERE user_id = $user_id";
    $total_result = mysqli_query($conn, $total_sql);
    $total_row = mysqli_fetch_row($total_result);
    $total_items = $total_row[0] ?? 0;
    $total_pages = ceil($total_items / $limit);

    // Searching data from database
    if (isset($_GET['search'])) {
        $filtervalues = $_GET['search'];
        $sql = "SELECT * FROM todolist WHERE user_id = $user_id AND title LIKE '%$filtervalues%'";
        $list = mysqli_query($conn, $sql);
    }

    // Deleting data from database
    if (isset($_POST['delete'])) {
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            die('Invalid CSRF token');
        }
        $id = $_POST['id'];
        $sql = "DELETE FROM todolist WHERE id = $id";
        if (mysqli_query($conn, $sql)) {
            header('Location: index.php?page=' . $page);
        } else {
            echo 'Error: ' . mysqli_error($conn);
        }
    }

    // Inserting data to database
    if (isset($_POST['submit'])) {
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            die('Invalid CSRF token');
        }
        $title = $_POST['title'];
        $body = $_POST['body'];
        $sql = "INSERT INTO todolist (title, body, user_id) VALUES ('$title', '$body', $user_id)";
        if (mysqli_query($conn, $sql)) {
            header('Location: index.php?page=' . $page);
        } else {
            echo 'Error: ' . mysqli_error($conn);
        }
    }

    // Updating task completion status
    if (isset($_POST['complete'])) {
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            die('Invalid CSRF token');
        }
        $id = $_POST['id'];
        $completed = isset($_POST['completed']) ? 1 : 0;
        $sql = "UPDATE todolist SET completed = '$completed' WHERE id = $id ";
        if (mysqli_query($conn, $sql)) {
            header('Location: index.php?page=' . $page);
        } else {
            echo 'Error: ' . mysqli_error($conn);
            header('Location: index.php?page=' . $page);
        }
    }
    ?>
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>CodeZilla || To-Do-List App</title>
        <link rel="stylesheet" href="../assets/css/styles.css" />
        <link rel="stylesheet" href="../assets/css/all.min.css" />

    </head>

    <body>
        <header class="header">
            <a href="index.php" class="header__title">CodeZilla</a>
            <div class="header__cta">
                <span class="header__cta--question" id="username">Hello, <?php echo ucfirst($username); ?></span>
                <a href="logout.php">
                    <button class="header__cta--button">logout</button>
                </a>
            </div>
        </header>
        <div class="container">
            <form action="" method="GET">
                <div class="row" id="search-bar">
                    <input type="text" name="search" id="search" />
                    <button type="submit"><i class="fa-solid fa-magnifying-glass"></i></button>
                </div>
            </form>
            <div class="todo-app">
                <div class="app-title">
                    <h2>To-do app</h2>
                    <i class="fa-solid fa-book-bookmark"></i>
                </div>
                <form method="POST" action="">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    <div class="row">
                        <input type="text" name="title" id="input-box" placeholder="Add your task title" required>
                    </div>
                    <div class="row">
                        <input type="text" name="body" id="input-box" placeholder="Add your task description" required>
                        <input type="hidden" name="submit">
                    </div>
                    <div class="btn-add">
                        <button type="submit">Add</button>
                    </div>
                </form>
                <?php if (empty($list)) : ?>
                <h1 id="empty-todo">Add your list for today</h1>
                <?php endif; ?>
                <ul id="list-container">
                    <?php foreach ($list as $item) : ?>
                    <li class="<?php echo $item['completed'] ? 'completed' : ''; ?>">
                        <form action="" method="POST">
                            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?>">
                            <input type="hidden" name="id" value="<?php echo $item['id'] ?>">
                            <input type="checkbox" class="check" name="completed"
                                <?php echo $item['completed'] ? 'checked' : ''; ?> onchange="this.form.submit()">
                            <input type="hidden" name="complete">


                        </form>
                        <h2><?php echo $item['title']; ?></h2>
                        <p><?php echo $item['body']; ?></p>
                        <form method="POST" action="">
                            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                            <input type="hidden" name="id" value="<?php echo $item['id']; ?>">
                            <span>
                                <button name="delete" type="submit" class="delete">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </span>
                        </form>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <div class="pagination">
                    <?php for ($i = 1; $i <= $total_pages; $i++) : ?>
                    <a href="?page=<?php echo $i; ?>"
                        <?php if ($i == $page) echo 'class="active"'; ?>><?php echo $i; ?></a>
                    <?php endfor; ?>
                </div>

            </div>
        </div>
        <?php include('footer.php'); ?>
    </body>

    </html>