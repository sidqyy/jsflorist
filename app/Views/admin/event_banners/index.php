<?= $this->extend('admin/layout/main') ?>

<?= $this->section('title') ?>
<?= esc($title) ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><?= esc($title) ?></h3>
                <div class="card-tools">
                    <a href="<?= base_url('admin/event-banners/create') ?>" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Tambah Event Banner
                    </a>
                </div>
            </div>
            <div class="card-body">
                <?php if (session()->getFlashdata('success')): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?= session()->getFlashdata('success') ?>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                <?php endif; ?>

                <?php if (session()->getFlashdata('error')): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?= session()->getFlashdata('error') ?>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                <?php endif; ?>

                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th width="5%">No</th>
                                <th width="12%">Gambar</th>
                                <th width="18%">Judul</th>
                                <th width="12%">Link URL</th>
                                <th width="10%">Mulai</th>
                                <th width="10%">Selesai</th>
                                <th width="10%">Domain</th>
                                <th width="8%">Status</th>
                                <th width="15%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($eventBanners)): ?>
                                <?php foreach ($eventBanners as $index => $banner): ?>
                                    <tr>
                                        <td><?= $index + 1 ?></td>
                                        <td>
                                            <img src="<?= base_url('uploads/event_banners/' . esc($banner['image_url'])) ?>" 
                                                 alt="<?= esc($banner['title']) ?>" 
                                                 class="img-thumbnail" 
                                                 style="max-width: 80px; max-height: 60px;">
                                        </td>
                                        <td><?= esc($banner['title']) ?></td>
                                        <td>
                                            <?php if (!empty($banner['link_url'])): ?>
                                                <a href="<?= esc($banner['link_url']) ?>" target="_blank" class="text-primary">
                                                    <?= esc(strlen($banner['link_url']) > 30 ? substr($banner['link_url'], 0, 30) . '...' : $banner['link_url']) ?>
                                                </a>
                                            <?php else: ?>
                                                <span class="text-muted">Tidak ada link</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= date('d/m/Y H:i', strtotime($banner['start_date'])) ?></td>
                                        <td><?= date('d/m/Y H:i', strtotime($banner['end_date'])) ?></td>
<td>
    <?php if (($banner['domain_specific'] ?? 0) == 1): ?>
        <?php 
            $rawData = $banner['allowed_domains'] ?? '';
            // Coba decode JSON
            $domains = json_decode($rawData, true);
            
            // Kalau bukan JSON (masih string biasa), jadikan array manual
            if (empty($domains) && !empty($rawData)) {
                $domains = [$rawData]; 
            }
        ?>

        <?php if (!empty($domains)): ?>
            <div class="d-flex flex-wrap" style="gap: 4px;">
                <?php foreach ($domains as $d): 
                    $d = trim($d); // Bersihkan spasi
                    // Logika warna manual
                    $bgColor = '#17a2b8'; // Default Biru Tois (Info)
                    if (strpos(strtolower($d), 'jsflorist') !== false) $bgColor = '#007bff'; // Biru (Primary)
                    if (strpos(strtolower($d), 'poppy') !== false) $bgColor = '#dc3545'; // Merah (Danger)
                ?>
                    <span class="badge text-white shadow-sm" 
                          style="background-color: <?= $bgColor ?>; padding: 5px 10px; font-size: 12px; display: inline-block; min-width: 50px; text-align: center;">
                        <?= esc($d) ?>
                    </span>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <span class="badge badge-secondary">Domain Kosong</span>
        <?php endif; ?>

    <?php else: ?>
        <span class="badge text-white shadow-sm" 
              style="background-color: #28a745; padding: 5px 10px; font-size: 12px; display: inline-block; min-width: 90px; text-align: center; font-weight: bold;">
            Semua Domain
        </span>
    <?php endif; ?>
</td>
                                        <td>
                                            <button type="button" 
                                                    class="btn btn-sm toggle-status <?= $banner['is_active'] ? 'btn-success' : 'btn-secondary' ?>" 
                                                    data-id="<?= $banner['id'] ?>">
                                                <?= $banner['is_active'] ? 'Aktif' : 'Tidak Aktif' ?>
                                            </button>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="<?= base_url('admin/event-banners/edit/' . $banner['id']) ?>" 
                                                   class="btn btn-warning btn-sm" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button type="button" 
                                                        class="btn btn-danger btn-sm delete-banner" 
                                                        data-id="<?= $banner['id'] ?>" 
                                                        title="Hapus">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="9" class="text-center">Belum ada event banner.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Konfirmasi Hapus</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                Apakah Anda yakin ingin menghapus event banner ini?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <a href="#" id="confirmDelete" class="btn btn-danger">Hapus</a>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('extra_js') ?>
<script>
$(document).ready(function() {
    // Toggle Status
    $('.toggle-status').click(function() {
        const id = $(this).data('id');
        const button = $(this);
        
        $.ajax({
            url: '<?= base_url('admin/event-banners/toggle-status') ?>/' + id,
            type: 'POST',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    if (button.hasClass('btn-success')) {
                        button.removeClass('btn-success').addClass('btn-secondary').text('Tidak Aktif');
                    } else {
                        button.removeClass('btn-secondary').addClass('btn-success').text('Aktif');
                    }
                    toastr.success(response.message);
                } else {
                    toastr.error(response.message);
                }
            },
            error: function() {
                toastr.error('Terjadi kesalahan saat mengubah status.');
            }
        });
    });

    // Delete Banner
    $('.delete-banner').click(function() {
        const id = $(this).data('id');
        $('#confirmDelete').attr('href', '<?= base_url('admin/event-banners/delete') ?>/' + id);
        $('#deleteModal').modal('show');
    });
});
</script>
<?= $this->endSection() ?>
