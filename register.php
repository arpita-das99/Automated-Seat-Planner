<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register</title>
    <link rel="stylesheet" href="css/authentication.css">
    <link rel="stylesheet" href="css/navbar.css">
</head>
<body>
    <nav>
        <div class="nav-container">
            <div class="navbar-brand">
                <h1>ArrangeEd</h1>
            </div>
            <div class="navbar-links">
                <a href="index.php" class="btn btn-alt">Sign In</a>
            </div>
        </div>
    </nav>
    <div class="wrapper">
        <h1>Sign Up</h1>
        <?php if (isset($_GET['error'])): ?>
            <p class="error-message"><?php echo htmlspecialchars($_GET['error']); ?></p>
        <?php endif; ?>
        <?php if (isset($_GET['message'])): ?>
            <p class="success-message"><?php echo htmlspecialchars($_GET['message']); ?></p>
        <?php endif; ?>
        <form id="registerForm" method="POST" action="register_action.php">
            <input type="text" name="full_name" placeholder="Full Name" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="tel" name="phone" placeholder="Phone" required>
            <input type="password" name="password" placeholder="Password" required>
            <input type="password" name="re-enter_password" placeholder="Re-Enter Password" required>
        </form>
        <button type="button" onclick="submitForm()">Sign Up</button>
        <div class="member">
            Already have an account? <a href="index.php">Sign In</a>
        </div>
    </div>
    <script>
        function submitForm() {
            document.getElementById('registerForm').submit();
        }
    </script>
</body>
</html>
