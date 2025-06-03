<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>الدردشة بين الطلاب</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="chat-container">
        <div class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <h3>الطلاب المتاحون</h3>
                <div class="search-box">
                    <input type="text" id="user-search" placeholder="ابحث عن طالب...">
                    <i class="fas fa-search"></i>
                </div>
            </div>
            <div class="user-list" id="user-list">
                <!-- قائمة المستخدمين تظهر هنا -->
            </div>
        </div>
        
        <div class="chat-area" id="chat-area">
            <div class="chat-header">
                <div class="back-to-list" id="back-to-list">
                    <i class="fas fa-arrow-left"></i>
                </div>
                <div class="user-info">
                    <h3 id="chat-with-name">اختر طالبًا للدردشة</h3>
                    <p id="chat-with-status">غير متصل</p>
                </div>
            </div>
            
            <div class="chat-messages" id="chat-messages">
                <!-- الرسائل تظهر هنا -->
                <div class="no-chat-selected">
                    <i class="fas fa-comments"></i>
                    <p>اختر طالبًا لبدء المحادثة</p>
                </div>
            </div>
            
            <div class="chat-input" id="chat-input">
                <input type="text" id="message-input" placeholder="اكتب رسالتك هنا..." disabled>
                <button id="send-btn" disabled><i class="fas fa-paper-plane"></i></button>
            </div>
        </div>
    </div>

    <script src="script.js"></script>
</body>
</html>