<?php
require_once('../config/config.php');

// Function to get seat allocation data
function getSeatAllocationData($conn) {
    $sql = "SELECT seat.room, seat.row, seat.col, students.name 
            FROM seat 
            LEFT JOIN students ON seat.examroll = students.examroll";
    $result = $conn->query($sql);

    if (!$result) {
        die("Seat allocation data query failed: " . $conn->error);
    }

    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    return $data;
}

// Function to get student distribution data
function getStudentDistributionData($conn) {
    $sql = "SELECT batch, COUNT(*) as student_count FROM students GROUP BY batch";
    $result = $conn->query($sql);

    if (!$result) {
        die("Student distribution data query failed: " . $conn->error);
    }

    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    return $data;
}

// Check if the request is AJAX
if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
    header('Content-Type: application/json');

    // Fetch and return data as JSON
    echo json_encode([
        'seat_allocation' => getSeatAllocationData($conn),
        'student_distribution' => getStudentDistributionData($conn)
    ]);

    exit; // Terminate the script after sending JSON response
} else {
    die("This script is meant to be accessed via AJAX only.");
}

$conn->close();
?>
