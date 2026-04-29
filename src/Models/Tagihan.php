<?php

namespace App\Models;

use App\Core\Database;
use App\Models\Contracts\Printable;

/**
 * Class Tagihan — Billing & Kasir
 *
 * Mengelola tagihan pasien (biaya periksa + tindakan + obat + kamar).
 * Total dihitung otomatis oleh PostgreSQL (GENERATED ALWAYS AS column).
 *
 * Implements Printable untuk cetak struk pembayaran.
 *
 * @package App\Models
 */
class Tagihan implements Printable
{
    public function __construct(
        private int   $pendaftaranId,
        private float $biayaPeriksa  = 0,
        private float $biayaTindakan = 0,
        private float $biayaObat     = 0,
        private float $biayaKamar    = 0,
        private ?int  $rekamMedisId  = null
    ) {}

    /**
     * Simpan tagihan baru.
     * @return int ID tagihan baru
     * @throws \RuntimeException
     */
    public function simpan(): int
    {
        try {
            $pdo  = Database::getInstance()->getConnection();
            $stmt = $pdo->prepare("
                INSERT INTO tagihan
                    (pendaftaran_id, rekam_medis_id,
                     biaya_periksa, biaya_tindakan, biaya_obat, biaya_kamar,
                     status_bayar)
                VALUES
                    (:pend_id, :rm_id, :periksa, :tindakan, :obat, :kamar, 'belum_bayar')
                RETURNING id
            ");
            $stmt->execute([
                ':pend_id'  => $this->pendaftaranId,
                ':rm_id'    => $this->rekamMedisId,
                ':periksa'  => $this->biayaPeriksa,
                ':tindakan' => $this->biayaTindakan,
                ':obat'     => $this->biayaObat,
                ':kamar'    => $this->biayaKamar,
            ]);
            return (int) $stmt->fetchColumn();
        } catch (\PDOException $e) {
            throw new \RuntimeException('Gagal menyimpan tagihan: ' . $e->getMessage());
        }
    }

    /**
     * Proses pembayaran — update status menjadi lunas.
     */
    public static function bayar(int $id, string $metodeBayar, ?string $catatan = null): bool
    {
        $valid = ['tunai', 'bpjs', 'transfer', 'kartu_kredit', 'kartu_debit'];
        if (!in_array($metodeBayar, $valid)) {
            throw new \InvalidArgumentException('Metode bayar tidak valid.');
        }
        try {
            $pdo  = Database::getInstance()->getConnection();
            $stmt = $pdo->prepare("
                UPDATE tagihan
                SET status_bayar = 'lunas',
                    metode_bayar  = :metode,
                    catatan_kasir = :catatan,
                    dibayar_at    = NOW()
                WHERE id = :id
            ");
            return $stmt->execute([
                ':id'      => $id,
                ':metode'  => $metodeBayar,
                ':catatan' => $catatan,
            ]);
        } catch (\PDOException $e) {
            throw new \RuntimeException('Gagal memproses pembayaran.');
        }
    }

    /** Ambil tagihan lengkap by ID dengan JOIN */
    public static function findById(int $id): ?array
    {
        try {
            $pdo  = Database::getInstance()->getConnection();
            $stmt = $pdo->prepare("
                SELECT
                    t.*,
                    ps.nama  AS pasien_nama, ps.id AS pasien_id,
                    dk.nama  AS dokter_nama,
                    km.nomor_kamar, km.tipe AS kamar_tipe,
                    pn.tanggal_janji
                FROM tagihan t
                JOIN pendaftaran pn ON t.pendaftaran_id = pn.id
                JOIN pasien      ps ON pn.pasien_id     = ps.id
                JOIN dokter      dk ON pn.dokter_id     = dk.id
                LEFT JOIN kamar  km ON pn.kamar_id      = km.id
                WHERE t.id = :id
            ");
            $stmt->execute([':id' => $id]);
            return $stmt->fetch() ?: null;
        } catch (\PDOException $e) {
            throw new \RuntimeException('Gagal mengambil data tagihan.');
        }
    }

    /** Daftar tagihan belum bayar — untuk halaman kasir */
    public static function findBelumBayar(): array
    {
        try {
            $pdo  = Database::getInstance()->getConnection();
            $stmt = $pdo->query("
                SELECT
                    t.id, t.total, t.status_bayar, t.created_at,
                    ps.nama AS pasien_nama,
                    dk.nama AS dokter_nama
                FROM tagihan t
                JOIN pendaftaran pn ON t.pendaftaran_id = pn.id
                JOIN pasien      ps ON pn.pasien_id     = ps.id
                JOIN dokter      dk ON pn.dokter_id     = dk.id
                WHERE t.status_bayar = 'belum_bayar'
                ORDER BY t.created_at DESC
            ");
            return $stmt->fetchAll();
        } catch (\PDOException $e) {
            throw new \RuntimeException('Gagal mengambil data tagihan.');
        }
    }

    /**
     * Laporan pendapatan harian / bulanan.
     *
     * @param string $periode 'harian' | 'bulanan'
     */
    public static function getLaporan(string $periode = 'harian'): array
    {
        try {
            $pdo = Database::getInstance()->getConnection();

            $groupBy = $periode === 'bulanan'
                ? "DATE_TRUNC('month', dibayar_at)"
                : "DATE(dibayar_at)";

            $stmt = $pdo->query("
                SELECT
                    {$groupBy}      AS periode,
                    COUNT(*)         AS jumlah_transaksi,
                    SUM(total)       AS total_pendapatan,
                    SUM(biaya_periksa)  AS total_periksa,
                    SUM(biaya_tindakan) AS total_tindakan,
                    SUM(biaya_obat)     AS total_obat,
                    SUM(biaya_kamar)    AS total_kamar
                FROM tagihan
                WHERE status_bayar = 'lunas'
                GROUP BY {$groupBy}
                ORDER BY {$groupBy} DESC
                LIMIT 30
            ");
            return $stmt->fetchAll();
        } catch (\PDOException $e) {
            throw new \RuntimeException('Gagal mengambil laporan pendapatan.');
        }
    }

    // ─── Implementasi Interface Printable ─────────────────────
    public function printStruk(): string
    {
        $total  = $this->biayaPeriksa + $this->biayaTindakan + $this->biayaObat + $this->biayaKamar;
        $border = str_repeat('=', 40);
        return implode("\n", [
            $border,
            "       STRUK PEMBAYARAN RS",
            $border,
            sprintf("  Biaya Periksa  : Rp %s", number_format($this->biayaPeriksa, 0, ',', '.')),
            sprintf("  Biaya Tindakan : Rp %s", number_format($this->biayaTindakan, 0, ',', '.')),
            sprintf("  Biaya Obat     : Rp %s", number_format($this->biayaObat, 0, ',', '.')),
            sprintf("  Biaya Kamar    : Rp %s", number_format($this->biayaKamar, 0, ',', '.')),
            $border,
            sprintf("  TOTAL          : Rp %s", number_format($total, 0, ',', '.')),
            $border,
            "  Terima kasih atas kepercayaan Anda.",
            $border,
        ]);
    }
}
