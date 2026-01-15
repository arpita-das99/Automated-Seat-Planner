<?php
require_once('config/config.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $password = $_POST['password'];
    $confirm_password = $_POST['re-enter_password'];

    if ($password !== $confirm_password) {
        $error = "Passwords do not match.";
        header("Location: ../views/register.php?error=" . urlencode($error));
        exit();
    } else {
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);

        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        $sql = "INSERT INTO admin_users (name, email, phone, password) VALUES (?,?,?,?)";
        $stmt = $conn->prepare($sql);

        if ($stmt === false) {
            die("Error preparing statement: " . $conn->error);
        }

        $stmt->bind_param("ssss", $full_name, $email, $phone, $hashed_password);

        if ($stmt->execute()) {
            header("Location: register.php?message=" . urlencode("You have registered successfully please sign in."));
            exit();
        } else {
            $error = "Error: " . $stmt->error;
            header("Location: register.php?error=" . urlencode($error));
            exit();
        }

        $stmt->close();
    }
}

$conn->close();
?>
