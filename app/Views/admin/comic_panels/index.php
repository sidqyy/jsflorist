<?= $this->extend('admin/layout/main') ?>
<?= $this->section('title') ?>Panel Komik<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Panel Komik</h1>
                    <p class="text-muted mb-0">Episode: <strong><?= esc($episode['title']) ?></strong></p>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?= base_url('admin/comics') ?>">Komik</a></li>
                        <li class="breadcrumb-item active">Panel</li>
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
                        <div class="card-header d-flex align-items-center justify-content-between">
                            <h3 class="card-title mb-0"><i class="fas fa-images me-2"></i>Daftar Panel</h3>
                            <div class="card-tools d-flex align-items-center" style="gap:.5rem;">
                                <a href="<?= base_url('admin/comics/' . $episode['id'] . '/panels/create') ?>" class="btn btn-primary btn-sm">
                                    <i class="fas fa-plus"></i> Tambah Panel
                                </a>
                                <a href="<?= base_url('admin/comics') ?>" class="btn btn-secondary btn-sm">
                                    <i class="fas fa-arrow-left"></i> Kembali
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            <?php if (session()->getFlashdata('success')) : ?>
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    <i class="fas fa-check-circle me-1"></i> <?= session()->getFlashdata('success') ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            <?php endif; ?>

                            <?php if (session()->getFlashdata('error')) : ?>
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <i class="fas fa-times-circle me-1"></i> <?= session()->getFlashdata('error') ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            <?php endif; ?>

                            <div class="table-responsive">
                                <table class="table table-bordered table-striped table-hover mb-0">
                                    <thead>
                                        <tr class="table-light">
                                            <th style="width:60px">No</th>
                                            <th style="width:140px">Gambar</th>
                                            <th>Caption</th>
                                            <th style="width:120px">Panel</th>
                                            <th style="width:120px">Status</th>
                                            <th style="width:140px">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($panels)) : ?>
                                            <?php foreach ($panels as $index => $panel) : ?>
                                                <tr>
                                                    <td class="text-center align-middle"><?= $index + 1 ?></td>
                                                    <td class="align-middle">
                                                        <img
                                                            src="<?= base_url('uploads/comics/panels/' . $panel['image_path']) ?>"
                                                            alt="Panel <?= esc($panel['panel_number']) ?>"
                                                            class="img-fluid img-thumbnail"
                                                            style="width:120px;height:72px;object-fit:cover;"
                                                            loading="lazy"
                                                        >
                                                    </td>
                                                    <td class="align-middle">
                                                        <?= esc($panel['caption'] ?? '-') ?>
                                                    </td>
                                                    <td class="align-middle">
                                                        <span class="badge bg-primary">#<?= esc($panel['panel_number']) ?></span>
                                                    </td>
                                                    <td class="align-middle">
                                                        <span class="badge <?= $panel['is_active'] ? 'bg-success' : 'bg-secondary' ?>">
                                                            <?= $panel['is_active'] ? 'Aktif' : 'Tidak Aktif' ?>
                                                        </span>
                                                    </td>
                                                    <td class="align-middle">
                                                        <div class="btn-group" role="group" aria-label="Aksi panel">
                                                            <a href="<?= base_url('admin/comics/' . $episode['id'] . '/panels/edit/' . $panel['id']) ?>" class="btn btn-warning btn-sm" title="Edit">
                                                                <i class="fas fa-edit"></i>
                                                            </a>
                                                            <a href="<?= base_url('admin/comics/' . $episode['id'] . '/panels/delete/' . $panel['id']) ?>" class="btn btn-danger btn-sm btn-delete-panel" data-title="Panel #<?= esc($panel['panel_number']) ?>" title="Hapus">
                                                                <i class="fas fa-trash"></i>
                                                            </a>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else : ?>
                                            <tr>
                                                <td colspan="6" class="text-center text-muted">
                                                    <i class="far fa-folder-open me-1"></i> Belum ada panel.
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-labelledby="confirmDeleteTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="confirmDeleteTitle"><i class="fas fa-exclamation-triangle me-2"></i>Konfirmasi Hapus</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Apakah Anda yakin ingin menghapus <strong id="deletePanelTitle">-</strong>?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <a href="#" id="deleteConfirmBtn" class="btn btn-danger">Hapus</a>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('extra_js') ?>
<script>
(function() {
    var deleteButtons = document.querySelectorAll('.btn-delete-panel');
    var modalEl = document.getElementById('confirmDeleteModal');
    var bsModal = (window.bootstrap && modalEl) ? new bootstrap.Modal(modalEl) : null;

    deleteButtons.forEach(function(btn){
        btn.addEventListener('click', function(e){
            e.preventDefault();
            var href = this.getAttribute('href');
            var title = this.getAttribute('data-title') || '-';
            var confirmBtn = document.getElementById('deleteConfirmBtn');
            var titleEl = document.getElementById('deletePanelTitle');
            if (confirmBtn && titleEl) {
                confirmBtn.setAttribute('href', href);
                titleEl.textContent = title;
            }
            if (bsModal) {
                bsModal.show();
            } else if (window.jQuery && typeof $('#confirmDeleteModal').modal === 'function') {
                $('#confirmDeleteModal').modal('show');
            } else {
                if (confirm('Apakah Anda yakin ingin menghapus ' + title + ' ?')) {
                    window.location.href = href;
                }
            }
        });
    });
})();
</script>
<?= $this->endSection() ?>
