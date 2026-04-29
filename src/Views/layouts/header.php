<?php

/**
 * Layout: header.php v2
 * Menampilkan sidebar sesuai ROLE user yang login.
 */

use App\Core\Auth;

Auth::startSession();
Auth::requireLogin(); // Paksa login untuk semua halaman pakai layout ini

$userRole = Auth::role();
$userName = Auth::user('nama');
$pageTitle = ($title ?? 'Dashboard') . ' — ' . APP_NAME;

// Helper: aktif jika URL sesuai
$isActive = fn(string $path): string =>
str_starts_with($_SERVER['REQUEST_URI'], BASE_URL . $path) ? 'active' : '';
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f0f2f5;
        }

        .sidebar {
            min-height: 100vh;
            background: #1a2e4a;
        }

        .sidebar .nav-link {
            color: #adb5bd;
            border-radius: 6px;
        }

        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            color: #fff;
            background: rgba(255, 255, 255, .12);
        }

        .sidebar .nav-section {
            color: #5a7a9a;
            font-size: .68rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .08em;
            padding: 12px 12px 4px;
        }

        .sidebar-brand {
            color: #fff;
            font-weight: 700;
            font-size: 1.05rem;
        }

        .main-content {
            padding: 2rem;
        }

        .role-badge-admin {
            background: #dc3545;
        }

        .role-badge-dokter {
            background: #0d6efd;
        }

        .role-badge-kasir {
            background: #198754;
        }
    </style>
</head>

<body>
    <div class="container-fluid">
        <div class="row">

            <!-- Sidebar -->
            <nav class="col-md-2 sidebar py-3 px-3">
                <div class="sidebar-brand mb-4 px-2">
                    <i class="bi bi-hospital me-2"></i><?= APP_NAME ?>
                </div>

                <!-- Info User Login -->
                <?php if (isset($authUser)): ?>
                    <div class="px-2 mb-3 pb-3 border-bottom border-secondary">
                        <div class="text-white small fw-semibold">
                            <i class="bi bi-person-circle me-1"></i>
                            <?= htmlspecialchars($authUser['nama']) ?>
                        </div>
                        <span class="badge <?= $authRole === 'admin' ? 'bg-danger' : 'bg-info text-dark' ?> mt-1">
                            <?= ucfirst($authRole) ?>
                        </span>
                    </div>
                <?php endif; ?>

                <ul class="nav flex-column gap-1">
                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>/">
                            <i class="bi bi-speedometer2 me-2"></i>Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>/pasien">
                            <i class="bi bi-people me-2"></i>Pasien
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>/dokter">
                            <i class="bi bi-person-badge me-2"></i>Dokter
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>/pendaftaran">
                            <i class="bi bi-clipboard2-pulse me-2"></i>Pendaftaran
                        </a>
                    </li>
                    <!-- Menu Users — admin only -->
                    <?php if (isset($authRole) && $authRole === 'admin'): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= BASE_URL ?>/users">
                                <i class="bi bi-shield-lock me-2"></i>Manajemen User
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>

                <!-- Tombol Logout -->
                <div class="mt-auto pt-4 px-2">
                    <a href="<?= BASE_URL ?>/logout" class="btn btn-outline-light btn-sm w-100">
                        <i class="bi bi-box-arrow-right me-1"></i>Logout
                    </a>
                </div>
            </nav>

            <!-- ══════ MAIN CONTENT ══════ -->
            <main class="col-md-10 main-content">