<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="bi bi-people-fill me-2"></i><?= $title ?></h4>
</div>

<?php if (!empty($_GET['success'])): ?>
    <div class="alert alert-success"><?= htmlspecialchars($_GET['success']) ?></div>
<?php endif; ?>
<?php if (!empty($_GET['error'])): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($_GET['error']) ?></div>
<?php endif; ?>

<!-- Form Tambah User -->
<div class="card shadow-sm mb-4" style="max-width: 640px;">
    <div class="card-header fw-semibold bg-primary text-white">
        <i class="bi bi-person-plus me-2"></i>Tambah User Baru
    </div>
    <div class="card-body">
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul class="mb-0">
                    <?php foreach ($errors as $e): ?>
                        <li><?= htmlspecialchars($e) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        <form method="POST" action="<?= BASE_URL ?>/users/store">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Nama Lengkap *</label>
                    <input type="text" name="nama" class="form-control" required
                        value="<?= htmlspecialchars($old['nama'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Username *</label>
                    <input type="text" name="username" class="form-control" required
                        value="<?= htmlspecialchars($old['username'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Password *</label>
                    <input type="password" name="password" class="form-control" required
                        placeholder="Min. 6 karakter">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Role *</label>
                    <select name="role" class="form-select" id="roleSelect"
                        onchange="toggleDokterField()">
                        <option value="dokter" <?= ($old['role'] ?? '') === 'dokter' ? 'selected' : '' ?>>Dokter</option>
                        <option value="admin" <?= ($old['role'] ?? '') === 'admin'  ? 'selected' : '' ?>>Admin</option>
                    </select>
                </div>
                <div class="col-md-3" id="dokterField">
                    <label class="form-label fw-semibold">Link Dokter</label>
                    <select name="dokter_id" class="form-select">
                        <option value="">— Pilih —</option>
                        <?php foreach ($dokterList as $d): ?>
                            <option value="<?= $d['id'] ?>"
                                <?= ($old['dokter_id'] ?? '') === $d['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($d['nama']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <button type="submit" class="btn btn-primary mt-3">
                <i class="bi bi-save me-1"></i>Simpan User
            </button>
        </form>
    </div>
</div>

<!-- Tabel User -->
<div class="card shadow-sm">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="table-dark">
                <tr>
                    <th>Nama</th>
                    <th>Username</th>
                    <th>Role</th>
                    <th>Linked Dokter</th>
                    <th>Status</th>
                    <th class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($userList as $u): ?>
                    <tr>
                        <td><?= htmlspecialchars($u['nama']) ?></td>
                        <td><code><?= htmlspecialchars($u['username']) ?></code></td>
                        <td>
                            <span class="badge <?= $u['role'] === 'admin' ? 'bg-danger' : 'bg-info text-dark' ?>">
                                <?= ucfirst($u['role']) ?>
                            </span>
                        </td>
                        <td><?= htmlspecialchars($u['dokter_nama'] ?? '—') ?></td>
                        <td>
                            <!-- PERBAIKAN: pakai is_aktif bukan is_active -->
                            <span class="badge <?= $u['is_aktif'] ? 'bg-success' : 'bg-secondary' ?>">
                                <?= $u['is_aktif'] ? 'Aktif' : 'Nonaktif' ?>
                            </span>
                        </td>
                        <td class="text-center">
                            <form method="POST"
                                action="<?= BASE_URL ?>/users/toggle/<?= $u['id'] ?>"
                                class="d-inline">
                                <button type="submit"
                                    class="btn btn-sm <?= $u['is_aktif'] ? 'btn-outline-warning' : 'btn-outline-success' ?>">
                                    <i class="bi <?= $u['is_aktif'] ? 'bi-pause-fill' : 'bi-play-fill' ?>"></i>
                                    <?= $u['is_aktif'] ? 'Nonaktifkan' : 'Aktifkan' ?>
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    function toggleDokterField() {
        const role = document.getElementById('roleSelect').value;
        const field = document.getElementById('dokterField');
        field.style.display = role === 'dokter' ? 'block' : 'none';
    }
    toggleDokterField();
</script>