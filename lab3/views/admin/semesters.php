<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Semesters</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<?php
$semesters = $semesters ?? [];
?>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="#">Grade Manager - Admin</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item"><a class="nav-link" href="index.php?page=admin.dashboard">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="index.php?page=admin.semesters">Semesters</a></li>
                    <li class="nav-item"><a class="nav-link" href="index.php?page=admin.courses">Courses</a></li>
                    <li class="nav-item"><a class="nav-link" href="index.php?page=admin.professors">Professors</a></li>
                    <li class="nav-item"><a class="nav-link" href="index.php?page=admin.students">Students</a></li>
                    <li class="nav-item"><a class="nav-link" href="index.php?page=admin.enrollments">Enrollments</a></li>
                </ul>
                <ul class="navbar-nav ms-auto">
                    <span class="nav-link text-light">Welcome, <?= htmlspecialchars($_SESSION['name']) ?></span>
                    <a class="nav-link" href="index.php?page=logout">Logout</a>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h2>Manage Semesters</h2>
        <?php if ($flash = getFlash()): ?>
            <div class="alert alert-<?= htmlspecialchars($flash['type']) ?> alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($flash['message']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <!-- Add Semester Form -->
        <div class="card mb-4">
            <div class="card-header">Add New Semester</div>
            <div class="card-body">
                <form method="POST" action="index.php?page=admin.semesters">
                    <div class="row">
                        <div class="col-md-3">
                            <input type="text" name="label" class="form-control" placeholder="Label (e.g., Fall)" required>
                        </div>
                        <div class="col-md-3">
                            <input type="text" name="academic_year" class="form-control" placeholder="Academic Year (e.g., 2024-2025)" required>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" name="action" value="create" class="btn btn-primary">Add Semester</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Semesters List -->
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Label</th>
                    <th>Academic Year</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($semesters as $semester): ?>
                <tr>
                    <td><?= $semester['id'] ?></td>
                    <td><?= htmlspecialchars($semester['label']) ?></td>
                    <td><?= htmlspecialchars($semester['academic_year']) ?></td>
                    <td>
                        <?php if ($semester['is_active']): ?>
                            <span class="badge bg-success">Active</span>
                        <?php else: ?>
                            <span class="badge bg-secondary">Inactive</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if (!$semester['is_active']): ?>
                            <form method="POST" style="display: inline-block;">
                                <input type="hidden" name="id" value="<?= $semester['id'] ?>">
                                <button type="submit" name="action" value="activate" class="btn btn-sm btn-success">Activate</button>
                            </form>
                        <?php endif; ?>
                        <form method="POST" style="display: inline-block;" onsubmit="return confirm('Delete this semester?')">
                            <input type="hidden" name="id" value="<?= $semester['id'] ?>">
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