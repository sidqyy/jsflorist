<?= $this->extend('admin/layout/main') ?>

<?= $this->section('title') ?>
Analisis Produk
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<h1 class="mb-4">Analisis Produk & Kategori</h1>

<div class="row">
    <div class="col-lg-7 mb-4">
        <div class="card shadow">
            <div class="card-header">
                <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-fire me-2"></i>Produk Terlaris (Top 10)</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th colspan="2">Produk</th>
                                <th>Terjual</th>
                                <th>Pendapatan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($produk_terlaris)): ?>
                                <?php foreach ($produk_terlaris as $produk): ?>
                                    <tr>
                                        <td style="width: 80px;">
                                            <img src="<?= base_url('assets/img/gambar/' . esc($produk['gambar_url'])) ?>" class="product-thumbnail" alt="<?= esc($produk['nama_produk']) ?>">
                                        </td>
                                        <td>
                                            <strong><?= esc($produk['nama_produk']) ?></strong>
                                        </td>
                                        <td><?= esc($produk['total_terjual']) ?> unit</td>
                                        <td>Rp<?= number_format($produk['total_pendapatan'], 0, ',', '.') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="text-center">Belum ada data penjualan.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-5 mb-4">
        <div class="card shadow">
            <div class="card-header">
                <h6 class="m-0 font-weight-bold text-success"><i class="fas fa-tags me-2"></i>Kategori Terlaris</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Nama Kategori</th>
                                <th>Pendapatan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($analisis_kategori)): ?>
                                <?php foreach ($analisis_kategori as $kategori): ?>
                                    <tr>
                                        <td>
                                            <strong><?= esc($kategori['nama_kategori']) ?></strong><br>
                                            <small class="text-muted"><?= esc($kategori['jumlah_transaksi']) ?> transaksi</small>
                                        </td>
                                        <td>
                                            <span class="badge bg-success fs-6">
                                                Rp<?= number_format($kategori['total_pendapatan_kategori'], 0, ',', '.') ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="2" class="text-center">Belum ada data penjualan.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>