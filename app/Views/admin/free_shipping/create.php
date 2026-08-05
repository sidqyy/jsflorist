<?= $this->extend('admin/layout/main') ?>
<?= $this->section('title') ?>Tambah Gratis Ongkir - <?= esc($store['name'] ?? 'Admin') ?><?= $this->endSection() ?>
<?= $this->section('content') ?>
<div class="container py-3">
    <h3>Tambah Rule Gratis Ongkir</h3>
    
    <?php if (session()->getFlashdata('errors')): ?>
        <div class="alert alert-danger">
            <ul>
                <?php foreach(session('errors') as $e): ?><li><?= esc($e) ?></li><?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="post" action="<?= base_url('admin/free-shipping/store') ?>">
        <?= csrf_field() ?>
        
        <div class="row">
            <!-- Kolom Kiri: Aturan Harga -->
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Minimal Subtotal (Rp)</label>
                    <input type="number" step="0.01" name="min_amount" class="form-control" required value="<?= old('min_amount') ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label">Maksimal Subtotal (Rp) - <small class="text-muted">opsional</small></label>
                    <input type="number" step="0.01" name="max_amount" class="form-control" value="<?= old('max_amount') ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label">Maksimal Jarak (km) - <small class="text-muted">opsional</small></label>
                    <input type="number" step="0.01" name="max_distance_km" class="form-control" value="<?= old('max_distance_km') ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label">Status</label>
                    <select name="is_active" class="form-select">
                        <option value="1" <?= old('is_active','1')=='1'?'selected':'' ?>>Aktif</option>
                        <option value="0" <?= old('is_active')=='0'?'selected':'' ?>>Nonaktif</option>
                    </select>
                </div>
            </div>

            <!-- Kolom Kanan: Waktu & Produk -->
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Tanggal Mulai</label>
                    <input type="datetime-local" name="start_date" class="form-control" required value="<?= old('start_date') ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label">Tanggal Berakhir</label>
                    <input type="datetime-local" name="end_date" class="form-control" required value="<?= old('end_date') ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label d-block">Berlaku Untuk</label>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="apply_to_all" id="apply_all" value="1" <?= old('apply_to_all', '1') == '1' ? 'checked' : '' ?>>
                        <label class="form-check-label" for="apply_all">Semua Produk</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="apply_to_all" id="apply_specific" value="0" <?= old('apply_to_all') == '0' ? 'checked' : '' ?>>
                        <label class="form-check-label" for="apply_specific">Produk Tertentu</label>
                    </div>
                </div>

                <!-- Daftar Produk (Hidden by default jika Semua Produk dipilih) -->
                <div id="product_selection" class="mb-3" style="display: <?= old('apply_to_all') == '0' ? 'block' : 'none' ?>;">
                    <label class="form-label">Pilih Produk</label>
                    <div class="border rounded p-3" style="max-height: 200px; overflow-y: auto;">
                        <?php if(!empty($products)): ?>
                            <?php foreach($products as $p): ?>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="product_ids[]" value="<?= $p['product_id'] ?>" id="prod_<?= $p['product_id'] ?>" <?= is_array(old('product_ids')) && in_array($p['product_id'], old('product_ids')) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="prod_<?= $p['product_id'] ?>">
                                        <?= esc($p['nama_produk']) ?>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <small class="text-danger">Belum ada data produk.</small>
                        <?php endif; ?>
                    </div>
                    <small class="text-muted">Ceklis produk yang mendapatkan promo gratis ongkir.</small>
                </div>
            </div>
        </div>

        <hr>
        <div class="mt-3">
            <button class="btn btn-primary">Simpan Rule</button>
            <a href="<?= base_url('admin/free-shipping') ?>" class="btn btn-secondary">Batal</a>
        </div>
    </form>
</div>

<!-- Script untuk toggle tampilan produk -->
<script>
    const radioAll = document.getElementById('apply_all');
    const radioSpecific = document.getElementById('apply_specific');
    const productBox = document.getElementById('product_selection');

    function toggleProductSelection() {
        if (radioSpecific.checked) {
            productBox.style.display = 'block';
        } else {
            productBox.style.display = 'none';
        }
    }

    radioAll.addEventListener('change', toggleProductSelection);
    radioSpecific.addEventListener('change', toggleProductSelection);
</script>

<?= $this->endSection() ?>