<?php

namespace App\Models;

use App\Core\Database;

/**
 * Class User
 * Model untuk autentikasi pengguna.
 */
class User
{
    /**
     * Cari user by username dan verifikasi password.
     */
    public static function attempt(string $username, string $password): ?array
    {
        if (empty(trim($username)) || empty($password)) {
            return null;
        }

        try {
            $pdo  = Database::getInstance()->getConnection();
            $stmt = $pdo->prepare(
                "SELECT id, nama, username, password, role, dokter_id, is_aktif
                 FROM users
                 WHERE username = :username
                 LIMIT 1"
            );
            $stmt->execute([':username' => trim($username)]);
            $user = $stmt->fetch();

            if (!$user) return null;

            // Cek kolom is_aktif (nama kolom di DB kamu)
            if (!$user['is_aktif']) return null;

            // Verifikasi password bcrypt
            if (!password_verify($password, $user['password'])) {
                return null;
            }

            return $user;
        } catch (\PDOException $e) {
            throw new \RuntimeException('Gagal melakukan autentikasi: ' . $e->getMessage());
        }
    }

    /**
     * Ambil semua user untuk halaman admin.
     */
    public static function findAll(): array
    {
        try {
            $pdo  = Database::getInstance()->getConnection();
            $stmt = $pdo->query(
                "SELECT u.id, u.nama, u.username, u.role, u.is_aktif,
                        u.created_at, d.nama AS dokter_nama
                 FROM users u
                 LEFT JOIN dokter d ON u.dokter_id = d.id
                 ORDER BY u.role, u.nama"
            );
            return $stmt->fetchAll();
        } catch (\PDOException $e) {
            throw new \RuntimeException('Gagal mengambil data user.');
        }
    }

    /**
     * Tambah user baru.
     */
    public static function create(array $data): bool
    {
        try {
            $pdo  = Database::getInstance()->getConnection();
            $stmt = $pdo->prepare(
                "INSERT INTO users (nama, username, password, role, dokter_id)
                 VALUES (:nama, :username, :password, :role, :dokter_id)"
            );
            return $stmt->execute([
                ':nama'      => $data['nama'],
                ':username'  => $data['username'],
                ':password'  => password_hash($data['password'], PASSWORD_BCRYPT),
                ':role'      => $data['role'],
                ':dokter_id' => $data['dokter_id'] ?: null,
            ]);
        } catch (\PDOException $e) {
            if ($e->getCode() === '23505') {
                throw new \RuntimeException('Username sudah digunakan.');
            }
            throw new \RuntimeException('Gagal membuat user: ' . $e->getMessage());
        }
    }

    /**
     * Toggle status aktif user.
     */
    public static function toggleActive(int $id): bool
    {
        try {
            $pdo  = Database::getInstance()->getConnection();
            $stmt = $pdo->prepare(
                "UPDATE users SET is_aktif = NOT is_aktif WHERE id = :id"
            );
            return $stmt->execute([':id' => $id]);
        } catch (\PDOException $e) {
            throw new \RuntimeException('Gagal mengubah status user.');
        }
    }
}
