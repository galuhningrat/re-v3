<?php

namespace App\Models;

/**
 * Abstract Class MedicalStaff — extends Person (Inheritance Chain Level 2)
 *
 * KONSEP OOP (PENTING UNTUK PRESENTASI):
 *
 * Ini adalah "Abstract Class bertingkat":
 *   Person (abstract) ← MedicalStaff (abstract) ← Dokter (concrete)
 *
 * Mengapa MedicalStaff masih abstract?
 * Karena di dunia nyata, "Tenaga Medis" itu masih umum —
 * ada Dokter, Perawat, Apoteker, dll.
 * Kita tidak pernah membuat objek "MedicalStaff" langsung.
 *
 * Apa yang ditambahkan MedicalStaff di atas Person?
 * - Properti $spesialis (khusus tenaga medis)
 * - Method getSpesialis()
 * - Override getInfo() untuk tambahkan spesialis
 * - Abstract method getJadwal() — wajib diimplementasi Dokter
 *
 * @package App\Models
 */
abstract class MedicalStaff extends Person
{
    /**
     * Bidang spesialisasi atau keahlian tenaga medis.
     * protected agar bisa diakses child class (Dokter).
     */
    protected string $spesialis;

    /**
     * Constructor — memanggil parent (Person) via parent::
     *
     * KONSEP: parent:: di constructor "chain" —
     * Dokter → MedicalStaff → Person
     * Setiap level memanggil parent di atasnya.
     *
     * @param string $nama      Nama lengkap tenaga medis
     * @param string $id        ID unik (misal D001)
     * @param string $spesialis Bidang spesialisasi
     */
    public function __construct(string $nama, string $id, string $spesialis)
    {
        // PARENT KEYWORD level 2: memanggil Person::__construct()
        parent::__construct($nama, $id);
        $this->spesialis = $spesialis;
    }

    // ─── Getter ───────────────────────────────────────────────

    public function getSpesialis(): string
    {
        return $this->spesialis;
    }

    /**
     * METHOD OVERRIDING (level 1): menimpa Person::getInfo()
     * untuk menambahkan info spesialisasi.
     *
     * Dokter akan override ini lagi (level 2) untuk tambahkan "[Dokter]".
     */
    public function getInfo(): string
    {
        return parent::getInfo() . " | Spesialis: {$this->spesialis}";
    }

    /**
     * ABSTRACT METHOD baru di level MedicalStaff.
     * Setiap tenaga medis wajib mendefinisikan jadwal kerjanya.
     * Dokter mengimplementasikan ini dengan jadwal praktek.
     *
     * @return string Representasi jadwal (misal "Senin - Jumat")
     */
    abstract public function getJadwal(): string;
}
