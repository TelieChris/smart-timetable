<?php
require_once '../includes/db.php';

// Add preferred_time column if not exists
$conn->query("ALTER TABLE modules ADD COLUMN IF NOT EXISTS preferred_time ENUM('Any','Morning','Afternoon') DEFAULT 'Any'");
// Add teacher_id column if not exists
$conn->query("ALTER TABLE modules ADD COLUMN IF NOT EXISTS teacher_id INT NULL");
// Add class_ids column if not exists
$conn->query("ALTER TABLE modules ADD COLUMN IF NOT EXISTS class_ids VARCHAR(255) NULL");
// Fetch teachers
$teacher_result = $conn->query("SELECT * FROM teachers ORDER BY name ASC");
$all_teachers = $teacher_result ? $teacher_result->fetch_all(MYSQLI_ASSOC) : [];
// Fetch classes
$class_result = $conn->query("SELECT * FROM rooms ORDER BY name ASC");
$all_classes = $class_result ? $class_result->fetch_all(MYSQLI_ASSOC) : [];

// Handle add, edit, delete actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $preferred_time = $_POST['preferred_time'] ?? 'Any';
    $teacher_id = $_POST['teacher_id'] ?? 'NULL';
    $class_ids = isset($_POST['class_ids']) ? (array)$_POST['class_ids'] : [];
    $class_ids = array_filter($class_ids, function($v) { return $v !== '' && $v !== null; });
    $class_ids_str = $class_ids ? implode(',', array_map('intval', $class_ids)) : '';
    if (isset($_POST['add'])) {
        $name = $conn->real_escape_string($_POST['name']);
        $module_code = $conn->real_escape_string($_POST['module_code']);
        $periods = (int)$_POST['periods'];
        $conn->query("INSERT INTO modules (name, module_code, periods_per_week, preferred_time, teacher_id, class_ids) VALUES ('$name', '$module_code', $periods, '$preferred_time', " . ($teacher_id === 'NULL' ? 'NULL' : (int)$teacher_id) . ", '$class_ids_str')");
    } elseif (isset($_POST['edit'])) {
        $id = (int)$_POST['id'];
        $name = $conn->real_escape_string($_POST['name']);
        $module_code = $conn->real_escape_string($_POST['module_code']);
        $periods = (int)$_POST['periods'];
        $conn->query("UPDATE modules SET name='$name', module_code='$module_code', periods_per_week=$periods, preferred_time='$preferred_time', teacher_id=" . ($teacher_id === 'NULL' ? 'NULL' : (int)$teacher_id) . ", class_ids='$class_ids_str' WHERE id=$id");
    } elseif (isset($_POST['delete'])) {
        $id = (int)$_POST['id'];
        $conn->query("DELETE FROM modules WHERE id=$id");
    }
    header('Location: modules.php');
    exit;
}

// Fetch all modules
$result = $conn->query("SELECT * FROM modules ORDER BY id DESC");
$modules = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage TVET Modules</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-success mb-4">
        <div class="container-fluid">
            <a class="navbar-brand" href="dashboard.php?type=tvet">Smart Timetable</a>
        </div>
    </nav>
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2>TVET Modules</h2>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">Add Module</button>
        </div>
        <table class="table table-bordered table-hover bg-white">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Module Name</th>
                    <th>Module Code</th>
                    <th>Periods/Week</th>
                    <th>Preferred Time</th>
                    <th>Teacher</th>
                    <th>Classes</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($modules as $i => $module): ?>
                <tr>
                    <td><?= $i+1 ?></td>
                    <td><?= htmlspecialchars($module['name']) ?></td>
                    <td><?= htmlspecialchars($module['module_code']) ?></td>
                    <td><?= $module['periods_per_week'] ?></td>
                    <td><?= htmlspecialchars($module['preferred_time'] ?? 'Any') ?></td>
                    <td>
                        <?php
                        $teacher_name = '';
                        if (!empty($module['teacher_id'])) {
                            foreach ($all_teachers as $t) {
                                if ($t['id'] == $module['teacher_id']) {
                                    $teacher_name = htmlspecialchars($t['name']);
                                    break;
                                }
                            }
                        }
                        echo $teacher_name ?: '-';
                        ?>
                    </td>
                    <td>
                        <?php
                        $class_names = [];
                        if (!empty($module['class_ids'])) {
                            $ids = explode(',', $module['class_ids']);
                            foreach ($all_classes as $c) {
                                if (in_array($c['id'], $ids)) {
                                    $class_names[] = htmlspecialchars($c['name']);
                                }
                            }
                        }
                        echo $class_names ? implode(', ', $class_names) : '-';
                        ?>
                    </td>
                    <td>
                        <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#editModal<?= $module['id'] ?>">Edit</button>
                        <form method="post" action="" style="display:inline-block">
                            <input type="hidden" name="id" value="<?= $module['id'] ?>">
                            <button type="submit" name="delete" class="btn btn-sm btn-danger" onclick="return confirm('Delete this module?')">Delete</button>
                        </form>
                    </td>
                </tr>
                <!-- Edit Modal -->
                <div class="modal fade" id="editModal<?= $module['id'] ?>" tabindex="-1" aria-labelledby="editModalLabel<?= $module['id'] ?>" aria-hidden="true">
                  <div class="modal-dialog">
                    <div class="modal-content">
                      <form method="post" action="">
                        <div class="modal-header">
                          <h5 class="modal-title" id="editModalLabel<?= $module['id'] ?>">Edit Module</h5>
                          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                          <input type="hidden" name="id" value="<?= $module['id'] ?>">
                          <div class="mb-3">
                            <label class="form-label">Module Name</label>
                            <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($module['name']) ?>" required>
                          </div>
                          <div class="mb-3">
                            <label class="form-label">Module Code</label>
                            <input type="text" name="module_code" class="form-control" value="<?= htmlspecialchars($module['module_code']) ?>" required>
                          </div>
                          <div class="mb-3">
                            <label class="form-label">Periods per Week</label>
                            <input type="number" name="periods" class="form-control" value="<?= $module['periods_per_week'] ?>" min="1" required>
                          </div>
                          <div class="mb-3">
                            <label class="form-label">Preferred Time</label>
                            <select name="preferred_time" class="form-select">
                                <option value="Any" <?= ($module['preferred_time'] ?? 'Any') == 'Any' ? 'selected' : '' ?>>Any</option>
                                <option value="Morning" <?= ($module['preferred_time'] ?? '') == 'Morning' ? 'selected' : '' ?>>Morning</option>
                                <option value="Afternoon" <?= ($module['preferred_time'] ?? '') == 'Afternoon' ? 'selected' : '' ?>>Afternoon</option>
                            </select>
                          </div>
                          <div class="mb-3">
                            <label class="form-label">Teacher</label>
                            <select name="teacher_id" class="form-select">
                                <option value="NULL">- None -</option>
                                <?php foreach ($all_teachers as $t): ?>
                                    <option value="<?= $t['id'] ?>" <?= (!empty($module['teacher_id']) && $module['teacher_id'] == $t['id']) ? 'selected' : '' ?>><?= htmlspecialchars($t['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                          </div>
                          <div class="mb-3">
                            <label class="form-label">Classes</label>
                            <select name="class_ids[]" class="form-select" multiple required>
                                <?php foreach ($all_classes as $c): ?>
                                    <option value="<?= $c['id'] ?>" <?= (!empty($module['class_ids']) && in_array($c['id'], explode(',', $module['class_ids']))) ? 'selected' : '' ?>><?= htmlspecialchars($c['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <input type="hidden" name="class_ids[]" value="">
                            <small class="form-text text-muted">Hold Ctrl (Windows) or Cmd (Mac) to select multiple classes.</small>
                          </div>
                        </div>
                        <div class="modal-footer">
                          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                          <button type="submit" name="edit" class="btn btn-success">Save Changes</button>
                        </div>
                      </form>
                    </div>
                  </div>
                </div>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <!-- Add Modal -->
    <div class="modal fade" id="addModal" tabindex="-1" aria-labelledby="addModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <form method="post" action="">
            <div class="modal-header">
              <h5 class="modal-title" id="addModalLabel">Add Module</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <div class="mb-3">
                <label class="form-label">Module Name</label>
                <input type="text" name="name" class="form-control" required>
              </div>
              <div class="mb-3">
                <label class="form-label">Module Code</label>
                <input type="text" name="module_code" class="form-control" required>
              </div>
              <div class="mb-3">
                <label class="form-label">Periods per Week</label>
                <input type="number" name="periods" class="form-control" min="1" required>
              </div>
              <div class="mb-3">
                <label class="form-label">Preferred Time</label>
                <select name="preferred_time" class="form-select">
                    <option value="Any">Any</option>
                    <option value="Morning">Morning</option>
                    <option value="Afternoon">Afternoon</option>
                </select>
              </div>
              <div class="mb-3">
                <label class="form-label">Teacher</label>
                <select name="teacher_id" class="form-select">
                    <option value="NULL">- None -</option>
                    <?php foreach ($all_teachers as $t): ?>
                        <option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['name']) ?></option>
                    <?php endforeach; ?>
                </select>
              </div>
              <div class="mb-3">
                <label class="form-label">Classes</label>
                <select name="class_ids[]" class="form-select" multiple required>
                    <?php foreach ($all_classes as $c): ?>
                        <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <input type="hidden" name="class_ids[]" value="">
                <small class="form-text text-muted">Hold Ctrl (Windows) or Cmd (Mac) to select multiple classes.</small>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
              <button type="submit" name="add" class="btn btn-primary">Add Module</button>
            </div>
          </form>
        </div>
      </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 