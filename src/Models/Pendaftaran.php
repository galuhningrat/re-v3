<?php

namespace App\Models;

use App\Core\Database;
use App\Models\Contracts\Printable;
use PDO;

/**
 * Class Pendaftaran
 *
 * Mengelola janji temu antara Pasien, Dokter, dan Kamar.
 * Mengimplementasikan Printable untuk mencetak struk pendaftaran.
 *
 * @package App\Models
 */
class Pendaftaran implements Printable
{
    private ?Dokter $dokter;
    private ?Pasien $pasien;
    private ?Kamar  $kamar;
    private string  $tanggalJanji;

    public function __construct(
        ?Dokter $dokter       = null,
        ?Pasien $pasien       = null,
        ?Kamar  $kamar        = null,
        string  $tanggalJanji = ''
    ) {
        $this->dokter       = $dokter;
        $this->pasien       = $pasien;
        $this->kamar        = $kamar;
        $this->tanggalJanji = $tanggalJanji;
    }

    /**
     * Implementasi wajib dari Interface Printable.
     */
    public function printStruk(): string
    {
        $separator = str_repeat('-', 40);
        return implode("\n", [
            $separator,
            "    STRUK PENDAFTARAN RS MEDIKA",
            $separator,
            $this->pasien?->getInfo()  ?? '-',
            "Dokter: " . ($this->dokter?->getInfo() ?? '-'),
            "Kamar : " . ($this->kamar?->getDetailKamar() ?? '-'),
            "Jadwal: " . $this->tanggalJanji,
            $separator,
        ]);
    }

    // =========================================================
    // Database Operations
    // =========================================================

    /**
     * Ambil semua pendaftaran dengan JOIN ke pasien, dokter, kamar, poli.
     */
    public static function findAll(): array
    {
        try {
            $pdo  = Database::getInstance()->getConnection();
            $stmt = $pdo->query("
                SELECT
                    pn.id,
                    pn.tanggal_janji,
                    pn.status,
                    pn.catatan,
                    pn.created_at,
                    ps.id        AS pasien_id,
                    ps.nama      AS pasien_nama,
                    ps.no_hp     AS pasien_hp,
                    dk.id        AS dokter_id,
                    dk.nama      AS dokter_nama,
                    dk.spesialis AS dokter_spesialis,
                    km.id        AS kamar_id,
                    km.nomor_kamar,
                    km.tipe      AS kamar_tipe,
                    po.nama      AS poli_nama
                FROM pendaftaran pn
                JOIN pasien  ps ON pn.pasien_id = ps.id
                JOIN dokter  dk ON pn.dokter_id = dk.id
                JOIN kamar   km ON pn.kamar_id  = km.id
                LEFT JOIN poli po ON pn.poli_id = po.id
                ORDER BY pn.created_at DESC
            ");
            return $stmt->fetchAll();
        } catch (\PDOException $e) {
            throw new \RuntimeException('Gagal mengambil data pendaftaran: ' . $e->getMessage());
        }
    }

    /**
     * Ambil satu pendaftaran by ID dengan JOIN lengkap termasuk nik & poli.
     *
     * PERBAIKAN: tambahkan ps.nik AS pasien_nik dan join ke tabel poli
     * agar show.php bisa menampilkan NIK dan nama poli.
     */
    public static function findById(int $id): ?array
    {
        try {
            $pdo  = Database::getInstance()->getConnection();
            $stmt = $pdo->prepare("
                SELECT
                    pn.id,
                    pn.tanggal_janji,
                    pn.status,
                    pn.catatan,
                    pn.created_at,
                    pn.nomor_registrasi,
                    ps.id        AS pasien_id,
                    ps.nama      AS pasien_nama,
                    ps.nik       AS pasien_nik,
                    ps.keluhan   AS pasien_keluhan,
                    ps.no_hp     AS pasien_hp,
                    dk.id        AS dokter_id,
                    dk.nama      AS dokter_nama,
                    dk.spesialis AS dokter_spesialis,
                    dk.no_hp     AS dokter_hp,
                    km.id        AS kamar_id,
                    km.nomor_kamar,
                    km.tipe      AS kamar_tipe,
                    km.harga_per_malam,
                    po.id        AS poli_id,
                    po.nama      AS poli_nama,
                    (SELECT a.nomor_antrean
                     FROM antrean a
                     WHERE a.pendaftaran_id = pn.id
                     ORDER BY a.id DESC
                     LIMIT 1)   AS nomor_antrean
                FROM pendaftaran pn
                JOIN pasien  ps ON pn.pasien_id = ps.id
                JOIN dokter  dk ON pn.dokter_id = dk.id
                JOIN kamar   km ON pn.kamar_id  = km.id
                LEFT JOIN poli po ON pn.poli_id = po.id
                WHERE pn.id = :id
            ");
            $stmt->execute([':id' => $id]);
            $result = $stmt->fetch();
            return $result ?: null;
        } catch (\PDOException $e) {
            throw new \RuntimeException('Gagal mengambil detail pendaftaran.');
        }
    }

    /**
     * Simpan pendaftaran baru + update status kamar.
     * Dibungkus dalam Transaction agar atomik.
     *
     * PERBAIKAN: tambahkan poli_id ke INSERT
     *
     * @param array $data
     * @return int ID pendaftaran yang baru dibuat
     */
    public static function create(array $data): int
    {
        $pdo = Database::getInstance()->getConnection();

        $required = ['pasien_id', 'dokter_id', 'kamar_id', 'tanggal_janji'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                throw new \InvalidArgumentException("Field '{$field}' wajib diisi.");
            }
        }

        try {
            $pdo->beginTransaction();

            // 1. Cek kamar masih tersedia (dengan lock)
            $stmtKamar = $pdo->prepare(
                "SELECT is_tersedia FROM kamar WHERE id = :id FOR UPDATE"
            );
            $stmtKamar->execute([':id' => $data['kamar_id']]);
            $kamar = $stmtKamar->fetch();

            if (!$kamar) {
                throw new \RuntimeException('Kamar tidak ditemukan.');
            }
            if ($kamar['is_tersedia'] === false || $kamar['is_tersedia'] === 'f') {
                throw new \RuntimeException('Kamar sudah terisi, pilih kamar lain.');
            }

            // 2. Insert pendaftaran (termasuk poli_id)
            $stmt = $pdo->prepare("
                INSERT INTO pendaftaran
                    (pasien_id, dokter_id, kamar_id, poli_id, tanggal_janji, status, catatan)
                VALUES
                    (:pasien_id, :dokter_id, :kamar_id, :poli_id, :tanggal_janji, :status, :catatan)
                RETURNING id
            ");
            $stmt->execute([
                ':pasien_id'     => $data['pasien_id'],
                ':dokter_id'     => $data['dokter_id'],
                ':kamar_id'      => (int) $data['kamar_id'],
                ':poli_id'       => $data['poli_id'] ?? null,
                ':tanggal_janji' => $data['tanggal_janji'],
                ':status'        => $data['status'] ?? 'menunggu',
                ':catatan'       => $data['catatan'] ?? null,
            ]);

            $newId = (int) $stmt->fetchColumn();

            // 3. Update kamar jadi tidak tersedia
            $pdo->prepare("UPDATE kamar SET is_tersedia = false WHERE id = :id")
                ->execute([':id' => $data['kamar_id']]);

            $pdo->commit();
            return $newId;
        } catch (\RuntimeException $e) {
            $pdo->rollBack();
            throw $e;
        } catch (\PDOException $e) {
            $pdo->rollBack();
            throw new \RuntimeException('Gagal menyimpan pendaftaran: ' . $e->getMessage());
        }
    }

    /**
     * Ambil dokter on-duty hari ini untuk poli tertentu.
     *
     * PERBAIKAN: menggunakan self::hariIniIndonesia() yang kini
     * didefinisikan di class ini (sebelumnya method ini tidak ada
     * sehingga menyebabkan fatal error saat getDokterAjax dipanggil).
     *
     * FALLBACK LOGIC:
     * 1. Dokter berjadwal hari ini di poli ini
     * 2. Dokter berjadwal di poli ini (hari apapun)
     * 3. Semua dokter aktif (last resort)
     */
    public static function getDokterByPoli(int $poliId): array
    {
        $hari = self::hariIniIndonesia();   // FIX: method ini sekarang ada

        try {
            $pdo = Database::getInstance()->getConnection();

            // Level 1: Jadwal hari ini di poli ini
            $result = self::queryDokterJadwal($pdo, $poliId, $hari);
            if (!empty($result)) {
                return $result;
            }

            // Level 2: Jadwal di poli ini (hari apapun)
            $stmt = $pdo->prepare("
                SELECT DISTINCT
                    d.id, d.nama, d.spesialis,
                    jd.jam_mulai, jd.jam_selesai, jd.kuota,
                    0 AS terpakai,
                    'Jadwal tersedia (hari lain)' AS keterangan
                FROM jadwal_dokter jd
                JOIN dokter d ON jd.dokter_id = d.id
                WHERE jd.poli_id = :poli_id
                ORDER BY d.nama ASC
            ");
            $stmt->execute([':poli_id' => $poliId]);
            $result = $stmt->fetchAll();
            if (!empty($result)) {
                return $result;
            }

            // Level 3: Semua dokter aktif (tanpa filter jadwal)
            $stmt = $pdo->query("
                SELECT
                    d.id, d.nama, d.spesialis,
                    '08:00'::TEXT AS jam_mulai,
                    '16:00'::TEXT AS jam_selesai,
                    999           AS kuota,
                    0             AS terpakai,
                    'Dokter umum' AS keterangan
                FROM dokter d
                ORDER BY d.nama ASC
            ");
            return $stmt->fetchAll();
        } catch (\PDOException $e) {
            throw new \RuntimeException('Gagal mengambil data dokter: ' . $e->getMessage());
        }
    }

    /** Query dokter dengan jadwal spesifik hari ini + info kuota terpakai */
    private static function queryDokterJadwal(\PDO $pdo, int $poliId, string $hari): array
    {
        $stmt = $pdo->prepare("
            SELECT
                d.id, d.nama, d.spesialis,
                jd.jam_mulai, jd.jam_selesai, jd.kuota,
                COALESCE((
                    SELECT COUNT(*)
                    FROM antrean a
                    JOIN pendaftaran p ON a.pendaftaran_id = p.id
                    WHERE p.dokter_id = d.id
                      AND a.poli_id   = :poli_id2
                      AND a.tanggal   = CURRENT_DATE
                      AND a.status   != 'tidak_hadir'
                ), 0) AS terpakai,
                'Jadwal hari ini' AS keterangan
            FROM jadwal_dokter jd
            JOIN dokter d ON jd.dokter_id = d.id
            WHERE jd.poli_id = :poli_id
              AND jd.hari    = :hari
            ORDER BY jd.jam_mulai ASC
        ");
        $stmt->execute([
            ':poli_id'  => $poliId,
            ':poli_id2' => $poliId,
            ':hari'     => $hari,
        ]);
        return $stmt->fetchAll();
    }

    /**
     * Nama hari dalam Bahasa Indonesia sesuai hari ini.
     *
     * PERBAIKAN: method ini sebelumnya tidak ada di class Pendaftaran
     * sehingga self::hariIniIndonesia() menyebabkan fatal error.
     */
    public static function hariIniIndonesia(): string
    {
        $days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        return $days[(int) date('w')];
    }

    /**
     * Update status pendaftaran.
     */
    public static function updateStatus(int $id, string $status, ?string $catatan = null): bool
    {
        $validStatus = ['menunggu', 'aktif', 'selesai', 'batal'];
        if (!in_array($status, $validStatus)) {
            throw new \InvalidArgumentException('Status tidak valid.');
        }

        try {
            $pdo = Database::getInstance()->getConnection();

            if (in_array($status, ['selesai', 'batal'])) {
                $pdo->beginTransaction();
                $stmtGet = $pdo->prepare("SELECT kamar_id FROM pendaftaran WHERE id = :id");
                $stmtGet->execute([':id' => $id]);
                $row = $stmtGet->fetch();
                if ($row) {
                    $pdo->prepare("UPDATE kamar SET is_tersedia = true WHERE id = :id")
                        ->execute([':id' => $row['kamar_id']]);
                }
            }

            $stmt = $pdo->prepare(
                "UPDATE pendaftaran SET status = :status, catatan = :catatan WHERE id = :id"
            );
            $result = $stmt->execute([
                ':id'      => $id,
                ':status'  => $status,
                ':catatan' => $catatan,
            ]);

            if ($pdo->inTransaction()) {
                $pdo->commit();
            }

            return $result;
        } catch (\PDOException $e) {
            if (isset($pdo) && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw new \RuntimeException('Gagal memperbarui status: ' . $e->getMessage());
        }
    }

    /**
     * Hapus pendaftaran + bebaskan kamar.
     */
    public static function hapus(int $id): bool
    {
        $pdo = Database::getInstance()->getConnection();
        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("SELECT kamar_id FROM pendaftaran WHERE id = :id");
            $stmt->execute([':id' => $id]);
            $row = $stmt->fetch();

            if ($row) {
                $pdo->prepare("UPDATE kamar SET is_tersedia = true WHERE id = :id")
                    ->execute([':id' => $row['kamar_id']]);
            }

            $pdo->prepare("DELETE FROM pendaftaran WHERE id = :id")
                ->execute([':id' => $id]);

            $pdo->commit();
            return true;
        } catch (\PDOException $e) {
            $pdo->rollBack();
            throw new \RuntimeException('Gagal menghapus pendaftaran: ' . $e->getMessage());
        }
    }
}
