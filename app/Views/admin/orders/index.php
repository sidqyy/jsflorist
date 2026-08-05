<?= $this->extend('admin/layout/main') ?>

<?= $this->section('title') ?>
Daftar Pesanan
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<style>
/* UI polish for Orders page */
.orders-hero {
    background: linear-gradient(135deg, rgba(208,156,76,.12), rgba(235,212,182,.12));
    border: 1px solid rgba(0,0,0,.06);
    border-radius: 1rem;
    padding: 1rem 1.25rem;
}
.stat-chip { display:inline-flex; align-items:center; gap:.5rem; padding:.5rem .75rem; border-radius:.75rem; background:#fff; border:1px solid rgba(0,0,0,.06); box-shadow:0 2px 8px rgba(0,0,0,.05); }
.stat-chip .num { font-weight: 800; }
.toolbar { gap:.75rem; }
.toolbar .form-control, .toolbar .form-select { border-radius:.6rem; }
.table thead th { position: sticky; top: 0; z-index: 2; }
.table-hover tbody tr:hover { background-color: rgba(235,212,182,.12); }
.badge-status { border-radius: 2rem; font-weight: 600; padding:.5rem .75rem; }
.btn-icon i { vertical-align: -1px; }
.card-soft { border:1px solid rgba(0,0,0,.06); border-radius: .75rem; box-shadow: 0 4px 12px rgba(0,0,0,.06); }
.border-top-soft { border-top: 1px solid rgba(0,0,0,.08) !important; }
/* Rounded table wrapper */
.table-rounded { border:1px solid rgba(0,0,0,.06); border-radius:.75rem; overflow:hidden; background:#fff; box-shadow:0 4px 12px rgba(0,0,0,.06); }
.table-rounded .table { margin-bottom: 0; }
.table-rounded .table thead th { border-top: 0; }

/* Mobile responsiveness improvements */
@media (max-width: 768px) {
    .table-responsive {
        overflow-x: auto !important;
        -webkit-overflow-scrolling: touch;
        touch-action: pan-x;
        border-radius: .75rem;
    }
    
    .table-responsive table {
        min-width: 800px; /* Force minimum width to enable horizontal scroll */
    }
    
    /* Make action buttons smaller on mobile */
    .btn-sm {
        padding: .25rem .5rem;
        font-size: .75rem;
    }
    
    /* Stack toolbar items on mobile */
    .toolbar {
        flex-direction: column;
        align-items: stretch !important;
        gap: .5rem;
    }
    
    .toolbar > * {
        width: 100%;
    }
    
    /* Better mobile search and filter */
    #activeSearch, #statusFilter {
        min-width: auto !important;
    }
}
</style>

<?php 
$rawIncomplete = isset($incomplete_orders) ? $incomplete_orders : [];
// Hitung jumlah aktif tanpa menghitung yang dibatalkan, tetapi tetap tampilkan semuanya di tabel
$totalIncomplete = count(array_filter($rawIncomplete, function($order){
    $status = $order['status_pesanan'] ?? '';
    return $status !== 'Dibatalkan' && $status !== 'Dibatalkan Sistem';
}));
$totalCompletedToday = isset($completed_today) ? count($completed_today) : 0; 
?>

<div class="orders-hero mb-4">
    <div class="d-flex flex-wrap align-items-center justify-content-between">
        <div>
            <h1 class="h3 mb-1">Manajemen Pesanan</h1>
            <div class="text-muted">Kelola pesanan aktif, update status, dan pantau penyelesaian hari ini</div>
        </div>
        <div class="d-flex flex-wrap gap-2 mt-3 mt-md-0">
            <div class="stat-chip"><span class="text-muted">Aktif</span> <span class="num text-primary"><?= number_format($totalIncomplete) ?></span></div>
            <div class="stat-chip"><span class="text-muted">Selesai Hari Ini</span> <span class="num text-success"><?= number_format($totalCompletedToday) ?></span></div>
        </div>
    </div>
</div>

<?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= esc(session()->getFlashdata('success')) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>
<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= esc(session()->getFlashdata('error')) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<!-- Tabel Pesanan Aktif (Belum Selesai) -->
<div class="d-flex align-items-center justify-content-between mt-4 mb-3 flex-wrap toolbar">
    <h2 class="h5 m-0">Pesanan Aktif (Belum Selesai)</h2>
    <div class="d-flex align-items-center flex-wrap toolbar">
        <input type="search" id="activeSearch" class="form-control" placeholder="Cari ID, nama, nomor, metode..." style="min-width:240px;">
        <select id="statusFilter" class="form-select">
            <option value="">Semua Status</option>
            <option>Menunggu Bukti Transfer</option>
            <option>Menunggu Verifikasi Admin</option>
            <option>Dikonfirmasi</option>
            <option>Diproses</option>
            <option>Siap Dikirim/Diambil</option>
            <option>Dalam Pengiriman</option>
            <option>Selesai</option>
            <option>Dikembalikan</option>
            <option>Dibatalkan</option>
            <option>Dibatalkan Sistem</option>
        </select>
        <button id="resetFilter" type="button" class="btn btn-outline-secondary">Reset</button>
    </div>
    <div class="w-100 d-md-none"></div>
</div>
    <div class="table-responsive table-rounded" style="overflow-x: auto; -webkit-overflow-scrolling: touch;">
                        <table id="activeOrdersTable" class="table table-striped table-hover align-middle">

        <thead class="bg-primary text-white">
            <tr>
                <th>ID Pesanan</th>
                <th>Tanggal Pesan</th>
                <th>Pelanggan</th>
                <th>Total Harga</th>
                <th>Metode Pembayaran</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($rawIncomplete)): ?>
                <?php foreach ($rawIncomplete as $order): ?>
                    <?php
                        $status = $order['status_pesanan'];
                        $icon = 'bi-question-circle';
                        $color = 'bg-dark';

                        switch ($status) {
                  case 'Menunggu Bukti Transfer':
                                $color = 'bg-secondary'; $icon = 'bi-cash-coin'; break;
                            case 'Menunggu Verifikasi Admin':
                                $color = 'bg-warning'; $icon = 'bi-shield-check'; break;
                            case 'Dikonfirmasi':
                                $color = 'bg-primary'; $icon = 'bi-patch-check'; break;
                            case 'Diproses':
                                $color = 'bg-warning'; $icon = 'bi-gear'; break;
                            case 'Siap Dikirim/Diambil':
                                $color = 'bg-info'; $icon = 'bi-box-seam'; break;
                            case 'Dalam Pengiriman':
                                $color = 'bg-info'; $icon = 'bi-truck'; break;
                            case 'Selesai':
                                $color = 'bg-success'; $icon = 'bi-check2-circle'; break;
                            case 'Dibatalkan':
                                $color = 'bg-danger'; $icon = 'bi-x-octagon'; break;
                            case 'Dikembalikan':
                                $color = 'bg-danger'; $icon = 'bi-arrow-counterclockwise'; break;
                            case 'Dibatalkan Sistem':
                                $color = 'bg-danger'; $icon = 'bi-x-octagon'; break;
                        }
                    ?>
                                        <tr data-status="<?= esc($status) ?>" data-text="<?= esc($order['order_id']) ?> <?= esc($order['penerima_nama']) ?> <?= esc($order['nomor_pemesan']) ?> <?= esc($order['metode_pembayaran']) ?>">
                        <td><?= esc($order['order_id']) ?></td>
                        <td><?= esc(date('d M Y H:i', strtotime($order['tanggal_pesan']))) ?></td>
                        <td><?= esc($order['penerima_nama']) ?> (<?= esc($order['nomor_pemesan']) ?>)</td>
                        <td>Rp<?= number_format($order['total_harga'], 0, ',', '.') ?></td>
                        <td><?= esc($order['metode_pembayaran']) ?></td>
                        <td>
                                                        <span class="badge <?= $color ?> text-white badge-status">
                                <i class="bi <?= $icon ?>"></i> <?= esc($status) ?>
                            </span>
                        </td>
                        <td>
                            <a href="<?= base_url('admin/orders/detail/' . $order['order_id']) ?>" 
                                                             class="btn btn-sm btn-primary btn-icon" 
                               title="Lihat detail pesanan">
                                <i class="bi bi-eye"></i> Detail
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                                <tr>
                    <td colspan="7" class="text-center">Tidak ada pesanan aktif yang ditemukan.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Pager -->
<div class="d-flex justify-content-center">
    <?php if ($pager) :?>
        <?= $pager->links('incomplete', 'default_full') ?>
    <?php endif ?>
</div>

<!-- Tabel Pesanan Selesai Hari Ini -->
<h2 class="mt-5 pt-4 border-top-soft">Pesanan Selesai Hari Ini</h2>
    <div class="table-responsive table-rounded" style="overflow-x: auto; -webkit-overflow-scrolling: touch;">
            <table class="table table-striped table-hover align-middle">

        <thead class="bg-primary text-white">
            <tr>
                <th>ID Pesanan</th>
                <th>Tanggal Selesai</th>
                <th>Pelanggan</th>
                <th>Total Harga</th>
                <th>Metode Pembayaran</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($completed_today)): ?>
                <?php foreach ($completed_today as $order): ?>
                    <?php
                        $status = $order['status_pesanan'];
                        $icon = 'bi-question-circle';
                        $color = 'bg-dark';

                        switch ($status) {
                            case 'Belum Dibayar':
                                $color = 'bg-secondary'; $icon = 'bi-wallet2'; break;
                            case 'Diproses':
                                $color = 'bg-warning'; $icon = 'bi-hourglass-split'; break;
                            case 'Dikirim':
                                $color = 'bg-info'; $icon = 'bi-truck'; break;
                            case 'Selesai':
                                $color = 'bg-success'; $icon = 'bi-check-circle'; break;
                            case 'Dibatalkan':
                                $color = 'bg-danger'; $icon = 'bi-x-circle'; break;
                        }
                    ?>
                    <tr>
                        <td><?= esc($order['order_id']) ?></td>
                        <td><?= esc(date('d M Y H:i', strtotime($order['updated_at']))) ?></td>
                        <td><?= esc($order['penerima_nama']) ?> (<?= esc($order['nomor_pemesan']) ?>)</td>
                        <td>Rp<?= number_format($order['total_harga'], 0, ',', '.') ?></td>
                        <td><?= esc($order['metode_pembayaran']) ?></td>
                        <td>
                                     <span class="badge <?= $color ?> text-white badge-status">
                                <i class="bi <?= $icon ?>"></i> <?= esc($status) ?>
                            </span>
                        </td>
                        <td>
                            <a href="<?= base_url('admin/orders/detail/' . $order['order_id']) ?>" 
                                         class="btn btn-sm btn-secondary btn-icon" 
                               title="Lihat kembali pesanan">
                                <i class="bi bi-arrow-clockwise"></i> Lihat Kembali
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" class="text-center">Tidak ada pesanan yang diselesaikan hari ini.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<script>
// Client-side filtering for Active Orders
(function(){
    const tbl = document.getElementById('activeOrdersTable');
    if(!tbl) return;
    const search = document.getElementById('activeSearch');
    const filter = document.getElementById('statusFilter');
    const resetBtn = document.getElementById('resetFilter');
    const rows = Array.from(tbl.querySelectorAll('tbody tr'));

    function applyFilter(){
        const q = (search?.value || '').trim().toLowerCase();
        const st = (filter?.value || '').trim();
        let visible = 0;
        rows.forEach(tr => {
            const text = (tr.getAttribute('data-text') || '').toLowerCase();
            const s = (tr.getAttribute('data-status') || '').trim();
            const matchText = !q || text.includes(q);
            const matchStatus = !st || s === st;
            const show = matchText && matchStatus;
            tr.style.display = show ? '' : 'none';
            if (show) visible++;
        });
        // Optionally we could show a "no result" row. For now, we rely on table looking empty.
    }
    search?.addEventListener('input', applyFilter);
    filter?.addEventListener('change', applyFilter);
    resetBtn?.addEventListener('click', ()=>{ if(search) search.value=''; if(filter) filter.value=''; applyFilter(); });
})();
</script>
<?= $this->endSection() ?>
