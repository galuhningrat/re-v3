<?php

namespace App\Controllers;

use App\Core\BaseController;
use App\Models\Pasien;
use App\Core\Auth;

/**
 * Class PasienController
 *
 * Menangani semua request manajemen Pasien.
 * Alur: Router → Controller → Model → View
 *
 * Exception Handling Strategy:
 * - RuntimeException     → tampilkan pesan error ke user via view
 * - InvalidArgumentException → validasi gagal, kembali ke form
 * - PDOException         → sudah dibungkus Model → tidak sampai sini
 *
 * @package App\Controllers
 */
class PasienController extends BaseController
{
    /**
     * READ: Tampilkan semua pasien.
     * Route: GET /pasien
     */
    public function index(): void
    {
        Auth::requireLogin();

        try {
            $pasienList = Pasien::findAll();
            $this->render('pasien/index', [
                'title'      => 'Manajemen Pasien',
                'pasienList' => $pasienList,
            ]);
        } catch (\RuntimeException $e) {
            $this->render('pasien/index', [
                'title'      => 'Manajemen Pasien',
                'pasienList' => [],
                'error'      => $e->getMessage(),
            ]);
        }
    }

    /**
     * CREATE FORM: Tampilkan form tambah pasien.
     * Route: GET /pasien/create
     */
    public function create(): void
    {
        Auth::requireRole('admin');
        
        $this->render('pasien/create', [
            'title'  => 'Tambah Pasien Baru',
            'newId'  => Pasien::generateId(),
        ]);
    }

    /**
     * CREATE PROCESS: Simpan pasien baru.
     * Route: POST /pasien/store
     */
    public function store(): void
    {
        Auth::requireRole('admin');

        // Sanitasi semua input POST
        $input = $this->sanitizeInput([
            'id'            => $_POST['id']            ?? '',
            'nama'          => $_POST['nama']          ?? '',
            'keluhan'       => $_POST['keluhan']       ?? '',
            'tanggal_lahir' => $_POST['tanggal_lahir'] ?? '',
            'alamat'        => $_POST['alamat']        ?? '',
            'no_hp'         => $_POST['no_hp']         ?? '',
        ]);

        // Validasi field wajib
        $errors = $this->validatePasienInput($input);
        if (!empty($errors)) {
            $this->render('pasien/create', [
                'title'  => 'Tambah Pasien Baru',
                'newId'  => $input['id'],
                'errors' => $errors,
                'old'    => $input,
            ]);
            return;
        }

        try {
            $pasien = new Pasien(
                $input['nama'],
                $input['id'],
                $input['keluhan'],
                $input['tanggal_lahir'] ?: null,
                $input['alamat']        ?: null,
                $input['no_hp']         ?: null
            );
            $pasien->save();

            header('Location: ' . BASE_URL . '/pasien?success=Pasien+berhasil+ditambahkan');
            exit;
        } catch (\InvalidArgumentException $e) {
            // Validasi di level Model — kembali ke form
            $this->render('pasien/create', [
                'title'  => 'Tambah Pasien Baru',
                'newId'  => $input['id'],
                'errors' => [$e->getMessage()],
                'old'    => $input,
            ]);
        } catch (\RuntimeException $e) {
            // Error database — kembali ke form dengan pesan
            $this->render('pasien/create', [
                'title'  => 'Tambah Pasien Baru',
                'newId'  => $input['id'],
                'errors' => [$e->getMessage()],
                'old'    => $input,
            ]);
        }
    }

    /**
     * UPDATE FORM: Tampilkan form edit pasien.
     * Route: GET /pasien/edit/{id}
     */
    public function edit(string $id): void
    {
        Auth::requireRole('admin');

        try {
            $pasien = Pasien::findById($id);
            if (!$pasien) {
                header('Location: ' . BASE_URL . '/pasien?error=Pasien+tidak+ditemukan');
                exit;
            }
            $this->render('pasien/edit', [
                'title'  => 'Edit Data Pasien',
                'pasien' => $pasien,
            ]);
        } catch (\RuntimeException $e) {
            header('Location: ' . BASE_URL . '/pasien?error=' . urlencode($e->getMessage()));
            exit;
        }
    }

    /**
     * UPDATE PROCESS: Simpan perubahan data pasien.
     * Route: POST /pasien/update/{id}
     */
    public function update(string $id): void
    {
        Auth::requireRole('admin');

        $data = $this->sanitizeInput([
            'nama'          => $_POST['nama']          ?? '',
            'keluhan'       => $_POST['keluhan']       ?? '',
            'tanggal_lahir' => $_POST['tanggal_lahir'] ?? '',
            'alamat'        => $_POST['alamat']        ?? '',
            'no_hp'         => $_POST['no_hp']         ?? '',
        ]);

        $errors = $this->validatePasienInput($data, isUpdate: true);
        if (!empty($errors)) {
            // Ambil data lama untuk isi ulang form
            $pasien = array_merge(['id' => $id], $data);
            $this->render('pasien/edit', [
                'title'  => 'Edit Data Pasien',
                'pasien' => $pasien,
                'errors' => $errors,
            ]);
            return;
        }

        try {
            Pasien::update($id, $data);
            header('Location: ' . BASE_URL . '/pasien?success=Data+pasien+berhasil+diperbarui');
            exit;
        } catch (\InvalidArgumentException | \RuntimeException $e) {
            $pasien = array_merge(['id' => $id], $data);
            $this->render('pasien/edit', [
                'title'  => 'Edit Data Pasien',
                'pasien' => $pasien,
                'errors' => [$e->getMessage()],
            ]);
        }
    }

    /**
     * DELETE: Hapus pasien.
     * Route: POST /pasien/delete/{id}
     */
    public function destroy(string $id): void
    {
        Auth::requireRole('admin');
        
        try {
            $pasienObj = new Pasien('', $id, '');
            $pasienObj->delete($id);
            header('Location: ' . BASE_URL . '/pasien?success=Pasien+berhasil+dihapus');
        } catch (\RuntimeException $e) {
            header('Location: ' . BASE_URL . '/pasien?error=' . urlencode($e->getMessage()));
        }
        exit;
    }

    // ─── Private Helpers ──────────────────────────────────────

    /**
     * Sanitasi semua input: trim + htmlspecialchars.
     */
    private function sanitizeInput(array $raw): array
    {
        return array_map(fn($v) => htmlspecialchars(trim((string) $v)), $raw);
    }

    /**
     * Validasi input pasien, kembalikan array error (kosong = valid).
     *
     * @param bool $isUpdate Jika true, ID tidak divalidasi
     */
    private function validatePasienInput(array $data, bool $isUpdate = false): array
    {
        $errors = [];

        if (!$isUpdate && empty($data['id'])) {
            $errors[] = 'ID Pasien tidak boleh kosong.';
        }
        if (empty($data['nama'])) {
            $errors[] = 'Nama pasien tidak boleh kosong.';
        }
        if (empty($data['keluhan'])) {
            $errors[] = 'Keluhan pasien tidak boleh kosong.';
        }
        // Validasi format nomor HP jika diisi
        if (!empty($data['no_hp']) && !preg_match('/^[0-9+\-\s]{8,20}$/', $data['no_hp'])) {
            $errors[] = 'Format nomor HP tidak valid.';
        }

        return $errors;
    }
}
