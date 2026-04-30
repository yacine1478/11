<?php
$semesters = $semesters ?? [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Professor Grade Entry</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .grade-select { width: 120px; }
        .table-container { margin-top: 20px; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="#">Grade Manager - Professor</a>
            <div class="navbar-nav ms-auto">
                <span class="nav-link text-light">Welcome, <?= htmlspecialchars($_SESSION['name']) ?></span>
                <a class="nav-link" href="index.php?page=logout">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h2>Grade Entry</h2>
        
        <div id="feedback"></div>
        
        <div class="row mt-3">
            <div class="col-md-4">
                <label class="form-label">Semester</label>
                <select id="semesterSelect" class="form-select">
                    <option value="">-- Select Semester --</option>
                    <?php foreach ($semesters as $semester): ?>
                        <option value="<?= $semester['id'] ?>"><?= htmlspecialchars($semester['label']) ?> (<?= htmlspecialchars($semester['academic_year']) ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Course</label>
                <select id="courseSelect" class="form-select" disabled>
                    <option value="">-- Select Course --</option>
                </select>
            </div>
        </div>
        
        <div class="table-container" id="gradeTable" style="display: none;">
            <h3 class="mt-4">Student Grades</h3>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Student Name</th>
                        <th>Student ID</th>
                        <th>Grade</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
            <button id="saveBtn" class="btn btn-primary">Save Grades</button>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        function buildGradeOptions(selected) {
            var grades = [
                ['', '-- Select Grade --'],
                ['4.0', 'A (4.0)'],
                ['3.0', 'B (3.0)'],
                ['2.0', 'C (2.0)'],
                ['1.0', 'D (1.0)'],
                ['0.0', 'F (0.0)']
            ];
            var html = '';
            for (var i = 0; i < grades.length; i++) {
                var selectedAttr = (grades[i][0] == selected) ? 'selected' : '';
                html += '<option value="' + grades[i][0] + '" ' + selectedAttr + '>' + grades[i][1] + '</option>';
            }
            return html;
        }
        
        function escapeHtml(text) {
            if (!text) return '';
            return text.replace(/[&<>]/g, function(m) {
                if (m === '&') return '&amp;';
                if (m === '<') return '&lt;';
                if (m === '>') return '&gt;';
                return m;
            });
        }
        
        $(document).ready(function() {
            $('#semesterSelect').change(function() {
                var semId = $(this).val();
                if (!semId) {
                    $('#courseSelect').html('<option value="">-- Select Course --</option>').prop('disabled', true);
                    $('#gradeTable').hide();
                    return;
                }
                
                $.get('api/grades.php', { action: 'courses', semester_id: semId }, function(data) {
                    var opts = '<option value="">-- Select Course --</option>';
                    for (var i = 0; i < data.length; i++) {
                        opts += '<option value="' + data[i].id + '">' + escapeHtml(data[i].name) + ' (' + data[i].credits + ' credits)</option>';
                    }
                    $('#courseSelect').html(opts).prop('disabled', false);
                    $('#gradeTable').hide();
                }).fail(function() {
                    alert('Error loading courses');
                });
            });
            
            $('#courseSelect').change(function() {
                var semId = $('#semesterSelect').val();
                var courseId = $(this).val();
                
                if (!semId || !courseId) {
                    $('#gradeTable').hide();
                    return;
                }
                
                $.get('api/grades.php', { action: 'students', semester_id: semId, course_id: courseId }, function(students) {
                    var html = '';
                    for (var i = 0; i < students.length; i++) {
                        var s = students[i];
                        var gradeVal = s.grade !== null ? s.grade : '';
                        html += '<tr>' +
                            '<td>' + escapeHtml(s.name) + '</td>' +
                            '<td>' + s.id + '</td>' +
                            '<td>' +
                                '<select class="form-select grade-select" data-student="' + s.id + '">' +
                                    buildGradeOptions(gradeVal) +
                                '</select>' +
                            '</td>' +
                        '</tr>';
                    }
                    $('#gradeTable tbody').html(html);
                    $('#gradeTable').show();
                }).fail(function() {
                    alert('Error loading students');
                });
            });
            
            $('#saveBtn').click(function() {
                var semId = $('#semesterSelect').val();
                var courseId = $('#courseSelect').val();
                var grades = [];
                
                $('.grade-select').each(function() {
                    var studentId = $(this).data('student');
                    var grade = $(this).val();
                    if (grade) {
                        grades.push({ student_id: studentId, grade: grade });
                    }
                });
                
                $.post('api/grades.php', {
                    action: 'save',
                    semester_id: semId,
                    course_id: courseId,
                    grades: grades
                }, function(res) {
                    var cls = res.success ? 'alert-success' : 'alert-danger';
                    var msg = res.success ? res.saved + ' grade(s) saved successfully.' : res.error;
                    $('#feedback').html('<div class="alert ' + cls + ' alert-dismissible fade show" role="alert">' + msg + '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>');
                    
                    setTimeout(function() {
                        $('.alert').alert('close');
                    }, 3000);
                }).fail(function() {
                    $('#feedback').html('<div class="alert alert-danger">Error saving grades</div>');
                });
            });
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>