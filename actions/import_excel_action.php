<?php
session_start();

require '../vendor/autoload.php';
require_once('../config/config.php');

use PhpOffice\PhpSpreadsheet\IOFactory;

if (!isset($_SESSION['email'])) {
    header('Location: login.php');
    exit();
}

// Ensure file upload and POST method
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_FILES['excel_file']) && $_FILES['excel_file']['error'] == 0) {
        $file = $_FILES['excel_file']['tmp_name'];
        
        try {
            $spreadsheet = IOFactory::load($file);
            $sheet = $spreadsheet->getActiveSheet();
            $data = $sheet->toArray(null, true, true, true);
        
            // Map column indices
            $headerMap = [
                'examroll' => 0,
                'name' => 1,
                'batch' => 2,
            ];
        
            // Prepare the insert statement with ON DUPLICATE KEY UPDATE
            $stmt = $conn->prepare("INSERT INTO students (examroll, name, batch) VALUES (?,?,?) ON DUPLICATE KEY UPDATE name=VALUES(name), batch=VALUES(batch)");
        
            if (!$stmt) {
                throw new Exception("Failed to prepare statement");
            }
        
            foreach ($data as $row) {
                $examroll = $row[$headerMap['examroll']] ?? null;
                $name = $row[$headerMap['name']] ?? null;
                $batch = $row[$headerMap['batch']] ?? null;
        
                if ($examroll && $name && $batch) {
                    $stmt->bind_param("sss", $examroll, $name, $batch);
                    if (!$stmt->execute()) {
                        throw new Exception("Failed to execute statement");
                    }
                } else {
                    // Handle missing data appropriately
                    echo "Skipping row due to missing data: " . json_encode($row) . "<br>";
                }
            }
        
            $stmt->close(); // Close the statement after use
        
            // Redirect after successful processing
            header('Location: ../views/index.php');
            exit();
        
        } catch (Exception $e) {
            echo 'Error processing Excel data: ' . $e->getMessage();
        }
    } else {
        echo "File upload error.";
    }
} else {
    echo "Invalid request method.";
}
?>
