<?php
$currentPage = basename($_SERVER['PHP_SELF']);
$showFloatingAd = in_array($currentPage, ['index.php', 'article.php'], true);
$footerLinks = [
    ['label' => 'Trang chủ', 'href' => 'index.php'],
    ['label' => 'Đã lưu', 'href' => 'saved.php'],
    ['label' => 'Giới thiệu', 'href' => 'about.php'],
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
  <div class="floating-ad-head drag-handle" data-drag-handle>
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
function saveUserPreference(partial) {
  const prefs = window.thoangPrefs || {};
  const next = {
    theme: partial.theme || prefs.theme || 'light',
    article_font_size: partial.articleFontSize || partial.article_font_size || prefs.articleFontSize || 16
  };

  prefs.theme = next.theme;
  prefs.articleFontSize = parseInt(next.article_font_size, 10);
  window.thoangPrefs = prefs;

  const storageKey = prefs.storageKey || 'thoangPrefsGuest';
  localStorage.setItem(storageKey, JSON.stringify({
    theme: next.theme,
    articleFontSize: prefs.articleFontSize
  }));
  localStorage.setItem('thoangTheme', next.theme);

  if (!prefs.isLoggedIn) {
    localStorage.setItem('thoangArticleFontSize', String(next.article_font_size));
    return;
  }

  fetch('api/user_preferences.php', {
    method: 'POST',
    headers: {'Content-Type': 'application/json; charset=utf-8'},
    body: JSON.stringify(next)
  }).catch(function () {});
}

document.addEventListener('DOMContentLoaded', function() {
  const themeToggle = document.getElementById('themeToggle');
  const root = document.documentElement;
  const prefs = window.thoangPrefs || {};

  function syncThemeButton(theme) {
    if (!themeToggle) return;
    themeToggle.setAttribute('aria-pressed', theme === 'dark' ? 'true' : 'false');
    themeToggle.title = theme === 'dark' ? 'Đổi sang chế độ sáng' : 'Đổi sang chế độ tối';
  }

  syncThemeButton(root.getAttribute('data-theme') || 'light');

  if (themeToggle) {
    themeToggle.addEventListener('click', function() {
      const nextTheme = root.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
      root.setAttribute('data-theme', nextTheme);
      syncThemeButton(nextTheme);
      saveUserPreference({
        theme: nextTheme,
        articleFontSize: prefs.articleFontSize || 16
      });
    });
  }
});

document.addEventListener('DOMContentLoaded', function() {
  fetchWeather();
});

function fetchWeather() {
  const weatherWidget = document.getElementById('weatherWidget');
  if (!weatherWidget) return;

  const updateWeatherUI = (data) => {
    if (data.success) {
      const icon = data.icon || '';
      weatherWidget.innerHTML = `
        ${icon
          ? `<img src="${icon}" alt="${data.description}" style="width:20px; height:20px; margin-right:2px;" referrerpolicy="no-referrer" onerror="this.outerHTML='<i class=&quot;bi bi-cloud-sun-fill&quot; style=&quot;color:var(--gold);font-size:16px;margin-right:4px;&quot;></i>';">`
          : `<i class="bi bi-cloud-sun-fill" style="color:var(--gold);font-size:16px;margin-right:4px;"></i>`}
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
    fetch(`api/get_weather.php?city=Ho Chi Minh City,VN`)
      .then(response => response.json())
      .then(updateWeatherUI)
      .catch(() => weatherWidget.innerHTML = '<span class="opacity-75 fs-7">Lỗi kết nối.</span>');
  };

  if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(
      pos => fetchByCoords(pos.coords.latitude, pos.coords.longitude),
      fetchByDefaultCity,
      { enableHighAccuracy: false, timeout: 7000, maximumAge: 600000 }
    );
  } else {
    fetchByDefaultCity();
  }
}
</script>

<!-- Nút mở Chatbot -->
<button id="open-chatbot" style="position: fixed; bottom: 30px; right: 30px; padding: 12px 20px; background: #534AB7; color: #fff; border: none; border-radius: 50px; cursor: pointer; box-shadow: 0 4px 10px rgba(0,0,0,0.2); z-index: 9998; font-size: 16px; font-weight: bold; display: flex; align-items: center; gap: 8px; transition: transform 0.2s;">
    💬 Chat AI
</button>

<!-- Khung Chatbot HTML -->
<div id="chatbot-container" class="chatbot-panel" style="position: fixed; bottom: 90px; right: 30px; width: 350px; border: 1px solid #ddd; border-radius: 12px; background: #fff; box-shadow: 0 8px 24px rgba(0,0,0,0.15); z-index: 9999; display: none; flex-direction: column; overflow: hidden; font-family: sans-serif;">
    <div id="chatbot-drag-head" class="drag-handle" data-drag-handle style="background: #534AB7; color: #fff; padding: 15px; display: flex; justify-content: space-between; align-items: center;">
        <span style="font-weight: bold; font-size: 16px;">Trợ lý AI Thoáng.vn</span>
        <button id="close-chatbot" style="background: none; border: none; color: #fff; font-size: 20px; cursor: pointer;">&times;</button>
    </div>
    
    <div id="chat-messages" style="height: 350px; overflow-y: auto; padding: 15px; font-size: 14px; background: #f9f9f9; display: flex; flex-direction: column; gap: 10px;">
        <div style="background: #e5e7eb; padding: 10px 14px; border-radius: 16px; align-self: flex-start; max-width: 85%; color: #374151;">
            Xin chào! Tôi là trợ lý AI của Thoáng.vn. Bạn muốn tìm hiểu tin tức gì hôm nay?
        </div>
    </div>
    
    <div style="padding: 12px; border-top: 1px solid #eee; display: flex; background: #fff; align-items: center;">
        <input type="text" id="chat-input" placeholder="Nhập câu hỏi của bạn..." style="flex: 1; padding: 10px 15px; border: 1px solid #ccc; border-radius: 20px; outline: none; font-size: 14px;">
        <button id="send-chat" style="margin-left: 8px; padding: 10px 18px; background: #534AB7; color: #fff; border: none; border-radius: 20px; cursor: pointer; font-weight: bold; font-size: 14px;">Gửi</button>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const chatContainer = document.getElementById('chatbot-container');
    const openBtn = document.getElementById('open-chatbot');
    const closeBtn = document.getElementById('close-chatbot');
    const sendBtn = document.getElementById('send-chat');
    const chatInput = document.getElementById('chat-input');
    const messagesDiv = document.getElementById('chat-messages');

    openBtn.addEventListener('click', () => {
        chatContainer.style.display = 'flex';
        openBtn.style.display = 'none';
    });

    closeBtn.addEventListener('click', () => {
        chatContainer.style.display = 'none';
        openBtn.style.display = 'flex';
    });

    sendBtn.addEventListener('click', sendMessage);
    chatInput.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') sendMessage();
    });

    function sendMessage() {
        const text = chatInput.value.trim();
        if (!text) return;

        // Lấy ID bài viết từ URL nếu người dùng đang ở trang article.php
        const urlParams = new URLSearchParams(window.location.search);
        const articleId = urlParams.get('id');

        messagesDiv.innerHTML += `
            <div style="background: #EEEDFE; color: #534AB7; padding: 10px 14px; border-radius: 16px; align-self: flex-end; max-width: 85%;">
                ${text}
            </div>`;
        chatInput.value = '';
        messagesDiv.scrollTop = messagesDiv.scrollHeight;

        const loadingId = 'loading-' + Date.now();
        messagesDiv.innerHTML += `
            <div id="${loadingId}" style="background: #e5e7eb; padding: 10px 14px; border-radius: 16px; align-self: flex-start; max-width: 85%; color: #6b7280; font-style: italic;">
                Đang suy nghĩ...
            </div>`;
        messagesDiv.scrollTop = messagesDiv.scrollHeight;

        fetch('api/chat.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ message: text, article_id: articleId })
        })
        .then(async response => {
            const rawText = await response.text();
            try {
                return JSON.parse(rawText);
            } catch (e) {
                throw new Error("Phản hồi không hợp lệ: " + rawText.substring(0, 150));
            }
        })
        .then(data => {
            document.getElementById(loadingId).remove();
            if (data.success) {
                const reply = data.reply.replace(/\n/g, '<br>');
                messagesDiv.innerHTML += `
                    <div style="background: #e5e7eb; padding: 10px 14px; border-radius: 16px; align-self: flex-start; max-width: 85%; color: #374151;">
                        ${reply}
                    </div>`;
            } else {
                messagesDiv.innerHTML += `
                    <div style="background: #fee2e2; color: #b91c1c; padding: 10px 14px; border-radius: 16px; align-self: flex-start; max-width: 85%;">
                        Lỗi: ${data.message}
                    </div>`;
            }
            messagesDiv.scrollTop = messagesDiv.scrollHeight;
        })
        .catch(err => {
            document.getElementById(loadingId).remove();
            messagesDiv.innerHTML += `
                <div style="background: #fee2e2; color: #b91c1c; padding: 10px 14px; border-radius: 16px; align-self: flex-start; max-width: 85%; overflow-wrap: anywhere;">
                    <b>Lỗi hệ thống:</b> ${err.message}
                </div>`;
            messagesDiv.scrollTop = messagesDiv.scrollHeight;
        });
    }
});
</script>

<script src="scripts/script.js"></script>

</body>
</html>
