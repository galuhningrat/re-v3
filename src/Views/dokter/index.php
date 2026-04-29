<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="bi bi-person-badge me-2"></i><?= $title ?></h4>
    <a href="<?= BASE_URL ?>/dokter/create" class="btn btn-success">
        <i class="bi bi-plus-circle me-1"></i>Tambah Dokter
    </a>
</div>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?= $error ?></div>
<?php endif; ?>
<?php if (!empty($_GET['success'])): ?>
    <div class="alert alert-success"><?= htmlspecialchars($_GET['success']) ?></div>
<?php endif; ?>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Nama</th>
                    <th>Spesialisasi</th>
                    <th>No. HP</th>
                    <th>Email</th>
                    <th class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($dokterList)): ?>
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">Belum ada data dokter.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($dokterList as $d): ?>
                        <tr>
                            <td><code><?= htmlspecialchars($d['id']) ?></code></td>
                            <td><?= htmlspecialchars($d['nama']) ?></td>
                            <td><span class="badge bg-info text-dark"><?= htmlspecialchars($d['spesialis']) ?></span></td>
                            <td><?= htmlspecialchars($d['no_hp'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($d['email'] ?? '-') ?></td>
                            <td class="text-center">
                                <a href="<?= BASE_URL ?>/dokter/edit/<?= $d['id'] ?>"
                                    class="btn btn-sm btn-warning">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form method="POST"
                                    action="<?= BASE_URL ?>/dokter/delete/<?= $d['id'] ?>"
                                    class="d-inline"
                                    onsubmit="return confirm('Hapus dokter ini?')">
                                    <button type="submit" class="btn btn-sm btn-danger">
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
</div>