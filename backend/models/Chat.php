<?php
declare(strict_types=1);

class Chat
{
    public function __construct(private PDO $pdo) {}

    public function send(int $userId, string $senderType, string $message): bool
    {
        $message = trim($message);
        if ($userId <= 0 || $message === '') return false;
        $stmt = $this->pdo->prepare("INSERT INTO tin_nhan_ho_tro (id_nguoi_dung, nguoi_gui, noi_dung) VALUES (:uid, :sender, :msg)");
        return $stmt->execute([':uid' => $userId, ':sender' => $senderType, ':msg' => $message]);
    }

    public function messages(int $userId): array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM tin_nhan_ho_tro WHERE id_nguoi_dung = :uid ORDER BY ngay_tao ASC, id_tin_nhan ASC");
        $stmt->execute([':uid' => $userId]);
        return $stmt->fetchAll();
    }

    public function conversations(): array
    {
        return $this->pdo->query("\n            SELECT u.id_nguoi_dung, u.ho_ten, u.email,\n                   MAX(tn.ngay_tao) AS tin_moi_nhat,\n                   SUM(CASE WHEN tn.nguoi_gui='khach' AND tn.da_doc=0 THEN 1 ELSE 0 END) AS chua_doc,\n                   SUBSTRING_INDEX(GROUP_CONCAT(tn.noi_dung ORDER BY tn.ngay_tao DESC SEPARATOR '||'), '||', 1) AS noi_dung_moi\n            FROM tin_nhan_ho_tro tn\n            JOIN nguoi_dung u ON u.id_nguoi_dung = tn.id_nguoi_dung\n            GROUP BY u.id_nguoi_dung, u.ho_ten, u.email\n            ORDER BY tin_moi_nhat DESC\n        ")->fetchAll();
    }

    public function markReadByAdmin(int $userId): void
    {
        $stmt = $this->pdo->prepare("UPDATE tin_nhan_ho_tro SET da_doc = 1 WHERE id_nguoi_dung = :uid AND nguoi_gui = 'khach'");
        $stmt->execute([':uid' => $userId]);
    }
}
