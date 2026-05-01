<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Enrollments</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="#">Grade Manager - Admin</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item"><a class="nav-link" href="index.php?page=admin.dashboard">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="index.php?page=admin.semesters">Semesters</a></li>
                    <li class="nav-item"><a class="nav-link" href="index.php?page=admin.courses">Courses</a></li>
                    <li class="nav-item"><a class="nav-link" href="index.php?page=admin.professors">Professors</a></li>
                    <li class="nav-item"><a class="nav-link" href="index.php?page=admin.students">Students</a></li>
                    <li class="nav-item"><a class="nav-link active" href="index.php?page=admin.enrollments">Enrollments</a></li>
                </ul>
                <ul class="navbar-nav ms-auto">
                    <span class="nav-link text-light"><?= htmlspecialchars($_SESSION['name']) ?></span>
                    <a class="nav-link" href="index.php?page=logout">Logout</a>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h2>Manage Enrollments</h2>
        <?php if ($flash = getFlash()): ?>
            <div class="alert alert-<?= htmlspecialchars($flash['type']) ?> alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($flash['message']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" action="index.php" class="row gy-2 align-items-end">
                    <input type="hidden" name="page" value="admin.enrollments">
                    <div class="col-md-6">
                        <label class="form-label">Choose Student</label>
                        <select name="student_id" class="form-select" onchange="this.form.submit()">
                            <option value="">-- Select student --</option>
                            <?php foreach ($students as $student): ?>
                                <option value="<?= $student['id'] ?>" <?= isset($selectedStudent) && $selectedStudent['id'] == $student['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($student['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </form>
            </div>
        </div>

        <?php if (isset($selectedStudent)): ?>
            <div class="card">
                <div class="card-header">Enrollment for <?= htmlspecialchars($selectedStudent['name']) ?></div>
                <div class="card-body">
                    <form method="POST" action="index.php?page=admin.enrollments">
                        <input type="hidden" name="student_id" value="<?= $selectedStudent['id'] ?>">
                        <div class="mb-3">
                            <p class="text-muted">Check the semesters this student should be enrolled in.</p>
                        </div>
                        <div class="row">
                            <?php foreach ($allSemesters as $semester): ?>
                                <div class="col-md-4">
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" name="semester_ids[]" value="<?= $semester['id'] ?>" id="semester_<?= $semester['id'] ?>"
                                            <?= in_array($semester['id'], $enrolledSemesterIds) ? 'checked' : '' ?> >
                                        <label class="form-check-label" for="semester_<?= $semester['id'] ?>">
                                            <?= htmlspecialchars($semester['label']) ?> (<?= htmlspecialchars($semester['academic_year']) ?>)
                                        </label>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <button type="submit" class="btn btn-primary">Update Enrollment</button>
                    </form>
                </div>
            </div>
        <?php else: ?>
            <div class="alert alert-secondary">Select a student to manage enrollment.</div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
