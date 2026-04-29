<?php

namespace App\Models\Contracts;

/**
 * Interface Manageable
 *
 * Kontrak CRUD standar untuk Model yang terhubung ke database.
 *
 * @package App\Models\Contracts
 */
interface Manageable
{
    public static function findAll(): array;
    public static function findById(string $id): ?array;
    public function save(): bool;
    public function delete(string $id): bool;
}