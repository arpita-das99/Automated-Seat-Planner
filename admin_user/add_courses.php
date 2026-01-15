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


// Check if deletecourse form is submitted
if(isset($_POST['deletecourse'])){
    $course = $_POST['deletecourse'];
    $delete = "DELETE FROM courses WHERE cid = '$course'";
    $delete_query = mysqli_query($conn, $delete);
    if($delete_query){
        $_SESSION['delete'] = "Course deleted successfully";
    } else {
        $_SESSION['deletenot'] = "Error!! Course not deleted.";
    }
}

// Check if editcourse form is submitted
if(isset($_POST['editcourse'])){
    $cid = $_POST['cid'];
    $name = $_POST['name'];
    $capacity = $_POST['capacity'];
    $batch = $_POST['batch'];
    $update = "UPDATE courses SET name = '$name', capacity = '$capacity', batch = '$batch' WHERE cid = '$cid'";
    $update_query = mysqli_query($conn, $update);
    if($update_query){
        $_SESSION['update'] = "Course updated successfully";
    } else {
        $_SESSION['updatenot'] = "Error!! Course not updated.";
    }
}

// Check if addcourse form is submitted
if(isset($_POST['addcourse'])){
    $name = $_POST['course_name'];
    $capacity = $_POST['capacity'];
    $batch = $_POST['batch'];
    $insert = "INSERT INTO courses (name, capacity, batch) VALUES ('$name', '$capacity', '$batch')";
    $insert_query = mysqli_query($conn, $insert);
    if($insert_query){
        $_SESSION['course'] = "Course added successfully";
    } else {
        $_SESSION['coursenot'] = "Error!! Course not added.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Courses</title>
    <link rel="stylesheet" href="../css/common.css">
    <?php include '../includes/link.php'; ?>
    <script>
        function toggleCourseRecords() {
            var records = document.getElementById('courseRecords');
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
                    <a href="add_courses.php" class="active_link"><img width="23" height="23" src="https://img.icons8.com/sf-ultralight/25/FFFFFF/diploma.png" alt="diploma"/>Courses</a>
                </li>
                <li>
                    <a href="add_students.php"><img width="23" height="23" src="https://img.icons8.com/sf-ultralight/25/FFFFFF/student-male.png" alt="student-male"/>Students</a>
                </li>
                <li>
                    <a href="add_rooms.php"><img width="23" height="23" src="https://img.icons8.com/sf-ultralight/25/FFFFFF/room.png" alt="room"/>Rooms</a>
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
                    <span class="page-name"> Manage Courses</span>
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
                if(isset($_SESSION['course'])){
                    echo "<div class='alert alert-warning alert-dismissible fade show' role='alert'>".$_SESSION['course']."<button class='close' data-dismiss='alert' aria-label='Close'><span aria-hidden='true'>&times;</span></button></div>";
                    unset($_SESSION['course']);
                }
                if(isset($_SESSION['coursenot'])){
                    echo "<div class='alert alert-danger alert-dismissible fade show' role='alert'>".$_SESSION['coursenot']."<button class='close' data-dismiss='alert' aria-label='Close'><span aria-hidden='true'>&times;</span></button></div>";
                    unset($_SESSION['coursenot']);
                }
                if(isset($_SESSION['delete'])){
                    echo "<div class='alert alert-warning alert-dismissible fade show' role='alert'>".$_SESSION['delete']."<button class='close' data-dismiss='alert' aria-label='Close'><span aria-hidden='true'>&times;</span></button></div>";
                    unset($_SESSION['delete']);
                }
                if(isset($_SESSION['deletenot'])){
                    echo "<div class='alert alert-danger alert-dismissible fade show' role='alert'>".$_SESSION['deletenot']."<button class='close' data-dismiss='alert' aria-label='Close'><span aria-hidden='true'>&times;</span></button></div>";
                    unset($_SESSION['deletenot']);
                }
                if(isset($_SESSION['update'])){
                    echo "<div class='alert alert-warning alert-dismissible fade show' role='alert'>".$_SESSION['update']."<button class='close' data-dismiss='alert' aria-label='Close'><span aria-hidden='true'>&times;</span></button></div>";
                    unset($_SESSION['update']);
                }
                if(isset($_SESSION['updatenot'])){
                    echo "<div class='alert alert-danger alert-dismissible fade show' role='alert'>".$_SESSION['updatenot']."<button class='close' data-dismiss='alert' aria-label='Close'><span aria-hidden='true'>&times;</span></button></div>";
                    unset($_SESSION['updatenot']);
                }
                ?>

                <form action="" method="post" class="mb-3">
                    <div class="form-group">
                        <label for="course_name">Course</label>
                        <input class="form-control" type="text" name="course_name" placeholder="name" required>
                    </div>
                    <div class="form-group">
                        <label for="capacity">Seats</label>
                        <input class="form-control" type="number" name="capacity" placeholder="capacity" required>
                    </div>
                    <div class="form-group">
                        <label for="batch">Batch</label>
                        <input class="form-control" type="text" name="batch" placeholder="batch (year)" required>
                    </div>
                    <button class="btn btn-primary" type="submit" name="addcourse">Add</button>
                </form>

                <button class="btn btn-info mb-3" onclick="toggleCourseRecords()">View Course Records</button>

                <div id="courseRecords" style="display: none;" class="table-responsive border">
                    <table class="table table-hover text-center">
                        <thead class="thead-light">
                            <tr>
                                <th>Course</th>
                                <th>Capacity</th>
                                <th>Batch</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $selectcourse = "SELECT * FROM courses ORDER BY name";
                            $selectcoursequery = mysqli_query($conn, $selectcourse);
                            if($selectcoursequery){
                                while ($row = mysqli_fetch_assoc($selectcoursequery)) {
                                    echo "<tr>
                                    <td>".$row['name']."</td>
                                    <td>".$row['capacity']."</td>
                                    <td>".$row['batch']."</td>
                                    <td>
                                        <form method='post' style='display:inline-block;'>
                                            <input type='hidden' name='deletecourse' value='".$row['cid']."'>
                                            <button class='btn btn-danger' type='submit'>Delete</button>
                                        </form>
                                        <button class='btn btn-warning' data-toggle='modal' data-target='#editModal".$row['cid']."'>Edit</button>
                                        <!-- Edit Modal -->
                                        <div class='modal fade' id='editModal".$row['cid']."' tabindex='-1' role='dialog' aria-labelledby='editModalLabel".$row['cid']."' aria-hidden='true'>
                                            <div class='modal-dialog' role='document'>
                                                <div class='modal-content'>
                                                    <div class='modal-header'>
                                                        <h5 class='modal-title' id='editModalLabel".$row['cid']."'>Edit Course</h5>
                                                        <button type='button' class='close' data-dismiss='modal' aria-label='Close'>
                                                            <span aria-hidden='true'>&times;</span>
                                                        </button>
                                                    </div>
                                                    <div class='modal-body'>
                                                        <form action='' method='post'>
                                                            <div class='form-group'>
                                                                <label for='editCourseName".$row['cid']."'>Course Name</label>
                                                                <input type='text' class='form-control' id='editCourseName".$row['cid']."' name='name' value='".$row['name']."' required>
                                                            </div>
                                                            <div class='form-group'>
                                                                <label for='editCapacity".$row['cid']."'>Capacity</label>
                                                                <input type='number' class='form-control' id='editCapacity".$row['cid']."' name='capacity' value='".$row['capacity']."' required>
                                                            </div>
                                                            <div class='form-group'>
                                                                <label for='editBatch".$row['cid']."'>Batch</label>
                                                                <input type='text' class='form-control' id='editBatch".$row['cid']."' name='batch' value='".$row['batch']."' required>
                                                            </div>
                                                            <input type='hidden' name='cid' value='".$row['cid']."'>
                                                            <button type='submit' name='editcourse' class='btn btn-primary'>Save changes</button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    </tr>";
                                }
                            } else {
                                echo "<tr><td colspan='4'>No courses available.</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
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
