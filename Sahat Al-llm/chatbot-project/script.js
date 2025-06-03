document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.getElementById('sidebar');
    const chatArea = document.getElementById('chat-area');
    const backToList = document.getElementById('back-to-list');
    const userList = document.getElementById('user-list');
    const chatMessages = document.getElementById('chat-messages');
    const messageInput = document.getElementById('message-input');
    const sendBtn = document.getElementById('send-btn');
    const userSearch = document.getElementById('user-search');
    const chatWithName = document.getElementById('chat-with-name');
    const chatWithStatus = document.getElementById('chat-with-status');
    
    let currentUser = null;
    let currentConversation = null;
    
    // تحميل قائمة المستخدمين
    loadUsers();
    
    // البحث عن المستخدمين
    userSearch.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        const users = document.querySelectorAll('.user-item');
        
        users.forEach(user => {
            const userName = user.querySelector('.user-name').textContent.toLowerCase();
            if (userName.includes(searchTerm)) {
                user.style.display = 'flex';
            } else {
                user.style.display = 'none';
            }
        });
    });
    
    // العودة لقائمة المستخدمين (للجوال)
    backToList.addEventListener('click', function() {
        sidebar.classList.remove('hidden');
        chatArea.classList.remove('active');
        currentUser = null;
        currentConversation = null;
        messageInput.disabled = true;
        sendBtn.disabled = true;
    });
    
    // إرسال الرسالة
    sendBtn.addEventListener('click', sendMessage);
    messageInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            sendMessage();
        }
    });
    
    async function loadUsers() {
        try {
            const response = await fetch('get_users.php');
            
            // التحقق مما إذا كان الرد ناجحًا
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            
            const data = await response.json();
            
            // التحقق من وجود خطأ في البيانات
            if (data.error) {
                throw new Error(data.error);
            }
            
            userList.innerHTML = '';
            
            if (data.users && data.users.length > 0) {
                data.users.forEach(user => {
                    const userItem = document.createElement('div');
                    userItem.className = 'user-item';
                    userItem.innerHTML = `
                        <div class="user-avatar">${user.username.charAt(0)}</div>
                        <div class="user-info">
                            <div class="user-name">${user.username}</div>
                            <div class="user-class">${user.student_class}</div>
                        </div>
                        <div class="user-status ${user.is_online ? 'online' : ''}"></div>
                    `;
                    
                    userItem.addEventListener('click', function() {
                        selectUser(user);
                    });
                    
                    userList.appendChild(userItem);
                });
            } else {
                userList.innerHTML = '<div class="no-users">لا يوجد مستخدمون متاحون</div>';
            }
        } catch (error) {
            console.error('Error loading users:', error);
            userList.innerHTML = `<div class="error-message">خطأ في تحميل المستخدمين: ${error.message}</div>`;
        }
    }
    
    function selectUser(user) {
        currentUser = user;
        chatWithName.textContent = user.username;
        chatWithStatus.textContent = user.is_online ? 'متصل الآن' : 'غير متصل';
        chatWithStatus.style.color = user.is_online ? 'var(--online-color)' : 'var(--offline-color)';
        
        // تحميل المحادثة مع هذا المستخدم
        loadConversation(user.id);
        
        // للجوال: إظهار منطقة المحادثة وإخفاء القائمة
        sidebar.classList.add('hidden');
        chatArea.classList.add('active');
        
        // تفعيل حقل الإدخال
        messageInput.disabled = false;
        sendBtn.disabled = false;
        messageInput.focus();
    }
    
    async function loadConversation(userId) {
        try {
            // جلب المحادثة بين المستخدم الحالي والمستخدم المحدد
            const response = await fetch(`get_conversation.php?user_id=${userId}`);
            const data = await response.json();
            
            currentConversation = data.conversation_id;
            chatMessages.innerHTML = '';
            
            if (data.messages.length === 0) {
                const noMessages = document.createElement('div');
                noMessages.className = 'no-messages';
                noMessages.textContent = 'لا توجد رسائل بعد. ابدأ المحادثة الآن!';
                chatMessages.appendChild(noMessages);
                return;
            }
            
            data.messages.forEach(message => {
                addMessageToChat(message, message.sender_id == currentUser.id);
            });
            
            scrollToBottom();
        } catch (error) {
            console.error('Error loading conversation:', error);
        }
    }
    
    async function sendMessage() {
        const messageText = messageInput.value.trim();
        if (!messageText || !currentUser || !currentConversation) return;
        
        try {
            // إرسال الرسالة للخادم
            const response = await fetch('send_message.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    conversation_id: currentConversation,
                    receiver_id: currentUser.id,
                    message: messageText
                })
            });
            
            const result = await response.json();
            
            if (result.success) {
                // إضافة الرسالة للشات
                addMessageToChat({
                    sender_id: 0, // سيتغير هذا بالقيمة الحقيقية من الخادم
                    message: messageText,
                    sent_at: new Date().toISOString()
                }, true);
                
                messageInput.value = '';
                scrollToBottom();
            }
        } catch (error) {
            console.error('Error sending message:', error);
        }
    }
    
    function addMessageToChat(message, isSent) {
        const messageElement = document.createElement('div');
        messageElement.className = `message ${isSent ? 'sent' : 'received'}`;
        
        const time = new Date(message.sent_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
        
        messageElement.innerHTML = `
            <div class="message-text">${message.message}</div>
            <div class="message-time">${time}</div>
        `;
        
        // إزالة رسالة "لا توجد رسائل" إذا كانت موجودة
        const noMessages = document.querySelector('.no-messages');
        if (noMessages) noMessages.remove();
        
        // إزالة رسالة "اختر طالبًا" إذا كانت موجودة
        const noChatSelected = document.querySelector('.no-chat-selected');
        if (noChatSelected) noChatSelected.remove();
        
        chatMessages.appendChild(messageElement);
    }
    
    function scrollToBottom() {
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }
    
    // محاكاة تحديث الحالة في الوقت الحقيقي (ستستبدل ب WebSocket أو Polling)
    setInterval(() => {
        if (currentUser) {
            // تحديث حالة الاتصال للمستخدم الحالي
            fetch(`check_online.php?user_id=${currentUser.id}`)
                .then(response => response.json())
                .then(data => {
                    if (data.is_online !== undefined) {
                        chatWithStatus.textContent = data.is_online ? 'متصل الآن' : 'غير متصل';
                        chatWithStatus.style.color = data.is_online ? 'var(--online-color)' : 'var(--offline-color)';
                    }
                });
            
            // التحقق من وجود رسائل جديدة
            fetch(`check_new_messages.php?conversation_id=${currentConversation}&last_message=${getLastMessageTime()}`)
                .then(response => response.json())
                .then(data => {
                    if (data.new_messages && data.new_messages.length > 0) {
                        data.new_messages.forEach(message => {
                            addMessageToChat(message, false);
                        });
                        scrollToBottom();
                    }
                });
        }
    }, 5000); // التحقق كل 5 ثواني
    
    function getLastMessageTime() {
        const messages = document.querySelectorAll('.message');
        if (messages.length === 0) return null;
        
        const lastMessage = messages[messages.length - 1];
        const timeText = lastMessage.querySelector('.message-time').textContent;
        return timeText; // هذا مثال بسيط، في التطبيق الحقيقي تحتاج لمعالجة الوقت بشكل صحيح
    }
});