<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link rel="stylesheet" href="css/authentication.css">
    <link rel="stylesheet" href="css/navbar.css">
    <style>
        .error-message {
            color: red;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <nav>
        <div class="nav-container">
            <div class="navbar-brand">
                <h1>ArrangeEd</h1>
            </div>
            <div class="navbar-links">
                <a href="register.php" class="btn btn-alt">Sign Up</a>
            </div>
        </div>
    </nav>
    <div class="wrapper">
        <h1>Sign In</h1>
        <?php
        session_start();
        if (isset($_SESSION['error'])):
            ?>
            <p class="error-message"><?php echo htmlspecialchars($_SESSION['error']); ?></p>
            <?php
            unset($_SESSION['error']); // Clear the error after displaying it
        endif;
        ?>
        <form method="POST" action="login_action.php">
            <input type="text" name="identifier" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <div class="recover">
                <a href="forget_password.php" class="forget_password">
                    Forget Password?
                </a>
            </div>
            <button type="submit">Sign In</button>
        </form>
        <div class="member">
            Not a member? <a href="register.php">Sign up</a>
        </div>
    </div>
</body>
</html>
