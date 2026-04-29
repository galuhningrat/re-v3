<div class="mb-4">
    <h4><i class="bi bi-arrow-repeat me-2"></i><?= $title ?></h4>
    <a href="<?= BASE_URL ?>/pendaftaran/show/<?= $data['id'] ?>"
        class="text-decoration-none text-muted">
        <i class="bi bi-arrow-left me-1"></i>Kembali ke detail
    </a>
</div>

<?php if (!empty($_GET['error'])): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($_GET['error']) ?></div>
<?php endif; ?>

<div class="card shadow-sm" style="max-width: 520px;">
    <div class="card-header fw-bold bg-warning">
        Update Status Pendaftaran #<?= $data['id'] ?>
    </div>
    <div class="card-body">
        <!-- Info ringkas -->
        <div class="mb-3 p-3 bg-light rounded small">
            <strong>Pasien:</strong> <?= htmlspecialchars($data['pasien_nama']) ?><br>
            <strong>Dokter:</strong> <?= htmlspecialchars($data['dokter_nama']) ?><br>
            <strong>Kamar :</strong> <?= htmlspecialchars($data['nomor_kamar']) ?>
            (<?= htmlspecialchars($data['kamar_tipe']) ?>)<br>
            <strong>Jadwal:</strong> <?= date('d M Y', strtotime($data['tanggal_janji'])) ?>
        </div>

        <form method="POST"
            action="<?= BASE_URL ?>/pendaftaran/update/<?= $data['id'] ?>">
            <div class="mb-3">
                <label class="form-label fw-semibold">Status <span class="text-danger">*</span></label>
                <select name="status" class="form-select" required>
                    <?php
                    $statuses = ['menunggu', 'aktif', 'selesai', 'batal'];
                    foreach ($statuses as $s):
                    ?>
                        <option value="<?= $s ?>"
                            <?= $data['status'] === $s ? 'selected' : '' ?>>
                            <?= ucfirst($s) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <small class="text-muted">
                    Status "Selesai" atau "Batal" akan otomatis membebaskan kamar.
                </small>
            </div>

            <div class="mb-4">
                <label class="form-label fw-semibold">Catatan</label>
                <textarea name="catatan" class="form-control" rows="3"
                    placeholder="Catatan perubahan status..."><?= htmlspecialchars($data['catatan'] ?? '') ?></textarea>
            </div>

            <button type="submit" class="btn btn-warning w-100">
                <i class="bi bi-save me-1"></i>Simpan Perubahan Status
            </button>
        </form>
    </div>
</div>