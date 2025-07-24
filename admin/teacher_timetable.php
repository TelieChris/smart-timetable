<?php
require_once '../includes/db.php';
$teacher_id = isset($_GET['teacher_id']) ? intval($_GET['teacher_id']) : 0;
$type = $_GET['type'] ?? 'olevel';
$title = $type === 'tvet' ? 'TVET' : 'O-Level';
$days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
$hours_per_day = 10;
$break_after = 4;
$lunch_after = 7;
$default_start_time = '08:00';
$start_time = $_GET['start_time'] ?? $default_start_time;

function add_minutes($time, $mins) {
    $t = strtotime($time) + $mins * 60;
    return date('H:i', $t);
}

// Get teacher name
$teacher_name = '';
$res = $conn->query("SELECT name FROM teachers WHERE id=$teacher_id");
if ($res && $row = $res->fetch_assoc()) {
    $teacher_name = $row['name'];
}

// Fetch all classes
$class_result = $conn->query("SELECT * FROM rooms");
$classes = $class_result ? $class_result->fetch_all(MYSQLI_ASSOC) : [];
$class_map = [];
foreach ($classes as $class) {
    $class_map[$class['id']] = $class['name'];
}

// Fetch timetable for this teacher
$timetable = [];
$query = "SELECT * FROM timetable WHERE type='$type'";
$query .= " ORDER BY FIELD(day, 'Monday','Tuesday','Wednesday','Thursday','Friday'), hour ASC";
$result = $conn->query($query);
while ($row = $result->fetch_assoc()) {
    // Get teacher_id for this entry
    $entry_teacher_id = null;
    if ($type === 'tvet') {
        $mod = $conn->query("SELECT teacher_id FROM modules WHERE id=" . intval($row['subject_module_id']));
        if ($mod && $mod_row = $mod->fetch_assoc()) {
            $entry_teacher_id = $mod_row['teacher_id'];
        }
    } else {
        $subj = $conn->query("SELECT teacher_id FROM subjects WHERE id=" . intval($row['subject_module_id']));
        if ($subj && $subj_row = $subj->fetch_assoc()) {
            $entry_teacher_id = $subj_row['teacher_id'];
        }
    }
    if ($entry_teacher_id == $teacher_id) {
        $timetable[$row['day']][$row['hour']] = [
            'name' => $row['subject_module_name'],
            'class_id' => $row['class_id'] ?? null,
            'subject_module_id' => $row['subject_module_id'] ?? null
        ];
    }
}

// Generate hour labels
$hour_labels = [];
$time = $start_time;
for ($h = 1; $h <= $hours_per_day; $h++) {
    $end = add_minutes($time, 40);
    $hour_labels[$h] = "$time - $end";
    $time = $end;
    if ($h == $break_after) $time = add_minutes($time, 15); // break
    if ($h == $lunch_after) $time = add_minutes($time, 40); // lunch
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Timetable - <?= htmlspecialchars($teacher_name) ?> (<?= $title ?>)</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
    <style>
        .break-row, .lunch-row { background: #f1f5f9; font-weight: bold; text-align: center; }
        .timetable-table th, .timetable-table td { vertical-align: middle; text-align: center; }
        .class-label { font-size: 0.9em; color: #555; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-info mb-4">
        <div class="container-fluid">
            <a class="navbar-brand" href="teachers.php">Smart Timetable</a>
        </div>
    </nav>
    <div class="container">
        <h2 class="mb-4">Timetable for <?= htmlspecialchars($teacher_name) ?> (<?= $title ?>)</h2>
        <form method="get" class="mb-3 row g-3 align-items-center">
            <input type="hidden" name="teacher_id" value="<?= $teacher_id ?>">
            <div class="col-auto">
                <label for="type" class="col-form-label">Type:</label>
            </div>
            <div class="col-auto">
                <select name="type" id="type" class="form-select" onchange="this.form.submit()">
                    <option value="olevel"<?= $type === 'olevel' ? ' selected' : '' ?>>O-Level</option>
                    <option value="tvet"<?= $type === 'tvet' ? ' selected' : '' ?>>TVET</option>
                </select>
            </div>
        </form>
        <div class="table-responsive">
            <table class="table table-bordered timetable-table bg-white">
                <thead class="table-light">
                    <tr>
                        <th>Hour</th>
                        <?php foreach ($days as $day): ?>
                        <th><?= $day ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php for ($h = 1; $h <= $hours_per_day; $h++): ?>
                        <?php if ($h == $break_after + 1): ?>
                        <tr class="break-row">
                            <td colspan="6">Break (15 min)</td>
                        </tr>
                        <?php endif; ?>
                        <?php if ($h == $lunch_after + 1): ?>
                        <tr class="lunch-row">
                            <td colspan="6">Lunch (40 min)</td>
                        </tr>
                        <?php endif; ?>
                        <tr>
                            <td><strong><?= $hour_labels[$h] ?? ("Hour $h") ?></strong></td>
                            <?php foreach ($days as $day): ?>
                                <td>
                                    <?php if (isset($timetable[$day][$h])): ?>
                                        <?= htmlspecialchars($timetable[$day][$h]['name']) ?><br>
                                        <?php if (!empty($timetable[$day][$h]['class_id']) && isset($class_map[$timetable[$day][$h]['class_id']])): ?>
                                            <span class="class-label">Class: <?= htmlspecialchars($class_map[$timetable[$day][$h]['class_id']]) ?></span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endfor; ?>
                </tbody>
            </table>
        </div>
        <a href="teachers.php" class="btn btn-secondary mt-3">Back to Teachers</a>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 