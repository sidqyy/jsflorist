<?= $this->extend('admin/layout/main') ?>

<?= $this->section('title') ?>
Laporan Pendapatan
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-8 d-flex align-items-center">
                    <h1 class="mb-0"><i class="bi bi-cash-coin me-2 text-success"></i>Laporan Pendapatan</h1>
                    <span class="badge bg-light text-dark ms-3">
                        <?php if (!empty($start_date) && !empty($end_date)) : ?>
                            Periode: <?= esc($start_date) ?> s/d <?= esc($end_date) ?>
                        <?php else : ?>
                            Periode: Semua waktu
                        <?php endif; ?>
                    </span>
                </div>
                <div class="col-sm-4">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Home</a></li>
                        <li class="breadcrumb-item active">Pendapatan</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <form action="<?= base_url('admin/revenue') ?>" method="get" id="filterForm">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-4">
                                <label for="start_date" class="form-label">Tanggal Mulai</label>
                                <input type="date" class="form-control" id="start_date" name="start_date" value="<?= esc($start_date) ?>">
                            </div>
                            <div class="col-md-4">
                                <label for="end_date" class="form-label">Tanggal Akhir</label>
                                <input type="date" class="form-control" id="end_date" name="end_date" value="<?= esc($end_date) ?>">
                            </div>
                            <div class="col-md-4 d-flex gap-2 flex-wrap">
                                <div class="btn-group btn-group-sm me-auto" role="group" aria-label="Presets">
                                    <button type="button" class="btn btn-outline-secondary" data-preset="today">Hari ini</button>
                                    <button type="button" class="btn btn-outline-secondary" data-preset="7d">7 Hari</button>
                                    <button type="button" class="btn btn-outline-secondary" data-preset="30d">30 Hari</button>
                                    <button type="button" class="btn btn-outline-secondary" data-preset="month">Bulan ini</button>
                                </div>
                                <button type="submit" class="btn btn-primary flex-grow-1"><i class="bi bi-funnel me-1"></i> Filter</button>
                                <button type="button" id="resetFilter" class="btn btn-light border"><i class="bi bi-arrow-counterclockwise me-1"></i> Reset</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="card border-start border-3 border-primary shadow h-100 py-2">
                        <div class="card-body">
                            <div class="text-uppercase small text-muted mb-1">Total Pendapatan (Bersih)</div>
                            <div class="h4 mb-0">Rp<?= number_format($total_revenue_bersih, 0, ',', '.') ?></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card border-start border-3 border-success shadow h-100 py-2">
                        <div class="card-body">
                            <div class="text-uppercase small text-muted mb-1">Total Pesanan Selesai</div>
                            <div class="h4 mb-0"><?= esc($total_orders_selesai) ?></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card border-start border-3 border-info shadow h-100 py-2">
                        <div class="card-body">
                            <div class="text-uppercase small text-muted mb-1">Rata-rata Nilai Pesanan</div>
                            <div class="h4 mb-0">Rp<?= number_format($average_order_value, 0, ',', '.') ?></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <h6 class="m-0 fw-bold text-primary"><i class="bi bi-check2-circle me-1"></i>Rincian Pendapatan Diterima (Selesai)</h6>
                    <div class="d-flex align-items-center gap-2">
                        <div class="input-group input-group-sm" style="width: 240px;">
                            <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                            <input type="text" id="searchCompleted" class="form-control" placeholder="Cari nama/ID..." aria-label="Cari">
                        </div>
                        <button type="button" id="exportCompleted" class="btn btn-outline-success btn-sm"><i class="bi bi-download me-1"></i> Export CSV</button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="completedTable" class="table table-bordered align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>ID Pesanan</th>
                                    <th>Nama Pemesan</th>
                                    <th>Tanggal</th>
                                    <th class="text-end">Total Harga</th>
                                    <th style="width:100px">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $page_sum_completed = 0; ?>
                                <?php if (!empty($completed_orders)): ?>
                                    <?php foreach ($completed_orders as $order): ?>
                                        <?php $page_sum_completed += (int) $order['total_harga']; ?>
                                        <tr>
                                            <td data-col="id"><?= esc($order['order_id']) ?></td>
                                            <td data-col="name"><?= esc($order['penerima_nama']) ?></td>
                                            <td><?= esc(date('d M Y H:i', strtotime($order['tanggal_pesan']))) ?></td>
                                            <td class="text-end" data-col="amount">Rp<?= number_format($order['total_harga'], 0, ',', '.') ?></td>
                                            <td>
                                                <a href="<?= base_url('admin/orders/detail/' . $order['order_id']) ?>" class="btn btn-sm btn-info"><i class="bi bi-eye"></i> Detail</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="5" class="text-center text-muted">Tidak ada pesanan yang selesai pada periode ini.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php if (!empty($completed_orders)): ?>
                        <div class="d-flex justify-content-between align-items-center mt-2 small text-muted">
                            <span>Total (halaman ini)</span>
                            <strong>Rp<?= number_format($page_sum_completed, 0, ',', '.') ?></strong>
                        </div>
                    <?php endif; ?>
                    <div class="mt-3">
                        <?php if (isset($pager_completed)) : ?>
                            <?= $pager_completed->links('completed', 'bootstrap_pager') ?>
                        <?php endif ?>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm mt-4">
                <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <h6 class="m-0 fw-bold text-danger"><i class="bi bi-arrow-return-left me-1"></i>Rincian Pesanan Dikembalikan</h6>
                    <div class="d-flex align-items-center gap-2">
                        <div class="input-group input-group-sm" style="width: 240px;">
                            <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                            <input type="text" id="searchReturned" class="form-control" placeholder="Cari nama/ID..." aria-label="Cari">
                        </div>
                        <button type="button" id="exportReturned" class="btn btn-outline-danger btn-sm"><i class="bi bi-download me-1"></i> Export CSV</button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="returnedTable" class="table table-bordered align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>ID Pesanan</th>
                                    <th>Nama Pemesan</th>
                                    <th>Tanggal</th>
                                    <th class="text-end">Harga Awal</th>
                                    <th class="text-end">Pengurangan (50%)</th>
                                    <th style="width:100px">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($returned_orders)): ?>
                                    <?php foreach ($returned_orders as $order): ?>
                                        <tr>
                                            <td data-col="id"><?= esc($order['order_id']) ?></td>
                                            <td data-col="name"><?= esc($order['penerima_nama']) ?></td>
                                            <td><?= esc(date('d M Y H:i', strtotime($order['tanggal_pesan']))) ?></td>
                                            <td class="text-end" data-col="amount">Rp<?= number_format($order['total_harga'], 0, ',', '.') ?></td>
                                            <td class="text-end text-danger">- Rp<?= number_format($order['total_harga'] * 0.5, 0, ',', '.') ?></td>
                                            <td>
                                                <a href="<?= base_url('admin/orders/detail/' . $order['order_id']) ?>" class="btn btn-sm btn-info"><i class="bi bi-eye"></i> Detail</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="6" class="text-center text-muted">Tidak ada pesanan yang dikembalikan pada periode ini.</td></tr>
                                <?php endif; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="4" class="text-end">Total Pengurangan dari Pengembalian:</th>
                                    <th class="text-danger text-end" colspan="2">- Rp<?= number_format($total_deduction, 0, ',', '.') ?></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    <div class="mt-3">
                        <?php if (isset($pager_returned)) : ?>
                            <?= $pager_returned->links('returned', 'bootstrap_pager') ?>
                        <?php endif ?>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<?= $this->endSection() ?>

<?= $this->section('extra_js') ?>
<script>
(function(){
    // Date presets
    const start = document.getElementById('start_date');
    const end = document.getElementById('end_date');
    const form = document.getElementById('filterForm');
    function fmt(d){ return d.toISOString().slice(0,10); }
    function setPreset(type){
        const now = new Date();
        let s, e;
        if (type === 'today') {
            s = new Date(now.getFullYear(), now.getMonth(), now.getDate());
            e = new Date(now.getFullYear(), now.getMonth(), now.getDate());
        } else if (type === '7d') {
            e = new Date(now.getFullYear(), now.getMonth(), now.getDate());
            s = new Date(e); s.setDate(s.getDate() - 6);
        } else if (type === '30d') {
            e = new Date(now.getFullYear(), now.getMonth(), now.getDate());
            s = new Date(e); s.setDate(s.getDate() - 29);
        } else if (type === 'month') {
            s = new Date(now.getFullYear(), now.getMonth(), 1);
            e = new Date(now.getFullYear(), now.getMonth() + 1, 0);
        }
        if (start && end) { start.value = fmt(s); end.value = fmt(e); }
    }
    document.querySelectorAll('[data-preset]').forEach(btn => {
        btn.addEventListener('click', function(){ setPreset(this.getAttribute('data-preset')); });
    });
    const resetBtn = document.getElementById('resetFilter');
    if (resetBtn) {
        resetBtn.addEventListener('click', function(){ if(start) start.value=''; if(end) end.value=''; form && form.submit(); });
    }

    // Client-side table filter
    function attachFilter(inputId, tableId){
        const input = document.getElementById(inputId);
        const table = document.getElementById(tableId);
        if (!input || !table) return;
        input.addEventListener('input', function(){
            const term = this.value.toLowerCase();
            table.querySelectorAll('tbody tr').forEach(tr => {
                const name = (tr.querySelector('[data-col="name"]')?.textContent || '').toLowerCase();
                const id = (tr.querySelector('[data-col="id"]')?.textContent || '').toLowerCase();
                const amount = (tr.querySelector('[data-col="amount"]')?.textContent || '').toLowerCase();
                const show = name.includes(term) || id.includes(term) || amount.includes(term);
                tr.style.display = show ? '' : 'none';
            });
        });
    }
    attachFilter('searchCompleted', 'completedTable');
    attachFilter('searchReturned', 'returnedTable');

    // CSV Export (visible rows only)
    function exportTable(tableId, filename){
        const table = document.getElementById(tableId);
        if (!table) return;
        const rows = Array.from(table.querySelectorAll('thead tr, tbody tr, tfoot tr'))
            .filter(tr => tr.querySelectorAll('th,td').length > 0 && (tr.closest('tbody') ? tr.style.display !== 'none' : true));
        const data = rows.map(tr => {
            return Array.from(tr.children).slice(0,5).map(cell => '"' + (cell.innerText || '').replace(/"/g,'""') + '"').join(',');
        }).join('\n');
        const blob = new Blob([data], {type: 'text/csv;charset=utf-8;'});
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url; a.download = filename; a.click();
        setTimeout(() => URL.revokeObjectURL(url), 1000);
    }
    const exportCompleted = document.getElementById('exportCompleted');
    if (exportCompleted) exportCompleted.addEventListener('click', () => exportTable('completedTable', 'pendapatan_selesai.csv'));
    const exportReturned = document.getElementById('exportReturned');
    if (exportReturned) exportReturned.addEventListener('click', () => exportTable('returnedTable', 'pesanan_dikembalikan.csv'));
})();
</script>
<?= $this->endSection() ?>

