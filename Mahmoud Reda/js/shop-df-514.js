
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
// عرض المنتجات في السلة مع حساب الإجمالي
function displayCart() {
    const cartContainer = document.querySelector('.cart-container');
    cartContainer.innerHTML = ''; // تنظيف السلة قبل العرض الجديد

    let totalAmount = 0; // متغير لحساب الإجمالي

    cartItems.forEach(item => {
        const totalPrice = (item.price * item.quantity).toFixed(2); // حساب السعر الكلي للمنتج بناءً على الكمية
        totalAmount += parseFloat(totalPrice); // إضافة السعر الكلي للمنتج إلى الإجمالي

        const cartItem = document.createElement('div');
        cartItem.classList.add('cart-item');
        cartItem.innerHTML = `
            <div class="totle-order" id="carCartContainer">
                <div class="prod-co-45">
                    <div class="se-order-12">
                        <img src="${item.image}" alt="${item.name}" loading="lazy">
                    </div>
                    <div class="se-order-12">
                        <h5>${item.name}</h5>
                    </div>
                    <div class="se-order-124">
                        <span>${item.quantity}</span>
                    </div>
                    <div class="se-order-12">
                        <span>${totalPrice} E.G</span>
                    </div>
                </div>
                <hr class="line-des">
            </div>
        `;
        cartContainer.appendChild(cartItem);
    });

    // عرض الإجمالي
    const totalElement = document.createElement('div');
    totalElement.classList.add('cart-total');
    totalElement.innerHTML = `
        <div style="text-align: right; margin-top: 10px;">
            <span style="font-weight: bold; font-size: 14px;">${totalAmount.toFixed(2)} E.G</span>
        </div>
    `;
    cartContainer.appendChild(totalElement);
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

// عناصر DOM
const openMenuBtn = document.getElementById('openMenuBtn');
const closeMenuBtn = document.getElementById('closeMenuBtn');
const menuSection = document.getElementById('menuSection');

// فتح النافذة عند الضغط على "إضافة عنوان جديد"
openMenuBtn.addEventListener('click', () => {
    menuSection.style.display = 'flex'; // عرض النافذة
});

// إغلاق النافذة عند الضغط على "إلغاء الأمر"
closeMenuBtn.addEventListener('click', () => {
    menuSection.style.display = 'none'; // إخفاء النافذة
});
