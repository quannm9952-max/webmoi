<?php
require_once __DIR__ . '/includes/bootstrap.php';
$page_title = 'Chính sách bảo mật — ' . APP_NAME;
require_once __DIR__ . '/includes/header.php';
?>

<div class="container py-5">

    <!-- Hero Banner -->
    <div class="py-5 px-4 mb-5 text-center text-white"
         style="background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 100%); border-radius: 24px;">
        <span class="badge mb-3 px-3 py-2 fw-600"
              style="background:rgba(255,255,255,.15); font-size:13px; border-radius:999px; letter-spacing:.04em;">
            <i class="bi bi-shield-lock me-1"></i> Pháp lý
        </span>
        <h1 class="fw-900 mb-2" style="letter-spacing:-.03em;">Chính sách bảo mật</h1>
        <p class="mb-0" style="color:#93c5fd; font-size:15px;">
            Cập nhật lần cuối: <strong>01/01/2025</strong> &nbsp;·&nbsp; Có hiệu lực từ: <strong>01/01/2025</strong>
        </p>
    </div>

    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb" style="font-size:14px;">
            <li class="breadcrumb-item"><a href="index.php" class="text-primary">Trang chủ</a></li>
            <li class="breadcrumb-item active">Chính sách bảo mật</li>
        </ol>
    </nav>

    <div class="row g-4 legal-page">

        <!-- Mục lục sidebar -->
        <div class="col-lg-3 d-none d-lg-block">
            <div class="p-3" style="background:#fff; border:1px solid #e2e8f0; border-radius:18px; position:sticky; top:110px;">
                <p class="fw-700 mb-3" style="color:#0f172a; font-size:13px; text-transform:uppercase; letter-spacing:.05em;">
                    <i class="bi bi-list-ul me-2 text-primary"></i>Mục lục
                </p>
                <ul class="list-unstyled mb-0" style="font-size:14px; line-height:1.4;">
                    <li class="mb-2"><a href="#priv-intro" class="text-muted text-decoration-none">Cam kết của chúng tôi</a></li>
                    <li class="mb-2"><a href="#priv-1" class="text-muted text-decoration-none">1. Thông tin thu thập</a></li>
                    <li class="mb-2"><a href="#priv-2" class="text-muted text-decoration-none">2. Mục đích sử dụng</a></li>
                    <li class="mb-2"><a href="#priv-3" class="text-muted text-decoration-none">3. Bảo mật thông tin</a></li>
                    <li class="mb-2"><a href="#priv-4" class="text-muted text-decoration-none">4. Chia sẻ thông tin</a></li>
                    <li class="mb-2"><a href="#priv-5" class="text-muted text-decoration-none">5. Cookies</a></li>
                    <li class="mb-2"><a href="#priv-6" class="text-muted text-decoration-none">6. Quyền của người dùng</a></li>
                    <li class="mb-2"><a href="#priv-7" class="text-muted text-decoration-none">7. Lưu trữ dữ liệu</a></li>
                    <li><a href="#priv-contact" class="text-muted text-decoration-none">8. Liên hệ</a></li>
                </ul>
            </div>
        </div>

        <!-- Nội dung chính -->
        <div class="col-lg-9">

            <section id="priv-intro">
                <div class="p-4 mb-4" style="background:#eff6ff; border-left:4px solid #2563eb; border-radius:12px;">
                    <p class="mb-0" style="color:#1e40af; font-size:15px;">
                        <i class="bi bi-shield-check-fill me-2"></i>
                        <strong>TechShop</strong> cam kết bảo vệ quyền riêng tư và thông tin cá nhân của khách hàng một cách an toàn, minh bạch và đúng quy định pháp luật. Chính sách này mô tả cách chúng tôi thu thập, sử dụng, lưu trữ và bảo vệ dữ liệu của bạn.
                    </p>
                </div>
            </section>

            <hr class="my-4" style="border-color:#e2e8f0;">

            <section id="priv-1">
                <h4 class="mb-3"><i class="bi bi-database me-2 text-primary"></i>1. Thông tin chúng tôi thu thập</h4>
                <p>Chúng tôi có thể thu thập các loại thông tin sau:</p>
                <p class="fw-700 mb-2" style="color:#0f172a;">a) Thông tin bạn cung cấp trực tiếp</p>
                <ul class="mb-3">
                    <li>Họ tên, địa chỉ email, số điện thoại</li>
                    <li>Địa chỉ giao hàng và thanh toán</li>
                    <li>Thông tin tài khoản (tên đăng nhập, mật khẩu đã được mã hóa)</li>
                    <li>Nội dung bình luận, đánh giá sản phẩm</li>
                </ul>
                <p class="fw-700 mb-2" style="color:#0f172a;">b) Thông tin thu thập tự động</p>
                <ul class="mb-4">
                    <li>Địa chỉ IP, loại trình duyệt, hệ điều hành</li>
                    <li>Trang bạn truy cập, thời gian truy cập, lịch sử tìm kiếm trên Website</li>
                    <li>Dữ liệu cookies và công nghệ theo dõi tương tự</li>
                </ul>
            </section>

            <hr class="my-4" style="border-color:#e2e8f0;">

            <section id="priv-2">
                <h4 class="mb-3"><i class="bi bi-bullseye me-2 text-primary"></i>2. Mục đích sử dụng thông tin</h4>
                <p>Thông tin được thu thập chỉ nhằm phục vụ các mục đích hợp pháp sau:</p>
                <ul class="mb-4">
                    <li><strong>Xử lý đơn hàng:</strong> Xác nhận, vận chuyển và hoàn tất giao dịch mua bán.</li>
                    <li><strong>Hỗ trợ khách hàng:</strong> Giải đáp thắc mắc, xử lý khiếu nại và yêu cầu đổi trả.</li>
                    <li><strong>Cải thiện dịch vụ:</strong> Phân tích hành vi sử dụng để tối ưu trải nghiệm và tính năng Website.</li>
                    <li><strong>Truyền thông tiếp thị:</strong> Gửi thông tin khuyến mãi, sản phẩm mới — chỉ khi bạn đã đồng ý nhận.</li>
                    <li><strong>Tuân thủ pháp luật:</strong> Thực hiện các nghĩa vụ pháp lý khi được cơ quan có thẩm quyền yêu cầu.</li>
                </ul>
            </section>

            <hr class="my-4" style="border-color:#e2e8f0;">

            <section id="priv-3">
                <h4 class="mb-3"><i class="bi bi-lock me-2 text-primary"></i>3. Bảo mật thông tin</h4>
                <p>TechShop áp dụng các biện pháp kỹ thuật và tổ chức phù hợp để bảo vệ thông tin của bạn:</p>
                <ul class="mb-3">
                    <li>Mã hóa dữ liệu truyền tải bằng giao thức <strong>HTTPS/TLS</strong>.</li>
                    <li>Mật khẩu được băm (hash) bằng thuật toán an toàn, không lưu dạng văn bản thô.</li>
                    <li>Kiểm soát truy cập nội bộ theo nguyên tắc quyền tối thiểu (least privilege).</li>
                    <li>Giám sát hệ thống và kiểm tra bảo mật định kỳ.</li>
                </ul>
                <div class="p-3 mb-4" style="background:#fef3c7; border-left:4px solid #f59e0b; border-radius:12px; font-size:14px;">
                    <i class="bi bi-exclamation-triangle-fill me-2" style="color:#92400e;"></i>
                    <span style="color:#78350f;">Dù chúng tôi cố gắng hết sức, không có hệ thống nào đảm bảo an toàn tuyệt đối. Bạn có trách nhiệm bảo mật thông tin đăng nhập của mình.</span>
                </div>
            </section>

            <hr class="my-4" style="border-color:#e2e8f0;">

            <section id="priv-4">
                <h4 class="mb-3"><i class="bi bi-share me-2 text-primary"></i>4. Chia sẻ thông tin với bên thứ ba</h4>
                <p><strong>TechShop cam kết không bán thông tin cá nhân của bạn cho bất kỳ bên thứ ba nào.</strong> Thông tin chỉ được chia sẻ trong các trường hợp giới hạn sau:</p>
                <ul class="mb-4">
                    <li><strong>Đối tác vận chuyển:</strong> Cung cấp họ tên, địa chỉ và số điện thoại cần thiết để giao hàng.</li>
                    <li><strong>Cổng thanh toán:</strong> Xử lý giao dịch tài chính một cách an toàn.</li>
                    <li><strong>Yêu cầu pháp lý:</strong> Tuân thủ lệnh của cơ quan nhà nước có thẩm quyền.</li>
                    <li><strong>Bảo vệ quyền lợi:</strong> Ngăn ngừa gian lận hoặc bảo vệ an toàn cho người dùng và TechShop.</li>
                </ul>
            </section>

            <hr class="my-4" style="border-color:#e2e8f0;">

            <section id="priv-5">
                <h4 class="mb-3"><i class="bi bi-browser-chrome me-2 text-primary"></i>5. Cookies và công nghệ theo dõi</h4>
                <p>Website sử dụng cookies và các công nghệ tương tự để:</p>
                <ul class="mb-3">
                    <li>Ghi nhớ phiên đăng nhập và giỏ hàng của bạn.</li>
                    <li>Phân tích lưu lượng truy cập và hành vi người dùng (Google Analytics, ...).</li>
                    <li>Cá nhân hóa nội dung và gợi ý sản phẩm phù hợp.</li>
                </ul>
                <p class="mb-4">Bạn có thể kiểm soát cookies thông qua cài đặt trình duyệt. Tuy nhiên, việc tắt cookies có thể ảnh hưởng đến một số tính năng của Website.</p>
            </section>

            <hr class="my-4" style="border-color:#e2e8f0;">

            <section id="priv-6">
                <h4 class="mb-3"><i class="bi bi-person-gear me-2 text-primary"></i>6. Quyền của người dùng</h4>
                <p>Theo quy định pháp luật hiện hành, bạn có các quyền sau đối với dữ liệu cá nhân của mình:</p>
                <ul class="mb-3">
                    <li><strong>Quyền truy cập:</strong> Yêu cầu xem thông tin cá nhân TechShop đang lưu trữ về bạn.</li>
                    <li><strong>Quyền chỉnh sửa:</strong> Cập nhật, sửa đổi thông tin không chính xác.</li>
                    <li><strong>Quyền xóa:</strong> Yêu cầu xóa dữ liệu cá nhân trong phạm vi quy định pháp luật cho phép.</li>
                    <li><strong>Quyền từ chối tiếp thị:</strong> Hủy đăng ký nhận email quảng cáo bất kỳ lúc nào.</li>
                    <li><strong>Quyền khiếu nại:</strong> Gửi khiếu nại đến cơ quan bảo vệ dữ liệu có thẩm quyền nếu quyền lợi bị xâm phạm.</li>
                </ul>
                <div class="p-3 mb-4" style="background:#dcfce7; border-left:4px solid #16a34a; border-radius:12px; font-size:14px;">
                    <i class="bi bi-check-circle-fill me-2" style="color:#166534;"></i>
                    <span style="color:#166534;">Để thực hiện các quyền trên, vui lòng liên hệ qua email <strong>cskh@techshop.vn</strong> với tiêu đề <em>"Yêu cầu về dữ liệu cá nhân"</em>. Chúng tôi sẽ phản hồi trong vòng <strong>5 ngày làm việc</strong>.</span>
                </div>
            </section>

            <hr class="my-4" style="border-color:#e2e8f0;">

            <section id="priv-7">
                <h4 class="mb-3"><i class="bi bi-archive me-2 text-primary"></i>7. Lưu trữ và xóa dữ liệu</h4>
                <p>Thông tin cá nhân của bạn được lưu trữ trên hệ thống máy chủ đặt tại Việt Nam và chỉ giữ lại trong thời gian cần thiết để thực hiện các mục đích đã nêu, hoặc theo yêu cầu pháp lý. Khi dữ liệu không còn cần thiết, chúng tôi sẽ xóa hoặc ẩn danh hóa một cách an toàn.</p>
                <p>Chính sách bảo mật này có thể được cập nhật để phản ánh thay đổi trong thực tiễn hoặc quy định pháp luật. Phiên bản mới sẽ được đăng tải trên trang này kèm theo ngày có hiệu lực.</p>
            </section>

            <hr class="my-4" style="border-color:#e2e8f0;">

            <section id="priv-contact">
                <h4 class="mb-3"><i class="bi bi-envelope me-2 text-primary"></i>8. Liên hệ về quyền riêng tư</h4>
                <p>Mọi thắc mắc, yêu cầu liên quan đến Chính sách bảo mật, vui lòng liên hệ:</p>
                <div class="p-4" style="background:#fff; border:1px solid #e2e8f0; border-radius:16px;">
                    <p class="mb-2"><i class="bi bi-building me-2 text-primary"></i><strong>TechShop — Bộ phận Bảo mật &amp; Quyền riêng tư</strong></p>
                    <p class="mb-2"><i class="bi bi-envelope me-2 text-primary"></i>Email: <a href="mailto:cskh@techshop.vn" class="text-primary">cskh@techshop.vn</a></p>
                    <p class="mb-0"><i class="bi bi-clock me-2 text-primary"></i>Giờ làm việc: Thứ 2 – Thứ 7, 8:00 – 17:30</p>
                </div>
            </section>

        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
