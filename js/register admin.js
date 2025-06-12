document.addEventListener('DOMContentLoaded', function() {
    // تبديل عرض كلمة المرور
    const togglePasswordButtons = document.querySelectorAll('.toggle-password');
    togglePasswordButtons.forEach(button => {
        button.addEventListener('click', function() {
            const input = this.parentElement.querySelector('input');
            const icon = this.querySelector('i');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    });
    
    // عرض اسم الملف المختار
    const fileInput = document.getElementById('profile_image');
    const fileNameElement = document.getElementById('file-name');
    
    if (fileInput && fileNameElement) {
        fileInput.addEventListener('change', function() {
            if (this.files.length > 0) {
                fileNameElement.textContent = this.files[0].name;
                fileNameElement.style.color = '#4361ee';
            } else {
                fileNameElement.textContent = 'لم يتم اختيار ملف';
                fileNameElement.style.color = '#6c757d';
            }
        });
    }
    
    // التحقق من صحة كلمة المرور أثناء الكتابة
    const passwordInput = document.getElementById('password');
    const confirmPasswordInput = document.getElementById('confirm_password');
    
    function validatePassword() {
        if (passwordInput.value !== confirmPasswordInput.value) {
            confirmPasswordInput.setCustomValidity('كلمة المرور غير متطابقة');
        } else {
            confirmPasswordInput.setCustomValidity('');
        }
    }
    
    if (passwordInput && confirmPasswordInput) {
        passwordInput.addEventListener('input', validatePassword);
        confirmPasswordInput.addEventListener('input', validatePassword);
    }
    
    // التحقق من صحة رقم الهوية
    const nationalIdInput = document.getElementById('national_id');
    if (nationalIdInput) {
        nationalIdInput.addEventListener('input', function() {
            const value = this.value.trim();
            if (value.length !== 10 && value.length !== 12) {
                this.setCustomValidity('رقم الهوية يجب أن يكون 10 أو 12 رقمًا');
            } else if (!/^\d+$/.test(value)) {
                this.setCustomValidity('يجب أن يحتوي على أرقام فقط');
            } else {
                this.setCustomValidity('');
            }
        });
    }
});