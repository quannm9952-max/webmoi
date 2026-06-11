<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../controllers/AdminUserController.php';

$controller = new AdminUserController(db_connect());
$controller->handlePost();

$data = $controller->indexData();
$users = $data['users'];
$keyword = $data['keyword'];
$success = $data['success'];
$error = $data['error'];

$page_title = 'Người dùng';
require __DIR__ . '/_layout_start.php';
?>

<div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
    <div>
        <h1 class="mb-1">Quản lý người dùng</h1>
        <p class="text-muted mb-0">Quản lý vai trò và trạng thái tài khoản.</p>
    </div>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAddUser">
        <i class="bi bi-person-plus-fill me-2"></i>Thêm người dùng
    </button>
</div>

<?php if ($success): ?>
<script>document.addEventListener('DOMContentLoaded',()=>typeof Swal!=='undefined'&&Swal.fire({toast:true,position:'top-end',icon:'success',title:<?= json_encode($success, JSON_UNESCAPED_UNICODE) ?>,showConfirmButton:false,timer:3000,timerProgressBar:true}));</script>
<?php endif; ?>
<?php if ($error): ?>
<script>document.addEventListener('DOMContentLoaded',()=>typeof Swal!=='undefined'&&Swal.fire({icon:'error',title:'Lỗi',text:<?= json_encode($error, JSON_UNESCAPED_UNICODE) ?>,confirmButtonText:'Đóng',confirmButtonColor:'#0ea5e9'}));</script>
<?php endif; ?>

<div class="bg-white p-4 rounded-4 shadow-sm mb-4">
    <form method="get" class="row g-3 align-items-end">
        <div class="col-md-9">
            <label class="form-label">Tìm kiếm</label>
            <input name="q" value="<?= h($keyword) ?>" class="form-control" placeholder="Tên, email, role...">
        </div>
        <div class="col-md-3">
            <button class="btn btn-primary w-100">
                <i class="bi bi-search me-2"></i>Lọc
            </button>
        </div>
    </form>
</div>

<div class="bg-white p-4 rounded-4 shadow-sm table-responsive">
    <table class="table align-middle">
        <thead>
            <tr>
                <th>Người dùng</th>
                <th>Role</th>
                <th>Trạng thái</th>
                <th class="text-end">Thao tác</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $u): ?>
                <tr>
                    <td>
                        <div class="fw-800"><?= h($u['ho_ten'] ?? '') ?></div>
                        <div class="text-muted small"><?= h($u['email']) ?></div>
                    </td>
                    <td><span class="badge rounded-pill bg-primary"><?= h($u['ten_vai_tro']) ?></span></td>
                    <td>
                        <span class="badge rounded-pill bg-<?= $u['trang_thai'] === 'hoat_dong' ? 'success' : 'danger' ?>">
                            <?= h($u['trang_thai']) ?>
                        </span>
                    </td>
                    <td class="text-end">
                        <?php 
                        $isSuperAdminRow = (int)$u['id_nguoi_dung'] === 1;
                        $currentUserIsSuper = (int)$_SESSION['id_nguoi_dung'] === 1;
                        $isAnotherAdmin = (int)$u['id_vai_tro'] === 1 && !$currentUserIsSuper;
                        
                        if ((int)$u['id_nguoi_dung'] === (int)$_SESSION['id_nguoi_dung']): ?>
                            <span class="text-muted small fw-bold">Tài khoản hiện tại</span>
                        <?php elseif ($isSuperAdminRow): ?>
                            <span class="badge bg-danger p-2"><i class="bi bi-star-fill me-1"></i>Super Admin</span>
                        <?php elseif ($isAnotherAdmin): ?>
                            <span class="text-danger small fw-bold" title="Chỉ Super Admin mới được sửa Admin khác"><i class="bi bi-shield-lock-fill me-1"></i>Đã khóa quyền sửa</span>
                        <?php else: ?>
                            <form method="post" class="d-inline-flex gap-2 align-items-center ajax-role-form">
                                <input type="hidden" name="_ajax" value="1">
                                <input type="hidden" name="id_nguoi_dung" value="<?= (int)$u['id_nguoi_dung'] ?>">
                                <select name="id_vai_tro" class="form-select form-select-sm" style="width:120px">
                                    <option value="1" <?= (int)$u['id_vai_tro'] === 1 ? 'selected' : '' ?>>Admin</option>
                                    <option value="2" <?= (int)$u['id_vai_tro'] === 2 ? 'selected' : '' ?>>Customer</option>
                                </select>
                                <select name="trang_thai" class="form-select form-select-sm" style="width:130px">
                                    <option value="hoat_dong" <?= $u['trang_thai'] === 'hoat_dong' ? 'selected' : '' ?>>Hoạt động</option>
                                    <option value="khoa" <?= $u['trang_thai'] === 'khoa' ? 'selected' : '' ?>>Khóa</option>
                                </select>
                                <button class="btn btn-sm btn-primary">Lưu</button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>

            <?php if (empty($users)): ?>
                <tr>
                    <td colspan="4" class="text-center text-muted py-5">Không có người dùng phù hợp.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require __DIR__ . '/_layout_end.php'; ?>

<!-- Modal: Thêm người dùng mới -->
<div class="modal fade" id="modalAddUser" tabindex="-1" aria-labelledby="modalAddUserLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content rounded-4 shadow">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title fw-800" id="modalAddUserLabel">
          <i class="bi bi-person-plus-fill text-primary me-2"></i>Thêm người dùng mới
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form method="post">
        <input type="hidden" name="_action" value="create">
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-12">
              <label class="form-label fw-600">Họ và tên <span class="text-danger">*</span></label>
              <input type="text" name="ho_ten" class="form-control" placeholder="Nguyễn Văn A" required>
            </div>
            <div class="col-12">
              <label class="form-label fw-600">Email <span class="text-danger">*</span></label>
              <input type="email" name="email" class="form-control" placeholder="email@example.com" required>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-600">Số điện thoại</label>
              <input type="tel" name="so_dien_thoai" class="form-control" placeholder="10 chữ số" pattern="[0-9]{10}" maxlength="10">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-600">Vai trò <span class="text-danger">*</span></label>
              <select name="id_vai_tro" class="form-select">
                <option value="2" selected>Customer</option>
                <option value="1">Admin</option>
              </select>
            </div>
            <div class="col-12">
              <label class="form-label fw-600">Mật khẩu <span class="text-danger">*</span></label>
              <div class="position-relative">
                <input type="password" id="modal-pwd" name="mat_khau" class="form-control"
                       placeholder="ít nhất 6 ký tự" required minlength="6" style="padding-right:44px">
                <button type="button" class="btn position-absolute end-0 top-0 h-100 px-3 text-muted"
                        onclick="toggleModalPwd()" tabindex="-1">
                  <i class="bi bi-eye" id="modal-pwd-icon"></i>
                </button>
              </div>
            </div>
            <div class="col-12">
              <label class="form-label fw-600">Địa chỉ</label>
              <textarea name="dia_chi" class="form-control" rows="2" placeholder="Địa chỉ (tùy chọn)"></textarea>
            </div>
          </div>
        </div>
        <div class="modal-footer border-0 pt-0">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
          <button type="submit" class="btn btn-primary">
            <i class="bi bi-person-check-fill me-2"></i>Tạo tài khoản
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Ajax Form Role/Status Update
    document.querySelectorAll('.ajax-role-form').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const btn = this.querySelector('button');
            const fd = new FormData(this);
            
            const origText = btn.innerHTML;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
            btn.disabled = true;

            fetch(window.location.href, { method: 'POST', body: fd })
                .then(r => r.json())
                .then(d => {
                    if (d.success) {
                        Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: d.message, showConfirmButton: false, timer: 3000, timerProgressBar: true });
                        // Optionally update badges here
                    } else {
                        Swal.fire({ icon: 'error', title: 'Lỗi', text: d.message, confirmButtonText: 'Đóng', confirmButtonColor: '#0ea5e9' });
                    }
                })
                .catch(() => {
                    Swal.fire({ icon: 'error', title: 'Lỗi', text: 'Lỗi kết nối máy chủ.', confirmButtonText: 'Đóng', confirmButtonColor: '#0ea5e9' });
                })
                .finally(() => {
                    btn.innerHTML = origText;
                    btn.disabled = false;
                });
        });
    });
});

function toggleModalPwd() {
  const input = document.getElementById('modal-pwd');
  const icon  = document.getElementById('modal-pwd-icon');
  const isText = input.type === 'text';
  input.type = isText ? 'password' : 'text';
  icon.className = isText ? 'bi bi-eye' : 'bi bi-eye-slash';
}
</script>
