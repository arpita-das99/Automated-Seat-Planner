<?php 
session_start();
require_once '../config/config.php'; 
// Check if the user is logged in
if (!isset($_SESSION['email'])) {
    header('Location: ../index.php');
    exit();
}

// Retrieve the user's name from the session if it exists
$name = isset($_SESSION['name']) ? $_SESSION['name'] : 'Guest';


if (isset($_POST['addstudent'])) {
    $sname = mysqli_real_escape_string($conn, $_POST['sname']);
    $scourse = mysqli_real_escape_string($conn, $_POST['scourse']);
    $sroll = mysqli_real_escape_string($conn, $_POST['sroll']);
    $insert = "INSERT INTO students (name, cid, examroll) VALUES ('$sname', '$scourse', '$sroll')";
    $insert_query = mysqli_query($conn, $insert);
    if ($insert_query) {
        $_SESSION['student'] = "Student added successfully";
    } else {
        $_SESSION['studentnot'] = "Error!! Student not added.";
    }
}

if (isset($_POST['deletestudent'])) {
    $examroll = $_POST['deletestudent'];
    $delete = "DELETE FROM students WHERE examroll = '$examroll'";
    $delete_query = mysqli_query($conn, $delete);
    if ($delete_query) {
        $_SESSION['delstudent'] = "Student deleted successfully";
    } else {
        $_SESSION['delnotstudent'] = "Error!! Student not deleted.";
    }
}

if (isset($_POST['editstudent'])) {
    $examroll = $_POST['examroll'];
    $name = $_POST['name'];
    $cid = $_POST['cid'];
    $update = "UPDATE students SET name = '$name', cid = '$cid' WHERE examroll = '$examroll'";
    $update_query = mysqli_query($conn, $update);
    if ($update_query) {
        $_SESSION['editstudent'] = "Student updated successfully";
    } else {
        $_SESSION['editnotstudent'] = "Error!! Student not updated.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Students</title>
    <link rel="stylesheet" href="../css/common.css">
    <?php include '../includes/link.php'; ?>
    <script>
        function toggleStudentRecords() {
            var records = document.getElementById('studentRecords');
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
                    <a href="add_students.php" class="active_link"><img width="23" height="23" src="https://img.icons8.com/sf-ultralight/25/FFFFFF/student-male.png" alt="student-male"/>Students</a>
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
                    <span class="page-name"> Manage Students</span>
                    <button class="btn btn-dark d-inline-block d-lg-none ml-auto" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                        <img src="https://img.icons8.com/ios-filled/20/ffffff/menu--v3.png"/>
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
                if (isset($_SESSION['student'])) {
                    echo "<div class='alert alert-success alert-dismissible fade show' role='alert'>" . $_SESSION['student'] . "<button class='close' data-dismiss='alert' aria-label='Close'><span aria-hidden='true'>&times;</span></button></div>";
                    unset($_SESSION['student']);
                }
                if (isset($_SESSION['studentnot'])) {
                    echo "<div class='alert alert-danger alert-dismissible fade show' role='alert'>" . $_SESSION['studentnot'] . "<button class='close' data-dismiss='alert' aria-label='Close'><span aria-hidden='true'>&times;</span></button></div>";
                    unset($_SESSION['studentnot']);
                }
                if (isset($_SESSION['delstudent'])) {
                    echo "<div class='alert alert-success alert-dismissible fade show' role='alert'>" . $_SESSION['delstudent'] . "<button class='close' data-dismiss='alert' aria-label='Close'><span aria-hidden='true'>&times;</span></button></div>";
                    unset($_SESSION['delstudent']);
                }
                if (isset($_SESSION['delnotstudent'])) {
                    echo "<div class='alert alert-danger alert-dismissible fade show' role='alert'>" . $_SESSION['delnotstudent'] . "<button class='close' data-dismiss='alert' aria-label='Close'><span aria-hidden='true'>&times;</span></button></div>";
                    unset($_SESSION['delnotstudent']);
                }
                if (isset($_SESSION['editstudent'])) {
                    echo "<div class='alert alert-success alert-dismissible fade show' role='alert'>" . $_SESSION['editstudent'] . "<button class='close' data-dismiss='alert' aria-label='Close'><span aria-hidden='true'>&times;</span></button></div>";
                    unset($_SESSION['editstudent']);
                }
                if (isset($_SESSION['editnotstudent'])) {
                    echo "<div class='alert alert-danger alert-dismissible fade show' role='alert'>" . $_SESSION['editnotstudent'] . "<button class='close' data-dismiss='alert' aria-label='Close'><span aria-hidden='true'>&times;</span></button></div>";
                    unset($_SESSION['editnotstudent']);
                }
                ?>
                <form action="" method="post" class="mb-3">
                    <div class="form-group">
                        <label for="sname">Student Name</label>
                        <input type="text" class="form-control" id="sname" name="sname" placeholder="name" required>
                    </div>
                    <div class="form-group">
                        <label for="scourse">Course</label>
                        <select id="scourse" name="scourse" class="form-control" required>
                            <option value="">-- Select Course --</option>
                            <?php 
                            $select_courses = "SELECT * FROM courses ORDER BY batch";
                            $result_courses = mysqli_query($conn, $select_courses);
                            if (mysqli_num_rows($result_courses) > 0) {
                                while ($row_course = mysqli_fetch_assoc($result_courses)) {
                                    echo "<option value='" . $row_course['cid'] . "'>" . $row_course['name'] . " (" . $row_course['batch'] . ")</option>";
                                }
                            } else {
                                echo "<option value=''>No courses found</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="sroll">Roll-Number</label>
                        <input type="text" class="form-control" id="sroll" name="sroll" placeholder="examroll" required>
                    </div>
                    <button class="btn btn-primary" name="addstudent">Add</button>
                </form>
                <button class="btn btn-info mb-3" onclick="toggleStudentRecords()">View Student Records</button>

                <div id="studentRecords" style="display: none;">
                    <div class="table-responsive border">
                        <table class="table table-hover text-center">
                            <thead class="thead-light">
                                <tr>
                                    <th>Name</th>
                                    <th>Course</th>
                                    <th>Exam Roll No.</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $select_students = "SELECT students.name AS student_name, courses.name AS course_name, courses.batch, students.examroll, students.cid
                                                    FROM students
                                                    JOIN courses ON students.cid = courses.cid
                                                    ORDER BY courses.batch, students.examroll";
                                $result_students = mysqli_query($conn, $select_students);
                                if (mysqli_num_rows($result_students) > 0) {
                                    while ($row_student = mysqli_fetch_assoc($result_students)) {
                                        echo "
                                        <tr>
                                            <td>" . $row_student['student_name'] . "</td>
                                            <td>" . $row_student['course_name'] . " (" . $row_student['batch'] . ")</td>
                                            <td>" . $row_student['examroll'] . "</td>
                                            <td>
                                                <form action='' method='post' style='display:inline-block;'>
                                                    <input type='hidden' name='deletestudent' value='" . $row_student['examroll'] . "'>
                                                    <button class='btn btn-danger' name='delete'>Delete</button>
                                                </form>
                                                <button class='btn btn-warning' data-toggle='modal' data-target='#editModal" . $row_student['examroll'] . "'>Edit</button>
                                                <!-- Edit Modal -->
                                                <div class='modal fade' id='editModal" . $row_student['examroll'] . "' tabindex='-1' role='dialog' aria-labelledby='editModalLabel" . $row_student['examroll'] . "' aria-hidden='true'>
                                                    <div class='modal-dialog' role='document'>
                                                        <div class='modal-content'>
                                                            <div class='modal-header'>
                                                                <h5 class='modal-title' id='editModalLabel" . $row_student['examroll'] . "'>Edit Student</h5>
                                                                <button type='button' class='close' data-dismiss='modal' aria-label='Close'>
                                                                    <span aria-hidden='true'>&times;</span>
                                                                </button>
                                                            </div>
                                                            <div class='modal-body'>
                                                                <form action='' method='post'>
                                                                    <div class='form-group'>
                                                                        <label for='editName" . $row_student['examroll'] . "'>Name</label>
                                                                        <input type='text' class='form-control' id='editName" . $row_student['examroll'] . "' name='name' value='" . $row_student['student_name'] . "' required>
                                                                    </div>
                                                                    <div class='form-group'>
                                                                        <label for='editCID" . $row_student['examroll'] . "'>Course ID</label>
                                                                        <input type='text' class='form-control' id='editCID" . $row_student['examroll'] . "' name='cid' value='" . $row_student['cid'] . "' required>
                                                                    </div>
                                                                    <input type='hidden' name='examroll' value='" . $row_student['examroll'] . "'>
                                                                    <button type='submit' name='editstudent' class='btn btn-primary'>Save changes</button>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='4'>No students found</td></tr>";
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
