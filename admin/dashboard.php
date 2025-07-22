<?php
$type = $_GET['type'] ?? 'olevel';
$title = $type === 'tvet' ? 'TVET' : 'O-Level';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - <?php echo $title; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
        <div class="container-fluid">
            <a class="navbar-brand" href="../index.php">Smart Timetable</a>
        </div>
    </nav>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow p-4">
                    <h2 class="mb-4">Admin Dashboard (<?php echo $title; ?>)</h2>
                    <div class="d-grid gap-3">
                        <?php if ($type === 'tvet'): ?>
                            <a href="modules.php" class="btn btn-success btn-lg">Manage Modules</a>
                        <?php else: ?>
                            <a href="subjects.php" class="btn btn-primary btn-lg">Manage Subjects</a>
                        <?php endif; ?>
                        <a href="generate.php?type=<?php echo $type; ?>" class="btn btn-secondary btn-lg">Generate Timetable</a>
                        <a href="timetable.php?type=<?php echo $type; ?>" class="btn btn-outline-dark btn-lg">View Timetable</a>
                        <a href="teachers.php" class="btn btn-info btn-lg">Manage Teachers</a>
                        <a href="rooms.php" class="btn btn-warning btn-lg">Manage Rooms</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 