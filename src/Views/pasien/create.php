<?php

/**
 * View: pasien/create.php
 * Form tambah pasien baru.
 * Tersedia: $title, $newId, $errors[], $old[]
 */
?>

<div class="mb-4">
    <h4><i class="bi bi-person-plus me-2"></i><?= htmlspecialchars($title) ?></h4>
    <p class="text-muted small mb-0">
        Gunakan form ini untuk menginput data pasien <strong>tanpa langsung mendaftarkan ke poli</strong>
        (misalnya data pasien rujukan atau rekam medis lama).
        Untuk pendaftaran langsung, gunakan
        <a href="<?= BASE_URL ?>/pendaftaran/create">Buat Pendaftaran</a>.
    </p>
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
    <div class="card-header fw-semibold bg-primary text-white">
        <i class="bi bi-person-vcard me-2"></i>Data Pasien Baru
    </div>
    <div class="card-body">
        <form method="POST" action="<?= BASE_URL ?>/pasien/store" novalidate>

            <!-- ID (auto-generate, readonly) -->
            <div class="mb-3">
                <label class="form-label fw-semibold">ID Pasien</label>
                <div class="input-group">
                    <span class="input-group-text bg-light">
                        <i class="bi bi-hash"></i>
                    </span>
                    <input type="text" name="id" class="form-control bg-light"
                        value="<?= htmlspecialchars($old['id'] ?? $newId) ?>" readonly>
                </div>
                <small class="text-muted">Digenerate otomatis oleh sistem.</small>
            </div>

            <!-- Nama -->
            <div class="mb-3">
                <label class="form-label fw-semibold">
                    Nama Lengkap <span class="text-danger">*</span>
                </label>
                <input type="text" name="nama" class="form-control" required
                    value="<?= htmlspecialchars($old['nama'] ?? '') ?>"
                    placeholder="Nama lengkap pasien">
            </div>

            <!-- Tanggal Lahir — field yang sebelumnya kurang -->
            <div class="mb-3">
                <label class="form-label fw-semibold">Tanggal Lahir</label>
                <input type="date" name="tanggal_lahir" class="form-control"
                    value="<?= htmlspecialchars($old['tanggal_lahir'] ?? '') ?>"
                    max="<?= date('Y-m-d') ?>">
                <small class="text-muted">Opsional. Tidak boleh di masa depan.</small>
            </div>

            <!-- Keluhan -->
            <div class="mb-3">
                <label class="form-label fw-semibold">
                    Keluhan Utama <span class="text-danger">*</span>
                </label>
                <textarea name="keluhan" class="form-control" rows="3" required
                    placeholder="Deskripsikan keluhan pasien secara singkat"><?= htmlspecialchars($old['keluhan'] ?? '') ?></textarea>
            </div>

            <!-- Alamat -->
            <div class="mb-3">
                <label class="form-label fw-semibold">Alamat</label>
                <input type="text" name="alamat" class="form-control"
                    value="<?= htmlspecialchars($old['alamat'] ?? '') ?>"
                    placeholder="Alamat lengkap pasien (opsional)">
            </div>

            <!-- No HP -->
            <div class="mb-4">
                <label class="form-label fw-semibold">Nomor HP</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-phone"></i></span>
                    <input type="tel" name="no_hp" class="form-control"
                        value="<?= htmlspecialchars($old['no_hp'] ?? '') ?>"
                        placeholder="08xxxxxxxxxx">
                </div>
            </div>

            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save me-1"></i>Simpan Data Pasien
                </button>
                <a href="<?= BASE_URL ?>/pasien" class="btn btn-outline-secondary">
                    Batal
                </a>
            </div>

        </form>
    </div>
</div>