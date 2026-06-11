<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/models/Chat.php';
require_login();

$uid = (int)$_SESSION['id_nguoi_dung'];
$chat = new Chat(db_connect());

if (is_post()) {
    $msg = (string)($_POST['noi_dung'] ?? '');
    if ($chat->send($uid, 'khach', $msg)) {
        $_SESSION['success'] = 'Đã gửi tin nhắn cho TechShop.';
    } else {
        $_SESSION['error'] = 'Vui lòng nhập nội dung tin nhắn.';
    }
    redirect('chat.php');
}

$messages = $chat->messages($uid);
$page_title = 'Chat hỗ trợ — TechShop';
$success = flash('success');
$error = flash('error');
require __DIR__ . '/includes/header.php';
?>
<main class="container py-4">
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb" style="font-size:13px">
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/account.php">Tài khoản</a></li>
            <li class="breadcrumb-item active">Chat hỗ trợ</li>
        </ol>
    </nav>

    <div class="section-heading mb-4"><i class="bi bi-chat-dots me-2"></i>Chat với TechShop</div>

    <div class="bg-white rounded-4 shadow-sm p-4">
        <div class="chat-box mb-3" style="height:420px;overflow:auto;background:#f8fafc;border-radius:18px;padding:18px">
            <?php if (!$messages): ?>
                <div class="text-center text-muted py-5">Bạn cần hỗ trợ về đơn hàng hoặc sản phẩm? Nhắn cho TechShop tại đây.</div>
            <?php endif; ?>
            <?php foreach ($messages as $m): $mine = $m['nguoi_gui'] === 'khach'; ?>
                <div class="d-flex mb-3 <?= $mine ? 'justify-content-end' : 'justify-content-start' ?>">
                    <div class="p-3 rounded-4 <?= $mine ? 'bg-primary text-white' : 'bg-white border' ?>" style="max-width:72%">
                        <div style="white-space:pre-wrap"><?= h($m['noi_dung']) ?></div>
                        <div class="small mt-1 <?= $mine ? 'text-white-50' : 'text-muted' ?>"><?= h($m['ngay_tao']) ?></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <form method="post" class="d-flex gap-2">
            <input name="noi_dung" class="form-control rounded-pill px-4" placeholder="Nhập tin nhắn...">
            <button class="btn btn-primary rounded-pill px-4"><i class="bi bi-send me-1"></i>Gửi</button>
        </form>
    </div>
</main>
<script>
document.addEventListener('DOMContentLoaded',()=>{const box=document.querySelector('.chat-box'); if(box) box.scrollTop=box.scrollHeight;});
</script>
<?php require __DIR__ . '/includes/footer.php'; ?>
