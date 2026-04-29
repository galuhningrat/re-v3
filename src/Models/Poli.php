<?php

namespace App\Models;

use App\Core\Database;

/**
 * Class Poli
 *
 * Representasi Poli/Klinik di rumah sakit.
 * Tidak extends Person karena bukan entitas manusia.
 *
 * @package App\Models
 */
class Poli
{
    private int    $id;
    private string $kode;
    private string $nama;
    private ?string $deskripsi;
    private bool   $isAktif;

    public function __construct(
        string  $kode,
        string  $nama,
        ?string $deskripsi = null,
        bool    $isAktif   = true,
        int     $id        = 0
    ) {
        $this->kode      = $kode;
        $this->nama      = $nama;
        $this->deskripsi = $deskripsi;
        $this->isAktif   = $isAktif;
        $this->id        = $id;
    }

    // ─── Getter ───────────────────────────────────────────────
    public function getId(): int       { return $this->id; }
    public function getKode(): string  { return $this->kode; }
    public function getNama(): string  { return $this->nama; }
    public function isAktif(): bool    { return $this->isAktif; }

    // ─── Static DB Methods ────────────────────────────────────

    public static function findAll(bool $aktifOnly = false): array
    {
        try {
            $pdo = Database::getInstance()->getConnection();
            $sql = "SELECT * FROM poli" . ($aktifOnly ? " WHERE is_aktif = TRUE" : "") . " ORDER BY nama";
            return $pdo->query($sql)->fetchAll();
        } catch (\PDOException $e) {
            throw new \RuntimeException('Gagal mengambil data poli.');
        }
    }

    public static function findById(int $id): ?array
    {
        try {
            $pdo  = Database::getInstance()->getConnection();
            $stmt = $pdo->prepare("SELECT * FROM poli WHERE id = :id");
            $stmt->execute([':id' => $id]);
            return $stmt->fetch() ?: null;
        } catch (\PDOException $e) {
            throw new \RuntimeException('Gagal mengambil data poli.');
        }
    }

    /**
     * Ambil dokter yang bertugas di poli ini hari ini.
     * Join dengan jadwal_dokter untuk filter by hari.
     */
    public static function getDokterOnDuty(int $poliId, ?string $hari = null): array
    {
        $hari ??= self::hariIni();
        try {
            $pdo  = Database::getInstance()->getConnection();
            $stmt = $pdo->prepare("
                SELECT
                    d.id, d.nama, d.spesialis,
                    jd.jam_mulai, jd.jam_selesai, jd.kuota,
                    (SELECT COUNT(*) FROM antrean a
                     JOIN pendaftaran p ON a.pendaftaran_id = p.id
                     WHERE p.dokter_id = d.id
                       AND a.poli_id = :poli_id
                       AND a.tanggal = CURRENT_DATE
                       AND a.status != 'tidak_hadir') AS terpakai
                FROM jadwal_dokter jd
                JOIN dokter d ON jd.dokter_id = d.id
                WHERE jd.poli_id = :poli_id AND jd.hari = :hari
                ORDER BY jd.jam_mulai
            ");
            $stmt->execute([':poli_id' => $poliId, ':hari' => $hari]);
            return $stmt->fetchAll();
        } catch (\PDOException $e) {
            throw new \RuntimeException('Gagal mengambil jadwal dokter.');
        }
    }

    /** Helper: nama hari Indonesia sesuai hari ini */
    public static function hariIni(): string
    {
        $days = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
        return $days[(int) date('w')];
    }

    public static function create(array $data): bool
    {
        try {
            $pdo  = Database::getInstance()->getConnection();
            $stmt = $pdo->prepare(
                "INSERT INTO poli (kode, nama, deskripsi) VALUES (:kode, :nama, :deskripsi)"
            );
            return $stmt->execute([
                ':kode'      => strtoupper(trim($data['kode'])),
                ':nama'      => trim($data['nama']),
                ':deskripsi' => trim($data['deskripsi'] ?? '') ?: null,
            ]);
        } catch (\PDOException $e) {
            if ($e->getCode() === '23505') {
                throw new \RuntimeException("Kode poli '{$data['kode']}' sudah digunakan.");
            }
            throw new \RuntimeException('Gagal menyimpan data poli.');
        }
    }

    public static function update(int $id, array $data): bool
    {
        try {
            $pdo  = Database::getInstance()->getConnection();
            $stmt = $pdo->prepare(
                "UPDATE poli SET nama = :nama, deskripsi = :deskripsi, is_aktif = :is_aktif WHERE id = :id"
            );
            return $stmt->execute([
                ':id'        => $id,
                ':nama'      => trim($data['nama']),
                ':deskripsi' => trim($data['deskripsi'] ?? '') ?: null,
                ':is_aktif'  => isset($data['is_aktif']) ? 'true' : 'false',
            ]);
        } catch (\PDOException $e) {
            throw new \RuntimeException('Gagal memperbarui data poli.');
        }
    }
}
