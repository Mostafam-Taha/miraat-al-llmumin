<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>الصفحة غير موجودة | ساحة العلم</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #4a6bff;
            --secondary-color: #ff6b6b;
            --dark-color: #2c3e50;
            --light-color: #f8f9fa;
            --text-color: #333;
            --text-light: #777;
            --success-color: #28a745;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
            --border-radius: 8px;
            --box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
            
            /* الألوان المخصصة التي قدمتها */
            --fc-btn-bg: #066ac9;
            --fc-btn-bg-co: #001e2b;
            --fc-color-btn-new: #d6293e;
            --fo-h1-h6-co: #21313c;
            --fc-p-co: #5f5f5f;
            --fc-par-color: #ffc107;
            --fc-prod-sale-color: #db3030;
            --fc-border-co-hevor: #066ac9;
            --fc-box-shadow-hevor: 0px 0px 0px 5px #066bc954;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Tajawal', sans-serif;
            background-color: var(--light-color);
            color: var(--text-color);
            height: 100vh;
            display: flex;
            flex-direction: column;
            background-image: radial-gradient(circle at 25% 50%, rgba(214, 41, 62, 0.1) 0%, rgba(255, 255, 255, 0) 50%);
        }
        
        .navbar {
            padding: 20px 5%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }
        
        .logo {
            height: 50px;
        }
        
        .error-container {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px;
            position: relative;
            overflow: hidden;
        }
        
        .error-content {
            text-align: center;
            max-width: 800px;
            position: relative;
            z-index: 2;
        }
        
        .error-illustration {
            max-width: 100%;
            height: 300px;
            margin-bottom: 30px;
            animation: float 6s ease-in-out infinite;
        }
        
        h1 {
            font-size: 2.5rem;
            margin-bottom: 15px;
            color: var(--fo-h1-h6-co);
        }
        
        p {
            font-size: 1.2rem;
            margin-bottom: 30px;
            line-height: 1.8;
            color: var(--fc-p-co);
        }
        
        .error-code {
            font-size: 5rem;
            font-weight: 700;
            color: var(--fc-color-btn-new);
            margin-bottom: 10px;
            opacity: 0.1;
            position: absolute;
            top: -30px;
            left: 50%;
            transform: translateX(-50%);
            z-index: -1;
        }
        
        .btn-group {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .btn {
            display: inline-block;
            padding: 12px 30px;
            border-radius: var(--border-radius);
            text-decoration: none;
            font-weight: 500;
            transition: var(--transition);
            border: 2px solid transparent;
            cursor: var(--fc-cursor-poi);
        }
        
        .btn-primary {
            background-color: var(--fc-btn-bg);
            color: white;
        }
        
        .btn-primary:hover {
            background-color: var(--fc-btn-bg-co);
            transform: translateY(-3px);
            box-shadow: var(--fc-box-shadow-hevor);
            border-color: var(--fc-border-co-hevor);
        }
        
        .btn-outline {
            border-color: var(--fc-color-btn-new);
            color: var(--fc-color-btn-new);
        }
        
        .btn-outline:hover {
            background-color: var(--fc-color-btn-new);
            color: white;
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(214, 41, 62, 0.2);
        }
        
        .search-box {
            margin: 30px auto;
            max-width: 500px;
            position: relative;
        }
        
        .search-input {
            width: 100%;
            padding: 15px 25px;
            border-radius: 50px;
            border: 1px solid #dfe2e1;
            font-size: 1rem;
            font-family: 'Tajawal', sans-serif;
            padding-right: 50px;
            box-shadow: var(--box-shadow);
            transition: var(--transition);
        }
        
        .search-input:focus {
            outline: none;
            border-color: var(--fc-border-co-hevor);
            box-shadow: var(--fc-box-shadow-hevor);
        }
        
        .search-btn {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--fc-btn-bg);
            cursor: pointer;
        }
        
        footer {
            text-align: center;
            padding: 20px;
            color: var(--text-light);
            font-size: 0.9rem;
            background-color: white;
            box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.05);
        }
        
        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-15px); }
            100% { transform: translateY(0px); }
        }
        
        .bg-shape {
            position: absolute;
            width: 300px;
            height: 300px;
            border-radius: 50%;
            background: linear-gradient(45deg, var(--fc-par-color), transparent);
            opacity: 0.1;
            z-index: -1;
        }
        
        .shape-1 {
            top: -100px;
            right: -100px;
        }
        
        .shape-2 {
            bottom: -50px;
            left: -100px;
            width: 200px;
            height: 200px;
            background: linear-gradient(45deg, var(--fc-color-btn-new), transparent);
            opacity: 0.1;
        }
        
        @media (max-width: 768px) {
            h1 {
                font-size: 2rem;
            }
            
            p {
                font-size: 1rem;
            }
            
            .error-illustration {
                height: 200px;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <a href="/">
            <img src="logo.png" alt="ساحة العلم" class="logo">
        </a>
    </nav>
    
    <div class="error-container">
        <div class="bg-shape shape-1"></div>
        <div class="bg-shape shape-2"></div>
        
        <div class="error-content">
            <div class="error-code">404</div>
            <img src="./image/element/undraw_page-not-found_6wni.png" alt="صفحة غير موجودة" class="error-illustration">
            <h1>عذرًا، الصفحة غير موجودة!</h1>
            <p>يبدو أن الصفحة التي تبحث عنها قد تم نقلها أو حذفها أو أنها غير متاحة مؤقتًا.<br> يمكنك العودة إلى الصفحة الرئيسية أو البحث عما تحتاجه.</p>
            
            <div class="search-box">
                <input type="text" class="search-input" placeholder="ابحث في ساحة العلم...">
                <button class="search-btn">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                </button>
            </div>
            
            <div class="btn-group">
                <a href="/" class="btn btn-primary">الصفحة الرئيسية</a>
                <a href="/contact" class="btn btn-outline">اتصل بنا</a>
            </div>
        </div>
    </div>
    
    <footer>
        جميع الحقوق محفوظة &copy; <span id="year"></span> ساحة العلم
    </footer>

    <script>
        document.getElementById('year').textContent = new Date().getFullYear();
    </script>
</body>
</html>