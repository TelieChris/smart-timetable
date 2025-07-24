<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart Timetable - Home</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #e0eafc 0%, #cfdef3 100%);
            min-height: 100vh;
        }
        .hero {
            padding: 60px 0 40px 0;
            text-align: center;
        }
        .hero-title {
            font-size: 2.8rem;
            font-weight: 700;
            color: #1a3c6c;
        }
        .hero-desc {
            font-size: 1.3rem;
            color: #3a3a3a;
            margin-bottom: 2rem;
        }
        .dashboard-cards {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 2rem;
        }
        .dashboard-card {
            min-width: 260px;
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
            font-size: 2.5rem;
            color: #0d6efd;
            margin-bottom: 1rem;
        }
        .dashboard-card .btn {
            margin-top: 1.2rem;
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
            <a class="navbar-brand fw-bold" href="#">Smart Timetable</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="admin/dashboard.php">Admin</a></li>
                    <li class="nav-item"><a class="nav-link" href="#">Teacher</a></li>
                    <li class="nav-item"><a class="nav-link" href="#">Student</a></li>
                </ul>
            </div>
        </div>
    </nav>
    <section class="hero">
        <div class="container">
            <h1 class="hero-title mb-3">Welcome to Smart Timetable</h1>
            <p class="hero-desc">A modern, intelligent platform for managing and generating school timetables for O-Level and TVET. Streamline your scheduling, avoid conflicts, and empower your school community.</p>
            <div class="dashboard-cards mt-5">
                <div class="dashboard-card">
                    <div class="icon"><i class="bi bi-shield-lock"></i></div>
                    <h5 class="fw-bold">Admin Panel</h5>
                    <p>Manage teachers, rooms, modules, subjects, and generate timetables with advanced controls.</p>
                    <a href="admin/dashboard.php" class="btn btn-primary w-100">Go to Admin</a>
                </div>
                <div class="dashboard-card">
                    <div class="icon"><i class="bi bi-person-badge"></i></div>
                    <h5 class="fw-bold">Teacher Portal</h5>
                    <p>View your personalized timetable, class assignments, and receive notifications.</p>
                    <a href="#" class="btn btn-outline-primary w-100 disabled">Coming Soon</a>
                </div>
                <div class="dashboard-card">
                    <div class="icon"><i class="bi bi-people"></i></div>
                    <h5 class="fw-bold">Student Portal</h5>
                    <p>Access your class timetable, room assignments, and stay up to date with changes.</p>
                    <a href="#" class="btn btn-outline-primary w-100 disabled">Coming Soon</a>
                </div>
            </div>
        </div>
    </section>
    <footer class="text-center py-4 mt-5 bg-white border-top">
        <div class="container">
            <span class="text-muted">&copy; <?= date('Y') ?> Smart Timetable. All rights reserved.</span>
        </div>
    </footer>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 