<?php
declare(strict_types=1);

class Brand
{
    public function __construct(private PDO $pdo) {}

    public function getActiveBrands(): array
    {
        $sql = "
            SELECT
                th.id_thuong_hieu AS id,
                th.ten_thuong_hieu AS name,
                COUNT(sp.id_san_pham) AS so_luong
            FROM thuong_hieu th
            LEFT JOIN san_pham sp
                ON sp.id_thuong_hieu = th.id_thuong_hieu
               AND sp.trang_thai = 'dang_ban'
            WHERE th.trang_thai = 'hien'
            GROUP BY th.id_thuong_hieu, th.ten_thuong_hieu
            ORDER BY th.ten_thuong_hieu ASC
        ";
        return $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }
}
