<?= $this->extend('admin/layout/main') ?>

<?= $this->section('title') ?>
Manajemen Produk
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<h1>Manajemen Produk</h1>

<div class="row mb-3">
    <div class="col-md-6">
        <a href="<?= base_url('admin/products/create') ?>" class="btn btn-primary">Tambah Produk Baru</a>
    </div>
    <div class="col-md-6">
        <form action="<?= base_url('admin/products') ?>" method="get" class="float-end">
            <div class="input-group">
                <select class="form-select" name="category">
                    <option value="">-- Semua Kategori --</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= $category['category_id'] ?>" <?= ($selectedCategory == $category['category_id']) ? 'selected' : '' ?>>
                            <?= esc($category['nama_kategori']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="btn btn-secondary">Filter</button>
            </div>
        </form>
    </div>
</div>

<?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success">
        <?= session()->getFlashdata('success') ?>
    </div>
<?php endif ?>

<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger">
        <?= session()->getFlashdata('error') ?>
    </div>
<?php endif ?>

<div class="card shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Gambar</th>
                        <th>Nama Produk</th>
                        <th>Kategori</th>
                        <th>Harga</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $i = 1 + ($pager->getCurrentPage() - 1) * $pager->getPerPage(); ?>
                    <?php if (!empty($products)): ?>
                        <?php foreach ($products as $product): ?>
                            <tr>
                                <td><?= $i++ ?></td>
                                <td>
                                    <img src="<?= base_url('assets/img/gambar/' . $product['gambar_url']) ?>" alt="<?= esc($product['nama_produk']) ?>" style="width: 50px;">
                                </td>
                                <td><?= esc($product['nama_produk']) ?></td>
                              <td>
    <?php if (!empty($product['sub_category_name'])) : ?>
        <?= esc($product['sub_category_name']) ?>
    <?php elseif (!empty($product['direct_main_category_name'])) : ?>
        <?= esc($product['direct_main_category_name']) ?>
    <?php elseif (!empty($product['main_category_name'])) : ?>
        <?= esc($product['main_category_name']) ?>
    <?php else : ?>
        -
    <?php endif; ?>
</td>
                                <td>Rp<?= number_format(esc($product['harga']), 0, ',', '.') ?></td>
                                <td><?= ($product['is_active'] == 1) ? 'Aktif' : 'Nonaktif' ?></td>
                                <td>
                                    <a href="<?= base_url('admin/products/edit/' . $product['product_id']) ?>" class="btn btn-warning btn-sm">Edit</a>
                                    <a href="<?= base_url('admin/products/delete/' . $product['product_id']) ?>" class="btn btn-danger btn-sm" onclick="return confirm('Apakah Anda yakin ingin menghapus produk ini?')">Hapus</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7">Tidak ada produk ditemukan.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?= $pager->links('products', 'bootstrap_pager') ?>
    </div>
</div>

<?= $this->endSection() ?>