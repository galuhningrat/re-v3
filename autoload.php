<?php

/**
 * Custom PSR-4 Autoloader
 *
 * Memetakan namespace App\ ke direktori src/
 * Contoh: App\Models\Pasien → src/Models/Pasien.php
 *
 * @author  Kelompok Anda
 * @version 1.0
 */

spl_autoload_register(function (string $fullyQualifiedClassName): void {

    // Prefix namespace yang kita handle
    $prefix    = 'App\\';
    $baseDir   = __DIR__ . '/src/';

    // Cek apakah class ini milik namespace kita
    $len = strlen($prefix);
    if (strncmp($prefix, $fullyQualifiedClassName, $len) !== 0) {
        // Bukan milik kita, biarkan autoloader lain handle
        return;
    }

    // Ambil bagian setelah prefix: misal "Models\Pasien"
    $relativeClass = substr($fullyQualifiedClassName, $len);

    // Ganti namespace separator (\) dengan directory separator (/)
    // lalu tambahkan ekstensi .php
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});
