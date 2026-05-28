<?php
/** @var bool $is_admin */
/** @var bool $is_logged */
/** @var array|null $cur_user */
/** @var string $user_email */
/** @var array $sections */

$is_admin = $is_admin ?? false;
$is_logged = $is_logged ?? false;
$cur_user = $cur_user ?? null;
$user_email = $user_email ?? '';
$sections = $sections ?? [];
$page_title = $page_title ?? 'Về chúng tôi - Thoáng.vn';
$active_nav = 'about';

include __DIR__ . '/../partials/header.php';
?>

<div class="about-hero admin-section" id="sec-hero">
  <?php if ($is_admin): ?>
    <button class="admin-edit-btn" type="button" onclick="openSectionEditor('hero')">
      <i class="bi bi-pencil-fill"></i> Sửa Hero
    </button>
  <?php endif; ?>
  <div class="container">
    <div class="about-hero-eyebrow"><?= htmlspecialchars($sections['hero']['eyebrow'] ?? 'Về chúng tôi') ?></div>
    <h1 class="about-hero-title"><?= $sections['hero']['title'] ?? 'Tin tức thời <em>thoáng qua,</em><br>kiến thức ở lại.' ?></h1>
    <p class="about-hero-lead"><?= htmlspecialchars($sections['hero']['lead'] ?? '') ?></p>
  </div>
</div>

<div class="container mb-4 admin-section" id="sec-stats">
  <?php if ($is_admin): ?>
    <button class="admin-edit-btn" type="button" onclick="openSectionEditor('stats')">
      <i class="bi bi-pencil-fill"></i> Sửa thống kê
    </button>
  <?php endif; ?>
  <div class="stats-bar">
    <div class="row g-0">
      <?php foreach (($sections['stats'] ?? []) as $stat): ?>
        <div class="col-6 col-md-3 stat-item">
          <div class="stat-num"><?= htmlspecialchars($stat['num'] ?? '') ?><span class="stat-unit"><?= htmlspecialchars($stat['unit'] ?? '') ?></span></div>
          <div class="stat-label"><?= htmlspecialchars($stat['label'] ?? '') ?></div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<div class="page-body pt-0">
  <div class="container">
    <div class="row g-4">
      <div class="col-lg-7 col-xl-8">
        <section class="content-card admin-section" id="sec-mission">
          <?php if ($is_admin): ?>
            <button class="admin-edit-btn" type="button" onclick="openSectionEditor('mission')">
              <i class="bi bi-pencil-fill"></i> Sửa sứ mệnh
            </button>
          <?php endif; ?>
          <span class="section-label">Sứ mệnh</span>
          <div class="about-quote mb-3">
            <p><?= htmlspecialchars($sections['mission']['quote'] ?? '') ?></p>
          </div>
          <div class="content-card-body"><?= $sections['mission']['body'] ?? '' ?></div>
        </section>

        <section class="content-card admin-section" id="sec-values">
          <?php if ($is_admin): ?>
            <button class="admin-edit-btn" type="button" onclick="openSectionEditor('values')">
              <i class="bi bi-pencil-fill"></i> Sửa giá trị
            </button>
          <?php endif; ?>
          <span class="section-label">Giá trị cốt lõi</span>
          <?php foreach (($sections['values'] ?? []) as $value): ?>
            <div class="value-item">
              <div class="value-icon"><i class="bi <?= htmlspecialchars($value['icon'] ?? 'bi-star') ?>"></i></div>
              <div>
                <div class="value-title"><?= htmlspecialchars($value['title'] ?? '') ?></div>
                <div class="value-desc"><?= htmlspecialchars($value['desc'] ?? '') ?></div>
              </div>
            </div>
          <?php endforeach; ?>
        </section>

        <section class="content-card admin-section" id="sec-story">
          <?php if ($is_admin): ?>
            <button class="admin-edit-btn" type="button" onclick="openSectionEditor('story')">
              <i class="bi bi-pencil-fill"></i> Sửa câu chuyện
            </button>
          <?php endif; ?>
          <span class="section-label">Câu chuyện</span>
          <div class="timeline-list">
            <?php foreach (($sections['story'] ?? []) as $index => $step): ?>
              <div class="timeline-item">
                <div class="timeline-num"><?= $index + 1 ?></div>
                <div>
                  <div class="timeline-title"><?= htmlspecialchars($step['title'] ?? '') ?></div>
                  <div class="timeline-desc"><?= htmlspecialchars($step['desc'] ?? '') ?></div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </section>
      </div>

      <div class="col-lg-5 col-xl-4">
        <section class="content-card admin-section" id="sec-features">
          <?php if ($is_admin): ?>
            <button class="admin-edit-btn" type="button" onclick="openSectionEditor('features')">
              <i class="bi bi-pencil-fill"></i> Sửa tính năng
            </button>
          <?php endif; ?>
          <span class="section-label">Tính năng nổi bật</span>
          <div class="d-flex flex-wrap">
            <?php foreach (($sections['features'] ?? []) as $feature): ?>
              <span class="feat-pill">
                <i class="bi <?= htmlspecialchars($feature['icon'] ?? 'bi-star') ?>"></i>
                <?= htmlspecialchars($feature['text'] ?? '') ?>
              </span>
            <?php endforeach; ?>
          </div>
        </section>

        <section class="content-card admin-section" id="sec-team">
          <?php if ($is_admin): ?>
            <button class="admin-edit-btn" type="button" onclick="openSectionEditor('team')">
              <i class="bi bi-pencil-fill"></i> Sửa đội ngũ
            </button>
          <?php endif; ?>
          <span class="section-label">Đội ngũ</span>
          <div class="row g-3">
            <?php foreach (($sections['team'] ?? []) as $member): ?>
              <div class="col-6">
                <div class="team-card">
                  <div class="team-avatar"><?= htmlspecialchars($member['initials'] ?? '') ?></div>
                  <div class="team-name"><?= htmlspecialchars($member['name'] ?? '') ?></div>
                  <div class="team-role"><?= htmlspecialchars($member['role'] ?? '') ?></div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </section>

        <section class="content-card admin-section" id="sec-cta">
          <?php if ($is_admin): ?>
            <button class="admin-edit-btn" type="button" onclick="openSectionEditor('cta')">
              <i class="bi bi-pencil-fill"></i> Sửa CTA
            </button>
          <?php endif; ?>
          <div class="cta-box">
            <h3><?= htmlspecialchars($sections['cta']['title'] ?? 'Thoáng qua là nắm ngay.') ?></h3>
            <p><?= htmlspecialchars($sections['cta']['desc'] ?? '') ?></p>
            <div class="d-flex justify-content-center gap-2 flex-wrap">
              <a href="<?= htmlspecialchars($sections['cta']['btn1_url'] ?? 'index.php') ?>" class="btn-cta-gold">
                <i class="bi bi-play-fill me-1"></i><?= htmlspecialchars($sections['cta']['btn1_text'] ?? 'Đọc tin ngay') ?>
              </a>
              <?php if (!$is_admin): ?>
                <button class="btn-cta-outline" type="button" onclick="openContactModal()">
                  <i class="bi bi-envelope me-1"></i><?= htmlspecialchars($sections['cta']['btn2_text'] ?? 'Gửi góp ý') ?>
                </button>
              <?php endif; ?>
            </div>
          </div>
        </section>
      </div>
    </div>
  </div>
</div>

<?php if (!$is_admin): ?>
<div class="modal fade" id="contactModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="bi bi-envelope me-2"></i>Gửi góp ý</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div id="contactAlert"></div>
        <?php if (!$is_logged): ?>
          <div class="text-center py-4">
            <i class="bi bi-lock" style="font-size:2rem;color:var(--navy);"></i>
            <p class="mt-3">Bạn cần đăng nhập hoặc đăng ký để gửi góp ý cho Thoáng.vn.</p>
            <a href="login.php" class="auth-link" style="background:var(--navy);color:#fff;text-decoration:none;">
              <i class="bi bi-box-arrow-in-right"></i> Đăng nhập
            </a>
          </div>
        <?php else: ?>
          <div class="mb-3">
            <label class="form-label-sm">Email nhận phản hồi</label>
            <input type="email" id="c_email" class="form-control form-control-sm" value="<?= htmlspecialchars($user_email) ?>">
          </div>
          <div class="mb-3">
            <label class="form-label-sm">Tiêu đề</label>
            <input type="text" id="c_subject" class="form-control form-control-sm" placeholder="Ví dụ: Góp ý giao diện">
          </div>
          <div class="mb-3">
            <label class="form-label-sm">Nội dung</label>
            <textarea id="c_message" class="form-control form-control-sm" rows="5" placeholder="Mô tả góp ý của bạn..."></textarea>
          </div>
          <button type="button" class="btn-submit w-100" onclick="sendContact()">
            <i class="bi bi-send me-2"></i>Gửi góp ý
          </button>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
<?php endif; ?>

<?php if ($is_admin): ?>
<div class="modal fade" id="sectionEditorModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="sectionEditorTitle">Chỉnh sửa nội dung</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="section_key">
        <label class="form-label-sm">Dữ liệu JSON của section</label>
        <textarea id="section_data" class="form-control" rows="14" spellcheck="false"></textarea>
        <div class="text-muted mt-2" style="font-size:12px;">Dữ liệu sẽ được lưu qua AJAX JSON vào bảng about_sections.</div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Hủy</button>
        <button type="button" class="btn-save-section" onclick="saveAboutSection()">Lưu thay đổi</button>
      </div>
    </div>
  </div>
</div>
<?php endif; ?>

<script>
const ABOUT_SECTIONS = <?= json_encode($sections, JSON_UNESCAPED_UNICODE) ?>;

function openContactModal() {
  const modal = document.getElementById('contactModal');
  if (!modal) return;
  new bootstrap.Modal(modal).show();
}

function sendContact() {
  const alertBox = document.getElementById('contactAlert');
  const fd = new FormData();
  fd.append('email', document.getElementById('c_email').value.trim());
  fd.append('subject', document.getElementById('c_subject').value.trim());
  fd.append('message', document.getElementById('c_message').value.trim());

  fetch('api/contact_submit.php', { method: 'POST', body: fd })
    .then(response => response.json())
    .then(data => {
      alertBox.innerHTML = `<div class="alert alert-${data.success ? 'success' : 'danger'} py-2 px-3 mb-3" style="font-size:13px">${data.message || 'Không thể gửi góp ý.'}</div>`;
      if (data.success) {
        document.getElementById('c_subject').value = '';
        document.getElementById('c_message').value = '';
      }
    })
    .catch(() => {
      alertBox.innerHTML = '<div class="alert alert-danger py-2 px-3 mb-3">Có lỗi xảy ra.</div>';
    });
}

function openSectionEditor(key) {
  const modal = document.getElementById('sectionEditorModal');
  if (!modal) return;
  document.getElementById('section_key').value = key;
  document.getElementById('sectionEditorTitle').textContent = 'Chỉnh sửa section: ' + key;
  document.getElementById('section_data').value = JSON.stringify(ABOUT_SECTIONS[key] || {}, null, 2);
  new bootstrap.Modal(modal).show();
}

function saveAboutSection() {
  const key = document.getElementById('section_key').value;
  const raw = document.getElementById('section_data').value;
  let parsed;

  try {
    parsed = JSON.parse(raw);
  } catch (e) {
    alert('JSON không hợp lệ. Vui lòng kiểm tra lại dấu ngoặc và dấu phẩy.');
    return;
  }

  const fd = new FormData();
  fd.append('section_key', key);
  fd.append('section_data', JSON.stringify(parsed));

  fetch('api/about_save.php', { method: 'POST', body: fd })
    .then(response => response.json())
    .then(data => {
      if (!data.success) {
        alert(data.message || 'Không thể lưu nội dung.');
        return;
      }
      location.reload();
    })
    .catch(() => alert('Lỗi kết nối.'));
}
</script>

<?php include __DIR__ . '/../partials/footer.php'; ?>