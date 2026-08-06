<?= $this->extend('admin/layout/main') ?>

<?= $this->section('title') ?>Tambah Aturan Diskon<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Tambah Aturan Diskon Baru</h3>
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

                    <!-- Debug info -->
                    <div id="debug-info" class="alert alert-info" style="display:none;">
                        <strong>Debug Info:</strong>
                        <pre id="debug-content"></pre>
                    </div>

                    <form action="<?= base_url('admin/discounts/store') ?>" method="post" id="discountForm">
                        <?= csrf_field() ?>
                        
                        <!-- Debug: Show CSRF Token -->
                        <input type="hidden" id="csrf_debug" value="<?= csrf_hash() ?>">
                        
                        <!-- Nama Diskon -->
                        <div class="mb-3">
                            <label for="name" class="form-label">Nama Aturan Diskon <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control" 
                                   id="name" 
                                   name="name" 
                                   value="<?= old('name') ?>" 
                                   placeholder="Contoh: Promo Tahun Baru 2026, Flash Sale Bunga, dll"
                                   required>
                            <div class="form-text">Nama untuk identifikasi aturan diskon</div>
                        </div>

                        <!-- Tipe Diskon -->
                        <div class="mb-3">
                            <label class="form-label">Tipe Diskon <span class="text-danger">*</span></label>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="discount_type" id="type_subtotal" 
                                       value="subtotal" <?= old('discount_type', 'subtotal') === 'subtotal' ? 'checked' : '' ?>>
                                <label class="form-check-label" for="type_subtotal">
                                    <strong>Subtotal</strong> - Diskon berdasarkan total belanja (persentase)
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="discount_type" id="type_product" 
                                       value="product" <?= old('discount_type') === 'product' ? 'checked' : '' ?>>
                                <label class="form-check-label" for="type_product">
                                    <strong>Produk</strong> - Diskon untuk produk tertentu (harga spesifik)
                                </label>
                            </div>
                        </div>

                        <!-- Section Subtotal -->
                        <div id="subtotal_section" class="border rounded p-3 mb-3 bg-light">
                            <h5 class="mb-3"><i class="fas fa-calculator"></i> Pengaturan Diskon Subtotal</h5>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="min_amount" class="form-label">Minimal Pembelian (Rp)</label>
                                        <input type="number" 
                                               class="form-control" 
                                               id="min_amount" 
                                               name="min_amount" 
                                               value="<?= old('min_amount') ?>" 
                                               step="1000"
                                               min="0">
                                        <div class="form-text">Minimal pembelian untuk mendapat diskon</div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="max_amount" class="form-label">Maksimal Pembelian (Rp)</label>
                                        <input type="number" 
                                               class="form-control" 
                                               id="max_amount" 
                                               name="max_amount" 
                                               value="<?= old('max_amount') ?>" 
                                               step="1000"
                                               min="0">
                                        <div class="form-text">Kosongkan jika tidak ada batas</div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="discount_percentage" class="form-label">Persentase Diskon (%)</label>
                                        <input type="number" 
                                               class="form-control" 
                                               id="discount_percentage" 
                                               name="discount_percentage" 
                                               value="<?= old('discount_percentage') ?>" 
                                               step="0.1"
                                               min="0.1"
                                               max="100">
                                        <div class="form-text">Persentase diskon (0.1 - 100)</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Section Produk -->
                        <div id="product_section" class="border rounded p-3 mb-3 bg-light" style="display:none;">
                            <h5 class="mb-3"><i class="fas fa-tags"></i> Pengaturan Diskon Produk</h5>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i> Pilih produk lalu tentukan <strong>harga setelah diskon</strong> untuk setiap produk.
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Pilih Produk yang akan didiskon:</label>
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
                                                $oldProducts = old('product_ids') ?? [];
                                                $oldPrices = old('product_prices') ?? [];
                                                foreach ($products as $product): 
                                                    $isChecked = in_array($product['product_id'], $oldProducts);
                                                    $oldPrice = $oldPrices[$product['product_id']] ?? '';
                                                ?>
                                                    <tr class="product-row">
                                                        <td class="text-center">
                                                            <input class="form-check-input product-checkbox" type="checkbox" 
                                                                   name="product_ids[]" 
                                                                   value="<?= esc($product['product_id']) ?>" 
                                                                   data-price="<?= $product['harga'] ?>"
                                                                   data-product-id="<?= esc($product['product_id']) ?>"
                                                                   id="product_<?= esc($product['product_id']) ?>"
                                                                   <?= $isChecked ? 'checked' : '' ?>>
                                                        </td>
                                                        <td>
                                                            <label for="product_<?= esc($product['product_id']) ?>" class="mb-0 cursor-pointer">
                                                                <?= esc($product['nama_produk']) ?>
                                                            </label>
                                                        </td>
                                                        <td class="text-end">
                                                            <strong>Rp<?= number_format($product['harga'], 0, ',', '.') ?></strong>
                                                        </td>
                                                        <td>
                                                            <input type="number" 
                                                                   class="form-control form-control-sm discounted-price-input"
                                                                   name="product_prices[<?= esc($product['product_id']) ?>]"
                                                                   data-product-id="<?= esc($product['product_id']) ?>"
                                                                   data-original-price="<?= $product['harga'] ?>"
                                                                   value="<?= esc($oldPrice) ?>"
                                                                   min="0"
                                                                   max="<?= $product['harga'] ?>"
                                                                   step="1000"
                                                                   placeholder="Harga diskon..."
                                                                   <?= !$isChecked ? 'disabled' : '' ?>>
                                                        </td>
                                                        <td class="text-center discount-display" data-product-id="<?= esc($product['product_id']) ?>">
                                                            -
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="5" class="text-center text-muted">Belum ada produk aktif.</td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Status & Batas Penggunaan -->
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="is_active" class="form-label">Status</label>
                                    <select class="form-control" id="is_active" name="is_active" required>
                                        <option value="1" <?= old('is_active', '1') == '1' ? 'selected' : '' ?>>Aktif</option>
                                        <option value="0" <?= old('is_active') == '0' ? 'selected' : '' ?>>Non-Aktif</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="usage_limit" class="form-label">Batas Penggunaan</label>
                                    <input type="number" 
                                           class="form-control" 
                                           id="usage_limit" 
                                           name="usage_limit" 
                                           value="<?= old('usage_limit') ?>" 
                                           min="1"
                                           placeholder="Tanpa batas">
                                    <div class="form-text">Kosongkan untuk tanpa batas</div>
                                </div>
                            </div>
                        </div>

                        <!-- Periode Diskon -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="start_date" class="form-label">Tanggal Mulai</label>
                                    <input type="datetime-local" 
                                           class="form-control" 
                                           id="start_date" 
                                           name="start_date" 
                                           value="<?= old('start_date') ?>">
                                    <div class="form-text">Kosongkan jika langsung aktif</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="end_date" class="form-label">Tanggal Berakhir</label>
                                    <input type="datetime-local" 
                                           class="form-control" 
                                           id="end_date" 
                                           name="end_date" 
                                           value="<?= old('end_date') ?>">
                                    <div class="form-text">Kosongkan jika tanpa batas waktu</div>
                                </div>
                            </div>
                        </div>

                        <!-- Jam Pengambilan -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="valid_pickup_start_time" class="form-label">Jam Mulai Berlaku (Pengambilan/Pengantaran)</label>
                                    <input type="time" 
                                           class="form-control" 
                                           id="valid_pickup_start_time" 
                                           name="valid_pickup_start_time" 
                                           value="<?= old('valid_pickup_start_time') ?>">
                                    <div class="form-text">Kosongkan jika berlaku jam berapa saja</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="valid_pickup_end_time" class="form-label">Jam Akhir Berlaku (Pengambilan/Pengantaran)</label>
                                    <input type="time" 
                                           class="form-control" 
                                           id="valid_pickup_end_time" 
                                           name="valid_pickup_end_time" 
                                           value="<?= old('valid_pickup_end_time') ?>">
                                    <div class="form-text">Kosongkan jika berlaku jam berapa saja</div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="alert alert-secondary">
                                <strong><i class="fas fa-lightbulb"></i> Contoh:</strong><br>
                                <strong>Diskon Subtotal:</strong> Belanja Rp100.000 - Rp500.000 dapat diskon 10%<br>
                                <strong>Diskon Produk:</strong> Bunga Mawar harga Rp1.550.000 → Rp1.250.000 (hemat Rp300.000)
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="<?= base_url('admin/discounts') ?>" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Kembali
                            </a>
                            <button type="submit" class="btn btn-primary" id="submitBtn">
                                <i class="fas fa-save"></i> Simpan Aturan Diskon
                            </button>
                            <!-- Debug button - hapus setelah debug -->
                            <button type="button" class="btn btn-warning" id="debugSubmitBtn">
                                <i class="fas fa-bug"></i> Debug Submit (AJAX)
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Debug response modal -->
<div class="modal fade" id="debugModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Debug Response</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <pre id="debugResponse" style="max-height: 400px; overflow: auto;"></pre>
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
    const maxInput = document.getElementById('max_amount');
    const pctInput = document.getElementById('discount_percentage');

    // Toggle sections based on discount type
    function toggleSections() {
        if (typeProduct.checked) {
            subtotalSection.style.display = 'none';
            productSection.style.display = 'block';
            minInput.removeAttribute('required');
            pctInput.removeAttribute('required');
        } else {
            subtotalSection.style.display = 'block';
            productSection.style.display = 'none';
            minInput.setAttribute('required', 'required');
            pctInput.setAttribute('required', 'required');
        }
    }

    typeSubtotal.addEventListener('change', toggleSections);
    typeProduct.addEventListener('change', toggleSections);
    toggleSections();

    // Handle product checkbox changes
    document.querySelectorAll('.product-checkbox').forEach(function(checkbox) {
        checkbox.addEventListener('change', function() {
            const productId = this.dataset.productId;
            const priceInput = document.querySelector('.discounted-price-input[data-product-id="' + productId + '"]');
            
            if (this.checked) {
                priceInput.disabled = false;
                priceInput.focus();
            } else {
                priceInput.disabled = true;
                priceInput.value = '';
                updateDiscountDisplay(productId, 0, 0);
            }
        });
    });

    // Handle discounted price input changes
    document.querySelectorAll('.discounted-price-input').forEach(function(input) {
        input.addEventListener('input', function() {
            const productId = this.dataset.productId;
            const originalPrice = parseFloat(this.dataset.originalPrice) || 0;
            const discountedPrice = parseFloat(this.value) || 0;
            
            updateDiscountDisplay(productId, originalPrice, discountedPrice);
        });

        // Trigger initial calculation if value exists
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
                displayEl.innerHTML = '<span class="text-success">-Rp' + numberFormat(discount) + '</span><br><small class="text-muted">(' + percentage + '%)</small>';
                displayEl.classList.add('text-success');
            } else {
                displayEl.innerHTML = '<span class="text-danger">Invalid</span>';
                displayEl.classList.remove('text-success');
            }
        } else {
            displayEl.innerHTML = '-';
            displayEl.classList.remove('text-success');
        }
    }

    function numberFormat(num) {
        return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }

    // Validate max amount for subtotal type
    function validateMax() {
        if (!typeSubtotal.checked) return;
        
        const minVal = parseFloat(minInput.value) || 0;
        const maxVal = parseFloat(maxInput.value);
        const hasMax = !Number.isNaN(maxVal) && maxVal > 0;

        if (hasMax && maxVal <= minVal) {
            maxInput.classList.add('is-invalid');
        } else {
            maxInput.classList.remove('is-invalid');
        }
    }

    maxInput.addEventListener('input', validateMax);
    minInput.addEventListener('input', validateMax);

    // Form submission handler with AJAX untuk debug
    const discountForm = document.getElementById('discountForm');
    discountForm.addEventListener('submit', function(e) {
        const debugInfo = document.getElementById('debug-info');
        const debugContent = document.getElementById('debug-content');
        
        // Collect form data for debugging
        const formData = new FormData(discountForm);
        let debugText = 'Form Action: ' + discountForm.action + '\n';
        debugText += 'Method: ' + discountForm.method + '\n\n';
        debugText += 'Form Data:\n';
        
        for (let [key, value] of formData.entries()) {
            debugText += '  ' + key + ': ' + value + '\n';
        }
        
        console.log('Form submission debug:', debugText);
        
        // Uncomment line below to see debug info before submit (will prevent submit)
        // debugInfo.style.display = 'block';
        // debugContent.textContent = debugText;
        // e.preventDefault();
        // return false;
    });

    // Debug AJAX submit button
    document.getElementById('debugSubmitBtn').addEventListener('click', function() {
        const formData = new FormData(discountForm);
        const debugResponse = document.getElementById('debugResponse');
        
        // Show loading
        debugResponse.textContent = 'Mengirim request...';
        const modal = new bootstrap.Modal(document.getElementById('debugModal'));
        modal.show();
        
        fetch(discountForm.action, {
            method: 'POST',
            body: formData,
            redirect: 'manual' // Prevent auto-redirect to see actual response
        })
        .then(response => {
            let responseText = 'Status: ' + response.status + ' ' + response.statusText + '\n';
            responseText += 'Type: ' + response.type + '\n';
            responseText += 'URL: ' + response.url + '\n';
            responseText += 'Redirected: ' + response.redirected + '\n';
            responseText += 'Headers:\n';
            
            response.headers.forEach((value, key) => {
                responseText += '  ' + key + ': ' + value + '\n';
            });
            
            if (response.type === 'opaqueredirect') {
                responseText += '\n[REDIRECT DETECTED - Form akan di-redirect]\n';
                responseText += 'Redirect URL kemungkinan: ' + response.url;
                debugResponse.textContent = responseText;
                return null;
            }
            
            return response.text().then(text => {
                responseText += '\nBody:\n' + text;
                debugResponse.textContent = responseText;
            });
        })
        .catch(error => {
            debugResponse.textContent = 'ERROR: ' + error.message + '\n\nStack: ' + error.stack;
        });
    });
});
</script>
<?= $this->endSection() ?>
