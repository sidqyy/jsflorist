<?= $this->extend('admin/layout/main') ?>

<?= $this->section('title') ?>
Detail Pesanan #<?= esc($order['order_id']) ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<h1>Detail Pesanan #<?= esc($order['order_id']) ?></h1>

<div class="card shadow-sm mb-4">
    <div class="card-header">
        <h5>Informasi Pesanan</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <p><strong>ID Pesanan:</strong> <?= esc($order['order_id']) ?></p>
                <p><strong>Tanggal Pesan:</strong> <?= esc($order['tanggal_pesan']) ?></p>
                <p><strong>Status Pesanan:</strong> <?= esc($order['status_pesanan']) ?></p>
                <p><strong>Total Harga:</strong> Rp<?= number_format($order['total_harga'], 0, ',', '.') ?></p>
                <p><strong>Metode Pembayaran:</strong> <?= esc($order['metode_pembayaran']) ?></p>
                <p><strong>Tanggal Pengantaran:</strong> <?= esc($order['tanggal_pengantaran']) ?></p>
                <p><strong>Tipe Pengantaran:</strong> <?= esc($order['tipe_pengantaran']) ?></p>
            </div>
            <div class="col-md-6">
                <p><strong>Nama Penerima:</strong> <?= esc($order['penerima_nama']) ?></p>
                <p><strong>Nomor HP Penerima:</strong> <?= esc($order['penerima_nomor_hp']) ?></p>
                <p><strong>Alamat Pengiriman:</strong> <?= esc($order['alamat_pengiriman_teks']) ?></p>
                <p><strong>Catatan Penerima:</strong> <?= esc($order['catatan_penerima']) ?></p>
                <p><strong>Nomor Pemesan:</strong> <?= esc($order['nomor_pemesan']) ?></p>
                <?php if ($order['bukti_bayar']): ?>
                    <p><strong>Bukti Pembayaran:</strong> <a href="<?= base_url(esc($order['bukti_bayar'])) ?>" target="_blank">Lihat Bukti</a></p>
                <?php endif; ?>
        </div>
        <hr>
        <h5>Informasi Pelanggan</h5>
        <p><strong>Username:</strong> <?= esc($user['username'] ?? 'Guest') ?></p>
        <p><strong>Email:</strong> <?= esc($user['email'] ?? '-') ?></p>
        <p><strong>Nomor HP:</strong> <?= esc($user['nomor_hp'] ?? '-') ?></p>

        <hr>
        <h5>Ubah Status Pesanan</h5>
        <form action="<?= base_url('admin/orders/update-status/' . $order['order_id']) ?>" method="post">
            <?= csrf_field() ?>
            <div class="mb-3">
                <label for="status_pesanan" class="form-label">Status Baru</label>
                <select class="form-select" id="status_pesanan" name="status_pesanan" required>
                    <option value="Pending" <?= ($order['status_pesanan'] == 'Pending') ? 'selected' : '' ?>>Pending</option>
                    <option value="Menunggu Bukti Transfer" <?= ($order['status_pesanan'] == 'Menunggu Bukti Transfer') ? 'selected' : '' ?>>Menunggu Bukti Transfer</option>
                    <option value="Menunggu Verifikasi Admin" <?= ($order['status_pesanan'] == 'Menunggu Verifikasi Admin') ? 'selected' : '' ?>>Menunggu Verifikasi Admin</option>
                    <option value="Dikonfirmasi" <?= ($order['status_pesanan'] == 'Dikonfirmasi') ? 'selected' : '' ?>>Dikonfirmasi</option>
                    <option value="Diproses" <?= ($order['status_pesanan'] == 'Diproses') ? 'selected' : '' ?>>Diproses</option>
                    <option value="Siap Dikirim/Diambil" <?= ($order['status_pesanan'] == 'Siap Dikirim/Diambil') ? 'selected' : '' ?>>Siap Dikirim/Diambil</option>
                    <option value="Dalam Pengiriman" <?= ($order['status_pesanan'] == 'Dalam Pengiriman') ? 'selected' : '' ?>>Dalam Pengiriman</option>
                    
                    <?php if ($currentUserRole === 'management'): ?>
                        <option value="Selesai" <?= ($order['status_pesanan'] == 'Selesai') ? 'selected' : '' ?>>Selesai</option>
                        <option value="Dibatalkan" <?= ($order['status_pesanan'] == 'Dibatalkan') ? 'selected' : '' ?>>Dibatalkan</option>
                        <option value="Dikembalikan" <?= ($order['status_pesanan'] == 'Dikembalikan') ? 'selected' : '' ?>>Dikembalikan</option>
                    <?php endif; ?>

                </select>
            </div>
            <button type="submit" class="btn btn-primary">Ubah Status</button>
        </form>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-header">
        <h5>Item Pesanan</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Gambar Produk</th>
                        <th>Produk</th>
                        <th>Kuantitas</th>
                        <th>Harga Satuan</th>
                        <th>Subtotal</th>
                        <th>Rincian Komponen Produk</th>
                        <th>Detail Kustom</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($orderItems)): ?>
                        <?php foreach ($orderItems as $item): ?>
                            <tr>
                                <td>
                                    <?php if (!empty($item['product_details']['gambar_url']) && file_exists(FCPATH . 'assets/img/gambar/' . $item['product_details']['gambar_url'])): ?>
                                        <img src="<?= base_url('assets/img/gambar/' . esc($item['product_details']['gambar_url'])) ?>" alt="<?= esc($item['product_details']['nama_produk']) ?>" class="img-thumbnail" style="width: 70px; height: 70px; object-fit: cover;">
                                    <?php else: ?>
                                        <img src="<?= base_url('assets/img/gambar/default_product.png') ?>" alt="Tidak Ada Gambar" class="img-thumbnail" style="width: 70px; height: 70px; object-fit: cover;">
                                    <?php endif; ?>
                                </td>
                                <td><?= esc($item['product_details']['nama_produk']) ?></td>
                                <td><?= esc($item['kuantitas']) ?></td>
                                <td>Rp<?= number_format($item['harga_satuan'], 0, ',', '.') ?></td>
                                <td>Rp<?= number_format($item['kuantitas'] * $item['harga_satuan'], 0, ',', '.') ?></td>
                                <td>
                                    <?php if (!empty($item['components'])): ?>
                                        <ul class="list-unstyled mb-0 small">
                                            <?php
                                            $totalComponentCost = 0;
                                            foreach ($item['components'] as $component):
                                                $subComponentTotal = $component['quantity'] * $component['unit_cost'];
                                                $totalComponentCost += $subComponentTotal;
                                            ?>
                                                <li>
                                                    <?= esc($component['component_name']) ?>:
                                                    <?= esc($component['quantity']) ?> @ Rp<?= number_format($component['unit_cost'], 0, ',', '.') ?>
                                                    (Total: Rp<?= number_format($subComponentTotal, 0, ',', '.') ?>)
                                                </li>
                                            <?php endforeach; ?>
                                            <?php if ($totalComponentCost > 0): ?>
                                                <li class="mt-2 pt-2 border-top">
                                                    <strong>Total Biaya Komponen:</strong>
                                                    <span class="float-end">Rp<?= number_format($totalComponentCost, 0, ',', '.') ?></span>
                                                </li>
                                            <?php endif; ?>
                                        </ul>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php
                                    // Handle custom_details if it's JSON
                                    $customDetails = json_decode($item['custom_details'], true);
                                    if (json_last_error() === JSON_ERROR_NONE && is_array($customDetails)) {
                                        echo '<ul class="list-unstyled mb-0 small">';
                                        foreach ($customDetails as $key => $value) {
                                            if (is_array($value)) {
                                                echo '<li><strong>' . esc(ucwords(str_replace('_', ' ', $key))) . ':</strong> ' . esc(implode(', ', $value)) . '</li>';
                                            } else {
                                                echo '<li><strong>' . esc(ucwords(str_replace('_', ' ', $key))) . ':</strong> ' . esc($value) . '</li>';
                                            }
                                        }
                                        echo '</ul>';
                                    } else {
                                        // Fallback if not JSON or empty
                                        echo esc($item['custom_details'] ?? '-');
                                    }
                                    ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center">Tidak ada item dalam pesanan ini.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?= $this->endSection() ?>