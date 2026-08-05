<?= $this->extend('admin/layout/main') ?>

<?= $this->section('title') ?>Kelola Voucher<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Kelola Voucher</h3>
                    <a href="/admin/vouchers/create" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Tambah Voucher
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

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Kode</th>
                                    <th>Nama</th>
                                    <th>Website</th>
                                    <th>Tipe</th>
                                    <th>Nilai</th>
                                    <th>Biaya Poin</th>
                                    <th>Masa Berlaku</th>
                                    <th>Penggunaan</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>

                            <tbody>
                                <?php if (!empty($vouchers)): ?>
                                    <?php foreach ($vouchers as $index => $voucher): ?>
                                        <?php
                                            $site = $voucher['site'] ?? 'all';

                                            if ($site === 'jsflorist') {
                                                $siteLabel = 'JS Florist';
                                                $siteClass = 'bg-primary';
                                            } elseif ($site === 'poppyflorist') {
                                                $siteLabel = 'Poppy Florist';
                                                $siteClass = 'bg-success';
                                            } else {
                                                $siteLabel = 'Semua Website';
                                                $siteClass = 'bg-dark';
                                            }
                                        ?>

                                        <tr>
                                            <td><?= $index + 1 ?></td>

                                            <td>
                                                <strong><?= esc($voucher['code']) ?></strong>
                                            </td>

                                            <td><?= esc($voucher['name']) ?></td>

                                            <td>
                                                <span class="badge <?= esc($siteClass) ?>">
                                                    <?= esc($siteLabel) ?>
                                                </span>
                                            </td>

                                            <td><?= esc($voucher['discount_type']) ?></td>

                                            <td>
                                                <?php if ($voucher['discount_type'] === 'percent'): ?>
                                                    <?= number_format($voucher['discount_value'], 1) ?>%
                                                <?php elseif ($voucher['discount_type'] === 'fixed'): ?>
                                                    Rp<?= number_format($voucher['discount_value'], 0, ',', '.') ?>
                                                <?php else: ?>
                                                    Gratis Ongkir
                                                <?php endif; ?>
                                            </td>

                                            <td><?= (int) ($voucher['points_cost'] ?? 0) ?></td>

                                            <td>
                                                <?= !empty($voucher['expires_at']) ? date('d/m/Y H:i', strtotime($voucher['expires_at'])) : 'Tidak ada' ?>
                                            </td>

                                            <td>
                                                <?= (int) ($voucher['used_count'] ?? 0) ?>
                                                <?php if (!empty($voucher['usage_limit'])): ?>
                                                    / <?= (int) $voucher['usage_limit'] ?>
                                                <?php else: ?>
                                                    / ∞
                                                <?php endif; ?>
                                            </td>

                                            <td>
                                                <span class="badge <?= (int) $voucher['is_active'] === 1 ? 'bg-success' : 'bg-secondary' ?>">
                                                    <?= (int) $voucher['is_active'] === 1 ? 'Aktif' : 'Non-Aktif' ?>
                                                </span>
                                            </td>

                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="/admin/vouchers/edit/<?= $voucher['id'] ?>" class="btn btn-sm btn-warning">
                                                        <i class="fas fa-edit"></i> Edit
                                                    </a>

                                                    <a href="/admin/vouchers/toggle-status/<?= $voucher['id'] ?>" class="btn btn-sm btn-secondary">
                                                        <i class="fas fa-toggle-on"></i> Toggle
                                                    </a>

                                                    <a href="/admin/vouchers/delete/<?= $voucher['id'] ?>"
                                                       class="btn btn-sm btn-danger"
                                                       onclick="return confirm('Yakin ingin menghapus voucher ini?')">
                                                        <i class="fas fa-trash"></i> Hapus
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="11" class="text-center">Belum ada voucher.</td>
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