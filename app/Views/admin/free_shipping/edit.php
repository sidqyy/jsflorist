<?= $this->extend('admin/layout/main') ?>
<?= $this->section('title') ?>Edit Gratis Ongkir - <?= esc($store['name'] ?? 'Admin') ?><?= $this->endSection() ?>
<?= $this->section('content') ?>
<div class="container py-3">
    <h3>Edit Rule Gratis Ongkir</h3>
    
    <?php if (session()->getFlashdata('errors')): ?>
        <div class="alert alert-danger">
            <ul>
                <?php foreach(session('errors') as $e): ?><li><?= esc($e) ?></li><?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="post" action="<?= base_url('admin/free-shipping/update/' . $rule['rule_id']) ?>">
        <?= csrf_field() ?>
        
        <div class="row">
            <!-- Kolom Kiri: Aturan Harga -->
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Minimal Subtotal (Rp)</label>
                    <input type="number" step="0.01" name="min_amount" class="form-control" required value="<?= old('min_amount', $rule['min_amount']) ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label">Maksimal Subtotal (Rp) - <small class="text-muted">opsional</small></label>
                    <input type="number" step="0.01" name="max_amount" class="form-control" value="<?= old('max_amount', $rule['max_amount']) ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label">Maksimal Jarak (km) - <small class="text-muted">opsional</small></label>
                    <input type="number" step="0.01" name="max_distance_km" class="form-control" value="<?= old('max_distance_km', $rule['max_distance_km']) ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label">Status</label>
                    <select name="is_active" class="form-select">
                        <option value="1" <?= old('is_active', (string)$rule['is_active'])=='1'?'selected':'' ?>>Aktif</option>
                        <option value="0" <?= old('is_active', (string)$rule['is_active'])=='0'?'selected':'' ?>>Nonaktif</option>
                    </select>
                </div>
            </div>

            <!-- Kolom Kanan: Waktu & Produk -->
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Tanggal Mulai</label>
                    <?php 
                        // Memastikan format tanggal cocok dengan input datetime-local (YYYY-MM-DDTHH:MM)
                        $startDate = $rule['start_date'] ? date('Y-m-d\TH:i', strtotime($rule['start_date'])) : '';
                        $endDate = $rule['end_date'] ? date('Y-m-d\TH:i', strtotime($rule['end_date'])) : '';
                    ?>
                    <input type="datetime-local" name="start_date" class="form-control" required value="<?= old('start_date', $startDate) ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label">Tanggal Berakhir</label>
                    <input type="datetime-local" name="end_date" class="form-control" required value="<?= old('end_date', $endDate) ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label d-block">Berlaku Untuk</label>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="apply_to_all" id="apply_all" value="1" <?= old('apply_to_all', (string)$rule['apply_to_all']) == '1' ? 'checked' : '' ?>>
                        <label class="form-check-label" for="apply_all">Semua Produk</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="apply_to_all" id="apply_specific" value="0" <?= old('apply_to_all', (string)$rule['apply_to_all']) == '0' ? 'checked' : '' ?>>
                        <label class="form-check-label" for="apply_specific">Produk Tertentu</label>
                    </div>
                </div>

                <!-- Daftar Produk -->
                <div id="product_selection" class="mb-3" style="display: <?= old('apply_to_all', (string)$rule['apply_to_all']) == '0' ? 'block' : 'none' ?>;">
                    <label class="form-label">Pilih Produk</label>
                    <div class="border rounded p-3" style="max-height: 200px; overflow-y: auto;">
                        <?php if(!empty($products)): ?>
                            <?php foreach($products as $p): ?>
                                <div class="form-check">
                                    <?php 
                                        // Cek apakah ID produk ada di array selectedProducts (dari DB) atau old input (flash data)
                                        $isChecked = false;
                                        if (is_array(old('product_ids'))) {
                                            $isChecked = in_array($p['product_id'], old('product_ids'));
                                        } else {
                                            $isChecked = in_array($p['product_id'], $selectedProducts);
                                        }
                                    ?>
                                    <input class="form-check-input" type="checkbox" name="product_ids[]" value="<?= $p['product_id'] ?>" id="prod_<?= $p['product_id'] ?>" <?= $isChecked ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="prod_<?= $p['product_id'] ?>">
                                        <?= esc($p['nama_produk']) ?>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <small class="text-danger">Belum ada data produk.</small>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <hr>
        <div class="mt-3">
            <button class="btn btn-primary">Perbarui Rule</button>
            <a href="<?= base_url('admin/free-shipping') ?>" class="btn btn-secondary">Batal</a>
        </div>
    </form>
</div>

<script>
    const radioAll = document.getElementById('apply_all');
    const radioSpecific = document.getElementById('apply_specific');
    const productBox = document.getElementById('product_selection');

    function toggleProductSelection() {
        productBox.style.display = radioSpecific.checked ? 'block' : 'none';
    }

    radioAll.addEventListener('change', toggleProductSelection);
    radioSpecific.addEventListener('change', toggleProductSelection);
</script>
<?= $this->endSection() ?>