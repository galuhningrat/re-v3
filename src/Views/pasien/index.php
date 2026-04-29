<?php

/**
 * View: pasien/index.php
 * Daftar semua pasien.
 * Tersedia: $title, $pasienList[], $error (string)
 */
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">
        <i class="bi bi-people me-2"></i><?= htmlspecialchars($title) ?>
    </h4>
    <a href="<?= BASE_URL ?>/pasien/create" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-person-plus me-1"></i>Input Data Pasien (Tanpa Pendaftaran)
    </a>
    <a href="<?= BASE_URL ?>/pendaftaran/create" class="btn btn-primary btn-sm ms-2">
        <i class="bi bi-clipboard2-plus me-1"></i>Daftarkan ke Poli
    </a>
</div>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger">
        <i class="bi bi-exclamation-triangle me-1"></i>
        <?= htmlspecialchars($error) ?>
    </div>
<?php endif; ?>

<?php if (!empty($_GET['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <i class="bi bi-check-circle me-1"></i>
        <?= htmlspecialchars($_GET['success']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if (!empty($_GET['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <i class="bi bi-exclamation-triangle me-1"></i>
        <?= htmlspecialchars($_GET['error']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Nama Pasien</th>
                    <th>Keluhan</th>
                    <th>Tgl Lahir</th>
                    <th>No. HP</th>
                    <th>Terdaftar</th>
                    <th class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($pasienList)): ?>
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                            Belum ada data pasien.
                            <a href="<?= BASE_URL ?>/pasien/create">Tambah sekarang →</a>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($pasienList as $p): ?>
                        <tr>
                            <td>
                                <code class="text-primary"><?= htmlspecialchars($p['id']) ?></code>
                            </td>
                            <td>
                                <strong><?= htmlspecialchars($p['nama']) ?></strong>
                            </td>
                            <td>
                                <span class="text-truncate d-inline-block" style="max-width:180px"
                                    title="<?= htmlspecialchars($p['keluhan']) ?>">
                                    <?= htmlspecialchars($p['keluhan']) ?>
                                </span>
                            </td>
                            <td>
                                <?php if (!empty($p['tanggal_lahir'])): ?>
                                    <?= date('d M Y', strtotime($p['tanggal_lahir'])) ?>
                                <?php else: ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($p['no_hp'] ?? '—') ?></td>
                            <td class="text-muted small">
                                <?= date('d M Y', strtotime($p['created_at'])) ?>
                            </td>
                            <td class="text-center">
                                <a href="<?= BASE_URL ?>/pasien/edit/<?= $p['id'] ?>"
                                    class="btn btn-sm btn-warning"
                                    title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form method="POST"
                                    action="<?= BASE_URL ?>/pasien/delete/<?= $p['id'] ?>"
                                    class="d-inline"
                                    onsubmit="return confirm('Hapus pasien <?= htmlspecialchars(addslashes($p['nama'])) ?>? Tindakan ini tidak bisa dibatalkan.')">
                                    <button type="submit" class="btn btn-sm btn-danger"
                                        title="Hapus">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php if (!empty($pasienList)): ?>
        <div class="card-footer text-muted small">
            Total: <?= count($pasienList) ?> pasien terdaftar
        </div>
    <?php endif; ?>
</div>