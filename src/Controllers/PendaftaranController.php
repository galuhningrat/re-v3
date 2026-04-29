<?php

namespace App\Controllers;

use App\Core\BaseController;
use App\Core\Auth;
use App\Models\Pendaftaran;
use App\Models\Pasien;
use App\Models\Dokter;
use App\Models\Kamar;

class PendaftaranController extends BaseController
{
    /** GET /pendaftaran — admin & dokter boleh lihat */
    public function index(): void
    {
        Auth::requireLogin();
        try {
            $list = Pendaftaran::findAll();
            $this->render('pendaftaran/index', [
                'title' => 'Manajemen Pendaftaran',
                'list'  => $list,
            ]);
        } catch (\RuntimeException $e) {
            $this->render('pendaftaran/index', [
                'title' => 'Manajemen Pendaftaran',
                'list'  => [],
                'error' => $e->getMessage(),
            ]);
        }
    }

    /** GET /pendaftaran/show/{id} — admin & dokter boleh lihat */
    public function show(string $id): void
    {
        Auth::requireLogin();

        $data = Pendaftaran::findById((int) $id);
        if (!$data) {
            header('Location: ' . BASE_URL . '/pendaftaran?error=Pendaftaran+tidak+ditemukan');
            exit;
        }
        $this->render('pendaftaran/show', [
            'title' => 'Detail Pendaftaran #' . $id,
            'data'  => $data,
        ]);
    }

    /** GET /pendaftaran/create — admin only */
    public function create(): void
    {
        Auth::requireRole('admin');
        try {
            $pasienList = Pasien::findAll();
            $dokterList = Dokter::findAll();
            $kamarList  = Kamar::findTersedia();
            $this->render('pendaftaran/create', [
                'title'      => 'Buat Pendaftaran Baru',
                'pasienList' => $pasienList,
                'dokterList' => $dokterList,
                'kamarList'  => $kamarList,
            ]);
        } catch (\RuntimeException $e) {
            $this->render('pendaftaran/create', [
                'title'      => 'Buat Pendaftaran Baru',
                'pasienList' => [],
                'dokterList' => [],
                'kamarList'  => [],
                'error'      => $e->getMessage(),
            ]);
        }
    }

    /** POST /pendaftaran/store — admin only */
    public function store(): void
    {
        Auth::requireRole('admin');

        $data = [
            'pasien_id'     => trim($_POST['pasien_id']     ?? ''),
            'dokter_id'     => trim($_POST['dokter_id']     ?? ''),
            'kamar_id'      => trim($_POST['kamar_id']      ?? ''),
            'tanggal_janji' => trim($_POST['tanggal_janji'] ?? ''),
            'status'        => 'menunggu',
            'catatan'       => trim($_POST['catatan']       ?? '') ?: null,
        ];

        $errors = [];
        if (empty($data['pasien_id']))     $errors[] = 'Pasien wajib dipilih.';
        if (empty($data['dokter_id']))     $errors[] = 'Dokter wajib dipilih.';
        if (empty($data['kamar_id']))      $errors[] = 'Kamar wajib dipilih.';
        if (empty($data['tanggal_janji'])) $errors[] = 'Tanggal janji wajib diisi.';
        if (!empty($data['tanggal_janji']) && $data['tanggal_janji'] < date('Y-m-d')) {
            $errors[] = 'Tanggal janji tidak boleh di masa lalu.';
        }

        if (!empty($errors)) {
            $this->render('pendaftaran/create', [
                'title'      => 'Buat Pendaftaran Baru',
                'pasienList' => Pasien::findAll(),
                'dokterList' => Dokter::findAll(),
                'kamarList'  => Kamar::findTersedia(),
                'errors'     => $errors,
                'old'        => $_POST,
            ]);
            return;
        }

        try {
            $newId = Pendaftaran::create($data);
            header('Location: ' . BASE_URL . '/pendaftaran/show/' . $newId
                . '?success=Pendaftaran+berhasil+dibuat');
            exit;
        } catch (\RuntimeException | \InvalidArgumentException $e) {
            $this->render('pendaftaran/create', [
                'title'      => 'Buat Pendaftaran Baru',
                'pasienList' => Pasien::findAll(),
                'dokterList' => Dokter::findAll(),
                'kamarList'  => Kamar::findTersedia(),
                'errors'     => [$e->getMessage()],
                'old'        => $_POST,
            ]);
        }
    }

    /** GET /pendaftaran/edit/{id} — admin only */
    public function edit(string $id): void
    {
        Auth::requireRole('admin');

        $data = Pendaftaran::findById((int) $id);
        if (!$data) {
            header('Location: ' . BASE_URL . '/pendaftaran?error=Pendaftaran+tidak+ditemukan');
            exit;
        }
        $this->render('pendaftaran/edit', [
            'title' => 'Update Status Pendaftaran #' . $id,
            'data'  => $data,
        ]);
    }

    /** POST /pendaftaran/update/{id} — admin only */
    public function update(string $id): void
    {
        Auth::requireRole('admin');

        $status  = trim($_POST['status']  ?? '');
        $catatan = trim($_POST['catatan'] ?? '') ?: null;

        $validStatus = ['menunggu', 'aktif', 'selesai', 'batal'];
        if (!in_array($status, $validStatus)) {
            header('Location: ' . BASE_URL . '/pendaftaran/edit/' . $id
                . '?error=Status+tidak+valid');
            exit;
        }

        try {
            Pendaftaran::updateStatus((int) $id, $status, $catatan);
            header('Location: ' . BASE_URL . '/pendaftaran/show/' . $id
                . '?success=Status+berhasil+diperbarui');
            exit;
        } catch (\RuntimeException $e) {
            header('Location: ' . BASE_URL . '/pendaftaran/edit/' . $id
                . '?error=' . urlencode($e->getMessage()));
            exit;
        }
    }

    /** POST /pendaftaran/delete/{id} — admin only */
    public function destroy(string $id): void
    {
        Auth::requireRole('admin');

        try {
            Pendaftaran::hapus((int) $id);
            header('Location: ' . BASE_URL . '/pendaftaran?success=Pendaftaran+berhasil+dihapus');
        } catch (\RuntimeException $e) {
            header('Location: ' . BASE_URL . '/pendaftaran?error=' . urlencode($e->getMessage()));
        }
        exit;
    }
}
