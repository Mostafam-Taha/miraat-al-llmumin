<?php
// الاتصال بقاعدة البيانات (تم توفيره مسبقاً)
require_once '../php/config.php';

// الحصول على الطلاب الأوائل (أول 10)
function getTopStudents($pdo) {
    $query = "SELECT 
                u.id as user_id,
                u.username as student_name,
                SUM(er.score) as total_score,
                u.student_class
              FROM 
                users u
              JOIN 
                exam_results er ON u.id = er.user_id
              GROUP BY 
                u.id, u.username, u.student_class
              ORDER BY 
                total_score DESC
              LIMIT 10";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// الحصول على ترتيب الطالب الحالي
function getCurrentUserRank($pdo, $current_user_id) {
    $query = "SELECT rank FROM (
                SELECT 
                  u.id,
                  RANK() OVER (ORDER BY SUM(er.score) DESC) as rank
                FROM 
                  users u
                JOIN 
                  exam_results er ON u.id = er.user_id
                GROUP BY 
                  u.id
              ) as ranked_users
              WHERE id = ?";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute([$current_user_id]);
    return $stmt->fetchColumn();
}

// الحصول على معلومات الطالب الحالي
function getCurrentUserInfo($pdo, $current_user_id) {
    $query = "SELECT 
                u.id as user_id,
                u.username as student_name,
                SUM(er.score) as total_score,
                u.student_class
              FROM 
                users u
              JOIN 
                exam_results er ON u.id = er.user_id
              WHERE 
                u.id = ?
              GROUP BY 
                u.id, u.username, u.student_class";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute([$current_user_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

session_start();
$current_user_id = $_SESSION['user_id'] ?? 0;

// الحصول على البيانات
$topStudents = getTopStudents($pdo);
$currentUserRank = getCurrentUserRank($pdo, $current_user_id);
$currentUserInfo = getCurrentUserInfo($pdo, $current_user_id);
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>الأوائل الطلبة</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h1 {
            text-align: center;
            color: #333;
        }
        .top-students {
            margin-top: 30px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 12px;
            text-align: center;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        tr:hover {
            background-color: #f9f9f9;
        }
        .current-user {
            background-color: #e6f7ff;
            font-weight: bold;
        }
        .user-rank {
            margin-top: 30px;
            padding: 15px;
            background-color: #f0f8ff;
            border-radius: 5px;
            text-align: center;
        }
        /* تنسيقات المراكز الأولى */
        .first-place {
            background-color: #ffd700;
            font-weight: bold;
        }
        .second-place {
            background-color: #c0c0c0;
            font-weight: bold;
        }
        .third-place {
            background-color: #cd7f32;
            font-weight: bold;
            color: white;
        }
        .student-link {
            color: #0066cc;
            text-decoration: none;
        }
        .student-link:hover {
            text-decoration: underline;
        }
        .medal {
            font-size: 20px;
            margin-left: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>الأوائل الطلبة</h1>
        
        <div class="top-students">
            <table>
                <thead>
                    <tr>
                        <th>الترتيب</th>
                        <th>اسم الطالب</th>
                        <th>الصف</th>
                        <th>المجموع الكلي</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($topStudents as $index => $student): 
                        $rowClass = '';
                        $medal = '';
                        if ($index == 0) {
                            $rowClass = 'first-place';
                            $medal = '🥇';
                        } elseif ($index == 1) {
                            $rowClass = 'second-place';
                            $medal = '🥈';
                        } elseif ($index == 2) {
                            $rowClass = 'third-place';
                            $medal = '🥉';
                        } elseif ($student['user_id'] == $current_user_id) {
                            $rowClass = 'current-user';
                        }
                    ?>
                        <tr class="<?php echo $rowClass; ?>">
                            <td>
                                <?php echo $index + 1; ?>
                                <span class="medal"><?php echo $medal; ?></span>
                            </td>
                            <td>
                                <a href="student_profile.php?id=<?php echo $student['user_id']; ?>" class="student-link">
                                    <?php echo sanitizeInput($student['student_name']); ?>
                                </a>
                            </td>
                            <td><?php echo sanitizeInput($student['student_class']); ?></td>
                            <td><?php echo sanitizeInput($student['total_score']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <?php if ($currentUserInfo && !in_array($current_user_id, array_column($topStudents, 'user_id'))): ?>
            <div class="user-rank">
                <p>ترتيبك الحالي: <?php echo sanitizeInput($currentUserRank); ?></p>
                <p>اسمك: <a href="student_profile.php?id=<?php echo $currentUserInfo['user_id']; ?>" class="student-link">
                    <?php echo sanitizeInput($currentUserInfo['student_name']); ?>
                </a></p>
                <p>الصف: <?php echo sanitizeInput($currentUserInfo['student_class']); ?></p>
                <p>مجموعك الكلي: <?php echo sanitizeInput($currentUserInfo['total_score']); ?></p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>