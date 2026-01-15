<?php
session_start();
require_once '../config/config.php'; // Ensure your database connection file is included

// Ensure the room ID, rows, and columns are available in session
if (!isset($_SESSION['room_id']) || !isset($_SESSION['rows']) || !isset($_SESSION['columns'])) {
    $_SESSION['batchnot'] = "No seat plan found.";
    header("Location: ../admin_user/dashboard.php");
    exit();
}

$room_id = $_SESSION['room_id'];
$rows = (int)$_SESSION['rows'];
$columns = (int)$_SESSION['columns'];

// Fetch the seat plan data for the specified room
$query = "SELECT sp.row, sp.col, sp.examroll, s.cid, c.name as course_name 
          FROM seat_plan sp 
          LEFT JOIN students s ON sp.examroll = s.examroll 
          LEFT JOIN courses c ON s.cid = c.cid 
          WHERE sp.rid = '$room_id'
          ORDER BY sp.row, sp.col";
$result = mysqli_query($conn, $query);

// Check for query error
if (!$result) {
    die("Error fetching seat plan: " . mysqli_error($conn));
}

// Store the seats in a grid
$seats = [];
while ($row = mysqli_fetch_assoc($result)) {
    $seats[$row['row']][$row['col']] = [
        'examroll' => $row['examroll'],
        'course' => $row['course_name']
    ];
}

// Clear the room ID, rows, and columns from the session
unset($_SESSION['room_id']);
unset($_SESSION['rows']);
unset($_SESSION['columns']);

// Divide the seats into two sections (Left and Right) based on columns
$left_section = [];
$right_section = [];
for ($r = 1; $r <= $rows; $r++) {
    for ($c = 1; $c <= $columns; $c++) {
        if ($c <= ceil($columns / 2)) {
            $left_section[$r][$c] = $seats[$r][$c] ?? null;
        } else {
            $right_section[$r][$c - ceil($columns / 2)] = $seats[$r][$c] ?? null;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Seat Plan Display</title>
    <link rel="stylesheet" href="../css/common.css">
    <?php include '../includes/link.php'; ?>
    <style>
        .seat-plan-container {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
        }
        .seat-plan-column {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 0; /* Remove gap between benches */
            max-width: 50%;
            box-sizing: border-box;
        }
        .room {
            border: 1px solid #ccc;
            padding: 20px;
            box-sizing: border-box;
        }
        .bench {
            display: flex;
            justify-content: space-between;
            border: 1px solid #ccc;
            padding: 10px;
            background-color: #f9f9f9;
        }
        .seat {
            flex: 1;
            padding: 5px;
            text-align: center;
            border-right: 1px solid #ccc;
        }
        .seat:last-child {
            border-right: none;
        }
        .seat.occupied {
            background-color: #d4edda; /* Light green for occupied seats */
        }
        .seat.empty {
            background-color: #f8d7da; /* Light red for empty seats */
        }
        .middle-gap {
            width: 50px; /* Adjust the width to create the desired gap */
        }
        .print-button {
            margin: 20px;
            text-align: center;
        }
        .print-button button {
            padding: 10px 20px;
            font-size: 16px;
            cursor: pointer;
        }
        .room-info {
            text-align: center;
            margin: 20px 0;
        }

        @media print {
            .print-button, .room-info p:nth-child(2), .room-info p:nth-child(3) {
                display: none;
            }
        }
    </style>
    <script>
        function printSeatPlan() {
            window.print();
        }
    </script>
</head>
<body>
    <div class="print-button">
        <button class="btn btn-primary" onclick="printSeatPlan()">Print Seat Plan</button>
    </div>
    <div class="room-info">
        <p>Room ID: <?php echo htmlspecialchars($room_id); ?></p>
    </div>
    <div class="seat-plan-container">
        <?php
        $sections = ['Left' => $left_section, 'Right' => $right_section];
        foreach ($sections as $section_name => $section) {
            echo '<div class="seat-plan-column">';
            echo '<div class="room">';
            echo "<h4>Room $room_id ($section_name)</h4>";
            echo '<div class="benches">';
            
            // Display the seats in the current section
            for ($r = 1; $r <= $rows; $r++) {
                echo "<div class='bench'>";
                for ($c = 1; $c <= ceil($columns / 2); $c++) {
                    $seat = $section[$r][$c] ?? null;
                    if ($seat) {
                        echo "<div class='seat occupied'>{$seat['examroll']} - {$seat['course']}</div>";
                    } else {
                        echo "<div class='seat empty'>Empty</div>";
                    }
                }
                echo "</div>";
            }
            
            echo '</div>';
            echo '</div>';
            echo '</div>';
        }
        ?>
        <div class="middle-gap"></div>
    </div>
</body>
</html>