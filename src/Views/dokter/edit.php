<div class="mb-4">
    <h4><i class="bi bi-pencil-square me-2"></i><?= $title ?></h4>
    <a href="<?= BASE_URL ?>/dokter" class="text-decoration-none text-muted">
        <i class="bi bi-arrow-left me-1"></i>Kembali ke daftar
    </a>
</div>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?= $error ?></div>
<?php endif; ?>

<div class="card shadow-sm" style="max-width: 640px;">
    <div class="card-body">
        <form method="POST" action="<?= BASE_URL ?>/dokter/update/<?= htmlspecialchars($dokter['id']) ?>">
            <div class="mb-3">
                <label class="form-label fw-semibold">ID Dokter</label>
                <input type="text" class="form-control"
                    value="<?= htmlspecialchars($dokter['id']) ?>" disabled>
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">Nama Lengkap <span class="text-danger">*</span></label>
                <input type="text" name="nama" class="form-control" required
                    value="<?= htmlspecialchars($dokter['nama']) ?>">
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">Spesialisasi <span class="text-danger">*</span></label>
                <input type="text" name="spesialis" class="form-control" required
                    value="<?= htmlspecialchars($dokter['spesialis']) ?>">
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">No. HP</label>
                <input type="text" name="no_hp" class="form-control"
                    value="<?= htmlspecialchars($dokter['no_hp'] ?? '') ?>">
            </div>
            <div class="mb-4">
                <label class="form-label fw-semibold">Email</label>
                <input type="email" name="email" class="form-control"
                    value="<?= htmlspecialchars($dokter['email'] ?? '') ?>">
            </div>
            <button type="submit" class="btn btn-warning w-100">
                <i class="bi bi-save me-1"></i>Perbarui Data Dokter
            </button>
        </form>
    </div>
</div>