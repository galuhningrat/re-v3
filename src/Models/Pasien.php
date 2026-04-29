<?php

namespace App\Models;

use App\Core\Database;
use App\Models\Contracts\Manageable;

/**
 * Class Pasien — extends Person (Inheritance 2)
 *
 * KONSEP OOP:
 * - Inheritance: extends Person (abstract class)
 * - parent::__construct() memanggil constructor Person
 * - Method Overriding: getInfo() ditambah info keluhan
 * - Interface Manageable: kontrak CRUD ke database
 * - Encapsulation: semua properti private, akses via getter
 *
 * @package App\Models
 */
class Pasien extends Person implements Manageable
{
    private string  $keluhan;
    private ?string $tanggalLahir;  // ← kolom dari DB yang sebelumnya terlewat
    private ?string $alamat;
    private ?string $noHp;

    public function __construct(
        string  $nama,
        string  $id,
        string  $keluhan,
        ?string $tanggalLahir = null,
        ?string $alamat       = null,
        ?string $noHp         = null
    ) {
        // PARENT KEYWORD — memanggil constructor Person
        parent::__construct($nama, $id);

        $this->keluhan      = $keluhan;
        $this->tanggalLahir = $tanggalLahir;
        $this->alamat       = $alamat;
        $this->noHp         = $noHp;
    }

    // ─── Implementasi abstract method dari Person ──────────────
    public function getRole(): string
    {
        return 'Pasien';
    }

    // ─── Getter (Encapsulation) ────────────────────────────────
    public function getKeluhan(): string
    {
        return $this->keluhan;
    }
    public function getTanggalLahir(): ?string
    {
        return $this->tanggalLahir;
    }
    public function getAlamat(): ?string
    {
        return $this->alamat;
    }
    public function getNoHp(): ?string
    {
        return $this->noHp;
    }

    /**
     * METHOD OVERRIDING — menimpa getInfo() dari Person
     * dengan menambahkan informasi keluhan.
     */
    public function getInfo(): string
    {
        return "[Pasien] " . parent::getInfo() . " | Keluhan: {$this->keluhan}";
    }

    // =========================================================
    // Implementasi Interface Manageable — Full CRUD + Exception
    // =========================================================

    /**
     * READ ALL — ambil semua pasien dari database.
     *
     * @return array
     * @throws \RuntimeException jika query gagal
     */
    public static function findAll(): array
    {
        try {
            $pdo  = Database::getInstance()->getConnection();
            $stmt = $pdo->query(
                "SELECT id, nama, keluhan, tanggal_lahir, alamat, no_hp, created_at
                 FROM pasien
                 ORDER BY created_at DESC"
            );
            return $stmt->fetchAll();
        } catch (\PDOException $e) {
            // Bungkus PDOException menjadi RuntimeException
            // agar Controller tidak perlu tau detail PDO
            throw new \RuntimeException(
                'Gagal mengambil data pasien. Silakan coba lagi.'
            );
        }
    }

    /**
     * READ ONE — ambil satu pasien by ID.
     *
     * @param string $id
     * @return array|null null jika tidak ditemukan
     * @throws \RuntimeException jika query gagal
     */
    public static function findById(string $id): ?array
    {
        if (empty(trim($id))) {
            throw new \InvalidArgumentException('ID pasien tidak boleh kosong.');
        }

        try {
            $pdo  = Database::getInstance()->getConnection();
            $stmt = $pdo->prepare(
                "SELECT id, nama, keluhan, tanggal_lahir, alamat, no_hp, created_at
                 FROM pasien WHERE id = :id LIMIT 1"
            );
            $stmt->execute([':id' => $id]);
            $result = $stmt->fetch();
            return $result ?: null;
        } catch (\PDOException $e) {
            throw new \RuntimeException('Gagal mengambil data pasien.');
        }
    }

    /**
     * CREATE — insert pasien baru ke database.
     * Gunakan Prepared Statement untuk cegah SQL Injection.
     *
     * @return bool
     * @throws \RuntimeException jika insert gagal
     */
    public function save(): bool
    {
        // Validasi business rule sebelum hit database
        if (empty(trim($this->keluhan))) {
            throw new \InvalidArgumentException('Keluhan pasien tidak boleh kosong.');
        }

        // Validasi tanggal lahir jika diisi
        if ($this->tanggalLahir !== null && $this->tanggalLahir !== '') {
            $date = \DateTime::createFromFormat('Y-m-d', $this->tanggalLahir);
            if (!$date || $date > new \DateTime()) {
                throw new \InvalidArgumentException('Tanggal lahir tidak valid.');
            }
        }

        try {
            $pdo  = Database::getInstance()->getConnection();
            $stmt = $pdo->prepare(
                "INSERT INTO pasien (id, nama, keluhan, tanggal_lahir, alamat, no_hp)
                 VALUES (:id, :nama, :keluhan, :tanggal_lahir, :alamat, :no_hp)"
            );
            return $stmt->execute([
                ':id'            => $this->getId(),
                ':nama'          => $this->nama,
                ':keluhan'       => $this->keluhan,
                ':tanggal_lahir' => $this->tanggalLahir ?: null,
                ':alamat'        => $this->alamat,
                ':no_hp'         => $this->noHp,
            ]);
        } catch (\PDOException $e) {
            // Deteksi duplikat ID (PostgreSQL error code 23505)
            if ($e->getCode() === '23505') {
                throw new \RuntimeException(
                    "ID pasien '{$this->getId()}' sudah digunakan. Gunakan ID lain."
                );
            }
            throw new \RuntimeException('Gagal menyimpan data pasien: ' . $e->getMessage());
        }
    }

    /**
     * UPDATE — perbarui data pasien yang sudah ada.
     *
     * @param string $id   ID pasien yang akan diupdate
     * @param array  $data Kolom yang mau diubah
     * @return bool
     * @throws \RuntimeException | \InvalidArgumentException
     */
    public static function update(string $id, array $data): bool
    {
        if (empty(trim($data['nama'] ?? ''))) {
            throw new \InvalidArgumentException('Nama pasien tidak boleh kosong.');
        }
        if (empty(trim($data['keluhan'] ?? ''))) {
            throw new \InvalidArgumentException('Keluhan pasien tidak boleh kosong.');
        }

        // Validasi tanggal lahir jika diisi
        $tglLahir = $data['tanggal_lahir'] ?? null;
        if (!empty($tglLahir)) {
            $date = \DateTime::createFromFormat('Y-m-d', $tglLahir);
            if (!$date || $date > new \DateTime()) {
                throw new \InvalidArgumentException('Tanggal lahir tidak valid.');
            }
        } else {
            $tglLahir = null;
        }

        try {
            $pdo  = Database::getInstance()->getConnection();
            $stmt = $pdo->prepare(
                "UPDATE pasien
                 SET nama = :nama,
                     keluhan = :keluhan,
                     tanggal_lahir = :tanggal_lahir,
                     alamat = :alamat,
                     no_hp = :no_hp
                 WHERE id = :id"
            );
            return $stmt->execute([
                ':id'            => $id,
                ':nama'          => htmlspecialchars(trim($data['nama'])),
                ':keluhan'       => htmlspecialchars(trim($data['keluhan'])),
                ':tanggal_lahir' => $tglLahir,
                ':alamat'        => htmlspecialchars(trim($data['alamat'] ?? '')),
                ':no_hp'         => htmlspecialchars(trim($data['no_hp'] ?? '')),
            ]);
        } catch (\PDOException $e) {
            throw new \RuntimeException('Gagal memperbarui data pasien: ' . $e->getMessage());
        }
    }

    /**
     * DELETE — hapus pasien by ID.
     *
     * @param string $id
     * @return bool
     * @throws \RuntimeException
     */
    public function delete(string $id): bool
    {
        try {
            $pdo  = Database::getInstance()->getConnection();
            $stmt = $pdo->prepare("DELETE FROM pasien WHERE id = :id");
            return $stmt->execute([':id' => $id]);
        } catch (\PDOException $e) {
            // Foreign key violation — pasien masih punya pendaftaran aktif
            if ($e->getCode() === '23503') {
                throw new \RuntimeException(
                    'Pasien tidak bisa dihapus karena masih memiliki data pendaftaran.'
                );
            }
            throw new \RuntimeException('Gagal menghapus data pasien.');
        }
    }

    /**
     * Generate ID pasien otomatis — format P001, P002, ...
     * Menggunakan MAX untuk hindari gap jika ada data yang dihapus.
     *
     * @return string
     */
    public static function generateId(): string
    {
        try {
            $pdo  = Database::getInstance()->getConnection();
            // Ambil ID terakhir yang formatnya P + angka
            $stmt = $pdo->query(
                "SELECT id FROM pasien WHERE id ~ '^P[0-9]+$'
                 ORDER BY CAST(SUBSTRING(id FROM 2) AS INTEGER) DESC
                 LIMIT 1"
            );
            $lastId = $stmt->fetchColumn();

            if ($lastId) {
                $num  = (int) substr($lastId, 1) + 1;
            } else {
                $num  = 1;
            }

            return 'P' . str_pad($num, 3, '0', STR_PAD_LEFT);
        } catch (\PDOException $e) {
            // Fallback jika query gagal
            return 'P' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);
        }
    }
}
