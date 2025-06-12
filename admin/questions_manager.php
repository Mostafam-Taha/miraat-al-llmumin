<?php
require_once '../php/config.php';
// require_once '../php/check_session.php';
session_start();

if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    header("Location: login.php");
    exit();
}

// معالجة حذف السؤال
if (isset($_GET['delete_id'])) {
    $delete_id = sanitizeInput($_GET['delete_id']);
    $stmt = $pdo->prepare("DELETE FROM questions WHERE id = ?");
    $stmt->execute([$delete_id]);
    $_SESSION['message'] = "تم حذف السؤال بنجاح";
    header('Location: questions_manager.php');
    exit;
}

// معالجة البحث والفلترة
$search = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';
$subject_filter = isset($_GET['subject']) ? sanitizeInput($_GET['subject']) : '';
$type_filter = isset($_GET['type']) ? sanitizeInput($_GET['type']) : '';

// بناء استعلام SQL مع الفلترة
$sql = "SELECT * FROM questions WHERE 1=1";
$params = [];

if (!empty($search)) {
    $sql .= " AND (question_text LIKE ? OR lesson_name LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if (!empty($subject_filter)) {
    $sql .= " AND subject = ?";
    $params[] = $subject_filter;
}

if (!empty($type_filter)) {
    $sql .= " AND question_type = ?";
    $params[] = $type_filter;
}

$sql .= " ORDER BY added_date DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// جلب المواد وأنواع الأسئلة للفلترة
$subjects = $pdo->query("SELECT DISTINCT subject FROM questions")->fetchAll(PDO::FETCH_COLUMN);
$question_types = $pdo->query("SELECT DISTINCT question_type FROM questions")->fetchAll(PDO::FETCH_COLUMN);
?>


<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>نظام إدارة الأسئلة</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <style>
        :root {
            --primary-color: #4361ee;
            --secondary-color: #3f37c9;
            --accent-color: #4cc9f0;
            --success-color: #4ad66d;
            --warning-color: #f8961e;
            --danger-color: #f94144;
            --light-bg: #f8f9fa;
            --dark-bg: #212529;
        }
        
        body {
            font-family: 'Tajawal', sans-serif;
            background-color: #f5f7fa;
            color: #333;
        }
        
        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
            color: var(--primary-color);
        }
        
        .sidebar {
            background: linear-gradient(135deg, #2b2d42 0%, #1a1a2e 100%);
            color: white;
            min-height: calc(100vh - 56px);
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        
        .sidebar .nav-link {
            color: rgba(255,255,255,0.8);
            border-radius: 5px;
            margin-bottom: 5px;
            transition: all 0.3s;
        }
        
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            background-color: rgba(255,255,255,0.1);
            color: white;
        }
        
        .sidebar .nav-link i {
            margin-left: 8px;
        }
        
        .main-content {
            padding: 2rem;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.05);
            margin: 1rem 0;
            margin: auto;
            width: 100%;
            max-width: 1600px;
        }
        
        .page-title {
            color: var(--primary-color);
            font-weight: 700;
            margin-bottom: 1.5rem;
            position: relative;
            padding-bottom: 10px;
        }
        
        .page-title:after {
            content: '';
            position: absolute;
            bottom: 0;
            right: 0;
            width: 80px;
            height: 4px;
            background: linear-gradient(to right, var(--primary-color), var(--accent-color));
            border-radius: 2px;
        }
        
        .question-card {
            border: none;
            border-radius: 10px;
            overflow: hidden;
            transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
            box-shadow: 0 3px 6px rgba(0,0,0,0.1);
            margin-bottom: 1.5rem;
            background: white;
            cursor: pointer;
        }
        
        .question-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.15);
        }
        
        .question-card .card-header {
            background: linear-gradient(to right, var(--primary-color), var(--secondary-color));
            color: white;
            font-weight: 600;
            border-bottom: none;
        }
        
        .question-card .card-body {
            padding: 1.5rem;
        }
        
        .question-card .card-footer {
            background-color: rgba(248, 249, 250, 0.8);
            border-top: 1px solid rgba(0,0,0,0.05);
            font-size: 0.85rem;
        }
        
        .badge {
            font-weight: 500;
            padding: 0.35em 0.65em;
        }
        
        .badge-type {
            background-color: var(--accent-color);
        }
        
        .badge-subject {
            background-color: var(--secondary-color);
        }
        
        .badge-difficulty-easy {
            background-color: var(--success-color);
        }
        
        .badge-difficulty-medium {
            background-color: var(--warning-color);
            color: white;
        }
        
        .badge-difficulty-hard {
            background-color: var(--danger-color);
            color: white;
        }
        
        .filter-section {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-primary:hover {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
        }
        
        .btn-add {
            background: linear-gradient(to right, var(--primary-color), var(--accent-color));
            border: none;
            font-weight: 600;
            letter-spacing: 0.5px;
            padding: 0.5rem 1.5rem;
            box-shadow: 0 4px 6px rgba(67, 97, 238, 0.3);
        }
        
        .btn-add:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 10px rgba(67, 97, 238, 0.4);
        }
        
        .modal-arabic {
            text-align: right;
            direction: rtl;
        }
        
        .correct-answer {
            background-color: rgba(40, 167, 69, 0.1);
            border-right: 3px solid var(--success-color);
            padding: 0.5rem;
            border-radius: 5px;
        }
        
        .answer-option {
            padding: 0.5rem;
            margin-bottom: 0.5rem;
            border-radius: 5px;
            background-color: rgba(248, 249, 250, 0.8);
        }
        
        .stats-card {
            border-radius: 10px;
            padding: 1.5rem;
            color: white;
            margin-bottom: 1rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            transition: all 0.3s;
        }
        
        .stats-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 15px rgba(0,0,0,0.1);
        }
        
        .stats-card .icon {
            font-size: 2.5rem;
            opacity: 0.8;
        }
        
        .stats-card .count {
            font-size: 2rem;
            font-weight: 700;
        }
        
        .stats-card .title {
            font-size: 0.9rem;
            opacity: 0.9;
        }
        
        .pagination .page-item.active .page-link {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .pagination .page-link {
            color: var(--primary-color);
        }
        
        .empty-state {
            text-align: center;
            padding: 3rem;
            color: #6c757d;
        }
        
        .empty-state i {
            font-size: 4rem;
            color: #dee2e6;
            margin-bottom: 1rem;
        }
        
        .empty-state h4 {
            color: #6c757d;
            margin-bottom: 1rem;
        }
        
        @media (min-width: 992px) {
            .col-lg-10 {
                flex: 0 0 auto;
                /* width: 83.33333333%; */
            }
        }

        @media (max-width: 768px) {
            .sidebar {
                min-height: auto;
                margin-bottom: 1rem;
            }
            
            .main-content {
                padding: 1rem;
            }
        }

        @media (min-width: 768px) {
            .col-md-9 {
                flex: 0 0 auto;
                width: 100%;
            }
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
                        <a class="nav-link active" href="#"><i class="bi bi-speedometer2"></i> لوحة التحكم</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#"><i class="bi bi-people"></i> المستخدمون</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#"><i class="bi bi-bar-chart"></i> التقارير</a>
                    </li>
                </ul>
                <div class="d-flex align-items-center">
                    <div class="dropdown me-3">
                        <a href="#" class="d-flex align-items-center text-decoration-none dropdown-toggle" id="dropdownUser" data-bs-toggle="dropdown">
                            <img src="https://via.placeholder.com/40" alt="صورة المستخدم" class="rounded-circle me-2">
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

    <div class="container-fluid">
        <div class="row">
            <!-- المحتوى الرئيسي -->
            <main class="col-lg-10 col-md-9 ms-sm-auto px-md-4">
                <div class="main-content animate__animated animate__fadeIn">
                    <!-- عنوان الصفحة -->
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h1 class="page-title">إدارة الأسئلة</h1>
                        <a href="add_question.php" class="btn btn-primary btn-add">
                            <i class="bi bi-plus-lg"></i> إضافة سؤال جديد
                        </a>
                    </div>

                    <!-- إحصائيات سريعة -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="stats-card" style="background: linear-gradient(135deg, #4361ee 0%, #3a0ca3 100%);">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="count"><?= count($questions) ?></div>
                                        <div class="title">إجمالي الأسئلة</div>
                                    </div>
                                    <i class="bi bi-journal-text icon"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stats-card" style="background: linear-gradient(135deg, #4cc9f0 0%, #4895ef 100%);">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="count"><?= count($subjects) ?></div>
                                        <div class="title">عدد المواد</div>
                                    </div>
                                    <i class="bi bi-book icon"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stats-card" style="background: linear-gradient(135deg, #f8961e 0%, #f3722c 100%);">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="count"><?= count(array_filter($questions, fn($q) => $q['difficulty_level'] === 'صعب')) ?></div>
                                        <div class="title">أسئلة صعبة</div>
                                    </div>
                                    <i class="bi bi-exclamation-triangle icon"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stats-card" style="background: linear-gradient(135deg, #4ad66d 0%, #2d6a4f 100%);">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="count"><?= count(array_filter($questions, fn($q) => $q['question_type'] === 'اختيار متعدد')) ?></div>
                                        <div class="title">أسئلة اختيار متعدد</div>
                                    </div>
                                    <i class="bi bi-ui-checks icon"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- رسائل النظام -->
                    <?php if (isset($_SESSION['message'])): ?>
                        <div class="alert alert-success alert-dismissible fade show animate__animated animate__fadeInDown" role="alert">
                            <i class="bi bi-check-circle-fill me-2"></i>
                            <?= $_SESSION['message'] ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        <?php unset($_SESSION['message']); ?>
                    <?php endif; ?>
                    
                    <!-- تعديل قسم الفلترة بإزالة حقل مستوى الصعوبة -->
                    <div class="filter-section animate__animated animate__fadeIn">
                        <form method="GET" action="">
                            <div class="row g-3">
                                <div class="col-lg-4 col-md-6">
                                    <div class="input-group">
                                        <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                                        <input type="text" name="search" class="form-control" placeholder="ابحث في الأسئلة أو الدروس..." value="<?= htmlspecialchars($search) ?>">
                                    </div>
                                </div>
                                <div class="col-lg-3 col-md-6">
                                    <select name="subject" class="form-select">
                                        <option value="">كل المواد</option>
                                        <?php foreach ($subjects as $subject): ?>
                                            <option value="<?= $subject ?>" <?= $subject_filter == $subject ? 'selected' : '' ?>><?= $subject ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-lg-3 col-md-6">
                                    <select name="type" class="form-select">
                                        <option value="">كل الأنواع</option>
                                        <?php foreach ($question_types as $type): ?>
                                            <option value="<?= $type ?>" <?= $type_filter == $type ? 'selected' : '' ?>><?= $type ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-lg-2 col-md-6">
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="bi bi-funnel"></i> تصفية
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- تعديل عرض البطاقات بإزالة مستوى الصعوبة -->
                    <div class="row">
                        <?php foreach ($questions as $question): ?>
                            <div class="col-xl-4 col-lg-6 col-md-6 mb-4">
                                <div class="card question-card animate__animated animate__fadeInUp" data-bs-toggle="modal" data-bs-target="#questionModal" data-id="<?= $question['id'] ?>">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <div>
                                            <span class="badge badge-subject"><?= $question['subject'] ?></span>
                                            <span class="badge badge-type"><?= $question['question_type'] ?></span>
                                        </div>
                                        <span class="text-white">#<?= $question['id'] ?></span>
                                    </div>
                                    <div class="card-body">
                                        <h6 class="card-title"><?= mb_substr(strip_tags($question['question_text']), 0, 80) ?>...</h6>
                                        <p class="card-text text-muted small mt-2">
                                            <i class="bi bi-bookmark"></i> <?= $question['lesson_name'] ?>
                                        </p>
                                    </div>
                                    <div class="card-footer d-flex justify-content-between">
                                        <div>
                                            <?php if (!empty($question['modified_date'])): ?>
                                                <span class="badge bg-warning text-dark" title="تم التعديل">
                                                    <i class="bi bi-pencil-fill"></i> معدل
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="text-muted">
                                            <i class="bi bi-calendar"></i> <?= date('Y-m-d', strtotime($question['added_date'])) ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <!-- عرض الأسئلة -->
                    <?php if (count($questions) > 0): ?>
                        <div class="row">
                            <?php foreach ($questions as $question): ?>
                                <div class="col-xl-4 col-lg-6 col-md-6 mb-4">
                                    <div class="card question-card animate__animated animate__fadeInUp" data-bs-toggle="modal" data-bs-target="#questionModal" data-id="<?= $question['id'] ?>">
                                        <div class="card-header d-flex justify-content-between align-items-center">
                                            <div>
                                                <span class="badge badge-subject"><?= $question['subject'] ?></span>
                                                <span class="badge badge-type"><?= $question['question_type'] ?></span>
                                                <span class="badge <?= $question['difficulty_level'] === 'صعب' ? 'badge-difficulty-hard' : ($question['difficulty_level'] === 'متوسط' ? 'badge-difficulty-medium' : 'badge-difficulty-easy') ?>">
                                                    <?= $question['difficulty_level'] ?>
                                                </span>
                                            </div>
                                            <span class="text-white">#<?= $question['id'] ?></span>
                                        </div>
                                        <div class="card-body">
                                            <h6 class="card-title"><?= mb_substr(strip_tags($question['question_text']), 0, 80) ?>...</h6>
                                            <p class="card-text text-muted small mt-2">
                                                <i class="bi bi-bookmark"></i> <?= $question['lesson_name'] ?>
                                            </p>
                                        </div>
                                        <div class="card-footer d-flex justify-content-between">
                                            <div>
                                                <?php if (!empty($question['modified_date'])): ?>
                                                    <span class="badge bg-warning text-dark" title="تم التعديل">
                                                        <i class="bi bi-pencil-fill"></i> معدل
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                            <div class="text-muted">
                                                <i class="bi bi-calendar"></i> <?= date('Y-m-d', strtotime($question['added_date'])) ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <!-- ترقيم الصفحات -->
                        <nav aria-label="Page navigation" class="mt-4">
                            <ul class="pagination justify-content-center">
                                <li class="page-item disabled">
                                    <a class="page-link" href="#" tabindex="-1" aria-disabled="true">السابق</a>
                                </li>
                                <li class="page-item active"><a class="page-link" href="#">1</a></li>
                                <li class="page-item"><a class="page-link" href="#">2</a></li>
                                <li class="page-item"><a class="page-link" href="#">3</a></li>
                                <li class="page-item">
                                    <a class="page-link" href="#">التالي</a>
                                </li>
                            </ul>
                        </nav>
                    <?php else: ?>
                        <div class="empty-state animate__animated animate__fadeIn">
                            <i class="bi bi-question-circle"></i>
                            <h4>لا توجد أسئلة متطابقة مع معايير البحث</h4>
                            <p class="text-muted">يمكنك إضافة سؤال جديد بالنقر على زر "إضافة سؤال جديد" بالأعلى</p>
                            <a href="add_question.php" class="btn btn-primary mt-3">
                                <i class="bi bi-plus-lg"></i> إضافة سؤال جديد
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </main>
        </div>
    </div>
    
    <!-- مودال عرض السؤال التفصيلي -->
    <div class="modal fade" id="questionModal" tabindex="-1" aria-labelledby="questionModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-arabic">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="questionModalLabel">تفاصيل السؤال</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="questionDetails">
                    <!-- سيتم ملؤه بالجافاسكريبت -->
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">جاري التحميل...</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <a href="#" class="btn btn-primary" id="editBtn">
                        <i class="bi bi-pencil-fill"></i> تعديل
                    </a>
                    <a href="#" class="btn btn-danger" id="deleteBtn">
                        <i class="bi bi-trash-fill"></i> حذف
                    </a>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-lg"></i> إغلاق
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // معالجة عرض السؤال التفصيلي في المودال
        document.querySelectorAll('.question-card').forEach(card => {
            card.addEventListener('click', function() {
                const questionId = this.getAttribute('data-id');
                const modal = document.getElementById('questionModal');
                
                // عرض مؤشر التحميل
                document.getElementById('questionDetails').innerHTML = `
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">جاري التحميل...</span>
                        </div>
                    </div>
                `;
                
                // جلب بيانات السؤال عبر AJAX
                fetch(`get_question_details.php?id=${questionId}`)
                    .then(response => response.text())
                    .then(data => {
                        document.getElementById('questionDetails').innerHTML = data;
                        
                        // تحديث روابط التعديل والحذف
                        document.getElementById('editBtn').href = `edit_question.php?id=${questionId}`;
                        document.getElementById('deleteBtn').href = `questions_manager.php?delete_id=${questionId}`;
                        
                        // إضافة تأثير ظهور للمحتوى
                        document.getElementById('questionDetails').classList.add('animate__animated', 'animate__fadeIn');
                    })
                    .catch(error => {
                        document.getElementById('questionDetails').innerHTML = `
                            <div class="alert alert-danger text-center">
                                <i class="bi bi-exclamation-triangle-fill"></i> حدث خطأ أثناء جلب بيانات السؤال
                            </div>
                        `;
                    });
            });
        });
        
        // تأكيد الحذف
        document.getElementById('deleteBtn').addEventListener('click', function(e) {
            if (!confirm('هل أنت متأكد من رغبتك في حذف هذا السؤال؟ لا يمكن التراجع عن هذه العملية.')) {
                e.preventDefault();
            }
        });
        
        // رسم مخطط إحصائي (مثال)
        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.createElement('canvas');
            ctx.id = 'questionsChart';
            document.querySelector('.stats-card').appendChild(ctx);
            
            if (ctx) {
                new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: ['اختيار متعدد', 'صح أو خطأ', 'مقالي', 'توصيل'],
                        datasets: [{
                            data: [12, 19, 3, 5],
                            backgroundColor: [
                                '#4361ee',
                                '#4cc9f0',
                                '#4ad66d',
                                '#f8961e'
                            ],
                            borderWidth: 0
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                rtl: true
                            }
                        }
                    }
                });
            }
        });
    </script>
</body>
</html>