<?php
declare(strict_types=1);

class Favorite
{
    public function __construct(private PDO $pdo)
    {
    }

    public function toggle(int $uid, int $pid): array
    {
        if ($uid <= 0 || $pid <= 0) {
            return ['success' => false, 'message' => 'Dữ liệu không hợp lệ.', 'action' => 'error'];
        }

        $check = $this->pdo->prepare("
            SELECT 1 FROM san_pham_yeu_thich
            WHERE id_nguoi_dung = :u AND id_san_pham = :p
            LIMIT 1
        ");
        $check->execute([':u' => $uid, ':p' => $pid]);

        if ($check->fetch()) {
            $delete = $this->pdo->prepare("
                DELETE FROM san_pham_yeu_thich
                WHERE id_nguoi_dung = :u AND id_san_pham = :p
            ");
            $delete->execute([':u' => $uid, ':p' => $pid]);

            return [
                'success' => true,
                'favorited' => false,
                'action' => 'removed',
                'message' => 'Đã xóa khỏi yêu thích.',
            ];
        }

        $insert = $this->pdo->prepare("
            INSERT INTO san_pham_yeu_thich(id_nguoi_dung, id_san_pham)
            VALUES(:u, :p)
        ");
        $insert->execute([':u' => $uid, ':p' => $pid]);

        return [
            'success' => true,
            'favorited' => true,
            'action' => 'added',
            'message' => 'Đã thêm vào yêu thích!',
        ];
    }

    public function countByUser(int $uid): int
    {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM san_pham_yeu_thich WHERE id_nguoi_dung = :u");
        $stmt->execute([':u' => $uid]);
        return (int)$stmt->fetchColumn();
    }

    public function getProductsByUser(int $uid): array
    {
        $stmt = $this->pdo->prepare("
            SELECT
                sp.id_san_pham,
                sp.ten_san_pham,
                sp.ma_san_pham,
                sp.gia AS gia_ban,
                sp.hinh_anh_chinh AS hinh_anh,
                th.ten_thuong_hieu,
                km.phan_tram_giam,
                CASE
                    WHEN km.phan_tram_giam IS NOT NULL
                    THEN ROUND(sp.gia * (1 - km.phan_tram_giam / 100))
                    ELSE NULL
                END AS gia_giam
            FROM san_pham_yeu_thich yt
            JOIN san_pham sp ON sp.id_san_pham = yt.id_san_pham
            JOIN thuong_hieu th ON th.id_thuong_hieu = sp.id_thuong_hieu
            LEFT JOIN (
                SELECT spkm.id_san_pham, k.phan_tram_giam
                FROM san_pham_khuyen_mai spkm
                JOIN khuyen_mai k ON k.id_khuyen_mai = spkm.id_khuyen_mai
                WHERE k.trang_thai = 'dang_dien_ra'
                  AND NOW() BETWEEN k.ngay_bat_dau AND k.ngay_ket_thuc
            ) km ON km.id_san_pham = sp.id_san_pham
            WHERE yt.id_nguoi_dung = :u
            ORDER BY yt.ngay_tao DESC
        ");
        $stmt->execute([':u' => $uid]);
        return $stmt->fetchAll();
    }
}
