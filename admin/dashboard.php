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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #e0eafc 0%, #cfdef3 100%);
            min-height: 100vh;
        }
        .dashboard-hero {
            padding: 40px 0 20px 0;
            text-align: center;
        }
        .dashboard-title {
            font-size: 2.2rem;
            font-weight: 700;
            color: #1a3c6c;
        }
        .dashboard-cards {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 2rem;
            margin-top: 2rem;
        }
        .dashboard-card {
            min-width: 240px;
            max-width: 320px;
            background: #fff;
            border-radius: 1rem;
            box-shadow: 0 2px 16px rgba(60,60,100,0.08);
            padding: 2rem 1.5rem;
            text-align: center;
            transition: box-shadow 0.2s;
        }
        .dashboard-card:hover {
            box-shadow: 0 4px 32px rgba(60,60,100,0.16);
        }
        .dashboard-card .icon {
            font-size: 2.2rem;
            color: #0d6efd;
            margin-bottom: 1rem;
        }
        .dashboard-card .btn {
            margin-top: 1.2rem;
        }
        .type-switcher {
            margin-bottom: 2rem;
        }
        @media (max-width: 768px) {
            .dashboard-cards {
                flex-direction: column;
                gap: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="../index.php">Smart Timetable</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="../index.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link active" href="#">Admin</a></li>
                </ul>
            </div>
        </div>
    </nav>
    <section class="dashboard-hero">
        <div class="container">
            <h1 class="dashboard-title mb-3">Admin Dashboard (<?php echo $title; ?>)</h1>
            <div class="type-switcher d-flex justify-content-center align-items-center gap-3 mb-4">
                <span class="fw-semibold">Timetable Type:</span>
                <a href="dashboard.php?type=olevel" class="btn btn-sm <?= $type === 'olevel' ? 'btn-primary' : 'btn-outline-primary' ?>">O-Level</a>
                <a href="dashboard.php?type=tvet" class="btn btn-sm <?= $type === 'tvet' ? 'btn-success' : 'btn-outline-success' ?>">TVET</a>
            </div>
            <p class="lead">Manage all aspects of your school's timetable, teachers, rooms, and more.</p>
            <div class="dashboard-cards mt-5">
                <div class="dashboard-card">
                    <div class="icon"><i class="bi bi-calendar-week"></i></div>
                    <h5 class="fw-bold">Generate Timetable</h5>
                    <p>Create and optimize the timetable for <?php echo $title; ?> classes.</p>
                    <a href="generate.php?type=<?php echo $type; ?>" class="btn btn-primary w-100">Generate</a>
                </div>
                <div class="dashboard-card">
                    <div class="icon"><i class="bi bi-table"></i></div>
                    <h5 class="fw-bold">View Timetable</h5>
                    <p>See the current timetable for all classes and make manual adjustments.</p>
                    <a href="timetable.php?type=<?php echo $type; ?>" class="btn btn-outline-primary w-100">View</a>
                </div>
                <?php if ($type === 'tvet'): ?>
                <div class="dashboard-card">
                    <div class="icon"><i class="bi bi-book"></i></div>
                    <h5 class="fw-bold">Manage Modules</h5>
                    <p>Add, edit, or remove TVET modules and their details.</p>
                    <a href="modules.php" class="btn btn-success w-100">Modules</a>
                </div>
                <?php else: ?>
                <div class="dashboard-card">
                    <div class="icon"><i class="bi bi-journal-text"></i></div>
                    <h5 class="fw-bold">Manage Subjects</h5>
                    <p>Add, edit, or remove O-Level subjects and their details.</p>
                    <a href="subjects.php" class="btn btn-primary w-100">Subjects</a>
                </div>
                <?php endif; ?>
                <div class="dashboard-card">
                    <div class="icon"><i class="bi bi-person"></i></div>
                    <h5 class="fw-bold">Manage Teachers</h5>
                    <p>Add, edit, or remove teachers and assign them to classes or modules.</p>
                    <a href="teachers.php" class="btn btn-info w-100">Teachers</a>
                </div>
                <div class="dashboard-card">
                    <div class="icon"><i class="bi bi-building"></i></div>
                    <h5 class="fw-bold">Manage Rooms</h5>
                    <p>Add, edit, or remove classrooms and assign them to classes.</p>
                    <a href="rooms.php" class="btn btn-warning w-100">Rooms</a>
                </div>
            </div>
        </div>
    </section>
    <footer class="text-center py-4 mt-5 bg-white border-top">
        <div class="container">
            <span class="text-muted">&copy; <?= date('Y') ?> Smart Timetable. All rights reserved.</span>
        </div>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 