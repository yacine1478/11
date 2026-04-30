<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Grade History</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="#">Grade Manager - Student</a>
            <div class="navbar-nav ms-auto">
                <span class="nav-link text-light">Welcome, <?= htmlspecialchars($_SESSION['name']) ?></span>
                <a class="nav-link" href="index.php?page=student.dashboard">Current</a>
                <a class="nav-link" href="index.php?page=student.history">History</a>
                <a class="nav-link" href="index.php?page=logout">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h2>Grade History</h2>
        <div id="historyContent">
            <div class="alert alert-info">Loading your grade history...</div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            $.get('api/gpa.php', { action: 'history' }, function(data) {
                if (data.error) {
                    $('#historyContent').html('<div class="alert alert-warning">' + data.error + '</div>');
                    return;
                }
                
                if (data.length === 0) {
                    $('#historyContent').html('<div class="alert alert-info">No grade history found.</div>');
                    return;
                }
                
                var html = '';
                for (var i = 0; i < data.length; i++) {
                    var sem = data[i];
                    html += '<div class="card mb-3">';
                    html += '<div class="card-header">' + sem.label + ' (' + sem.academic_year + ')</div>';
                    html += '<div class="card-body">';
                    html += '<table class="table table-sm">';
                    html += '<thead><tr><th>Course</th><th>Credits</th><th>Grade</th></tr></thead><tbody>';
                    
                    for (var j = 0; j < sem.courses.length; j++) {
                        var c = sem.courses[j];
                        var gradeDisplay = c.grade !== null ? c.grade : 'Pending';
                        html += '<tr><td>' + c.name + '</td><td>' + c.credits + '</td><td>' + gradeDisplay + '</td></tr>';
                    }
                    
                    var gpaDisplay = sem.gpa !== null ? sem.gpa : 'N/A';
                    html += '</tbody></table>';
                    html += '<strong>Semester GPA: ' + gpaDisplay + '</strong>';
                    html += '</div></div>';
                }
                
                $('#historyContent').html(html);
            }).fail(function() {
                $('#historyContent').html('<div class="alert alert-danger">Error loading history</div>');
            });
        });
    </script>
</body>
</html>