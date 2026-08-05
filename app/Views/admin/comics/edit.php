<?= $this->extend('admin/layout/main') ?>

<?= $this->section('title') ?>Edit Episode Komik<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Edit Episode Komik</h3>
                <div class="card-tools">
                    <a href="<?= base_url('admin/comics') ?>" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>
            </div>
            <form action="<?= base_url('admin/comics/update/' . $episode['id']) ?>" method="post" enctype="multipart/form-data">
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
                        <label for="title">Judul Episode <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="title" name="title" value="<?= old('title', $episode['title']) ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="episode_number">Nomor Episode <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="episode_number" name="episode_number" value="<?= old('episode_number', $episode['episode_number']) ?>" min="1" required>
                    </div>

                    <div class="form-group">
                        <label for="description">Deskripsi</label>
                        <textarea class="form-control" id="description" name="description" rows="4"><?= old('description', $episode['description']) ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="cover_image">Cover Episode</label>
                        <input type="file" class="form-control-file" id="cover_image" name="cover_image" accept="image/*">
                        <small class="form-text text-muted">Kosongkan jika tidak ingin mengubah cover. Maksimal 2MB.</small>
                    </div>

                    <div class="form-group">
                        <label for="is_active">Status</label>
                        <select class="form-control" id="is_active" name="is_active">
                            <option value="1" <?= old('is_active', $episode['is_active']) == '1' ? 'selected' : '' ?>>Aktif</option>
                            <option value="0" <?= old('is_active', $episode['is_active']) == '0' ? 'selected' : '' ?>>Tidak Aktif</option>
                        </select>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update
                    </button>
                    <a href="<?= base_url('admin/comics') ?>" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Preview Cover</h3>
            </div>
            <div class="card-body text-center">
                <?php if (!empty($episode['cover_image'])): ?>
                    <img id="coverPreview" src="<?= base_url('uploads/comics/episodes/' . $episode['cover_image']) ?>" alt="Preview" class="img-fluid" style="max-height:300px;">
                <?php else: ?>
                    <img id="coverPreview" src="#" alt="Preview" class="img-fluid" style="display:none; max-height:300px;">
                    <p id="noCoverText" class="text-muted">Belum ada cover</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('extra_js') ?>
<script>
(function() {
    var input = document.getElementById('cover_image');
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
            var img = document.getElementById('coverPreview');
            img.src = e.target.result;
            img.style.display = 'block';
            var noText = document.getElementById('noCoverText');
            if (noText) noText.style.display = 'none';
        };
        reader.readAsDataURL(file);
    });
})();
</script>
<?= $this->endSection() ?>
