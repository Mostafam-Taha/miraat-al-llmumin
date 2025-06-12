<?php
require_once '../php/config.php';
require_once '../php/auth.php'; // ملف للتحقق من صلاحيات المدير
require_once '../php/check_session.php'; // ملف يحتوي على دوال مساعدة


// استعلامات للحصول على الإحصائيات
try {
    // عدد الأسئلة الكلي
    $totalQuestions = $pdo->query("SELECT COUNT(*) FROM questions")->fetchColumn();
    
    // عدد الأسئلة حسب النوع
    $questionTypes = $pdo->query("SELECT question_type, COUNT(*) as count FROM questions GROUP BY question_type")->fetchAll();
    
    // عدد الأسئلة حسب المستوى
    $difficultyLevels = $pdo->query("SELECT difficulty_level, COUNT(*) as count FROM questions GROUP BY difficulty_level")->fetchAll();
    
    // عدد المستخدمين المسجلين
    $totalUsers = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    
    // عدد الإجابات المسجلة
    $totalAnswers = $pdo->query("SELECT COUNT(*) FROM student_answers")->fetchColumn();
    
    // عدد الإجابات الصحيحة
    $correctAnswers = $pdo->query("SELECT COUNT(*) FROM student_answers WHERE is_correct = 1")->fetchColumn();
    
    // نسبة الإجابات الصحيحة
    $correctPercentage = $totalAnswers > 0 ? round(($correctAnswers / $totalAnswers) * 100, 2) : 0;
    
    // أحدث المستخدمين مسجلين
    $latestUsers = $pdo->query("SELECT username, email, registration_date FROM users ORDER BY registration_date DESC LIMIT 5")->fetchAll();
    
    // أحدث الأسئلة مضافة
    $latestQuestions = $pdo->query("SELECT id, question_text, subject, added_date FROM questions ORDER BY added_date DESC LIMIT 5")->fetchAll();

} catch (PDOException $e) {
    die("Error fetching statistics: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>نظرة عامة - لوحة التحكم</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../css/admin-header.css">
    <style>
        .stat-card {
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
        .card-icon {
            font-size: 2rem;
            opacity: 0.7;
        }
    </style>
</head>
<body>
        <!-- شريط التنقل العلوي -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <i class="bi bi-journal-bookmark-fill"></i> نظام الأسئلة
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="questions_manager.php"><i class="bi bi-speedometer2"></i>المدير</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="add_question.php"><i class="bi bi-question-lg"></i></i>اضافة سؤال</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#"><i class="bi bi-bar-chart"></i> التقارير</a>
                    </li>
                </ul>
                <div class="d-flex align-items-center">
                    <div class="dropdown me-3">
                        <a href="#" class="d-flex align-items-center text-decoration-none dropdown-toggle" id="dropdownUser" data-bs-toggle="dropdown">
                            <img src="./uploads/profiles/default-profile.jpg" alt="صورة المستخدم" class="rounded-circle me-2" id="adminProfileImage">                            
                            <span>مدير النظام</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="#"><i class="bi bi-person me-2"></i> الملف الشخصي</a></li>
                            <li><a class="dropdown-item" href="#"><i class="bi bi-gear me-2"></i> الإعدادات</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="#"><i class="bi bi-box-arrow-left me-2"></i> تسجيل الخروج</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <div class="container-fluid py-4">
        <h1 class="mb-4">نظرة عامة على الموقع</h1>
        
        <!-- إحصائيات رئيسية -->
        <div class="row g-4 mb-4">
            <div class="col-md-3">
                <div class="card stat-card bg-primary text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="card-title">إجمالي الأسئلة</h5>
                                <h2 class="mb-0"><?= number_format($totalQuestions) ?></h2>
                            </div>
                            <i class="bi bi-question-circle card-icon"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="card stat-card bg-success text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="card-title">إجمالي المستخدمين</h5>
                                <h2 class="mb-0"><?= number_format($totalUsers) ?></h2>
                            </div>
                            <i class="bi bi-people card-icon"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="card stat-card bg-info text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="card-title">إجمالي الإجابات</h5>
                                <h2 class="mb-0"><?= number_format($totalAnswers) ?></h2>
                            </div>
                            <i class="bi bi-check2-circle card-icon"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="card stat-card bg-warning text-dark">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="card-title">نسبة الإجابات الصحيحة</h5>
                                <h2 class="mb-0"><?= $correctPercentage ?>%</h2>
                            </div>
                            <i class="bi bi-graph-up card-icon"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- مخططات وتفاصيل -->
        <div class="row g-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-secondary text-white">
                        <h5 class="mb-0">توزيع الأسئلة حسب النوع</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="questionTypesChart" height="250"></canvas>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-secondary text-white">
                        <h5 class="mb-0">توزيع الأسئلة حسب الصعوبة</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="difficultyChart" height="250"></canvas>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- أحدث المستخدمين والأسئلة -->
        <div class="row g-4 mt-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">أحدث المستخدمين المسجلين</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>اسم المستخدم</th>
                                        <th>البريد الإلكتروني</th>
                                        <th>تاريخ التسجيل</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($latestUsers as $user): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($user['username'] ?? 'غير معروف') ?></td>
                                        <td><?= htmlspecialchars($user['email']) ?></td>
                                        <td><?= date('Y/m/d H:i', strtotime($user['registration_date'])) ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">أحدث الأسئلة المضافة</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>السؤال</th>
                                        <th>المادة</th>
                                        <th>التاريخ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($latestQuestions as $question): ?>
                                    <tr>
                                        <td><?= $question['id'] ?></td>
                                        <td><?= mb_substr(htmlspecialchars($question['question_text']), 0, 30) ?>...</td>
                                        <td><?= htmlspecialchars($question['subject']) ?></td>
                                        <td><?= date('Y/m/d H:i', strtotime($question['added_date'])) ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/dashboard_overview.js"></script>
    <script src="../js/admin-header.js"></script>

    <script>
        // مخطط توزيع الأسئلة حسب النوع
        const questionTypesCtx = document.getElementById('questionTypesChart').getContext('2d');
        const questionTypesChart = new Chart(questionTypesCtx, {
            type: 'pie',
            data: {
                labels: [
                    <?php foreach ($questionTypes as $type): ?>
                        '<?= $type['question_type'] ?>',
                    <?php endforeach; ?>
                ],
                datasets: [{
                    data: [
                        <?php foreach ($questionTypes as $type): ?>
                            <?= $type['count'] ?>,
                        <?php endforeach; ?>
                    ],
                    backgroundColor: [
                        '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40'
                    ]
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'right',
                        rtl: true
                    }
                }
            }
        });
        
        // مخطط توزيع الأسئلة حسب الصعوبة
        const difficultyCtx = document.getElementById('difficultyChart').getContext('2d');
        const difficultyChart = new Chart(difficultyCtx, {
            type: 'bar',
            data: {
                labels: [
                    <?php foreach ($difficultyLevels as $level): ?>
                        '<?= $level['difficulty_level'] ?>',
                    <?php endforeach; ?>
                ],
                datasets: [{
                    label: 'عدد الأسئلة',
                    data: [
                        <?php foreach ($difficultyLevels as $level): ?>
                            <?= $level['count'] ?>,
                        <?php endforeach; ?>
                    ],
                    backgroundColor: [
                        '#4CAF50', '#2196F3', '#F44336'
                    ]
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
</body>
</html>