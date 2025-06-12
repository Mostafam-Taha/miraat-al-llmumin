<?php
require_once './php/config.php';
session_start();

// التحقق من تسجيل الدخول
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// الحصول على معلومات المستخدم
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT username, avatar FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// دالة للتحقق من صحة الملف
function validateFile($file) {
    $maxSize = 200 * 1024 * 1024; // 200MB
    $allowedTypes = [
        'image/jpeg', 'image/png', 'image/gif', 
        'application/pdf', 
        'application/msword', 
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'application/zip',
        'application/x-rar-compressed'
    ];
    
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'حدث خطأ أثناء رفع الملف.'];
    }
    
    if ($file['size'] > $maxSize) {
        return ['success' => false, 'message' => 'حجم الملف يجب أن لا يتجاوز 200MB.'];
    }
    
    $fileType = mime_content_type($file['tmp_name']);
    if (!in_array($fileType, $allowedTypes)) {
        return ['success' => false, 'message' => 'نوع الملف غير مسموح به.'];
    }
    
    return ['success' => true, 'type' => $fileType];
}

// إرسال رسالة جديدة
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // إذا كان طلب AJAX لتعديل الرسالة
    if (isset($_POST['ajax_edit']) && isset($_POST['message_id']) && isset($_POST['new_message'])) {
        $message_id = (int)$_POST['message_id'];
        $new_message = sanitizeInput($_POST['new_message']);
        
        // التحقق من أن المستخدم هو صاحب الرسالة
        $stmt = $pdo->prepare("SELECT user_id FROM chat_messages WHERE id = ?");
        $stmt->execute([$message_id]);
        $message_owner = $stmt->fetch();
        
        if ($message_owner && $message_owner['user_id'] == $user_id) {
            $stmt = $pdo->prepare("UPDATE chat_messages SET message = ? WHERE id = ?");
            $stmt->execute([$new_message, $message_id]);
            
            // إرجاع رد JSON
            header('Content-Type: application/json');
            echo json_encode(['success' => true]);
            exit();
        } else {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'غير مصرح لك بتعديل هذه الرسالة']);
            exit();
        }
    }
    
    // إذا كان طلب AJAX لحذف الرسالة
    // في قسم معالجة حذف الرسائل
    if (isset($_POST['ajax_delete']) && isset($_POST['message_id'])) {
        $message_id = (int)$_POST['message_id'];
        
        $stmt = $pdo->prepare("SELECT user_id, file_path FROM chat_messages WHERE id = ?");
        $stmt->execute([$message_id]);
        $message_data = $stmt->fetch();
        
        if ($message_data && $message_data['user_id'] == $user_id) {
            if ($message_data['file_path'] && file_exists($message_data['file_path'])) {
                unlink($message_data['file_path']);
            }
            
            $stmt = $pdo->prepare("DELETE FROM chat_messages WHERE id = ?");
            $stmt->execute([$message_id]);
            
            // تحديث وقت آخر تعديل في سجل الدردشة
            $pdo->prepare("UPDATE chat_meta SET last_update = NOW() WHERE chat_id = 1")->execute();
            
            echo json_encode(['success' => true]);
            exit();
        }
    }
    
    // معالجة إرسال رسالة جديدة (الكود الأصلي)
    $message = isset($_POST['message']) ? sanitizeInput($_POST['message']) : '';
    $fileInfo = null;
    
    // معالجة الملف المرفوع إذا وجد
    if (isset($_FILES['file']) && $_FILES['file']['error'] !== UPLOAD_ERR_NO_FILE) {
        $validation = validateFile($_FILES['file']);
        
        if (!$validation['success']) {
            $_SESSION['error'] = $validation['message'];
            header("Location: qdl-o.php");
            exit();
        }
        
        $uploadDir = './uploads/chat_files/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        $extension = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
        $fileName = uniqid() . '_' . bin2hex(random_bytes(8)) . '.' . $extension;
        $filePath = $uploadDir . $fileName;
        
        if (move_uploaded_file($_FILES['file']['tmp_name'], $filePath)) {
            $fileInfo = [
                'name' => $_FILES['file']['name'],
                'path' => $filePath,
                'type' => $validation['type'],
                'size' => $_FILES['file']['size']
            ];
        } else {
            $_SESSION['error'] = 'حدث خطأ أثناء حفظ الملف.';
            header("Location: qdl-o.php");
            exit();
        }
    }
    
    if (!empty($message) || $fileInfo) {
        $stmt = $pdo->prepare("INSERT INTO chat_messages 
                              (user_id, message, file_name, file_path, file_type, file_size) 
                              VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $user_id, 
            $message,
            $fileInfo ? $fileInfo['name'] : null,
            $fileInfo ? $fileInfo['path'] : null,
            $fileInfo ? $fileInfo['type'] : null,
            $fileInfo ? $fileInfo['size'] : null
        ]);
    }
    
    header("Location: qdl-o.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>الدردشة الجماعية</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body {
            font-family: 'Tajawal', sans-serif;
            margin: 0;
            padding: 0;
            height: 100vh;
            overflow: hidden;
            background-color: #f5f7fa;
        }
        
        .chat-container {
            height: 100vh;
            display: flex;
            flex-direction: column;
            background-color: white;
        }
        
        .chat-header {
            background: linear-gradient(135deg, #4361ee, #3f37c9);
            color: white;
            padding: 1rem;
            text-align: center;
            font-weight: bold;
            flex-shrink: 0;
        }
        
        .chat-messages {
            flex-grow: 1;
            overflow-y: auto;
            padding: 1rem;
            background-color: #f8f9fa;
        }
        
        .message {
            margin-bottom: 1rem;
            padding: 0.75rem 1rem;
            border-radius: 15px;
            max-width: 70%;
            position: relative;
            animation: fadeIn 0.3s ease-out;
        }
        
        .received {
            background-color: white;
            border: 1px solid #e9ecef;
            margin-right: auto;
        }
        
        .sent {
            background-color: #e3f2fd;
            border: 1px solid #bbdefb;
            margin-left: auto;
        }
        
        .message-header {
            display: flex;
            align-items: center;
            margin-bottom: 0.5rem;
        }
        
        .user-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            margin-left: 0.5rem;
            object-fit: cover;
        }
        
        .username {
            font-weight: bold;
            color: #3f37c9;
        }
        
        .message-time {
            font-size: 0.75rem;
            color: #6c757d;
            margin-top: 0.25rem;
            text-align: left;
        }
        
        .message-actions {
            position: absolute;
            top: 0;
            left: -30px;
            display: none;
            background: white;
            border-radius: 15px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            z-index: 10;
        }
        
        .message:hover .message-actions {
            display: flex;
        }
        
        .message-action {
            padding: 5px;
            cursor: pointer;
            color: #6c757d;
        }
        
        .message-action:hover {
            color: #4361ee;
        }
        
        .chat-input {
            display: flex;
            padding: 1rem;
            background-color: white;
            border-top: 1px solid #e9ecef;
            flex-shrink: 0;
        }
        
        #message-input {
            flex-grow: 1;
            border-radius: 25px;
            padding: 0.75rem 1rem;
            border: 1px solid #ced4da;
            outline: none;
        }
        
        #send-button {
            margin-right: 0.5rem;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #4361ee;
            color: white;
            border: none;
        }
        
        #send-button:hover {
            background-color: #3f37c9;
        }
        
        .edit-message-input {
            width: 100%;
            padding: 0.5rem;
            border-radius: 5px;
            border: 1px solid #ced4da;
            margin-bottom: 0.5rem;
        }
        
        .edit-buttons {
            display: flex;
            justify-content: flex-end;
            gap: 0.5rem;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        /* تخصيص شريط التمرير */
        .chat-messages::-webkit-scrollbar {
            width: 8px;
        }
        
        .chat-messages::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }
        
        .chat-messages::-webkit-scrollbar-thumb {
            background: #4361ee;
            border-radius: 10px;
        }
        
        .chat-messages::-webkit-scrollbar-thumb:hover {
            background: #3f37c9;
        }


        /* التنسيق الأساسي لمعاينة الملف */
        .file-preview {
            position: fixed;
            bottom: 20px;
            left: 50%;
            top: 0;
            transform: translateX(-50%);
            width: 90%;
            max-width: 400px;
            background: white;
            border-radius: 10px;
            padding: 15px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            z-index: 1000;
            display: flex;
            flex-direction: column;
            align-items: center;
            border: 1px solid #e0e0e0;
        }

        /* تنسيق معلومات الملف */
        .file-info {
            width: 100%;
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            font-size: 14px;
        }

        /* تنسيق اسم الملف */
        .file-name {
            font-weight: bold;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 70%;
        }

        /* تنسيق حجم الملف */
        .file-size {
            color: #666;
        }

        /* تنسيق محتوى المعاينة */
        #file-preview-content {
            width: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 10px 0;
        }

        /* تنسيق أيقونة الإغلاق */
        .remove-file {
            position: absolute;
            top: -17px;
            right: -15px;
            /* background: white; */
            border-radius: 50%;
            padding: 5px;
            /* box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2); */
            color: #ff4444;
            font-size: 20px;
            cursor: pointer;
            z-index: 1001;
        }

        /* التأثير عند التحويم */
        .remove-file:hover {
            color: #cc0000;
            transform: scale(1.1);
        }

        .img_file-preview {
            width: 125px;
            border-radius: 10px;
        }



        /* التكيف مع الشاشات الصغيرة */
        @media (max-width: 480px) {
            .file-preview {
                width: 95%;
                padding: 12px;
                bottom: 225px;
            }
            
            .file-info {
                flex-direction: column;
            }
            
            .file-name {
                max-width: 100%;
                margin-bottom: 5px;
            }
        }
        .sending-message {
            opacity: 0.7;
            position: relative;
        }

        .sending-message::after {
            content: "";
            position: absolute;
            top: 50%;
            right: 10px;
            transform: translateY(-50%);
            width: 12px;
            height: 12px;
            border: 2px solid rgba(0,0,0,0.2);
            border-radius: 50%;
            border-top-color: #4361ee;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            to { transform: translateY(-50%) rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="chat-container">
        <div class="chat-header">
            <h5 class="mb-0"><i class="bi bi-chat-dots me-2"></i>الدردشة الجماعية</h5>
        </div>
        
        <div class="chat-messages" id="chat-messages">
            <!-- يتم تحميل الرسائل هنا عبر AJAX -->
        </div>
        
        <div class="chat-input">
            <form id="chat-form" method="post" enctype="multipart/form-data" class="w-100 d-flex">
                <button type="submit" id="send-button">
                    <i class="bi bi-send"></i>
                </button>
                <input type="text" id="message-input" name="message" placeholder="اكتب رسالتك هنا..." autocomplete="off">
                <label for="file-input" class="btn btn-outline-primary me-2" style="white-space: nowrap;">
                    <i class="bi bi-paperclip"></i>
                </label>
                <input type="file" id="file-input" name="file" style="display: none;" accept="image/*,.pdf,.doc,.docx,.xls,.xlsx,.zip,.rar">
                <div id="file-preview-container" class="file-preview" style="display: none;">
                    <div class="file-info">
                        <div class="file-name" id="file-name"></div>
                        <div class="file-size" id="file-size"></div>
                    </div>
                    <div id="file-preview-content"></div>
                    <i class="bi bi-x-circle remove-file" id="remove-file"></i>
                </div>
            </form>
        </div>
    </div>

    <!-- نموذج تعديل الرسالة -->
    <div class="modal fade" id="editMessageModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">تعديل الرسالة</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="edit-message-form" method="post">
                        <input type="hidden" id="edit-message-id" name="edit_message_id">
                        <textarea class="edit-message-input" id="edit-message-text" name="edited_message" rows="3" required></textarea>
                        <div class="edit-buttons">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                            <button type="submit" class="btn btn-primary">حفظ التعديلات</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // العناصر الأساسية
            const chatForm = document.getElementById('chat-form');
            const messageInput = document.getElementById('message-input');
            const chatMessages = document.getElementById('chat-messages');
            const fileInput = document.getElementById('file-input');
            const filePreviewContainer = document.getElementById('file-preview-container');
            const fileNameElement = document.getElementById('file-name');
            const fileSizeElement = document.getElementById('file-size');
            const filePreviewContent = document.getElementById('file-preview-content');
            const removeFileButton = document.getElementById('remove-file');
            
            let selectedFile = null;
            let isPollingActive = true;

            // بدء تحميل الرسائل عند فتح الصفحة
            loadInitialMessages();

            // إدارة الملف المرفق
            fileInput.addEventListener('change', handleFileSelect);
            removeFileButton.addEventListener('click', removeSelectedFile);

            // إرسال رسالة جديدة
            chatForm.addEventListener('submit', handleMessageSubmit);

            // وظيفة تحميل الرسائل الأولية
            function loadInitialMessages() {
                fetch('get_messages.php')
                    .then(response => response.json())
                    .then(messages => {
                        renderMessages(messages);
                        scrollToBottom();
                        startPollingForNewMessages();
                    })
                    .catch(error => console.error('Error loading initial messages:', error));
            }

            // بدء استطلاع الرسائل الجديدة
            // إضافة متغير لتتبع آخر تحديث
            let lastUpdateTime = null;

            // تعديل دالة البولينج لفحص التحديثات
            function startPollingForNewMessages() {
                function poll() {
                    if (!isPollingActive) return;
                    
                    // جلب وقت آخر تحديث
                    fetch('get_last_update.php')
                        .then(response => response.json())
                        .then(data => {
                            if (lastUpdateTime === null) {
                                lastUpdateTime = data.last_update;
                            }
                            
                            // إذا كان هناك تحديث جديد
                            if (lastUpdateTime !== data.last_update) {
                                lastUpdateTime = data.last_update;
                                checkForDeletedMessages();
                                loadNewMessages();
                            }
                            
                            setTimeout(poll, 1000);
                        })
                        .catch(error => {
                            console.error('Polling error:', error);
                            setTimeout(poll, 3000);
                        });
                }
                
                poll();
            }

            // دالة للتحقق من الرسائل المحذوفة
            function checkForDeletedMessages() {
                const currentMessageIds = Array.from(document.querySelectorAll('.message')).map(el => el.dataset.messageId);
                
                if (currentMessageIds.length === 0) return;
                
                fetch(`check_messages.php?ids=${currentMessageIds.join(',')}`)
                    .then(response => response.json())
                    .then(data => {
                        data.deletedIds.forEach(id => {
                            const messageEl = document.querySelector(`.message[data-message-id="${id}"]`);
                            if (messageEl) {
                                messageEl.remove();
                            }
                        });
                    });
            }

            // دالة لجلب الرسائل الجديدة فقط
            function loadNewMessages() {
                const lastMessageId = getLastMessageId();
                fetch(`get_messages.php?last_id=${lastMessageId}`)
                    .then(response => response.json())
                    .then(newMessages => {
                        if (newMessages.length > 0) {
                            renderMessages(newMessages);
                            if (shouldScrollToBottom()) {
                                scrollToBottom();
                            }
                        }
                    });
            }

            // تعديل دالة حذف الرسالة
            document.querySelectorAll('.delete-message').forEach(btn => {
                btn.addEventListener('click', function() {
                    if (confirm('هل أنت متأكد من حذف هذه الرسالة؟')) {
                        const messageId = this.closest('.message').dataset.messageId;
                        
                        fetch('qdl-o.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                            },
                            body: `ajax_delete=1&message_id=${messageId}`
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                // حذف الرسالة من الواجهة فورياً
                                document.querySelector(`.message[data-message-id="${messageId}"]`).remove();
                            } else {
                                alert(data.error || 'حدث خطأ أثناء حذف الرسالة');
                            }
                        });
                    }
                });
            });
            // عرض الرسائل في الواجهة
            function renderMessages(messages) {
                messages.forEach(message => {
                    // تخطي الرسائل الموجودة مسبقًا
                    if (document.querySelector(`.message[data-message-id="${message.id}"]`)) {
                        return;
                    }

                    const isCurrentUser = <?php echo $user_id; ?> === message.user_id;
                    const messageElement = createMessageElement(message, isCurrentUser);
                    chatMessages.appendChild(messageElement);
                });

                // إضافة مستمعي الأحداث للرسائل الجديدة
                addMessageActionsListeners();
            }

            // إنشاء عنصر رسالة جديد
            function createMessageElement(message, isCurrentUser) {
                const messageClass = isCurrentUser ? 'sent' : 'received';
                const messageElement = document.createElement('div');
                messageElement.className = `message ${messageClass}`;
                messageElement.dataset.messageId = message.id;

                let fileContent = '';
                if (message.file_path) {
                    fileContent = createFileContent(message);
                }

                messageElement.innerHTML = `
                    <div class="message-header">
                        <a href="./exam/student_profile.php?id=${message.user_id}" style="text-decoration: none; color: inherit;">
                            ${message.avatar ? `<img src="./api/${message.avatar}" class="user-avatar">` : ''}
                            <span class="username">${message.username}</span>
                        </a>
                    </div>
                    ${message.message ? `<div class="message-text">${message.message}</div>` : ''}
                    ${fileContent}
                    <div class="message-time">${formatTime(message.timestamp)}</div>
                    
                    ${isCurrentUser ? `
                    <div class="message-actions">
                        <i class="bi bi-pencil-square message-action edit-message" title="تعديل"></i>
                        <i class="bi bi-trash message-action delete-message" title="حذف"></i>
                        <i class="bi bi-clipboard message-action copy-message" title="نسخ"></i>
                    </div>
                    ` : ''}
                `;

                return messageElement;
            }

            // إنشاء محتوى الملف المرفق
            function createFileContent(message) {
                if (message.file_type.startsWith('image/')) {
                    return `
                        <div class="file-message">
                            <img src="${message.file_path}" style="max-width: 100%; max-height: 300px; border-radius: 5px;">
                            <a href="${message.file_path}" class="file-download" download="${message.file_name}">
                                <i class="bi bi-download"></i> تحميل الصورة
                            </a>
                        </div>
                    `;
                } else {
                    const iconClass = getFileIconClass({type: message.file_type});
                    return `
                        <div class="file-message">
                            <i class="bi ${iconClass} file-icon"></i>
                            <div>
                                <div>${message.file_name}</div>
                                <div>${formatFileSize(message.file_size)}</div>
                                <a href="${message.file_path}" class="file-download" download="${message.file_name}">
                                    <i class="bi bi-download"></i> تحميل الملف
                                </a>
                            </div>
                        </div>
                    `;
                }
            }

            // إدارة اختيار الملف
            function handleFileSelect(e) {
                if (this.files && this.files[0]) {
                    const file = this.files[0];
                    const maxSize = 200 * 1024 * 1024; // 200MB
                    
                    if (file.size > maxSize) {
                        alert('حجم الملف يجب أن لا يتجاوز 200MB');
                        this.value = '';
                        return;
                    }
                    
                    selectedFile = file;
                    fileNameElement.textContent = file.name;
                    fileSizeElement.textContent = formatFileSize(file.size);
                    
                    // عرض معاينة للصور
                    if (file.type.startsWith('image/')) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            filePreviewContent.innerHTML = `<img class="img_file-preview" src="${e.target.result}" alt="معاينة الصورة">`;
                        }
                        reader.readAsDataURL(file);
                    } else {
                        // عرض أيقونة للملفات الأخرى
                        const iconClass = getFileIconClass(file);
                        filePreviewContent.innerHTML = `<i class="bi ${iconClass} file-icon"></i>`;
                    }
                    
                    filePreviewContainer.style.display = 'flex';
                }
            }

            // إزالة الملف المحدد
            function removeSelectedFile() {
                fileInput.value = '';
                selectedFile = null;
                filePreviewContainer.style.display = 'none';
                filePreviewContent.innerHTML = '';
            }

            // إرسال الرسالة
            function handleMessageSubmit(e) {
                e.preventDefault();
                
                const message = messageInput.value.trim();
                const formData = new FormData();
                
                if (message) {
                    formData.append('message', message);
                }
                
                if (selectedFile) {
                    formData.append('file', selectedFile);
                }
                
                if (message || selectedFile) {
                    // إضافة رسالة مؤقتة مع مؤشر الإرسال
                    const tempMessageId = 'temp-' + Date.now();
                    const tempMessageElement = createTempMessageElement(tempMessageId, message);
                    chatMessages.appendChild(tempMessageElement);
                    scrollToBottom();
                    
                    fetch('qdl-o.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(() => {
                        messageInput.value = '';
                        removeSelectedFile();
                        // إزالة الرسالة المؤقتة وتحميل الرسائل الجديدة
                        document.getElementById(tempMessageId)?.remove();
                        // بدء استطلاع جديد لاستلام الرسالة الجديدة
                        const lastMessageId = getLastMessageId();
                        checkForNewMessages(lastMessageId);
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showMessageError(tempMessageId, message);
                    });
                }
            }

            // إنشاء رسالة مؤقتة أثناء الإرسال
            function createTempMessageElement(id, message) {
                const tempElement = document.createElement('div');
                tempElement.className = 'message sent sending-message';
                tempElement.id = id;
                tempElement.innerHTML = `
                    <div class="message-header">
                        <span class="username">أنت</span>
                    </div>
                    <div class="message-text">${message || 'ملف مرفق'}</div>
                    <div class="message-time">يتم الإرسال...</div>
                `;
                return tempElement;
            }

            // عرض خطأ في الرسالة المؤقتة
            function showMessageError(tempMessageId, message) {
                const tempMsg = document.getElementById(tempMessageId);
                if (tempMsg) {
                    tempMsg.classList.remove('sending-message');
                    tempMsg.innerHTML = `
                        <div class="message-header">
                            <span class="username">أنت</span>
                        </div>
                        <div class="message-text">${message || 'ملف مرفق'}</div>
                        <div class="message-time" style="color: #dc3545;">
                            فشل الإرسال - <a href="#" onclick="retrySendMessage(event, '${tempMessageId}')">إعادة المحاولة</a>
                        </div>
                    `;
                }
            }

            // التحقق من الرسائل الجديدة بعد إرسال رسالة
            function checkForNewMessages(lastId) {
                fetch(`get_messages.php?last_id=${lastId}`)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`خطأ في الشبكة! الحالة: ${response.status}`);
                        }
                        return response.json();
                    })
                    .then(newMessages => {
                        if (newMessages.length > 0) {
                            renderMessages(newMessages);
                            scrollToBottom();
                        } else {
                            setTimeout(() => checkForNewMessages(lastId), 500);
                        }
                    })
                    .catch(error => {
                        console.error('خطأ في جلب الرسائل الجديدة:', error);
                        // إعادة المحاولة بعد تأخير
                        setTimeout(() => checkForNewMessages(lastId), 3000);
                    });
            }

            // إضافة مستمعي الأحداث لأزرار الرسائل
            function addMessageActionsListeners() {
                // نسخ الرسالة
                document.querySelectorAll('.copy-message').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const messageElement = this.closest('.message');
                        const messageText = messageElement.querySelector('.message-text')?.textContent || '';
                        navigator.clipboard.writeText(messageText)
                            .then(() => alert('تم نسخ الرسالة بنجاح'))
                            .catch(err => console.error('فشل في نسخ الرسالة: ', err));
                    });
                });
                
                // حذف الرسالة
                document.querySelectorAll('.delete-message').forEach(btn => {
                    btn.addEventListener('click', function() {
                        if (confirm('هل أنت متأكد من حذف هذه الرسالة؟')) {
                            const messageId = this.closest('.message').dataset.messageId;
                            
                            fetch('qdl-o.php', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/x-www-form-urlencoded',
                                },
                                body: `ajax_delete=1&message_id=${messageId}`
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    // حذف الرسالة من الواجهة فورياً
                                    document.querySelector(`.message[data-message-id="${messageId}"]`).remove();
                                } else {
                                    alert(data.error || 'حدث خطأ أثناء حذف الرسالة');
                                }
                            })
                            .catch(error => {
                                console.error('Error:', error);
                                alert('حدث خطأ أثناء حذف الرسالة');
                            });
                        }
                    });
                });
                
                // تعديل الرسالة
                document.querySelectorAll('.edit-message').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const messageElement = this.closest('.message');
                        const messageId = messageElement.dataset.messageId;
                        const messageText = messageElement.querySelector('.message-text')?.textContent || '';
                        
                        showEditModal(messageId, messageText);
                    });
                });
            }

            // إضافة مستمع للنموذج التعديل
            document.getElementById('edit-message-form').addEventListener('submit', function(e) {
                e.preventDefault();
                
                const messageId = document.getElementById('edit-message-id').value;
                const newMessage = document.getElementById('edit-message-text').value.trim();
                
                if (!newMessage) {
                    alert('الرجاء إدخال نص الرسالة');
                    return;
                }
                
                fetch('qdl-o.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `ajax_edit=1&message_id=${messageId}&new_message=${encodeURIComponent(newMessage)}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // تحديث الرسالة في الواجهة فورياً
                        const messageElement = document.querySelector(`.message[data-message-id="${messageId}"]`);
                        if (messageElement) {
                            messageElement.querySelector('.message-text').textContent = newMessage;
                        }
                        
                        // إغلاق النافذة
                        bootstrap.Modal.getInstance(document.getElementById('editMessageModal')).hide();
                    } else {
                        alert(data.error || 'حدث خطأ أثناء تعديل الرسالة');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('حدث خطأ أثناء تعديل الرسالة');
                });
            });

            // عرض نافذة التعديل
            function showEditModal(messageId, messageText) {
                const editModal = new bootstrap.Modal(document.getElementById('editMessageModal'));
                document.getElementById('edit-message-id').value = messageId;
                document.getElementById('edit-message-text').value = messageText;
                editModal.show();
            }

            // الحصول على آخر معرف رسالة
            function getLastMessageId() {
                const messages = document.querySelectorAll('.message:not([id^="temp-"])');
                if (messages.length === 0) return 0;
                return parseInt(messages[messages.length - 1].dataset.messageId) || 0;
            }

            // التحقق إذا كان يجب التمرير للأسفل
            function shouldScrollToBottom() {
                const threshold = 100;
                return chatMessages.scrollHeight - chatMessages.scrollTop - chatMessages.clientHeight < threshold;
            }

            // التمرير لأسفل الدردشة
            function scrollToBottom() {
                chatMessages.scrollTop = chatMessages.scrollHeight;
            }

            // تنسيق الوقت
            function formatTime(timestamp) {
                const date = new Date(timestamp);
                return date.toLocaleTimeString('ar-EG', {
                    hour: '2-digit',
                    minute: '2-digit'
                });
            }
            
            // تنسيق حجم الملف
            function formatFileSize(bytes) {
                if (bytes === 0) return '0 Bytes';
                const k = 1024;
                const sizes = ['Bytes', 'KB', 'MB', 'GB'];
                const i = Math.floor(Math.log(bytes) / Math.log(k));
                return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
            }
            
            // الحصول على أيقونة الملف المناسبة
            function getFileIconClass(file) {
                if (file.type.startsWith('image/')) return 'bi-image';
                if (file.type === 'application/pdf') return 'bi-file-earmark-pdf';
                if (file.type === 'application/msword' || file.type === 'application/vnd.openxmlformats-officedocument.wordprocessingml.document') 
                    return 'bi-file-earmark-word';
                if (file.type === 'application/vnd.ms-excel' || file.type === 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet') 
                    return 'bi-file-earmark-excel';
                if (file.type === 'application/zip' || file.type === 'application/x-rar-compressed') 
                    return 'bi-file-earmark-zip';
                return 'bi-file-earmark';
            }

            // التركيز على حقل الإدخال عند تحميل الصفحة
            messageInput.focus();

            // دالة إعادة المحاولة (عامة)
            window.retrySendMessage = function(e, tempId) {
                e.preventDefault();
                const tempMsg = document.getElementById(tempId);
                if (tempMsg) {
                    tempMsg.classList.add('sending-message');
                    tempMsg.querySelector('.message-time').textContent = 'يتم الإرسال...';
                    
                    // هنا يمكنك إعادة إرسال البيانات
                    // هذا مثال مبسط فقط
                    setTimeout(() => {
                        const lastMessageId = getLastMessageId();
                        checkForNewMessages(lastMessageId);
                        tempMsg.remove();
                    }, 1000);
                }
            };
        }); 
    </script>
</body>
</html>