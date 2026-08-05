<?= $this->extend('admin/layout/main') ?>

<?= $this->section('title') ?>Kelola Bonus Promo<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Kelola Bonus Promo</h3>
                    <a href="<?= base_url('admin/bonus/rules/create') ?>" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Tambah Aturan Bonus
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

                    <div class="mb-3">
                        <span class="badge bg-gift text-white me-2" style="background-color: #6f42c1;">Bonus Item</span> = Hadiah tambahan otomatis berdasarkan nominal harga produk
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama Promo</th>
                                    <th>Hadiah / Item</th>
                                    <th>Ketentuan Rentang Harga (JSON Config)</th>
                                    <th>Penggunaan Kuota</th>
                                    <th>Periode</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($rules)): ?>
                                    <?php foreach ($rules as $index => $rule): ?>
                                        <?php 
                                            $quotaLimit = $rule['quota_limit'] ?? null;
                                            $usageCount = (int)($rule['usage_count'] ?? 0);
                                            $isLimitReached = $quotaLimit && $usageCount >= $quotaLimit;
                                            $isExpired = false;
                                            
                                            // Cek validitas tanggal
                                            $now = date('Y-m-d H:i:s');
                                            if (!empty($rule['end_date']) && $now > $rule['end_date']) {
                                                $isExpired = true;
                                            }
                                            if (!empty($rule['start_date']) && $now < $rule['start_date']) {
                                                $isExpired = true; // Belum masuk periode mulai
                                            }

                                            // Parse JSON config agar terlihat rapi di tabel admin
                                            $tieringData = json_decode($rule['bonus_config'] ?? '[]', true) ?? [];
                                            krsort($tieringData); // Urutkan dari yang terbesar ke terkecil
                                        ?>
                                        <tr class="<?= ($isExpired || $isLimitReached) && $rule['is_active'] == 1 ? 'table-warning' : '' ?>">
                                            <td><?= $index + 1 ?></td>
                                            <td>
                                                <strong><?= esc($rule['rule_name'] ?? '-') ?></strong>
                                            </td>
                                            <td>
                                                <span class="badge bg-purple" style="background-color: #6f42c1; color: white;">
                                                    <?= esc($rule['bonus_item_name'] ?? '-') ?>
                                                </span>
                                            </td>
                                            <td>
                                                <small>
                                                    <?php if (!empty($tieringData)): ?>
                                                        <?php foreach ($tieringData as $minPrice => $amount): ?>
                                                            • Harga $\ge$ Rp<?= number_format($minPrice, 0, ',', '.') ?> &rarr; <strong><?= $amount ?> pcs</strong><br>
                                                        <?php endforeach; ?>
                                                    <?php else: ?>
                                                        <span class="text-muted">Konfigurasi kosong</span>
                                                    <?php endif; ?>
                                                </small>
                                            </td>
                                            <td>
                                                <span class="<?= $isLimitReached ? 'text-danger fw-bold' : '' ?>">
                                                    <?= $usageCount ?> / <?= $quotaLimit ?>
                                                </span>
                                                <?php if ($isLimitReached): ?>
                                                    <br><small class="text-danger">Limit tercapai!</small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if (!empty($rule['start_date']) || !empty($rule['end_date'])): ?>
                                                    <small>
                                                        <?= !empty($rule['start_date']) ? date('d/m/Y', strtotime($rule['start_date'])) : '-' ?>
                                                        s/d
                                                        <?= !empty($rule['end_date']) ? date('d/m/Y', strtotime($rule['end_date'])) : '∞' ?>
                                                        <?php if ($isExpired): ?>
                                                            <br><span class="text-danger">Sudah Berakhir/Belum Mulai</span>
                                                        <?php endif; ?>
                                                    </small>
                                                <?php else: ?>
                                                    <span class="text-muted">Tanpa batas</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($rule['is_active'] == 1): ?>
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
                                                    <a href="<?= base_url('admin/bonus/rules/edit/' . $rule['bonus_id']) ?>" 
                                                       class="btn btn-sm btn-warning" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <a href="<?= base_url('admin/bonus/rules/delete/' . $rule['bonus_id']) ?>" 
                                                       class="btn btn-sm btn-danger" title="Hapus"
                                                       onclick="return confirm('Yakin ingin menghapus aturan bonus ini?')">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="8" class="text-center">Belum ada aturan bonus promo.</td>
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