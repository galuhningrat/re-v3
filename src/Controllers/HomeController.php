<?php

namespace App\Controllers;

use App\Core\BaseController;
use App\Core\Database;
use App\Core\Auth;

/**
 * Class HomeController
 *
 * Menampilkan dashboard utama dengan statistik real-time dari database.
 *
 * @package App\Controllers
 */
class HomeController extends BaseController
{
    /**
     * GET /
     * Dashboard dengan stats diambil langsung dari DB.
     */
    public function index(): void
    {
        Auth::requireLogin();
        
        $stats = $this->fetchStats();

        $this->render('home/index', [
            'title' => 'Dashboard',
            'stats' => $stats,
        ]);
    }

    /**
     * Ambil semua statistik dari database dalam satu method.
     * Jika DB error, kembalikan array default (angka 0)
     * agar halaman tidak blank.
     *
     * @return array<string, int>
     */
    private function fetchStats(): array
    {
        // Default fallback jika DB tidak bisa dihubungi
        $default = [
            'total_pasien'        => 0,
            'total_dokter'        => 0,
            'kamar_tersedia'      => 0,
            'kamar_total'         => 0,
            'pendaftaran_hari_ini' => 0,
            'pendaftaran_menunggu' => 0,
        ];

        try {
            $pdo = Database::getInstance()->getConnection();

            return [
                'total_pasien' => (int) $pdo
                    ->query("SELECT COUNT(*) FROM pasien")
                    ->fetchColumn(),

                'total_dokter' => (int) $pdo
                    ->query("SELECT COUNT(*) FROM dokter")
                    ->fetchColumn(),

                'kamar_tersedia' => (int) $pdo
                    ->query("SELECT COUNT(*) FROM kamar WHERE is_tersedia = TRUE")
                    ->fetchColumn(),

                'kamar_total' => (int) $pdo
                    ->query("SELECT COUNT(*) FROM kamar")
                    ->fetchColumn(),

                'pendaftaran_hari_ini' => (int) $pdo
                    ->query("SELECT COUNT(*) FROM pendaftaran WHERE DATE(created_at) = CURRENT_DATE")
                    ->fetchColumn(),

                'pendaftaran_menunggu' => (int) $pdo
                    ->query("SELECT COUNT(*) FROM pendaftaran WHERE status = 'menunggu'")
                    ->fetchColumn(),
            ];
        } catch (\RuntimeException $e) {
            // Jangan crash — tampilkan 0 dan biarkan halaman tetap muncul
            return $default;
        }
    }
}
