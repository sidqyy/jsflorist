<?= $this->extend('admin/layout/main') ?>



<?= $this->section('title') ?>

Edit Produk: <?= esc($product['nama_produk']) ?>

<?= $this->endSection() ?>



<?= $this->section('content') ?>

<h1>Edit Produk</h1>

<!-- Tombol Kembali di Pojok Kiri Atas -->
<div class="mb-3 mt-2">
    <button type="button" class="btn btn-light border shadow-sm" onclick="history.back()">
        <i class="bi bi-arrow-left"></i> Kembali
    </button>
</div>

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



<div class="card shadow-sm">

    <div class="card-body">

        <form action="<?= base_url('admin/products/update/' . $product['product_id']) ?>" method="post" enctype="multipart/form-data">

            <?= csrf_field() ?>

            <input type="hidden" name="_method" value="PUT">

            

            <div class="mb-3">

                <label for="nama_produk" class="form-label">Nama Produk</label>

                <input type="text" class="form-control" id="nama_produk" name="nama_produk" value="<?= old('nama_produk', esc($product['nama_produk'])) ?>" required>

            </div>

            <div class="mb-3">

                <label for="deskripsi_produk" class="form-label">Deskripsi</label>

                <textarea class="form-control" id="deskripsi_produk" name="deskripsi_produk" rows="3"><?= old('deskripsi_produk', esc($product['deskripsi_produk'])) ?></textarea>

            </div>

            <div class="row">

                <div class="col-md-6 mb-3">

                    <label for="category_id" class="form-label">Kategori</label>

                    <select class="form-select" id="category_id" name="category_id" required>

                        <option value="">-- Pilih Kategori --</option>

                        <?php foreach ($categories as $category): ?>

                            <option value="<?= $category['category_id'] ?>" <?= (old('category_id', $selectedCategoryId ?? null) == $category['category_id']) ? 'selected' : '' ?>>

                                <?= esc($category['nama_kategori']) ?>

                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>

                <div class="col-md-6 mb-3">

                    <label for="subcategory_id" class="form-label">Sub-Kategori</label>

                    <select class="form-select" id="subcategory_id" name="subcategory_id">

                        <option value="">-- Pilih Sub-Kategori (Opsional) --</option>

                    </select>

                </div>

            </div>

            <div class="mb-3">

                <label class="form-label">Pilih Acara (Occasion)</label>

                <div class="row">

                    <?php 

                    $checked_occasions = old('occasions') ?? $product_occasion_ids ?? [];

                    foreach($occasions as $occasion): 

                    ?>

                        <div class="col-md-3">

                            <div class="form-check">

                                <input class="form-check-input" type="checkbox" name="occasions[]" value="<?= $occasion['occasion_id'] ?>" id="occasion_<?= $occasion['occasion_id'] ?>" <?= in_array($occasion['occasion_id'], $checked_occasions) ? 'checked' : '' ?>>

                                <label class="form-check-label" for="occasion_<?= $occasion['occasion_id'] ?>">

                                    <?= esc($occasion['occasion_name']) ?>

                                </label>

                            </div>

                        </div>

                    <?php endforeach; ?>

                </div>

            </div>

            <div class="mb-3">

                <label for="harga" class="form-label">Harga Utama</label>

                <div class="input-group">

                    <span class="input-group-text">Rp</span>

                    <input type="number" class="form-control" id="harga" name="harga" value="<?= old('harga', esc($product['harga'])) ?>" required>

                </div>

            </div>

            <div class="mb-3">

                <label for="gambar_url" class="form-label">Gambar Produk Utama (Biarkan kosong jika tidak ingin mengubah)</label>

                <input class="form-control" type="file" id="gambar_url" name="gambar_url" accept="image/*">

                <?php if ($product['gambar_url']): ?>

                    <p class="mt-2">Gambar saat ini: <a href="<?= base_url('assets/img/gambar/' . $product['gambar_url']) ?>" target="_blank"><?= esc($product['gambar_url']) ?></a></p>

                <?php endif; ?>

            </div>

            

            <hr>



            <div class="mb-3">

                <label class="form-label">Varian Harga & Ukuran</label>

                <div id="variantsContainer">

                    <?php if (!empty($variants)): ?>

                        <?php foreach ($variants as $index => $variant) : ?>

                            <div class="row variant-row mb-2 align-items-center">

                                <div class="col-4">

                                    <input type="hidden" name="variants[<?= $index ?>][id]" value="<?= esc($variant['id']); ?>">

                                    <input type="text" name="variants[<?= $index ?>][name]" class="form-control" placeholder="Nama Varian (cth: Ukuran Sedang)" value="<?= old("variants[{$index}][name]", esc($variant['name'])); ?>" required>

                                </div>

                                <div class="col-3">

                                    <input type="text" name="variants[<?= $index ?>][price]" class="form-control" placeholder="Harga" value="<?= old("variants[{$index}][price]", number_format($variant['price'], 0, ',', '.')); ?>" required>

                                </div>

                                <div class="col-auto">

                                    <input type="file" name="variants[<?= $index ?>][gambar_varian_url]" class="form-control-file" accept="image/*">

                                    <?php if (!empty($variant['gambar_varian_url'])): ?>

                                        <img src="<?= base_url('assets/img/variants/' . esc($variant['gambar_varian_url'])); ?>" alt="Gambar Varian" class="img-thumbnail ms-2" style="width: 50px; height: 50px; object-fit: cover;">

                                        <input type="hidden" name="variants[<?= $index ?>][existing_gambar_varian_url]" value="<?= esc($variant['gambar_varian_url']); ?>">

                                    <?php endif; ?>

                                </div>

                                <div class="col-2">

                                    <button type="button" class="btn btn-danger remove-variant-btn">Hapus</button>

                                </div>

                            </div>

                        <?php endforeach; ?>

                    <?php endif; ?>

                </div>

                <button type="button" id="addVariant" class="btn btn-sm btn-outline-primary mt-2">Tambah Varian</button>

            </div>

            

            <hr>



            <!-- Komponen Produk (dipindah ke luar loop gambar) -->
            <div class="mb-3">
                <label class="form-label">Komponen Produk</label>
                <div id="componentsContainer">
                    <?php if (!empty($components)): foreach ($components as $i => $comp): ?>
                        <div class="row g-2 align-items-end mb-2 component-row">
                            <input type="hidden" name="components[<?= $i ?>][id]" value="<?= esc($comp['id']) ?>">
                            <div class="col-md-4">
                                <label class="form-label">Nama Komponen</label>
                                <input type="text" name="components[<?= $i ?>][component_name]" class="form-control" value="<?= esc($comp['component_name']) ?>" required>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Qty</label>
                                <input type="number" step="0.01" name="components[<?= $i ?>][quantity]" class="form-control" value="<?= esc($comp['quantity']) ?>" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Biaya Satuan</label>
                                <input type="number" step="0.01" name="components[<?= $i ?>][unit_cost]" class="form-control" value="<?= esc($comp['unit_cost']) ?>" required>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Urutan</label>
                                <input type="number" name="components[<?= $i ?>][sort_order]" class="form-control" value="<?= esc($comp['sort_order'] ?? 0) ?>">
                            </div>
                            <div class="col-md-1">
                                <button type="button" class="btn btn-outline-danger w-100 remove-component">Hapus</button>
                            </div>
                        </div>
                    <?php endforeach; endif; ?>
                </div>
                <button type="button" id="addComponent" class="btn btn-sm btn-outline-primary mt-2">Tambah Komponen</button>
                <small class="text-muted d-block mt-1">Tambah, ubah, atau hapus komponen produk.</small>
            </div>

            <div class="mb-3">

                <label class="form-label">Gambar Tambahan</label>

                <input type="file" class="form-control" id="additional_images" name="additional_images[]" accept="image/*" multiple>

                <small class="form-text text-muted">Anda bisa memilih lebih dari satu gambar.</small>

            </div>



            <div class="row mt-3">

                <?php if (!empty($images)): foreach ($images as $img) : ?>

                <div class="col-md-3 mb-3 text-center">

                    <img src="<?= base_url('assets/img/products/' . $img['image_url']); ?>" class="img-thumbnail mb-2" style="height: 150px; object-fit: cover;">

                    <div>

                         <input type="checkbox" name="delete_images[<?= $img['id']; ?>]" value="1" class="form-check-input"> <label>Hapus</label>

                    </div>

                </div>

                <?php endforeach; endif; ?>

            </div>

            

            <hr>



            <div class="form-check form-switch mb-3">

                <input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active" value="1" <?= old('is_active', $product['is_active']) ? 'checked' : '' ?>>

                <label class="form-check-label" for="is_active">Aktifkan Produk</label>

            </div>

            

            <a href="<?= base_url('admin/products') ?>" class="btn btn-secondary">Batal</a>

            <button type="submit" class="btn btn-primary">Perbarui Produk</button>

            

        </form>

    </div>

</div>



<script>

    document.addEventListener('DOMContentLoaded', function() {

        const categorySelect = document.getElementById('category_id');

        const subcategorySelect = document.getElementById('subcategory_id');

        const selectedSubcategoryId = "<?= $selectedSubcategoryId ?? '' ?>";

        const selectedCategoryId = "<?= $selectedCategoryId ?? '' ?>";



        function loadSubcategories(categoryId, selectedSubId) {

            subcategorySelect.innerHTML = '<option value="">-- Pilih Sub-Kategori (Opsional) --</option>';

            if (categoryId) {

                fetch(`<?= base_url('admin/get-subcategories/') ?>${categoryId}`)

                    .then(response => response.json())

                    .then(data => {

                        data.forEach(subcategory => {

                            let option = document.createElement('option');

                            option.value = subcategory.sub_cat_id;

                            option.textContent = subcategory.sub_cat_name;

                            if (subcategory.sub_cat_id == selectedSubId) {

                                option.selected = true;

                            }

                            subcategorySelect.appendChild(option);

                        });

                    })

                    .catch(error => console.error('Error:', error));

            }

        }

        

        categorySelect.addEventListener('change', function() {

            loadSubcategories(this.value, null);

        });

        

        if (selectedCategoryId) {

            loadSubcategories(selectedCategoryId, selectedSubcategoryId);

        }



        // Initialize variant index from existing variants

        let variantIndex = <?= count($variants ?? []) ?>; 



        document.getElementById('addVariant').addEventListener('click', function() {

            const container = document.getElementById('variantsContainer');

            const newRow = document.createElement('div');

            newRow.className = 'row variant-row mb-2 align-items-center';

            newRow.innerHTML = `

                <div class="col-4">

                    <input type="hidden" name="variants[${variantIndex}][id]" value="new">

                    <input type="text" name="variants[${variantIndex}][name]" class="form-control" placeholder="Nama Varian (cth: Ukuran Besar)" required>

                </div>

                <div class="col-3">

                    <input type="text" name="variants[${variantIndex}][price]" class="form-control" placeholder="Harga" required>

                </div>

                <div class="col-auto">

                    <input type="file" name="variants[${variantIndex}][gambar_varian_url]" class="form-control-file" accept="image/*">

                </div>

                <div class="col-2">

                    <button type="button" class="btn btn-danger remove-variant-btn">Hapus</button>

                </div>

            `;

            container.appendChild(newRow);

            variantIndex++;



            // Add event listener for the new remove button

            newRow.querySelector('.remove-variant-btn').addEventListener('click', function() {

                newRow.remove();

            });

        });



        // Add event listeners for existing remove buttons (if not already handled)

        document.querySelectorAll('.remove-variant-btn').forEach(button => {

            button.addEventListener('click', function() {

                button.closest('.variant-row').remove();

            });

        });

        // Validasi ukuran file gambar (2MB)
        function validateFileSize(input, maxSizeMB = 2) {
            const file = input.files[0];
            if (file) {
                const fileSizeMB = file.size / 1024 / 1024;
                if (fileSizeMB > maxSizeMB) {
                    alert(`Ukuran file terlalu besar! Maksimal ${maxSizeMB}MB. Ukuran file Anda: ${fileSizeMB.toFixed(2)}MB`);
                    input.value = '';
                    return false;
                }
            }
            return true;
        }

        // ===== Komponen Produk (dinamis) =====
        let compIndex = <?= count($components ?? []) ?>;
        const compContainer = document.getElementById('componentsContainer');
        const addCompBtn = document.getElementById('addComponent');

        function addComponentRow(values = {}) {
            const idx = compIndex++;
            const row = document.createElement('div');
            row.className = 'row g-2 align-items-end mb-2 component-row';
            row.innerHTML = `
                <input type="hidden" name="components[${idx}][id]" value="new">
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
        // Tambah baris komponen baru
        addCompBtn.addEventListener('click', () => addComponentRow());
        // Bind remove to existing component rows
        document.querySelectorAll('#componentsContainer .component-row .remove-component').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const row = e.target.closest('.component-row');
                if (row) row.remove();
            });
        });

        // Saat submit, hapus baris komponen tanpa nama agar tidak diproses
        const formEl = document.querySelector('form');
        if (formEl) {
            formEl.addEventListener('submit', () => {
                document.querySelectorAll('#componentsContainer .component-row').forEach(row => {
                    const nameInput = row.querySelector('input[name*="[component_name]"]');
                    if (!nameInput || nameInput.value.trim() === '') {
                        row.remove();
                    }
                });
            });
        }

    });

</script>

<?= $this->endSection() ?>