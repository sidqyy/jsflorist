<?= $this->extend('admin/layout/main') ?>

<?= $this->section('content') ?>
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Manajemen Artikel</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Home</a></li>
                        <li class="breadcrumb-item active">Artikel</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Daftar Artikel</h3>
                            <div class="card-tools">
                                <a href="<?= base_url('admin/articles/create') ?>" class="btn btn-primary btn-sm">
                                    <i class="fas fa-plus"></i> Tambah Artikel
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            <?php if (session()->getFlashdata('success')) : ?>
                                <div class="alert alert-success">
                                    <?= session()->getFlashdata('success') ?>
                                </div>
                            <?php endif; ?>
                            <table id="example1" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Gambar</th>
                                        <th>Judul</th>
                                        <th>Tanggal Dibuat</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $no = 1 + (10 * ($pager->getCurrentPage('articles') - 1)); ?>
                                    <?php foreach ($articles as $article) : ?>
                                        <tr>
                                            <td><?= $no++ ?></td>
                                            <td>
                                                <img src="<?= base_url('assets/img/artikel/' . $article['gambar']) ?>" alt="<?= esc($article['judul']) ?>" width="100">
                                            </td>
                                            <td><?= esc($article['judul']) ?></td>
                                            <td><?= date('d F Y H:i', strtotime($article['tanggal_dibuat'])) ?></td>
                                            <td>
                                                <a href="<?= base_url('admin/articles/edit/' . $article['id_artikel']) ?>" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i></a>
                                                <a href="<?= base_url('admin/articles/delete/' . $article['id_artikel']) ?>" class="btn btn-danger btn-sm" onclick="return confirm('Apakah Anda yakin ingin menghapus artikel ini?')"><i class="fas fa-trash"></i></a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="card-footer clearfix">
                            <?= $pager->links('articles', 'bootstrap_pager') ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
<?= $this->endSection() ?>
