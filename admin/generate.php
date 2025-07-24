<?php
require_once '../includes/db.php';
$type = $_GET['type'] ?? 'olevel';
$title = $type === 'tvet' ? 'TVET' : 'O-Level';

// Fetch subjects or modules
$table = $type === 'tvet' ? 'modules' : 'subjects';
$result = $conn->query("SELECT * FROM $table");
$items = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];

$days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
$hours_per_day = 10;
$break_after = 4;
$lunch_after = 7;

// Default start time
$default_start_time = '08:00';
$start_time = $_POST['start_time'] ?? $default_start_time;

// Helper to add minutes to a time string
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

// Filter out modules with 0 periods per week
$periods_per_week = [];
foreach ($items as $item) {
    if ((int)($item['periods_per_week'] ?? $item['periods']) > 0) {
        $periods_per_week[] = [
            'id' => $item['id'],
            'name' => $item['name'],
            'periods' => $item['periods_per_week'],
            'preferred_time' => $item['preferred_time'] ?? 'Any',
            'teacher_id' => $item['teacher_id'] ?? null,
            'class_ids' => $item['class_ids'] ?? '',
        ];
    }
}

$generated = false;
// Global teacher schedule to prevent double-booking across all classes
$global_teacher_schedule = [];
// If generating TVET, preload O-Level teacher assignments into global_teacher_schedule
if ($type === 'tvet') {
    $olevel_result = $conn->query("SELECT day, hour, subject_module_id, class_id FROM timetable WHERE type='olevel'");
    if ($olevel_result) {
        while ($row = $olevel_result->fetch_assoc()) {
            // Get teacher_id for this subject
            $subj_id = $row['subject_module_id'];
            $teacher_id = null;
            $subj = $conn->query("SELECT teacher_id FROM subjects WHERE id=" . intval($subj_id));
            if ($subj && $subj_row = $subj->fetch_assoc()) {
                $teacher_id = $subj_row['teacher_id'];
            }
            if ($teacher_id) {
                $global_teacher_schedule[$row['day']][$row['hour']][$teacher_id] = true;
            }
        }
    }
}
// If generating O-Level, preload TVET teacher assignments into global_teacher_schedule
if ($type === 'olevel') {
    $tvet_result = $conn->query("SELECT day, hour, subject_module_id, class_id FROM timetable WHERE type='tvet'");
    if ($tvet_result) {
        while ($row = $tvet_result->fetch_assoc()) {
            // Get teacher_id for this module
            $mod_id = $row['subject_module_id'];
            $teacher_id = null;
            $mod = $conn->query("SELECT teacher_id FROM modules WHERE id=" . intval($mod_id));
            if ($mod && $mod_row = $mod->fetch_assoc()) {
                $teacher_id = $mod_row['teacher_id'];
            }
            if ($teacher_id) {
                $global_teacher_schedule[$row['day']][$row['hour']][$teacher_id] = true;
            }
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['generate']) && count($periods_per_week) > 0) {
    // Clear previous timetable for this type
    $conn->query("DELETE FROM timetable WHERE type='$type'");
    // Add class_id column to timetable if not exists
    $conn->query("ALTER TABLE timetable ADD COLUMN IF NOT EXISTS class_id INT NULL");
    // Fetch all classes
    $class_result = $conn->query("SELECT * FROM rooms ORDER BY id ASC");
    $all_classes = $class_result ? $class_result->fetch_all(MYSQLI_ASSOC) : [];
    // For each class, generate timetable
    foreach ($all_classes as $class) {
        $class_id = $class['id'];
        // Filter subjects/modules for this class (multi-class support)
        $class_items = array_filter($periods_per_week, function($item) use ($class_id) {
            if (!isset($item['class_ids'])) return false;
            $ids = explode(',', $item['class_ids']);
            return in_array($class_id, array_map('strval', $ids));
        });
        if (empty($class_items)) continue;
        // Sort by periods descending for priority
        $class_items = array_values($class_items);
        usort($class_items, function($a, $b) { return $b['periods'] - $a['periods']; });
        // Prepare periods pool
        $pool = [];
        foreach ($class_items as $item) {
            for ($i = 0; $i < $item['periods']; $i++) {
                $pool[] = [
                    'id' => $item['id'],
                    'name' => $item['name'],
                    'priority' => $item['periods'],
                    'preferred_time' => $item['preferred_time'],
                    'teacher_id' => $item['teacher_id'],
                ];
            }
        }
        // Distribute periods with preferred time and teacher conflict check
        $slots = [];
        $teacher_schedule = [];
        foreach ($days as $day) {
            for ($h = 1; $h <= $hours_per_day; $h++) {
                $slots[$day][$h] = null;
            }
        }
        $morning_hours = range(1, 4);
        $afternoon_hours = range(8, 10);
        $any_hours = array_merge(range(1, 10));
        // Helper to assign with teacher conflict check (define only once)
        if (!function_exists('assign_with_teacher_check')) {
            function assign_with_teacher_check(&$slots, &$pool, &$teacher_schedule, &$global_teacher_schedule, $days, $hours, $preferred) {
                foreach ($days as $day) {
                    foreach ($hours as $h) {
                        foreach ($pool as $k => $item) {
                            if ($item['preferred_time'] === $preferred && !$slots[$day][$h]) {
                                $tid = $item['teacher_id'];
                                if ($tid) {
                                    // Check global teacher schedule
                                    if (isset($global_teacher_schedule[$day][$h][$tid])) continue;
                                }
                                if ($tid && isset($teacher_schedule[$day][$h][$tid])) continue; // teacher busy in this class
                                $slots[$day][$h] = $item;
                                if ($tid) {
                                    $teacher_schedule[$day][$h][$tid] = true;
                                    $global_teacher_schedule[$day][$h][$tid] = true;
                                }
                                unset($pool[$k]);
                                break;
                            }
                        }
                    }
                }
            }
        }
        // Morning
        assign_with_teacher_check($slots, $pool, $teacher_schedule, $global_teacher_schedule, $days, $morning_hours, 'Morning');
        // Afternoon
        assign_with_teacher_check($slots, $pool, $teacher_schedule, $global_teacher_schedule, $days, $afternoon_hours, 'Afternoon');
        // Any
        assign_with_teacher_check($slots, $pool, $teacher_schedule, $global_teacher_schedule, $days, $any_hours, 'Any');
        // Fill any remaining pool in any empty slot
        foreach ($days as $day) {
            foreach ($any_hours as $h) {
                if (!$slots[$day][$h] && !empty($pool)) {
                    // Find first available with no teacher conflict
                    foreach ($pool as $k => $item) {
                        $tid = $item['teacher_id'];
                        if ($tid && (isset($teacher_schedule[$day][$h][$tid]) || isset($global_teacher_schedule[$day][$h][$tid]))) continue;
                        $slots[$day][$h] = $item;
                        if ($tid) {
                            $teacher_schedule[$day][$h][$tid] = true;
                            $global_teacher_schedule[$day][$h][$tid] = true;
                        }
                        unset($pool[$k]);
                        break;
                    }
                }
            }
            // Improved: Repeatedly fill one of any three consecutive free hours as long as possible
            $changed = true;
            while ($changed && !empty($pool)) {
                $changed = false;
                for ($h = 1; $h <= $hours_per_day - 2; $h++) {
                    if (empty($slots[$day][$h]) && empty($slots[$day][$h+1]) && empty($slots[$day][$h+2])) {
                        // Try to fill the middle slot first
                        $target_h = $h+1;
                        foreach ($pool as $k => $item) {
                            $tid = $item['teacher_id'];
                            if (!$slots[$day][$target_h] && (!($tid && (isset($teacher_schedule[$day][$target_h][$tid]) || isset($global_teacher_schedule[$day][$target_h][$tid]))))) {
                                $slots[$day][$target_h] = $item;
                                if ($tid) {
                                    $teacher_schedule[$day][$target_h][$tid] = true;
                                    $global_teacher_schedule[$day][$target_h][$tid] = true;
                                }
                                unset($pool[$k]);
                                $changed = true;
                                break 2;
                            }
                        }
                    }
                }
            }
        }
        // Save to DB with class_id
        foreach ($days as $day) {
            for ($h = 1; $h <= $hours_per_day; $h++) {
                if (isset($slots[$day][$h]) && $slots[$day][$h]) {
                    $item = $slots[$day][$h];
                    $conn->query("INSERT INTO timetable (type, day, hour, subject_module_id, subject_module_name, class_id) VALUES ('$type', '$day', $h, {$item['id']}, '{$item['name']}', $class_id)");
                }
            }
        }
    }
    $generated = true;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generate Timetable - <?php echo $title; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-secondary mb-4">
        <div class="container-fluid">
            <a class="navbar-brand" href="dashboard.php?type=<?php echo $type; ?>">Smart Timetable</a>
        </div>
    </nav>
    <div class="container">
        <h2 class="mb-4">Generate Timetable (<?php echo $title; ?>)</h2>
        <?php if (count($periods_per_week) === 0): ?>
            <div class="alert alert-warning">No <?php echo $type === 'tvet' ? 'modules' : 'subjects'; ?> found. Please add them first.</div>
        <?php else: ?>
            <form method="post" class="mb-4">
                <div class="row g-3 align-items-center">
                    <div class="col-auto">
                        <label for="start_time" class="col-form-label">Start Time:</label>
                    </div>
                    <div class="col-auto">
                        <input type="time" id="start_time" name="start_time" class="form-control" value="<?php echo htmlspecialchars($start_time); ?>" required>
                    </div>
                    <div class="col-auto">
                        <button type="submit" name="generate" class="btn btn-primary btn-lg">Generate Timetable</button>
                    </div>
                </div>
            </form>
        <?php endif; ?>
        <?php if ($generated): ?>
            <div class="alert alert-success mt-4">Timetable generated successfully! <a href="timetable.php?type=<?php echo $type; ?>&start_time=<?php echo urlencode($start_time); ?>" class="alert-link">View Timetable</a></div>
        <?php endif; ?>
        <div class="mt-5">
            <h4>Summary of <?php echo $type === 'tvet' ? 'Modules' : 'Subjects'; ?></h4>
            <table class="table table-bordered bg-white">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Periods/Week</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($periods_per_week as $i => $item): ?>
                    <tr>
                        <td><?= $i+1 ?></td>
                        <td><?= htmlspecialchars($item['name']) ?></td>
                        <td><?= $item['periods'] ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="mt-4">
            <h5>Daily Structure</h5>
            <ul>
                <li>4 hours (40 min each) → 15 min break</li>
                <li>3 hours (40 min each) → 40 min lunch</li>
                <li>3 hours (40 min each)</li>
            </ul>
        </div>
        <div class="mt-4">
            <h5>Hour Labels (Preview)</h5>
            <ul>
                <?php foreach ($hour_labels as $h => $label): ?>
                <li>Hour <?= $h ?>: <?= $label ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 