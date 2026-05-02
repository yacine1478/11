<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Courses</title>
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
                    <li class="nav-item"><a class="nav-link active" href="index.php?page=admin.courses">Courses</a></li>
                    <li class="nav-item"><a class="nav-link" href="index.php?page=admin.professors">Professors</a></li>
                    <li class="nav-item"><a class="nav-link" href="index.php?page=admin.students">Students</a></li>
                    <li class="nav-item"><a class="nav-link" href="index.php?page=admin.enrollments">Enrollments</a></li>
                </ul>
                <ul class="navbar-nav ms-auto">
                    <span class="nav-link text-light"><?= htmlspecialchars($_SESSION['name']) ?></span>
                    <a class="nav-link" href="index.php?page=logout">Logout</a>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h2>Manage Courses</h2>
        <?php if ($flash = getFlash()): ?>
            <div class="alert alert-<?= htmlspecialchars($flash['type']) ?> alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($flash['message']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="card mb-4">
            <div class="card-header">Add New Course</div>
            <div class="card-body">
                <form method="POST" action="index.php?page=admin.courses">
                    <div class="row gy-2 align-items-end">
                        <div class="col-md-4">
                            <label class="form-label">Course Name</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Semester</label>
                            <select name="semester_id" class="form-select" required>
                                <option value="">Select semester</option>
                                <?php foreach ($semesters as $semester): ?>
                                    <option value="<?= $semester['id'] ?>"><?= htmlspecialchars($semester['label']) ?> (<?= htmlspecialchars($semester['academic_year']) ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Credits</label>
                            <input type="number" name="credits" class="form-control" min="1" value="3" required>
                        </div>
                        <div class="col-md-3">
                            <button type="submit" name="action" value="create" class="btn btn-primary w-100">Create Course</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Semester</th>
                    <th>Credits</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($courses as $course): ?>
                    <tr>
                        <td><?= $course['id'] ?></td>
                        <td><?= htmlspecialchars($course['name']) ?></td>
                        <td><?= htmlspecialchars($course['semester_label']) ?> (<?= htmlspecialchars($course['academic_year']) ?>)</td>
                        <td><?= $course['credits'] ?></td>
                        <td>
                            <form method="POST" class="d-inline" onsubmit="return confirm('Delete this course?');">
                                <input type="hidden" name="id" value="<?= $course['id'] ?>">
                                <button type="submit" name="action" value="delete" class="btn btn-sm btn-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
