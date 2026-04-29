<?php

namespace App\Controllers;

use App\Core\BaseController;
use App\Core\Auth;
use App\Models\Dokter;

class DokterController extends BaseController
{
    /** GET /dokter — admin & dokter boleh lihat */
    public function index(): void
    {
        Auth::requireLogin();
        try {
            $dokterList = Dokter::findAll();
            $this->render('dokter/index', [
                'title'      => 'Manajemen Dokter',
                'dokterList' => $dokterList,
            ]);
        } catch (\RuntimeException $e) {
            $this->render('dokter/index', [
                'title'      => 'Manajemen Dokter',
                'dokterList' => [],
                'error'      => $e->getMessage(),
            ]);
        }
    }

    /** GET /dokter/create — admin only */
    public function create(): void
    {
        Auth::requireRole('admin');
        $this->render('dokter/create', [
            'title' => 'Tambah Dokter Baru',
        ]);
    }

    /** POST /dokter/store — admin only */
    public function store(): void
    {
        Auth::requireRole('admin');

        $id        = htmlspecialchars(trim($_POST['id']        ?? ''));
        $nama      = htmlspecialchars(trim($_POST['nama']      ?? ''));
        $spesialis = htmlspecialchars(trim($_POST['spesialis'] ?? ''));
        $noHp      = htmlspecialchars(trim($_POST['no_hp']     ?? ''));
        $email     = htmlspecialchars(trim($_POST['email']     ?? ''));

        if (empty($id) || empty($nama) || empty($spesialis)) {
            $this->render('dokter/create', [
                'title' => 'Tambah Dokter Baru',
                'error' => 'ID, Nama, dan Spesialisasi wajib diisi.',
                'old'   => $_POST,
            ]);
            return;
        }

        try {
            $pdo  = \App\Core\Database::getInstance()->getConnection();
            $stmt = $pdo->prepare(
                "INSERT INTO dokter (id, nama, spesialis, no_hp, email)
                 VALUES (:id, :nama, :spesialis, :no_hp, :email)"
            );
            $stmt->execute([
                ':id'        => $id,
                ':nama'      => $nama,
                ':spesialis' => $spesialis,
                ':no_hp'     => $noHp ?: null,
                ':email'     => $email ?: null,
            ]);
            header('Location: ' . BASE_URL . '/dokter?success=Dokter+berhasil+ditambahkan');
            exit;
        } catch (\Exception $e) {
            $this->render('dokter/create', [
                'title' => 'Tambah Dokter Baru',
                'error' => 'Gagal menyimpan: ' . $e->getMessage(),
                'old'   => $_POST,
            ]);
        }
    }

    /** GET /dokter/edit/{id} — admin only */
    public function edit(string $id): void
    {
        Auth::requireRole('admin');

        $dokter = Dokter::findById($id);
        if (!$dokter) {
            header('Location: ' . BASE_URL . '/dokter?error=Dokter+tidak+ditemukan');
            exit;
        }
        $this->render('dokter/edit', [
            'title'  => 'Edit Data Dokter',
            'dokter' => $dokter,
        ]);
    }

    /** POST /dokter/update/{id} — admin only */
    public function update(string $id): void
    {
        Auth::requireRole('admin');

        try {
            $pdo  = \App\Core\Database::getInstance()->getConnection();
            $stmt = $pdo->prepare(
                "UPDATE dokter
                 SET nama = :nama, spesialis = :spesialis, no_hp = :no_hp, email = :email
                 WHERE id = :id"
            );
            $stmt->execute([
                ':id'        => $id,
                ':nama'      => htmlspecialchars(trim($_POST['nama']      ?? '')),
                ':spesialis' => htmlspecialchars(trim($_POST['spesialis'] ?? '')),
                ':no_hp'     => htmlspecialchars(trim($_POST['no_hp']     ?? '')) ?: null,
                ':email'     => htmlspecialchars(trim($_POST['email']     ?? '')) ?: null,
            ]);
            header('Location: ' . BASE_URL . '/dokter?success=Data+dokter+berhasil+diperbarui');
            exit;
        } catch (\Exception $e) {
            $this->render('dokter/edit', [
                'title'  => 'Edit Data Dokter',
                'dokter' => array_merge(['id' => $id], $_POST),
                'error'  => $e->getMessage(),
            ]);
        }
    }

    /** POST /dokter/delete/{id} — admin only */
    public function destroy(string $id): void
    {
        Auth::requireRole('admin');

        try {
            $pdo  = \App\Core\Database::getInstance()->getConnection();
            $stmt = $pdo->prepare("DELETE FROM dokter WHERE id = :id");
            $stmt->execute([':id' => $id]);
            header('Location: ' . BASE_URL . '/dokter?success=Dokter+berhasil+dihapus');
        } catch (\Exception $e) {
            header('Location: ' . BASE_URL . '/dokter?error=' . urlencode($e->getMessage()));
        }
        exit;
    }
}
