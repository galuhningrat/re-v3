<?php

/**
 * View: pendaftaran/show.php — v3
 * Fix: print ticket kosong karena CSS @media print konflik dengan Bootstrap sidebar
 */
$badgeMap = [
    'menunggu' => 'warning text-dark',
    'aktif'    => 'primary',
    'selesai'  => 'success',
    'batal'    => 'danger',
];
$barcodeVal = htmlspecialchars($barcodeValue ?? ('REG-' . str_pad($data['id'], 8, '0', STR_PAD_LEFT)));
?>

<!-- ── Header aksi (layar saja) ──────────────────────────── -->
<div class="d-flex justify-content-between align-items-start mb-4 d-print-none">
    <div>
        <h4 class="mb-1">
            <i class="bi bi-file-earmark-text me-2"></i><?= htmlspecialchars($title) ?>
        </h4>
        <a href="<?= BASE_URL ?>/pendaftaran" class="text-decoration-none text-muted small">
            <i class="bi bi-arrow-left me-1"></i>Kembali ke daftar
        </a>
    </div>
    <div class="d-flex gap-2">
        <?php if (!in_array($data['status'], ['selesai', 'batal'])): ?>
            <a href="<?= BASE_URL ?>/pendaftaran/edit/<?= $data['id'] ?>"
                class="btn btn-warning btn-sm">
                <i class="bi bi-pencil me-1"></i>Update Status
            </a>
        <?php endif; ?>
        <button type="button" class="btn btn-primary btn-sm" id="btnCetak">
            <i class="bi bi-printer me-1"></i>Cetak Tiket
        </button>
        <form method="POST"
            action="<?= BASE_URL ?>/pendaftaran/delete/<?= $data['id'] ?>"
            class="d-inline"
            onsubmit="return confirm('Hapus pendaftaran ini?')">
            <button type="submit" class="btn btn-outline-danger btn-sm">
                <i class="bi bi-trash me-1"></i>Hapus
            </button>
        </form>
    </div>
</div>

<?php if (!empty($_GET['success'])): ?>
    <div class="alert alert-success d-print-none">
        <i class="bi bi-check-circle me-1"></i>
        <?= htmlspecialchars($_GET['success']) ?>
    </div>
<?php endif; ?>

<!-- ── Detail cards (layar saja) ─────────────────────────── -->
<div class="row g-4 d-print-none">
    <div class="col-md-5">
        <div class="card shadow-sm border-0">
            <div class="card-header fw-bold text-white" style="background:#1a2e4a;">
                <i class="bi bi-receipt me-2"></i>Ringkasan Pendaftaran
            </div>
            <div class="card-body">
                <dl class="row small mb-0">
                    <dt class="col-5 text-muted">No. Registrasi</dt>
                    <dd class="col-7"><code class="text-primary fw-bold"><?= $barcodeVal ?></code></dd>
                    <dt class="col-5 text-muted">Status</dt>
                    <dd class="col-7">
                        <span class="badge bg-<?= $badgeMap[$data['status']] ?? 'secondary' ?>">
                            <?= ucfirst($data['status']) ?>
                        </span>
                    </dd>
                    <dt class="col-5 text-muted">No. Antrean</dt>
                    <dd class="col-7">
                        <?php if (!empty($data['nomor_antrean'])): ?>
                            <span class="badge bg-dark fs-6 px-3"><?= htmlspecialchars($data['nomor_antrean']) ?></span>
                        <?php else: ?>
                            <span class="text-muted">—</span>
                        <?php endif; ?>
                    </dd>
                    <dt class="col-5 text-muted">Poli</dt>
                    <dd class="col-7"><?= htmlspecialchars($data['poli_nama'] ?? '—') ?></dd>
                    <dt class="col-5 text-muted">Tgl Janji</dt>
                    <dd class="col-7"><?= date('d M Y', strtotime($data['tanggal_janji'])) ?></dd>
                    <dt class="col-5 text-muted">Tgl Daftar</dt>
                    <dd class="col-7 text-muted"><?= date('d M Y, H:i', strtotime($data['created_at'])) ?></dd>
                    <?php if (!empty($data['catatan'])): ?>
                        <dt class="col-5 text-muted">Catatan</dt>
                        <dd class="col-7"><?= htmlspecialchars($data['catatan']) ?></dd>
                    <?php endif; ?>
                </dl>
            </div>
        </div>
    </div>
    <div class="col-md-7">
        <div class="card shadow-sm border-0 mb-3">
            <div class="card-header fw-semibold bg-light">
                <i class="bi bi-person-fill me-2 text-primary"></i>Data Pasien
            </div>
            <div class="card-body">
                <dl class="row small mb-0">
                    <dt class="col-4 text-muted">ID</dt>
                    <dd class="col-8"><code><?= htmlspecialchars($data['pasien_id']) ?></code></dd>
                    <dt class="col-4 text-muted">NIK</dt>
                    <dd class="col-8 font-monospace"><?= htmlspecialchars($data['pasien_nik'] ?? '—') ?></dd>
                    <dt class="col-4 text-muted">Nama</dt>
                    <dd class="col-8 fw-semibold"><?= htmlspecialchars($data['pasien_nama']) ?></dd>
                    <dt class="col-4 text-muted">Keluhan</dt>
                    <dd class="col-8"><?= htmlspecialchars($data['pasien_keluhan'] ?? '—') ?></dd>
                    <dt class="col-4 text-muted">No. HP</dt>
                    <dd class="col-8"><?= htmlspecialchars($data['pasien_hp'] ?? '—') ?></dd>
                </dl>
            </div>
        </div>
        <div class="card shadow-sm border-0 mb-3">
            <div class="card-header fw-semibold bg-light">
                <i class="bi bi-person-badge-fill me-2 text-success"></i>Data Dokter
            </div>
            <div class="card-body">
                <dl class="row small mb-0">
                    <dt class="col-4 text-muted">Nama</dt>
                    <dd class="col-8 fw-semibold"><?= htmlspecialchars($data['dokter_nama']) ?></dd>
                    <dt class="col-4 text-muted">Spesialis</dt>
                    <dd class="col-8">
                        <span class="badge bg-info text-dark"><?= htmlspecialchars($data['dokter_spesialis']) ?></span>
                    </dd>
                </dl>
            </div>
        </div>
        <?php if (!empty($data['kamar_id'])): ?>
            <div class="card shadow-sm border-0">
                <div class="card-header fw-semibold bg-light">
                    <i class="bi bi-door-open-fill me-2 text-warning"></i>Data Kamar
                </div>
                <div class="card-body">
                    <dl class="row small mb-0">
                        <dt class="col-4 text-muted">Nomor</dt>
                        <dd class="col-8 fw-semibold"><?= htmlspecialchars($data['nomor_kamar']) ?></dd>
                        <dt class="col-4 text-muted">Tipe</dt>
                        <dd class="col-8"><?= htmlspecialchars($data['kamar_tipe']) ?></dd>
                        <dt class="col-4 text-muted">Tarif/Malam</dt>
                        <dd class="col-8"><strong>Rp <?= number_format($data['harga_per_malam'] ?? 0, 0, ',', '.') ?></strong></dd>
                    </dl>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- ════════════════════════════════════════════════════════════
     TIKET CETAK
     Strategi fix: tiket di-render sebagai overlay position:fixed
     saat mode print, bukan bergantung pada hide/show Bootstrap.
     JsBarcode diload synchronous sebelum print dipanggil.
════════════════════════════════════════════════════════════ -->
<div id="ticketOverlay">
    <div id="printTicket">

        <!-- Header -->
        <div class="tk-header">
            <div class="tk-rs-label">RUMAH SAKIT</div>
            <div class="tk-rs-name"><?= htmlspecialchars(APP_NAME) ?></div>
            <div class="tk-rs-sub">TIKET PENDAFTARAN PASIEN</div>
        </div>

        <!-- Nomor antrean besar -->
        <?php if (!empty($data['nomor_antrean'])): ?>
            <div class="tk-queue">
                <div class="tk-queue-label">Nomor Antrean</div>
                <div class="tk-queue-number"><?= htmlspecialchars($data['nomor_antrean']) ?></div>
                <div class="tk-queue-poli"><?= htmlspecialchars($data['poli_nama'] ?? '') ?></div>
            </div>
        <?php endif; ?>

        <!-- Detail pasien -->
        <div class="tk-body">
            <table class="tk-table">
                <tr>
                    <td class="tk-label">Pasien</td>
                    <td class="tk-value fw-semibold"><?= htmlspecialchars($data['pasien_nama']) ?></td>
                </tr>
                <?php if (!empty($data['pasien_nik'])): ?>
                    <tr>
                        <td class="tk-label">NIK</td>
                        <td class="tk-value tk-mono"><?= htmlspecialchars($data['pasien_nik']) ?></td>
                    </tr>
                <?php endif; ?>
                <tr>
                    <td class="tk-label">Dokter</td>
                    <td class="tk-value"><?= htmlspecialchars($data['dokter_nama']) ?></td>
                </tr>
                <tr>
                    <td class="tk-label">Spesialis</td>
                    <td class="tk-value"><?= htmlspecialchars($data['dokter_spesialis']) ?></td>
                </tr>
                <tr>
                    <td class="tk-label">Poli</td>
                    <td class="tk-value"><?= htmlspecialchars($data['poli_nama'] ?? '—') ?></td>
                </tr>
                <tr>
                    <td class="tk-label">Tgl Janji</td>
                    <td class="tk-value"><?= date('d M Y', strtotime($data['tanggal_janji'])) ?></td>
                </tr>
                <?php if (!empty($data['nomor_kamar'])): ?>
                    <tr>
                        <td class="tk-label">Kamar</td>
                        <td class="tk-value"><?= htmlspecialchars($data['nomor_kamar']) ?> (<?= htmlspecialchars($data['kamar_tipe']) ?>)</td>
                    </tr>
                <?php endif; ?>
            </table>
        </div>

        <!-- Barcode section -->
        <div class="tk-barcode">
            <div class="tk-barcode-label">No. Registrasi</div>
            <!-- JsBarcode render ke sini -->
            <svg id="barcodeSvg"></svg>
            <div class="tk-barcode-text"><?= $barcodeVal ?></div>
        </div>

        <!-- Footer -->
        <div class="tk-footer">
            <div>Dicetak: <?= date('d M Y, H:i') ?> WIB</div>
            <div>Harap tunjukkan tiket ini kepada petugas.</div>
        </div>

    </div><!-- /#printTicket -->
</div><!-- /#ticketOverlay -->

<!-- JsBarcode CDN -->
<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js"></script>

<script>
    (function() {
        'use strict';

        const BARCODE_VALUE = '<?= addslashes($barcodeVal) ?>';
        const overlay = document.getElementById('ticketOverlay');
        const btnCetak = document.getElementById('btnCetak');

        // Render barcode sekali saat halaman dimuat
        // Tidak lazy — agar saat print sudah siap
        function renderBarcode() {
            try {
                JsBarcode('#barcodeSvg', BARCODE_VALUE, {
                    format: 'CODE128',
                    width: 2,
                    height: 50,
                    displayValue: false,
                    margin: 4,
                    background: '#ffffff',
                    lineColor: '#1a2e4a',
                });
            } catch (e) {
                document.getElementById('barcodeSvg').style.display = 'none';
            }
        }

        // Render segera setelah DOM ready
        renderBarcode();

        // Tombol cetak: tampilkan overlay → print → sembunyikan kembali
        btnCetak.addEventListener('click', function() {
            overlay.classList.add('show');

            // Tunda sedikit agar browser sempat paint overlay sebelum print dialog
            requestAnimationFrame(function() {
                requestAnimationFrame(function() {
                    window.print();
                    // Sembunyikan lagi setelah dialog ditutup
                    setTimeout(function() {
                        overlay.classList.remove('show');
                    }, 300);
                });
            });
        });

        // Keyboard shortcut Ctrl+P / Cmd+P juga tampilkan tiket
        window.addEventListener('beforeprint', function() {
            overlay.classList.add('show');
        });
        window.addEventListener('afterprint', function() {
            overlay.classList.remove('show');
        });

    }());
</script>

<style>
    /* ── Tiket: tampilan layar (tersembunyi default) ─────────── */
    #ticketOverlay {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, .5);
        z-index: 1050;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }

    #ticketOverlay.show {
        display: flex;
    }

    #printTicket {
        background: #fff;
        width: 340px;
        border-radius: 10px;
        overflow: hidden;
        font-family: 'Courier New', Courier, monospace;
        box-shadow: 0 8px 32px rgba(0, 0, 0, .3);
    }

    /* Header */
    .tk-header {
        background: #1a2e4a;
        color: #fff;
        padding: 14px 16px;
        text-align: center;
    }

    .tk-rs-label {
        font-size: .65rem;
        opacity: .7;
        letter-spacing: .12em;
        text-transform: uppercase;
    }

    .tk-rs-name {
        font-size: .95rem;
        font-weight: 700;
        margin: 2px 0;
    }

    .tk-rs-sub {
        font-size: .6rem;
        opacity: .75;
    }

    /* Nomor Antrean */
    .tk-queue {
        background: #f8f9fa;
        text-align: center;
        padding: 12px 8px;
        border-bottom: 1px dashed #dee2e6;
    }

    .tk-queue-label {
        font-size: .6rem;
        color: #6c757d;
        text-transform: uppercase;
        letter-spacing: .1em;
    }

    .tk-queue-number {
        font-size: 3rem;
        font-weight: 900;
        color: #1a2e4a;
        line-height: 1;
        margin: 4px 0;
    }

    .tk-queue-poli {
        font-size: .7rem;
        color: #6c757d;
    }

    /* Body tabel */
    .tk-body {
        padding: 10px 14px;
        border-bottom: 1px dashed #dee2e6;
    }

    .tk-table {
        width: 100%;
        border-collapse: collapse;
        font-size: .72rem;
    }

    .tk-label {
        color: #6c757d;
        padding: 2px 0;
        width: 38%;
        vertical-align: top;
    }

    .tk-value {
        font-size: .72rem;
        padding: 2px 0;
    }

    .tk-mono {
        font-size: .65rem;
        letter-spacing: .06em;
    }

    .fw-semibold {
        font-weight: 600;
    }

    /* Barcode */
    .tk-barcode {
        text-align: center;
        padding: 12px 14px 8px;
        border-bottom: 1px dashed #dee2e6;
    }

    .tk-barcode-label {
        font-size: .6rem;
        color: #6c757d;
        text-transform: uppercase;
        letter-spacing: .08em;
        margin-bottom: 6px;
    }

    .tk-barcode-text {
        font-size: .6rem;
        font-family: monospace;
        letter-spacing: .1em;
        color: #495057;
        margin-top: 4px;
    }

    #barcodeSvg {
        max-width: 100%;
    }

    /* Footer */
    .tk-footer {
        padding: 8px 14px;
        text-align: center;
        font-size: .6rem;
        color: #6c757d;
        line-height: 1.6;
    }

    /* ── Print media: HANYA tiket yang tercetak ──────────────── */
    @media print {

        /*
     * Strategi: sembunyikan SEMUA, lalu tampilkan hanya tiket.
     * Tidak mengandalkan class Bootstrap karena layout sidebar
     * menyebabkan konflik. Pakai visibility:hidden + position:fixed
     * agar tiket tercetak di posisi yang benar.
     */
        html,
        body {
            margin: 0 !important;
            padding: 0 !important;
            background: white !important;
        }

        /* Sembunyikan seluruh body */
        body>* {
            visibility: hidden !important;
        }

        /* Tampilkan overlay dan semua descendant-nya */
        #ticketOverlay,
        #ticketOverlay * {
            visibility: visible !important;
        }

        /* Posisi tiket: tengah halaman */
        #ticketOverlay {
            display: block !important;
            position: fixed !important;
            inset: 0 !important;
            background: white !important;
            z-index: 9999 !important;
            padding: 0 !important;
        }

        #printTicket {
            position: absolute !important;
            top: 10mm !important;
            left: 50% !important;
            transform: translateX(-50%) !important;
            box-shadow: none !important;
            border-radius: 0 !important;
            border: 1px solid #ccc !important;
            width: 80mm !important;
            /* ukuran thermal printer standar */
        }

        /* Paksa warna background tercetak */
        .tk-header {
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        .tk-queue {
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        /* Sembunyikan tombol dan elemen UI */
        .d-print-none,
        button,
        .btn {
            display: none !important;
        }
    }
</style>