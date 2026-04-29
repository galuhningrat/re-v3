<?php

namespace App\Controllers;

use App\Core\BaseController;
use App\Core\Auth;
use App\Models\User;

/**
 * Class AuthController
 * Menangani Login, Logout, dan Manajemen User (admin only).
 *
 * @package App\Controllers
 */
class AuthController extends BaseController
{
    /**
     * GET /login — Tampilkan form login.
     * Jika sudah login, redirect ke dashboard.
     */
    public function showLogin(): void
    {
        if (Auth::check()) {
            header('Location: ' . BASE_URL . '/');
            exit;
        }
        $this->renderLogin([]);
    }

    /**
     * POST /login — Proses login.
     */
    public function processLogin(): void
    {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        // Validasi input kosong
        if (empty($username) || empty($password)) {
            $this->renderLogin([
                'error' => 'Username dan password wajib diisi.',
                'old'   => ['username' => $username],
            ]);
            return;
        }

        try {
            $user = User::attempt($username, $password);

            if (!$user) {
                // Jangan bedakan "user tidak ada" dan "password salah"
                // untuk cegah username enumeration
                $this->renderLogin([
                    'error' => 'Username atau password salah.',
                    'old'   => ['username' => $username],
                ]);
                return;
            }

            Auth::login($user);
            header('Location: ' . BASE_URL . '/?welcome=1');
            exit;
        } catch (\RuntimeException $e) {
            $this->renderLogin([
                'error' => $e->getMessage(),
                'old'   => ['username' => $username],
            ]);
        }
    }

    /**
     * GET /logout
     */
    public function logout(): void
    {
        Auth::logout();
    }

    // ─── User Management (Admin Only) ─────────────────────────

    /**
     * GET /users — Daftar semua user
     */
    public function userIndex(): void
    {
        Auth::requireRole('admin');
        try {
            $userList   = User::findAll();
            $dokterList = \App\Models\Dokter::findAll();
            $this->render('auth/users', [
                'title'      => 'Manajemen User',
                'userList'   => $userList,
                'dokterList' => $dokterList,
            ]);
        } catch (\RuntimeException $e) {
            $this->render('auth/users', [
                'title'    => 'Manajemen User',
                'userList' => [],
                'error'    => $e->getMessage(),
            ]);
        }
    }

    /**
     * POST /users/store — Tambah user baru
     */
    public function userStore(): void
    {
        Auth::requireRole('admin');

        $data = [
            'nama'      => htmlspecialchars(trim($_POST['nama']      ?? '')),
            'username'  => htmlspecialchars(trim($_POST['username']  ?? '')),
            'password'  => $_POST['password'] ?? '',
            'role'      => $_POST['role']      ?? 'dokter',
            'dokter_id' => $_POST['dokter_id'] ?? null,
        ];

        $errors = [];
        if (empty($data['nama']))     $errors[] = 'Nama wajib diisi.';
        if (empty($data['username'])) $errors[] = 'Username wajib diisi.';
        if (strlen($data['password']) < 6) $errors[] = 'Password minimal 6 karakter.';
        if (!in_array($data['role'], ['admin', 'dokter'])) $errors[] = 'Role tidak valid.';

        if (!empty($errors)) {
            $userList   = User::findAll();
            $dokterList = \App\Models\Dokter::findAll();
            $this->render('auth/users', [
                'title'      => 'Manajemen User',
                'userList'   => $userList,
                'dokterList' => $dokterList,
                'errors'     => $errors,
                'old'        => $data,
            ]);
            return;
        }

        try {
            User::create($data);
            header('Location: ' . BASE_URL . '/users?success=User+berhasil+ditambahkan');
        } catch (\RuntimeException $e) {
            $userList   = User::findAll();
            $dokterList = \App\Models\Dokter::findAll();
            $this->render('auth/users', [
                'title'      => 'Manajemen User',
                'userList'   => $userList,
                'dokterList' => $dokterList,
                'errors'     => [$e->getMessage()],
                'old'        => $data,
            ]);
        }
        exit;
    }

    /**
     * POST /users/toggle/{id} — Aktifkan/nonaktifkan user
     */
    public function userToggle(string $id): void
    {
        Auth::requireRole('admin');
        try {
            User::toggleActive((int) $id);
            header('Location: ' . BASE_URL . '/users?success=Status+user+diperbarui');
        } catch (\RuntimeException $e) {
            header('Location: ' . BASE_URL . '/users?error=' . urlencode($e->getMessage()));
        }
        exit;
    }

    // ─── Private Helper ────────────────────────────────────────

    /**
     * Render halaman login tanpa layout sidebar.
     * Login page punya layout sendiri (full-page centered).
     */
    private function renderLogin(array $data = []): void
    {
        extract($data);
        require __DIR__ . '/../Views/auth/login.php';
    }
}
