<?php
require_once '../includes/db.php';
$type = $_GET['type'] ?? 'olevel';
$title = $type === 'tvet' ? 'TVET' : 'O-Level';
$days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
$hours_per_day = 10;
$break_after = 4;
$lunch_after = 7;

// Get start time from query or default
$default_start_time = '08:00';
$start_time = $_GET['start_time'] ?? $default_start_time;

function add_minutes($time, $mins) {
    $t = strtotime($time) + $mins * 60;
    return date('H:i', $t);
}

// Generate hour labels based on start time
$hour_labels = [];
$time = $start_time;
for ($h = 1; $h <= $hours_per_day; $h++) {
    $end = add_minutes($time, 40);
    $hour_labels[$h] = "$time - $end";
    $time = $end;
    if ($h == $break_after) $time = add_minutes($time, 15); // break
    if ($h == $lunch_after) $time = add_minutes($time, 40); // lunch
}

// Fetch all classes
$class_result = $conn->query("SELECT * FROM rooms");
$classes = $class_result ? $class_result->fetch_all(MYSQLI_ASSOC) : [];
$class_map = [];
foreach ($classes as $class) {
    $class_map[$class['id']] = $class['name'];
}

// Class filter
$selected_class = isset($_GET['class_id']) ? $_GET['class_id'] : '';

// Fetch timetable with class_id
$timetable = [];
$query = "SELECT * FROM timetable WHERE type='$type'";
if ($selected_class !== '' && $selected_class !== 'all') {
    $query .= " AND class_id=" . intval($selected_class);
}
$query .= " ORDER BY FIELD(day, 'Monday','Tuesday','Wednesday','Thursday','Friday'), hour ASC";
$result = $conn->query($query);
while ($row = $result->fetch_assoc()) {
    $timetable[$row['day']][$row['hour']] = [
        'name' => $row['subject_module_name'],
        'class_id' => $row['class_id'] ?? null
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Timetable - <?php echo $title; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
    <style>
        .break-row, .lunch-row { background: #f1f5f9; font-weight: bold; text-align: center; }
        .timetable-table th, .timetable-table td { vertical-align: middle; text-align: center; }
        .class-label { font-size: 0.9em; color: #555; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
        <div class="container-fluid">
            <a class="navbar-brand" href="dashboard.php?type=<?php echo $type; ?>">Smart Timetable</a>
        </div>
    </nav>
    <div class="container">
        <h2 class="mb-4">Weekly Timetable (<?php echo $title; ?>)</h2>
        <form method="get" class="mb-3 row g-3 align-items-center">
            <input type="hidden" name="type" value="<?php echo htmlspecialchars($type); ?>">
            <input type="hidden" name="start_time" value="<?php echo htmlspecialchars($start_time); ?>">
            <div class="col-auto">
                <label for="class_id" class="col-form-label">Class:</label>
            </div>
            <div class="col-auto">
                <select name="class_id" id="class_id" class="form-select" onchange="this.form.submit()">
                    <option value="all"<?= ($selected_class === '' || $selected_class === 'all') ? ' selected' : '' ?>>All Classes</option>
                    <?php foreach ($classes as $class): ?>
                        <option value="<?= $class['id'] ?>"<?= ($selected_class == $class['id']) ? ' selected' : '' ?>><?= htmlspecialchars($class['name']) ?></option>
                    <?php endforeach; ?>
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
                                        <?php if (!empty($timetable[$day][$h]['class_id']) && isset($class_map[$timetable[$day][$h]['class_id']]) && ($selected_class === '' || $selected_class === 'all')): ?>
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
        <a href="generate.php?type=<?php echo $type; ?>&start_time=<?php echo urlencode($start_time); ?>" class="btn btn-secondary mt-3">Regenerate Timetable</a>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 