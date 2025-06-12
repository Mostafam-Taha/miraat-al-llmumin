document.addEventListener('DOMContentLoaded', function() {
    // تبديل القوائم المنسدلة
    const notificationBtn = document.getElementById('notificationBtn');
    const messageBtn = document.getElementById('messageBtn');
    const profileBtn = document.getElementById('profileBtn');
    const toggleSidebar = document.getElementById('toggleSidebar');
    
    // إغلاق القوائم المنسدلة عند النقر خارجها
    document.addEventListener('click', function(e) {
        if (!notificationBtn.contains(e.target) && !document.getElementById('notificationDropdown').contains(e.target)) {
            document.querySelector('.notifications').classList.remove('active');
        }
        
        if (!messageBtn.contains(e.target) && !document.getElementById('messageDropdown').contains(e.target)) {
            document.querySelector('.messages').classList.remove('active');
        }
        
        if (!profileBtn.contains(e.target) && !document.getElementById('profileDropdown').contains(e.target)) {
            document.querySelector('.admin-profile').classList.remove('active');
        }
    });
    
    // تفعيل/إلغاء تفعيل القوائم المنسدلة
    if (notificationBtn) {
        notificationBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            document.querySelector('.notifications').classList.toggle('active');
            document.querySelector('.messages').classList.remove('active');
            document.querySelector('.admin-profile').classList.remove('active');
        });
    }
    
    if (messageBtn) {
        messageBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            document.querySelector('.messages').classList.toggle('active');
            document.querySelector('.notifications').classList.remove('active');
            document.querySelector('.admin-profile').classList.remove('active');
        });
    }
    
    if (profileBtn) {
        profileBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            document.querySelector('.admin-profile').classList.toggle('active');
            document.querySelector('.notifications').classList.remove('active');
            document.querySelector('.messages').classList.remove('active');
        });
    }
    
    // تبديل الشريط الجانبي (يمكن ربطه بوظيفة الشريط الجانبي)
    if (toggleSidebar) {
        toggleSidebar.addEventListener('click', function() {
            document.body.classList.toggle('sidebar-collapsed');
            localStorage.setItem('sidebarCollapsed', document.body.classList.contains('sidebar-collapsed'));
        });
        
        // تحميل حالة الشريط الجانبي من localStorage
        if (localStorage.getItem('sidebarCollapsed') === 'true') {
            document.body.classList.add('sidebar-collapsed');
        }
    }
    
    // تعيين جميع الإشعارات كمقروءة
    const markAllReadButtons = document.querySelectorAll('.mark-all-read');
    markAllReadButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const dropdown = this.closest('.notification-dropdown, .message-dropdown');
            const items = dropdown.querySelectorAll('.unread');
            
            items.forEach(item => {
                item.classList.remove('unread');
            });
            
            // هنا يمكنك إضافة كود AJAX لتحديث حالة الإشعارات في الخادم
        });
    });
});


// ملف script.js أو داخل وسم <script>
document.addEventListener('DOMContentLoaded', function() {
    // جلب بيانات المستخدم المسجل دخوله
    fetch('get_admin_data.php')
        .then(response => response.json())
        .then(data => {
            // تحديث صورة البروفايل
            document.getElementById('adminProfileImage').src = data.profile_image;
            
            // يمكنك أيضاً تحديث الاسم والبريد الإلكتروني إذا كنت تريد
            // document.querySelector('.profile-name').textContent = data.full_name;
            // document.querySelector('.profile-email').textContent = data.email;
        })
        .catch(error => {
            console.error('Error:', error);
        });
});