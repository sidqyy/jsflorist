<?= $this->extend('admin/layout/main') ?>

<?= $this->section('title') ?>Edit Aturan Diskon<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Edit Aturan Diskon</h3>
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

                    <?php 
                    $currentType = old('discount_type', $discount['discount_type'] ?? 'subtotal');
                    $usageCount = (int)($discount['usage_count'] ?? 0);
                    $usageLimit = $discount['usage_limit'] ?? null;
                    
                    // Parse selected_products
                    $selectedProducts = [];
                    $productPrices = [];
                    if (!empty($discount['product_ids'])) {
                        $productData = json_decode($discount['product_ids'], true) ?? [];
                        foreach ($productData as $productId => $info) {
                            if (is_array($info) && isset($info['discounted_price'])) {
                                $selectedProducts[] = $productId;
                                $productPrices[$productId] = $info['discounted_price'];
                            } else {
                                $selectedProducts[] = $info;
                            }
                        }
                    }
                    ?>

                    <form action="<?= base_url('admin/discounts/update/' . $discount['discount_id']) ?>" method="post" novalidate>
                        <?= csrf_field() ?>
                        
                        <div class="mb-3">
                            <label for="name" class="form-label">Nama Aturan Diskon <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control" 
                                   id="name" 
                                   name="name" 
                                   value="<?= old('name', $discount['name'] ?? '') ?>" 
                                   placeholder="Contoh: Promo Tahun Baru 2026"
                                   required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Tipe Diskon <span class="text-danger">*</span></label>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="discount_type" id="type_subtotal" 
                                       value="subtotal" <?= $currentType === 'subtotal' ? 'checked' : '' ?>>
                                <label class="form-check-label" for="type_subtotal">
                                    <strong>Subtotal</strong> - Diskon berdasarkan total belanja (persentase)
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="discount_type" id="type_product" 
                                       value="product" <?= $currentType === 'product' ? 'checked' : '' ?>>
                                <label class="form-check-label" for="type_product">
                                    <strong>Produk</strong> - Diskon untuk produk tertentu (harga spesifik)
                                </label>
                            </div>
                        </div>

                        <div id="subtotal_section" class="border rounded p-3 mb-3 bg-light">
                            <h5 class="mb-3"><i class="fas fa-calculator"></i> Pengaturan Diskon Subtotal</h5>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="min_amount" class="form-label">Minimal Pembelian (Rp)</label>
                                        <input type="number" class="form-control" id="min_amount" name="min_amount" value="<?= old('min_amount', $discount['min_amount'] ?? '') ?>" step="1000" min="0">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="max_amount" class="form-label">Maksimal Pembelian (Rp)</label>
                                        <input type="number" class="form-control" id="max_amount" name="max_amount" value="<?= old('max_amount', $discount['max_amount'] ?? '') ?>" step="1000" min="0">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="discount_percentage" class="form-label">Persentase Diskon (%)</label>
                                        <input type="number" class="form-control" id="discount_percentage" name="discount_percentage" value="<?= old('discount_percentage', $discount['discount_percentage'] ?? '') ?>" step="0.1" min="0.1" max="100">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div id="product_section" class="border rounded p-3 mb-3 bg-light" style="display:none;">
                            <h5 class="mb-3"><i class="fas fa-tags"></i> Pengaturan Diskon Produk</h5>
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover" id="product_table">
                                    <thead class="table-secondary">
                                        <tr>
                                            <th style="width: 50px;">Pilih</th>
                                            <th>Nama Produk</th>
                                            <th style="width: 180px;">Harga Asli</th>
                                            <th style="width: 200px;">Harga Setelah Diskon</th>
                                            <th style="width: 100px;">Potongan</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($products)): ?>
                                            <?php 
                                            $oldProductIds = old('product_ids') ?? $selectedProducts;
                                            $oldPrices = old('product_prices') ?? $productPrices;
                                            foreach ($products as $product): 
                                                $isChecked = in_array($product['product_id'], $oldProductIds);
                                                $valPrice = $oldPrices[$product['product_id']] ?? '';
                                            ?>
                                                <tr class="product-row">
                                                    <td class="text-center">
                                                        <input class="form-check-input product-checkbox" type="checkbox" 
                                                               name="product_ids[]" 
                                                               value="<?= esc($product['product_id']) ?>" 
                                                               data-product-id="<?= esc($product['product_id']) ?>"
                                                               id="product_<?= esc($product['product_id']) ?>"
                                                               <?= $isChecked ? 'checked' : '' ?>>
                                                    </td>
                                                    <td><label for="product_<?= esc($product['product_id']) ?>" class="mb-0 cursor-pointer"><?= esc($product['nama_produk']) ?></label></td>
                                                    <td class="text-end"><strong>Rp<?= number_format($product['harga'], 0, ',', '.') ?></strong></td>
                                                    <td>
                                                        <input type="number" 
                                                               class="form-control form-control-sm discounted-price-input <?= !$isChecked ? 'bg-light' : '' ?>"
                                                               name="product_prices[<?= esc($product['product_id']) ?>]"
                                                               data-product-id="<?= esc($product['product_id']) ?>"
                                                               data-original-price="<?= $product['harga'] ?>"
                                                               value="<?= esc($valPrice) ?>"
                                                               min="0" max="<?= $product['harga'] ?>" step="1000"
                                                               <?= !$isChecked ? 'readonly' : '' ?>>
                                                    </td>
                                                    <td class="text-center discount-display" data-product-id="<?= esc($product['product_id']) ?>">-</td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="is_active" class="form-label">Status</label>
                                <select class="form-control" id="is_active" name="is_active" required>
                                    <option value="1" <?= old('is_active', $discount['is_active']) == '1' ? 'selected' : '' ?>>Aktif</option>
                                    <option value="0" <?= old('is_active', $discount['is_active']) == '0' ? 'selected' : '' ?>>Non-Aktif</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="usage_limit" class="form-label">Batas Penggunaan</label>
                                <input type="number" class="form-control" id="usage_limit" name="usage_limit" value="<?= old('usage_limit', $discount['usage_limit'] ?? '') ?>" min="1" placeholder="Tanpa batas">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Statistik (<?= $usageCount ?>x digunakan)</label>
                                <?php if ($usageCount > 0): ?>
                                    <div class="form-check mt-2">
                                        <input class="form-check-input" type="checkbox" name="reset_usage" value="1" id="reset_usage">
                                        <label class="form-check-label text-danger" for="reset_usage">Reset hitungan ke 0</label>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="start_date" class="form-label">Tanggal Mulai</label>
                                <input type="datetime-local" class="form-control" id="start_date" name="start_date" value="<?= old('start_date', $discount['start_date'] ? date('Y-m-d\TH:i', strtotime($discount['start_date'])) : '') ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="end_date" class="form-label">Tanggal Berakhir</label>
                                <input type="datetime-local" class="form-control" id="end_date" name="end_date" value="<?= old('end_date', $discount['end_date'] ? date('Y-m-d\TH:i', strtotime($discount['end_date'])) : '') ?>">
                            </div>
                        </div>

                        <div class="d-flex justify-content-between mt-4">
                            <a href="<?= base_url('admin/discounts') ?>" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
                            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan Perubahan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.cursor-pointer { cursor: pointer; }
.product-row:hover { background-color: #f8f9fa; }
.discount-display.text-success { font-weight: bold; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const typeSubtotal = document.getElementById('type_subtotal');
    const typeProduct = document.getElementById('type_product');
    const subtotalSection = document.getElementById('subtotal_section');
    const productSection = document.getElementById('product_section');
    const minInput = document.getElementById('min_amount');
    const pctInput = document.getElementById('discount_percentage');

    function toggleSections() {
    if (typeProduct.checked) {
        subtotalSection.style.display = 'none';
        productSection.style.display = 'block';
        
        // REVISI: Hapus required DAN kosongkan nilai agar tidak kena validasi min="0.1"
        minInput.removeAttribute('required');
        pctInput.removeAttribute('required');
        minInput.value = ''; 
        pctInput.value = '';
    } else {
        subtotalSection.style.display = 'block';
        productSection.style.display = 'none';
        
        minInput.setAttribute('required', 'required');
        pctInput.setAttribute('required', 'required');
    }
}
// REVISI: Tambahkan ini di bawah fungsi toggleSections()
document.querySelector('form').addEventListener('submit', function() {
    // Pastikan semua input diaktifkan sesaat sebelum kirim agar datanya masuk ke PHP
    document.querySelectorAll('.discounted-price-input').forEach(input => {
        if (input.value !== "") {
            input.readOnly = false;
        }
    });
});

    typeSubtotal.addEventListener('change', toggleSections);
    typeProduct.addEventListener('change', toggleSections);
    toggleSections();

    // Checkbox Handler: Ganti disabled menjadi readOnly agar data terkirim
    document.querySelectorAll('.product-checkbox').forEach(function(checkbox) {
        checkbox.addEventListener('change', function() {
            const productId = this.dataset.productId;
            const priceInput = document.querySelector('.discounted-price-input[data-product-id="' + productId + '"]');
            
            if (this.checked) {
                priceInput.readOnly = false;
                priceInput.classList.remove('bg-light');
                priceInput.focus();
            } else {
                priceInput.readOnly = true;
                priceInput.classList.add('bg-light');
                priceInput.value = '';
                updateDiscountDisplay(productId, 0, 0);
            }
        });
    });

    // Discount Calculation
    document.querySelectorAll('.discounted-price-input').forEach(function(input) {
        input.addEventListener('input', function() {
            const pId = this.dataset.productId;
            const original = parseFloat(this.dataset.originalPrice) || 0;
            const discounted = parseFloat(this.value) || 0;
            updateDiscountDisplay(pId, original, discounted);
        });

        if (input.value) {
            input.dispatchEvent(new Event('input'));
        }
    });

    function updateDiscountDisplay(productId, originalPrice, discountedPrice) {
        const displayEl = document.querySelector('.discount-display[data-product-id="' + productId + '"]');
        if (discountedPrice > 0 && originalPrice > 0) {
            const discount = originalPrice - discountedPrice;
            const percentage = ((discount / originalPrice) * 100).toFixed(1);
            if (discount > 0) {
                displayEl.innerHTML = '<span class="text-success">-Rp' + discount.toLocaleString('id-ID') + '</span><br><small class="text-muted">(' + percentage + '%)</small>';
            } else {
                displayEl.innerHTML = '<span class="text-danger">Invalid</span>';
            }
        } else {
            displayEl.innerHTML = '-';
        }
    }
});
</script>
<?= $this->endSection() ?>