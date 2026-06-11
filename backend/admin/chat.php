<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../models/Chat.php';

$chat = new Chat(db_connect());
$userId = (int)($_GET['user_id'] ?? $_POST['user_id'] ?? 0);

if (is_post() && $userId > 0) {
    $msg = (string)($_POST['noi_dung'] ?? '');
    if ($chat->send($userId, 'admin', $msg)) $_SESSION['success'] = 'Đã trả lời khách hàng.';
    else $_SESSION['error'] = 'Vui lòng nhập nội dung phản hồi.';
    redirect('admin/chat.php?user_id=' . $userId);
}

$conversations = $chat->conversations();
$messages = [];
if ($userId > 0) {
    $chat->markReadByAdmin($userId);
    $messages = $chat->messages($userId);
}
$success = flash('success');
$error = flash('error');
$page_title = 'Chat khách hàng';
require __DIR__ . '/_layout_start.php';
?>
<div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
    <div><h1 class="mb-1">Chat khách hàng</h1><p class="text-muted mb-0">Trao đổi và hỗ trợ khách hàng trực tiếp.</p></div>
</div>
<?php if ($success): ?><div class="alert alert-success rounded-4"><?= h($success) ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-danger rounded-4"><?= h($error) ?></div><?php endif; ?>
<div class="row g-4">
    <div class="col-lg-4">
        <div class="bg-white rounded-4 shadow-sm p-3">
            <h6 class="fw-bold mb-3">Cuộc trò chuyện</h6>
            <?php foreach ($conversations as $c): ?>
                <a class="d-block text-decoration-none border rounded-4 p-3 mb-2 <?= (int)$c['id_nguoi_dung']===$userId ? 'bg-primary-subtle border-primary' : 'bg-light' ?>" href="<?= BASE_URL ?>/admin/chat.php?user_id=<?= (int)$c['id_nguoi_dung'] ?>">
                    <div class="d-flex justify-content-between gap-2">
                        <strong class="text-dark"><?= h($c['ho_ten']) ?></strong>
                        <?php if ((int)$c['chua_doc'] > 0): ?><span class="badge bg-danger"><?= (int)$c['chua_doc'] ?></span><?php endif; ?>
                    </div>
                    <div class="small text-muted text-truncate"><?= h($c['noi_dung_moi'] ?? '') ?></div>
                </a>
            <?php endforeach; ?>
            <?php if (!$conversations): ?><div class="text-muted text-center py-4">Chưa có tin nhắn.</div><?php endif; ?>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="bg-white rounded-4 shadow-sm p-4">
            <?php if ($userId <= 0): ?>
                <div class="text-center text-muted py-5">Chọn một cuộc trò chuyện để trả lời.</div>
            <?php else: ?>
                <div class="chat-box mb-3" style="height:430px;overflow:auto;background:#f8fafc;border-radius:18px;padding:18px">
                    <?php foreach ($messages as $m): $admin = $m['nguoi_gui'] === 'admin'; ?>
                        <div class="d-flex mb-3 <?= $admin ? 'justify-content-end' : 'justify-content-start' ?>">
                            <div class="p-3 rounded-4 <?= $admin ? 'bg-primary text-white' : 'bg-white border' ?>" style="max-width:75%">
                                <div style="white-space:pre-wrap"><?= h($m['noi_dung']) ?></div>
                                <div class="small mt-1 <?= $admin ? 'text-white-50' : 'text-muted' ?>"><?= h($m['ngay_tao']) ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <form method="post" class="d-flex gap-2">
                    <input type="hidden" name="user_id" value="<?= (int)$userId ?>">
                    <input name="noi_dung" class="form-control rounded-pill px-4" placeholder="Nhập phản hồi cho khách...">
                    <button class="btn btn-primary rounded-pill px-4"><i class="bi bi-send me-1"></i>Gửi</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>
<script>document.addEventListener('DOMContentLoaded',()=>{const box=document.querySelector('.chat-box'); if(box) box.scrollTop=box.scrollHeight;});</script>
<?php require __DIR__ . '/_layout_end.php'; ?>
