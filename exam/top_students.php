<?php
// الاتصال بقاعدة البيانات (تم توفيره مسبقاً)
require_once '../php/config.php';

// الحصول على الطلاب الأوائل (أول 10)
function getTopStudents($pdo) {
    $query = "SELECT 
                u.id as user_id,
                u.username as student_name,
                u.avatar as student_avatar,
                SUM(er.score) as total_score,
                u.student_class
              FROM 
                users u
              JOIN 
                exam_results er ON u.id = er.user_id
              GROUP BY 
                u.id, u.username, u.student_class, u.avatar
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
                u.avatar as student_avatar,
                SUM(er.score) as total_score,
                u.student_class
              FROM 
                users u
              JOIN 
                exam_results er ON u.id = er.user_id
              WHERE 
                u.id = ?
              GROUP BY 
                u.id, u.username, u.student_class, u.avatar";
    
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
    <title>لوحة الشرف - الأوائل الطلبة</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #4361ee;
            --secondary-color: #3f37c9;
            --accent-color: #4895ef;
            --gold-color: #ffd700;
            --silver-color: #c0c0c0;
            --bronze-color: #cd7f32;
            --dark-color: #2b2d42;
            --light-color: #f8f9fa;
            --success-color: #4cc9f0;
            --danger-color: #f72585;
            --border-radius: 12px;
            --box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Tajawal', sans-serif;
        }

        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            padding: 40px 20px;
            color: var(--dark-color);
        }

        .container {
            max-width: 1000px;
            margin: 0 auto;
            background-color: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            overflow: hidden;
            position: relative;
        }

        .header {
            background: linear-gradient(to right, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 25px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .header h1 {
            font-size: 2.2rem;
            margin-bottom: 10px;
            position: relative;
            z-index: 2;
        }

        .header p {
            font-size: 1.1rem;
            opacity: 0.9;
            position: relative;
            z-index: 2;
        }

        .header::before {
            content: "";
            position: absolute;
            top: -50px;
            right: -50px;
            width: 200px;
            height: 200px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            z-index: 1;
        }

        .header::after {
            content: "";
            position: absolute;
            bottom: -80px;
            left: -30px;
            width: 150px;
            height: 150px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            z-index: 1;
        }

        .top-students {
            padding: 20px;
        }

        .leaderboard {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 10px;
            margin-top: 20px;
        }

        .leaderboard thead th {
            background-color: var(--light-color);
            color: var(--dark-color);
            padding: 15px;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
        }

        .leaderboard thead th:first-child {
            border-top-right-radius: var(--border-radius);
            border-bottom-right-radius: var(--border-radius);
        }

        .leaderboard thead th:last-child {
            border-top-left-radius: var(--border-radius);
            border-bottom-left-radius: var(--border-radius);
        }

        .leaderboard tbody tr {
            background-color: white;
            transition: var(--transition);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            border-radius: var(--border-radius);
        }

        .leaderboard tbody tr:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .leaderboard tbody td {
            padding: 15px;
            text-align: center;
            vertical-align: middle;
            border: none;
        }

        .leaderboard tbody td:first-child {
            border-top-right-radius: var(--border-radius);
            border-bottom-right-radius: var(--border-radius);
        }

        .leaderboard tbody td:last-child {
            border-top-left-radius: var(--border-radius);
            border-bottom-left-radius: var(--border-radius);
        }

        .rank {
            display: inline-block;
            width: 30px;
            height: 30px;
            line-height: 30px;
            border-radius: 50%;
            background-color: var(--light-color);
            color: var(--dark-color);
            font-weight: bold;
            text-align: center;
        }

        .first-place .rank {
            background-color: var(--gold-color);
            color: #000;
        }

        .second-place .rank {
            background-color: var(--silver-color);
            color: #000;
        }

        .third-place .rank {
            background-color: var(--bronze-color);
            color: white;
        }

        .student-info {
            display: flex;
            align-items: center;
            justify-content: flex-end;
        }

        .student-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
            margin-left: 15px;
            border: 3px solid var(--light-color);
            box-shadow: 0 3px 5px rgba(0, 0, 0, 0.1);
            transition: var(--transition);
        }

        .student-avatar:hover {
            transform: scale(1.1);
        }

        .student-name {
            font-weight: 600;
            color: var(--dark-color);
            transition: var(--transition);
        }

        .student-link:hover .student-name {
            color: var(--primary-color);
        }

        .student-class {
            font-size: 0.9rem;
            color: #666;
            margin-top: 3px;
        }

        .total-score {
            font-weight: 700;
            color: var(--primary-color);
            font-size: 1.1rem;
        }

        .current-user {
            background-color: rgba(67, 97, 238, 0.1);
            position: relative;
        }

        .current-user::before {
            content: "";
            position: absolute;
            right: 0;
            top: 0;
            height: 100%;
            width: 4px;
            background-color: var(--primary-color);
            border-top-right-radius: var(--border-radius);
            border-bottom-right-radius: var(--border-radius);
        }

        .user-rank-card {
            background: white;
            border-radius: var(--border-radius);
            padding: 25px;
            margin-top: 30px;
            box-shadow: var(--box-shadow);
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .user-rank-card::before {
            content: "";
            position: absolute;
            top: 0;
            right: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(to right, var(--primary-color), var(--accent-color));
        }

        .user-rank-title {
            font-size: 1.3rem;
            margin-bottom: 15px;
            color: var(--dark-color);
        }

        .user-rank-value {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--primary-color);
            margin: 10px 0;
        }

        .user-info {
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 20px 0;
        }

        .user-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid var(--light-color);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .user-details {
            text-align: right;
            margin-right: 20px;
        }

        .user-name {
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--dark-color);
        }

        .user-class {
            font-size: 1rem;
            color: #666;
            margin-top: 5px;
        }

        .user-score {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--accent-color);
            margin-top: 10px;
        }

        .medal-icon {
            font-size: 1.5rem;
            margin-right: 5px;
            vertical-align: middle;
        }

        .gold {
            color: var(--gold-color);
        }

        .silver {
            color: var(--silver-color);
        }

        .bronze {
            color: var(--bronze-color);
        }

        .progress-container {
            width: 100%;
            height: 10px;
            background-color: #e9ecef;
            border-radius: 5px;
            margin: 15px 0;
            overflow: hidden;
        }

        .progress-bar {
            height: 100%;
            background: linear-gradient(to right, var(--primary-color), var(--accent-color));
            border-radius: 5px;
            transition: width 0.5s ease;
        }

        .motivational-message {
            font-style: italic;
            color: #666;
            margin-top: 15px;
            font-size: 0.95rem;
        }

        @media (max-width: 768px) {
            .container {
                border-radius: 0;
            }
            
            .header h1 {
                font-size: 1.8rem;
            }
            
            .header p {
                font-size: 1rem;
            }
            
            .leaderboard thead {
                display: none;
            }
            
            .leaderboard tbody tr {
                display: block;
                margin-bottom: 15px;
                padding: 15px;
            }
            
            .leaderboard tbody td {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 10px 15px;
                text-align: left;
            }
            
            .leaderboard tbody td::before {
                content: attr(data-label);
                font-weight: bold;
                margin-left: 10px;
                color: var(--dark-color);
            }
            
            .student-info {
                justify-content: space-between;
                width: 100%;
            }
            
            .user-info {
                flex-direction: column;
                text-align: center;
            }
            
            .user-details {
                margin: 15px 0 0 0;
                text-align: center;
            }
        }

        /* تأثيرات إضافية */
        .pulse {
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% {
                box-shadow: 0 0 0 0 rgba(67, 97, 238, 0.4);
            }
            70% {
                box-shadow: 0 0 0 10px rgba(67, 97, 238, 0);
            }
            100% {
                box-shadow: 0 0 0 0 rgba(67, 97, 238, 0);
            }
        }

        .floating {
            animation: floating 3s ease-in-out infinite;
        }

        @keyframes floating {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
            100% { transform: translateY(0px); }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-trophy medal-icon gold"></i> لوحة الشرف <i class="fas fa-trophy medal-icon gold"></i></h1>
            <p>الأوائل الطلبة لهذا الشهر</p>
        </div>
        
        <div class="top-students">
            <table class="leaderboard">
                <thead>
                    <tr>
                        <th>الترتيب</th>
                        <th>الطالب</th>
                        <th>الصف</th>
                        <th>المجموع</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($topStudents as $index => $student): 
                        $rowClass = '';
                        $medalClass = '';
                        if ($index == 0) {
                            $rowClass = 'first-place';
                            $medalClass = 'gold';
                        } elseif ($index == 1) {
                            $rowClass = 'second-place';
                            $medalClass = 'silver';
                        } elseif ($index == 2) {
                            $rowClass = 'third-place';
                            $medalClass = 'bronze';
                        } elseif ($student['user_id'] == $current_user_id) {
                            $rowClass = 'current-user';
                        }
                        
                        $avatar = !empty($student['student_avatar']) ? $student['student_avatar'] : 'default_avatar.png';
                    ?>
                        <tr class="<?php echo $rowClass; ?>">
                            <td data-label="الترتيب">
                                <span class="rank">
                                    <?php echo $index + 1; ?>
                                    <?php if ($index < 3): ?>
                                        <i class="fas fa-medal medal-icon <?php echo $medalClass; ?>"></i>
                                    <?php endif; ?>
                                </span>
                            </td>
                            <td data-label="الطالب">
                                <a href="student_profile.php?id=<?php echo $student['user_id']; ?>" class="student-link">
                                    <div class="student-info">
                                        <div>
                                            <div class="student-name"><?php echo sanitizeInput($student['student_name']); ?></div>
                                            <!-- <div class="student-class"><?php echo sanitizeInput($student['student_class']); ?></div> -->
                                        </div>
                                        <img src="../api/<?php echo sanitizeInput($avatar); ?>" alt="صورة الطالب" class="student-avatar <?php echo $index == 0 ? 'pulse' : ''; ?>">
                                    </div>
                                </a>
                            </td>
                            <td data-label="الصف"><?php echo sanitizeInput($student['student_class']); ?></td>
                            <td data-label="المجموع" class="total-score"><?php echo sanitizeInput($student['total_score']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <?php if ($currentUserInfo && !in_array($current_user_id, array_column($topStudents, 'user_id'))): 
            $currentUserAvatar = !empty($currentUserInfo['student_avatar']) ? $currentUserInfo['student_avatar'] : 'default_avatar.png';
            $progressPercentage = min(100, ($currentUserRank / count($topStudents)) * 100);
        ?>
            <div class="user-rank-card">
                <h3 class="user-rank-title">ترتيبك الحالي</h3>
                <div class="user-rank-value">#<?php echo sanitizeInput($currentUserRank); ?></div>
                
                <div class="user-info">
                    <div class="user-details">
                        <div class="user-name"><?php echo sanitizeInput($currentUserInfo['student_name']); ?></div>
                        <div class="user-class"><?php echo sanitizeInput($currentUserInfo['student_class']); ?></div>
                    </div>
                    <img src="../api/<?php echo sanitizeInput($currentUserAvatar); ?>" alt="صورة الطالب" class="user-avatar floating">
                </div>
                
                <div class="user-score"><?php echo sanitizeInput($currentUserInfo['total_score']); ?> نقطة</div>
                
                <div class="progress-container">
                    <div class="progress-bar" style="width: <?php echo $progressPercentage; ?>%"></div>
                </div>
                
                <p class="motivational-message">
                    <i class="fas fa-quote-right"></i> 
                    استمر في التقدم! أنت على بعد <?php echo max(1, $currentUserRank - 10); ?> مراكز من الوصول إلى القائمة الأولى
                    <i class="fas fa-quote-left"></i>
                </p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>