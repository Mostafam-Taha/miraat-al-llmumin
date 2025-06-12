document.addEventListener('DOMContentLoaded', function() {
    // العناصر
    const toggleUser = document.getElementById('toggle-user');
    const userBox = document.getElementById('user-box');
    const overlay = document.getElementById('overlay');

    // وظيفة فتح وإغلاق صندوق المستخدم
    function toggleUserBox() {
        userBox.classList.toggle('show');
        overlay.classList.toggle('active');
        
        // إيقاف التمرير عند فتح الصندوق
        document.body.style.overflow = userBox.classList.contains('show') ? 'hidden' : '';
    }

    // حدث النقر على أيقونة المستخدم
    toggleUser.addEventListener('click', function(e) {
        e.stopPropagation(); // منع الانتشار لتجنب إغلاق الصندوق فور فتحه
        toggleUserBox();
    });

    // إغلاق الصندوق عند النقر خارجًا
    overlay.addEventListener('click', toggleUserBox);
    
    // إغلاق الصندوق عند النقر على أي مكان في الصفحة (اختياري)
    document.addEventListener('click', function(e) {
        if (!userBox.contains(e.target)) {
            userBox.classList.remove('show');
            overlay.classList.remove('active');
            document.body.style.overflow = '';
        }
    });

    // منع إغلاق الصندوق عند النقر داخله
    userBox.addEventListener('click', function(e) {
        e.stopPropagation();
    });
});