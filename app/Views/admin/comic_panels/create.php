<?= $this->extend('admin/layout/main') ?>

<?= $this->section('title') ?>Tambah Panel Komik<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Tambah Panel - <?= esc($episode['title']) ?></h3>
                <div class="card-tools">
                    <a href="<?= base_url('admin/comics/' . $episode['id'] . '/panels') ?>" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>
            </div>
            <form action="<?= base_url('admin/comics/' . $episode['id'] . '/panels/store') ?>" method="post" enctype="multipart/form-data">
                <?= csrf_field() ?>
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

                    <div class="form-group">
                        <label for="panel_number">Nomor Panel Awal <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="panel_number" name="panel_number" value="<?= old('panel_number', 1) ?>" min="1" required>
                        <small class="form-text text-muted">Panel berikutnya akan otomatis berurutan.</small>
                    </div>

                    <div class="form-group">
                        <label for="panel_images">Gambar Panel (Bisa Banyak) <span class="text-danger">*</span></label>
                        <input type="file" class="form-control-file" id="panel_images" name="panel_images[]" accept="image/*" multiple required>
                        <small class="form-text text-muted">Format: JPG, JPEG, PNG. Maksimal 2MB per gambar. Urutan mengikuti urutan file yang dipilih.</small>
                    </div>

                    <div class="form-group">
                        <label for="caption">Caption (Opsional)</label>
                        <input type="text" class="form-control" id="caption" name="caption" value="<?= old('caption') ?>">
                    </div>

                    <div class="form-group">
                        <label for="is_active">Status</label>
                        <select class="form-control" id="is_active" name="is_active">
                            <option value="1" <?= old('is_active', '1') == '1' ? 'selected' : '' ?>>Aktif</option>
                            <option value="0" <?= old('is_active') == '0' ? 'selected' : '' ?>>Tidak Aktif</option>
                        </select>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Simpan
                    </button>
                    <a href="<?= base_url('admin/comics/' . $episode['id'] . '/panels') ?>" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Preview Panel</h3>
            </div>
            <div class="card-body text-center">
                <div id="panelPreview" class="row g-2"></div>
                <p id="noPanelText" class="text-muted">Pilih gambar panel untuk melihat preview</p>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('extra_js') ?>
<script>
(function() {
    var input = document.getElementById('panel_images');
    if (!input) return;
    input.addEventListener('change', function() {
        var files = Array.from(this.files || []);
        var preview = document.getElementById('panelPreview');
        preview.innerHTML = '';
        if (!files.length) {
            document.getElementById('noPanelText').style.display = 'block';
            return;
        }

        for (var i = 0; i < files.length; i++) {
            var file = files[i];
            var fileSizeMB = file.size / 1024 / 1024;
            if (fileSizeMB > 2) {
                alert('Ukuran file terlalu besar! Maksimal 2MB.');
                input.value = '';
                preview.innerHTML = '';
                document.getElementById('noPanelText').style.display = 'block';
                return;
            }

            var reader = new FileReader();
            reader.onload = function(e) {
                var col = document.createElement('div');
                col.className = 'col-6';
                var img = document.createElement('img');
                img.className = 'img-fluid rounded border';
                img.style.maxHeight = '140px';
                img.src = e.target.result;
                col.appendChild(img);
                preview.appendChild(col);
            };
            reader.readAsDataURL(file);
        }

        document.getElementById('noPanelText').style.display = 'none';
    });
})();
</script>
<?= $this->endSection() ?>
