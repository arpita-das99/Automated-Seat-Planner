<?php
session_start();
require_once 'config/config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $identifier = $_POST['identifier']; // This will hold either email or phone
    $password = $_POST['password'];

    // Determine if the identifier is an email or a phone number
    if (filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
        $sql = "SELECT email, password, name FROM admin_users WHERE email = ?";
    } else {
        $sql = "SELECT email, password, name FROM admin_users WHERE phone = ?";
    }

    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $identifier);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($email, $hashed_password, $name);
        $stmt->fetch();

        if (password_verify($password, $hashed_password)) {
            $_SESSION['identifier'] = $identifier;
            $_SESSION['email'] = $email;
            $_SESSION['name'] = $name;
            header('Location: ../major_project/admin_user/dashboard.php');
            exit();
        } else {
            $_SESSION['error'] = 'Incorrect password';
            header('Location: index.php');
            exit();
        }
    } else {
        $_SESSION['error'] = 'User not found';
        header('Location: index.php');
        exit();
    }

    $stmt->close();
    $conn->close();
} else {
    header('Location: index.php');
    exit();
}
?>
