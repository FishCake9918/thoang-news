// =============================================
// main.js — Thoáng.vn
// =============================================

// --- DATA ---
var articles = [
  { 
    id: 1, 
    cat: 'tech', 
    catLabel: 'Công nghệ', 
    catBg: '#EEEDFE', 
    catColor: '#534AB7',
    title: 'Meta ra mắt kính AR thế hệ mới tại CES 2026: Xóa nhòa ranh giới thực ảo',
    summary: 'Tại triển lãm CES 2026, Meta đã gây kinh ngạc khi trình làng mẫu kính AR "Orion Pro" với thiết kế siêu mỏng như kính thuốc thông thường. Thiết bị sở hữu màn hình Holographic thế hệ thứ 2, cho độ trễ hình ảnh dưới 5ms, giúp loại bỏ hoàn toàn cảm giác chóng mặt khi sử dụng lâu. Với mức giá 299 USD, đây được xem là đòn giáng mạnh vào các đối thủ trong phân khúc cao cấp, dự kiến sẽ phổ cập công nghệ thực tế tăng cường đến hàng triệu người dùng trong quý 3 năm nay.',
    source: 'VnExpress', 
    sourceInit: 'VN', 
    time: '2 phút trước', 
    tag: '#Meta #AR #CES2026', 
    url: 'article.html' 
  },
  { 
    id: 2, 
    cat: 'biz', 
    catLabel: 'Kinh tế', 
    catBg: '#FFF3CD', 
    catColor: '#856404',
    title: 'Thị trường bùng nổ: VN-Index chinh phục mốc 1.300 điểm nhờ sức kéo ngân hàng',
    summary: 'Kết thúc phiên giao dịch sáng nay, VN-Index tăng vọt 18 điểm, chính thức vượt ngưỡng kháng cự tâm lý quan trọng. Dòng tiền tập trung mạnh mẽ vào nhóm Big 4 ngân hàng và các mã bất động sản vốn hóa lớn, đẩy thanh khoản trên sàn HoSE đạt kỷ lục 21.000 tỷ đồng. Các chuyên gia tài chính nhận định, việc khối ngoại quay lại mua ròng hơn 320 tỷ đồng là tín hiệu tích cực, cho thấy niềm tin của nhà đầu tư quốc tế vào đà hồi phục của kinh tế Việt Nam đang dần trở lại.',
    source: 'CafeF', 
    sourceInit: 'CF', 
    time: '8 phút trước', 
    tag: '#ChungKhoan #VNIndex', 
    url: 'article.html' 
  },
  { 
    id: 3, 
    cat: 'world', 
    catLabel: 'Thế giới', 
    catBg: '#D1ECF1', 
    catColor: '#0c5460',
    title: 'Thượng đỉnh G20: Cam kết lịch sử về quỹ khí hậu 500 tỷ USD',
    summary: 'Các nhà lãnh đạo G20 vừa đạt được đồng thuận mang tính bước ngoặt trong nỗ lực chống biến đổi khí hậu toàn cầu. Theo thỏa thuận mới, các quốc gia phát triển sẽ đóng góp 500 tỷ USD vào một quỹ chung nhằm hỗ trợ các nước đang phát triển chuyển đổi sang năng lượng sạch. Mục tiêu cắt giảm 40% lượng phát thải carbon vào năm 2035 đã được đưa vào văn bản ký kết chính thức, bất chấp những tranh cãi gay gắt về lộ trình loại bỏ hoàn toàn nhiệt điện than trước đó.',
    source: 'Reuters VN', 
    sourceInit: 'RT', 
    time: '15 phút trước', 
    tag: '#G20 #ClimateChange', 
    url: 'article.html' 
  },
  { 
    id: 4, 
    cat: 'sport', 
    catLabel: 'Thể thao', 
    catBg: '#F8D7DA', 
    catColor: '#721c24',
    title: 'HLV Kim Sang-sik: "Chiến thắng này thuộc về sự đoàn kết của ĐTVN"',
    summary: 'Đội tuyển Việt Nam đã có một đêm thăng hoa tại sân Mỹ Đình khi đánh bại đối thủ khó chịu với tỷ số 2-0. Tiền đạo Tiến Linh mở tỷ số từ một pha đánh đầu hiểm hóc, trước khi Công Phượng ấn định chiến thắng bằng cú sút xa đầy quyết đoán ở phút 85. HLV Kim Sang-sik bày tỏ sự hài lòng tuyệt đối với lối chơi pressing tầm cao và khả năng giữ cự ly đội hình linh hoạt của các học trò, khẳng định đây là bước chạy đà hoàn hảo cho chiến dịch AFF Cup sắp tới.',
    source: 'Bóng Đá', 
    sourceInit: 'BD', 
    time: '22 phút trước', 
    tag: '#BongDaVN #TienLinh', 
    url: 'article.html' 
  },
  { 
    id: 5, 
    cat: 'tech', 
    catLabel: 'Công nghệ', 
    catBg: '#EEEDFE', 
    catColor: '#534AB7',
    title: 'Kỷ nguyên AI mới: OpenAI chính thức trình làng GPT-5 với khả năng lý luận như người',
    summary: 'Sáng nay, OpenAI đã làm cả thế giới công nghệ chấn động khi công bố phiên bản GPT-5. Điểm đột phá nằm ở kiến trúc "Reasoning Core" mới, cho phép mô hình không chỉ dự đoán từ tiếp theo mà còn có khả năng lập luận đa bước phức tạp để giải quyết các bài toán cấp độ Olympic. Đáng chú ý, nhờ tối ưu hóa phần cứng, GPT-5 tiêu thụ ít năng lượng hơn 30% so với tiền nhiệm nhưng lại đạt tốc độ phản hồi nhanh gấp đôi, mở ra triển vọng ứng dụng AI sâu rộng vào y khoa và nghiên cứu khoa học.',
    source: 'TechCrunch', 
    sourceInit: 'TC', 
    time: '35 phút trước', 
    tag: '#AI #OpenAI #GPT5', 
    url: 'article.html' 
  },
  { 
    id: 6, 
    cat: 'life', 
    catLabel: 'Đời sống', 
    catBg: '#D4EDDA', 
    catColor: '#155724',
    title: 'Giao thông xanh: TP.HCM chính thức vận hành tuyến xe buýt điện thông minh D4',
    summary: 'Người dân thành phố từ nay đã có thêm lựa chọn di chuyển hiện đại với tuyến xe buýt điện D4 kết nối khu vực Bến Thành và Thảo Điền. Với chiều dài lộ trình 14km, các xe buýt mới được trang bị wifi miễn phí, cổng sạc USB và hệ thống hỗ trợ người khuyết tật hiện đại. Giá vé chỉ từ 5.000 đồng cùng tần suất 8 phút/chuyến vào giờ cao điểm, tuyến D4 không chỉ giúp giảm thiểu ùn tắc mà còn là bước đi quan trọng của TP.HCM trong lộ trình chuyển đổi giao thông xanh bền vững.',
    source: 'Tuổi Trẻ', 
    sourceInit: 'TT', 
    time: '50 phút trước', 
    tag: '#GiaoThongXanh #TPHCM', 
    url: 'article.html' 
  }
];

var currentIdx = 0;
var activeCat  = 'all';
var filtered   = articles.slice();
var savedIds   = JSON.parse(localStorage.getItem('thoang_saved') || '[]');

// --- RENDER CARD ---
function renderCard() {
  if (currentIdx >= filtered.length) {
    showDone(); return;
  }
  var a = filtered[currentIdx];
  document.getElementById('cardCat').textContent    = a.catLabel;
  document.getElementById('cardCat').style.background = a.catBg;
  document.getElementById('cardCat').style.color      = a.catColor;
  document.getElementById('cardTitle').textContent   = a.title;
  document.getElementById('cardSummary').textContent = a.summary;
  document.getElementById('cardSource').textContent  = a.source;
  document.getElementById('cardSourceInit').textContent = a.sourceInit;
  document.getElementById('cardTime').textContent    = a.time;
  document.getElementById('cardTag').textContent     = a.tag;
  document.getElementById('cardLink').href           = a.url;
  document.getElementById('frontCard').style.transform = '';

  // progress
  var cur   = currentIdx + 1;
  var total = filtered.length;
  document.getElementById('progressLabel').textContent = cur + ' / ' + total;
  document.getElementById('progressBar').style.width   = Math.round(cur / total * 100) + '%';

  // save button state
  var isSaved = savedIds.indexOf(a.id) !== -1;
  document.getElementById('btnSave').classList.toggle('saved', isSaved);
  document.getElementById('saveIcon').className = isSaved ? 'bi bi-bookmark-fill' : 'bi bi-bookmark';

  // stack layers
  document.getElementById('back1').style.display = filtered.length - currentIdx > 1 ? '' : 'none';
  document.getElementById('back2').style.display = filtered.length - currentIdx > 2 ? '' : 'none';
}

// --- SKIP ---
function skipCard() {
  var card = document.getElementById('frontCard');
  card.style.transition = 'transform .35s ease, opacity .3s';
  card.style.transform  = 'translateX(-120%) rotate(-15deg)';
  card.style.opacity    = '0';
  setTimeout(function() {
    card.style.transition = '';
    card.style.opacity    = '1';
    currentIdx++;
    renderCard();
  }, 380);
}

// --- TOGGLE SAVE ---
function toggleSave() {
  if (currentIdx >= filtered.length) return;
  var id  = filtered[currentIdx].id;
  var idx = savedIds.indexOf(id);
  if (idx === -1) { savedIds.push(id); }
  else            { savedIds.splice(idx, 1); }
  localStorage.setItem('thoang_saved', JSON.stringify(savedIds));
  renderCard();
}

// --- DONE ---
function showDone() {
  document.getElementById('frontCard').classList.add('d-none');
  document.getElementById('doneCard').classList.remove('d-none');
  document.getElementById('actionArea').classList.add('d-none');
  document.getElementById('back1').style.display = 'none';
  document.getElementById('back2').style.display = 'none';
}

function restart() {
  currentIdx = 0;
  document.getElementById('frontCard').classList.remove('d-none');
  document.getElementById('doneCard').classList.add('d-none');
  document.getElementById('actionArea').classList.remove('d-none');
  renderCard();
}

// --- FILTER ---
document.querySelectorAll('.secondary-nav a').forEach(function(link) {
  link.addEventListener('click', function(e) {
    e.preventDefault();
    document.querySelectorAll('.secondary-nav a').forEach(function(l) { l.classList.remove('active'); });
    this.classList.add('active');
    var cat = this.dataset.cat;
    activeCat = cat;
    filtered  = cat === 'all' ? articles.slice() : articles.filter(function(a) { return a.cat === cat; });
    currentIdx = 0;
    document.getElementById('frontCard').classList.remove('d-none');
    document.getElementById('doneCard').classList.add('d-none');
    document.getElementById('actionArea').classList.remove('d-none');
    renderCard();
  });
});

// --- PRIMARY NAV active ---
// Tự động nhận diện trang hiện tại để set class active cho menu
const currentPath = window.location.pathname.split("/").pop();
document.querySelectorAll('.primary-nav .nav-link, .masthead .nav-link').forEach(function(link) {
    // Nếu href của link trùng với tên file hiện tại thì thêm class active
    if (link.getAttribute('href') === currentPath) {
        link.classList.add('active');
    }

    link.addEventListener('click', function() {
        document.querySelectorAll('.nav-link').forEach(l => l.classList.remove('active'));
        this.classList.add('active');
    });
});

// --- DRAG / SWIPE ---
var dragging = false, startX = 0, curX = 0;
var card = document.getElementById('frontCard');

card.addEventListener('mousedown', dragStart);
card.addEventListener('touchstart', dragStart, { passive: true });

function dragStart(e) {
  dragging = true;
  startX = curX = e.type === 'touchstart' ? e.touches[0].clientX : e.clientX;
  document.addEventListener('mousemove', dragMove);
  document.addEventListener('mouseup',   dragEnd);
  document.addEventListener('touchmove', dragMove, { passive: true });
  document.addEventListener('touchend',  dragEnd);
}
function dragMove(e) {
  if (!dragging) return;
  curX = e.type === 'touchmove' ? e.touches[0].clientX : e.clientX;
  var diff = curX - startX;
  card.style.transition = 'none';
  card.style.transform  = 'translateX(' + diff + 'px) rotate(' + diff * 0.04 + 'deg)';
  document.getElementById('hintSkip').style.opacity = diff < -20 ? Math.min((Math.abs(diff) - 20) / 60, 1) : 0;
  document.getElementById('hintSave').style.opacity = diff >  20 ? Math.min((Math.abs(diff) - 20) / 60, 1) : 0;
}
function dragEnd() {
  if (!dragging) return;
  dragging = false;
  document.removeEventListener('mousemove', dragMove);
  document.removeEventListener('mouseup',   dragEnd);
  document.removeEventListener('touchmove', dragMove);
  document.removeEventListener('touchend',  dragEnd);
  document.getElementById('hintSkip').style.opacity = 0;
  document.getElementById('hintSave').style.opacity = 0;
  var diff = curX - startX;
  if (diff < -80) {
    skipCard();
  } else if (diff > 80) {
    // swipe right = save + next
    var id = filtered[currentIdx] ? filtered[currentIdx].id : null;
    if (id && savedIds.indexOf(id) === -1) { savedIds.push(id); localStorage.setItem('thoang_saved', JSON.stringify(savedIds)); }
    skipCard();
  } else {
    card.style.transition = 'transform .3s ease';
    card.style.transform  = '';
  }
}

// --- INIT ---
renderCard();
