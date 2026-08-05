<?= $this->extend('admin/layout/main') ?>

<?= $this->section('title') ?>Tambah Aturan Bonus<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Tambah Aturan Bonus Baru</h3>
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

                    <form action="<?= base_url('admin/bonus/rules/store') ?>" method="post" id="bonusForm">
                        <?= csrf_field() ?>
                        
                        <div class="mb-3">
                            <label for="rule_name" class="form-label">Nama Aturan Promo <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control" 
                                   id="rule_name" 
                                   name="rule_name" 
                                   value="<?= old('rule_name') ?>" 
                                   placeholder="Contoh: Promo Ferrero Rocher Mei, Bonus Kejutan Ramadhan, dll"
                                   required>
                        </div>

                        <div class="mb-3">
                            <label for="bonus_item_name" class="form-label">Nama Item Bonus / Hadiah <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control" 
                                   id="bonus_item_name" 
                                   name="bonus_item_name" 
                                   value="<?= old('bonus_item_name') ?>" 
                                   placeholder="Contoh: Ferrero Rocher, Boneka Beruang, dll"
                                   required>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Pilih Produk yang Berlaku untuk Promo Ini <span class="text-danger">*</span></label>
                            
                            <div class="row g-2 mb-2 align-items-center">
                                <div class="col-md-4">
                                    <select class="form-control form-control-sm" id="filterCategory">
                                        <option value="all">-- Semua Kategori --</option>
                                        <option value="NULL">-- Produk Tanpa Kategori (NULL) --</option>
                                        <?php if (!empty($categories)): ?>
                                            <?php foreach ($categories as $cat): ?>
                                                <?php 
                                                    $cId = is_object($cat) ? ($cat->category_id ?? $cat->id ?? '') : ($cat['category_id'] ?? $cat['id'] ?? '');
                                                    $cNama = is_object($cat) ? ($cat->nama_kategori ?? $cat->name ?? '') : ($cat['nama_kategori'] ?? $cat['name'] ?? '');
                                                ?>
                                                <option value="<?= $cId ?>"><?= esc($cNama) ?></option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                </div>
                                
                                <div class="col-md-4">
                                    <div class="form-check pt-1">
                                        <input class="form-check-input" type="checkbox" id="selectAllVisible">
                                        <label class="form-check-label fw-bold text-primary" for="selectAllVisible" style="cursor: pointer;">
                                            Pilih Semua yang Tampil
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="table-responsive border rounded" style="max-height: 300px; overflow-y: auto;">
                                <table class="table table-sm table-bordered table-striped table-hover mb-0" id="productChecklistTable">
                                    <thead class="table-dark sticky-top">
                                        <tr>
                                            <th style="width: 60px;" class="text-center">Pilih</th>
                                            <th>Nama Produk</th>
                                            <th style="width: 150px;" class="text-center">Harga Asli</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($products)): ?>
                                            <?php foreach ($products as $product): ?>
                                                <?php 
                                                    // PERBAIKAN SINKRONISASI: Membaca kolom product_id sesuai struktur phpMyAdmin kamu
                                                    $pId = is_object($product) ? ($product->product_id ?? '') : ($product['product_id'] ?? '');
                                                    $pNama = is_object($product) ? ($product->nama_produk ?? $product->name ?? 'Produk Tanpa Nama') : ($product['nama_produk'] ?? $product['name'] ?? 'Produk Tanpa Nama');
                                                    $pHarga = is_object($product) ? ($product->harga ?? $product->price ?? 0) : ($product['harga'] ?? $product['price'] ?? 0);
                                                    
                                                    $pCatId = is_object($product) ? ($product->category_id ?? $product->sub_category_id ?? 'NULL') : ($product['category_id'] ?? $product['sub_category_id'] ?? 'NULL');
                                                    if ($pCatId === null || $pCatId === '') $pCatId = 'NULL';
                                                ?>
                                                <tr class="product-row" data-category="<?= $pCatId ?>" style="cursor: pointer;">
                                                    <td class="text-center">
                                                        <input class="form-check-input prod-checkbox" type="checkbox" name="product_ids[]" value="<?= trim((string)$pId) ?>"> 
                                                    </td>
                                                    <td class="prod-name">
                                                        <?= esc($pNama) ?>
                                                    </td>
                                                    <td class="text-end fw-bold px-3">
                                                        Rp<?= number_format($pHarga, 0, ',', '.') ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="3" class="text-center text-muted py-3">Tidak ada data produk aktif di database.</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="border rounded p-3 mb-3 bg-light">
                            <h5 class="mb-3"><i class="fas fa-layer-group"></i> Pengaturan Aturan Rentang Harga & Jumlah Hadiah</h5>
                            <div id="tier_container">
                                <div class="row g-2 align-items-center mb-2 tier-row">
                                    <div class="col-md-5">
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text">Harga &ge; Rp</span>
                                            <input type="number" class="form-control" name="min_price[]" placeholder="Contoh: 200000" min="0" required>
                                        </div>
                                    </div>
                                    <div class="col-md-5">
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text">Dapat Hadiah:</span>
                                            <input type="number" class="form-control" name="bonus_amount[]" placeholder="Jumlah pcs" min="1" required>
                                            <span class="input-group-text">Pcs</span>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <button type="button" class="btn btn-sm btn-danger w-100 remove-tier-btn" disabled>
                                            <i class="fas fa-trash"></i> Hapus
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <button type="button" class="btn btn-sm btn-success mt-2" id="addTierBtn">
                                <i class="fas fa-plus"></i> Tambah Aturan Rentang Harga
                            </button>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="quota_limit" class="form-label">Kuota Promo (Orang Tercepat) <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="quota_limit" name="quota_limit" value="<?= old('quota_limit', '5') ?>" min="1" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="is_active" class="form-label">Status</label>
                                    <select class="form-control" id="is_active" name="is_active" required>
                                        <option value="1" <?= old('is_active', '1') == '1' ? 'selected' : '' ?>>Aktif</option>
                                        <option value="0" <?= old('is_active') == '0' ? 'selected' : '' ?>>Non-Aktif</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="start_date" class="form-label">Tanggal Mulai <span class="text-danger">*</span></label>
                                    <input type="datetime-local" class="form-control" id="start_date" name="start_date" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="end_date" class="form-label">Tanggal Berakhir <span class="text-danger">*</span></label>
                                    <input type="datetime-local" class="form-control" id="end_date" name="end_date" required>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between mt-4">
                            <a href="<?= base_url('admin/bonus/rules') ?>" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Kembali
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Simpan Aturan Bonus
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const tierContainer = document.getElementById('tier_container');
    const addTierBtn = document.getElementById('addTierBtn');
    const filterCategory = document.getElementById('filterCategory');
    const selectAllCheck = document.getElementById('selectAllVisible');

    document.querySelectorAll('.product-row').forEach(row => {
        row.addEventListener('click', function(e) {
            if (e.target.type !== 'checkbox') {
                const cb = this.querySelector('.prod-checkbox');
                if (cb) {
                    cb.checked = !cb.checked;
                    cb.dispatchEvent(new Event('change'));
                }
            }
        });
    });

    if (filterCategory) {
        filterCategory.addEventListener('change', function() {
            const selectedCategory = String(this.value); 
            const productRows = document.querySelectorAll('.product-row');
            if (selectAllCheck) selectAllCheck.checked = false;

            productRows.forEach(row => {
                const rowCategory = String(row.getAttribute('data-category')); 
                if (selectedCategory === 'all' || rowCategory === selectedCategory) {
                    row.style.display = ''; 
                } else {
                    row.style.display = 'none'; 
                }
            });
        });
    }

    if (selectAllCheck) {
        selectAllCheck.addEventListener('change', function() {
            const isChecked = this.checked;
            document.querySelectorAll('.product-row').forEach(row => {
                if (row.style.display !== 'none') {
                    const cb = row.querySelector('.prod-checkbox');
                    if (cb) {
                        cb.checked = isChecked;
                        cb.dispatchEvent(new Event('change'));
                    }
                }
            });
        });
    }

    if (addTierBtn) {
        addTierBtn.addEventListener('click', function() {
            const rows = document.querySelectorAll('.tier-row');
            if (rows.length > 0 && tierContainer) {
                const newRow = rows[0].cloneNode(true);
                newRow.querySelectorAll('input').forEach(input => input.value = '');
                const removeBtn = newRow.querySelector('.remove-tier-btn');
                if (removeBtn) {
                    removeBtn.removeAttribute('disabled');
                    removeBtn.addEventListener('click', function() { newRow.remove(); });
                }
                tierContainer.appendChild(newRow);
            }
        });
    }
});
</script>