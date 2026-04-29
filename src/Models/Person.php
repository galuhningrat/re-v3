<?php

namespace App\Models;

/**
 * Abstract Class Person
 *
 * Representasi dasar dari setiap orang dalam sistem.
 * Abstract karena kita tidak pernah membuat objek "Person" secara langsung —
 * selalu melalui subclass konkret (Dokter, Pasien).
 *
 * @package App\Models
 */
abstract class Person
{
    /** @var string Nama lengkap */
    protected string $nama;

    /** @var string ID unik (P001, D001, dst) */
    private string $id;

    /**
     * @param string $nama Nama lengkap
     * @param string $id   ID unik entitas
     */
    public function __construct(string $nama, string $id)
    {
        $this->nama = $nama;
        $this->id   = $id;
    }

    /**
     * Getter untuk $id yang private.
     * Encapsulation: $id tidak bisa diakses langsung dari luar.
     *
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    public function getNama(): string
    {
        return $this->nama;
    }

    /**
     * Mengembalikan informasi dasar entitas.
     * Di-override oleh subclass (Method Overriding).
     *
     * @return string
     */
    public function getInfo(): string
    {
        return "ID: {$this->id} | Nama: {$this->nama}";
    }

    /**
     * Method abstract: subclass WAJIB mengimplementasikan.
     * Mendefinisikan peran spesifik entitas di rumah sakit.
     *
     * @return string
     */
    abstract public function getRole(): string;
}
