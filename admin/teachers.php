<?php
require_once '../includes/db.php';

// Handle add, edit, delete actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add'])) {
        $name = $conn->real_escape_string($_POST['name']);
        $conn->query("INSERT INTO teachers (name) VALUES ('$name')");
    } elseif (isset($_POST['edit'])) {
        $id = (int)$_POST['id'];
        $name = $conn->real_escape_string($_POST['name']);
        $conn->query("UPDATE teachers SET name='$name' WHERE id=$id");
    } elseif (isset($_POST['delete'])) {
        $id = (int)$_POST['id'];
        $conn->query("DELETE FROM teachers WHERE id=$id");
    }
    header('Location: teachers.php');
    exit;
}

// Fetch all teachers
$result = $conn->query("SELECT * FROM teachers ORDER BY id DESC");
$teachers = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Teachers</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #e0eafc 0%, #cfdef3 100%);
            min-height: 100vh;
        }
        .teachers-hero {
            padding: 40px 0 20px 0;
            text-align: center;
        }
        .teachers-title {
            font-size: 2.2rem;
            font-weight: 700;
            color: #1a3c6c;
        }
        .teachers-card {
            background: #fff;
            border-radius: 1rem;
            box-shadow: 0 2px 16px rgba(60,60,100,0.08);
            padding: 2rem 1.5rem;
            margin-bottom: 2rem;
        }
        .table th, .table td {
            vertical-align: middle;
            text-align: center;
        }
        @media (max-width: 768px) {
            .teachers-card {
                padding: 1rem 0.2rem;
            }
            .teachers-title {
                font-size: 1.3rem;
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
                    <li class="nav-item"><a class="nav-link" href="dashboard.php">Admin</a></li>
                    <li class="nav-item"><a class="nav-link active" href="#">Teachers</a></li>
                </ul>
            </div>
        </div>
    </nav>
    <section class="teachers-hero">
        <div class="container">
            <h1 class="teachers-title mb-3">Teachers</h1>
            <div class="teachers-card mx-auto" style="max-width: 900px;">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h2 class="h4">Teacher List</h2>
                    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addModal"><i class="bi bi-plus-lg"></i> Add Teacher</button>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover bg-white">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($teachers as $i => $teacher): ?>
                            <tr>
                                <td><?= $i+1 ?></td>
                                <td><?= htmlspecialchars($teacher['name']) ?></td>
                                <td>
                                    <a href="teacher_timetable.php?teacher_id=<?= $teacher['id'] ?>" class="btn btn-sm btn-info">View Timetable</a>
                                    <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#editModal<?= $teacher['id'] ?>">Edit</button>
                                    <form method="post" action="" style="display:inline-block">
                                        <input type="hidden" name="id" value="<?= $teacher['id'] ?>">
                                        <button type="submit" name="delete" class="btn btn-sm btn-danger" onclick="return confirm('Delete this teacher?')">Delete</button>
                                    </form>
                                </td>
                            </tr>
                            <!-- Edit Modal -->
                <div class="modal fade" id="editModal<?= $teacher['id'] ?>" tabindex="-1" aria-labelledby="editModalLabel<?= $teacher['id'] ?>" aria-hidden="true">
                  <div class="modal-dialog">
                    <div class="modal-content">
                      <form method="post" action="">
                        <div class="modal-header">
                          <h5 class="modal-title" id="editModalLabel<?= $teacher['id'] ?>">Edit Teacher</h5>
                          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                          <input type="hidden" name="id" value="<?= $teacher['id'] ?>">
                          <div class="mb-3">
                            <label class="form-label">Name</label>
                            <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($teacher['name']) ?>" required>
                          </div>
                        </div>
                        <div class="modal-footer">
                          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                          <button type="submit" name="edit" class="btn btn-primary">Save Changes</button>
                        </div>
                      </form>
                    </div>
                  </div>
                </div>
                <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
    <!-- Add Modal -->
    <div class="modal fade" id="addModal" tabindex="-1" aria-labelledby="addModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <form method="post" action="">
            <div class="modal-header">
              <h5 class="modal-title" id="addModalLabel">Add Teacher</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <div class="mb-3">
                <label class="form-label">Name</label>
                <input type="text" name="name" class="form-control" required>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
              <button type="submit" name="add" class="btn btn-success">Add Teacher</button>
            </div>
          </form>
        </div>
      </div>
    </div>
    <footer class="text-center py-4 mt-5 bg-white border-top">
        <div class="container">
            <span class="text-muted">&copy; <?= date('Y') ?> Smart Timetable. All rights reserved.</span>
        </div>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 