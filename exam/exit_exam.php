<?php
session_start();

// مسح جميع بيانات الاختبار من الجلسة
unset($_SESSION['exam_answers']);
unset($_SESSION['exam_questions']);
unset($_SESSION['exam_data']);

// توجيه المستخدم إلى الصفحة الرئيسية أو صفحة الملف الشخصي
header("Location: ../profile.php");
exit();
?>