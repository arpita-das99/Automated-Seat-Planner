<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['email'])) {
    header('Location: ../index.php');
    exit();
}

// Retrieve the user's name from the session if it exists
$name = isset($_SESSION['name']) ? $_SESSION['name'] : 'Guest';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard</title> 
    <link rel="stylesheet" href="../css/common.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <?php include '../includes/link.php'; ?>
    <style>
        #content .grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            width: 100%;
            max-width: 800px;
            margin: 0 auto; /* Center the grid */
        }
        #content .container {
            margin-top: 50px;
        }
        #content article {
            background-color: #b3d1e0;
            padding: 40px;
            border-radius: 10px;
            text-align: center;
            color: #333;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }

        #content article h2 {
            margin-bottom: 1rem;
            font-size: 1.5em;
            color: #333;
        }

        #content .button {
            display: inline-block;
            padding: 10px 20px;
            font-size: 1em;
            color: #333;
            background-color: #b3d1e0;
            border-radius: 5px;
            border: 1.5px solid #333;
            text-decoration: none;
            transition: background-color 0.3s;
        }

        #content .button:hover {
            background-color: rgba(0, 0, 0, 0.1);
        }
    </style>
</head>

<body>
    <div class="wrapper">
        <!-- Sidebar -->
        <nav id="sidebar">
            <div class="sidebar-header">
                <h4>DASHBOARD</h4>
            </div>
            <ul class="list-unstyled components">
                <li>
                    <a href=""><img width="23" height="23" src="https://img.icons8.com/ios-glyphs/30/FFFFFF/user--v1.png" alt="user--v1"/> <?php echo htmlspecialchars($name); ?></a>
                </li>
            </ul>
        </nav>

        <!-- Page Content -->
        <div id="content">
            <nav class="navbar navbar-expand-lg navbar-light bg-light">
                <div class="container-fluid">
                    <button type="button" id="sidebarCollapse" class="btn btn-info">
                        <img src="https://img.icons8.com/ios-filled/19/ffffff/menu--v3.png"/>
                    </button>
                    <span class="page-name"> Home</span>
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
            <main class="container">
                <section class="grid">
                    <article>
                        <h2>Courses</h2>
                        <a href="add_courses.php" class="button">Manage</a>
                    </article>
                    <article>
                        <h2>Students</h2>
                        <a href="add_students.php" class="button">Manage</a>
                    </article>
                    <article>
                        <h2>Rooms</h2>
                        <a href="add_rooms.php" class="button">Manage</a>
                    </article>
                    <article>
                        <h2>Seat Plan</h2>
                        <a href="seat_plan.php" class="button">Generate</a>
                    </article>
                </section>
            </main>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>
    <script>
        $(document).ready(function () {
            $('#sidebarCollapse').on('click', function () {
                $('#sidebar').toggleClass('active');
            });
        });
    </script>
</body>

</html>
