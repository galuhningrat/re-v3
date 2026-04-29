<?php

namespace App\Controllers;

use App\Core\BaseController;
use App\Core\Auth;
use App\Core\Database;
use App\Models\Pendaftaran;
use App\Models\Pasien;
use App\Models\Kamar;
use App\Models\Poli;

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
            $poliList  = Poli::findAll(true);   // hanya poli aktif
            $kamarList = Kamar::findTersedia();
            $this->render('pendaftaran/create', [
                'title'     => 'Buat Pendaftaran Baru',
                'poliList'  => $poliList,
                'kamarList' => $kamarList,
            ]);
        } catch (\RuntimeException $e) {
            $this->render('pendaftaran/create', [
                'title'     => 'Buat Pendaftaran Baru',
                'poliList'  => [],
                'kamarList' => [],
                'error'     => $e->getMessage(),
            ]);
        }
    }

    /** POST /pendaftaran/store — admin only */
    public function store(): void
    {
        Auth::requireRole('admin');

        $isNewPasien = ($_POST['is_new_pasien'] ?? '0') === '1';
        $pasienId    = trim($_POST['pasien_id'] ?? '');

        // ── Jika pasien baru, daftarkan dulu ke tabel pasien ──
        if ($isNewPasien) {
            $nama    = htmlspecialchars(trim($_POST['nama']    ?? ''));
            $keluhan = htmlspecialchars(trim($_POST['keluhan'] ?? ''));
            $nik     = trim($_POST['nik'] ?? '');

            if (empty($nama) || empty($keluhan)) {
                $this->renderCreateWithErrors(
                    ['Nama dan keluhan pasien baru wajib diisi.'],
                    $_POST
                );
                return;
            }

            try {
                $pasienId = Pasien::generateId();
                $pdo      = Database::getInstance()->getConnection();
                $stmt     = $pdo->prepare(
                    "INSERT INTO pasien (id, nik, nama, keluhan, tanggal_lahir, alamat, no_hp)
                     VALUES (:id, :nik, :nama, :keluhan, :tanggal_lahir, :alamat, :no_hp)"
                );
                $stmt->execute([
                    ':id'            => $pasienId,
                    ':nik'           => $nik ?: null,
                    ':nama'          => $nama,
                    ':keluhan'       => $keluhan,
                    ':tanggal_lahir' => trim($_POST['tanggal_lahir'] ?? '') ?: null,
                    ':alamat'        => htmlspecialchars(trim($_POST['alamat'] ?? '')) ?: null,
                    ':no_hp'         => htmlspecialchars(trim($_POST['no_hp']   ?? '')) ?: null,
                ]);
            } catch (\Exception $e) {
                $this->renderCreateWithErrors(
                    ['Gagal menyimpan data pasien baru: ' . $e->getMessage()],
                    $_POST
                );
                return;
            }
        }

        $data = [
            'pasien_id'     => $pasienId,
            'dokter_id'     => trim($_POST['dokter_id']     ?? ''),
            'kamar_id'      => trim($_POST['kamar_id']      ?? ''),
            'poli_id'       => trim($_POST['poli_id']       ?? '') ?: null,
            'tanggal_janji' => trim($_POST['tanggal_janji'] ?? ''),
            'status'        => 'menunggu',
            'catatan'       => trim($_POST['catatan']       ?? '') ?: null,
        ];

        $errors = [];
        if (empty($data['pasien_id']))     $errors[] = 'Pasien wajib diidentifikasi melalui NIK.';
        if (empty($data['dokter_id']))     $errors[] = 'Dokter wajib dipilih.';
        if (empty($data['kamar_id']))      $errors[] = 'Kamar wajib dipilih.';
        if (empty($data['tanggal_janji'])) $errors[] = 'Tanggal janji wajib diisi.';
        if (!empty($data['tanggal_janji']) && $data['tanggal_janji'] < date('Y-m-d')) {
            $errors[] = 'Tanggal janji tidak boleh di masa lalu.';
        }

        if (!empty($errors)) {
            $this->renderCreateWithErrors($errors, $_POST);
            return;
        }

        try {
            $newId = Pendaftaran::create($data);

            // ── Auto-generate nomor antrean ───────────────────────
            // Antrean dibuat otomatis setelah pendaftaran berhasil.
            // Jika gagal (misal poli_id null atau error DB), pendaftaran
            // tetap dianggap berhasil — nomor bisa digenerate manual nanti.
            $nomorAntrean = null;
            if (!empty($data['poli_id'])) {
                try {
                    $antrean      = new \App\Models\Antrean($newId, (int) $data['poli_id']);
                    $nomorAntrean = $antrean->simpan();
                } catch (\Exception $antreanErr) {
                    // Sengaja diabaikan — pendaftaran tidak dibatalkan
                }
            }

            $successMsg = $nomorAntrean
                ? 'Pendaftaran+berhasil+dibuat.+Nomor+antrean%3A+' . urlencode($nomorAntrean)
                : 'Pendaftaran+berhasil+dibuat';

            header('Location: ' . BASE_URL . '/pendaftaran/show/' . $newId
                . '?success=' . $successMsg);
            exit;
        } catch (\RuntimeException | \InvalidArgumentException $e) {
            $this->renderCreateWithErrors([$e->getMessage()], $_POST);
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

    // ─────────────────────────────────────────────────────────
    // AJAX Endpoints (sebelumnya TIDAK ADA — inilah penyebab
    // "Gagal menghubungi server")
    // ─────────────────────────────────────────────────────────

    /**
     * POST /pendaftaran/cek-nik
     *
     * Mengecek apakah NIK sudah terdaftar sebagai pasien.
     * Response JSON: { found: bool, pasien?: { id, nama, keluhan, no_hp } }
     */
    public function cekNik(): void
    {
        Auth::requireRole('admin');
        header('Content-Type: application/json');

        $nik = trim($_POST['nik'] ?? '');

        if (strlen($nik) !== 16 || !ctype_digit($nik)) {
            echo json_encode(['found' => false, 'error' => 'NIK harus tepat 16 digit angka']);
            return;
        }

        try {
            $pdo  = Database::getInstance()->getConnection();
            $stmt = $pdo->prepare(
                "SELECT id, nama, keluhan, no_hp
                 FROM pasien
                 WHERE nik = :nik
                 LIMIT 1"
            );
            $stmt->execute([':nik' => $nik]);
            $pasien = $stmt->fetch();

            echo json_encode(
                $pasien
                    ? ['found' => true,  'pasien' => $pasien]
                    : ['found' => false]
            );
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['found' => false, 'error' => 'Gagal mengecek NIK ke database']);
        }
    }

    /**
     * POST /pendaftaran/get-dokter
     *
     * Mengambil daftar dokter on-duty untuk poli tertentu.
     * Response JSON: array of dokter dengan info jadwal & kuota.
     */
    public function getDokterAjax(): void
    {
        Auth::requireRole('admin');
        header('Content-Type: application/json');

        $poliId = (int) ($_POST['poli_id'] ?? 0);
        if (!$poliId) {
            echo json_encode([]);
            return;
        }

        try {
            $list = Pendaftaran::getDokterByPoli($poliId);
            echo json_encode($list);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([]);
        }
    }

    // ─── Private Helper ───────────────────────────────────────

    /** Re-render form create dengan pesan error dan repopulate input */
    private function renderCreateWithErrors(array $errors, array $old = []): void
    {
        try {
            $poliList  = Poli::findAll(true);
            $kamarList = Kamar::findTersedia();
        } catch (\Exception $e) {
            $poliList  = [];
            $kamarList = [];
        }
        $this->render('pendaftaran/create', [
            'title'     => 'Buat Pendaftaran Baru',
            'poliList'  => $poliList,
            'kamarList' => $kamarList,
            'errors'    => $errors,
            'old'       => $old,
        ]);
    }
}
