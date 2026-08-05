<?= $this->extend('admin/layout/main') ?>

<?= $this->section('title') ?>Kelola Aturan Diskon<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Kelola Aturan Diskon</h3>
                    <a href="<?= base_url('admin/discounts/create') ?>" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Tambah Aturan Diskon
                    </a>
                </div>
                <div class="card-body">
                    <?php if (session()->getFlashdata('success')): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?= session()->getFlashdata('success') ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <?php if (session()->getFlashdata('error')): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?= session()->getFlashdata('error') ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <!-- Legend -->
                    <div class="mb-3">
                        <span class="badge bg-info me-2">Subtotal</span> = Diskon berdasarkan total belanja
                        <span class="badge bg-warning text-dark me-2 ms-3">Produk</span> = Diskon untuk produk tertentu
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama</th>
                                    <th>Tipe</th>
                                    <th>Ketentuan</th>
                                    <th>Diskon (%)</th>
                                    <th>Penggunaan</th>
                                    <th>Periode</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($discounts)): ?>
                                    <?php foreach ($discounts as $index => $discount): ?>
                                        <?php 
                                            $isProductType = ($discount['discount_type'] ?? 'subtotal') === 'product';
                                            
                                            // Parse product_ids - format baru: {"PROD001": {"discounted_price": 1250000}}
                                            $productData = $isProductType ? (json_decode($discount['product_ids'] ?? '[]', true) ?? []) : [];
                                            $productIds = [];
                                            $productPrices = [];
                                            foreach ($productData as $pid => $info) {
                                                if (is_array($info) && isset($info['discounted_price'])) {
                                                    // Format baru
                                                    $productIds[] = $pid;
                                                    $productPrices[$pid] = $info['discounted_price'];
                                                } else {
                                                    // Format lama
                                                    $productIds[] = $info;
                                                }
                                            }
                                            
                                            $usageLimit = $discount['usage_limit'] ?? null;
                                            $usageCount = (int)($discount['usage_count'] ?? 0);
                                            $remaining = $usageLimit ? max(0, $usageLimit - $usageCount) : null;
                                            $isExpired = false;
                                            
                                            // Cek tanggal
                                            $now = date('Y-m-d H:i:s');
                                            if (!empty($discount['end_date']) && $now > $discount['end_date']) {
                                                $isExpired = true;
                                            }
                                            if (!empty($discount['start_date']) && $now < $discount['start_date']) {
                                                $isExpired = true; // Belum mulai
                                            }
                                            
                                            // Cek usage limit
                                            $isLimitReached = $usageLimit && $usageCount >= $usageLimit;
                                        ?>
                                        <tr class="<?= ($isExpired || $isLimitReached) && $discount['is_active'] == 1 ? 'table-warning' : '' ?>">
                                            <td><?= $index + 1 ?></td>
                                            <td>
                                                <?= esc($discount['name'] ?? '-') ?>
                                            </td>
                                            <td>
                                                <?php if ($isProductType): ?>
                                                    <span class="badge bg-warning text-dark">Produk</span>
                                                <?php else: ?>
                                                    <span class="badge bg-info">Subtotal</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($isProductType): ?>
                                                    <small>
                                                        <?php 
                                                        $productInfo = [];
                                                        foreach ($productIds as $pid) {
                                                            $name = esc($products_map[$pid] ?? $pid);
                                                            if (isset($productPrices[$pid])) {
                                                                $name .= ' <span class="text-success">(Rp' . number_format($productPrices[$pid], 0, ',', '.') . ')</span>';
                                                            }
                                                            $productInfo[] = $name;
                                                        }
                                                        echo count($productInfo) > 2 
                                                            ? implode('<br>', array_slice($productInfo, 0, 2)) . '<br>+' . (count($productInfo) - 2) . ' lainnya'
                                                            : implode('<br>', $productInfo);
                                                        ?>
                                                    </small>
                                                <?php else: ?>
                                                    Rp<?= number_format($discount['min_amount'] ?? 0, 0, ',', '.') ?>
                                                    <?php if (!empty($discount['max_amount'])): ?>
                                                        - Rp<?= number_format($discount['max_amount'], 0, ',', '.') ?>
                                                    <?php else: ?>
                                                        ke atas
                                                    <?php endif; ?>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($isProductType): ?>
                                                    <span class="badge bg-secondary">Harga Fixed</span>
                                                <?php else: ?>
                                                    <span class="badge bg-success">
                                                        <?= number_format($discount['discount_percentage'] ?? 0, 1) ?>%
                                                    </span>
                                                <?php endif; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($usageLimit): ?>
                                                    <span class="<?= $isLimitReached ? 'text-danger fw-bold' : '' ?>">
                                                        <?= $usageCount ?> / <?= $usageLimit ?>
                                                    </span>
                                                    <?php if ($isLimitReached): ?>
                                                        <br><small class="text-danger">Limit tercapai!</small>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <span class="text-muted"><?= $usageCount ?> (∞)</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if (!empty($discount['start_date']) || !empty($discount['end_date'])): ?>
                                                    <small>
                                                        <?php if (!empty($discount['start_date'])): ?>
                                                            <?= date('d/m/Y', strtotime($discount['start_date'])) ?>
                                                        <?php else: ?>
                                                            -
                                                        <?php endif; ?>
                                                        s/d
                                                        <?php if (!empty($discount['end_date'])): ?>
                                                            <?= date('d/m/Y', strtotime($discount['end_date'])) ?>
                                                            <?php if ($isExpired): ?>
                                                                <br><span class="text-danger">Berakhir</span>
                                                            <?php endif; ?>
                                                        <?php else: ?>
                                                            ∞
                                                        <?php endif; ?>
                                                    </small>
                                                <?php else: ?>
                                                    <span class="text-muted">Tanpa batas</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($discount['is_active'] == 1): ?>
                                                    <?php if ($isLimitReached): ?>
                                                        <span class="badge bg-secondary">Limit Habis</span>
                                                    <?php elseif ($isExpired): ?>
                                                        <span class="badge bg-secondary">Expired</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-success">Aktif</span>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">Non-Aktif</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="<?= base_url('admin/discounts/edit/' . $discount['discount_id']) ?>" 
                                                       class="btn btn-sm btn-warning" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <a href="<?= base_url('admin/discounts/delete/' . $discount['discount_id']) ?>" 
                                                       class="btn btn-sm btn-danger" title="Hapus"
                                                       onclick="return confirm('Yakin ingin menghapus aturan diskon ini?')">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="9" class="text-center">Belum ada aturan diskon.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
