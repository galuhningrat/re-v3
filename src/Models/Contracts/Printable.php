<?php

namespace App\Models\Contracts;

/**
 * Interface Printable
 *
 * Kontrak untuk entitas yang dapat mencetak struk/dokumen.
 * Setiap class yang mengimplementasi ini WAJIB mendefinisikan printStruk().
 *
 * @package App\Models\Contracts
 */
interface Printable
{
    /**
     * Mengembalikan representasi teks dari struk/dokumen entitas.
     *
     * @return string
     */
    public function printStruk(): string;
}
