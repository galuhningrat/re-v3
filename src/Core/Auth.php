<?php

namespace App\Core;

/**
 * Class Auth
 *
 * Mengelola autentikasi berbasis session.
 * Menyediakan helper static untuk login, logout, dan guard role.
 *
 * @package App\Core
 */
class Auth
{
    private const SESSION_KEY = 'auth_user';

    /**
     * Mulai session jika belum aktif.
     * Dipanggil sekali di index.php.
     */
    public static function startSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Simpan data user ke session setelah login berhasil.
     *
     * @param array $user Row dari tabel users
     */
    public static function login(array $user): void
    {
        // Regenerate session ID untuk cegah session fixation attack
        session_regenerate_id(true);

        $_SESSION[self::SESSION_KEY] = [
            'id'        => $user['id'],
            'nama'      => $user['nama'],
            'username'  => $user['username'],
            'role'      => $user['role'],
            'dokter_id' => $user['dokter_id'] ?? null,
        ];
    }

    /**
     * Hapus session dan redirect ke login.
     */
    public static function logout(): void
    {
        session_destroy();
        header('Location: ' . BASE_URL . '/login');
        exit;
    }

    /**
     * Cek apakah user sudah login.
     */
    public static function check(): bool
    {
        return isset($_SESSION[self::SESSION_KEY]);
    }

    /**
     * Ambil data user yang sedang login.
     *
     * @return array|null
     */
    public static function user(): ?array
    {
        return $_SESSION[self::SESSION_KEY] ?? null;
    }

    /**
     * Ambil role user yang sedang login.
     */
    public static function role(): ?string
    {
        return $_SESSION[self::SESSION_KEY]['role'] ?? null;
    }

    /**
     * Cek apakah user punya role tertentu.
     *
     * @param string|array $roles Role tunggal atau array of roles
     */
    public static function hasRole(string|array $roles): bool
    {
        $userRole = self::role();
        if ($userRole === null) return false;

        if (is_string($roles)) return $userRole === $roles;
        return in_array($userRole, $roles);
    }

    /**
     * Guard: Redirect ke login jika belum login.
     * Dipanggil di awal setiap controller method yang butuh auth.
     */
    public static function requireLogin(): void
    {
        if (!self::check()) {
            header('Location: ' . BASE_URL . '/login?error=Silakan+login+terlebih+dahulu');
            exit;
        }
    }

    /**
     * Guard: Redirect ke dashboard jika role tidak diizinkan.
     *
     * @param string|array $roles Role yang diizinkan
     */
    public static function requireRole(string|array $roles): void
    {
        self::requireLogin();

        if (!self::hasRole($roles)) {
            header('Location: ' . BASE_URL . '/?error=Anda+tidak+memiliki+akses+ke+halaman+ini');
            exit;
        }
    }

    /**
     * Cek apakah user adalah admin.
     */
    public static function isAdmin(): bool
    {
        return self::hasRole('admin');
    }

    /**
     * Cek apakah user adalah dokter.
     */
    public static function isDokter(): bool
    {
        return self::hasRole('dokter');
    }
}
