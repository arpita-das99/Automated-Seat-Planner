<?php
session_start();
include '../config/config.php';

if (isset($_POST['addcourse'])) {
    $course_name = mysqli_real_escape_string($conn, $_POST['course_name']);
    $capacity = mysqli_real_escape_string($conn, $_POST['capacity']);
    $batch = mysqli_real_escape_string($conn, $_POST['batch']);

    if (empty($course_name) || empty($capacity) || empty($batch)) {
        $_SESSION['coursenot'] = "Please fill in all fields.";
        header("Location: ../admin_user/add_courses.php");
        exit();
    }

    $insert = "INSERT INTO courses (name, capacity, batch) VALUES ('$course_name', '$capacity', '$batch')";
    if (mysqli_query($conn, $insert)) {
        $_SESSION['course'] = "Course added successfully.";
    } else {
        $_SESSION['coursenot'] = "Error!! Course not added.";
    }
    header("Location: ../admin_user/add_courses.php");
}
?>
