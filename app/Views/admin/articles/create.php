<?= $this->extend('admin/layout/main') ?>

<?= $this->section('title') ?>
Tambah Artikel Baru
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<h1 class="mb-4">Tambah Artikel Baru</h1>

<div class="card shadow-sm">
    <div class="card-body">
        <?php if (session()->getFlashdata('errors')): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>Terjadi Kesalahan:</strong>
                <ul>
                    <?php foreach (session()->getFlashdata('errors') as $error) : ?>
                        <li><?= esc($error) ?></li>
                    <?php endforeach ?>
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif ?>

        <form action="<?= base_url('admin/articles/store') ?>" method="post" enctype="multipart/form-data">
            <?= csrf_field() ?>

            <div class="mb-3">
                <label for="judul" class="form-label">Judul Artikel</label>
                <input type="text" class="form-control" id="judul" name="judul" value="<?= old('judul') ?>" required>
            </div>
            
            <div class="mb-3">
                <label for="isi" class="form-label">Isi Artikel</label>
                <textarea class="form-control" id="isi" name="isi" rows="10" required><?= old('isi') ?></textarea>
            </div>

            <div class="mb-3">
                <label for="gambar" class="form-label">Gambar Utama</label>
                <input class="form-control" type="file" id="gambar" name="gambar" required>
                <div class="form-text">Format: JPG, PNG, JPEG. Maksimal: 2MB.</div>
            </div>

            <h5 class="mt-4">Produk Terkait (Opsional)</h5>
            <p class="text-muted">Pilih kategori dan produk yang relevan dengan artikel ini.</p>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="category_select" class="form-label">Filter Berdasarkan Kategori</label>
                    <select class="form-select" id="category_select">
                        <option value="">-- Semua Kategori --</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?= esc($category['category_id']) ?>">
                                <?= esc($category['nama_kategori']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="produk_terkait" class="form-label">Pilih Produk</label>
                    <select class="form-select" id="produk_terkait" name="produk_terkait[]" multiple="multiple" size="5">
                        <?php foreach ($products as $product): ?>
                            <option value="<?= esc($product['product_id']) ?>" <?= in_array($product['product_id'], old('produk_terkait', []), true) ? 'selected' : '' ?>>
                                <?= esc($product['nama_produk']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="form-text">Tahan CTRL/CMD untuk memilih lebih dari satu produk.</div>
                </div>
            </div>

            <a href="<?= base_url('admin/articles') ?>" class="btn btn-secondary">Batal</a>
            <button type="submit" class="btn btn-primary">Simpan Artikel</button>
        </form>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('extra_js') ?>
<script src="<?= base_url('assets/summernote/summernote-lite.min.js') ?>"></script>
<script>
    $(document).ready(function() {
        $('#isi').summernote({
            placeholder: 'Tulis isi artikel...',
            tabsize: 2,
            height: 300,
            toolbar: [
                ['style', ['style']],
                ['font', ['bold', 'italic', 'underline', 'clear']],
                ['color', ['color']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['table', ['table']],
                ['insert', ['link', 'picture', 'video']],
                ['view', ['fullscreen', 'codeview', 'help']]
            ]
        });

        // Handle filter produk berdasarkan kategori
        $('#category_select').on('change', function() {
            var categoryId = $(this).val();
            $.ajax({
                url: '<?= base_url('admin/articles/get-products') ?>',
                method: 'POST',
                data: { 
                    category: categoryId,
                    '<?= csrf_token() ?>': '<?= csrf_hash() ?>' 
                },
                dataType: 'json',
                success: function(products) {
                    var productSelect = $('#produk_terkait');
                    productSelect.empty();
                    if (products.length > 0) {
                        products.forEach(function(product) {
                            productSelect.append(new Option(product.nama_produk, product.product_id));
                        });
                    } else {
                        productSelect.append(new Option('Tidak ada produk di kategori ini.', '', true, true));
                    }
                },
                error: function(xhr, status, error) {
                    console.error("AJAX Error:", status, error, xhr.responseText);
                    alert('Gagal memuat produk. Silakan coba lagi.');
                }
            });
        });
    });
</script>
<?= $this->endSection() ?>