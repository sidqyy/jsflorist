<?= $this->extend('admin/layout/main') ?>

<?= $this->section('title') ?>
<?= esc($title) ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><?= esc($title) ?></h3>
                <div class="card-tools">
                    <a href="<?= base_url('admin/event-banners') ?>" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>
            </div>
            <form action="<?= base_url('admin/event-banners/store') ?>" method="post" enctype="multipart/form-data">
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

                    <?php if (session()->getFlashdata('error')): ?>
                        <div class="alert alert-danger">
                            <?= session()->getFlashdata('error') ?>
                        </div>
                    <?php endif; ?>

                    <div class="form-group">
                        <label for="title">Judul Event Banner <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="title" name="title"
                            value="<?= old('title') ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="image">Gambar Event Banner <span class="text-danger">*</span></label>
                        <input type="file" class="form-control-file" id="image" name="image"
                            accept="image/*" required>
                        <small class="form-text text-muted">Format: JPG, JPEG, PNG. Maksimal 2MB.</small>
                    </div>

                    <div class="form-group">
                        <label for="link_url">Link URL (Opsional)</label>
                        <input type="url" class="form-control" id="link_url" name="link_url"
                            value="<?= old('link_url') ?>" placeholder="https://example.com">
                        <small class="form-text text-muted">Jika diisi, gambar akan dapat diklik untuk mengarah ke URL ini.</small>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="start_date">Tanggal Mulai <span class="text-danger">*</span></label>
                                <input type="datetime-local" class="form-control" id="start_date" name="start_date"
                                    value="<?= old('start_date') ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="end_date">Tanggal Selesai <span class="text-danger">*</span></label>
                                <input type="datetime-local" class="form-control" id="end_date" name="end_date"
                                    value="<?= old('end_date') ?>" required>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="is_active">Status</label>
                        <select class="form-control" id="is_active" name="is_active">
                            <option value="1" <?= old('is_active', '1') == '1' ? 'selected' : '' ?>>Aktif</option>
                            <option value="0" <?= old('is_active') == '0' ? 'selected' : '' ?>>Tidak Aktif</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="domain_specific">Pengaturan Domain</label>
                        <select class="form-control" id="domain_specific" name="domain_specific" onchange="toggleDomainSelection()">
                            <option value="0" <?= old('domain_specific', '0') == '0' ? 'selected' : '' ?>>Tampilkan di Semua Domain</option>
                            <option value="1" <?= old('domain_specific') == '1' ? 'selected' : '' ?>>Domain Spesifik</option>
                        </select>
                    </div>

                    <div class="form-group" id="domain_selection" style="display: none;">
                        <label>Pilih Domain yang Diizinkan</label>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="jsflorist.com" id="domain_js" name="allowed_domains[]"
                                <?= in_array('jsflorist.com', old('allowed_domains', [])) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="domain_js">
                                JS Florist (jsflorist.com)
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="poppyflorist.com" id="domain_poppy" name="allowed_domains[]"
                                <?= in_array('poppyflorist.com', old('allowed_domains', [])) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="domain_poppy">
                                Poppy Florist (poppyflorist.com)
                            </label>
                        </div>
                        <small class="form-text text-muted">Pilih domain mana saja yang boleh menampilkan event banner ini.</small>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Simpan
                    </button>
                    <a href="<?= base_url('admin/event-banners') ?>" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Preview Gambar</h3>
            </div>
            <div class="card-body text-center">
                <img id="imagePreview" src="#" alt="Preview" class="img-fluid" style="display: none; max-height: 300px;">
                <p id="noImageText" class="text-muted">Pilih gambar untuk melihat preview</p>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('extra_js') ?>
<script>
    $(document).ready(function() {
        // Image Preview
        $('#image').change(function() {
            const file = this.files[0];
            if (file) {
                // Validasi ukuran file (2MB)
                const fileSizeMB = file.size / 1024 / 1024;
                if (fileSizeMB > 2) {
                    alert(`Ukuran file terlalu besar! Maksimal 2MB. Ukuran file Anda: ${fileSizeMB.toFixed(2)}MB`);
                    $(this).val('');
                    $('#imagePreview').hide();
                    $('#noImageText').show();
                    return;
                }

                const reader = new FileReader();
                reader.onload = function(e) {
                    $('#imagePreview').attr('src', e.target.result).show();
                    $('#noImageText').hide();
                };
                reader.readAsDataURL(file);
            } else {
                $('#imagePreview').hide();
                $('#noImageText').show();
            }
        });

        // Validate dates
        $('#start_date, #end_date').change(function() {
            const startDate = new Date($('#start_date').val());
            const endDate = new Date($('#end_date').val());

            if (startDate && endDate && startDate > endDate) {
                alert('Tanggal mulai tidak boleh lebih besar dari tanggal selesai!');
                $('#end_date').val('');
            }
        });
    });

    // Toggle domain selection visibility
    function toggleDomainSelection() {
        const domainSpecific = document.getElementById('domain_specific').value;
        const domainSelection = document.getElementById('domain_selection');

        console.log('Domain specific value:', domainSpecific); // Debug log

        if (domainSpecific == '1') {
            domainSelection.style.display = 'block';
        } else {
            domainSelection.style.display = 'none';
            // Uncheck all domain checkboxes when hiding
            document.querySelectorAll('input[name="allowed_domains[]"]').forEach(checkbox => {
                checkbox.checked = false;
            });
        }
    }

    // Log form data before submit for debugging
    document.querySelector('form').addEventListener('submit', function(e) {
        const domainSpecific = document.getElementById('domain_specific').value;
        const checkedDomains = [];
        document.querySelectorAll('input[name="allowed_domains[]"]:checked').forEach(checkbox => {
            checkedDomains.push(checkbox.value);
        });

        console.log('Form submit - Domain Specific:', domainSpecific);
        console.log('Form submit - Checked Domains:', checkedDomains);

        if (domainSpecific == '1' && checkedDomains.length === 0) {
            alert('Silakan pilih minimal satu domain untuk event yang domain-spesifik!');
            e.preventDefault();
            return false;
        }
    });
</script>
<?= $this->endSection() ?>