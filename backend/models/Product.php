<?php
declare(strict_types=1);

class Product
{
    public function __construct(private PDO $pdo) {}

    private function baseSelect(): string
    {
        return "
            SELECT
                sp.id_san_pham,
                sp.id_danh_muc,
                sp.id_thuong_hieu,
                sp.ten_san_pham,
                sp.ma_san_pham,
                sp.gia,
                sp.gia AS gia_ban,
                sp.hinh_anh_chinh,
                sp.hinh_anh_chinh AS hinh_anh,
                sp.mo_ta_ngan,
                sp.mo_ta_chi_tiet,
                sp.so_luong_ton,
                sp.trang_thai,
                sp.ngay_tao,
                dm.ten_danh_muc,
                th.ten_thuong_hieu,
                km.phan_tram_giam,
                CASE
                    WHEN km.phan_tram_giam IS NOT NULL
                    THEN ROUND(sp.gia * (1 - km.phan_tram_giam / 100), 0)
                    ELSE NULL
                END AS gia_giam
            FROM san_pham sp
            INNER JOIN danh_muc dm ON dm.id_danh_muc = sp.id_danh_muc
            INNER JOIN thuong_hieu th ON th.id_thuong_hieu = sp.id_thuong_hieu
            LEFT JOIN (
                SELECT
                    spkm.id_san_pham,
                    MAX(k.phan_tram_giam) AS phan_tram_giam
                FROM san_pham_khuyen_mai spkm
                INNER JOIN khuyen_mai k ON k.id_khuyen_mai = spkm.id_khuyen_mai
                WHERE k.trang_thai = 'dang_dien_ra'
                  AND NOW() BETWEEN k.ngay_bat_dau AND k.ngay_ket_thuc
                GROUP BY spkm.id_san_pham
            ) km ON km.id_san_pham = sp.id_san_pham
        ";
    }

    private function orderBy(string $sort = 'newest'): string
    {
        return match ($sort) {
            'price_asc'  => " ORDER BY COALESCE(gia_giam, gia_ban) ASC, id_san_pham DESC",
            'price_desc' => " ORDER BY COALESCE(gia_giam, gia_ban) DESC, id_san_pham DESC",
            default      => " ORDER BY id_san_pham DESC",
        };
    }

    public function getAll(string $sort = 'newest'): array
    {
        $sql = "SELECT * FROM (" . $this->baseSelect() . ") p WHERE p.trang_thai = 'dang_ban'" . $this->orderBy($sort);
        return $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById(int $id): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM (" . $this->baseSelect() . ") p WHERE p.id_san_pham = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        return $product ?: null;
    }

public function search(string $keyword = '', string $sort = 'newest'): array
{
    $sql = "SELECT * FROM (" . $this->baseSelect() . ") p
            WHERE p.trang_thai = 'dang_ban'";

    $params = [];

    if ($keyword !== '') {
        $sql .= " AND (
            p.ten_san_pham LIKE ?
            OR p.ma_san_pham LIKE ?
            OR p.mo_ta_ngan LIKE ?
            OR p.mo_ta_chi_tiet LIKE ?
            OR p.ten_danh_muc LIKE ?
            OR p.ten_thuong_hieu LIKE ?
        )";

        $like = '%' . $keyword . '%';

        $params = [
            $like,
            $like,
            $like,
            $like,
            $like,
            $like
        ];
    }

    $sql .= $this->orderBy($sort);

    $stmt = $this->pdo->prepare($sql);
    $stmt->execute($params);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
    public function getByCategory(int $categoryId, string $sort = 'newest'): array
    {
        $sql = "SELECT * FROM (" . $this->baseSelect() . ") p
                WHERE p.trang_thai = 'dang_ban' AND p.id_danh_muc = :id" . $this->orderBy($sort);
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $categoryId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getByBrand(int $brandId, string $sort = 'newest'): array
    {
        $sql = "SELECT * FROM (" . $this->baseSelect() . ") p
                WHERE p.trang_thai = 'dang_ban' AND p.id_thuong_hieu = :id" . $this->orderBy($sort);
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $brandId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getByCategoryAndBrand(int $categoryId, int $brandId, string $sort = 'newest'): array
    {
        $sql = "SELECT * FROM (" . $this->baseSelect() . ") p
                WHERE p.trang_thai = 'dang_ban'
                  AND p.id_danh_muc = :category_id
                  AND p.id_thuong_hieu = :brand_id" . $this->orderBy($sort);
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':category_id' => $categoryId,
            ':brand_id' => $brandId,
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
