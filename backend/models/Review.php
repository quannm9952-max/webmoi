<?php
declare(strict_types=1);

class Review
{
    private array $cols = [];

    public function __construct(private PDO $pdo)
    {
        $stmt = $this->pdo->query("SHOW COLUMNS FROM danh_gia_san_pham");
        $this->cols = array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'Field');
    }

    private function hasCol(string $name): bool
    {
        return in_array($name, $this->cols, true);
    }

    private function reviewIdCol(): string
    {
        return $this->hasCol('id_danh_gia') ? 'id_danh_gia' : 'id';
    }

    private function contentCol(): string
    {
        return $this->hasCol('noi_dung') ? 'noi_dung' : 'binh_luan';
    }

    public function orderCanReview(int $orderId, int $userId): bool
    {
        $stmt = $this->pdo->prepare("SELECT 1 FROM don_hang WHERE id_don_hang = :oid AND id_nguoi_dung = :uid AND trang_thai_don_hang = 'da_giao' LIMIT 1");
        $stmt->execute([':oid' => $orderId, ':uid' => $userId]);
        return (bool)$stmt->fetchColumn();
    }

    public function getReviewableItems(int $orderId, int $userId): array
    {
        $idCol = $this->reviewIdCol();
        $contentCol = $this->contentCol();
        $updatedSelect = $this->hasCol('ngay_cap_nhat') ? ', dg.ngay_cap_nhat' : '';

        $sql = "
            SELECT ct.id_chi_tiet, ct.id_don_hang, ct.id_san_pham, ct.so_luong, ct.don_gia, ct.thanh_tien,
                   sp.ten_san_pham, sp.hinh_anh_chinh,
                   dg.`$idCol` AS id_danh_gia, dg.so_sao, dg.`$contentCol` AS noi_dung, dg.ngay_tao AS ngay_danh_gia
                   $updatedSelect
            FROM chi_tiet_don_hang ct
            JOIN don_hang dh ON dh.id_don_hang = ct.id_don_hang
            JOIN san_pham sp ON sp.id_san_pham = ct.id_san_pham
            LEFT JOIN danh_gia_san_pham dg
                   ON dg.id_don_hang = ct.id_don_hang
                  AND dg.id_san_pham = ct.id_san_pham
                  AND dg.id_nguoi_dung = dh.id_nguoi_dung
            WHERE ct.id_don_hang = :oid
              AND dh.id_nguoi_dung = :uid
              AND dh.trang_thai_don_hang = 'da_giao'
            ORDER BY ct.id_chi_tiet ASC
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':oid' => $orderId, ':uid' => $userId]);
        return $stmt->fetchAll();
    }

    public function save(int $orderId, int $productId, int $userId, int $stars, string $content): array
    {
        $stars = max(1, min(5, $stars));
        $content = trim($content);

        if (!$this->orderCanReview($orderId, $userId)) {
            return ['success' => false, 'message' => 'Chỉ có thể đánh giá sau khi đơn hàng đã giao.'];
        }
        if ($content === '') {
            return ['success' => false, 'message' => 'Vui lòng nhập nội dung feedback.'];
        }

        $stmt = $this->pdo->prepare(" 
            SELECT 1
            FROM chi_tiet_don_hang ct
            JOIN don_hang dh ON dh.id_don_hang = ct.id_don_hang
            WHERE ct.id_don_hang = :oid
              AND ct.id_san_pham = :pid
              AND dh.id_nguoi_dung = :uid
              AND dh.trang_thai_don_hang = 'da_giao'
            LIMIT 1
        ");
        $stmt->execute([':oid' => $orderId, ':pid' => $productId, ':uid' => $userId]);
        if (!$stmt->fetchColumn()) {
            return ['success' => false, 'message' => 'Sản phẩm không thuộc đơn hàng đã giao của bạn.'];
        }

        $idCol = $this->reviewIdCol();
        $contentCol = $this->contentCol();

        try {
            $find = $this->pdo->prepare("SELECT `$idCol` FROM danh_gia_san_pham WHERE id_don_hang = :oid AND id_san_pham = :pid AND id_nguoi_dung = :uid LIMIT 1");
            $find->execute([':oid' => $orderId, ':pid' => $productId, ':uid' => $userId]);
            $existingId = $find->fetchColumn();

            if ($existingId) {
                $sql = "UPDATE danh_gia_san_pham SET so_sao = :stars, `$contentCol` = :content";
                if ($this->hasCol('ngay_cap_nhat')) {
                    $sql .= ", ngay_cap_nhat = CURRENT_TIMESTAMP";
                }
                $sql .= " WHERE `$idCol` = :rid";
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute([':stars' => $stars, ':content' => $content, ':rid' => $existingId]);
            } else {
                $stmt = $this->pdo->prepare("INSERT INTO danh_gia_san_pham (id_nguoi_dung, id_san_pham, id_don_hang, so_sao, `$contentCol`) VALUES (:uid, :pid, :oid, :stars, :content)");
                $stmt->execute([':uid' => $userId, ':pid' => $productId, ':oid' => $orderId, ':stars' => $stars, ':content' => $content]);
            }

            return ['success' => true, 'message' => 'Cảm ơn bạn đã gửi feedback.'];
        } catch (Throwable $e) {
            return ['success' => false, 'message' => 'Không thể lưu feedback: ' . $e->getMessage()];
        }
    }

    public function getPublicByProduct(int $productId): array
    {
        $idCol = $this->reviewIdCol();
        $contentCol = $this->contentCol();
        $statusWhere = $this->hasCol('trang_thai') ? "AND COALESCE(dg.trang_thai, 'hien') = 'hien'" : '';
        $replySelect = $this->hasCol('phan_hoi_admin') ? ', dg.phan_hoi_admin' : ", NULL AS phan_hoi_admin";
        $sql = "SELECT dg.`$idCol` AS id_danh_gia, dg.so_sao, dg.`$contentCol` AS noi_dung, dg.ngay_tao, u.ho_ten $replySelect
                FROM danh_gia_san_pham dg
                LEFT JOIN nguoi_dung u ON u.id_nguoi_dung = dg.id_nguoi_dung
                WHERE dg.id_san_pham = :pid $statusWhere
                ORDER BY dg.ngay_tao DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':pid' => $productId]);
        return $stmt->fetchAll();
    }

    public function getStatsByProduct(int $productId): array
    {
        $statusWhere = $this->hasCol('trang_thai') ? "AND COALESCE(trang_thai, 'hien') = 'hien'" : '';
        $stmt = $this->pdo->prepare("SELECT COUNT(*) AS total, COALESCE(ROUND(AVG(so_sao),1),0) AS avg_star FROM danh_gia_san_pham WHERE id_san_pham = :pid $statusWhere");
        $stmt->execute([':pid' => $productId]);
        return $stmt->fetch() ?: ['total' => 0, 'avg_star' => 0];
    }

    public function adminAll(string $keyword = '', string $status = ''): array
    {
        $idCol = $this->reviewIdCol();
        $contentCol = $this->contentCol();
        $statusSelect = $this->hasCol('trang_thai') ? "dg.trang_thai" : "'hien' AS trang_thai";
        $reasonSelect = $this->hasCol('ly_do_an') ? "dg.ly_do_an" : "NULL AS ly_do_an";
        $replySelect = $this->hasCol('phan_hoi_admin') ? "dg.phan_hoi_admin" : "NULL AS phan_hoi_admin";
        $where = [];
        $params = [];
        if ($keyword !== '') {
            $where[] = "(u.ho_ten LIKE :q OR u.email LIKE :q OR sp.ten_san_pham LIKE :q OR dg.`$contentCol` LIKE :q)";
            $params[':q'] = '%' . $keyword . '%';
        }
        if ($status !== '' && $this->hasCol('trang_thai')) {
            $where[] = "dg.trang_thai = :status";
            $params[':status'] = $status;
        }
        $whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';
        $sql = "SELECT dg.`$idCol` AS id_danh_gia, dg.id_don_hang, dg.so_sao, dg.`$contentCol` AS noi_dung, dg.ngay_tao,
                       $statusSelect, $reasonSelect, $replySelect, u.ho_ten, u.email, sp.ten_san_pham
                FROM danh_gia_san_pham dg
                LEFT JOIN nguoi_dung u ON u.id_nguoi_dung = dg.id_nguoi_dung
                LEFT JOIN san_pham sp ON sp.id_san_pham = dg.id_san_pham
                $whereSql
                ORDER BY dg.ngay_tao DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function adminUpdate(int $reviewId, string $status, string $reason = '', string $reply = ''): array
    {
        if ($reviewId <= 0) return ['success' => false, 'message' => 'Feedback không hợp lệ.'];
        if (!in_array($status, ['hien','an','cho_duyet'], true)) $status = 'hien';
        $idCol = $this->reviewIdCol();
        $sets = [];
        $params = [':id' => $reviewId];
        if ($this->hasCol('trang_thai')) { $sets[] = 'trang_thai = :status'; $params[':status'] = $status; }
        if ($this->hasCol('ly_do_an')) { $sets[] = 'ly_do_an = :reason'; $params[':reason'] = trim($reason); }
        if ($this->hasCol('phan_hoi_admin')) { $sets[] = 'phan_hoi_admin = :reply'; $params[':reply'] = trim($reply); }
        if ($this->hasCol('ngay_cap_nhat')) { $sets[] = 'ngay_cap_nhat = CURRENT_TIMESTAMP'; }
        if (!$sets) return ['success' => false, 'message' => 'Hãy chạy SQL bổ sung để thêm cột xử lý feedback.'];
        $stmt = $this->pdo->prepare("UPDATE danh_gia_san_pham SET " . implode(', ', $sets) . " WHERE `$idCol` = :id");
        $stmt->execute($params);
        return ['success' => true, 'message' => 'Đã cập nhật feedback.'];
    }

}
