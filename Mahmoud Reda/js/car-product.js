
document.addEventListener("DOMContentLoaded", function() {
    const productsButton = document.getElementById("shop-clear");
    if (productsButton) {
        productsButton.onclick = function() {
            window.location.href = "shop.php";
        };
    }
});


document.addEventListener("DOMContentLoaded", function () {
    const payButton = document.getElementById("paychack");
    if (payButton) {
        payButton.onclick = function () {
            window.location.href = "shop-checkout.php";
        };
    } else {
        console.error("العنصر paychack غير موجود في الـ HTML.");
    }
});


document.addEventListener("DOMContentLoaded", function() {
function openSection() {
    const section = document.querySelector('.car-product');
    section.classList.remove('hide');
    section.classList.add('show');
}

function closeSection() {
    const section = document.querySelector('.car-product');
    section.classList.remove('show');
    section.classList.add('hide');
}

    // إضافة الدوال إلى العناصر
    document.querySelector('.bi-bag-check').onclick = openSection;
    document.querySelector('.bi-x-lg').onclick = closeSection;
});