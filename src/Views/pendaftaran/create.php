<?php

/**
 * View: pendaftaran/create.php (v2)
 * Alur: Input NIK → cek pasien lama/baru → pilih poli → tampil dokter on-duty → submit
* @var string $title
 * @var array $poliList
 * @var array $kamarList
 * @var array $errors
 * @var array $old
 */
?>

<div class="mb-4">
    <h4><i class="bi bi-clipboard2-plus me-2"></i><?= htmlspecialchars($title) ?></h4>
    <a href="<?= BASE_URL ?>/pendaftaran" class="text-decoration-none text-muted">
        <i class="bi bi-arrow-left me-1"></i>Kembali ke daftar
    </a>
</div>

<?php if (!empty($errors)): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <strong><i class="bi bi-exclamation-triangle me-1"></i>Perbaiki kesalahan:</strong>
        <ul class="mb-0 mt-1">
            <?php foreach ($errors as $e): ?>
                <li><?= htmlspecialchars($e) ?></li>
            <?php endforeach; ?>
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<form method="POST" action="<?= BASE_URL ?>/pendaftaran/store" id="formPendaftaran" novalidate>
    <input type="hidden" name="is_new_pasien" id="isNewPasien" value="0">
    <input type="hidden" name="pasien_id" id="pasienId" value="<?= htmlspecialchars($old['pasien_id'] ?? '') ?>">

    <div class="row g-4">

        <!-- ═══ KOLOM KIRI: Data Pasien ═══ -->
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header fw-bold bg-primary text-white">
                    <i class="bi bi-person-vcard me-2"></i>Step 1 — Identifikasi Pasien
                </div>
                <div class="card-body">

                    <!-- NIK Input -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            NIK (Nomor Induk Kependudukan) <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <input type="text" id="inputNik" name="nik" class="form-control"
                                placeholder="16 digit NIK" maxlength="16"
                                value="<?= htmlspecialchars($old['nik'] ?? '') ?>"
                                pattern="\d{16}" required>
                            <button type="button" class="btn btn-outline-primary" id="btnCekNik">
                                <i class="bi bi-search"></i> Cek NIK
                            </button>
                        </div>
                        <div id="nikStatus" class="mt-2"></div>
                    </div>

                    <!-- Informasi Pasien (muncul setelah NIK dicek) -->
                    <div id="sectionPasienLama" class="d-none">
                        <div class="alert alert-success py-2">
                            <i class="bi bi-check-circle me-1"></i>
                            <strong>Pasien Lama Ditemukan</strong>
                        </div>
                        <table class="table table-sm table-borderless mb-0">
                            <tr>
                                <td class="text-muted" width="35%">ID</td>
                                <td><code id="infoPasienId"></code></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Nama</td>
                                <td><strong id="infoPasienNama"></strong></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Keluhan</td>
                                <td id="infoPasienKeluhan"></td>
                            </tr>
                            <tr>
                                <td class="text-muted">No. HP</td>
                                <td id="infoPasienHp"></td>
                            </tr>
                        </table>
                    </div>

                    <!-- Form Pasien Baru (muncul jika NIK belum terdaftar) -->
                    <div id="sectionPasienBaru" class="d-none">
                        <div class="alert alert-warning py-2">
                            <i class="bi bi-person-plus me-1"></i>
                            <strong>Pasien Baru</strong> — Silakan lengkapi data berikut.
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" name="nama" class="form-control"
                                value="<?= htmlspecialchars($old['nama'] ?? '') ?>"
                                placeholder="Nama lengkap sesuai KTP">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Tanggal Lahir</label>
                            <input type="date" name="tanggal_lahir" class="form-control"
                                value="<?= htmlspecialchars($old['tanggal_lahir'] ?? '') ?>"
                                max="<?= date('Y-m-d') ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Keluhan <span class="text-danger">*</span></label>
                            <textarea name="keluhan" class="form-control" rows="2"
                                placeholder="Keluhan utama pasien"><?= htmlspecialchars($old['keluhan'] ?? '') ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Alamat</label>
                            <input type="text" name="alamat" class="form-control"
                                value="<?= htmlspecialchars($old['alamat'] ?? '') ?>">
                        </div>
                        <div class="mb-0">
                            <label class="form-label fw-semibold">No. HP</label>
                            <input type="tel" name="no_hp" class="form-control"
                                value="<?= htmlspecialchars($old['no_hp'] ?? '') ?>"
                                placeholder="08xxxxxxxxxx">
                        </div>
                    </div>

                    <!-- Placeholder sebelum NIK dicek -->
                    <div id="sectionNikPlaceholder" class="text-center text-muted py-4">
                        <i class="bi bi-card-text fs-3 d-block mb-2"></i>
                        Masukkan NIK dan klik <strong>Cek NIK</strong> untuk melanjutkan.
                    </div>

                </div>
            </div>
        </div>

        <!-- ═══ KOLOM KANAN: Poli, Dokter, Kamar ═══ -->
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header fw-bold" style="background:#198754; color:#fff">
                    <i class="bi bi-hospital me-2"></i>Step 2 — Pilih Poli & Jadwal
                </div>
                <div class="card-body">

                    <!-- Poli -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Poli Tujuan <span class="text-danger">*</span></label>
                        <select name="poli_id" id="selectPoli" class="form-select" required>
                            <option value="">— Pilih Poli —</option>
                            <?php foreach ($poliList as $p): ?>
                                <option value="<?= $p['id'] ?>"
                                    <?= ($old['poli_id'] ?? '') == $p['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($p['kode'] . ' — ' . $p['nama']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Dokter (dynamic berdasarkan poli & hari ini) -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            Dokter On-Duty <span class="text-danger">*</span>
                            <span class="badge bg-info text-dark ms-1 small" id="hariIni">
                                <?= \App\Models\Poli::hariIni() ?>
                            </span>
                        </label>
                        <select name="dokter_id" id="selectDokter" class="form-select" required disabled>
                            <option value="">— Pilih poli dulu —</option>
                        </select>
                        <div id="kuotaInfo" class="mt-1 small text-muted"></div>
                    </div>

                    <!-- Kamar -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Kamar Rawat Inap <span class="text-danger">*</span></label>
                        <select name="kamar_id" class="form-select" required>
                            <option value="">— Pilih Kamar —</option>
                            <?php foreach ($kamarList as $k): ?>
                                <option value="<?= $k['id'] ?>"
                                    <?= ($old['kamar_id'] ?? '') == $k['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars(
                                        $k['nomor_kamar'] . ' (' . $k['tipe'] . ') — Rp '
                                            . number_format($k['harga_per_malam'], 0, ',', '.') . '/malam'
                                    ) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (empty($kamarList)): ?>
                            <small class="text-danger">Semua kamar sedang terisi.</small>
                        <?php endif; ?>
                    </div>

                    <!-- Tanggal Janji -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Tanggal Janji <span class="text-danger">*</span></label>
                        <input type="date" name="tanggal_janji" class="form-control" required
                            min="<?= date('Y-m-d') ?>"
                            value="<?= htmlspecialchars($old['tanggal_janji'] ?? date('Y-m-d')) ?>">
                    </div>

                    <!-- Catatan -->
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Catatan Tambahan</label>
                        <textarea name="catatan" class="form-control" rows="2"
                            placeholder="Opsional"><?= htmlspecialchars($old['catatan'] ?? '') ?></textarea>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary" id="btnSubmit" disabled>
                            <i class="bi bi-save me-1"></i>Daftarkan & Generate Antrean
                        </button>
                        <a href="<?= BASE_URL ?>/pendaftaran" class="btn btn-outline-secondary">Batal</a>
                    </div>

                </div>
            </div>
        </div>

    </div><!-- /row -->
</form>

<script>
    // ─── AJAX: Cek NIK ────────────────────────────────────────────
    document.getElementById('btnCekNik').addEventListener('click', function() {
        const nik = document.getElementById('inputNik').value.trim();
        const status = document.getElementById('nikStatus');

        if (nik.length !== 16 || isNaN(nik)) {
            status.innerHTML = '<div class="alert alert-warning py-2">NIK harus tepat 16 digit angka.</div>';
            return;
        }

        status.innerHTML = '<div class="text-muted small"><span class="spinner-border spinner-border-sm"></span> Mengecek NIK...</div>';

        fetch('<?= BASE_URL ?>/pendaftaran/cek-nik', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: 'nik=' + encodeURIComponent(nik)
            })
            .then(r => r.json())
            .then(data => {
                status.innerHTML = '';
                document.getElementById('sectionNikPlaceholder').classList.add('d-none');

                if (data.found) {
                    // Pasien lama — isi info
                    document.getElementById('sectionPasienLama').classList.remove('d-none');
                    document.getElementById('sectionPasienBaru').classList.add('d-none');
                    document.getElementById('isNewPasien').value = '0';
                    document.getElementById('pasienId').value = data.pasien.id;
                    document.getElementById('infoPasienId').textContent = data.pasien.id;
                    document.getElementById('infoPasienNama').textContent = data.pasien.nama;
                    document.getElementById('infoPasienKeluhan').textContent = data.pasien.keluhan;
                    document.getElementById('infoPasienHp').textContent = data.pasien.no_hp || '—';
                } else {
                    // Pasien baru — tampilkan form input
                    document.getElementById('sectionPasienBaru').classList.remove('d-none');
                    document.getElementById('sectionPasienLama').classList.add('d-none');
                    document.getElementById('isNewPasien').value = '1';
                    document.getElementById('pasienId').value = '';
                }
                enableSubmitIfReady();
            })
            .catch(() => {
                status.innerHTML = '<div class="alert alert-danger py-2">Gagal menghubungi server.</div>';
            });
    });

    // ─── AJAX: Load dokter berdasarkan poli ──────────────────────
    document.getElementById('selectPoli').addEventListener('change', function() {
        const poliId = this.value;
        const selDok = document.getElementById('selectDokter');
        const kuota = document.getElementById('kuotaInfo');

        if (!poliId) {
            selDok.innerHTML = '<option>— Pilih poli dulu —</option>';
            selDok.disabled = true;
            kuota.innerHTML = '';
            return;
        }

        selDok.innerHTML = '<option>Memuat dokter...</option>';
        selDok.disabled = true;

        fetch('<?= BASE_URL ?>/pendaftaran/get-dokter', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: 'poli_id=' + encodeURIComponent(poliId)
            })
            .then(r => r.json())
            .then(r => {
                if (!r.ok) throw new Error('Server error: ' + r.status);
                return r.json();
            })
            .then(list => {
                selectDokter.innerHTML = '<option value="">— Pilih Dokter —</option>';

                if (Array.isArray(list) && list.length === 0) {
                    // Seharusnya tidak terjadi lagi setelah fallback di backend
                    selectDokter.innerHTML = '<option value="">Tidak ada dokter tersedia</option>';
                    kuotaInfo.innerHTML =
                        '<span class="text-danger">' +
                        '<i class="bi bi-exclamation-circle me-1"></i>' +
                        'Belum ada dokter terdaftar di sistem.' +
                        '</span>';
                    return;
                }

                list.forEach(function(d) {
                    const kuota = parseInt(d.kuota) || 999;
                    const terpakai = parseInt(d.terpakai) || 0;
                    const sisa = kuota - terpakai;

                    // Susun label dengan info jadwal jika ada
                    let label = d.nama + ' (' + d.spesialis + ')';
                    if (d.jam_mulai && d.jam_selesai && d.jam_mulai !== '08:00:00' || kuota < 999) {
                        label += '  —  ' + d.jam_mulai.slice(0, 5) + '–' + d.jam_selesai.slice(0, 5);
                        label += '  |  Sisa: ' + sisa + '/' + kuota;
                    }

                    const opt = new Option(label, d.id);
                    // Disable hanya jika punya kuota terbatas dan sudah penuh
                    if (kuota < 999 && sisa <= 0) {
                        opt.disabled = true;
                        opt.text += '  ⛔ PENUH';
                    }
                    selectDokter.add(opt);
                });

                selectDokter.disabled = false;

                // Tampilkan info fallback level jika bukan jadwal hari ini
                const infoText = list[0] && list[0].keterangan ?
                    list[0].keterangan :
                    list.length + ' dokter tersedia';

                kuotaInfo.innerHTML =
                    '<span class="text-muted small">' +
                    '<i class="bi bi-info-circle me-1"></i>' +
                    infoText +
                    '</span>';
            })
            .catch(() => {
                selDok.innerHTML = '<option value="">Gagal memuat dokter</option>';
            });
    });

    // ─── Enable submit button ─────────────────────────────────────
    function enableSubmitIfReady() {
        const nikOk = document.getElementById('inputNik').value.length === 16;
        const dokOk = document.getElementById('selectDokter').value !== '';
        document.getElementById('btnSubmit').disabled = !(nikOk);
    }

    document.getElementById('selectDokter').addEventListener('change', enableSubmitIfReady);
    document.getElementById('inputNik').addEventListener('input', () => {
        if (document.getElementById('inputNik').value.length !== 16) {
            document.getElementById('sectionNikPlaceholder').classList.remove('d-none');
            document.getElementById('sectionPasienLama').classList.add('d-none');
            document.getElementById('sectionPasienBaru').classList.add('d-none');
        }
    });
</script>