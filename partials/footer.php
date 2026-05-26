<?php
// partials/footer.php

$currentPage = basename($_SERVER['PHP_SELF']);
$showFloatingAd = in_array($currentPage, ['index.php', 'article.php']);
?>

<footer class="site-footer">
  <div class="container">
    <div class="footer-logo">Thoáng<span>.</span>vn</div>
    <div class="footer-tagline">Tin tức nhanh — Đọc ngay — Hiểu liền</div>
    <div class="footer-links">
      <a href="index.php">Trang chủ</a>
      <a href="article.php">Bài viết</a>
      <a href="saved.php">Đã lưu</a>
      <a href="about.php">Giới thiệu</a>
    </div>
    <div class="footer-copy">© 2026 Thoáng.vn — Dự án môn Lập trình Web · UEH</div>
  </div>
</footer>

<?php if ($showFloatingAd): ?>
<div id="floatingAd" class="floating-ad">
  <div class="floating-ad-head">
    <span>Quảng cáo</span>
    <div class="floating-ad-actions">
    <button id="minimizeAd" type="button" onclick="minimizeFloatingAd()">−</button>
    <button id="closeAd" type="button" onclick="closeFloatingAd()">×</button>
    </div>
  </div>

  <div class="floating-ad-body">
    <img id="adImage" src="images/ad1.jpg" alt="Quảng cáo bóng đá">
  </div>
</div>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="scripts/script.js"></script>
</body>
</html>
