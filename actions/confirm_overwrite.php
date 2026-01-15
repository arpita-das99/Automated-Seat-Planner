<?php
session_start();

if (!isset($_SESSION['overwrite_room'])) {
    header("Location: ../admin_user/dashboard.php");
    exit();
}

if (!isset($_SESSION['rows']) || !isset($_SESSION['columns']) || !isset($_SESSION['num_students']) || !isset($_SESSION['courses'])) {
    // Handle the error or redirect as appropriate
    $_SESSION['batchnot'] = "Session variables not set. Please try again.";
    header("Location: ../admin_user/seat_plan.php");
    exit();
}

$room = $_SESSION['overwrite_room'];
$rows = $_SESSION['rows'];
$columns = $_SESSION['columns'];
$num_students = $_SESSION['num_students'];
$courses = $_SESSION['courses'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['confirm']) && $_POST['confirm'] == 'yes') {
        require_once '../config/config.php';
        $total_capacity = $rows * $columns;

        // Fetch student data for selected courses
        $student_list = [];
        foreach ($courses as $course_id) {
            $course_id = mysqli_real_escape_string($conn, $course_id);
            $query = "SELECT s.examroll, s.name, c.cid, c.name as course_name 
                      FROM students s 
                      JOIN courses c ON s.cid = c.cid 
                      WHERE c.cid = '$course_id'";
            $result = mysqli_query($conn, $query);
            if (!$result) {
                $_SESSION['batchnot'] = "Error fetching student data: " . mysqli_error($conn);
                header("Location: ../admin_user/seat_plan.php");
                exit();
            }
            while ($row = mysqli_fetch_assoc($result)) {
                $student_list[] = $row;
            }
        }

        generateSeatPlan($room, $rows, $columns, $num_students, $student_list, $total_capacity, $conn);
    } else {
        header("Location: ../admin_user/seat_plan.php");
        exit();
    }
}

function generateSeatPlan($room, $rows, $columns, $num_students, $student_list, $total_capacity, $conn) {
    // Clear the previous seat plans
    $clear_query = "DELETE FROM seat_plan WHERE rid = '$room'";
    if (!mysqli_query($conn, $clear_query)) {
        $_SESSION['batchnot'] = "Error clearing previous seat plans: " . mysqli_error($conn);
        header("Location: ../admin_user/seat_plan.php");
        exit();
    }

    // Shuffle the student list
    shuffle($student_list);

    // Assign seats to students
    $seat_plan = [];
    $student_index = 0;

    for ($r = 1; $r <= $rows; $r++) {
        for ($c = 1; $c <= $columns; $c++) {
            if ($student_index < $num_students) {
                $seat_plan[] = [
                    'rid' => $room,
                    'row' => $r,
                    'col' => $c,
                    'examroll' => $student_list[$student_index]['examroll']
                ];
                $student_index++;
            }
        }
    }

    // Insert the new seat plan into the database
    foreach ($seat_plan as $seat) {
        $insert_query = "INSERT INTO seat_plan (rid, row, col, examroll) VALUES ('{$seat['rid']}', '{$seat['row']}', '{$seat['col']}', '{$seat['examroll']}')";
        if (!mysqli_query($conn, $insert_query)) {
            $_SESSION['batchnot'] = "Error inserting seat plan: " . mysqli_error($conn);
            header("Location: ../admin_user/seat_plan.php");
            exit();
        }
    }

    // Redirect to the seat plan display page
    $_SESSION['room_id'] = $room;
    $_SESSION['rows'] = $rows;
    $_SESSION['columns'] = $columns;
    header("Location: ../admin_user/display_seat_plan.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Confirm Overwrite</title>
    <link rel="stylesheet" href="../css/common.css">
    <?php include '../includes/link.php'; ?>
</head>
<body>
    <h2>Confirm Overwrite</h2>
    <p>A seat plan already exists for room <?php echo htmlspecialchars($room); ?>. Do you want to overwrite it?</p>
    <form method="post" action="confirm_overwrite.php">
        <button class="btn btn-primary" type="submit" name="confirm" value="yes">Yes</button>
        <button class="btn btn-danger" type="submit" name="confirm" value="no">No</button>
    </form>
</body>
</html>