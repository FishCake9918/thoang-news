<?php
$currentPage = basename($_SERVER['PHP_SELF']);
$showFloatingAd = in_array($currentPage, ['index.php', 'article.php'], true);
$footerLinks = [
    ['label' => 'Trang chủ', 'href' => 'index.php'],
    ['label' => 'Đã lưu', 'href' => 'saved.php'],
    ['label' => 'Giới thiệu', 'href' => 'about.php'],
    ['label' => 'Chính trị', 'href' => 'index.php?category=politics'],
    ['label' => 'Thế giới', 'href' => 'index.php?category=world'],
    ['label' => 'Kinh tế', 'href' => 'index.php?category=biz'],
    ['label' => 'Thể thao', 'href' => 'index.php?category=sport'],
    ['label' => 'Công nghệ', 'href' => 'index.php?category=tech'],
    ['label' => 'Đời sống', 'href' => 'index.php?category=life'],
    ['label' => 'Giáo dục', 'href' => 'index.php?category=edu'],
    ['label' => 'Khác', 'href' => 'index.php?category=other'],
];
?>

<footer class="site-footer">
  <div class="container">
    <div class="footer-logo">Thoáng<span>.</span>vn</div>
    <div class="footer-tagline">Tin tức nhanh - Đọc ngay - Hiểu liền</div>
    <div class="footer-links">
      <?php foreach ($footerLinks as $link): ?>
        <a href="<?= htmlspecialchars($link['href']) ?>"><?= htmlspecialchars($link['label']) ?></a>
      <?php endforeach; ?>
    </div>
    <div class="footer-copy">© 2026 Thoáng.vn - Dự án môn Lập trình Web · UEH</div>
  </div>
</footer>

<?php if ($showFloatingAd): ?>
<div id="floatingAd" class="floating-ad">
  <div class="floating-ad-head">
    <span>Quảng cáo</span>
    <button type="button" class="btn-close" style="font-size:10px; filter:invert(1);" aria-label="Close" onclick="closeFloatingAd()"></button>
  </div>
  <div class="floating-ad-body bg-white p-2">
  <a id="adLink" href="#" target="_blank" style="text-decoration:none;color:inherit;">
    <img id="adImage"
         src=""
         style="width:100%;height:120px;object-fit:cover;border-radius:6px;">

    <div id="adTitle"
         style="font-size:13px;font-weight:600;margin-top:8px;">
    </div>

    <div id="adPrice"
         style="font-size:12px;color:#198754;">
    </div>
  </a>
</div>
</div>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
  fetchWeather();
});

function fetchWeather() {
  const weatherWidget = document.getElementById('weatherWidget');
  if (!weatherWidget) return;

  const updateWeatherUI = (data) => {
    if (data.success) {
      weatherWidget.innerHTML = `
        <img src="${data.icon}" alt="${data.description}" style="width:20px; height:20px; margin-right: 2px;">
        <span style="font-weight: 500;">${data.temp}°C</span>
        <span class="d-none d-lg-inline opacity-75">, ${data.city}</span>
        <span class="opacity-75 ms-1">- ${data.description}</span>
      `;
    } else {
      weatherWidget.innerHTML = `<span class="opacity-75" style="font-size:12px;">${data.message || 'Lỗi thời tiết.'}</span>`;
    }
  };

  const fetchByCoords = (lat, lon) => {
    fetch(`api/get_weather.php?lat=${lat}&lon=${lon}`)
      .then(response => response.json())
      .then(updateWeatherUI)
      .catch(() => weatherWidget.innerHTML = '<span class="opacity-75 fs-7">Lỗi kết nối.</span>');
  };

  const fetchByDefaultCity = () => {
    fetch(`api/get_weather.php?city=Ho Chi Minh City`)
      .then(response => response.json())
      .then(updateWeatherUI)
      .catch(() => weatherWidget.innerHTML = '<span class="opacity-75 fs-7">Lỗi kết nối.</span>');
  };

  if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(pos => fetchByCoords(pos.coords.latitude, pos.coords.longitude), fetchByDefaultCity);
  } else {
    fetchByDefaultCity();
  }
}
</script>

<script src="/thoang-news/scripts/script.js"></script>

</body>
</html>
