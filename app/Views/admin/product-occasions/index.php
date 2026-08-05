<?= $this->extend('admin/layout/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Pilih Occasion untuk Menambahkan Produk</h3>
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

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama Occasion</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($occasions)): ?>
                                    <tr>
                                        <td colspan="3" class="text-center">Tidak ada occasion tersedia</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($occasions as $index => $occasion): ?>
                                        <tr>
                                            <td><?= $index + 1 ?></td>
                                            <td><?= esc($occasion['occasion_name']) ?></td>
                                            <td>
                                                <a href="<?= base_url("/admin/product-occasions/products/{$occasion['occasion_id']}") ?>" 
                                                   class="btn btn-primary btn-sm">
                                                    <i class="fas fa-plus"></i> Tambah Produk
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>

                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    $(document).ready(function() {
        $('.table').DataTable({
            responsive: true,
            language: {
                url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/id.json'
            }
        });
    });
</script>
<?= $this->endSection() ?>
