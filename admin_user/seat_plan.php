<?php
session_start();
include '../config/config.php';
// Check if the user is logged in
if (!isset($_SESSION['email'])) {
    header('Location: ../index.php');
    exit();
}

// Retrieve the user's name from the session if it exists
$name = isset($_SESSION['name']) ? $_SESSION['name'] : 'Guest';

?>
<html>
<head>
    <title>SeatPlan</title>
    <link rel="stylesheet" href="../css/common.css">
    <?php include '../includes/link.php'; ?>
</head>
<body>
<?php
if (isset($_POST['deletebatch'])) {
    $batch = mysqli_real_escape_string($conn, htmlentities($_POST['deletebatch']));
    $delete = "DELETE FROM seat_plan WHERE sid = '$batch'";
    $delete_query = mysqli_query($conn, $delete);
    if ($delete_query) {
        $_SESSION['delbatch'] = "Allotment deleted successfully";
    } else {
        $_SESSION['delnotbatch'] = "Error!! Allotment not deleted.";
    }
}
?>

<div class="wrapper">
<nav id="sidebar">
            <div class="sidebar-header">
                <h4>DASHBOARD</h4>   
            </div>
            <ul class="list-unstyled components">
            <li>
                    <a href=""><img width="23" height="23" src="https://img.icons8.com/ios-glyphs/30/FFFFFF/user--v1.png" alt="user--v1"/> <?php echo htmlspecialchars($name); ?></a>
                </li>
                <li>
                    <a href="dashboard.php"><img width="23" height="23" src="https://img.icons8.com/sf-ultralight/25/FFFFFF/home-page.png" alt="home-page"/>Home</a>
                </li>
                <li>
                    <a href="add_courses.php" ><img width="23" height="23" src="https://img.icons8.com/sf-ultralight/25/FFFFFF/diploma.png" alt="diploma"/>Courses</a>
                </li>
                <li>
                    <a href="add_students.php"><img width="23" height="23" src="https://img.icons8.com/sf-ultralight/25/FFFFFF/student-male.png" alt="student-male"/>Students</a>
                </li>
                <li>
                    <a href="add_rooms.php"><img width="23" height="23" src="https://img.icons8.com/sf-ultralight/25/FFFFFF/room.png" alt="room"/>Rooms</a>
                </li>
                <li>
                    <a href="seat_plan.php" class="active_link"><img width="23" height="23" src="https://img.icons8.com/sf-ultralight/25/FFFFFF/ingredients-list.png" alt="ingredients-list"/>Seat Plan</a>
                </li>
            </ul>
        </nav>
    <div id="content">
        <nav class="navbar navbar-expand-lg navbar-light bg-light">
            <div class="container-fluid">
                <button type="button" id="sidebarCollapse" class="btn btn-info">
                    <img src="https://img.icons8.com/ios-filled/19/ffffff/menu--v3.png"/>
                </button><span class="page-name"> Generate Seat Plan</span>
                <button class="btn btn-dark d-inline-block d-lg-none ml-auto" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                    <img src="https://img.icons8.com/ios-filled/19/ffffff/menu--v3.png"/>
                </button>
                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <ul class="nav navbar-nav ml-auto">
                        <li class="nav-item active">
                            <a class="nav-link" href="../actions/logout.php">Logout</a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
        <div class="main-content">
            <?php
            if (isset($_SESSION['batch'])) {
                echo "<div class='alert alert-warning alert-dismissible fade show' role='alert'>".$_SESSION['batch']."<button class='close' data-dismiss='alert' aria-label='Close'><span aria-hidden='true'>&times;</span></button></div>";
                unset($_SESSION['batch']);
            }
            if (isset($_SESSION['batchnot'])) {
                echo "<div class='alert alert-danger alert-dismissible fade show' role='alert'>".$_SESSION['batchnot']."<button class='close' data-dismiss='alert' aria-label='Close'><span aria-hidden='true'>&times;</span></button></div>";
                unset($_SESSION['batchnot']);
            }
            if (isset($_SESSION['delbatch'])) {
                echo "<div class='alert alert-warning alert-dismissible fade show' role='alert'>".$_SESSION['delbatch']."<button class='close' data-dismiss='alert' aria-label='Close'><span aria-hidden='true'>&times;</span></button></div>";
                unset($_SESSION['delbatch']);
            }
            if (isset($_SESSION['delnotbatch'])) {
                echo "<div class='alert alert-danger alert-dismissible fade show' role='alert'>".$_SESSION['delnotbatch']."<button class='close' data-dismiss='alert' aria-label='Close'><span aria-hidden='true'>&times;</span></button></div>";
                unset($_SESSION['delnotbatch']);
            }
            ?>
            <div class="table-responsive border">
                <form method="POST" action="../actions/generate_seat_plan_action.php" onsubmit="return validateCourseSelection();">
                    <table class="table table-hover text-center">
                        <thead class="thead-light">
                            <tr>
                                <th>Room No.</th>
                                <th>Rows</th>
                                <th>Columns</th>
                                <th>No. of students</th>
                                <th>Courses</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>

                        
                                <th class="py-3 bg-light">
                                    <select name="room" class="form-control" required>
                                        <?php
                                        $select_rooms = "SELECT r.rid, r.roomno, r.capacity, IFNULL(SUM(sp.rid), 0) as filled FROM rooms r LEFT JOIN seat_plan sp ON sp.rid = r.rid GROUP BY r.rid";
                                        $select_rooms_query = mysqli_query($conn, $select_rooms);
                                        if (mysqli_num_rows($select_rooms_query) > 0) {
                                            echo "<option value=''>--select--</option>";
                                            while ($row = mysqli_fetch_assoc($select_rooms_query)) {
                                                if ($row['capacity'] > $row['filled']) {
                                                    echo "<option value='" . $row['rid'] . "'>Room-" . $row['roomno'] . "</option>";
                                                }
                                            }
                                        } else {
                                            echo "<option>No Rooms</option>";
                                        }
                                        ?>
                                    </select>
                                </th>
                                <th class="py-3 bg-light"><input type="number" name="rows" class="form-control" required></th>
                                <th class="py-3 bg-light"><input type="number" name="columns" class="form-control" required></th>
                                <th class="py-3 bg-light"><input type="number" name="num_students" class="form-control" required></th>
                                <th class="py-3 bg-light">
                                    <div>
                                        <?php
                                        $select_courses = "SELECT cid, name FROM courses";
                                        $select_courses_query = mysqli_query($conn, $select_courses);
                                        if (mysqli_num_rows($select_courses_query) > 0) {
                                            while ($course = mysqli_fetch_assoc($select_courses_query)) {
                                                echo "<label><input type='checkbox' name='courses[]' value='" . $course['cid'] . "'> " . $course['name'] . "</label><br/>";
                                            }
                                        } else {
                                            echo "No courses available.";
                                        }
                                        ?>
                                    </div>
                                </th>
                                <th class="py-3 bg-light"><button class="btn btn-primary" type="submit">Generate</button></th>
                            </tr> 
                        </tbody>
                    </table>
                </form>
            </div>
            <?php if (isset($_SESSION['seat_plan_generated']) && $_SESSION['seat_plan_generated']): ?>
                <div class="seat-plan-section">
                    <h3>Generated Seat Plan</h3>
                    <div class="seat-plan-grid">
                        <?php
                        // Fetch seat plan data for the generated room
                        $room_id = $_SESSION['room_id'];
                        $query = "SELECT sp.sid, r.roomno, sp.row, sp.col, sp.examroll, sp.seat_data 
                                  FROM seat_plan sp 
                                  JOIN rooms r ON r.rid = sp.rid 
                                  WHERE sp.rid = '$room_id'";
                        $result = mysqli_query($conn, $query);
                        while ($seat_plan = mysqli_fetch_assoc($result)): ?>
                            <div class="room">
                                <h4>Room <?php echo htmlspecialchars($seat_plan['roomno']); ?></h4>
                                <div class="grid-container">
                                    <?php
                                    $seat_data = json_decode($seat_plan['seat_data'], true);
                                    for ($row = 1; $row <= $seat_plan['row']; $row++) {
                                        for ($col = 1; $col <= $seat_plan['col']; $col++) {
                                            $found = false;
                                            foreach ($seat_data as $seat) {
                                                if ($seat['row'] == $row && $seat['col'] == $col) {
                                                    echo "<div class='grid-cell'>" . htmlspecialchars($seat['name']) . "<br>" . htmlspecialchars($seat['course']) . "</div>";
                                                    $found = true;
                                                    break;
                                                }
                                            }
                                            if (!$found) {
                                                echo "<div class='grid-cell empty'></div>";
                                            }
                                        }
                                    }
                                    ?>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                    <form action="print_seat_plan.php" method="POST">
                        <button type="submit" class="btn btn-primary">Print Seat Plan</button>
                    </form>
                </div>
            <?php endif; ?>
        </div>
        <?php include '../includes/footer.php'; ?>
    </div>
    </div>
<script src="../js/jquery-3.5.1.min.js"></script>
<script src="../js/bootstrap.bundle.min.js"></script>
<script src="../js/popper.min.js"></script>
<script src="../js/app.js"></script>
</body>
</html>        