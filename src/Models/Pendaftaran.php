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
     *
     * @return string
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
    // Database Operations — JOIN 3 tabel
    // =========================================================

    /**
     * Ambil semua pendaftaran dengan JOIN ke pasien, dokter, kamar.
     *
     * @return array
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
                    km.tipe      AS kamar_tipe
                FROM pendaftaran pn
                JOIN pasien  ps ON pn.pasien_id = ps.id
                JOIN dokter  dk ON pn.dokter_id = dk.id
                JOIN kamar   km ON pn.kamar_id  = km.id
                ORDER BY pn.created_at DESC
            ");
            return $stmt->fetchAll();
        } catch (\PDOException $e) {
            throw new \RuntimeException('Gagal mengambil data pendaftaran: ' . $e->getMessage());
        }
    }

    /**
     * Ambil satu pendaftaran by ID dengan JOIN lengkap.
     *
     * @param int $id
     * @return array|null
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
                    ps.id        AS pasien_id,
                    ps.nama      AS pasien_nama,
                    ps.keluhan   AS pasien_keluhan,
                    ps.no_hp     AS pasien_hp,
                    dk.id        AS dokter_id,
                    dk.nama      AS dokter_nama,
                    dk.spesialis AS dokter_spesialis,
                    dk.no_hp     AS dokter_hp,
                    km.id        AS kamar_id,
                    km.nomor_kamar,
                    km.tipe      AS kamar_tipe,
                    km.harga_per_malam
                FROM pendaftaran pn
                JOIN pasien  ps ON pn.pasien_id = ps.id
                JOIN dokter  dk ON pn.dokter_id = dk.id
                JOIN kamar   km ON pn.kamar_id  = km.id
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
     * @param array $data
     * @return int ID pendaftaran yang baru dibuat
     */
    public static function create(array $data): int
    {
        $pdo = Database::getInstance()->getConnection();

        // Validasi data wajib
        $required = ['pasien_id', 'dokter_id', 'kamar_id', 'tanggal_janji'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                throw new \InvalidArgumentException("Field '{$field}' wajib diisi.");
            }
        }

        try {
            $pdo->beginTransaction();

            // 1. Cek kamar masih tersedia
            $stmtKamar = $pdo->prepare(
                "SELECT is_tersedia FROM kamar WHERE id = :id FOR UPDATE"
            );
            $stmtKamar->execute([':id' => $data['kamar_id']]);
            $kamar = $stmtKamar->fetch();

            if (!$kamar) {
                throw new \RuntimeException('Kamar tidak ditemukan.');
            }
            // PostgreSQL: is_tersedia adalah boolean, nilainya 't' atau true
            if ($kamar['is_tersedia'] === false || $kamar['is_tersedia'] === 'f') {
                throw new \RuntimeException('Kamar sudah terisi, pilih kamar lain.');
            }

            // 2. Insert pendaftaran
            $stmt = $pdo->prepare("
                INSERT INTO pendaftaran
                    (pasien_id, dokter_id, kamar_id, tanggal_janji, status, catatan)
                VALUES
                    (:pasien_id, :dokter_id, :kamar_id, :tanggal_janji, :status, :catatan)
                RETURNING id
            ");
            $stmt->execute([
                ':pasien_id'    => $data['pasien_id'],
                ':dokter_id'    => $data['dokter_id'],
                ':kamar_id'     => (int) $data['kamar_id'],
                ':tanggal_janji' => $data['tanggal_janji'],
                ':status'       => $data['status'] ?? 'menunggu',
                ':catatan'      => $data['catatan'] ?? null,
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
     * FALLBACK LOGIC:
     * 1. Coba ambil dokter dengan jadwal hari ini di poli ini (strict)
     * 2. Jika kosong → ambil semua dokter yang punya jadwal di poli ini (any hari)
     * 3. Jika masih kosong → ambil SEMUA dokter aktif (tanpa filter poli/hari)
     *    agar form tidak pernah stuck karena data jadwal belum lengkap
     */
    public static function getDokterByPoli(int $poliId): array
    {
        $hari = self::hariIniIndonesia();

        try {
            $pdo = Database::getInstance()->getConnection();

            // ── Level 1: Jadwal hari ini di poli ini ──────────────
            $result = self::queryDokterJadwal($pdo, $poliId, $hari);

            if (!empty($result)) {
                return $result;
            }

            // ── Level 2: Jadwal di poli ini (hari apapun) ─────────
            // Berguna jika jadwal belum dikonfigurasi untuk hari ini
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

            // ── Level 3: Semua dokter aktif (tanpa filter jadwal) ──
            // Last resort — pastikan form selalu bisa disubmit
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

    /**
     * Query dokter dengan jadwal spesifik hari ini + info kuota terpakai.
     */
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
            ), 0) AS terpakai
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
     * Update status pendaftaran.
     *
     * @param int    $id
     * @param string $status
     * @param string|null $catatan
     * @return bool
     */
    public static function updateStatus(int $id, string $status, ?string $catatan = null): bool
    {
        $validStatus = ['menunggu', 'aktif', 'selesai', 'batal'];
        if (!in_array($status, $validStatus)) {
            throw new \InvalidArgumentException('Status tidak valid.');
        }

        try {
            $pdo  = Database::getInstance()->getConnection();

            // Jika batal/selesai, bebaskan kamar
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
                "UPDATE pendaftaran
                 SET status = :status, catatan = :catatan
                 WHERE id = :id"
            );
            $result = $stmt->execute([
                ':id'      => $id,
                ':status'  => $status,
                ':catatan' => $catatan,
            ]);

            if (isset($pdo) && $pdo->inTransaction()) {
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
     *
     * @param int $id
     * @return bool
     */
    public static function hapus(int $id): bool
    {
        $pdo = Database::getInstance()->getConnection();
        try {
            $pdo->beginTransaction();

            // Ambil kamar_id dulu sebelum hapus
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
