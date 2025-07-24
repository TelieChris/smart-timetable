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
        'class_id' => $row['class_id'] ?? null,
        'subject_module_id' => $row['subject_module_id'] ?? null
    ];
}
// Fetch module codes if TVET
$module_codes = [];
if ($type === 'tvet') {
    $mod_result = $conn->query("SELECT id, module_code FROM modules");
    if ($mod_result) {
        while ($mod = $mod_result->fetch_assoc()) {
            $module_codes[$mod['id']] = $mod['module_code'];
        }
    }
}

// Update title based on selected class
if ($type === 'tvet' && $selected_class !== '' && $selected_class !== 'all' && isset($class_map[$selected_class])) {
    $title = 'TVET - ' . $class_map[$selected_class];
}

// Fetch all teachers for tooltip logic
$teacher_result = $conn->query("SELECT * FROM teachers ORDER BY name ASC");
$all_teachers = $teacher_result ? $teacher_result->fetch_all(MYSQLI_ASSOC) : [];

// Handle manual timetable update with advanced conflict checking and removal
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_timetable'])) {
    $edit_day = $conn->real_escape_string($_POST['edit_day']);
    $edit_hour = intval($_POST['edit_hour']);
    $edit_class_id = intval($_POST['edit_class_id']);
    $edit_type = $conn->real_escape_string($_POST['edit_type']);
    $edit_subject_module_id = isset($_POST['edit_subject_module_id']) ? intval($_POST['edit_subject_module_id']) : 0;
    $edit_subject_module_name = isset($_POST['edit_subject_module_name']) ? $conn->real_escape_string($_POST['edit_subject_module_name']) : '';
    $edit_teacher_id = isset($_POST['edit_teacher_id']) ? intval($_POST['edit_teacher_id']) : 0;
    $edit_class_id_new = isset($_POST['edit_class_id_new']) ? intval($_POST['edit_class_id_new']) : $edit_class_id;
    // Remove slot if subject/module is set to 0 (None)
    if ($edit_subject_module_id === 0) {
        $conn->query("DELETE FROM timetable WHERE type='$edit_type' AND day='$edit_day' AND hour=$edit_hour AND class_id=$edit_class_id");
        header("Location: timetable.php?type=$edit_type&class_id=$edit_class_id&start_time=" . urlencode($start_time));
        exit;
    }
    // Get teacher_id for the selected subject/module if not manually set
    if ($edit_teacher_id === 0) {
        if ($edit_type === 'tvet') {
            $q = $conn->query("SELECT teacher_id FROM modules WHERE id=$edit_subject_module_id");
        } else {
            $q = $conn->query("SELECT teacher_id FROM subjects WHERE id=$edit_subject_module_id");
        }
        $edit_teacher_id = ($q && $row = $q->fetch_assoc()) ? intval($row['teacher_id']) : 0;
    }
    // Advanced conflict check: teacher and class
    $conflict = false;
    $conflict_msg = '';
    // Teacher conflict
    if ($edit_teacher_id) {
        $conflict_q = $conn->query("SELECT * FROM timetable WHERE type='$edit_type' AND day='$edit_day' AND hour=$edit_hour AND class_id!=$edit_class_id_new");
        while ($row = $conflict_q->fetch_assoc()) {
            $other_id = intval($row['subject_module_id']);
            $other_teacher_id = 0;
            if ($edit_type === 'tvet') {
                $other_q = $conn->query("SELECT teacher_id FROM modules WHERE id=$other_id");
            } else {
                $other_q = $conn->query("SELECT teacher_id FROM subjects WHERE id=$other_id");
            }
            $other_teacher_id = ($other_q && $other_row = $other_q->fetch_assoc()) ? intval($other_row['teacher_id']) : 0;
            if ($other_teacher_id && $other_teacher_id == $edit_teacher_id) {
                $conflict = true;
                $conflict_msg = 'Teacher is already assigned to another class at this time!';
                break;
            }
        }
    }
    // Class conflict (should not have two subjects in the same class at the same time)
    $class_conflict_q = $conn->query("SELECT * FROM timetable WHERE type='$edit_type' AND day='$edit_day' AND hour=$edit_hour AND class_id=$edit_class_id_new");
    if ($class_conflict_q && $class_conflict_q->num_rows > 0) {
        $row = $class_conflict_q->fetch_assoc();
        if ($row['subject_module_id'] != $edit_subject_module_id) {
            $conflict = true;
            $conflict_msg = 'This class already has another subject/module at this time!';
        }
    }
    if ($conflict) {
        $error = $conflict_msg;
    } else {
        // If slot exists, update; else, insert
        $exists = $conn->query("SELECT * FROM timetable WHERE type='$edit_type' AND day='$edit_day' AND hour=$edit_hour AND class_id=$edit_class_id_new");
        if ($exists && $exists->num_rows > 0) {
            $conn->query("UPDATE timetable SET subject_module_id=$edit_subject_module_id, subject_module_name='$edit_subject_module_name', class_id=$edit_class_id_new WHERE type='$edit_type' AND day='$edit_day' AND hour=$edit_hour AND class_id=$edit_class_id_new");
        } else {
            $conn->query("INSERT INTO timetable (type, day, hour, subject_module_id, subject_module_name, class_id) VALUES ('$edit_type', '$edit_day', $edit_hour, $edit_subject_module_id, '$edit_subject_module_name', $edit_class_id_new)");
        }
        header("Location: timetable.php?type=$edit_type&class_id=$edit_class_id_new&start_time=" . urlencode($start_time));
        exit;
    }
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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #e0eafc 0%, #cfdef3 100%);
            min-height: 100vh;
        }
        .timetable-hero {
            padding: 40px 0 20px 0;
            text-align: center;
        }
        .timetable-title {
            font-size: 2.2rem;
            font-weight: 700;
            color: #1a3c6c;
        }
        .timetable-card {
            background: #fff;
            border-radius: 1rem;
            box-shadow: 0 2px 16px rgba(60,60,100,0.08);
            padding: 2rem 1.5rem;
            margin-bottom: 2rem;
        }
        .timetable-table th, .timetable-table td {
            vertical-align: middle;
            text-align: center;
        }
        .break-row, .lunch-row {
            background: #f1f5f9;
            font-weight: bold;
            text-align: center;
        }
        .class-label {
            font-size: 0.9em;
            color: #555;
        }
        @media (max-width: 768px) {
            .timetable-card {
                padding: 1rem 0.2rem;
            }
            .timetable-title {
                font-size: 1.3rem;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container-fluid">
            <a class="navbar-brand" href="dashboard.php?type=<?php echo $type; ?>">Smart Timetable</a>
        </div>
    </nav>
    <section class="timetable-hero">
    <div class="container">
        <h2 class="timetable-title mb-3">Weekly Timetable (<?php echo $title; ?>)</h2>
        <form method="get" class="mb-4 row g-3 align-items-center justify-content-center">
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
        <div class="timetable-card mx-auto" style="max-width: 1100px;">
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
                                <td
                                    <?php if (isset($timetable[$day][$h])): ?>
                                        <?php
                                        $tooltip = '';
                                        $tooltip_html = false;
                                        if ($type === 'tvet' && isset($timetable[$day][$h]['subject_module_id'])) {
                                            $mod_id = $timetable[$day][$h]['subject_module_id'];
                                            $mod_result = $conn->query("SELECT name, module_code, teacher_id FROM modules WHERE id=" . intval($mod_id));
                                            $mod = $mod_result ? $mod_result->fetch_assoc() : null;
                                            if ($mod) {
                                                $teacher_name = '-';
                                                foreach ($all_teachers as $t) {
                                                    if ($t['id'] == $mod['teacher_id']) {
                                                        $teacher_name = $t['name'];
                                                        break;
                                                    }
                                                }
                                                $class_name = isset($class_map[$timetable[$day][$h]['class_id']]) ? $class_map[$timetable[$day][$h]['class_id']] : '-';
                                                $tooltip = 'Module: ' . htmlspecialchars($mod['name'], ENT_QUOTES|ENT_SUBSTITUTE, 'UTF-8') . ' (' . htmlspecialchars($mod['module_code'], ENT_QUOTES|ENT_SUBSTITUTE, 'UTF-8') . ')<br>Teacher: ' . htmlspecialchars($teacher_name, ENT_QUOTES|ENT_SUBSTITUTE, 'UTF-8') . '<br>Class: ' . htmlspecialchars($class_name, ENT_QUOTES|ENT_SUBSTITUTE, 'UTF-8');
                                                $tooltip_html = true;
                                            }
                                        } elseif ($type === 'olevel' && isset($timetable[$day][$h]['subject_module_id'])) {
                                            $subj_id = $timetable[$day][$h]['subject_module_id'];
                                            $subj_result = $conn->query("SELECT name, teacher_id FROM subjects WHERE id=" . intval($subj_id));
                                            $subj = $subj_result ? $subj_result->fetch_assoc() : null;
                                            if ($subj) {
                                                $teacher_name = '-';
                                                foreach ($all_teachers as $t) {
                                                    if ($t['id'] == $subj['teacher_id']) {
                                                        $teacher_name = $t['name'];
                                                        break;
                                                    }
                                                }
                                                $class_name = isset($class_map[$timetable[$day][$h]['class_id']]) ? $class_map[$timetable[$day][$h]['class_id']] : '-';
                                                $tooltip = 'Subject: ' . htmlspecialchars($subj['name'], ENT_QUOTES|ENT_SUBSTITUTE, 'UTF-8') . '<br>Teacher: ' . htmlspecialchars($teacher_name, ENT_QUOTES|ENT_SUBSTITUTE, 'UTF-8') . '<br>Class: ' . htmlspecialchars($class_name, ENT_QUOTES|ENT_SUBSTITUTE, 'UTF-8');
                                                $tooltip_html = true;
                                            }
                                        }
                                        ?>
                                        data-bs-toggle="tooltip" data-bs-placement="top" <?= $tooltip_html ? 'data-html="true" data-bs-title=' . json_encode($tooltip) : 'title="' . htmlspecialchars($tooltip, ENT_QUOTES|ENT_SUBSTITUTE, 'UTF-8') . '"' ?>
                                    <?php endif; ?>
                                >
                                    <?php
                                    $cell_has_entry = isset($timetable[$day][$h]);
                                    $cell_subject_module_id = $cell_has_entry ? $timetable[$day][$h]['subject_module_id'] : '';
                                    $cell_name = $cell_has_entry ? $timetable[$day][$h]['name'] : '';
                                    $cell_teacher_id = '';
                                    if ($cell_has_entry) {
                                        if ($type === 'tvet') {
                                            $q = $conn->query("SELECT teacher_id FROM modules WHERE id=" . intval($cell_subject_module_id));
                                            $cell_teacher_id = ($q && $row = $q->fetch_assoc()) ? $row['teacher_id'] : '';
                                        } else {
                                            $q = $conn->query("SELECT teacher_id FROM subjects WHERE id=" . intval($cell_subject_module_id));
                                            $cell_teacher_id = ($q && $row = $q->fetch_assoc()) ? $row['teacher_id'] : '';
                                        }
                                    }
                                    ?>
                                    <span data-bs-toggle="tooltip" data-bs-html="true" data-bs-placement="top" title="<?= $cell_has_entry ? $tooltip : '' ?>">
                                        <a href="#" data-bs-toggle="modal" data-bs-target="#editModal_<?= $day ?>_<?= $h ?>_<?= $selected_class ?>" style="text-decoration:underline; color:inherit;">
                                        <?php if ($cell_has_entry && $type === 'tvet' && isset($module_codes[$cell_subject_module_id])): ?>
                                            <?= htmlspecialchars($module_codes[$cell_subject_module_id]) ?>
                                        <?php elseif ($cell_has_entry): ?>
                                            <?= htmlspecialchars($cell_name) ?><br>
                                        <?php else: ?>
                                            <span style="color:#bbb;">(Free)</span>
                                        <?php endif; ?>
                                        <?php if ($cell_has_entry && !empty($timetable[$day][$h]['class_id']) && isset($class_map[$timetable[$day][$h]['class_id']]) && ($selected_class === '' || $selected_class === 'all')): ?>
                                            <span class="class-label">Class: <?= htmlspecialchars($class_map[$timetable[$day][$h]['class_id']]) ?></span>
                                        <?php endif; ?>
                                        </a>
                                    </span>
                                    <!-- Edit Modal -->
                                    <div class="modal fade" id="editModal_<?= $day ?>_<?= $h ?>_<?= $selected_class ?>" tabindex="-1" aria-labelledby="editModalLabel_<?= $day ?>_<?= $h ?>_<?= $selected_class ?>" aria-hidden="true">
                                      <div class="modal-dialog">
                                        <div class="modal-content">
                                          <form method="post" action="">
                                            <input type="hidden" name="edit_timetable" value="1">
                                            <input type="hidden" name="edit_day" value="<?= $day ?>">
                                            <input type="hidden" name="edit_hour" value="<?= $h ?>">
                                            <input type="hidden" name="edit_class_id" value="<?= $selected_class ?>">
                                            <input type="hidden" name="edit_type" value="<?= $type ?>">
                                            <div class="modal-header">
                                              <h5 class="modal-title" id="editModalLabel_<?= $day ?>_<?= $h ?>_<?= $selected_class ?>">Edit Timetable Slot (<?= $day ?>, Hour <?= $h ?>)</h5>
                                              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                              <?php if ($error && isset($_POST['edit_day']) && $_POST['edit_day'] === $day && intval($_POST['edit_hour']) === $h && intval($_POST['edit_class_id']) === intval($selected_class)): ?>
                                                <div class="alert alert-danger"> <?= htmlspecialchars($error) ?> </div>
                                    <?php endif; ?>
                                              <div class="mb-3">
                                                <label class="form-label">Subject/Module</label>
                                                <select name="edit_subject_module_id" class="form-select">
                                                  <option value="0">-- None (Free Slot) --</option>
                                                  <?php
                                                  if ($type === 'tvet') {
                                                    $mod_result = $conn->query("SELECT id, name, module_code FROM modules WHERE class_ids LIKE '%$selected_class%' ORDER BY name ASC");
                                                    while ($mod = $mod_result->fetch_assoc()) {
                                                      $selected = ($mod['id'] == $cell_subject_module_id) ? 'selected' : '';
                                                      echo '<option value="' . $mod['id'] . '" ' . $selected . '>' . htmlspecialchars($mod['name']) . ' (' . htmlspecialchars($mod['module_code']) . ')</option>';
                                                    }
                                                  } else {
                                                    $subj_result = $conn->query("SELECT id, name FROM subjects WHERE class_ids LIKE '%$selected_class%' ORDER BY name ASC");
                                                    while ($subj = $subj_result->fetch_assoc()) {
                                                      $selected = ($subj['id'] == $cell_subject_module_id) ? 'selected' : '';
                                                      echo '<option value="' . $subj['id'] . '" ' . $selected . '>' . htmlspecialchars($subj['name']) . '</option>';
                                                    }
                                                  }
                                                  ?>
                                                </select>
                                              </div>
                                              <div class="mb-3">
                                                <label class="form-label">Teacher</label>
                                                <select name="edit_teacher_id" class="form-select">
                                                  <option value="0">-- Default/Auto --</option>
                                                  <?php foreach ($all_teachers as $t): ?>
                                                    <option value="<?= $t['id'] ?>" <?= ($cell_teacher_id == $t['id']) ? 'selected' : '' ?>><?= htmlspecialchars($t['name']) ?></option>
                                                  <?php endforeach; ?>
                                                </select>
                                              </div>
                                              <div class="mb-3">
                                                <label class="form-label">Class</label>
                                                <select name="edit_class_id_new" class="form-select">
                                                  <?php foreach ($classes as $c): ?>
                                                    <option value="<?= $c['id'] ?>" <?= ($selected_class == $c['id']) ? 'selected' : '' ?>><?= htmlspecialchars($c['name']) ?></option>
                                                  <?php endforeach; ?>
                                                </select>
                                              </div>
                                              <input type="hidden" name="edit_subject_module_name" value="<?= htmlspecialchars($cell_name) ?>">
                                            </div>
                                            <div class="modal-footer">
                                              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                              <button type="submit" class="btn btn-primary">Save Changes</button>
                                            </div>
                                          </form>
                                        </div>
                                      </div>
                                    </div>
                                </td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endfor; ?>
                </tbody>
            </table>
            </div>
                <a href="generate.php?type=<?php echo $type; ?>&start_time=<?php echo urlencode($start_time); ?>" class="btn btn-secondary mt-3">Regenerate Timetable</a>
            </div>
            <?php if ($type === 'tvet'): ?>
                <?php
                // For selected class, show only module codes used in that class's timetable
                $used_module_ids = [];
                foreach ($timetable as $day_slots) {
                    foreach ($day_slots as $cell) {
                        if (isset($cell['subject_module_id']) && $cell['subject_module_id']) {
                            $used_module_ids[$cell['subject_module_id']] = true;
                        }
                    }
                }
                $desc_query = "SELECT module_code, name FROM modules";
                if ($selected_class !== '' && $selected_class !== 'all' && count($used_module_ids) > 0) {
                    $ids = implode(',', array_map('intval', array_keys($used_module_ids)));
                    $desc_query .= " WHERE id IN ($ids)";
                }
                $desc_query .= " ORDER BY module_code ASC";
                $desc_result = $conn->query($desc_query);
                $module_descs = $desc_result ? $desc_result->fetch_all(MYSQLI_ASSOC) : [];
                ?>
                <div class="mt-5 timetable-card mx-auto" style="max-width: 600px;">
                    <h4>Module Code Descriptions<?php if ($selected_class !== '' && $selected_class !== 'all' && isset($class_map[$selected_class])) echo ' for ' . htmlspecialchars($class_map[$selected_class]); ?></h4>
                    <table class="table table-bordered bg-white w-auto">
                        <thead class="table-light">
                            <tr>
                                <th>Module Code</th>
                                <th>Description</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($module_descs as $desc): ?>
                                <tr>
                                    <td><?= htmlspecialchars($desc['module_code']) ?></td>
                                    <td><?= htmlspecialchars($desc['name']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </section>
    <footer class="text-center py-4 mt-5 bg-white border-top">
        <div class="container">
            <span class="text-muted">&copy; <?= date('Y') ?> Smart Timetable. All rights reserved.</span>
        </div>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.forEach(function (tooltipTriggerEl) {
            new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });
    </script>
</body>
</html> 
    