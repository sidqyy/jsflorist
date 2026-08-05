<?= $this->extend('admin/layout/main') ?>

<?= $this->section('title') ?>
Edit Artikel
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<h1 class="mb-4">Edit Artikel: "<?= esc($article['judul']) ?>"</h1>

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

        <form action="<?= base_url('admin/articles/update/' . $article['id_artikel']) ?>" method="post" enctype="multipart/form-data">
            <?= csrf_field() ?>

            <div class="mb-3">
                <label for="judul" class="form-label">Judul Artikel</label>
                <input type="text" class="form-control" id="judul" name="judul" value="<?= old('judul', $article['judul']) ?>" required>
            </div>
            
            <div class="mb-3">
                <label for="isi" class="form-label">Isi Artikel</label>
                <textarea class="form-control" id="isi" name="isi" rows="10" required><?= old('isi', $article['isi']) ?></textarea>
            </div>

            <div class="mb-3">
                <label for="gambar" class="form-label">Ganti Gambar Utama (Opsional)</label>
                <input class="form-control" type="file" id="gambar" name="gambar">
                <div class="form-text">Biarkan kosong jika tidak ingin mengganti gambar.</div>
                
                <div class="mt-2">
                    <label>Gambar Saat Ini:</label><br>
                    <img src="<?= base_url('assets/img/artikel/' . $article['gambar']) ?>" alt="Gambar Saat Ini" style="max-width: 200px; height: auto; border-radius: 5px;">
                </div>
            </div>

            <h5 class="mt-4">Produk Terkait (Opsional)</h5>
            <p class="text-muted">Pilih produk yang relevan dengan artikel ini.</p>

            <div class="mb-3">
                <label for="produk_terkait" class="form-label">Pilih Produk</label>
                <select class="form-select" id="produk_terkait" name="produk_terkait[]" multiple="multiple" size="5">
                     <?php 
                        $selected_products = old('produk_terkait', explode(',', $article['produk_terkait']));
                     ?>
                     <?php if (!empty($products)): ?>
                        <?php foreach ($products as $product): ?>
                           <option value="<?= esc($product['product_id']) ?>" <?= in_array($product['product_id'], $selected_products, true) ? 'selected' : '' ?>>
                               <?= esc($product['nama_produk']) ?>
                           </option>
                        <?php endforeach; ?>
                     <?php endif; ?>
                </select>
                <div class="form-text">Tahan CTRL/CMD untuk memilih lebih dari satu produk.</div>
            </div>

            <a href="<?= base_url('admin/articles') ?>" class="btn btn-secondary">Batal</a>
            <button type="submit" class="btn btn-primary">Update Artikel</button>
        </form>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('extra_js') ?>
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>
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
    });
</script>
<?= $this->endSection() ?>