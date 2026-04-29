<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="bi bi-clipboard2-pulse me-2"></i><?= $title ?></h4>
    <a href="<?= BASE_URL ?>/pendaftaran/create" class="btn btn-primary">
        <i class="bi bi-plus-circle me-1"></i>Buat Pendaftaran
    </a>
</div>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>
<?php if (!empty($_GET['success'])): ?>
    <div class="alert alert-success"><?= htmlspecialchars($_GET['success']) ?></div>
<?php endif; ?>
<?php if (!empty($_GET['error'])): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($_GET['error']) ?></div>
<?php endif; ?>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="table-dark">
                <tr>
                    <th>#</th>
                    <th>Pasien</th>
                    <th>Dokter</th>
                    <th>Kamar</th>
                    <th>Tanggal Janji</th>
                    <th>Status</th>
                    <th class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($list)): ?>
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">
                            Belum ada data pendaftaran.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php
                    $badgeMap = [
                        'menunggu' => 'warning text-dark',
                        'aktif'    => 'primary',
                        'selesai'  => 'success',
                        'batal'    => 'danger',
                    ];
                    ?>
                    <?php foreach ($list as $row): ?>
                        <tr>
                            <td><code><?= $row['id'] ?></code></td>
                            <td>
                                <strong><?= htmlspecialchars($row['pasien_nama']) ?></strong>
                                <br><small class="text-muted"><?= htmlspecialchars($row['pasien_id']) ?></small>
                            </td>
                            <td>
                                <?= htmlspecialchars($row['dokter_nama']) ?>
                                <br><small class="text-muted"><?= htmlspecialchars($row['dokter_spesialis']) ?></small>
                            </td>
                            <td>
                                <?= htmlspecialchars($row['nomor_kamar']) ?>
                                <br><small class="text-muted"><?= htmlspecialchars($row['kamar_tipe']) ?></small>
                            </td>
                            <td><?= date('d M Y', strtotime($row['tanggal_janji'])) ?></td>
                            <td>
                                <span class="badge bg-<?= $badgeMap[$row['status']] ?? 'secondary' ?>">
                                    <?= ucfirst($row['status']) ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <a href="<?= BASE_URL ?>/pendaftaran/show/<?= $row['id'] ?>"
                                    class="btn btn-sm btn-info text-white" title="Detail">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="<?= BASE_URL ?>/pendaftaran/edit/<?= $row['id'] ?>"
                                    class="btn btn-sm btn-warning" title="Edit Status">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form method="POST"
                                    action="<?= BASE_URL ?>/pendaftaran/delete/<?= $row['id'] ?>"
                                    class="d-inline"
                                    onsubmit="return confirm('Hapus pendaftaran ini? Kamar akan dibebaskan.')">
                                    <button type="submit" class="btn btn-sm btn-danger" title="Hapus">
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