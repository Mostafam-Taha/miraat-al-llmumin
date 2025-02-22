document.addEventListener("DOMContentLoaded", function() {
  const surahSelect = document.getElementById("surah");
  const ayahsContainer = document.getElementById("ayahs-container");
  const tafseerText = document.getElementById("tafseer-text");

  // استدعاء السور من API
  fetch('https://api.alquran.cloud/v1/surah')
      .then(response => response.json())
      .then(data => {
          const surahs = data.data;
          surahs.forEach(surah => {
              const option = document.createElement("option");
              option.value = surah.number;
              option.textContent = surah.name;
              surahSelect.appendChild(option);
          });
      });

  // تحميل الآيات عند اختيار السورة
  surahSelect.addEventListener("change", function() {
      const surahId = surahSelect.value;
      if (surahId) {
          fetch(`https://api.alquran.cloud/v1/surah/${surahId}`)
              .then(response => response.json())
              .then(data => {
                  const ayahs = data.data.ayahs;
                  ayahsContainer.innerHTML = ''; // مسح المحتوى السابق
                  ayahs.forEach(ayah => {
                      const ayahElement = document.createElement("div");
                      ayahElement.classList.add("ayah");
                      ayahElement.innerHTML = `<p><strong>${ayah.numberInSurah}:</strong> ${ayah.text}</p>`;
                      ayahsContainer.appendChild(ayahElement);

                      // إضافة التفسير عند الضغط على الآية
                      ayahElement.addEventListener("click", function() {
                          fetch(`https://api.alquran.cloud/v1/tafsir/${surahId}/${ayah.numberInSurah}`)
                              .then(response => response.json())
                              .then(tafseer => {
                                  tafseerText.textContent = tafseer.data.text;
                              })
                              .catch(error => {
                                  tafseerText.textContent = "لم يتم العثور على تفسير لهذه الآية.";
                              });
                      });
                  });
              });
      }
  });
});
