<?php
declare(strict_types=1);

class AdminCatalog
{
    public function __construct(private PDO $pdo) {}

    // ===== DANH MỤC =====
    public function categories(): array
    {
        return $this->pdo
            ->query("SELECT * FROM danh_muc ORDER BY id_danh_muc DESC")
            ->fetchAll();
    }

    public function findCategory(int $id): ?array
    {
        $s = $this->pdo->prepare("SELECT * FROM danh_muc WHERE id_danh_muc = :id");
        $s->execute([':id' => $id]);
        $r = $s->fetch();
        return $r ?: null;
    }

    public function saveCategory(array $d, int $id = 0): bool
    {
        if ($id) {
            $s = $this->pdo->prepare("
                UPDATE danh_muc
                SET ten_danh_muc = :n, trang_thai = :t
                WHERE id_danh_muc = :id
            ");
            return $s->execute([
                ':n'  => $d['ten_danh_muc'],
                ':t'  => $d['trang_thai'] ?? 'hien',
                ':id' => $id,
            ]);
        }

        $s = $this->pdo->prepare("
            INSERT INTO danh_muc (ten_danh_muc, trang_thai)
            VALUES (:n, :t)
        ");
        return $s->execute([
            ':n' => $d['ten_danh_muc'],
            ':t' => $d['trang_thai'] ?? 'hien',
        ]);
    }

    // ===== THƯƠNG HIỆU =====
    public function brands(): array
    {
        return $this->pdo
            ->query("SELECT * FROM thuong_hieu ORDER BY id_thuong_hieu DESC")
            ->fetchAll();
    }

    public function findBrand(int $id): ?array
    {
        $s = $this->pdo->prepare("SELECT * FROM thuong_hieu WHERE id_thuong_hieu = :id");
        $s->execute([':id' => $id]);
        $r = $s->fetch();
        return $r ?: null;
    }

    public function saveBrand(array $d, int $id = 0): bool
    {
        if ($id) {
            $s = $this->pdo->prepare("
                UPDATE thuong_hieu
                SET ten_thuong_hieu = :n, trang_thai = :t
                WHERE id_thuong_hieu = :id
            ");
            return $s->execute([
                ':n'  => $d['ten_thuong_hieu'],
                ':t'  => $d['trang_thai'] ?? 'hien',
                ':id' => $id,
            ]);
        }

        $s = $this->pdo->prepare("
            INSERT INTO thuong_hieu (ten_thuong_hieu, trang_thai)
            VALUES (:n, :t)
        ");
        return $s->execute([
            ':n' => $d['ten_thuong_hieu'],
            ':t' => $d['trang_thai'] ?? 'hien',
        ]);
    }

    // ===== SẢN PHẨM =====
    public function products(): array
    {
        return $this->pdo->query("
            SELECT sp.*, dm.ten_danh_muc, th.ten_thuong_hieu
            FROM san_pham sp
            JOIN danh_muc dm ON dm.id_danh_muc = sp.id_danh_muc
            JOIN thuong_hieu th ON th.id_thuong_hieu = sp.id_thuong_hieu
            WHERE sp.trang_thai != 'an'
            ORDER BY sp.id_san_pham DESC
        ")->fetchAll();
    }

    public function findProduct(int $id): ?array
    {
        $s = $this->pdo->prepare("SELECT * FROM san_pham WHERE id_san_pham = :id");
        $s->execute([':id' => $id]);
        $p = $s->fetch();
        return $p ?: null;
    }

    public function saveProduct(array $d, int $id = 0): bool
    {
        if (!empty($d['ma_san_pham'])) {
            $check = $this->pdo->prepare("
                SELECT COUNT(*)
                FROM san_pham
                WHERE ma_san_pham = :sku
                AND id_san_pham != :id
            ");
            $check->execute([
                ':sku' => $d['ma_san_pham'],
                ':id'  => $id,
            ]);

            if ((int)$check->fetchColumn() > 0) {
                throw new Exception('Mã sản phẩm đã tồn tại, vui lòng nhập mã khác.');
            }
        }

        $params = [
            ':c'   => $d['id_danh_muc'],
            ':b'   => $d['id_thuong_hieu'],
            ':n'   => $d['ten_san_pham'],
            ':sku' => $d['ma_san_pham'] ?? '',
            ':g'   => $d['gia'],
            ':img' => !empty($d['hinh_anh_chinh']) ? $d['hinh_anh_chinh'] : 'assets/images/no-image.jpg',
            ':mn'  => $d['mo_ta_ngan'] ?? '',
            ':mt'  => $d['mo_ta_chi_tiet'] ?? '',
            ':sl'  => $d['so_luong_ton'] ?? 0,
            ':tt'  => $d['trang_thai'] ?? 'dang_ban',
        ];

        if ($id) {
            $params[':id'] = $id;
            $s = $this->pdo->prepare("
                UPDATE san_pham
                SET id_danh_muc    = :c,
                    id_thuong_hieu = :b,
                    ten_san_pham   = :n,
                    ma_san_pham    = :sku,
                    gia            = :g,
                    hinh_anh_chinh = :img,
                    mo_ta_ngan     = :mn,
                    mo_ta_chi_tiet = :mt,
                    so_luong_ton   = :sl,
                    trang_thai     = :tt
                WHERE id_san_pham  = :id
            ");
        } else {
            $s = $this->pdo->prepare("
                INSERT INTO san_pham
                    (id_danh_muc, id_thuong_hieu, ten_san_pham, ma_san_pham,
                     gia, hinh_anh_chinh, mo_ta_ngan, mo_ta_chi_tiet, so_luong_ton, trang_thai)
                VALUES (:c, :b, :n, :sku, :g, :img, :mn, :mt, :sl, :tt)
            ");
        }

        return $s->execute($params);
    }

    public function deleteProduct(int $id): bool
    {
        $s = $this->pdo->prepare("
            UPDATE san_pham
            SET trang_thai = 'an'
            WHERE id_san_pham = :id
        ");
        return $s->execute([':id' => $id]);
    }

    // ===== KHUYẾN MÃI =====
    public function promotions(): array
    {
        return $this->pdo->query("
            SELECT *
            FROM khuyen_mai
            WHERE trang_thai != 'an'
            ORDER BY id_khuyen_mai DESC
        ")->fetchAll();
    }

    public function findPromotion(int $id): ?array
    {
        $s = $this->pdo->prepare("SELECT * FROM khuyen_mai WHERE id_khuyen_mai = :id");
        $s->execute([':id' => $id]);
        $km = $s->fetch();
        return $km ?: null;
    }

    public function savePromotion(array $d, int $id = 0): bool
    {
        if ($id) {
            $s = $this->pdo->prepare("
                UPDATE khuyen_mai
                SET ten_khuyen_mai = :ten,
                    phan_tram_giam = :giam,
                    ngay_bat_dau = :bd,
                    ngay_ket_thuc = :kt,
                    trang_thai = :tt
                WHERE id_khuyen_mai = :id
            ");
            return $s->execute([
                ':ten'  => $d['ten_khuyen_mai'],
                ':giam' => $d['phan_tram_giam'],
                ':bd'   => $d['ngay_bat_dau'],
                ':kt'   => $d['ngay_ket_thuc'],
                ':tt'   => $d['trang_thai'] ?? 'dang_dien_ra',
                ':id'   => $id,
            ]);
        }

        $s = $this->pdo->prepare("
            INSERT INTO khuyen_mai
                (ten_khuyen_mai, phan_tram_giam, ngay_bat_dau, ngay_ket_thuc, trang_thai)
            VALUES
                (:ten, :giam, :bd, :kt, :tt)
        ");
        return $s->execute([
            ':ten'  => $d['ten_khuyen_mai'],
            ':giam' => $d['phan_tram_giam'],
            ':bd'   => $d['ngay_bat_dau'],
            ':kt'   => $d['ngay_ket_thuc'],
            ':tt'   => $d['trang_thai'] ?? 'dang_dien_ra',
        ]);
    }

    public function deletePromotion(int $id): bool
    {
        $this->pdo->prepare("
            DELETE FROM san_pham_khuyen_mai
            WHERE id_khuyen_mai = ?
        ")->execute([$id]);

        $s = $this->pdo->prepare("
            UPDATE khuyen_mai
            SET trang_thai = 'an'
            WHERE id_khuyen_mai = :id
        ");
        return $s->execute([':id' => $id]);
    }

    public function getPromotionProductIds(int $promotionId): array
    {
        $s = $this->pdo->prepare("
            SELECT id_san_pham
            FROM san_pham_khuyen_mai
            WHERE id_khuyen_mai = ?
        ");
        $s->execute([$promotionId]);
        return array_map('intval', $s->fetchAll(PDO::FETCH_COLUMN));
    }

    public function syncPromotionProducts(int $promotionId, array $productIds): void
    {
        $this->pdo->prepare("
            DELETE FROM san_pham_khuyen_mai
            WHERE id_khuyen_mai = ?
        ")->execute([$promotionId]);

        if (empty($productIds)) {
            return;
        }

        $stmt = $this->pdo->prepare("
            INSERT INTO san_pham_khuyen_mai (id_san_pham, id_khuyen_mai)
            VALUES (?, ?)
        ");

        foreach ($productIds as $productId) {
            $productId = (int)$productId;
            if ($productId > 0) {
                $stmt->execute([$productId, $promotionId]);
            }
        }
    }

    public function productsForPromotionSelect(): array
    {
        return $this->pdo->query("
            SELECT id_san_pham, ten_san_pham, gia, trang_thai
            FROM san_pham
            WHERE trang_thai != 'an'
            ORDER BY id_san_pham DESC
        ")->fetchAll();
    }
}
