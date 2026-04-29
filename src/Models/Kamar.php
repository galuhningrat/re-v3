<?php

namespace App\Models;

use App\Core\Database;

/**
 * Class Kamar
 *
 * Representasi kamar rawat inap di rumah sakit.
 * Encapsulation penuh dengan getter/setter.
 *
 * @package App\Models
 */
class Kamar
{
    private int     $id;
    private string  $nomorKamar;
    private string  $tipe;
    private bool    $isTersedia;
    private float   $hargaPerMalam;

    public function __construct(
        string $nomorKamar,
        string $tipe,
        float  $hargaPerMalam = 0,
        bool   $isTersedia    = true,
        int    $id            = 0
    ) {
        $this->nomorKamar   = $nomorKamar;
        $this->tipe         = $tipe;
        $this->hargaPerMalam = $hargaPerMalam;
        $this->isTersedia   = $isTersedia;
        $this->id           = $id;
    }

    // =========================================================
    // Getters (Encapsulation)
    // =========================================================

    public function getId(): int
    {
        return $this->id;
    }
    public function getNomorKamar(): string
    {
        return $this->nomorKamar;
    }
    public function getTipe(): string
    {
        return $this->tipe;
    }
    public function isTersedia(): bool
    {
        return $this->isTersedia;
    }
    public function getHarga(): float
    {
        return $this->hargaPerMalam;
    }

    public function getStatus(): string
    {
        return $this->isTersedia ? 'Tersedia' : 'Terisi';
    }

    public function getDetailKamar(): string
    {
        return "Kamar: {$this->nomorKamar} ({$this->tipe}) - Status: {$this->getStatus()}";
    }

    // =========================================================
    // Business Logic
    // =========================================================

    public function bookingKamar(): void
    {
        $this->isTersedia = false;
    }

    public function bebaskanKamar(): void
    {
        $this->isTersedia = true;
    }

    // =========================================================
    // Database Operations (Static)
    // =========================================================

    public static function findAll(): array
    {
        try {
            $pdo  = Database::getInstance()->getConnection();
            $stmt = $pdo->query("SELECT * FROM kamar ORDER BY nomor_kamar ASC");
            return $stmt->fetchAll();
        } catch (\PDOException $e) {
            throw new \RuntimeException('Gagal mengambil data kamar: ' . $e->getMessage());
        }
    }

    public static function findTersedia(): array
    {
        try {
            $pdo  = Database::getInstance()->getConnection();
            $stmt = $pdo->prepare("SELECT * FROM kamar WHERE is_tersedia = true ORDER BY tipe");
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (\PDOException $e) {
            throw new \RuntimeException('Gagal mengambil data kamar tersedia.');
        }
    }

    public static function findById(int $id): ?array
    {
        try {
            $pdo  = Database::getInstance()->getConnection();
            $stmt = $pdo->prepare("SELECT * FROM kamar WHERE id = :id");
            $stmt->execute([':id' => $id]);
            $result = $stmt->fetch();
            return $result ?: null;
        } catch (\PDOException $e) {
            throw new \RuntimeException('Gagal mengambil data kamar.');
        }
    }
}
