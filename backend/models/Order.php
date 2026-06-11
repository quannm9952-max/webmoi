<?php
declare(strict_types=1);

class Order
{
    public function __construct(private PDO $pdo) {}

    public function getPaymentMethods(): array
    {
        return $this->pdo
            ->query("SELECT * FROM phuong_thuc_thanh_toan WHERE trang_thai='hien' ORDER BY id_phuong_thuc")
            ->fetchAll();
    }

    public function getOrdersByUser(int $uid): array
    {
        $s = $this->pdo->prepare("
            SELECT dh.*, pt.ten_phuong_thuc
            FROM don_hang dh
            LEFT JOIN phuong_thuc_thanh_toan pt ON pt.id_phuong_thuc = dh.id_phuong_thuc
            WHERE dh.id_nguoi_dung = :u
            ORDER BY dh.ngay_dat DESC
        ");
        $s->execute([':u' => $uid]);
        return $s->fetchAll();
    }

    public function getByIdForUser(int $oid, int $uid): ?array
    {
        $s = $this->pdo->prepare("
            SELECT dh.*, pt.ten_phuong_thuc
            FROM don_hang dh
            LEFT JOIN phuong_thuc_thanh_toan pt ON pt.id_phuong_thuc = dh.id_phuong_thuc
            WHERE dh.id_don_hang = :o AND dh.id_nguoi_dung = :u
            LIMIT 1
        ");
        $s->execute([':o' => $oid, ':u' => $uid]);
        $o = $s->fetch();
        return $o ?: null;
    }

    public function getItems(int $oid): array
    {
        $s = $this->pdo->prepare("
            SELECT ct.*, sp.ten_san_pham, sp.ma_san_pham,
                   sp.hinh_anh_chinh, sp.hinh_anh_chinh AS hinh_anh
            FROM chi_tiet_don_hang ct
            JOIN san_pham sp ON sp.id_san_pham = ct.id_san_pham
            WHERE ct.id_don_hang = :o
        ");
        $s->execute([':o' => $oid]);
        return $s->fetchAll();
    }

    public function createFromCart(int $uid, array $d): array
    {
        $m = (int)($d['id_phuong_thuc'] ?? 0);
        $n = trim((string)($d['ten_nguoi_nhan'] ?? ''));
        $phone = trim((string)($d['so_dien_thoai_nhan'] ?? ''));
        $a = trim((string)($d['dia_chi_giao_hang'] ?? ''));
        $note = trim((string)($d['ghi_chu'] ?? ''));

        if (!$m || $n === '' || $phone === '' || $a === '') {
            return [
                'success' => false,
                'message' => 'Vui lòng nhập đầy đủ thông tin.',
            ];
        }

        try {
            $this->pdo->beginTransaction();

            // Lấy giỏ hàng
            $stmt = $this->pdo->prepare('SELECT id_gio_hang FROM gio_hang WHERE id_nguoi_dung = :uid LIMIT 1 FOR UPDATE');
            $stmt->execute([':uid' => $uid]);
            $cart = $stmt->fetch();
            if (!$cart) {
                throw new Exception('Giỏ hàng trống.');
            }
            $cartId = (int)$cart['id_gio_hang'];

            // Kiểm tra chi tiết giỏ hàng
            $stmt = $this->pdo->prepare('SELECT 1 FROM chi_tiet_gio_hang WHERE id_gio_hang = :cid LIMIT 1');
            $stmt->execute([':cid' => $cartId]);
            if (!$stmt->fetchColumn()) {
                throw new Exception('Giỏ hàng trống.');
            }

            // Kiểm tra phương thức
            $stmt = $this->pdo->prepare("SELECT 1 FROM phuong_thuc_thanh_toan WHERE id_phuong_thuc = :m AND trang_thai = 'hien' LIMIT 1");
            $stmt->execute([':m' => $m]);
            if (!$stmt->fetchColumn()) {
                throw new Exception('Phương thức thanh toán không hợp lệ.');
            }

            // Kiểm tra tồn kho
            $stmt = $this->pdo->prepare("
                SELECT 1 
                FROM chi_tiet_gio_hang ct 
                JOIN san_pham sp ON sp.id_san_pham = ct.id_san_pham 
                WHERE ct.id_gio_hang = :cid 
                AND (sp.trang_thai <> 'dang_ban' OR sp.so_luong_ton < ct.so_luong) 
                LIMIT 1
            ");
            $stmt->execute([':cid' => $cartId]);
            if ($stmt->fetchColumn()) {
                throw new Exception('Vượt quá số lượng tồn kho');
            }

            // Tính tổng tiền
            $stmt = $this->pdo->prepare('SELECT COALESCE(SUM(so_luong * don_gia), 0) FROM chi_tiet_gio_hang WHERE id_gio_hang = :cid');
            $stmt->execute([':cid' => $cartId]);
            $tongTien = (float)$stmt->fetchColumn();

            // Tạo đơn hàng
            $stmt = $this->pdo->prepare('
                INSERT INTO don_hang (id_nguoi_dung, id_phuong_thuc, tong_tien, ten_nguoi_nhan, so_dien_thoai_nhan, dia_chi_giao_hang, ghi_chu) 
                VALUES (:uid, :method, :total, :name, :phone, :address, :note)
            ');
            $stmt->execute([
                ':uid' => $uid,
                ':method' => $m,
                ':total' => $tongTien,
                ':name' => $n,
                ':phone' => $phone,
                ':address' => $a,
                ':note' => $note !== '' ? $note : null,
            ]);
            $oid = (int)$this->pdo->lastInsertId();

            // Chuyển chi tiết giỏ hàng sang chi tiết đơn hàng
            $stmt = $this->pdo->prepare('
                INSERT INTO chi_tiet_don_hang (id_don_hang, id_san_pham, so_luong, don_gia, thanh_tien)
                SELECT :oid, id_san_pham, so_luong, don_gia, so_luong * don_gia 
                FROM chi_tiet_gio_hang WHERE id_gio_hang = :cid
            ');
            $stmt->execute([':oid' => $oid, ':cid' => $cartId]);

            // Cập nhật tồn kho
            $stmt = $this->pdo->prepare("
                UPDATE san_pham sp
                JOIN chi_tiet_gio_hang ct ON ct.id_san_pham = sp.id_san_pham
                SET sp.so_luong_ton = sp.so_luong_ton - ct.so_luong,
                    sp.trang_thai = CASE WHEN sp.so_luong_ton - ct.so_luong <= 0 THEN 'het_hang' ELSE sp.trang_thai END
                WHERE ct.id_gio_hang = :cid
            ");
            $stmt->execute([':cid' => $cartId]);

            // Tạo thanh toán
            $stmt = $this->pdo->prepare('INSERT INTO thanh_toan(id_don_hang, id_phuong_thuc, so_tien) VALUES(:oid, :m, :total)');
            $stmt->execute([':oid' => $oid, ':m' => $m, ':total' => $tongTien]);

            // Tạo vận chuyển
            $stmt = $this->pdo->prepare('INSERT INTO van_chuyen(id_don_hang, phi_van_chuyen) VALUES(:oid, 0)');
            $stmt->execute([':oid' => $oid]);

            // Xoá chi tiết giỏ hàng
            $stmt = $this->pdo->prepare('DELETE FROM chi_tiet_gio_hang WHERE id_gio_hang = :cid');
            $stmt->execute([':cid' => $cartId]);

            $this->pdo->commit();

            return [
                'success' => true,
                'id_don_hang' => $oid,
            ];
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            $message = $e->getMessage();
            if (str_contains($message, 'Vượt quá số lượng tồn kho')) {
                $message = 'Vượt quá số lượng tồn kho';
            }

            return [
                'success' => false,
                'message' => $message ?: 'Không thể tạo đơn hàng.',
            ];
        }
    }

    public function stats(): array
    {
        return [
            'totalOrders' => (int)$this->pdo->query("SELECT COUNT(*) FROM don_hang")->fetchColumn(),
            'revenue' => (float)$this->pdo->query("SELECT COALESCE(SUM(tong_tien),0) FROM don_hang WHERE trang_thai_don_hang = 'da_giao'")->fetchColumn(),
            'pending' => (int)$this->pdo->query("SELECT COUNT(*) FROM don_hang WHERE trang_thai_don_hang = 'cho_xac_nhan'")->fetchColumn(),
        ];
    }

    public function getAllOrders(): array
    {
        return $this->pdo->query("
            SELECT dh.*, nd.ho_ten, nd.email, pt.ten_phuong_thuc
            FROM don_hang dh
            JOIN nguoi_dung nd ON nd.id_nguoi_dung = dh.id_nguoi_dung
            LEFT JOIN phuong_thuc_thanh_toan pt ON pt.id_phuong_thuc = dh.id_phuong_thuc
            ORDER BY dh.ngay_dat DESC
        ")->fetchAll();
    }

    public function updateStatus(int $id, string $st): array
    {
        if (!in_array($st, ['cho_xac_nhan', 'da_xac_nhan', 'dang_giao', 'da_giao', 'da_huy'], true)) {
            return ['success' => false, 'message' => 'Trạng thái không hợp lệ.'];
        }

        try {
            $this->pdo->beginTransaction();

            $stmt = $this->pdo->prepare('SELECT trang_thai_don_hang FROM don_hang WHERE id_don_hang = :id FOR UPDATE');
            $stmt->execute([':id' => $id]);
            $currentStatus = $stmt->fetchColumn();

            if ($currentStatus === false) {
                throw new Exception('Không tìm thấy đơn hàng.');
            }

            if ($currentStatus === $st) {
                throw new Exception('Trạng thái đơn hàng không thay đổi.');
            }

            // Logic cấm quay ngược trạng thái
            if ($currentStatus === 'da_huy') {
                throw new Exception('Đơn hàng đã hủy, không thể thay đổi trạng thái.');
            }
            if ($currentStatus === 'da_giao') {
                throw new Exception('Đơn hàng đã giao thành công, không thể thay đổi trạng thái.');
            }
            if ($currentStatus === 'dang_giao' && in_array($st, ['cho_xac_nhan', 'da_xac_nhan'])) {
                throw new Exception('Đơn hàng đang giao, không thể quay lại trạng thái trước đó.');
            }
            if ($currentStatus === 'da_xac_nhan' && $st === 'cho_xac_nhan') {
                throw new Exception('Đơn hàng đã xác nhận, không thể quay lại chờ xác nhận.');
            }

            if ($st === 'da_huy') {
                // Chúng ta sẽ gọi thủ công hàm cancel logic ở đây hoặc commit rồi gọi cancelOrder
                // Nhưng cancelOrder có transaction riêng. Do đó, ta sẽ rollback transaction hiện tại rồi gọi cancelOrder.
                $this->pdo->rollBack();
                return $this->cancelOrder($id);
            }

            $s = $this->pdo->prepare("UPDATE don_hang SET trang_thai_don_hang = :s WHERE id_don_hang = :id");
            $s->execute([':s' => $st, ':id' => $id]);

            $this->pdo->commit();

            return ['success' => true, 'message' => 'Đã cập nhật trạng thái đơn hàng.'];
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            return ['success' => false, 'message' => $e->getMessage() ?: 'Không thể cập nhật trạng thái.'];
        }
    }

    public function cancelOrder(int $id, ?int $uid = null): array
    {
        try {
            $this->pdo->beginTransaction();

            $query = 'SELECT trang_thai_don_hang FROM don_hang WHERE id_don_hang = :id';
            $params = [':id' => $id];
            
            if ($uid !== null) {
                $query .= ' AND id_nguoi_dung = :uid';
                $params[':uid'] = $uid;
            }
            $query .= ' LIMIT 1 FOR UPDATE';
            
            $stmt = $this->pdo->prepare($query);
            $stmt->execute($params);
            
            $status = $stmt->fetchColumn();

            if ($status === false) {
                throw new Exception('Không tìm thấy đơn hàng cần hủy.');
            }

            if ($status === 'dang_giao') throw new Exception('Không thể hủy đơn hàng đang giao.');
            if ($status === 'da_giao') throw new Exception('Không thể hủy đơn hàng đã giao.');
            if ($status === 'da_huy') throw new Exception('Đơn hàng đã được hủy trước đó.');

            // Hoàn tồn kho
            $stmt = $this->pdo->prepare("
                UPDATE san_pham sp
                JOIN chi_tiet_don_hang ct ON ct.id_san_pham = sp.id_san_pham
                SET sp.so_luong_ton = sp.so_luong_ton + ct.so_luong,
                    sp.trang_thai = CASE
                        WHEN sp.trang_thai = 'het_hang' AND sp.so_luong_ton + ct.so_luong > 0 THEN 'dang_ban'
                        ELSE sp.trang_thai
                    END
                WHERE ct.id_don_hang = :id
            ");
            $stmt->execute([':id' => $id]);

            // Cập nhật trạng thái
            $stmt = $this->pdo->prepare("UPDATE don_hang SET trang_thai_don_hang = 'da_huy' WHERE id_don_hang = :id");
            $stmt->execute([':id' => $id]);

            $this->pdo->commit();

            return ['success' => true, 'message' => 'Đã hủy đơn hàng thành công.'];
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            return ['success' => false, 'message' => $e->getMessage() ?: 'Không thể hủy đơn hàng.'];
        }
    }

}