document.addEventListener('DOMContentLoaded', function() {
    // Testimonials Slider
    const testimonials = document.querySelectorAll('.testimonial');
    const dots = document.querySelectorAll('.dot');
    const prevBtn = document.querySelector('.slider-prev');
    const nextBtn = document.querySelector('.slider-next');
    let currentIndex = 0;
    
    function showTestimonial(index) {
        testimonials.forEach(testimonial => testimonial.classList.remove('active'));
        dots.forEach(dot => dot.classList.remove('active'));
        
        testimonials[index].classList.add('active');
        dots[index].classList.add('active');
        currentIndex = index;
    }
    
    function nextTestimonial() {
        currentIndex = (currentIndex + 1) % testimonials.length;
        showTestimonial(currentIndex);
    }
    
    function prevTestimonial() {
        currentIndex = (currentIndex - 1 + testimonials.length) % testimonials.length;
        showTestimonial(currentIndex);
    }
    
    // Auto slide every 5 seconds
    let slideInterval = setInterval(nextTestimonial, 5000);
    
    // Event listeners
    nextBtn.addEventListener('click', () => {
        nextTestimonial();
        clearInterval(slideInterval);
        slideInterval = setInterval(nextTestimonial, 5000);
    });
    
    prevBtn.addEventListener('click', () => {
        prevTestimonial();
        clearInterval(slideInterval);
        slideInterval = setInterval(nextTestimonial, 5000);
    });
    
    dots.forEach((dot, index) => {
        dot.addEventListener('click', () => {
            showTestimonial(index);
            clearInterval(slideInterval);
            slideInterval = setInterval(nextTestimonial, 5000);
        });
    });
    
    // Smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            
            const targetId = this.getAttribute('href');
            if (targetId === '#') return;
            
            const targetElement = document.querySelector(targetId);
            if (targetElement) {
                window.scrollTo({
                    top: targetElement.offsetTop - 80,
                    behavior: 'smooth'
                });
            }
        });
    });
    
    // Mobile menu toggle (can be added if needed)
    // const menuToggle = document.createElement('button');
    // menuToggle.classList.add('menu-toggle');
    // menuToggle.innerHTML = '<i class="fas fa-bars"></i>';
    // document.querySelector('header .container').prepend(menuToggle);
    
    // menuToggle.addEventListener('click', function() {
    //     document.querySelector('nav').classList.toggle('active');
    // });
    
    // Sticky header
    window.addEventListener('scroll', function() {
        const header = document.querySelector('header');
        header.classList.toggle('sticky', window.scrollY > 100);
    });
    
    // Animation on scroll
    function animateOnScroll() {
        const elements = document.querySelectorAll('.service-card, .section-title, .section-subtitle');
        
        elements.forEach(element => {
            const elementPosition = element.getBoundingClientRect().top;
            const screenPosition = window.innerHeight / 1.3;
            
            if (elementPosition < screenPosition) {
                element.style.opacity = '1';
                element.style.transform = 'translateY(0)';
            }
        });
    }
    
    // Set initial state for animation
    document.querySelectorAll('.service-card').forEach(card => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(30px)';
        card.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
    });
    
    document.querySelectorAll('.section-title, .section-subtitle').forEach(el => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(30px)';
        el.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
    });
    
    // Run once on page load
    animateOnScroll();
    
    // Run on scroll
    window.addEventListener('scroll', animateOnScroll);
});




// العناصر
const toggleUser = document.getElementById('toggle-user');
const userBox = document.getElementById('user-box');
const overlay = document.getElementById('overlay');

// وظيفة فتح العنصر
toggleUser.addEventListener('click', () => {
    userBox.classList.add('show');
    overlay.classList.add('active');
});

// وظيفة إغلاق العنصر عند الضغط خارجًا
overlay.addEventListener('click', () => {
    userBox.classList.remove('show');
    overlay.classList.remove('active');
});

// عندما يتم تحميل الصفحة بالكامل
window.addEventListener("load", function () {
    // إخفاء شاشة التحميل بعد ثانية واحدة
    setTimeout(() => {
      // إخفاء شاشة التحميل
      const loadingScreen = document.getElementById("loading-screen");
      loadingScreen.style.display = "none";
  
      // عرض المحتوى
      const content = document.getElementById("content");
    //   content.style.display = "block";
    }, 1); // تأخير لمدة ثانية واحدة (1000 مللي ثانية)
});