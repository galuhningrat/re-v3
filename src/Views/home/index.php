<?php

/**
 * View: home/index.php
 * Dashboard utama dengan statistik real-time.
 * Variabel $stats diinject oleh HomeController::index()
 */
?>

<div class="mb-4">
    <h4><i class="bi bi-speedometer2 me-2"></i><?= htmlspecialchars($title) ?></h4>
    <p class="text-muted mb-0">Selamat datang di Sistem Manajemen <?= APP_NAME ?>.</p>
    <small class="text-muted">
        <i class="bi bi-clock me-1"></i>Data diperbarui: <?= date('d M Y, H:i') ?> WIB
    </small>
</div>

<!-- STAT CARDS — angka dari database, bukan hardcoded -->
<div class="row g-3 mb-4">

    <div class="col-md-3 col-sm-6">
        <div class="card text-white h-100" style="background:#1a6fc4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="small opacity-75 mb-1">Total Pasien</div>
                        <div class="fs-2 fw-bold"><?= $stats['total_pasien'] ?></div>
                    </div>
                    <i class="bi bi-people fs-1 opacity-50"></i>
                </div>
            </div>
            <div class="card-footer border-0 bg-black bg-opacity-10">
                <a href="<?= BASE_URL ?>/pasien" class="text-white text-decoration-none small">
                    Lihat semua pasien <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>

    <div class="col-md-3 col-sm-6">
        <div class="card text-white h-100" style="background:#198754">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="small opacity-75 mb-1">Total Dokter</div>
                        <div class="fs-2 fw-bold"><?= $stats['total_dokter'] ?></div>
                    </div>
                    <i class="bi bi-person-badge fs-1 opacity-50"></i>
                </div>
            </div>
            <div class="card-footer border-0 bg-black bg-opacity-10">
                <a href="<?= BASE_URL ?>/dokter" class="text-white text-decoration-none small">
                    Lihat semua dokter <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>

    <div class="col-md-3 col-sm-6">
        <div class="card text-white h-100" style="background:#e6a817">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="small opacity-75 mb-1">Kamar Tersedia</div>
                        <div class="fs-2 fw-bold">
                            <?= $stats['kamar_tersedia'] ?>
                            <small class="fs-6 opacity-75">/ <?= $stats['kamar_total'] ?></small>
                        </div>
                    </div>
                    <i class="bi bi-door-open fs-1 opacity-50"></i>
                </div>
            </div>
            <div class="card-footer border-0 bg-black bg-opacity-10">
                <span class="text-white small">
                    <?= $stats['kamar_total'] - $stats['kamar_tersedia'] ?> kamar terisi
                </span>
            </div>
        </div>
    </div>

    <div class="col-md-3 col-sm-6">
        <div class="card text-white h-100" style="background:#6f42c1">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="small opacity-75 mb-1">Antrian Hari Ini</div>
                        <div class="fs-2 fw-bold"><?= $stats['pendaftaran_menunggu'] ?></div>
                    </div>
                    <i class="bi bi-clipboard2-pulse fs-1 opacity-50"></i>
                </div>
            </div>
            <div class="card-footer border-0 bg-black bg-opacity-10">
                <a href="<?= BASE_URL ?>/pendaftaran" class="text-white text-decoration-none small">
                    <?= $stats['pendaftaran_hari_ini'] ?> pendaftaran hari ini
                    <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>

</div>

<!-- QUICK ACCESS -->
<div class="row g-3">
    <div class="col-md-4">
        <div class="card shadow-sm h-100">
            <div class="card-header fw-semibold bg-light">
                <i class="bi bi-lightning me-1 text-warning"></i>Akses Cepat
            </div>
            <div class="card-body d-flex flex-column gap-2">
                <a href="<?= BASE_URL ?>/pasien/create" class="btn btn-outline-primary btn-sm text-start">
                    <i class="bi bi-person-plus me-2"></i>Tambah Pasien Baru
                </a>
                <a href="<?= BASE_URL ?>/dokter/create" class="btn btn-outline-success btn-sm text-start">
                    <i class="bi bi-person-badge me-2"></i>Tambah Dokter Baru
                </a>
                <a href="<?= BASE_URL ?>/pendaftaran/create" class="btn btn-outline-purple btn-sm text-start"
                    style="border-color:#6f42c1; color:#6f42c1">
                    <i class="bi bi-clipboard2-plus me-2"></i>Buat Pendaftaran
                </a>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card shadow-sm h-100">
            <div class="card-header fw-semibold bg-light">
                <i class="bi bi-info-circle me-1 text-info"></i>
                Konsep OOP yang Diterapkan di Sistem Ini
            </div>
            <div class="card-body">
                <div class="row g-2 small">
                    <div class="col-6">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="badge bg-danger">Abstract</span>
                            <span>Class <code>Person</code> — blueprint Dokter & Pasien</span>
                        </div>
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="badge bg-info text-dark">Abstract</span>
                            <span>Class <code>MedicalStaff</code> — extends Person</span>
                        </div>
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="badge bg-primary">Interface</span>
                            <span><code>Manageable</code> — kontrak CRUD</span>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-primary">Interface</span>
                            <span><code>Printable</code> — kontrak struk</span>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="badge bg-success">Inherit</span>
                            <span><code>Dokter</code> extends MedicalStaff</span>
                        </div>
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="badge bg-success">Inherit</span>
                            <span><code>Pasien</code> extends Person</span>
                        </div>
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="badge bg-warning text-dark">Override</span>
                            <span><code>getInfo()</code> di Dokter & Pasien</span>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-secondary">PSR-4</span>
                            <span>Autoloading via <code>autoload.php</code></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>