// =============================================
// main.js — Thoáng.vn (API & Session Driven)
// =============================================

let allNews = []; 
let currentIdx = 0;

// Tính thời gian hiển thị (VD: "10 phút trước")
function formatTimeAgo(dateString) {
  if (!dateString) return 'Vừa xong';
  const date = new Date(dateString);
  const seconds = Math.floor((new Date() - date) / 1000);
  
  let interval = seconds / 31536000;
  if (interval > 1) return Math.floor(interval) + " năm trước";
  interval = seconds / 2592000;
  if (interval > 1) return Math.floor(interval) + " tháng trước";
  interval = seconds / 86400;
  if (interval > 1) return Math.floor(interval) + " ngày trước";
  interval = seconds / 3600;
  if (interval > 1) return Math.floor(interval) + " giờ trước";
  interval = seconds / 60;
  if (interval > 1) return Math.floor(interval) + " phút trước";
  return "Vừa xong";
}

// UC01 & UC02: Load dữ liệu từ API
async function loadNews(category = 'all') {
  try {
    const response = await fetch(`api/get_news.php?category=${category}`);
    if (!response.ok) throw new Error('Network response was not ok');
    
    allNews = await response.json();
    currentIdx = 0;

    document.getElementById('frontCard').classList.remove('d-none');
    document.getElementById('doneCard').classList.add('d-none');
    document.getElementById('actionArea').classList.remove('d-none');

    renderCard();
  } catch (error) {
    console.error("Lỗi fetch dữ liệu:", error);
    document.getElementById('cardTitle').textContent = "Lỗi kết nối dữ liệu";
  }
}

function renderCard() {
  if (allNews.length === 0 || currentIdx >= allNews.length) {
    showDone();
    return;
  }

  const a = allNews[currentIdx];
  const catStyles = {
    tech:  { label: 'Công nghệ', bg: '#EEEDFE', color: '#534AB7' },
    biz:   { label: 'Kinh tế',   bg: '#FFF3CD', color: '#856404' },
    world: { label: 'Thế giới',  bg: '#D1ECF1', color: '#0c5460' },
    sport: { label: 'Thể thao',  bg: '#F8D7DA', color: '#721c24' },
    life:  { label: 'Đời sống',  bg: '#D4EDDA', color: '#155724' },
    edu:   { label: 'Giáo dục',  bg: '#E2E3E5', color: '#383d41' }
  };

  const style = catStyles[a.category] || catStyles['life'];
  const sourceName = a.source_name || 'Thoáng';

  document.getElementById('cardCat').textContent = style.label;
  document.getElementById('cardCat').style.background = style.bg;
  document.getElementById('cardCat').style.color = style.color;
  document.getElementById('cardTitle').textContent = a.title;
  document.getElementById('cardSummary').textContent = a.summary;
  document.getElementById('cardSource').textContent = sourceName;
  document.getElementById('cardSourceInit').textContent = sourceName.charAt(0).toUpperCase();
  document.getElementById('cardTime').textContent = formatTimeAgo(a.created_at);
  document.getElementById('cardTag').textContent = a.tags || '';
  document.getElementById('cardLink').href = `article.php?id=${a.id}`;
  document.getElementById('frontCard').style.transform = '';

  const cur = currentIdx + 1;
  const total = allNews.length;
  document.getElementById('progressLabel').textContent = `${cur} / ${total}`;
  document.getElementById('progressBar').style.width = `${Math.round((cur / total) * 100)}%`;

  document.getElementById('back1').style.display = total - currentIdx > 1 ? '' : 'none';
  document.getElementById('back2').style.display = total - currentIdx > 2 ? '' : 'none';
  
  // Hiển thị trạng thái Bookmark từ DB
  updateBookmarkUI(a.is_saved == 1);
}

// UC03: Lưu tin ẩn danh qua API
async function toggleSave() {
  if (allNews.length === 0 || currentIdx >= allNews.length) return;
  const newsId = allNews[currentIdx].id;

  const formData = new FormData();
  formData.append('news_id', newsId);

  try {
    const res = await fetch('api/toggle_bookmark.php', { method: 'POST', body: formData });
    const result = await res.json();

    if (result.status === 'success') {
      const isSaved = result.action === 'saved';
      updateBookmarkUI(isSaved);
      allNews[currentIdx].is_saved = isSaved ? 1 : 0;
    }
  } catch (error) {
    console.error("Lỗi bookmark:", error);
  }
}

function updateBookmarkUI(isSaved) {
  const btn = document.getElementById('btnSave');
  const icon = document.getElementById('saveIcon');
  if (btn && icon) {
      btn.classList.toggle('saved', isSaved);
      icon.className = isSaved ? 'bi bi-bookmark-fill' : 'bi bi-bookmark';
  }
}

// Hiệu ứng vuốt
function skipCard(direction = 'left') {
  const card = document.getElementById('frontCard');
  card.style.transition = 'transform .35s ease, opacity .3s';
  const moveX = direction === 'left' ? '-120%' : '120%';
  const rotate = direction === 'left' ? '-15deg' : '15deg';
  
  card.style.transform = `translateX(${moveX}) rotate(${rotate})`;
  card.style.opacity = '0';
  
  setTimeout(() => {
    card.style.transition = '';
    card.style.opacity = '1';
    currentIdx++;
    renderCard();
  }, 380);
}

function showDone() {
  document.getElementById('frontCard').classList.add('d-none');
  document.getElementById('doneCard').classList.remove('d-none');
  document.getElementById('actionArea').classList.add('d-none');
  document.getElementById('back1').style.display = 'none';
  document.getElementById('back2').style.display = 'none';
}

function restart() {
  const activeCat = document.querySelector('.secondary-nav a.active').dataset.cat;
  loadNews(activeCat);
}

// Event Listeners cho menu lọc tin
document.querySelectorAll('.secondary-nav a').forEach(link => {
  link.addEventListener('click', function(e) {
    e.preventDefault();
    document.querySelectorAll('.secondary-nav a').forEach(l => l.classList.remove('active'));
    this.classList.add('active');
    loadNews(this.dataset.cat);
  });
});

const currentPath = window.location.pathname.split("/").pop() || 'index.php';
document.querySelectorAll('.primary-nav .nav-link, .top-bar a').forEach(link => {
    if (link.getAttribute('href') === currentPath) link.classList.add('active');
});

// Logic Kéo/Vuốt
let dragging = false, startX = 0, curX = 0;
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
  let diff = curX - startX;
  card.style.transition = 'none';
  card.style.transform = `translateX(${diff}px) rotate(${diff * 0.04}deg)`;
  
  document.getElementById('hintSkip').style.opacity = diff < -20 ? Math.min((Math.abs(diff) - 20) / 60, 1) : 0;
  document.getElementById('hintSave').style.opacity = diff > 20 ? Math.min((Math.abs(diff) - 20) / 60, 1) : 0;
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
  
  let diff = curX - startX;
  if (diff < -80) {
    skipCard('left');
  } else if (diff > 80) {
    toggleSave(); 
    skipCard('right');
  } else {
    card.style.transition = 'transform .3s ease';
    card.style.transform = '';
  }
}

// Khởi chạy
document.addEventListener('DOMContentLoaded', () => loadNews());