<?php
declare(strict_types=1);

class User
{
    public function __construct(private PDO $pdo) {}

    public function findByEmail(string $email): ?array
    {
        $s = $this->pdo->prepare("
            SELECT nd.*, vt.ten_vai_tro
            FROM nguoi_dung nd
            JOIN vai_tro vt ON vt.id_vai_tro = nd.id_vai_tro
            WHERE nd.email = :email
            LIMIT 1
        ");
        $s->execute([':email' => $email]);
        $u = $s->fetch();
        return $u ?: null;
    }

    public function findById(int $id): ?array
    {
        $s = $this->pdo->prepare("
            SELECT nd.*, vt.ten_vai_tro
            FROM nguoi_dung nd
            JOIN vai_tro vt ON vt.id_vai_tro = nd.id_vai_tro
            WHERE nd.id_nguoi_dung = :id
            LIMIT 1
        ");
        $s->execute([':id' => $id]);
        $u = $s->fetch();
        return $u ?: null;
    }

    public function existsByEmail(string $email): bool
    {
        $s = $this->pdo->prepare("SELECT COUNT(*) FROM nguoi_dung WHERE email = :email");
        $s->execute([':email' => $email]);
        return (int)$s->fetchColumn() > 0;
    }

    public function create(array $d): array
    {
        if ($this->existsByEmail($d['email'])) {
            return ['success' => false, 'message' => 'Email đã tồn tại'];
        }

        try {
            $s = $this->pdo->prepare("
                INSERT INTO nguoi_dung (id_vai_tro, ho_ten, email, so_dien_thoai, mat_khau, dia_chi, trang_thai)
                VALUES (:r, :n, :e, :p, :m, :a, 'hoat_dong')
            ");
            $s->execute([
                ':r' => (int)($d['id_vai_tro'] ?? 2),
                ':n' => $d['ho_ten'],
                ':e' => $d['email'],
                ':p' => $d['so_dien_thoai'] ?: null,
                ':m' => password_hash($d['mat_khau'], PASSWORD_DEFAULT),
                ':a' => $d['dia_chi'] ?: null,
            ]);
            return ['success' => true, 'id' => (int)$this->pdo->lastInsertId()];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Lỗi cơ sở dữ liệu: ' . $e->getMessage()];
        }
    }

    public function updateProfile(int $id, array $d): bool
    {
        $s = $this->pdo->prepare("
            UPDATE nguoi_dung
            SET ho_ten = :n, so_dien_thoai = :p, dia_chi = :a
            WHERE id_nguoi_dung = :id
        ");
        return $s->execute([
            ':n'  => $d['ho_ten'],
            ':p'  => $d['so_dien_thoai'] ?: null,
            ':a'  => $d['dia_chi'] ?: null,
            ':id' => $id,
        ]);
    }

    public function findByGoogleId(string $gid): ?array
    {
        $s = $this->pdo->prepare("
            SELECT nd.*, vt.ten_vai_tro
            FROM nguoi_dung nd
            JOIN vai_tro vt ON vt.id_vai_tro = nd.id_vai_tro
            WHERE nd.google_id = :g
            LIMIT 1
        ");
        $s->execute([':g' => $gid]);
        $u = $s->fetch();
        return $u ?: null;
    }

    public function createOrUpdateFromGoogle(array $g): array
    {
        $gid    = (string)$g['id'];
        $email  = (string)$g['email'];
        $name   = (string)($g['name'] ?? $email);
        $avatar = (string)($g['picture'] ?? '');

        $u = $this->findByGoogleId($gid);
        if ($u) {
            $this->pdo->prepare("
                UPDATE nguoi_dung
                SET ho_ten = :n, avatar_url = :a, provider = 'google'
                WHERE id_nguoi_dung = :id
            ")->execute([':n' => $name, ':a' => $avatar ?: null, ':id' => $u['id_nguoi_dung']]);
            return $this->findById((int)$u['id_nguoi_dung']);
        }

        $u = $this->findByEmail($email);
        if ($u) {
            $this->pdo->prepare("
                UPDATE nguoi_dung
                SET google_id = :g, provider = 'google', avatar_url = :a
                WHERE id_nguoi_dung = :id
            ")->execute([':g' => $gid, ':a' => $avatar ?: null, ':id' => $u['id_nguoi_dung']]);
            return $this->findById((int)$u['id_nguoi_dung']);
        }

        $s = $this->pdo->prepare("
            INSERT INTO nguoi_dung (id_vai_tro, ho_ten, email, mat_khau, trang_thai, provider, google_id, avatar_url)
            VALUES (2, :n, :e, :m, 'hoat_dong', 'google', :g, :a)
        ");
        $s->execute([
            ':n' => $name,
            ':e' => $email,
            ':m' => password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT),
            ':g' => $gid,
            ':a' => $avatar ?: null,
        ]);

        return $this->findById((int)$this->pdo->lastInsertId());
    }

    public function allUsers(): array
    {
        return $this->pdo->query("
            SELECT nd.*, vt.ten_vai_tro
            FROM nguoi_dung nd
            JOIN vai_tro vt ON vt.id_vai_tro = nd.id_vai_tro
            ORDER BY nd.id_nguoi_dung DESC
        ")->fetchAll();
    }

    public function updateRoleAndStatus(int $id, int $role, string $status): bool
    {
        if (!in_array($status, ['hoat_dong', 'khoa'], true)) {
            return false;
        }

        $s = $this->pdo->prepare("
            UPDATE nguoi_dung
            SET id_vai_tro = :r, trang_thai = :s
            WHERE id_nguoi_dung = :id
        ");
        return $s->execute([':r' => $role, ':s' => $status, ':id' => $id]);
    }
}
