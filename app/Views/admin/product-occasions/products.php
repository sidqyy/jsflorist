<?= $this->extend('admin/layout/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Tambahkan Produk ke Occasion: <?= esc($occasion['occasion_name']) ?></h3>

                    <div class="card-tools">
                        <a href="<?= base_url('/admin/product-occasions') ?>" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (session()->getFlashdata('success')): ?>
                        <div class="alert alert-success">
                            <?= session()->getFlashdata('success') ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (session()->getFlashdata('error')): ?>
                        <div class="alert alert-danger">
                            <?= session()->getFlashdata('error') ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="mb-3">
                        <form action="<?= base_url('/admin/product-occasions/products/' . $occasion['occasion_id']) ?>" method="get" class="row g-3 align-items-center">

                            <div class="col-md-auto">
                                <label for="categoryFilter" class="form-label mb-0">Filter berdasarkan Kategori:</label>
                            </div>
                            <div class="col-md-4">
                                <select class="form-select" id="categoryFilter" name="category">
                                    <option value="">Semua Kategori</option>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?= esc($category['sub_category_id']) ?>" <?= $selectedCategory == $category['sub_category_id'] ? 'selected' : '' ?>>
                                            <?= esc($category['category_name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-auto">
                                <button type="submit" class="btn btn-primary">Terapkan Filter</button>
                                <a href="<?= base_url('/admin/product-occasions/products/' . $occasion['occasion_id']) ?>" class="btn btn-outline-secondary">Reset</a>
                            </div>

                        </form>
                    </div>

                    <form action="<?= base_url('/admin/product-occasions/add-products') ?>" method="post">
                        <?= csrf_field() ?>
                        <input type="hidden" name="occasion_id" value="<?= $occasion['occasion_id'] ?>">

                        
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th width="50">
                                            <input type="checkbox" id="selectAll" class="form-check-input">
                                        </th>
                                        <th>Gambar</th>
                                        <th>Nama Produk</th>
                                        <th>Harga</th>
                                        <th>Kategori</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($products)): ?>
                                        <tr>
                                            <td colspan="6" class="text-center">Tidak ada produk tersedia</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($products as $product): ?>
                                            <tr>
                                                <td>
                                                    <input type="checkbox" 
                                                           name="product_ids[]" 
                                                           value="<?= $product['product_id'] ?>" 
                                                           class="form-check-input product-checkbox"
                                                           <?= in_array($product['product_id'], $existingProductIds) ? 'checked' : '' ?>>
                                                </td>
                                                <td>
                                                    <?php if (!empty($product['gambar_url'])): ?>
                                                        <img src="<?= base_url('assets/img/gambar/' . $product['gambar_url']) ?>" 
                                                             alt="<?= esc($product['nama_produk']) ?>" 
                                                             style="width: 50px; height: 50px; object-fit: cover;">
                                                    <?php else: ?>
                                                        <img src="<?= base_url('assets/img/no-image.jpg') ?>" 
                                                             alt="No Image" 
                                                             style="width: 50px; height: 50px; object-fit: cover;">
                                                    <?php endif; ?>
                                                </td>
                                                <td><?= esc($product['nama_produk']) ?></td>
                                                <td>Rp <?= number_format($product['harga'], 0, ',', '.') ?></td>
                                                <td>
                                                    <?= esc($product['category_display'] ?? '-') ?>
                                                </td>
                                                <td>
                                                    <span class="badge badge-<?= $product['is_active'] ? 'success' : 'secondary' ?>">
                                                        <?= $product['is_active'] ? 'Aktif' : 'Tidak Aktif' ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-3">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save"></i> Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    $(document).ready(function() {
        // Select/Deselect all checkboxes
        $('#selectAll').click(function() {
            $('.product-checkbox').prop('checked', this.checked);
        });

        // Update select all checkbox based on individual checkboxes
        $('.product-checkbox').change(function() {
            if ($('.product-checkbox:checked').length === $('.product-checkbox').length) {
                $('#selectAll').prop('checked', true);
            } else {
                $('#selectAll').prop('checked', false);
            }
        });

        // Initialize DataTable
        $('.table').DataTable({
            responsive: true,
            language: {
                url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/id.json'
            }
        });
    });
</script>
<?= $this->endSection() ?>
