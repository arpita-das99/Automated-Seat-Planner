<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Import Students from Excel</title>
    <link rel="stylesheet" href="../css/common.css">
</head>
<body>
    <div class="container">
        <h1>Import Students Data</h1>
        <form method="POST" action="../actions/import_excel_action.php" enctype="multipart/form-data">
            <label for="excel_file">Choose Excel File:</label>
            <input type="file" name="excel_file" id="excel_file" accept=".xlsx, .xls" required>
            <input type="submit" value="Import">
        </form>
    </div>
</body>
</html>
