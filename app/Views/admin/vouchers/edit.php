<?= $this->extend('admin/layout/main') ?>

<?= $this->section('title') ?>Edit Voucher<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Edit Voucher</h3>
                </div>
                <div class="card-body">
                    <?php if (session()->getFlashdata('errors')): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach (session()->getFlashdata('errors') as $error): ?>
                                    <li><?= esc($error) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <?php if (session()->getFlashdata('error')): ?>
                        <div class="alert alert-danger">
                            <?= session()->getFlashdata('error') ?>
                        </div>
                    <?php endif; ?>

                    <form action="/admin/vouchers/update/<?= $voucher['id'] ?>" method="post">
                        <?= csrf_field() ?>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="code" class="form-label">Kode Voucher</label>
                                    <input type="text"
                                           class="form-control"
                                           id="code"
                                           name="code"
                                           value="<?= old('code', $voucher['code']) ?>"
                                           required>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Nama Voucher</label>
                                    <input type="text"
                                           class="form-control"
                                           id="name"
                                           name="name"
                                           value="<?= old('name', $voucher['name']) ?>"
                                           required>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="site" class="form-label">Berlaku Untuk</label>
                                    <?php $selectedSite = old('site', $voucher['site'] ?? 'all'); ?>

                                    <select class="form-control" id="site" name="site" required>
                                        <option value="all" <?= $selectedSite === 'all' ? 'selected' : '' ?>>Semua Website</option>
                                        <option value="jsflorist" <?= $selectedSite === 'jsflorist' ? 'selected' : '' ?>>JS Florist</option>
                                        <option value="poppyflorist" <?= $selectedSite === 'poppyflorist' ? 'selected' : '' ?>>Poppy Florist</option>
                                    </select>

                                    <small class="text-muted">
                                        Pilih website yang boleh menggunakan voucher ini.
                                    </small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="discount_type" class="form-label">Tipe Diskon</label>
                                    <select class="form-control" id="discount_type" name="discount_type" required>
                                        <option value="percent" <?= old('discount_type', $voucher['discount_type']) === 'percent' ? 'selected' : '' ?>>Persen</option>
                                        <option value="fixed" <?= old('discount_type', $voucher['discount_type']) === 'fixed' ? 'selected' : '' ?>>Nominal</option>
                                        <option value="free_shipping" <?= old('discount_type', $voucher['discount_type']) === 'free_shipping' ? 'selected' : '' ?>>Gratis Ongkir</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="discount_value" class="form-label">Nilai Diskon</label>
                                    <input type="number"
                                           class="form-control"
                                           id="discount_value"
                                           name="discount_value"
                                           value="<?= old('discount_value', $voucher['discount_value']) ?>"
                                           step="0.01"
                                           min="0"
                                           required>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="points_cost" class="form-label">
                                        Biaya Poin <small class="text-muted">(Opsional)</small>
                                    </label>
                                    <input type="number"
                                           class="form-control"
                                           id="points_cost"
                                           name="points_cost"
                                           value="<?= old('points_cost', $voucher['points_cost'] ?? '') ?>"
                                           min="0"
                                           placeholder="Kosongkan jika tidak pakai poin">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="min_amount" class="form-label">Minimal Pembelian (Rp)</label>
                                    <input type="number"
                                           class="form-control"
                                           id="min_amount"
                                           name="min_amount"
                                           value="<?= old('min_amount', $voucher['min_amount']) ?>"
                                           step="1000"
                                           min="0">
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="max_amount" class="form-label">Maksimal Pembelian (Rp)</label>
                                    <input type="number"
                                           class="form-control"
                                           id="max_amount"
                                           name="max_amount"
                                           value="<?= old('max_amount', $voucher['max_amount']) ?>"
                                           step="1000"
                                           min="0">
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="usage_limit" class="form-label">Batas Penggunaan</label>
                                    <input type="number"
                                           class="form-control"
                                           id="usage_limit"
                                           name="usage_limit"
                                           value="<?= old('usage_limit', $voucher['usage_limit']) ?>"
                                           min="0">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="expires_at" class="form-label">Tanggal Kadaluarsa</label>
                                    <input type="datetime-local"
                                           class="form-control"
                                           id="expires_at"
                                           name="expires_at"
                                           value="<?= old('expires_at', $voucher['expires_at'] ? date('Y-m-d\TH:i', strtotime($voucher['expires_at'])) : '') ?>">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="is_active" class="form-label">Status</label>
                                    <select class="form-control" id="is_active" name="is_active" required>
                                        <option value="1" <?= old('is_active', $voucher['is_active']) == 1 ? 'selected' : '' ?>>Aktif</option>
                                        <option value="0" <?= old('is_active', $voucher['is_active']) == 0 ? 'selected' : '' ?>>Non-Aktif</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="/admin/vouchers" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Kembali
                            </a>

                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Update Voucher
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>