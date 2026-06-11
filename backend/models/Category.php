<?php
declare(strict_types=1);

class Category
{
    public function __construct(private PDO $pdo) {}

    public function getWithCount(): array
    {
        $sql = "
            SELECT
                dm.id_danh_muc,
                dm.ten_danh_muc,
                COUNT(sp.id_san_pham) AS so_luong
            FROM danh_muc dm
            LEFT JOIN san_pham sp
                ON sp.id_danh_muc = dm.id_danh_muc
                AND sp.trang_thai = 'dang_ban'
            GROUP BY dm.id_danh_muc, dm.ten_danh_muc
            ORDER BY dm.id_danh_muc ASC
        ";

        return $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }
}
