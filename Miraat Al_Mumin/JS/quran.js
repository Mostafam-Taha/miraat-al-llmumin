$(document).ready(function () {
    // جلب قائمة السور من API
    $.get("https://api.alquran.cloud/v1/surah", function (data) {
        const suras = data.data;

        suras.forEach(function (sura) {
            const suraHTML = `
                <li class="sura-item" onclick="loadSuraDetails(${sura.number})">
                    <strong>${sura.number}. ${sura.name}</strong>
                    <p class="text-muted small">${sura.englishName}</p>
                </li>
            `;
            $("#suras-container").append(suraHTML);
        });
        });
    });

// دالة لتحميل تفاصيل السورة
function loadSuraDetails(suraNumber) {
        $.get(`https://api.alquran.cloud/v1/surah/${suraNumber}`, function (data) {
            const sura = data.data;
            let suraDetailsHTML = `<h3>سورة ${sura.name}</h3>`;
    sura.ayahs.forEach(function (ayah) {
    suraDetailsHTML += `
        <p><strong>${ayah.numberInSurah}.</strong> ${ayah.text}</p>
    `;
    });
    $("#sura-details").html(suraDetailsHTML);
});
}


function loadSuraDetails(suraNumber) {
$.get(`https://api.alquran.cloud/v1/surah/${suraNumber}`, function (data) {
    const sura = data.data;
    let suraDetailsHTML = `<h3> ${sura.name}</h3>`;
    sura.ayahs.forEach(function (ayah) {
    suraDetailsHTML += `
        <div class="ayah-item">
        <p><strong>${ayah.numberInSurah}.</strong> ${ayah.text}</p>
        <div class="ayah-actions">
            <button class="btn btn-sm btn-success play-audio" data-audio="${ayah.audio || ''}">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-play" viewBox="0 0 16 16"><path d="M10.804 8 5 4.633v6.734zm.792-.696a.802.802 0 0 1 0 1.392l-6.363 3.692C4.713 12.69 4 12.345 4 11.692V4.308c0-.653.713-.998 1.233-.696z"/></svg>
            </button>
            <button class="btn btn-sm btn-info show-tafsir" data-ayah="${ayah.number}">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-book" viewBox="0 0 16 16"><path d="M1 2.828c.885-.37 2.154-.769 3.388-.893 1.33-.134 2.458.063 3.112.752v9.746c-.935-.53-2.12-.603-3.213-.493-1.18.12-2.37.461-3.287.811zm7.5-.141c.654-.689 1.782-.886 3.112-.752 1.234.124 2.503.523 3.388.893v9.923c-.918-.35-2.107-.692-3.287-.81-1.094-.111-2.278-.039-3.213.492zM8 1.783C7.015.936 5.587.81 4.287.94c-1.514.153-3.042.672-3.994 1.105A.5.5 0 0 0 0 2.5v11a.5.5 0 0 0 .707.455c.882-.4 2.303-.881 3.68-1.02 1.409-.142 2.59.087 3.223.877a.5.5 0 0 0 .78 0c.633-.79 1.814-1.019 3.222-.877 1.378.139 2.8.62 3.681 1.02A.5.5 0 0 0 16 13.5v-11a.5.5 0 0 0-.293-.455c-.952-.433-2.48-.952-3.994-1.105C10.413.809 8.985.936 8 1.783"/></svg>
            </button>
            <button class="btn btn-sm btn-warning add-comment" data-ayah="${ayah.number}">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-chat" viewBox="0 0 16 16"><path d="M2.678 11.894a1 1 0 0 1 .287.801 11 11 0 0 1-.398 2c1.395-.323 2.247-.697 2.634-.893a1 1 0 0 1 .71-.074A8 8 0 0 0 8 14c3.996 0 7-2.807 7-6s-3.004-6-7-6-7 2.808-7 6c0 1.468.617 2.83 1.678 3.894m-.493 3.905a22 22 0 0 1-.713.129c-.2.032-.352-.176-.273-.362a10 10 0 0 0 .244-.637l.003-.01c.248-.72.45-1.548.524-2.319C.743 11.37 0 9.76 0 8c0-3.866 3.582-7 8-7s8 3.134 8 7-3.582 7-8 7a9 9 0 0 1-2.347-.306c-.52.263-1.639.742-3.468 1.105"/></svg>
            </button>
        </div>
        </div>
        <hr>
    `;
    });
    $("#sura-details").html(suraDetailsHTML);

    // ربط الأحداث بالأزرار
    $(".play-audio").on("click", function () {
    const audioURL = $(this).data("audio");
    if (audioURL) {
        const audio = new Audio(audioURL);
        audio.play();
        } else {
        alert("لا يتوفر صوت لهذه الآية حالياً.");
    }
    });

    $(".show-tafsir").on("click", function () {
    const ayahNumber = $(this).data("ayah");
    alert(`عرض التفسير للآية رقم ${ayahNumber}. (سيتم استبدال هذه الرسالة بالتفسير الحقيقي لاحقاً)`);
    });

    $(".add-comment").on("click", function () {
    const ayahNumber = $(this).data("ayah");
    const comment = prompt(`أضف تعليقاً للآية رقم ${ayahNumber}:`);
    if (comment) {
        alert(`تم إضافة تعليقك: "${comment}"`);
    }
    });
});
}





// دالة لتبديل حالة القائمة
function toggleSurasList() {
    const surasList = document.querySelector('.suras-list');
    surasList.classList.toggle('open'); // إضافة أو إزالة حالة الفتح
  }
  
  // إغلاق القائمة عند النقر خارجها
  document.addEventListener('click', (event) => {
    const surasList = document.querySelector('.suras-list');
    const listIcon = document.querySelector('.bi-list');
    
    // التحقق إذا كان النقر خارج القائمة والأيقونة
    if (!surasList.contains(event.target) && !listIcon.contains(event.target)) {
      surasList.classList.remove('open'); // إغلاق القائمة
    }
});
