<?= $this->extend('admin/layout/main') ?>
<?= $this->section('title') ?>Komik<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Manajemen Komik</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Home</a></li>
                        <li class="breadcrumb-item active">Komik</li>
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
                            <h3 class="card-title mb-0"><i class="fas fa-book-open me-2"></i>Daftar Episode</h3>
                            <div class="card-tools d-flex align-items-center" style="gap:.5rem;">
                                <div class="input-group input-group-sm" style="width: 260px;">
                                    <span class="input-group-text bg-white"><i class="fas fa-search"></i></span>
                                    <input type="text" class="form-control" placeholder="Cari judul..." aria-label="Cari judul" id="comicSearch">
                                </div>
                                <a href="<?= base_url('admin/comics/create') ?>" class="btn btn-primary btn-sm">
                                    <i class="fas fa-plus"></i> Tambah Episode
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
                                            <th style="width:140px">Cover</th>
                                            <th>Judul</th>
                                            <th style="width:120px">Episode</th>
                                            <th style="width:120px">Panel</th>
                                            <th style="width:120px">Status</th>
                                            <th style="width:160px">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $no = 1 + (10 * ($pager->getCurrentPage('comic_episodes') - 1)); ?>
                                        <?php if (!empty($episodes)) : ?>
                                            <?php foreach ($episodes as $episode) : ?>
                                                <tr>
                                                    <td class="text-center align-middle"><?= $no++ ?></td>
                                                    <td class="align-middle">
                                                        <?php if (!empty($episode['cover_image'])): ?>
                                                            <img
                                                                src="<?= base_url('uploads/comics/episodes/' . $episode['cover_image']) ?>"
                                                                alt="<?= esc($episode['title']) ?>"
                                                                class="img-fluid img-thumbnail"
                                                                style="width:120px;height:72px;object-fit:cover;"
                                                                loading="lazy"
                                                            >
                                                        <?php else: ?>
                                                            <div class="text-muted small">Tanpa cover</div>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="align-middle" data-col="title"><strong><?= esc($episode['title']) ?></strong></td>
                                                    <td class="align-middle">
                                                        <span class="badge bg-secondary">#<?= esc($episode['episode_number']) ?></span>
                                                    </td>
                                                    <td class="align-middle">
                                                        <?= (int) ($panelCounts[$episode['id']] ?? 0) ?> panel
                                                    </td>
                                                    <td class="align-middle">
                                                        <button type="button"
                                                            class="btn btn-sm toggle-status <?= $episode['is_active'] ? 'btn-success' : 'btn-secondary' ?>"
                                                            data-id="<?= $episode['id'] ?>">
                                                            <?= $episode['is_active'] ? 'Aktif' : 'Tidak Aktif' ?>
                                                        </button>
                                                    </td>
                                                    <td class="align-middle">
                                                        <div class="btn-group" role="group" aria-label="Aksi komik">
                                                            <a href="<?= base_url('admin/comics/' . $episode['id'] . '/panels') ?>" class="btn btn-info btn-sm" title="Panel">
                                                                <i class="fas fa-images"></i>
                                                            </a>
                                                            <a href="<?= base_url('admin/comics/edit/' . $episode['id']) ?>" class="btn btn-warning btn-sm" title="Edit">
                                                                <i class="fas fa-edit"></i>
                                                            </a>
                                                            <a href="<?= base_url('admin/comics/delete/' . $episode['id']) ?>" class="btn btn-danger btn-sm btn-delete-episode" data-title="<?= esc($episode['title']) ?>" title="Hapus">
                                                                <i class="fas fa-trash"></i>
                                                            </a>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else : ?>
                                            <tr>
                                                <td colspan="7" class="text-center text-muted">
                                                    <i class="far fa-folder-open me-1"></i> Belum ada episode.
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="card-footer clearfix">
                            <?= $pager->links('comic_episodes', 'bootstrap_pager') ?>
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
                Apakah Anda yakin ingin menghapus episode: <strong id="deleteEpisodeTitle">-</strong>?
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
    var searchInput = document.getElementById('comicSearch');
    if (searchInput) {
        searchInput.addEventListener('input', function () {
            var term = this.value.toLowerCase();
            var rows = document.querySelectorAll('tbody tr');
            rows.forEach(function (row) {
                var titleCell = row.querySelector('[data-col="title"]');
                if (!titleCell) return;
                var match = titleCell.textContent.toLowerCase().indexOf(term) !== -1;
                row.style.display = match ? '' : 'none';
            });
        });
    }

    var deleteButtons = document.querySelectorAll('.btn-delete-episode');
    var modalEl = document.getElementById('confirmDeleteModal');
    var bsModal = (window.bootstrap && modalEl) ? new bootstrap.Modal(modalEl) : null;

    deleteButtons.forEach(function(btn){
        btn.addEventListener('click', function(e){
            e.preventDefault();
            var href = this.getAttribute('href');
            var title = this.getAttribute('data-title') || '-';
            var confirmBtn = document.getElementById('deleteConfirmBtn');
            var titleEl = document.getElementById('deleteEpisodeTitle');
            if (confirmBtn && titleEl) {
                confirmBtn.setAttribute('href', href);
                titleEl.textContent = title;
            }
            if (bsModal) {
                bsModal.show();
            } else if (window.jQuery && typeof $('#confirmDeleteModal').modal === 'function') {
                $('#confirmDeleteModal').modal('show');
            } else {
                if (confirm('Apakah Anda yakin ingin menghapus episode: ' + title + ' ?')) {
                    window.location.href = href;
                }
            }
        });
    });

    var toggleButtons = document.querySelectorAll('.toggle-status');
    toggleButtons.forEach(function(btn){
        btn.addEventListener('click', function(){
            var id = this.getAttribute('data-id');
            fetch('<?= base_url('admin/comics/toggle-status') ?>/' + id, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            }).then(function(res){ return res.json(); })
              .then(function(data){
                  if (!data || !data.success) {
                      alert(data && data.message ? data.message : 'Gagal memperbarui status.');
                      return;
                  }
                  if (btn.classList.contains('btn-success')) {
                      btn.classList.remove('btn-success');
                      btn.classList.add('btn-secondary');
                      btn.textContent = 'Tidak Aktif';
                  } else {
                      btn.classList.remove('btn-secondary');
                      btn.classList.add('btn-success');
                      btn.textContent = 'Aktif';
                  }
              }).catch(function(){
                  alert('Terjadi kesalahan saat mengubah status.');
              });
        });
    });
})();
</script>
<?= $this->endSection() ?>
