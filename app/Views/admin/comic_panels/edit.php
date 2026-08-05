<?= $this->extend('admin/layout/main') ?>

<?= $this->section('title') ?>Edit Panel Komik<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Edit Panel - <?= esc($episode['title']) ?></h3>
                <div class="card-tools">
                    <a href="<?= base_url('admin/comics/' . $episode['id'] . '/panels') ?>" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>
            </div>
            <form action="<?= base_url('admin/comics/' . $episode['id'] . '/panels/update/' . $panel['id']) ?>" method="post" enctype="multipart/form-data">
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
                        <label for="panel_number">Nomor Panel <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="panel_number" name="panel_number" value="<?= old('panel_number', $panel['panel_number']) ?>" min="1" required>
                    </div>

                    <div class="form-group">
                        <label for="panel_image">Gambar Panel</label>
                        <input type="file" class="form-control-file" id="panel_image" name="panel_image" accept="image/*">
                        <small class="form-text text-muted">Kosongkan jika tidak ingin mengubah gambar. Maksimal 2MB.</small>
                    </div>

                    <div class="form-group">
                        <label for="caption">Caption (Opsional)</label>
                        <input type="text" class="form-control" id="caption" name="caption" value="<?= old('caption', $panel['caption']) ?>">
                    </div>

                    <div class="form-group">
                        <label for="is_active">Status</label>
                        <select class="form-control" id="is_active" name="is_active">
                            <option value="1" <?= old('is_active', $panel['is_active']) == '1' ? 'selected' : '' ?>>Aktif</option>
                            <option value="0" <?= old('is_active', $panel['is_active']) == '0' ? 'selected' : '' ?>>Tidak Aktif</option>
                        </select>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update
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
                <img id="panelPreview" src="<?= base_url('uploads/comics/panels/' . $panel['image_path']) ?>" alt="Preview" class="img-fluid" style="max-height:300px;">
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('extra_js') ?>
<script>
(function() {
    var input = document.getElementById('panel_image');
    if (!input) return;
    input.addEventListener('change', function() {
        var file = this.files[0];
        if (!file) return;
        var fileSizeMB = file.size / 1024 / 1024;
        if (fileSizeMB > 2) {
            alert('Ukuran file terlalu besar! Maksimal 2MB.');
            this.value = '';
            return;
        }
        var reader = new FileReader();
        reader.onload = function(e) {
            var img = document.getElementById('panelPreview');
            img.src = e.target.result;
        };
        reader.readAsDataURL(file);
    });
})();
</script>
<?= $this->endSection() ?>
