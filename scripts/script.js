let allNews = [];
let currentIdx = 0;
let homeReadingTimer = null;

function formatTimeAgo(dateString) {
  if (!dateString) return 'Vừa xong';
  const date = new Date(dateString);
  const seconds = Math.floor((new Date() - date) / 1000);

  let interval = seconds / 31536000;
  if (interval > 1) return Math.floor(interval) + ' năm trước';
  interval = seconds / 2592000;
  if (interval > 1) return Math.floor(interval) + ' tháng trước';
  interval = seconds / 86400;
  if (interval > 1) return Math.floor(interval) + ' ngày trước';
  interval = seconds / 3600;
  if (interval > 1) return Math.floor(interval) + ' giờ trước';
  interval = seconds / 60;
  if (interval > 1) return Math.floor(interval) + ' phút trước';
  return 'Vừa xong';
}

async function loadNews(category = 'all') {
  try {
    const response = await fetch(`api/get_news.php?category=${encodeURIComponent(category)}`);
    if (!response.ok) throw new Error('Network response was not ok');

    allNews = await response.json();
    currentIdx = 0;

    document.getElementById('frontCard').classList.remove('d-none');
    document.getElementById('doneCard').classList.add('d-none');
    document.getElementById('actionArea').classList.remove('d-none');

    renderCard();
  } catch (error) {
    console.error('Lỗi fetch dữ liệu:', error);
    document.getElementById('cardTitle').textContent = 'Lỗi kết nối dữ liệu';
  }
}

function renderCard() {
  if (allNews.length === 0 || currentIdx >= allNews.length) {
    showDone();
    return;
  }

  const a = allNews[currentIdx];
  const catStyles = {
    tech: { label: 'Công nghệ', bg: '#EEEDFE', color: '#534AB7' },
    biz: { label: 'Kinh tế', bg: '#FFF3CD', color: '#856404' },
    world: { label: 'Thế giới', bg: '#D1ECF1', color: '#0c5460' },
    sport: { label: 'Thể thao', bg: '#F8D7DA', color: '#721c24' },
    life: { label: 'Đời sống', bg: '#D4EDDA', color: '#155724' },
    edu: { label: 'Giáo dục', bg: '#E2E3E5', color: '#383d41' },
    other: { label: 'Khác', bg: '#E5E7EB', color: '#374151' }
  };

  const style = catStyles[a.category] || {
    label: a.category_name || 'Tin tức',
    bg: a.color_bg || '#E5E7EB',
    color: a.color_text || '#374151'
  };
  const sourceName = a.source_name || 'Thoáng';

  document.getElementById('cardCat').textContent = style.label;
  document.getElementById('cardCat').style.background = style.bg;
  document.getElementById('cardCat').style.color = style.color;
  document.getElementById('cardTitle').textContent = a.title;
  document.getElementById('cardSummary').textContent = a.summary;
  document.getElementById('cardViews').textContent = Number(a.view_count || 0).toLocaleString('vi-VN');
  document.getElementById('cardComments').textContent = Number(a.comment_count || 0).toLocaleString('vi-VN');

  const imageWrap = document.getElementById('cardImageWrap');
  const image = document.getElementById('cardImage');
  if (a.image_url) {
    image.src = a.image_url;
    image.alt = a.title || 'Ảnh bài viết';
    imageWrap.classList.remove('d-none');
    image.onerror = () => {
      imageWrap.classList.add('d-none');
      image.removeAttribute('src');
    };
  } else {
    imageWrap.classList.add('d-none');
    image.removeAttribute('src');
    image.alt = '';
    image.onerror = null;
  }

  document.getElementById('cardSource').textContent = sourceName;
  document.getElementById('cardSourceInit').textContent = sourceName.charAt(0).toUpperCase();
  document.getElementById('cardTime').textContent = formatTimeAgo(a.published_at || a.created_at);
  document.getElementById('cardTag').textContent = a.tags || '';
  document.getElementById('cardLink').href = `article.php?id=${a.id}`;
  document.getElementById('frontCard').style.transform = '';

  const cur = currentIdx + 1;
  const total = allNews.length;
  document.getElementById('progressLabel').textContent = `${cur} / ${total}`;
  startHomeReadingTimer();

  document.getElementById('back1').style.display = total - currentIdx > 1 ? '' : 'none';
  document.getElementById('back2').style.display = total - currentIdx > 2 ? '' : 'none';

  updateBookmarkUI(a.is_saved == 1);
  updateNewsNavControls();
}

function showAuthRequired(message) {
  let modal = document.getElementById('authRequiredModal');
  if (!modal) {
    modal = document.createElement('div');
    modal.className = 'modal fade';
    modal.id = 'authRequiredModal';
    modal.tabIndex = -1;
    modal.innerHTML = `
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title"><i class="bi bi-lock me-2"></i>Yêu cầu đăng nhập</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <p class="mb-0" id="authRequiredMessage"></p>
          </div>
          <div class="modal-footer">
            <a href="register.php" class="btn btn-outline-secondary btn-sm">Đăng ký</a>
            <a href="login.php" class="btn btn-primary btn-sm" style="background:var(--navy);border-color:var(--navy)">Đăng nhập</a>
          </div>
        </div>
      </div>
    `;
    document.body.appendChild(modal);
  }

  document.getElementById('authRequiredMessage').textContent =
    message || 'Vui lòng đăng nhập hoặc đăng ký để sử dụng chức năng này.';
  new bootstrap.Modal(modal).show();
}

async function toggleSave() {
  if (allNews.length === 0 || currentIdx >= allNews.length) return false;
  const newsId = allNews[currentIdx].id;

  const formData = new FormData();
  formData.append('news_id', newsId);

  try {
    const res = await fetch('api/toggle_bookmark.php', { method: 'POST', body: formData });
    const result = await res.json();

    if (result.status === 'auth_required') {
      showAuthRequired(result.message);
      return false;
    }

    if (result.status === 'success') {
      const isSaved = result.action === 'saved';
      updateBookmarkUI(isSaved);
      allNews[currentIdx].is_saved = isSaved ? 1 : 0;
      return true;
    }
  } catch (error) {
    console.error('Lỗi bookmark:', error);
  }
  return false;
}

function updateBookmarkUI(isSaved) {
  const btn = document.getElementById('btnSave');
  const icon = document.getElementById('saveIcon');
  if (btn && icon) {
    btn.classList.toggle('saved', isSaved);
    icon.className = isSaved ? 'bi bi-bookmark-fill' : 'bi bi-bookmark';
  }
}

function animateCard(direction, onDone) {
  const card = document.getElementById('frontCard');
  if (!card) return;

  clearInterval(homeReadingTimer);
  card.style.transition = 'transform .35s ease, opacity .3s';
  const moveX = direction === 'left' ? '-120%' : '120%';
  const rotate = direction === 'left' ? '-15deg' : '15deg';

  card.style.transform = `translateX(${moveX}) rotate(${rotate})`;
  card.style.opacity = '0';

  setTimeout(() => {
    card.style.transition = '';
    card.style.opacity = '1';
    card.style.transform = '';
    onDone();
    renderCard();
  }, 380);
}

function nextCard() {
  if (allNews.length === 0 || currentIdx >= allNews.length) return;

  animateCard('left', () => {
    currentIdx++;
  });
}

function prevCard() {
  if (allNews.length === 0 || currentIdx <= 0) return;

  animateCard('right', () => {
    currentIdx--;
  });
}

function skipCard(direction = 'left') {
  if (direction === 'right') {
    prevCard();
    return;
  }

  nextCard();
}

function updateNewsNavControls() {
  const prevBtn = document.getElementById('btnPrev') || document.getElementById('btnSkip');
  const nextBtn = document.getElementById('btnNext');

  if (prevBtn) {
    prevBtn.id = 'btnPrev';
    prevBtn.title = 'Tin trước';
    prevBtn.setAttribute('aria-label', 'Tin trước');
    prevBtn.innerHTML = '<i class="bi bi-arrow-left"></i>';
    prevBtn.onclick = prevCard;
    prevBtn.disabled = currentIdx <= 0;
    prevBtn.classList.toggle('is-disabled', currentIdx <= 0);
  }

  if (nextBtn) {
    nextBtn.disabled = allNews.length === 0 || currentIdx >= allNews.length - 1;
    nextBtn.classList.toggle('is-disabled', allNews.length === 0 || currentIdx >= allNews.length - 1);
  }
}

function showDone() {
  clearInterval(homeReadingTimer);
  document.getElementById('frontCard').classList.add('d-none');
  document.getElementById('doneCard').classList.remove('d-none');
  document.getElementById('actionArea').classList.add('d-none');
  document.getElementById('back1').style.display = 'none';
  document.getElementById('back2').style.display = 'none';
}

function restart() {
  const activeCat = document.querySelector('.primary-nav .nav-link.active')?.dataset.cat || 'all';
  loadNews(activeCat);
}

document.querySelectorAll('.primary-nav [data-cat]').forEach(link => {
  link.addEventListener('click', function(e) {
    const currentPage = window.location.pathname.split('/').pop() || 'index.php';
    if (currentPage === 'index.php' || currentPage === '') {
      e.preventDefault();
      document.querySelectorAll('.primary-nav .nav-link[data-cat]').forEach(l => l.classList.remove('active'));
      const owningNavLink = this.classList.contains('nav-link')
        ? this
        : this.closest('.nav-dropdown')?.querySelector('.nav-link');
      if (owningNavLink) owningNavLink.classList.add('active');
      const cat = this.dataset.cat || 'all';
      const url = new URL(window.location.href);
      url.searchParams.set('category', cat);
      window.history.replaceState({}, '', url);
      loadNews(cat);
    }
  });
});

const currentPath = window.location.pathname.split('/').pop() || 'index.php';
document.querySelectorAll('.primary-nav .nav-link, .top-bar a').forEach(link => {
  if (link.getAttribute('href') === currentPath) link.classList.add('active');
});

let dragging = false;
let startX = 0;
let curX = 0;
const card = document.getElementById('frontCard');

if (card) {
  card.addEventListener('mousedown', dragStart);
  card.addEventListener('touchstart', dragStart, { passive: true });
}

function dragStart(e) {
  dragging = true;
  startX = curX = e.type === 'touchstart' ? e.touches[0].clientX : e.clientX;
  document.addEventListener('mousemove', dragMove);
  document.addEventListener('mouseup', dragEnd);
  document.addEventListener('touchmove', dragMove, { passive: true });
  document.addEventListener('touchend', dragEnd);
}

function dragMove(e) {
  if (!dragging) return;
  curX = e.type === 'touchmove' ? e.touches[0].clientX : e.clientX;
  const diff = curX - startX;
  card.style.transition = 'none';
  card.style.transform = `translateX(${diff}px) rotate(${diff * 0.04}deg)`;

  document.getElementById('hintSkip').style.opacity = diff < -20 ? Math.min((Math.abs(diff) - 20) / 60, 1) : 0;
  document.getElementById('hintSave').style.opacity = diff > 20 && currentIdx > 0 ? Math.min((Math.abs(diff) - 20) / 60, 1) : 0;
}

function dragEnd() {
  if (!dragging) return;
  dragging = false;
  document.removeEventListener('mousemove', dragMove);
  document.removeEventListener('mouseup', dragEnd);
  document.removeEventListener('touchmove', dragMove);
  document.removeEventListener('touchend', dragEnd);

  document.getElementById('hintSkip').style.opacity = 0;
  document.getElementById('hintSave').style.opacity = 0;

  const diff = curX - startX;
  if (diff < -80) {
    nextCard();
  } else if (diff > 80) {
    if (currentIdx > 0) {
      prevCard();
    } else {
      card.style.transition = 'transform .3s ease';
      card.style.transform = '';
    }
  } else {
    card.style.transition = 'transform .3s ease';
    card.style.transform = '';
  }
}

document.addEventListener('DOMContentLoaded', () => {
  const frontCard = document.getElementById('frontCard');

  // Không phải trang chủ thì không chạy loadNews
  if (!frontCard) return;

  const prevHint = document.getElementById('hintSave');
  const nextHint = document.getElementById('hintSkip');
  const nextBtn = document.querySelector('.btn-next-act');
  const saveBtn = document.getElementById('btnSave');

  if (prevHint) prevHint.textContent = 'TIN TRƯỚC';
  if (nextHint) nextHint.textContent = 'TIN TIẾP';
  if (nextBtn) {
    nextBtn.id = 'btnNext';
    nextBtn.title = 'Tiếp theo';
    nextBtn.onclick = nextCard;
  }
  if (saveBtn) saveBtn.title = 'Lưu lại';

  document.addEventListener('keydown', e => {
    if (e.target && ['INPUT', 'TEXTAREA', 'SELECT'].includes(e.target.tagName)) return;

    if (e.key === 'ArrowLeft') {
      prevCard();
    }

    if (e.key === 'ArrowRight') {
      nextCard();
    }
  });

  const initialCat = new URLSearchParams(window.location.search).get('category') || 'all';
  const activeLink = document.querySelector(`.primary-nav [data-cat="${initialCat}"]`);

  if (activeLink) {
    document.querySelectorAll('.primary-nav .nav-link[data-cat]').forEach(l => l.classList.remove('active'));
    const owningNavLink = activeLink.classList.contains('nav-link')
      ? activeLink
      : activeLink.closest('.nav-dropdown')?.querySelector('.nav-link');
    if (owningNavLink) owningNavLink.classList.add('active');
  }

  loadNews(initialCat);
});

/* =========================
   HOME READING TIMER 30S
========================= */

function startHomeReadingTimer() {
  const bar = document.getElementById('progressBar');
  const card = document.getElementById('frontCard');

  if (!bar || !card) return;

  clearInterval(homeReadingTimer);

  const duration = 30;
  const startTime = Date.now();

  bar.style.width = '0%';

  homeReadingTimer = setInterval(() => {
    const elapsed = (Date.now() - startTime) / 1000;
    const percent = Math.min((elapsed / duration) * 100, 100);

    bar.style.width = percent + '%';

    if (elapsed >= duration) {
      clearInterval(homeReadingTimer);

      card.classList.add('auto-next-out');

      setTimeout(() => {
        card.classList.remove('auto-next-out');
        nextCard();
      }, 650);
    }
  }, 50);
}

/* =================================
   FLOATING FOOTBALL ADS
================================= */

let ads = [];
let adIndex = 0;

async function loadAds() {
  try {
    const response = await fetch('https://dummyjson.com/products/category/laptops?limit=10');

    if (!response.ok) throw new Error('Không gọi được API quảng cáo');

    const data = await response.json();
    ads = data.products || [];

    if (ads.length > 0) {
      setTimeout(showAd, 500);
    }
  } catch (error) {
    console.error('Lỗi tải quảng cáo:', error);
  }
  setInterval(() => {
    adIndex = (adIndex + 1) % ads.length;
    showAd();
}, 8000);
}

function showAd() {
  const img = document.getElementById("adImage");
  const title = document.getElementById("adTitle");
  const price = document.getElementById("adPrice");
  const link = document.getElementById("adLink");

  if (!img || !title || !price || !link) return;
  if (!ads.length) return;

  img.classList.add("ad-changing");
  title.classList.add("ad-changing");
  price.classList.add("ad-changing");

  setTimeout(() => {

    const ad = ads[adIndex];

    img.src = ad.images?.[0] || ad.thumbnail;
    title.textContent = ad.title;
    price.textContent =
      (ad.price * 25000).toLocaleString("vi-VN") + "₫";

    link.href =
      "https://www.google.com/search?q=" + encodeURIComponent(ad.title);

    img.classList.remove("ad-changing");
    title.classList.remove("ad-changing");
    price.classList.remove("ad-changing");

  }, 300);
}

document.addEventListener("DOMContentLoaded", function () {
  const ad = document.getElementById("floatingAd");
  const adImage = document.getElementById("adImage");

  if (!ad) return;

  loadAds();

  ad.style.display = "none";

  setTimeout(() => {
    ad.style.display = "block";
  }, 5000);

  
  if (adImage) {
    setInterval(() => {
      if (!ads.length) return;

      adImage.style.opacity = "0";

      setTimeout(() => {
        adIndex = (adIndex + 1) % ads.length;
        showAd();
        adImage.style.opacity = "1";
      }, 350);

    }, 15000);
  }
});

let floatingAdMinimizeTimer = null;

window.minimizeFloatingAd = function () {
  const ad = document.getElementById("floatingAd");
  const btn = document.getElementById("minimizeAd");

  if (!ad || !btn) return;

  clearTimeout(floatingAdMinimizeTimer);

  if (ad.classList.contains("minimized")) {
    ad.classList.remove("minimized");
    btn.textContent = "−";
    return;
  
  }
  
  ad.classList.add("minimized");
  btn.textContent = "+";

  floatingAdMinimizeTimer = setTimeout(() => {
    ad.classList.remove("minimized");
    btn.textContent = "−";
  }, 8000);
};

window.closeFloatingAd = function () {
  const ad = document.getElementById("floatingAd");

  if (!ad) return;

  ad.style.display = "none";

  setTimeout(() => {
    ad.style.display = "block";
  }, 10000);
};

function makePanelDraggable(panel, handle) {
  if (!panel || !handle || panel.dataset.draggableReady === "1") return;
  panel.dataset.draggableReady = "1";

  let startX = 0;
  let startY = 0;
  let startLeft = 0;
  let startTop = 0;
  let moved = false;

  function clamp(value, min, max) {
    return Math.min(Math.max(value, min), max);
  }

  function placePanel(left, top) {
    const rect = panel.getBoundingClientRect();
    const margin = 8;
    const maxLeft = Math.max(margin, window.innerWidth - rect.width - margin);
    const maxTop = Math.max(margin, window.innerHeight - rect.height - margin);

    panel.style.left = clamp(left, margin, maxLeft) + "px";
    panel.style.top = clamp(top, margin, maxTop) + "px";
    panel.style.right = "auto";
    panel.style.bottom = "auto";
    panel.style.position = "fixed";
  }

  handle.addEventListener("pointerdown", function (event) {
    if (event.button !== undefined && event.button !== 0) return;
    if (event.target.closest("button, a, input, textarea, select")) return;

    const rect = panel.getBoundingClientRect();
    startX = event.clientX;
    startY = event.clientY;
    startLeft = rect.left;
    startTop = rect.top;
    moved = false;

    panel.classList.add("is-dragging-panel");
    panel.style.width = rect.width + "px";
    placePanel(startLeft, startTop);
    handle.setPointerCapture?.(event.pointerId);
    event.preventDefault();
  });

  handle.addEventListener("pointermove", function (event) {
    if (!panel.classList.contains("is-dragging-panel")) return;

    const dx = event.clientX - startX;
    const dy = event.clientY - startY;
    if (Math.abs(dx) > 2 || Math.abs(dy) > 2) moved = true;
    placePanel(startLeft + dx, startTop + dy);
  });

  function endDrag(event) {
    if (!panel.classList.contains("is-dragging-panel")) return;
    panel.classList.remove("is-dragging-panel");
    handle.releasePointerCapture?.(event.pointerId);
  }

  handle.addEventListener("pointerup", endDrag);
  handle.addEventListener("pointercancel", endDrag);

  handle.addEventListener("click", function (event) {
    if (!moved) return;
    event.preventDefault();
    event.stopPropagation();
    moved = false;
  }, true);

  window.addEventListener("resize", function () {
    const rect = panel.getBoundingClientRect();
    if (panel.style.left && panel.style.top) {
      placePanel(rect.left, rect.top);
    }
  });
}

document.addEventListener("DOMContentLoaded", function () {
  makePanelDraggable(
    document.getElementById("floatingAd"),
    document.querySelector("#floatingAd .floating-ad-head")
  );

  makePanelDraggable(
    document.getElementById("chatbot-container"),
    document.getElementById("chatbot-drag-head")
  );
});
