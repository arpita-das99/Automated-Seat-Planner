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

// Check if deleteroom form is submitted
if (isset($_POST['deleteroom'])) {
    $room = $_POST['deleteroom'];
    $delete = "DELETE FROM rooms WHERE rid = '$room'";
    $delete_query = mysqli_query($conn, $delete);
    if ($delete_query) {
        $_SESSION['delroom'] = "Room deleted successfully";
    } else {
        $_SESSION['delnotroom'] = "Error!! Room not deleted: " . mysqli_error($conn);
    }
}

// Check if editroom form is submitted
if (isset($_POST['editroom'])) {
    $rid = $_POST['rid'];
    $roomno = $_POST['roomno'];
    $capacity = $_POST['capacity'];
    $update = "UPDATE rooms SET roomno = '$roomno', capacity = '$capacity' WHERE rid = '$rid'";
    $update_query = mysqli_query($conn, $update);
    if ($update_query) {
        $_SESSION['update'] = "Room updated successfully";
    } else {
        $_SESSION['updatenot'] = "Error!! Room not updated: " . mysqli_error($conn);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Rooms</title>
    <link rel="stylesheet" href="../css/common.css">
    <?php include'../includes/link.php'; ?>
    <script>
        function toggleRoomRecords() {
            var records = document.getElementById('roomRecords');
            if (records.style.display === 'none' || records.style.display === '') {
                records.style.display = 'block';
            } else {
                records.style.display = 'none';
            }
        }
    </script>
</head>
<body>
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
                    <a href="add_rooms.php" class="active_link"><img width="23" height="23" src="https://img.icons8.com/sf-ultralight/25/FFFFFF/room.png" alt="room"/>Rooms</a>
                </li>
                <li>
                    <a href="seat_plan.php"><img width="23" height="23" src="https://img.icons8.com/sf-ultralight/25/FFFFFF/ingredients-list.png" alt="ingredients-list"/>Seat Plan</a>
                </li>
            </ul>
        </nav>
        <div id="content">
            <nav class="navbar navbar-expand-lg navbar-light bg-light">
                <div class="container-fluid">
                    <button type="button" id="sidebarCollapse" class="btn btn-info">
                        <img src="https://img.icons8.com/ios-filled/19/ffffff/menu--v3.png"/>
                    </button>
                    <span class="page-name"> Manage Rooms</span>
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
                if (isset($_SESSION['room'])) {
                    echo "<div class='alert alert-warning alert-dismissible fade show' role='alert'>" . $_SESSION['room'] . "<button class='close' data-dismiss='alert' aria-label='Close'><span aria-hidden='true'>&times;</span></button></div>";
                    unset($_SESSION['room']);
                }
                if (isset($_SESSION['roomnot'])) {
                    echo "<div class='alert alert-danger alert-dismissible fade show' role='alert'>" . $_SESSION['roomnot'] . "<button class='close' data-dismiss='alert' aria-label='Close'><span aria-hidden='true'>&times;</span></button></div>";
                    unset($_SESSION['roomnot']);
                }
                if (isset($_SESSION['delroom'])) {
                    echo "<div class='alert alert-warning alert-dismissible fade show' role='alert'>" . $_SESSION['delroom'] . "<button class='close' data-dismiss='alert' aria-label='Close'><span aria-hidden='true'>&times;</span></button></div>";
                    unset($_SESSION['delroom']);
                }
                if (isset($_SESSION['delnotroom'])) {
                    echo "<div class='alert alert-danger alert-dismissible fade show' role='alert'>" . $_SESSION['delnotroom'] . "<button class='close' data-dismiss='alert' aria-label='Close'><span aria-hidden='true'>&times;</span></button></div>";
                    unset($_SESSION['delnotroom']);
                }
                if (isset($_SESSION['update'])) {
                    echo "<div class='alert alert-warning alert-dismissible fade show' role='alert'>" . $_SESSION['update'] . "<button class='close' data-dismiss='alert' aria-label='Close'><span aria-hidden='true'>&times;</span></button></div>";
                    unset($_SESSION['update']);
                }
                if (isset($_SESSION['updatenot'])) {
                    echo "<div class='alert alert-danger alert-dismissible fade show' role='alert'>" . $_SESSION['updatenot'] . "<button class='close' data-dismiss='alert' aria-label='Close'><span aria-hidden='true'>&times;</span></button></div>";
                    unset($_SESSION['updatenot']);
                }
                ?>

                <form action="../actions/addroom.php" method="post" class="mb-3">
                    <div class="form-group">
                        <label for="roomno">Room</label>
                        <input class="form-control" type="number" min="0" max="815" name="roomno" placeholder="number" required>
                    </div>
                    <div class="form-group">
                        <label for="capacity">Capacity</label>
                        <input class="form-control" type="number" min="0" max="80" name="cap" placeholder="capacity" required>
                    </div>
                    <button class="btn btn-primary" type="submit" name="addroom">Add</button>
                </form>
                <button class="btn btn-info mb-3" onclick="toggleRoomRecords()">View Room Records</button>

                <div id="roomRecords" style="display: none;">
                    <div class="table-responsive border">
                        <table class="table table-hover text-center">
                            <thead class="thead-light">
                                <tr>
                                    <th>Room No.</th>
                                    <th>Capacity</th>
                                    <th>Actions</th>
                                </tr>   
                            </thead>
                            <tbody>
                                <?php
                                $selectRooms = "SELECT r.rid, r.roomno, r.capacity
                                                FROM rooms r";
                                $selectRoomsQuery = mysqli_query($conn, $selectRooms);
                                if ($selectRoomsQuery) {
                                    if (mysqli_num_rows($selectRoomsQuery) > 0) {
                                        while ($row = mysqli_fetch_assoc($selectRoomsQuery)) {
                                            echo "<tr>
                                                <td>" . $row['roomno'] . "</td>
                                                <td>" . $row['capacity'] . "</td>
                                                <td>
                                                    <form method='post' style='display:inline-block;'>
                                                        <input type='hidden' name='deleteroom' value='" . $row['rid'] . "'>
                                                        <button class='btn btn-danger' type='submit'>Delete</button>
                                                    </form>
                                                    <button class='btn btn-warning' data-toggle='modal' data-target='#editModal" . $row['rid'] . "'>Edit</button>
                                                    <!-- Edit Modal -->
                                                    <div class='modal fade' id='editModal" . $row['rid'] . "' tabindex='-1' role='dialog' aria-labelledby='editModalLabel" . $row['rid'] . "' aria-hidden='true'>
                                                        <div class='modal-dialog' role='document'>
                                                            <div class='modal-content'>
                                                                <div class='modal-header'>
                                                                    <h5 class='modal-title' id='editModalLabel" . $row['rid'] . "'>Edit Room</h5>
                                                                    <button type='button' class='close' data-dismiss='modal' aria-label='Close'>
                                                                        <span aria-hidden='true'>&times;</span>
                                                                    </button>
                                                                </div>
                                                                <div class='modal-body'>
                                                                    <form action='' method='post'>
                                                                        <div class='form-group'>
                                                                            <label for='editRoomNo" . $row['rid'] . "'>Room No.</label>
                                                                            <input type='number' class='form-control' id='editRoomNo" . $row['rid'] . "' name='roomno' value='" . $row['roomno'] . "' required>
                                                                        </div>
                                                                        <div class='form-group'>
                                                                            <label for='editCapacity" . $row['rid'] . "'>Capacity</label>
                                                                            <input type='number' class='form-control' id='editCapacity" . $row['rid'] . "' name='capacity' value='" . $row['capacity'] . "' required>
                                                                        </div>
                                                                        <input type='hidden' name='rid' value='" . $row['rid'] . "'>
                                                                        <button type='submit' name='editroom' class='btn btn-primary'>Save changes</button>
                                                                    </form>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>";
                                        }
                                    } else {
                                        echo "<tr><td colspan='3'>No rooms available.</td></tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='3'>Error: " . mysqli_error($conn) . "</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <?php include '../includes/footer.php'; ?>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
</body>
</html>
