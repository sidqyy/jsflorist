
<?= $this->extend('admin/layout/main') ?>



<?= $this->section('title') ?>

Detail Pesanan #<?= esc($order['order_id']) ?>

<?= $this->endSection() ?>



<?= $this->section('content') ?>

<?php

    $diskon  = isset($order['diskon']) ? (float) $order['diskon'] : 0;

    $ongkir  = isset($order['biaya_pengiriman']) ? (float) $order['biaya_pengiriman'] : 0;

    $status  = $order['status_pesanan'] ?? 'Pending';



    $totalHargaDB = (float)($order['total_harga'] ?? 0);
    $hargaSetelahDiskon = max(0, $totalHargaDB - $ongkir);

    // Menghitung harga produk murni dari orderItems
    $hargaAsliProduk = 0;

    if (isset($orderItems) && is_array($orderItems)) {
        foreach ($orderItems as $item) {
            $isBonus = false;
            $customDetails = json_decode($item['custom_details'] ?? '{}', true);
            if ((float)$item['harga_satuan'] == 0 || (isset($customDetails['note']) && strpos($customDetails['note'], 'Hadiah') !== false)) {
                $isBonus = true;
            }
            if (!$isBonus) {
                $qty = (int)$item['kuantitas'];
                // Mengambil harga asli dari tabel product jika ada, jika tidak fallback ke harga_satuan
                $hargaOriginal = isset($item['product_details']['harga']) ? (float)$item['product_details']['harga'] : (float)$item['harga_satuan'];
                
                $hargaAsliProduk += ($hargaOriginal * $qty);
            }
        }
    }

    // Jika kosong, fallback
    if ($hargaAsliProduk == 0) {
        $hargaAsliProduk = $hargaSetelahDiskon + $diskon;
    }

    // Total semua potongan (asli - harga akhir sblm ongkir)
    $totalDiskon = max(0, $hargaAsliProduk - $hargaSetelahDiskon);



    $statusClass = [

        'Pending' => 'bg-warning text-dark',

        'Menunggu Bukti Transfer' => 'bg-warning text-dark',

        'Menunggu Verifikasi Admin' => 'bg-info text-dark',

        'Dikonfirmasi' => 'bg-primary',

        'Diproses' => 'bg-primary',

        'Siap Dikirim/Diambil' => 'bg-secondary',

        'Dalam Pengiriman' => 'bg-info',

        'Selesai' => 'bg-success',

        'Dibatalkan' => 'bg-danger',

        'Dibatalkan Sistem' => 'bg-danger',

        'Dikembalikan' => 'bg-dark'

    ][$status] ?? 'bg-secondary';

?>

<style>

    .order-hero {

        background: linear-gradient(135deg, rgba(208,156,76,.12), rgba(208,156,76,.05));

        border: 1px solid #f0e5d3;

        border-radius: 12px;

    }



    .order-meta small {

        color: #6c757d;

    }



    .badge.status {

        border-radius: 999px;

        padding: .5rem .75rem;

    }



    .btn-chip {

        border-radius: 999px;

    }



    .img-thumb-rounded {

        border-radius: 10px;

    }



    .table thead th {

        background: #fffaf4;

    }



    @media(max-width:576px) {

        .stack-sm {

            flex-direction: column;

            align-items: flex-start;

            gap: .25rem;

        }

    }

</style>



<div class="order-hero p-3 p-md-4 mb-4 shadow-sm">

    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">

        <div class="d-flex align-items-center gap-3">

            <div class="rounded-circle d-inline-flex align-items-center justify-content-center bg-white shadow-sm" style="width:36px;height:36px;color:#d09c4c;">

                <i class="bi bi-receipt"></i>

            </div>



            <div>

                <h4 class="mb-1">

                    Detail Pesanan <span class="text-primary">#<?= esc($order['order_id']) ?></span>

                </h4>



                <div class="order-meta d-flex stack-sm align-items-center gap-3 small">

                    <span><i class="bi bi-calendar3 me-1"></i><?= esc($order['tanggal_pesan']) ?></span>

                    <span class="d-none d-sm-inline">•</span>

                    <span><i class="bi bi-credit-card me-1"></i><?= esc($order['metode_pembayaran']) ?></span>

                </div>

            </div>

        </div>



        <span class="badge status <?= $statusClass ?>">

            <i class="bi bi-info-circle me-1"></i><?= esc($status) ?>

        </span>

    </div>

</div>



<div class="card shadow-sm mb-4">

    <div class="card-header">

        <h5>Informasi Pesanan</h5>

    </div>



    <div class="card-body">

        <div class="row">

            <div class="col-md-6">

                <p><strong>ID Pesanan:</strong> <?= esc($order['order_id']) ?></p>



                <?php if (!empty($order['store_name'])): ?>

                    <p>

                        <strong>Asal Toko:</strong>

                        <span class="fw-bold text-info"><?= esc($order['store_name']) ?></span>

                    </p>

                <?php endif; ?>



                <p><strong>Tanggal Pesan:</strong> <?= esc($order['tanggal_pesan']) ?></p>



                <p>

                    <strong>Status Pesanan:</strong>

                    <span class="badge rounded-pill <?= $statusClass ?>">

                        <?= esc($order['status_pesanan']) ?>

                    </span>

                </p>



                <p>
                    <strong>Harga Asli (Produk):</strong>
                    <?php if ($totalDiskon > 0): ?>
                        <span class="text-muted text-decoration-line-through">
                            Rp<?= number_format($hargaAsliProduk, 0, ',', '.') ?>
                        </span>
                    <?php else: ?>
                        <span>
                            Rp<?= number_format($hargaAsliProduk, 0, ',', '.') ?>
                        </span>
                    <?php endif; ?>
                </p>

                <?php if ($totalDiskon > 0): ?>
                <p>
                    <strong>Total Diskon:</strong>
                    <span class="text-success">
                        - Rp<?= number_format($totalDiskon, 0, ',', '.') ?>
                    </span>
                </p>
                <?php endif; ?>

                <p>
                    <strong>Harga Setelah Diskon:</strong>
                    Rp<?= number_format($hargaSetelahDiskon, 0, ',', '.') ?>
                </p>



                <p>

                    <strong>Ongkir:</strong>

                    Rp<?= number_format($ongkir, 0, ',', '.') ?>

                </p>



                <p class="fw-bold">

                    <strong>Total Harga:</strong>

                    Rp<?= number_format($totalHargaDB, 0, ',', '.') ?>

                </p>



                <p><strong>Metode Pembayaran:</strong> <?= esc($order['metode_pembayaran']) ?></p>

            </div>



            <div class="col-md-6">

                <p>

                    <strong>Tipe Pengantaran:</strong>

                    <span class="badge bg-primary rounded-pill">

                        <i class="bi bi-truck me-1"></i><?= esc($order['tipe_pengantaran']) ?>

                    </span>

                </p>



                <p><strong>Jadwal:</strong> <?= esc($order['tanggal_pengantaran']) ?></p>



                <?php if ($order['tipe_pengantaran'] == 'Self-Pickup'): ?>

                    <p><strong>Nama Pengambil:</strong> <?= esc($order['penerima_nama']) ?></p>

                <?php else: ?>

                    <p><strong>Nama Penerima:</strong> <?= esc($order['penerima_nama']) ?></p>

                    <p><strong>Nomor HP Penerima:</strong> <?= esc($order['penerima_nomor_hp'] ?? '-') ?></p>

                    <p><strong>Alamat Pengiriman:</strong> <?= esc($order['alamat_pengiriman_teks']) ?></p>



                    <?php if (!empty($order['alamat_latitude']) && !empty($order['alamat_longitude'])): ?>

                        <p class="mb-2">

                            <strong>Koordinat:</strong>

                            <?= esc($order['alamat_latitude']) ?>, <?= esc($order['alamat_longitude']) ?>

                        </p>



                        <a class="btn btn-outline-primary btn-sm btn-chip"

                           href="https://maps.google.com/?q=<?= esc($order['alamat_latitude']) ?>,<?= esc($order['alamat_longitude']) ?>"

                           target="_blank"

                           rel="noopener noreferrer">

                            <i class="bi bi-geo-alt"></i> Lihat di Peta

                        </a>

                    <?php endif; ?>

                <?php endif; ?>



                <p><strong>Catatan:</strong> <?= esc($order['catatan_penerima'] ?? '-') ?></p>

                <p><strong>Nomor Pemesan:</strong> <?= esc($order['nomor_pemesan']) ?></p>



                <?php if (!empty($order['bukti_bayar'])): ?>

                    <p>

                        <strong>Bukti Pembayaran:</strong>

                        <a class="btn btn-outline-success btn-sm btn-chip"

                           href="<?= base_url('view-proof/' . esc(basename($order['bukti_bayar']))) ?>"

                           target="_blank">

                            <i class="bi bi-file-earmark-image"></i> Lihat Bukti

                        </a>

                    </p>

                <?php endif; ?>

            </div>

        </div>



        <hr>



        <h5>Informasi Pelanggan</h5>

        <p><strong>Username:</strong> <?= esc($user['username'] ?? 'Guest') ?></p>

        <p><strong>Email:</strong> <?= esc($user['email'] ?? '-') ?></p>

        <p><strong>Nomor HP:</strong> <?= esc($user['nomor_hp'] ?? '-') ?></p>



        <hr>



        <h5>Ubah Status Pesanan</h5>



        <?php

            $finalStatuses = ['Selesai', 'Dibatalkan', 'Dibatalkan Sistem', 'Dikembalikan'];

            $isFinalStatus = in_array($order['status_pesanan'], $finalStatuses);

        ?>



        <form action="<?= base_url('admin/orders/update-status/' . $order['order_id']) ?>" method="post">

            <?= csrf_field() ?>



            <div class="mb-3">

                <label for="status_pesanan" class="form-label">Status Baru</label>



                <select class="form-select" id="status_pesanan" name="status_pesanan" required <?= $isFinalStatus ? 'disabled' : '' ?>>

                    <option value="Pending" <?= ($order['status_pesanan'] == 'Pending') ? 'selected' : '' ?>>Pending</option>

                    <option value="Menunggu Bukti Transfer" <?= ($order['status_pesanan'] == 'Menunggu Bukti Transfer') ? 'selected' : '' ?>>Menunggu Bukti Transfer</option>

                    <option value="Menunggu Verifikasi Admin" <?= ($order['status_pesanan'] == 'Menunggu Verifikasi Admin') ? 'selected' : '' ?>>Menunggu Verifikasi Admin</option>

                    <option value="Dikonfirmasi" <?= ($order['status_pesanan'] == 'Dikonfirmasi') ? 'selected' : '' ?>>Dikonfirmasi</option>

                    <option value="Diproses" <?= ($order['status_pesanan'] == 'Diproses') ? 'selected' : '' ?>>Diproses</option>

                    <option value="Siap Dikirim/Diambil" <?= ($order['status_pesanan'] == 'Siap Dikirim/Diambil') ? 'selected' : '' ?>>Siap Dikirim/Diambil</option>

                    <option value="Dalam Pengiriman" <?= ($order['status_pesanan'] == 'Dalam Pengiriman') ? 'selected' : '' ?>>Dalam Pengiriman</option>

                    <option value="Selesai" <?= ($order['status_pesanan'] == 'Selesai') ? 'selected' : '' ?>>Selesai</option>



                    <?php if ($order['status_pesanan'] === 'Dibatalkan Sistem'): ?>

                        <option value="Dibatalkan Sistem" selected>Dibatalkan Sistem</option>

                    <?php endif; ?>



                    <?php if ($currentUserRole === 'management'): ?>

                        <option value="Dibatalkan" <?= ($order['status_pesanan'] == 'Dibatalkan') ? 'selected' : '' ?>>Dibatalkan</option>

                        <option value="Dikembalikan" <?= ($order['status_pesanan'] == 'Dikembalikan') ? 'selected' : '' ?>>Dikembalikan</option>

                    <?php endif; ?>

                </select>

            </div>



            <button type="submit" class="btn btn-primary" <?= $isFinalStatus ? 'disabled' : '' ?>>

                Ubah Status

            </button>

        </form>

    </div>

</div>



<div class="card shadow-sm">

    <div class="card-header">

        <h5>Item Pesanan</h5>

    </div>



    <div class="card-body">

        <div class="table-responsive">

            <table class="table table-bordered table-striped table-hover align-middle">

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

                            <?php

                                $customDetails = json_decode($item['custom_details'] ?? '{}', true);

                                $isBonusItem = false;



                                if ((float)$item['harga_satuan'] == 0 || (isset($customDetails['note']) && strpos($customDetails['note'], 'Hadiah') !== false)) {

                                    $isBonusItem = true;

                                }

                            ?>



                            <tr>

                                <td>

                                    <?php if (!empty($item['product_details']['gambar_url']) && file_exists(FCPATH . 'assets/img/gambar/' . $item['product_details']['gambar_url'])): ?>

                                        <img src="<?= base_url('assets/img/gambar/' . esc($item['product_details']['gambar_url'])) ?>"

                                             alt="<?= esc($item['product_details']['nama_produk']) ?>"

                                             class="img-thumbnail img-thumb-rounded"

                                             style="width: 70px; height: 70px; object-fit: cover;">

                                    <?php else: ?>

                                        <img src="<?= base_url('assets/img/gambar/default_product.png') ?>"

                                             alt="Tidak Ada Gambar"

                                             class="img-thumbnail img-thumb-rounded"

                                             style="width: 70px; height: 70px; object-fit: cover;">

                                    <?php endif; ?>

                                </td>



                                <td>

                                    <?= esc($item['product_details']['nama_produk'] ?? 'Produk Hadiah') ?>



                                    <?php if ($isBonusItem): ?>

                                        <span class="badge bg-success ms-2 text-white" style="background-color: #198754 !important; font-size: 0.75rem; font-weight: bold; border-radius: 4px; padding: 4px 8px;">

                                            <i class="bi bi-gift-fill me-1"></i> HADIAH BONUS

                                        </span>

                                    <?php endif; ?>

                                </td>



                                <td><?= esc($item['kuantitas']) ?></td>



                                <td>

                                    <?php if ($isBonusItem): ?>

                                        <span class="text-success fw-bold">Gratis (Promo)</span>

                                    <?php else: ?>

                                        Rp<?= number_format($item['harga_satuan'], 0, ',', '.') ?>

                                    <?php endif; ?>

                                </td>



                                <td>

                                    <?php if ($isBonusItem): ?>

                                        <span class="text-success fw-bold">Rp0</span>

                                    <?php else: ?>

                                        Rp<?= number_format($item['kuantitas'] * $item['harga_satuan'], 0, ',', '.') ?>

                                    <?php endif; ?>

                                </td>



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