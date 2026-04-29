<?php

namespace App\Models;

use App\Core\Database;
use App\Models\Contracts\Printable;

/**
 * Class RekamMedis — Medical Record
 *
 * KONSEP OOP: Implements Printable (kontrak untuk cetak ringkasan rekam medis).
 * Satu pasien bisa punya banyak rekam medis (one-to-many).
 * Encapsulation: semua input divalidasi sebelum masuk DB.
 *
 * @package App\Models
 */
class RekamMedis implements Printable
{
    public function __construct(
        private string  $pasienId,
        private string  $dokterId,
        private int     $poliId,
        private string  $keluhan,
        private string  $diagnosa,
        private ?string $tindakan      = null,
        private ?string $resep         = null,
        private ?string $catatanDokter = null,
        private ?int    $pendaftaranId = null
    ) {}

    // ─── Getter ───────────────────────────────────────────────
    public function getPasienId(): string  { return $this->pasienId; }
    public function getDokterId(): string  { return $this->dokterId; }

    /**
     * Simpan rekam medis baru ke database.
     * Return ID rekam medis yang baru dibuat.
     *
     * @throws \InvalidArgumentException | \RuntimeException
     */
    public function simpan(): int
    {
        // Validasi
        if (empty(trim($this->keluhan))) {
            throw new \InvalidArgumentException('Keluhan tidak boleh kosong.');
        }
        if (empty(trim($this->diagnosa))) {
            throw new \InvalidArgumentException('Diagnosa tidak boleh kosong.');
        }

        try {
            $pdo  = Database::getInstance()->getConnection();
            $stmt = $pdo->prepare("
                INSERT INTO rekam_medis
                    (pasien_id, dokter_id, poli_id, pendaftaran_id,
                     keluhan, diagnosa, tindakan, resep, catatan_dokter)
                VALUES
                    (:pasien_id, :dokter_id, :poli_id, :pendaftaran_id,
                     :keluhan, :diagnosa, :tindakan, :resep, :catatan_dokter)
                RETURNING id
            ");
            $stmt->execute([
                ':pasien_id'      => $this->pasienId,
                ':dokter_id'      => $this->dokterId,
                ':poli_id'        => $this->poliId,
                ':pendaftaran_id' => $this->pendaftaranId,
                ':keluhan'        => $this->keluhan,
                ':diagnosa'       => $this->diagnosa,
                ':tindakan'       => $this->tindakan,
                ':resep'          => $this->resep,
                ':catatan_dokter' => $this->catatanDokter,
            ]);
            return (int) $stmt->fetchColumn();
        } catch (\PDOException $e) {
            throw new \RuntimeException('Gagal menyimpan rekam medis: ' . $e->getMessage());
        }
    }

    /**
     * Ambil riwayat rekam medis seorang pasien (semua kunjungan).
     * Penting untuk dokter melihat histori sebelum tindakan baru.
     */
    public static function findByPasien(string $pasienId): array
    {
        try {
            $pdo  = Database::getInstance()->getConnection();
            $stmt = $pdo->prepare("
                SELECT
                    rm.*,
                    d.nama  AS dokter_nama,
                    d.spesialis,
                    po.nama AS poli_nama
                FROM rekam_medis rm
                JOIN dokter d  ON rm.dokter_id = d.id
                LEFT JOIN poli po ON rm.poli_id = po.id
                WHERE rm.pasien_id = :pasien_id
                ORDER BY rm.tanggal_periksa DESC, rm.id DESC
            ");
            $stmt->execute([':pasien_id' => $pasienId]);
            return $stmt->fetchAll();
        } catch (\PDOException $e) {
            throw new \RuntimeException('Gagal mengambil riwayat rekam medis.');
        }
    }

    /** Ambil satu rekam medis by ID dengan JOIN */
    public static function findById(int $id): ?array
    {
        try {
            $pdo  = Database::getInstance()->getConnection();
            $stmt = $pdo->prepare("
                SELECT
                    rm.*,
                    ps.nama     AS pasien_nama, ps.nik,
                    d.nama      AS dokter_nama, d.spesialis,
                    po.nama     AS poli_nama
                FROM rekam_medis rm
                JOIN pasien  ps ON rm.pasien_id = ps.id
                JOIN dokter  d  ON rm.dokter_id  = d.id
                LEFT JOIN poli po ON rm.poli_id  = po.id
                WHERE rm.id = :id
            ");
            $stmt->execute([':id' => $id]);
            return $stmt->fetch() ?: null;
        } catch (\PDOException $e) {
            throw new \RuntimeException('Gagal mengambil detail rekam medis.');
        }
    }

    /**
     * Ambil semua rekam medis yang ditangani dokter tertentu.
     * Dipakai di dashboard dokter.
     */
    public static function findByDokter(string $dokterId, ?string $tanggal = null): array
    {
        try {
            $pdo    = Database::getInstance()->getConnection();
            $params = [':dokter_id' => $dokterId];
            $where  = "rm.dokter_id = :dokter_id";

            if ($tanggal) {
                $where .= " AND rm.tanggal_periksa = :tanggal";
                $params[':tanggal'] = $tanggal;
            }

            $stmt = $pdo->prepare("
                SELECT rm.*, ps.nama AS pasien_nama, po.nama AS poli_nama
                FROM rekam_medis rm
                JOIN pasien  ps ON rm.pasien_id = ps.id
                LEFT JOIN poli po ON rm.poli_id = po.id
                WHERE {$where}
                ORDER BY rm.tanggal_periksa DESC, rm.id DESC
            ");
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (\PDOException $e) {
            throw new \RuntimeException('Gagal mengambil data rekam medis dokter.');
        }
    }

    // ─── Implementasi Interface Printable ─────────────────────
    public function printStruk(): string
    {
        $border = str_repeat('-', 44);
        return implode("\n", [
            $border,
            "  RINGKASAN REKAM MEDIS",
            "  Tanggal: " . date('d M Y'),
            $border,
            "  Keluhan  : {$this->keluhan}",
            "  Diagnosa : {$this->diagnosa}",
            "  Tindakan : " . ($this->tindakan ?? '-'),
            "  Resep    : " . ($this->resep    ?? '-'),
            $border,
        ]);
    }
}
