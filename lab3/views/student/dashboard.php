<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="#">Grade Manager - Student</a>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item"><a class="nav-link active" href="index.php?page=student.dashboard">Current</a></li>
                    <li class="nav-item"><a class="nav-link" href="index.php?page=student.history">History</a></li>
                </ul>
                <div class="navbar-nav ms-auto">
                    <span class="nav-link text-light">Welcome, <?= htmlspecialchars($_SESSION['name']) ?></span>
                    <a class="nav-link" href="index.php?page=logout">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h2>My Grades</h2>
        <p class="text-muted">Only grades entered by your professor are shown here.</p>
        <div id="gradesContent">
            <div class="alert alert-info">Loading your grades...</div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            $.get('api/gpa.php', { action: 'current' }, function(data) {
                if (data.error) {
                    $('#gradesContent').html('<div class="alert alert-warning">' + data.error + '</div>');
                    return;
                }
                
                var html = '<div class="alert alert-info">Semester: ' + data.semester.label + ' (' + data.semester.academic_year + ')</div>';
                html += '<table class="table table-bordered">';
                html += '<thead><tr><th>Course</th><th>Credits</th><th>Grade</th><th>Grade Points</th></tr></thead><tbody>';
                
                var totalPoints = 0;
                var totalCredits = 0;
                
                for (var i = 0; i < data.courses.length; i++) {
                    var c = data.courses[i];
                    var gradeDisplay = c.grade !== null ? c.grade : 'Pending';
                    var points = c.grade !== null ? (parseFloat(c.grade) * c.credits) : 0;
                    
                    if (c.grade !== null) {
                        totalPoints += points;
                        totalCredits += c.credits;
                    }
                    
                    html += '<tr>' +
                        '<td>' + c.name + '</td>' +
                        '<td>' + c.credits + '</td>' +
                        '<td>' + gradeDisplay + '</td>' +
                        '<td>' + points.toFixed(1) + '</td>' +
                    '</tr>';
                }
                
                var gpa = totalCredits > 0 ? (totalPoints / totalCredits).toFixed(2) : 'N/A';
                var gpaClass = gpa >= 3.7 ? 'success' : (gpa >= 3.0 ? 'info' : (gpa >= 2.0 ? 'warning' : 'danger'));
                
                html += '</tbody></table>';
                html += '<div class="alert alert-' + gpaClass + '"><strong>GPA: ' + gpa + '</strong></div>';
                
                $('#gradesContent').html(html);
            }).fail(function() {
                $('#gradesContent').html('<div class="alert alert-danger">Error loading grades</div>');
            });
        });
    </script>
</body>
</html>