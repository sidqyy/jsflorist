<?= $this->extend('admin/layout/main') ?>

<?= $this->section('title') ?>
Dashboard Admin
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<h1 class="mb-4">Dashboard</h1>

<style>
/* Gen Z vibe: subtle gradients, rounded corners, and micro-interactions */
.card-genz { border-radius: 16px; border: 0; }
.hover-raise { transition: transform .2s ease, box-shadow .2s ease; }
.hover-raise:hover { transform: translateY(-4px); box-shadow: 0 12px 30px rgba(0,0,0,.08); }
.icon-bubble { width: 48px; height: 48px; border-radius: 12px; display:flex; align-items:center; justify-content:center; color:#fff; box-shadow: 0 6px 20px rgba(0,0,0,.12); }
.bg-grad-1 { background: linear-gradient(135deg,#6366F1,#06B6D4); }
.bg-grad-2 { background: linear-gradient(135deg,#F59E0B,#EF4444); }
.bg-grad-3 { background: linear-gradient(135deg,#10B981,#3B82F6); }
.list-group-flush .list-group-item.genz { border: 0; border-radius: 12px; margin: 6px 8px; background: rgba(255,255,255,.85); backdrop-filter: blur(6px); transition: background .2s ease, transform .2s ease; }
.list-group-item.genz:hover { background: rgba(255,255,255,.98); transform: translateY(-2px); }
.badge-status { border-radius: 999px; padding: .35rem .6rem; font-weight: 600; }
.small-muted { color: #6B7280; }
.card-header .m-0 { letter-spacing: .2px; }
</style>

<div class="row">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2 card-genz hover-raise">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Pendapatan (Hari Ini)</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">Rp<?= number_format($pendapatan_hari_ini, 0, ',', '.') ?></div>
                    </div>
                    <div class="col-auto">
                        <div class="icon-bubble bg-grad-1">
                            <i class="fas fa-calendar-day"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100 py-2 card-genz hover-raise">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                            Pesanan Baru (Hari Ini)</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?= esc($pesanan_baru_hari_ini) ?></div>
                    </div>
                    <div class="col-auto">
                        <div class="icon-bubble bg-grad-2">
                            <i class="fas fa-clipboard-list"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-info shadow h-100 py-2 card-genz hover-raise">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Total Pelanggan
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?= esc($total_pelanggan) ?></div>
                    </div>
                    <div class="col-auto">
                        <div class="icon-bubble bg-grad-3">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">

    <div class="col-xl-8 col-lg-7">
    <div class="card shadow mb-4 card-genz hover-raise">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Grafik Pendapatan (<?= esc(date('d M Y', strtotime($start_date ?? date('Y-m-d')))) ?> - <?= esc(date('d M Y', strtotime($end_date ?? date('Y-m-d')))) ?>)</h6>
                <form class="d-flex align-items-center gap-2" method="get" action="">
                    <div class="input-group input-group-sm me-2" style="max-width: 320px;">
                        <span class="input-group-text">Dari</span>
                        <input type="date" class="form-control" name="start_date" value="<?= esc($start_date ?? '') ?>">
                        <span class="input-group-text">Sampai</span>
                        <input type="date" class="form-control" name="end_date" value="<?= esc($end_date ?? '') ?>">
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm">Terapkan</button>
                </form>
            </div>
            <div class="card-body">
                <div class="chart-area">
                    <canvas id="myAreaChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4 col-lg-5">
    <div class="card shadow mb-4 card-genz hover-raise">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">5 Pesanan Terakhir</h6>
        </div>
        <div class="card-body p-2"> <div class="list-group list-group-flush">
                <?php if (!empty($pesanan_terakhir)): ?>
                    <?php foreach ($pesanan_terakhir as $order): ?>
                        <?php
                            // Kode dari Anda untuk menentukan warna dan ikon
                            $status = $order['status_pesanan'];
                            $icon   = 'bi-question-circle';
                            $color  = 'bg-dark';

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
                        <a href="<?= base_url('admin/orders/detail/' . $order['order_id']) ?>" class="list-group-item list-group-item-action genz">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1">Pesanan #<?= esc($order['order_id']) ?></h6>
                                <div class="d-flex align-items-center gap-2 small-muted">
                                    <small><?= date('d M Y', strtotime($order['tanggal_pesan'])) ?></small>
                                    <i class="bi bi-chevron-right"></i>
                                </div>
                            </div>
                            <p class="mb-1"><?= esc($order['penerima_nama']) ?></p>
                            
                            <small>
                                <span class="badge <?= $color ?> text-white">
                                    <i class="bi <?= $icon ?> me-1"></i>
                                    <?= esc($status) ?>
                                </span>
                            </small>

                        </a>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-center p-3">Tidak ada pesanan terbaru.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var canvas = document.getElementById('myAreaChart');
    var ctx = canvas.getContext('2d');
    // Gradient fill for a modern look
    var gradient = ctx.createLinearGradient(0, 0, 0, canvas.height || 200);
    gradient.addColorStop(0, 'rgba(78, 115, 223, 0.25)');
    gradient.addColorStop(1, 'rgba(78, 115, 223, 0.00)');

    var myAreaChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?= $chart_labels ?>,
            datasets: [{
                label: "Pendapatan",
                lineTension: 0.35,
                fill: true,
                backgroundColor: gradient,
                borderColor: "rgba(78, 115, 223, 1)",
                borderWidth: 3,
                pointRadius: 0,
                pointHoverRadius: 4,
                data: <?= $chart_totals ?>,
            }],
        },
        options: {
            maintainAspectRatio: false,
            scales: {
                x: {
                    grid: {
                        display: false
                    }
                },
                y: {
                    ticks: {
                        callback: function(value, index, values) {
                            return 'Rp' + new Intl.NumberFormat('id-ID').format(value);
                        }
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) {
                                label += ': ';
                            }
                            if (context.parsed.y !== null) {
                                label += new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(context.parsed.y);
                            }
                            return label;
                        }
                    }
                }
            }
        }
    });
});
</script>
<?= $this->endSection() ?>