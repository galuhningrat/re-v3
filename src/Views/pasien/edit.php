<?php

/**
 * View: pasien/edit.php
 * Form edit data pasien.
 * Tersedia: $title, $pasien[], $errors[]
 */
?>

<div class="mb-4">
    <h4><i class="bi bi-pencil-square me-2"></i><?= htmlspecialchars($title) ?></h4>
    <a href="<?= BASE_URL ?>/pasien" class="text-decoration-none text-muted">
        <i class="bi bi-arrow-left me-1"></i>Kembali ke daftar
    </a>
</div>

<?php if (!empty($errors)): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <strong><i class="bi bi-exclamation-triangle me-1"></i>Mohon perbaiki kesalahan berikut:</strong>
        <ul class="mb-0 mt-1">
            <?php foreach ($errors as $err): ?>
                <li><?= htmlspecialchars($err) ?></li>
            <?php endforeach; ?>
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="card shadow-sm" style="max-width: 640px;">
    <div class="card-header fw-semibold bg-warning">
        <i class="bi bi-person-vcard me-2"></i>
        Edit Pasien: <?= htmlspecialchars($pasien['nama'] ?? '') ?>
    </div>
    <div class="card-body">
        <form method="POST"
            action="<?= BASE_URL ?>/pasien/update/<?= htmlspecialchars($pasien['id']) ?>"
            novalidate>

            <!-- ID (read-only, tidak bisa diubah) -->
            <div class="mb-3">
                <label class="form-label fw-semibold">ID Pasien</label>
                <div class="input-group">
                    <span class="input-group-text bg-light"><i class="bi bi-hash"></i></span>
                    <input type="text" class="form-control bg-light"
                        value="<?= htmlspecialchars($pasien['id']) ?>" disabled>
                </div>
                <small class="text-muted">ID tidak dapat diubah.</small>
            </div>

            <!-- Nama -->
            <div class="mb-3">
                <label class="form-label fw-semibold">
                    Nama Lengkap <span class="text-danger">*</span>
                </label>
                <input type="text" name="nama" class="form-control" required
                    value="<?= htmlspecialchars($pasien['nama'] ?? '') ?>">
            </div>

            <!-- Tanggal Lahir -->
            <div class="mb-3">
                <label class="form-label fw-semibold">Tanggal Lahir</label>
                <input type="date" name="tanggal_lahir" class="form-control"
                    value="<?= htmlspecialchars($pasien['tanggal_lahir'] ?? '') ?>"
                    max="<?= date('Y-m-d') ?>">
                <small class="text-muted">Opsional.</small>
            </div>

            <!-- Keluhan -->
            <div class="mb-3">
                <label class="form-label fw-semibold">
                    Keluhan Utama <span class="text-danger">*</span>
                </label>
                <textarea name="keluhan" class="form-control" rows="3" required><?= htmlspecialchars($pasien['keluhan'] ?? '') ?></textarea>
            </div>

            <!-- Alamat -->
            <div class="mb-3">
                <label class="form-label fw-semibold">Alamat</label>
                <input type="text" name="alamat" class="form-control"
                    value="<?= htmlspecialchars($pasien['alamat'] ?? '') ?>">
            </div>

            <!-- No HP -->
            <div class="mb-4">
                <label class="form-label fw-semibold">Nomor HP</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-phone"></i></span>
                    <input type="tel" name="no_hp" class="form-control"
                        value="<?= htmlspecialchars($pasien['no_hp'] ?? '') ?>">
                </div>
            </div>

            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-warning">
                    <i class="bi bi-save me-1"></i>Perbarui Data Pasien
                </button>
                <a href="<?= BASE_URL ?>/pasien" class="btn btn-outline-secondary">
                    Batal
                </a>
            </div>

        </form>
    </div>
</div>