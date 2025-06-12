document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.getElementById('sidebar');
    const userList = document.getElementById('user-list');
    const userSearch = document.getElementById('user-search');
    const chatArea = document.getElementById('chat-area');
    const backToList = document.getElementById('back-to-list');
    const chatWithName = document.getElementById('chat-with-name');
    const chatWithStatus = document.getElementById('chat-with-status');
    const chatMessages = document.getElementById('chat-messages');
    const messageInput = document.getElementById('message-input');
    const sendBtn = document.getElementById('send-btn');
    
    let currentUser = null;
    let currentConversationId = null;
    let pollingInterval = null;
    
    // تحميل قائمة المستخدمين
    function loadUsers(search = '') {
            fetch(`../api/get_users.php?search=${encodeURIComponent(search)}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(users => {
                userList.innerHTML = '';
                
                if(users.error) {
                    showError(users.error);
                    return;
                }
                
                if(users.length === 0) {
                    userList.innerHTML = '<div class="no-users">لا يوجد طلاب متاحون</div>';
                    return;
                }
                
                users.forEach(user => {
                    const userElement = document.createElement('div');
                    userElement.className = 'user-item';
                    userElement.innerHTML = `
                        <div class="user-avatar">
                            <img src="${user.avatar || 'default-avatar.png'}" alt="${user.username}">
                        </div>
                        <div class="user-details">
                            <h4>${user.username}</h4>
                            <p>${user.student_class}</p>
                        </div>
                        ${user.has_chat_history ? '<div class="chat-indicator"><i class="fas fa-comment"></i></div>' : ''}
                    `;
                    
                    userElement.addEventListener('click', () => startChat(user));
                    userList.appendChild(userElement);
                });
            })
            .catch(error => {
                console.error('Error loading users:', error);
                showError('حدث خطأ في تحميل قائمة الطلاب. يرجى المحاولة مرة أخرى.');
            });
    }
    
    function showError(message) {
        userList.innerHTML = `
            <div class="error-message">
                <i class="fas fa-exclamation-triangle"></i>
                <p>${message}</p>
                <button id="retry-btn">إعادة المحاولة</button>
            </div>
        `;
        
        document.getElementById('retry-btn').addEventListener('click', () => {
            loadUsers(userSearch.value);
        });
    }
    
    // بدء الدردشة مع مستخدم
    function startChat(user) {
        currentUser = user;
        chatWithName.textContent = user.username;
        chatWithStatus.textContent = 'جار التحميل...';
        
        // إظهار منطقة الدردشة وإخفاء القائمة على الأجهزة المحمولة
        sidebar.classList.remove('active');
        chatArea.classList.add('active');
        
        // تمكين إدخال الرسائل
        messageInput.disabled = false;
        sendBtn.disabled = false;
        
        // الحصول على المحادثة
        fetch(`api/get_conversation.php?user_id=${user.id}`)
            .then(response => response.json())
            .then(data => {
                currentConversationId = data.conversation_id;
                loadMessages();
                
                // بدء التحديث التلقائي للرسائل
                if(pollingInterval) clearInterval(pollingInterval);
                pollingInterval = setInterval(loadMessages, 3000);
            })
            .catch(error => console.error('Error starting chat:', error));
    }
    
    // تحميل الرسائل
    function loadMessages() {
        if(!currentConversationId) return;
        
        fetch(`../api/get_messages.php?conversation_id=${currentConversationId}`)
            .then(response => response.json())
            .then(messages => {
                chatMessages.innerHTML = '';
                
                if(messages.length === 0) {
                    chatMessages.innerHTML = `
                        <div class="no-messages">
                            <i class="fas fa-comment-slash"></i>
                            <p>لا توجد رسائل بعد. ابدأ المحادثة الآن!</p>
                        </div>
                    `;
                    return;
                }
                
                messages.reverse().forEach(message => {
                    const messageElement = document.createElement('div');
                    messageElement.className = `message ${message.sender_id == currentUser.id ? 'received' : 'sent'}`;
                    messageElement.innerHTML = `
                        <div class="message-content">
                            <p>${message.message}</p>
                            <span class="message-time">${formatTime(message.sent_at)}</span>
                        </div>
                    `;
                    chatMessages.appendChild(messageElement);
                });
                
                // التمرير إلى الأسفل
                chatMessages.scrollTop = chatMessages.scrollHeight;
                
                // تحديث حالة الاتصال
                chatWithStatus.textContent = 'متصل';
            })
            .catch(error => console.error('Error loading messages:', error));
    }
    
    // إرسال رسالة
    function sendMessage() {
        const message = messageInput.value.trim();
        if(!message || !currentConversationId) return;
        
        fetch('../api/send_message.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                conversation_id: currentConversationId,
                message: message
            })
        })
        .then(response => response.json())
        .then(() => {
            messageInput.value = '';
            loadMessages();
        })
        .catch(error => console.error('Error sending message:', error));
    }
    
    // تنسيق الوقت
    function formatTime(dateString) {
        const date = new Date(dateString);
        return date.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
    }
    
    // أحداث واجهة المستخدم
    backToList.addEventListener('click', () => {
        sidebar.classList.add('active');
        chatArea.classList.remove('active');
        if(pollingInterval) clearInterval(pollingInterval);
    });
    
    sendBtn.addEventListener('click', sendMessage);
    
    messageInput.addEventListener('keypress', (e) => {
        if(e.key === 'Enter') sendMessage();
    });
    
    userSearch.addEventListener('input', () => {
        loadUsers(userSearch.value);
    });
    
    // التهيئة الأولية
    loadUsers();
});