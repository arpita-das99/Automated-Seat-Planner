<?php
session_start();
require_once '../config/config.php'; // Ensure your database connection file is included

// Ensure the request method is POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $room = mysqli_real_escape_string($conn, $_POST['room']);
    $rows = (int)$_POST['rows'];
    $columns = (int)$_POST['columns'];
    $num_students = min((int)$_POST['num_students'], 50); // Limit room capacity to 50
    $courses = $_POST['courses']; // Assuming this is an array of course IDs

    // Validate input data
    if (empty($room) || !is_array($courses) || empty($courses) || $rows <= 0 || $columns <= 0 || $num_students <= 0) {
        $_SESSION['batchnot'] = "Invalid input.";
        header("Location: ../admin_user/dashboard.php");
        exit();
    }

    $total_capacity = min($rows * $columns, 50); // Ensure total capacity is not more than 50

    // Fetch student data for selected courses
    $student_list = [];
    foreach ($courses as $course_id) {
        $course_id = mysqli_real_escape_string($conn, $course_id);
        $query = "SELECT s.examroll, s.name, c.cid, c.name as course_name 
                  FROM students s 
                  JOIN courses c ON s.cid = c.cid 
                  WHERE c.cid = '$course_id' ORDER BY s.examroll";
        $result = mysqli_query($conn, $query);
        if (!$result) {
            $_SESSION['batchnot'] = "Error fetching student data: " . mysqli_error($conn);
            header("Location: ../admin_user/dashboard.php");
            exit();
        }
        while ($row = mysqli_fetch_assoc($result)) {
            $student_list[] = $row;
        }
    }

    // Validate if enough students are available
    if (count($student_list) < $num_students) {
        $_SESSION['batchnot'] = "Not enough students in selected courses.";
        header("Location: ../admin_user/dashboard.php");
        exit();
    }

    // Check if there is already a seat plan for the selected room
    $existing_plan_query = "SELECT COUNT(*) as count FROM seat_plan WHERE rid = '$room'";
    $existing_plan_result = mysqli_query($conn, $existing_plan_query);
    if ($existing_plan_result) {
        $existing_plan_row = mysqli_fetch_assoc($existing_plan_result);
        if ($existing_plan_row['count'] > 0) {
            $_SESSION['overwrite_room'] = $room;
            $_SESSION['rows'] = $rows;
            $_SESSION['columns'] = $columns;
            $_SESSION['num_students'] = $num_students;
            $_SESSION['courses'] = $courses;
            header("Location: ../actions/confirm_overwrite.php");
            exit();
        }
    }

    // Continue with seat plan generation if no existing plan or overwrite confirmed
    generateSeatPlan($room, $rows, $columns, $num_students, $student_list, $total_capacity, $conn);
}

function generateSeatPlan($room, $rows, $columns, $num_students, $student_list, $total_capacity, $conn) {
    // Clear previous seat plans for the room
    $clear_query = "DELETE FROM seat_plan WHERE rid = '$room'";
    if (!mysqli_query($conn, $clear_query)) {
        $_SESSION['batchnot'] = "Error clearing previous seat plans: " . mysqli_error($conn);
        header("Location: ../admin_user/seat_plan.php");
        exit();
    }

    // Group students by course (batch) and sort by examroll
    $batches = [];
    foreach ($student_list as $student) {
        $batches[$student['cid']][] = $student;
    }

    foreach ($batches as &$batch) {
        usort($batch, function($a, $b) {
            return strcmp($a['examroll'], $b['examroll']);
        });
    }

    // Interleave students from different batches to avoid same batch students sitting together
    $interleaved_students = [];
    while (count($interleaved_students) < $num_students) {
        foreach ($batches as &$batch) {
            if (!empty($batch)) {
                $interleaved_students[] = array_shift($batch);
            }
        }
    }

    // Prepare seat data ensuring no two students from the same batch are in the same row or column
    $seat_data = [];
    $current_student_index = 0;
    $seating_plan = array_fill(1, $rows, array_fill(1, $columns, ['examroll' => 'EMPTY', 'cid' => 'EMPTY']));

    // Fill diagonally
    $diagonal_index = 0;
    for ($i = 0; $i < $num_students; $i++) {
        if ($diagonal_index >= $rows) {
            $diagonal_index = 0;
        }

        for ($j = 0; $j < $rows; $j++) {
            if ($diagonal_index + $j < $rows && $j < $columns) {
                $seating_plan[$diagonal_index + $j][$j + 1] = [
                    'examroll' => $interleaved_students[$i]['examroll'],
                    'cid' => $interleaved_students[$i]['cid']
                ];
                $i++;
                if ($i >= $num_students) break;
            }
        }
        $diagonal_index++;
        if ($i >= $num_students) break;
    }

    // Flatten the seating plan and prepare seat data
    foreach ($seating_plan as $row => $cols) {
        foreach ($cols as $col => $seat) {
            $seat_data[] = [
                'row' => $row,
                'col' => $col,
                'examroll' => $seat['examroll'],
                'cid' => $seat['cid']
            ];
        }
    }

    // Insert seat plan into seat_plan table
    $success = true;

    foreach ($seat_data as $seat) {
        $examroll = $seat['examroll'] !== 'EMPTY' ? "'{$seat['examroll']}'" : 'NULL';
        $cid = $seat['cid'] !== 'EMPTY' ? "'{$seat['cid']}'" : 'NULL';

        $query = "INSERT INTO seat_plan (rid, row, col, examroll, cid) 
                  VALUES ('$room', '{$seat['row']}', '{$seat['col']}', $examroll, $cid)";
        if (!mysqli_query($conn, $query)) {
            $_SESSION['batchnot'] = "Error inserting seat plan: " . mysqli_error($conn);
            $success = false; // Set $success to false if an error occurs
            break; // Break out of the loop
        }
    }

    if ($success) {
        $_SESSION['batch'] = "Seat plan generated successfully!";
        // Store room ID and rows and columns in session to fetch the seat plan in the next page
        $_SESSION['room_id'] = $room;
        $_SESSION['rows'] = $rows;
        $_SESSION['columns'] = $columns;
        header("Location: ../admin_user/display_seat_plan.php");
    } else {
        header("Location: ../admin_user/seat_plan.php");
    }
    exit();
}
?>