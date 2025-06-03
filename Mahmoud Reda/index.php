<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Kufi+Arabic:wght@100..900&family=Raleway:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <title>الرئسية | Home</title>
    <link rel="icon" type="image/svg" sizes="32x32" href="image/logo/basket.svg">
</head>
<body>
    <div class="notification" id="notification">
        <div class="contanier">
            <div><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" class="bi bi-check-circle-fill" viewBox="0 0 16 16"><path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0m-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/></svg></div>
            <label>تم الأضافة إلى للعربة</label>
        </div>
    </div>
    <!-- Header -->
    <header class="header">
        <nav class="navbar">
            <a href="index.php">
                <div class="logo">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" class="bi bi-basket" viewBox="0 0 16 16">
                        <path d="M5.757 1.071a.5.5 0 0 1 .172.686L3.383 6h9.234L10.07 1.757a.5.5 0 1 1 .858-.514L13.783 6H15a1 1 0 0 1 1 1v1a1 1 0 0 1-1 1v4.5a2.5 2.5 0 0 1-2.5 2.5h-9A2.5 2.5 0 0 1 1 13.5V9a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h1.217L5.07 1.243a.5.5 0 0 1 .686-.172zM2 9v4.5A1.5 1.5 0 0 0 3.5 15h9a1.5 1.5 0 0 0 1.5-1.5V9zM1 7v1h14V7zm3 3a.5.5 0 0 1 .5.5v3a.5.5 0 0 1-1 0v-3A.5.5 0 0 1 4 10m2 0a.5.5 0 0 1 .5.5v3a.5.5 0 0 1-1 0v-3A.5.5 0 0 1 6 10m2 0a.5.5 0 0 1 .5.5v3a.5.5 0 0 1-1 0v-3A.5.5 0 0 1 8 10m2 0a.5.5 0 0 1 .5.5v3a.5.5 0 0 1-1 0v-3a.5.5 0 0 1 .5-.5m2 0a.5.5 0 0 1 .5.5v3a.5.5 0 0 1-1 0v-3a.5.5 0 0 1 .5-.5"/>
                    </svg>
                    <h1>Cane</h1>
                </div>
            </a>
            <div class="search-griy">
                <label for="search">
                    <i class="bi bi-search"></i>
                    <input type="search" name="search" id="search" placeholder="البحث عن منتج" required/>
                </label>
            </div>
            <div class="root-linge">
                <ul>
                    <i class="bi bi-heart"></i>
                    <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" class="bi bi-person" viewBox="0 0 16 16">
                        <path d="M8 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6m2-3a2 2 0 1 1-4 0 2 2 0 0 1 4 0m4 8c0 1-1 1-1 1H3s-1 0-1-1 1-4 6-4 6 3 6 4m-1-.004c-.001-.246-.154-.986-.832-1.664C11.516 10.68 10.289 10 8 10s-3.516.68-4.168 1.332c-.678.678-.83 1.418-.832 1.664z"/>
                    </svg>
                    <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" class="bi bi-bag-check" viewBox="0 0 16 16" onclick="openSection()">
                        <path fill-rule="evenodd" d="M10.854 8.146a.5.5 0 0 1 0 .708l-3 3a.5.5 0 0 1-.708 0l-1.5-1.5a.5.5 0 0 1 .708-.708L7.5 10.793l2.646-2.647a.5.5 0 0 1 .708 0"/>
                        <path d="M8 1a2.5 2.5 0 0 1 2.5 2.5V4h-5v-.5A2.5 2.5 0 0 1 8 1m3.5 3v-.5a3.5 3.5 0 1 0-7 0V4H1v10a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V4zM2 5h12v9a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1z"/>
                    </svg>
                </ul>
            </div>
        </nav>
        <div class="nav-block">
            <ul>
                <li><a href="index.php">البيت</a></li>
                <li><a href="shop.php">التسوق</a></li>
                <li><a href="">المحلات</a></li>
                <li><a href="">عنى</a></li>
                <li><a href="dashboard/products.php">حسابي</a></li>
            </ul>
        </div>
    </header>
    <section class="ads">
        <div class="contanier">
            <div class="fr-cont-c1">
                <div class="fr-show">
                    <p>شحن مجاني - الطلبات أو الطلب %100</p>
                </div>
                <div class="show-now">
                    <h1>شحن مجاني للطلبات التي تزيد عن <span>100جنية</span></h1>
                </div>
                <div class="fr-p">
                    <p>شحن مجاني للعملاء لأول مرة فقط، يتم تطبيق العروض الترويجية والخصومات اللاحقة</p>
                </div>
                <div class="fr-btn">
                    <button> اعرض الآن
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-right" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M1 8a.5.5 0 0 1 .5-.5h11.793l-3.147-3.146a.5.5 0 0 1 .708-.708l4 4a.5.5 0 0 1 0 .708l-4 4a.5.5 0 0 1-.708-.708L13.293 8.5H1.5A.5.5 0 0 1 1 8"/></svg>
                    </button>
                </div>
            </div>
            <div class="fr-cont-c2">
                <div class="fr-h-arrow-r-l">
                    <h2 style="margin: 11px 0;">الفئات المميزة</h2>
                    <div class="fr-arrow">
                        <div>
                            <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" class="bi bi-arrow-left-short" viewBox="0 0 16 16">
                                <path fill-rule="evenodd" d="M12 8a.5.5 0 0 1-.5.5H5.707l2.147 2.146a.5.5 0 0 1-.708.708l-3-3a.5.5 0 0 1 0-.708l3-3a.5.5 0 1 1 .708.708L5.707 7.5H11.5a.5.5 0 0 1 .5.5"/>
                            </svg>
                        </div>
                        <div>
                            <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" class="bi bi-arrow-right-short" viewBox="0 0 16 16">
                                <path fill-rule="evenodd" d="M4 8a.5.5 0 0 1 .5-.5h5.793L8.146 5.354a.5.5 0 1 1 .708-.708l3 3a.5.5 0 0 1 0 .708l-3 3a.5.5 0 0 1-.708-.708L10.293 8.5H4.5A.5.5 0 0 1 4 8"/>
                            </svg>
                        </div>
                    </div>
                </div>
                <div class="fr-card-con">
                    <div class="fr-card">
                        <img src="image/products-img/OIP.jfif" alt="لا يوجد صورة للمنتج" loading="lazy">
                        <div>
                            <p>شاي العروسة</p>
                        </div>
                    </div>
                    <div class="fr-card">
                        <img src="image/products-img/ساده-250.png" alt="لا يوجد صورة للمنتج" loading="lazy">
                        <div>
                            <p>بن موكا</p>
                        </div>
                    </div>
                    <div class="fr-card">
                        <img src="image/products-img/OIP (1).jfif" alt="لا يوجد صورة للمنتج" loading="lazy">
                        <div>
                            <p>بج</p>
                        </div>
                    </div>
                    <div class="fr-card">
                        <img src="image/products-img/238.png-550x550w.png.webp" alt="لا يوجد صورة للمنتج" loading="lazy">
                        <div>
                            <p>ابدومي بنكهت الخضار</p>
                        </div>
                    </div>
                    <div class="fr-card">
                        <img src="image/products-img/eggs-30pcs-500x500.webp" alt="لا يوجد صورة للمنتج" loading="lazy">
                        <div>
                            <p>بيض</p>
                        </div>
                    </div>
                    <div class="fr-card">
                        <img src="image/products-img/product-img-9.jpg" alt="لا يوجد صورة للمنتج" loading="lazy">
                        <div>
                            <p>شاي العروسة</p>
                        </div>
                    </div>
                    <div class="fr-card">
                        <img src="image/products-img/product-img-12.jpg" alt="لا يوجد صورة للمنتج" loading="lazy">
                        <div>
                            <p>شاي العروسة</p>
                        </div>
                    </div>
                    <div class="fr-card">
                        <img src="image/products-img/product-img-2.jpg" alt="لا يوجد صورة للمنتج" loading="lazy">
                        <div>
                            <p>شاي العروسة</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!--  -->
    <!--  -->
    <!--  -->
    <main ain id="popular-products-fd-fexid" class="popular-products-fd-fexid" style="display: none;">
        <div class="contanier">
            <div onclick="hideElement()"><svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="currentColor" class="bi bi-x" viewBox="0 0 16 16"><path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708"/></svg></div>
            <div class="griy-width">
                <div class="fg-view-image">
                <img src="image/products-img/product-img-2.jpg" alt="لا يوجد صورة للمنتج" loading="lazy">
                    <div class="fg-two-img">
                        <img src="image/products-img/product-img-2.jpg" alt="لا يوجد صورة للمنتج" loading="lazy">
                        <img src="image/products-img/product-img-2.jpg" alt="لا يوجد صورة للمنتج" loading="lazy">
                        <img src="image/products-img/product-img-2.jpg" alt="لا يوجد صورة للمنتج" loading="lazy">
                        <img src="image/products-img/product-img-2.jpg" alt="لا يوجد صورة للمنتج" loading="lazy">
                    </div>
                </div>
                <div class="fg-view-title">
                    <h1 class="fg-name-prod"></h1>
                    <div>
                        <span>0</span>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" class="bi bi-eye" viewBox="0 0 16 16"><path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8M1.173 8a13 13 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5s3.879 1.168 5.168 2.457A13 13 0 0 1 14.828 8q-.086.13-.195.288c-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5s-3.879-1.168-5.168-2.457A13 13 0 0 1 1.172 8z"/><path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5M4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0"/></svg>
                    </div>
                    <label>
                        <span>50 E.G<del>45 E.G</del></span>
                        <small> 10%off</small>
                    </label>
                    <hr>
                    <div class="fg-block-with">
                        <div>200g</div>
                        <div>500g</div>
                        <div>1kg</div>
                    </div>
                    <div class="griy-orders">
                        <div class="add-order">
                            <button><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-bag" viewBox="0 0 16 16"><path d="M8 1a2.5 2.5 0 0 1 2.5 2.5V4h-5v-.5A2.5 2.5 0 0 1 8 1m3.5 3v-.5a3.5 3.5 0 1 0-7 0V4H1v10a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V4zM2 5h12v9a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1z"/></svg>اضاف إلى العربة</button>
                        </div>
                        <div class="xmlns">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" width="14" height="14" fill="currentColor" class="bi bi-heart"><path d="m8 2.748-.717-.737C5.6.281 2.514.878 1.4 3.053c-.523 1.023-.641 2.5.314 4.385.92 1.815 2.834 3.989 6.286 6.357 3.452-2.368 5.365-4.542 6.286-6.357.955-1.886.838-3.362.314-4.385C13.486.878 10.4.28 8.717 2.01zM8 15C-7.333 4.868 3.279-3.04 7.824 1.143q.09.083.176.171a3 3 0 0 1 .176-.17C12.72-3.042 23.333 4.867 8 15"></path></svg>
                        </div>
                    </div>
                    <hr>
                    <div class="fg-menu">
                        <div><samp>asf</samp>: رمز المنتج</div>
                        <div><span>التوفر </span>: في المخزون</div>
                        <div>النوع : <span> فواكه</span></div>
                        <div>الشحن: <span> شحن خلال 01 يوم. (الاستلام مجاني اليوم)</span></div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <!--  -->
    <!--  -->
    <!--  -->
    <section class="popular-products">
        <div class="contanier">
            <?php
            // تضمين ملف الاتصال بقاعدة البيانات
            include('./php/config.php');

            // استعلام لاستخراج المنتجات من قاعدة البيانات
            $sql = "SELECT * FROM products";
            $result = mysqli_query($con, $sql);
            ?>

            <?php
            // التحقق من وجود نتائج
            if (mysqli_num_rows($result) > 0) {
                // عرض المنتجات باستخدام حلقة
                while ($row = mysqli_fetch_assoc($result)) {
                    // الحصول على البيانات من قاعدة البيانات
                    $productName = $row['product_name'];
                    $description = $row['description'];
                    $price = $row['price'];
                    $productImage = $row['product_image'];
                    $discountPrice = $price - ($price * 0.10); // حساب الخصم (10%)
            ?>
            <div class="fd-card">
                <div class="sale">
                    <div>
                        <span>خصم</span>
                        <span class="graay">10%</span>
                    </div>
                </div>
                <div class="fd-prod-img">
                    <!-- عرض الصورة من قاعدة البيانات -->
                    <img src="./php/uploads/<?= basename($productImage) ?>" alt="لا يوجد صورة أو المتصفح لا يدعم هذه الصيغة" loading="lazy">
                </div>
                <div class="fd-prod">
                    <div>
                        <p><?= $description ?></p>
                        <h4><?= $productName ?></h4>
                        <div class="fd-card-dle">
                            <svg onclick="showElement()" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" width="14" height="14" fill="currentColor" class="bi bi-eye" viewBox="0 0 16 16">
                                <path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8M1.173 8a13 13 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5s3.879 1.168 5.168 2.457A13 13 0 0 1 14.828 8q-.086.13-.195.288c-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5s-3.879-1.168-5.168-2.457A13 13 0 0 1 1.172 8z"/>
                                <path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5M4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0"/>
                            </svg>
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" width="14" height="14" fill="currentColor" class="bi bi-heart">
                                <path d="m8 2.748-.717-.737C5.6.281 2.514.878 1.4 3.053c-.523 1.023-.641 2.5.314 4.385.92 1.815 2.834 3.989 6.286 6.357 3.452-2.368 5.365-4.542 6.286-6.357.955-1.886.838-3.362.314-4.385C13.486.878 10.4.28 8.717 2.01zM8 15C-7.333 4.868 3.279-3.04 7.824 1.143q.09.083.176.171a3 3 0 0 1 .176-.17C12.72-3.042 23.333 4.867 8 15"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="fd-btn-price">
                        <button title="اضاف إلى السلة" onclick="addToCart('<?= $productName ?>', '<?= $discountPrice ?>', './php/uploads/<?= basename($productImage) ?>')">
                            <i class="bi bi-plus"></i> اضافة
                        </button>
                        <span><?= $discountPrice ?>     E.G<del><?= $price ?> E.G</del></span>
                    </div>    
                </div>
            </div>
            <?php
                }
            } else {
                echo "لا توجد منتجات لعرضها.";
            }
            ?>

            <?php
            // غلق الاتصال بقاعدة البيانات
            mysqli_close($con);
            ?>
        </div>
    </section>
    <section class="car-product">
        <div class="contanier">
            <div class="car-po-1">
                <div><h3>عربة التسوق</h3></div>
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-x-lg" viewBox="0 0 16 16" onclick="closeSection()">
                    <path d="M2.146 2.854a.5.5 0 1 1 .708-.708L8 7.293l5.146-5.147a.5.5 0 0 1 .708.708L8.707 8l5.147 5.146a.5.5 0 0 1-.708.708L8 8.707l-5.146 5.147a.5.5 0 0 1-.708-.708L7.293 8z"/>
                </svg>
            </div>
            <hr>
            <div class="cart-container">
                <div class="po-21">
                    <div class="flex-1">
                        <img src="./image/products-img/product-img-2.jpg" alt="لا توجد صورة منتج" loading="lazy">
                        <div class="title">
                            <h5>أسم المنتج</h5>
                            <div>
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash" viewBox="0 0 16 16"><path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0z"/><path d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4zM2.5 3h11V2h-11z"/></svg><span>إزالة</span>
                            </div>
                        </div>
                    </div>
                    <div class="flex-2">
                        <div class="po-54">
                            <div class="plus-left" onclick="decrement()">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-plus" viewBox="0 0 16 16">
                                    <path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4"/>
                                </svg>
                            </div>
                            <input type="text" id="counter" value="1">
                            <div class="plus-right" onclick="increment()"> <!-- نزيد الرقم عند الضغط -->
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-plus" viewBox="0 0 16 16">
                                    <path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4"/>
                                </svg>
                            </div>
                        </div>
                        <div class="po-78">
                            <p><span>21.60$</span></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="footer">
            <hr class="hr">
            <div class="co-footer">
                <div class="btn-co-12">
                    <button id="paychack">الدفع</button>
                </div>
                <div class="btn-co-10">
                    <button id="shop-clear">مواصلة التسوق</button>
                </div>
            </div>
        </div>
    </section>
    <script src="js/script.js"></script>
    <script src="js/car-product.js"></script>
    <script>
    </script>
</body>
</html>