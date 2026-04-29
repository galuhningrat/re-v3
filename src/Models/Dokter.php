<?php

namespace App\Models;

use App\Core\Database;
use App\Models\Contracts\Manageable;

/**
 * Class Dokter — extends MedicalStaff (Inheritance Chain: Dokter → MedicalStaff → Person)
 *
 * KONSEP OOP:
 * - Inheritance level 3: Dokter extends MedicalStaff extends Person
 * - Implements Manageable: kontrak CRUD ke PostgreSQL
 * - Method Overriding: getInfo() menambahkan label "[Dokter]"
 * - parent:: dipakai di constructor (MedicalStaff::__construct)
 * - Implements abstract method: getRole() dari Person & getJadwal() dari MedicalStaff
 *
 * @package App\Models
 */
class Dokter extends MedicalStaff implements Manageable
{
    /** @var string|null Nomor HP dokter */
    private ?string $noHp;

    /** @var string|null Email dokter */
    private ?string $email;

    public function __construct(
        string  $nama,
        string  $id,
        string  $spesialis,
        ?string $noHp  = null,
        ?string $email = null
    ) {
        // PARENT KEYWORD — memanggil MedicalStaff::__construct()
        // yang kemudian memanggil Person::__construct() juga
        parent::__construct($nama, $id, $spesialis);

        $this->noHp  = $noHp;
        $this->email = $email;
    }

    // ─── Implementasi abstract method dari Person ──────────────

    /**
     * Mendefinisikan peran spesifik di sistem RS.
     * Wajib karena Person::getRole() adalah abstract.
     */
    public function getRole(): string
    {
        return 'Dokter';
    }

    // ─── Implementasi abstract method dari MedicalStaff ───────

    /**
     * Jadwal praktek dokter.
     * Wajib karena MedicalStaff::getJadwal() adalah abstract.
     * Di implementasi DB, ini bisa diambil dari kolom jadwal.
     */
    public function getJadwal(): string
    {
        return 'Senin - Jumat';
    }

    // ─── Getter ───────────────────────────────────────────────

    public function getNoHp(): ?string
    {
        return $this->noHp;
    }
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * METHOD OVERRIDING (level 2) — menimpa MedicalStaff::getInfo()
     * Hasilnya: "[Dokter] ID: D001 | Nama: Dr. X | Spesialis: Kardiologi"
     */
    public function getInfo(): string
    {
        return "[Dokter] " . parent::getInfo();
    }

    // =========================================================
    // Implementasi Interface Manageable (Full CRUD + Exception)
    // =========================================================

    /**
     * READ ALL — ambil semua dokter.
     *
     * @return array
     * @throws \RuntimeException
     */
    public static function findAll(): array
    {
        try {
            $pdo  = Database::getInstance()->getConnection();
            $stmt = $pdo->query(
                "SELECT id, nama, spesialis, no_hp, email, created_at
                 FROM dokter ORDER BY nama ASC"
            );
            return $stmt->fetchAll();
        } catch (\PDOException $e) {
            throw new \RuntimeException('Gagal mengambil data dokter. Silakan coba lagi.');
        }
    }

    /**
     * READ ONE — ambil satu dokter by ID.
     *
     * @param string $id
     * @return array|null
     * @throws \RuntimeException | \InvalidArgumentException
     */
    public static function findById(string $id): ?array
    {
        if (empty(trim($id))) {
            throw new \InvalidArgumentException('ID dokter tidak boleh kosong.');
        }

        try {
            $pdo  = Database::getInstance()->getConnection();
            $stmt = $pdo->prepare(
                "SELECT id, nama, spesialis, no_hp, email, created_at
                 FROM dokter WHERE id = :id"
            );
            $stmt->execute([':id' => $id]);
            $result = $stmt->fetch();
            return $result ?: null;
        } catch (\PDOException $e) {
            throw new \RuntimeException('Gagal mengambil data dokter.');
        }
    }

    /**
     * CREATE — insert dokter baru.
     *
     * @return bool
     * @throws \RuntimeException | \InvalidArgumentException
     */
    public function save(): bool
    {
        if (empty(trim($this->spesialis))) {
            throw new \InvalidArgumentException('Spesialisasi dokter tidak boleh kosong.');
        }

        try {
            $pdo  = Database::getInstance()->getConnection();
            $stmt = $pdo->prepare(
                "INSERT INTO dokter (id, nama, spesialis, no_hp, email)
                 VALUES (:id, :nama, :spesialis, :no_hp, :email)"
            );
            return $stmt->execute([
                ':id'        => $this->getId(),
                ':nama'      => $this->nama,
                ':spesialis' => $this->spesialis,
                ':no_hp'     => $this->noHp,
                ':email'     => $this->email,
            ]);
        } catch (\PDOException $e) {
            if ($e->getCode() === '23505') {
                throw new \RuntimeException(
                    "ID dokter '{$this->getId()}' sudah digunakan."
                );
            }
            throw new \RuntimeException('Gagal menyimpan data dokter: ' . $e->getMessage());
        }
    }

    /**
     * DELETE — hapus dokter by ID.
     *
     * @param string $id
     * @return bool
     * @throws \RuntimeException
     */
    public function delete(string $id): bool
    {
        try {
            $pdo  = Database::getInstance()->getConnection();
            $stmt = $pdo->prepare("DELETE FROM dokter WHERE id = :id");
            return $stmt->execute([':id' => $id]);
        } catch (\PDOException $e) {
            if ($e->getCode() === '23503') {
                throw new \RuntimeException(
                    'Dokter tidak bisa dihapus karena masih memiliki data pendaftaran aktif.'
                );
            }
            throw new \RuntimeException('Gagal menghapus data dokter.');
        }
    }
}
