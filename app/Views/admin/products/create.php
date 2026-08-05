<?= $this->extend('admin/layout/main') ?>



<?= $this->section('title') ?>

Tambah Produk Baru

<?= $this->endSection() ?>



<?= $this->section('content') ?>

<h1>Tambah Produk Baru</h1>



<?php if (session()->getFlashdata('errors')): ?>
    <div class="alert alert-danger">
        <strong>Terjadi Kesalahan:</strong>
        <ul>
            <?php foreach (session()->getFlashdata('errors') as $error) : ?>
                <li><?= esc($error) ?></li>
            <?php endforeach ?>
        </ul>
    </div>
<?php endif ?>

<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger">
        <strong>Error:</strong> <?= esc(session()->getFlashdata('error')) ?>
    </div>
<?php endif ?>

<?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success">
        <?= esc(session()->getFlashdata('success')) ?>
    </div>
<?php endif ?>



<div class="card shadow-sm">

    <div class="card-body">

        <form action="<?= base_url('admin/products/store') ?>" method="post" enctype="multipart/form-data">

            <?= csrf_field() ?>

            <div class="mb-3">

                <label for="nama_produk" class="form-label">Nama Produk</label>

                <input type="text" class="form-control" id="nama_produk" name="nama_produk" value="<?= old('nama_produk') ?>" required>

            </div>

            <hr>

            <div class="mb-3">
                <label class="form-label">Komponen Produk</label>
                <div id="componentsContainer"></div>
                <button type="button" id="addComponent" class="btn btn-sm btn-outline-primary mt-2">Tambah Komponen</button>
                <small class="text-muted d-block mt-1">Tambah beberapa komponen untuk produk ini beserta kuantitas dan biaya satuannya.</small>
            </div>

            <div class="mb-3">

                <label for="deskripsi_produk" class="form-label">Deskripsi</label>

                <textarea class="form-control" id="deskripsi_produk" name="deskripsi_produk" rows="3"><?= old('deskripsi_produk') ?></textarea>

            </div>

            <div class="row">

                <div class="col-md-12 mb-3">

                    <label for="sub_category_id" class="form-label">Kategori</label>

                    <select class="form-select" id="sub_category_id" name="sub_category_id" required>

                        <option value="">-- Pilih Kategori --</option>

                        <?php foreach ($categories as $category): ?>

                            <option value="<?= $category['id'] ?>" <?= old('sub_category_id') == $category['id'] ? 'selected' : '' ?>>

                                <?= esc($category['name']) ?>

                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>

            </div>

            <div class="mb-3">

                <label class="form-label">Pilih Acara (Occasion)</label>

                <div class="row">

                    <?php foreach($occasions as $occasion): ?>

                        <div class="col-md-3">

                            <div class="form-check">

                                <input class="form-check-input" type="checkbox" name="occasions[]" value="<?= $occasion['occasion_id'] ?>" id="occasion_<?= $occasion['occasion_id'] ?>" <?= is_array(old('occasions')) && in_array($occasion['occasion_id'], old('occasions')) ? 'checked' : '' ?>>

                                <label class="form-check-label" for="occasion_<?= $occasion['occasion_id'] ?>">

                                    <?= esc($occasion['occasion_name']) ?>

                                </label>



                            </div>

                        </div>

                    <?php endforeach; ?>





                </div>

            </div>

            <div class="mb-3">

                <label for="harga" class="form-label">Harga</label>



                <div class="input-group">

                    <span class="input-group-text">Rp</span>

                    <input type="number" class="form-control" id="harga" name="harga" value="<?= old('harga') ?>" required>

                </div>

            </div>

            <div class="mb-3">

                <label for="gambar_url" class="form-label">Gambar Produk</label>

                <input class="form-control" type="file" id="gambar_url" name="gambar_url" accept="image/*" required>

            </div>

            <div class="form-check form-switch mb-3">

                <input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active" value="1" checked>

                <label class="form-check-label" for="is_active">Aktifkan Produk</label>

            </div>

            <a href="<?= base_url('admin/products') ?>" class="btn btn-secondary">Batal</a>

            <button type="submit" class="btn btn-primary">Simpan Produk</button>

        </form>

    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Validasi ukuran file gambar (2MB)
    function validateFileSize(input, maxSizeMB = 2) {
        const file = input.files[0];
        if (file) {
            const fileSizeMB = file.size / 1024 / 1024;
            console.log('File size:', fileSizeMB.toFixed(2), 'MB');
            if (fileSizeMB > maxSizeMB) {
                alert(`Ukuran file terlalu besar! Maksimal ${maxSizeMB}MB. Ukuran file Anda: ${fileSizeMB.toFixed(2)}MB`);
                input.value = '';
                return false;
            }
        }
        return true;
    }

    // Tambahkan event listener untuk semua input file gambar
    document.querySelectorAll('input[type="file"][accept*="image"]').forEach(input => {
        input.addEventListener('change', function() {
            console.log('File input changed:', this.name);
            validateFileSize(this);
        });
    });

    // Validasi untuk form submit
    document.querySelector('form').addEventListener('submit', function(e) {
        console.log('Form submission attempt');
        let isValid = true;
        let invalidFiles = [];
        
        // Validasi semua input file
        document.querySelectorAll('input[type="file"][accept*="image"]').forEach(input => {
            if (input.files[0]) {
                console.log('Validating file from input:', input.name, 'Size:', input.files[0].size, 'bytes');
                const file = input.files[0];
                const fileSizeMB = file.size / 1024 / 1024;
                if (fileSizeMB > 2) {
                    isValid = false;
                    invalidFiles.push(input.name + ' (' + fileSizeMB.toFixed(2) + 'MB)');
                }
            }
        });

        if (!isValid) {
            e.preventDefault();
            e.stopPropagation();
            console.log('Form submission prevented due to invalid files:', invalidFiles);
            alert('❌ FORM TIDAK DAPAT DISUBMIT!\n\nUkuran file terlalu besar. Maksimal 2MB per file.\nFile yang bermasalah:\n' + invalidFiles.join('\n'));
            return false;
        }
        
        console.log('✅ Form validation passed, submitting...');
        return true;
    });

    // ===== Komponen Produk (dinamis) =====
    let compIndex = 0;
    const compContainer = document.getElementById('componentsContainer');
    const addCompBtn = document.getElementById('addComponent');
    function addComponentRow(values = {}) {
        const idx = compIndex++;
        const row = document.createElement('div');
        row.className = 'row g-2 align-items-end mb-2 component-row';
        row.innerHTML = `
            <div class="col-md-4">
                <label class="form-label">Nama Komponen</label>
                <input type="text" name="components[${idx}][component_name]" class="form-control" value="${values.component_name || ''}" required>
            </div>
            <div class="col-md-2">
                <label class="form-label">Qty</label>
                <input type="number" step="0.01" name="components[${idx}][quantity]" class="form-control" value="${values.quantity || ''}" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">Biaya Satuan</label>
                <input type="number" step="0.01" name="components[${idx}][unit_cost]" class="form-control" value="${values.unit_cost || ''}" required>
            </div>
            <div class="col-md-2">
                <label class="form-label">Urutan</label>
                <input type="number" name="components[${idx}][sort_order]" class="form-control" value="${values.sort_order || 0}">
            </div>
            <div class="col-md-1">
                <button type="button" class="btn btn-outline-danger w-100 remove-component">Hapus</button>
            </div>`;
        compContainer.appendChild(row);
        row.querySelector('.remove-component').addEventListener('click', () => row.remove());
    }
    addCompBtn.addEventListener('click', () => addComponentRow());
});
</script>

<?= $this->endSection() ?>

