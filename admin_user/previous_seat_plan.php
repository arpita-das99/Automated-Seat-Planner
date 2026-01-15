<?php
session_start();
require_once('../config/config.php');

$sql = "SELECT DISTINCT r.roomno, sp.created_at 
        FROM seat_plan sp 
        JOIN rooms r ON sp.rid = r.rid 
        ORDER BY sp.created_at DESC";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo "Error preparing statement: ". $conn->error;
    exit();
}
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Previous Seat Plans</title>
    <link rel="stylesheet" href="../css/dashboard.css">
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }
        td, th {
            border: 1px solid black;
            padding: 8px;
            text-align: left;
        }
    </style>
</head>
<body>
<?php include('../includes/navbar.php'); ?>
    <h1>Previous Seat Plans</h1>
    <table>
        <thead>
            <tr>
                <th>Room</th>
                <th>Date Created</th>
                <th>View</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['roomno']); ?></td>
                    <td><?php echo htmlspecialchars($row['created_at']); ?></td>
                    <td><a href="display_seat_plan.php?room=<?php echo urlencode($row['roomno']); ?>&created_at=<?php echo urlencode($row['created_at']); ?>">View</a></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</body>
</html>
<?php
$stmt->close();
?>
