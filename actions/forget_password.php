<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Forgot Password</title>
    <link rel="stylesheet" href="../css/authentication.css">
    <link rel="stylesheet" href="../css/navindex.css">
</head>
<body>
    <nav>
        <div class="nav-container">
            <div class="navbar-brand">
                <h1>SeatScheduler</h1>
            </div>
            <div class="navbar-links">
                <a href="About.php" class="btn">About</a>
                <a href="register.php" class="btn btn-alt">Sign Up</a>
            </div>
        </div>
    </nav>
    <div class="wrapper">
        <h1>Forgot Password</h1>
        <?php if (isset($_GET['error'])): ?>
            <p class="error-message"><?php echo htmlspecialchars($_GET['error']); ?></p>
        <?php endif; ?>
        <?php if (isset($_GET['message'])): ?>
            <p class="success-message"><?php echo htmlspecialchars($_GET['message']); ?></p>
        <?php endif; ?>
        <form method="POST" action="../actions/forget_password_action.php">
            <input type="text" name="identifier" placeholder="Email or Phone" required>
            <button type="submit">Send Reset Link</button>
        </form>
        <div class="member">
            Remembered your password? <a href="index.php">Sign In</a>
        </div>
    </div>
</body>
</html>
