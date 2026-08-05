<?= $this->extend('templates/main_layout') ?>

<?php
    // Map status pesanan ke badge class bertema
    $status = $order['status_pesanan'] ?? '';
    $badgeClass = 'bg-primary';
    $map = [
        'Menunggu Pembayaran' => 'bg-warning text-dark',
        'Menunggu Bukti Transfer' => 'bg-warning text-dark',
        'Menunggu Verifikasi Admin' => 'bg-warning text-dark',
        'Dikonfirmasi' => 'bg-primary',
        'Diproses' => 'bg-primary',
        'Siap Dikirim' => 'bg-primary',
        'Siap Dikirim/Diambil' => 'bg-primary',
        'Dalam Pengiriman' => 'bg-primary text-white',
        'Dikirim' => 'bg-info text-dark',
        'Selesai' => 'bg-success',
        'Dikembalikan' => 'bg-secondary',
        'Dibatalkan' => 'bg-danger',
        'Dibatalkan Sistem' => 'bg-danger',
    ];
    if (isset($map[$status])) { $badgeClass = $map[$status]; }
    
    // Resolve Lottie animation JSON per status.
    // 1) Explicit overrides (custom file names)
    $statusToFile = [
        'Dalam Pengiriman' => 'otw.json', // existing custom file
    ];
    // 2) Slugified fallback: e.g., "Siap Dikirim/Diambil" -> siap_dikirim_diambil.json
    $slug = strtolower(trim($status));
    $slug = str_replace([' ', '/', '-', '\\'], '_', $slug);
    $slug = preg_replace('/[^a-z0-9_]/', '', $slug);
    $candidates = [];
    if (isset($statusToFile[$status])) { $candidates[] = $statusToFile[$status]; }
    if ($slug !== '') { $candidates[] = $slug . '.json'; }
    // Determine first existing file in public/assets
    $animationUrl = '';
    foreach ($candidates as $file) {
        $abs = FCPATH . 'assets/' . $file;
        if (is_file($abs)) {
            $animationUrl = base_url('assets/' . $file);
            break;
        }
    }
?>

<?= $this->section('title') ?>
    Detail Pesanan #<?= esc($order['order_id']) ?> | <?= esc($store['name'] ?? 'JS Florist') ?>
<?= $this->endSection() ?>

<?= $this->section('meta_description') ?>
    Lihat status dan rincian pesanan #<?= esc($order['order_id']) ?> di <?= esc($store['name'] ?? 'JS Florist') ?>.
<?= $this->endSection() ?>

<?= $this->section('content') ?>

    <!-- Page Header -->
    <div class="container-fluid page-header py-5" style="background: linear-gradient(rgba(0, 0, 0, 0.35), rgba(0, 0, 0, 0.35)), url(<?= base_url('assets/img/page-header.png') ?>) center center no-repeat; background-size: cover;">
        <h1 class="text-center text-white display-6">Detail Pesanan</h1>
        <ol class="breadcrumb justify-content-center mb-0">
            <li class="breadcrumb-item"><a href="<?= site_url('dashboard') ?>">Home</a></li>
            <li class="breadcrumb-item"><a href="<?= site_url('tracking') ?>">Lacak Pesanan</a></li>
            <li class="breadcrumb-item active text-white">#<?= esc($order['order_id']) ?></li>
        </ol>
    </div>

    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-11 col-xl-10">
                <div class="bg-light rounded-3 p-4 p-md-5 border border-secondary">
                    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
                        <div>
                            <h2 class="mb-1">Pesanan #<?= esc($order['order_id']) ?></h2>
                            <div class="text-muted">Tanggal Pesan: <?= esc(date('d F Y, H:i', strtotime($order['tanggal_pesan']))) ?> WITA</div>
                        </div>
                        <span class="badge <?= $badgeClass ?> fs-6 py-2 px-3"><?= esc($order['status_pesanan']) ?></span>
                    </div>
                    <?php if (!empty($animationUrl)): ?>
                        <div class="d-flex align-items-center justify-content-center my-3">
                            <div id="otwAnimation" style="width: 160px; height: 160px;"></div>
                        </div>
                    <?php endif; ?>

                    <div class="row g-4 mb-4">
                        <div class="col-md-6">
                            <div class="p-3 border rounded-3 h-100">
                                <h5 class="mb-3"><i class="fa fa-truck me-2 text-primary"></i>Pengantaran</h5>
                                <dl class="row mb-0">
                                    <dt class="col-5">Tipe</dt>
                                    <dd class="col-7"><?= esc($order['tipe_pengantaran']) ?></dd>
                                    <dt class="col-5">Tanggal Antar</dt>
                                    <dd class="col-7"><?= esc(date('d F Y, H:i', strtotime($order['tanggal_pengantaran']))) ?> WITA</dd>
                                    <?php if ($order['tipe_pengantaran'] === 'Delivery'): ?>
                                        <dt class="col-5">Alamat</dt>
                                        <dd class="col-7"><?= esc($order['alamat_pengiriman_teks']) ?></dd>
                                    <?php endif; ?>
                                </dl>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 border rounded-3 h-100">
                                <h5 class="mb-3"><i class="fa fa-user me-2 text-primary"></i>Penerima & Pemesan</h5>
                                <dl class="row mb-0">
                                    <dt class="col-5">Nama Penerima</dt>
                                    <dd class="col-7"><?= esc($order['penerima_nama']) ?></dd>
                                    <?php if (!empty($order['penerima_nomor_hp'])): ?>
                                        <dt class="col-5">HP Penerima</dt>
                                        <dd class="col-7"><?= esc($order['penerima_nomor_hp']) ?></dd>
                                    <?php endif; ?>
                                    <dt class="col-5">HP Pemesan</dt>
                                    <dd class="col-7"><?= esc($order['nomor_pemesan']) ?></dd>
                                </dl>
                            </div>
                        </div>
                    </div>

                    <div class="row g-4 mb-4">
                        <div class="col-md-6">
                            <div class="p-3 border rounded-3 h-100">
                                <h5 class="mb-3"><i class="fa fa-credit-card me-2 text-primary"></i>Pembayaran</h5>
                                <dl class="row mb-0">
                                    <dt class="col-5">Metode</dt>
                                    <dd class="col-7"><?= esc($order['metode_pembayaran']) ?></dd>
                                    <dt class="col-5">Total</dt>
                                    <dd class="col-7">Rp<?= number_format($order['total_harga'], 0, ',', '.') ?></dd>
                                </dl>
                                <?php if (!empty($order['bukti_bayar'])): ?>
                                    <a href="<?= base_url('view-proof/' . esc(basename($order['bukti_bayar']))) ?>" target="_blank" class="btn btn-outline-primary btn-sm mt-3"><i class="fa fa-file-image me-2"></i>Lihat Bukti</a>
                                <?php else: ?>
                                    <?php if ($order['status_pesanan'] === 'Menunggu Bukti Transfer'): ?>
                                        <p class="text-danger small mb-2">Bukti pembayaran belum diunggah.</p>
                                        <?php $uploadUrl = $order['metode_pembayaran'] === 'QRIS' ? base_url('checkout/qris/' . esc($order['order_id'])) : base_url('payment/bank-transfer/' . esc($order['order_id'])); ?>
                                        <a href="<?= $uploadUrl ?>" class="btn btn-primary btn-sm"><i class="fa fa-upload me-2"></i>Unggah Bukti</a>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <?php if (!empty($order['catatan_penerima'])): ?>
                            <div class="p-3 border rounded-3 h-100">
                                <h5 class="mb-2"><i class="fa fa-sticky-note me-2 text-primary"></i>Catatan/Ucapan</h5>
                                <p class="mb-0"><?= nl2br(esc($order['catatan_penerima'])) ?></p>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <h4 class="mb-3">Item Pesanan</h4>
                    <div class="table-responsive">
                        <table class="table table-striped align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:90px;">Produk</th>
                                    <th>Nama Produk</th>
                                    <th class="text-center" style="width:120px;">Kuantitas</th>
                                    <th class="text-end" style="width:160px;">Harga Satuan</th>
                                    <th class="text-end" style="width:160px;">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $itemSubtotal = 0; ?>
                                <?php foreach ($orderItems as $item): ?>
                                    <?php $currentSubtotal = ($item['harga_satuan'] ?? 0) * ($item['kuantitas'] ?? 0); ?>
                                    <?php $itemSubtotal += $currentSubtotal; ?>
                                    <tr>
                                        <td>
                                            <?php if (!empty($item['gambar_url'])): ?>
                                                <img src="<?= base_url('assets/img/gambar/' . esc($item['gambar_url'])) ?>" alt="<?= esc($item['nama_produk']) ?>" class="product-thumbnail">
                                            <?php else: ?>
                                                <img src="<?= base_url('assets/img/default-product.jpg') ?>" alt="No Image" class="product-thumbnail">
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="fw-semibold"><?= esc($item['nama_produk']) ?></div>
                                            <?php if (!empty($item['custom_details'])): ?>
                                                <?php $details = json_decode($item['custom_details'], true); ?>
                                                <div class="mt-2 text-muted small">
                                                    <?php if (isset($details['variant_name']) && $details['variant_name'] !== ''): ?>
                                                        <div><strong>Varian:</strong> <?= esc($details['variant_name']) ?></div>
                                                    <?php endif; ?>
                                                    <?php if (isset($details['jenis_item']) && !empty($details['jenis_item'])): ?>
                                                        <div><strong>Jenis:</strong> <?= esc($details['jenis_item']) ?></div>
                                                    <?php endif; ?>
                                                    <?php if (isset($details['jumlah_item']) && !empty($details['jumlah_item'])): ?>
                                                        <div><strong>Jumlah:</strong> <?= esc($details['jumlah_item']) ?></div>
                                                    <?php endif; ?>
                                                    <?php if (isset($details['bunga']) && is_array($details['bunga']) && !empty($details['bunga'])): ?>
                                                        <div><strong>Bunga:</strong> <?= esc(implode(', ', $details['bunga'])) ?></div>
                                                    <?php endif; ?>
                                                    <?php if (isset($details['nominal'])): ?>
                                                        <div><strong>Nominal:</strong> Rp<?= number_format((float)$details['nominal'], 0, ',', '.') ?></div>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center"><?= esc($item['kuantitas']) ?></td>
                                        <td class="text-end">Rp<?= number_format($item['harga_satuan'], 0, ',', '.') ?></td>
                                        <td class="text-end">Rp<?= number_format($currentSubtotal, 0, ',', '.') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="4" class="text-end"><strong>Total Item Pesanan:</strong></td>
                                    <td class="text-end"><strong>Rp<?= number_format($itemSubtotal, 0, ',', '.') ?></strong></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mt-4">
                        <a href="<?= site_url('dashboard') ?>" class="btn btn-outline-secondary rounded-pill px-4"><i class="fa fa-home me-2"></i>Beranda</a>
                        <a href="<?= site_url('tracking') ?>" class="btn btn-primary border-2 border-secondary rounded-pill px-4"><i class="fa fa-search me-2"></i>Lacak Pesanan Lain</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?= $this->endSection() ?>

<?= $this->section('extra_js') ?>
<script>
    $(function(){
        var el = document.getElementById('otwAnimation');
        var path = <?= json_encode($animationUrl ?? '') ?>;
        if (el && window.lottie && path) {
            lottie.loadAnimation({
                container: el,
                renderer: 'svg',
                loop: <?= ($status === 'Selesai') ? 'false' : 'true' ?>,
                autoplay: true,
                path: path
            });
        }
    });
 </script>
<?= $this->endSection() ?>