const cardContainer = document.querySelector('.fr-card-con');
const leftArrow = document.querySelector('.fr-arrow .bi-arrow-left-short');
const rightArrow = document.querySelector('.fr-arrow .bi-arrow-right-short');
let autoScroll;

function scrollRight() {
    // إذا كانت الحاوية قد وصلت إلى نهاية التمرير، نعيد التمرير إلى البداية
    if (cardContainer.scrollLeft + cardContainer.offsetWidth >= cardContainer.scrollWidth) {
        cardContainer.scrollTo({ left: 0, behavior: 'smooth' });
    } else {
        cardContainer.scrollBy({ left: 820, behavior: 'smooth' });
    }
}

function scrollLeft() {
    // إذا كانت الحاوية قد وصلت إلى البداية، نعيد التمرير إلى النهاية
    if (cardContainer.scrollLeft === 0) {
        cardContainer.scrollTo({ left: cardContainer.scrollWidth, behavior: 'smooth' });
    } else {
        cardContainer.scrollBy({ left: -820, behavior: 'smooth' });
    }
}

// تفعيل التمرير التلقائي كل 3 ثواني
function startAutoScroll() {
    autoScroll = setInterval(scrollRight, 3000);
}

// إيقاف التمرير التلقائي عند الضغط على أي سهم
function stopAutoScroll() {
    clearInterval(autoScroll);
}

// إضافة الأحداث للأزرار
rightArrow.addEventListener('click', () => {
    scrollRight();
    stopAutoScroll(); // إيقاف التمرير التلقائي عند الضغط
});

leftArrow.addEventListener('click', () => {
    scrollLeft();
    stopAutoScroll(); // إيقاف التمرير التلقائي عند الضغط
});

// بدء التمرير التلقائي عند تحميل الصفحة
startAutoScroll();

// حدد كل عناصر .fd-card
const cards = document.querySelectorAll('.fd-card');

cards.forEach(card => {
  // حدد العنصر .fd-card-dle داخل البطاقة
  const details = card.querySelector('.fd-card-dle');
  
  // عند تمرير الماوس على البطاقة، أظهر العنصر
  card.addEventListener('mouseenter', () => {
    details.style.opacity = '1';
  });

  // عند إبعاد الماوس عن البطاقة، أخفِ العنصر
  card.addEventListener('mouseleave', () => {
    details.style.opacity = '0';
  });
});

// أخفاء وإظهار عنصر .popular-products-fd-fexid
function showElement() {
    document.getElementById("popular-products-fd-fexid").style.display = "flex";
}

function hideElement() {
    document.getElementById("popular-products-fd-fexid").style.display = "none";
}



let cartItems = JSON.parse(localStorage.getItem('cartItems')) || [];

// إضافة منتج إلى السلة
function addToCart(productName, price, image) {
    // التحقق مما إذا كان المنتج موجودًا بالفعل في السلة
    const existingItem = cartItems.find(item => item.name === productName);

    if (existingItem) {
        // إذا كان المنتج موجودًا، زيادة الكمية
        existingItem.quantity++;
    } else {
        // إضافة منتج جديد إلى السلة
        cartItems.push({
            name: productName,
            price: parseFloat(price),
            image: image,
            quantity: 1
        });
    }

    // تحديث localStorage
    localStorage.setItem('cartItems', JSON.stringify(cartItems));

    // تحديث واجهة السلة بعد إضافة المنتج
    displayCart();
}

// عرض المنتجات في السلة
function displayCart() {
    const cartContainer = document.querySelector('.cart-container');
    cartContainer.innerHTML = ''; // تنظيف السلة قبل العرض الجديد

    cartItems.forEach(item => {
        const totalPrice = (item.price * item.quantity).toFixed(2); // حساب السعر الكلي بناءً على الكمية
        const cartItem = document.createElement('div');
        cartItem.classList.add('cart-item');
        cartItem.innerHTML = `
            <div class="car-po-2">
                <div class="po-21">
                    <div class="flex-1">
                        <img src="${item.image}" alt="${item.name}" loading="lazy">
                        <div class="title">
                            <h5>${item.name}</h5>
                            <div>
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash" viewBox="0 0 16 16">
                                    <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0z"/>
                                    <path d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4zM2.5 3h11V2h-11z"/>
                                </svg>
                                <span onclick="removeFromCart('${item.name}')">إزالة</span>
                            </div>
                        </div>
                    </div>
                    <div class="flex-2">
                        <div class="po-54">
                            <div class="plus-left" onclick="decrement('${item.name}')">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-plus" viewBox="0 0 16 16">
                                    <path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4"/>
                                </svg>
                            </div>
                            <p><span>${item.quantity}</span></p>
                            <div class="plus-right" onclick="increment('${item.name}')">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-plus" viewBox="0 0 16 16">
                                    <path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4"/>
                                </svg>
                            </div>
                        </div>
                        <div class="po-78">
                            <p><span>${totalPrice}</span> E.G</p> <!-- عرض السعر الإجمالي -->
                        </div>
                    </div>
                </div>
            </div>
            <hr class="hr">
        `;
        cartContainer.appendChild(cartItem);
    });
}

// إزالة المنتج من السلة
function removeFromCart(productName) {
    cartItems = cartItems.filter(item => item.name !== productName);
    localStorage.setItem('cartItems', JSON.stringify(cartItems));
    displayCart();
}

// تحديث الكمية عند الضغط على "+" (زيادة الكمية والسعر)
function increment(productName) {
    let item = cartItems.find(item => item.name === productName);
    if (item) {
        item.quantity++;
        localStorage.setItem('cartItems', JSON.stringify(cartItems));
        displayCart();
    }
}

// تحديث الكمية عند الضغط على "-" (تقليل الكمية والسعر)
function decrement(productName) {
    let item = cartItems.find(item => item.name === productName);
    if (item && item.quantity > 1) {
        item.quantity--;
        localStorage.setItem('cartItems', JSON.stringify(cartItems));
        displayCart();
    }
}

// إزالة المنتج من السلة
function removeFromCart(productName) {
    cartItems = cartItems.filter(item => item.name !== productName);
    localStorage.setItem('cartItems', JSON.stringify(cartItems));
    displayCart();
}

// استدعاء دالة العرض عند تحميل الصفحة
window.onload = function() {
    displayCart();
}




function showNotification() {
    const notification = document.getElementById('notification');

    // إضافة الكلاس "show" لإظهار الإشعار
    notification.classList.add('show');
    notification.classList.remove('hide');
    notification.style.display = 'flex';

// إخفاء الإشعار بعد 3 ثوانٍ
setTimeout(() => {
    notification.classList.add('hide');
        notification.classList.remove('show');

        // تعيين display: none بعد انتهاء الرسوم المتحركة
    setTimeout(() => {
            notification.style.display = 'none';
        }, 1000); // مطابقة مع مدة الـ transition
}, 3000);
}

// دالة إضافة المنتج إلى العربة
function addToCart(productName, price, image) {
let cartItems = JSON.parse(localStorage.getItem('cartItems')) || [];
const existingItem = cartItems.find(item => item.name === productName);

if (existingItem) {
    existingItem.quantity++;
} else {
    cartItems.push({
        name: productName,
        price: parseFloat(price),
        image: image,
        quantity: 1
    });
}

localStorage.setItem('cartItems', JSON.stringify(cartItems));

// عرض الإشعار
showNotification();
}