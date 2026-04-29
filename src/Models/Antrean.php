<?php

namespace App\Models;

use App\Core\Database;

/**
 * Class Antrean
 *
 * Mengelola nomor antrean per poli per hari.
 * Format nomor: [KODE_POLI]-[3_DIGIT] → contoh: A-001, GIG-002
 *
 * KONSEP OOP: Implements Printable untuk cetak tiket antrean.
 *
 * @package App\Models
 */
class Antrean implements \App\Models\Contracts\Printable
{
    public function __construct(
        private int    $pendaftaranId,
        private int    $poliId,
        private string $nomorAntrean = '',
        private string $status       = 'menunggu'
    ) {}

    /**
     * Simpan antrean baru dengan generate nomor otomatis.
     * Dibungkus transaction agar nomor tidak duplikat.
     *
     * @return string Nomor antrean yang dibuat
     * @throws \RuntimeException
     */
    public function simpan(): string
    {
        $pdo = Database::getInstance()->getConnection();

        try {
            $pdo->beginTransaction();

            // Kunci untuk hindari race condition (concurrent request)
            $pdo->exec("LOCK TABLE antrean IN SHARE ROW EXCLUSIVE MODE");

            // Ambil nomor terakhir hari ini untuk poli ini
            $stmt = $pdo->prepare("
                SELECT nomor_antrean FROM antrean
                WHERE poli_id = :poli_id AND tanggal = CURRENT_DATE
                ORDER BY id DESC LIMIT 1
            ");
            $stmt->execute([':poli_id' => $this->poliId]);
            $last = $stmt->fetchColumn();

            // Generate nomor berikutnya
            $this->nomorAntrean = $this->generateNomor($last);

            // Insert
            $stmt = $pdo->prepare("
                INSERT INTO antrean (nomor_antrean, pendaftaran_id, poli_id, tanggal, status)
                VALUES (:nomor, :pend_id, :poli_id, CURRENT_DATE, 'menunggu')
            ");
            $stmt->execute([
                ':nomor'   => $this->nomorAntrean,
                ':pend_id' => $this->pendaftaranId,
                ':poli_id' => $this->poliId,
            ]);

            $pdo->commit();
            return $this->nomorAntrean;

        } catch (\PDOException $e) {
            $pdo->rollBack();
            throw new \RuntimeException('Gagal membuat nomor antrean: ' . $e->getMessage());
        }
    }

    /**
     * Generate nomor antrean berurutan.
     * Jika belum ada: 001. Jika terakhir 005, berikutnya 006.
     *
     * @param string|false $lastNomor Nomor terakhir dari DB (misal "A-005")
     * @return string Nomor baru (misal "A-006")
     */
    private function generateNomor(string|false $lastNomor): string
    {
        // Ambil prefix dari kode poli (huruf pertama, max 3 karakter)
        $poliData = self::getPoliById($this->poliId);
        $prefix   = strtoupper(substr($poliData['kode'] ?? 'A', 0, 1));

        if (!$lastNomor) {
            $next = 1;
        } else {
            // Extract angka dari nomor terakhir: "A-005" → 5
            $parts = explode('-', $lastNomor);
            $next  = (int) end($parts) + 1;
        }

        return $prefix . '-' . str_pad($next, 3, '0', STR_PAD_LEFT);
    }

    /** Ambil semua antrean hari ini untuk semua poli */
    public static function getHariIni(?int $poliId = null): array
    {
        try {
            $pdo  = Database::getInstance()->getConnection();
            $sql  = "
                SELECT
                    a.id, a.nomor_antrean, a.status, a.created_at,
                    ps.id AS pasien_id, ps.nama AS pasien_nama,
                    dk.nama AS dokter_nama,
                    po.nama AS poli_nama,
                    p.tanggal_janji
                FROM antrean a
                JOIN pendaftaran p  ON a.pendaftaran_id = p.id
                JOIN pasien     ps  ON p.pasien_id      = ps.id
                JOIN dokter     dk  ON p.dokter_id      = dk.id
                JOIN poli       po  ON a.poli_id        = po.id
                WHERE a.tanggal = CURRENT_DATE
            ";
            $params = [];
            if ($poliId) {
                $sql .= " AND a.poli_id = :poli_id";
                $params[':poli_id'] = $poliId;
            }
            $sql .= " ORDER BY a.id ASC";

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (\PDOException $e) {
            throw new \RuntimeException('Gagal mengambil data antrean.');
        }
    }

    /** Update status antrean */
    public static function updateStatus(int $id, string $status): bool
    {
        $valid = ['menunggu', 'dipanggil', 'selesai', 'tidak_hadir'];
        if (!in_array($status, $valid)) {
            throw new \InvalidArgumentException('Status antrean tidak valid.');
        }
        try {
            $pdo  = Database::getInstance()->getConnection();
            $stmt = $pdo->prepare("UPDATE antrean SET status = :status WHERE id = :id");
            return $stmt->execute([':status' => $status, ':id' => $id]);
        } catch (\PDOException $e) {
            throw new \RuntimeException('Gagal memperbarui status antrean.');
        }
    }

    /** Statistik antrean hari ini per poli */
    public static function getStatHariIni(): array
    {
        try {
            $pdo  = Database::getInstance()->getConnection();
            $stmt = $pdo->query("
                SELECT
                    po.nama AS poli,
                    COUNT(*)                               AS total,
                    COUNT(*) FILTER (WHERE a.status = 'menunggu')   AS menunggu,
                    COUNT(*) FILTER (WHERE a.status = 'dipanggil')  AS dipanggil,
                    COUNT(*) FILTER (WHERE a.status = 'selesai')    AS selesai
                FROM antrean a
                JOIN poli po ON a.poli_id = po.id
                WHERE a.tanggal = CURRENT_DATE
                GROUP BY po.id, po.nama
                ORDER BY po.nama
            ");
            return $stmt->fetchAll();
        } catch (\PDOException $e) {
            return [];
        }
    }

    private static function getPoliById(int $id): array
    {
        $pdo  = Database::getInstance()->getConnection();
        $stmt = $pdo->prepare("SELECT kode, nama FROM poli WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch() ?: ['kode' => 'A', 'nama' => 'Umum'];
    }

    // ─── Implementasi Interface Printable ─────────────────────
    public function printStruk(): string
    {
        $border = str_repeat('═', 36);
        return implode("\n", [
            $border,
            "    🏥 TIKET ANTREAN RS",
            $border,
            "  Nomor  : {$this->nomorAntrean}",
            "  Tanggal: " . date('d M Y'),
            "  Waktu  : " . date('H:i') . " WIB",
            $border,
            "  Harap simpan tiket ini.",
            $border,
        ]);
    }
}
