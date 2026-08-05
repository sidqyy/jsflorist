<?= $this->extend('admin/layout/main') ?>

<?= $this->section('title') ?>
<?= $title ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <!-- Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-chart-line me-2"></i>
            <?= $title ?>
        </h1>
        <div>
            <a href="<?= base_url('admin/traffic/pages') ?>" class="btn btn-success">
                <i class="fas fa-file-alt me-2"></i>Analisis Halaman
            </a>
            <a href="<?= base_url('admin/traffic/logs') ?>" class="btn btn-info">
                <i class="fas fa-list-alt me-2"></i>Lihat Semua Log
            </a>
            <a href="<?= base_url('admin/traffic/debug') ?>" class="btn btn-secondary">
                <i class="fas fa-bug me-2"></i>Debug
            </a>
        </div>
    </div>

    <!-- Statistik Cards -->
    <div class="row mb-4">
        <!-- Kunjungan Hari Ini -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Kunjungan Hari Ini
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= number_format($todayVisits) ?>
                            </div>
                            <?php if ($todayChangePercent != 0): ?>
                                <small class="text-<?= $todayChangePercent > 0 ? 'success' : 'danger' ?>">
                                    <i class="fas fa-<?= $todayChangePercent > 0 ? 'arrow-up' : 'arrow-down' ?>"></i>
                                    <?= abs($todayChangePercent) ?>% dari kemarin
                                </small>
                            <?php endif; ?>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Kunjungan Kemarin -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Kunjungan Kemarin
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= number_format($yesterdayVisits) ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar-day fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Kunjungan Bulan Ini -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Kunjungan Bulan Ini
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= number_format($monthlyVisits) ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Rata-rata Per Hari -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Rata-rata Per Hari
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= number_format($monthlyVisits / 30, 1) ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-chart-bar fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row">
        <!-- Grafik Kunjungan Per Jam -->
        <div class="col-xl-8 col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-clock me-2"></i>
                        Kunjungan Per Jam (24 Jam Terakhir)
                    </h6>
                    <button class="btn btn-sm btn-outline-primary" onclick="refreshHourlyChart()">
                        <i class="fas fa-sync-alt"></i> Refresh
                    </button>
                </div>
                <div class="card-body">
                    <div class="chart-area">
                        <canvas id="hourlyTrafficChart" width="100%" height="40"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top Countries -->
        <div class="col-xl-4 col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-globe me-2"></i>
                        Negara Teratas
                    </h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Negara</th>
                                    <th class="text-end">Kunjungan</th>
                                </tr>
                            </thead>
                            <tbody id="countriesTableBody">
                                <?php if (!empty($topCountries)): ?>
                                    <?php foreach ($topCountries as $index => $country): ?>
                                        <tr>
                                            <td>
                                                <span class="badge bg-secondary me-2"><?= $index + 1 ?></span>
                                                <?= esc($country['country']) ?>
                                            </td>
                                            <td class="text-end">
                                                <strong><?= number_format($country['visits']) ?></strong>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="2" class="text-center text-muted">
                                            <i class="fas fa-info-circle me-2"></i>
                                            Belum ada data kunjungan
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

    <!-- Daily Traffic Chart -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-chart-line me-2"></i>
                        Tren Kunjungan Harian (30 Hari Terakhir)
                    </h6>
                    <button class="btn btn-sm btn-outline-primary" onclick="refreshDailyChart()">
                        <i class="fas fa-sync-alt"></i> Refresh
                    </button>
                </div>
                <div class="card-body">
                    <div class="chart-area">
                        <canvas id="dailyTrafficChart" width="100%" height="40"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
// Data untuk charts
const hourlyData = <?= json_encode($hourlyChartData) ?>;
const dailyData = <?= json_encode($dailyChartData) ?>;

// Hourly Traffic Chart
let hourlyChart;
function initHourlyChart() {
    const ctx = document.getElementById('hourlyTrafficChart').getContext('2d');
    hourlyChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: hourlyData.labels,
            datasets: [{
                label: 'Kunjungan',
                data: hourlyData.data,
                backgroundColor: 'rgba(208, 156, 76, 0.2)',
                borderColor: 'rgba(208, 156, 76, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        title: function(context) {
                            return 'Jam: ' + context[0].label;
                        }
                    }
                }
            }
        }
    });
}

// Daily Traffic Chart
let dailyChart;
function initDailyChart() {
    const ctx = document.getElementById('dailyTrafficChart').getContext('2d');
    dailyChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: dailyData.labels,
            datasets: [{
                label: 'Kunjungan Harian',
                data: dailyData.data,
                borderColor: 'rgba(208, 156, 76, 1)',
                backgroundColor: 'rgba(208, 156, 76, 0.1)',
                borderWidth: 2,
                fill: true,
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });
}

// Fungsi untuk refresh data
function refreshHourlyChart() {
    fetch('<?= base_url('admin/traffic/api-data') ?>?type=hourly')
        .then(response => response.json())
        .then(data => {
            hourlyChart.data.labels = data.labels;
            hourlyChart.data.datasets[0].data = data.data;
            hourlyChart.update();
        })
        .catch(error => {
            console.error('Error refreshing hourly chart:', error);
        });
}

function refreshDailyChart() {
    fetch('<?= base_url('admin/traffic/api-data') ?>?type=daily')
        .then(response => response.json())
        .then(data => {
            dailyChart.data.labels = data.labels;
            dailyChart.data.datasets[0].data = data.data;
            dailyChart.update();
        })
        .catch(error => {
            console.error('Error refreshing daily chart:', error);
        });
}

// Auto refresh setiap 5 menit
setInterval(() => {
    refreshHourlyChart();
    refreshDailyChart();
    
    // Refresh countries table
    fetch('<?= base_url('admin/traffic/api-data') ?>?type=countries')
        .then(response => response.json())
        .then(data => {
            const tbody = document.getElementById('countriesTableBody');
            tbody.innerHTML = '';
            
            if (data.length > 0) {
                data.forEach((country, index) => {
                    const row = `
                        <tr>
                            <td>
                                <span class="badge bg-secondary me-2">${index + 1}</span>
                                ${country.country}
                            </td>
                            <td class="text-end">
                                <strong>${parseInt(country.visits).toLocaleString()}</strong>
                            </td>
                        </tr>
                    `;
                    tbody.innerHTML += row;
                });
            } else {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="2" class="text-center text-muted">
                            <i class="fas fa-info-circle me-2"></i>
                            Belum ada data kunjungan
                        </td>
                    </tr>
                `;
            }
        })
        .catch(error => {
            console.error('Error refreshing countries:', error);
        });
}, 300000); // 5 menit

// Inisialisasi charts saat halaman dimuat
document.addEventListener('DOMContentLoaded', function() {
    initHourlyChart();
    initDailyChart();
});
</script>

<style>
.card {
    box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15) !important;
}

.border-left-primary {
    border-left: 0.25rem solid #d09c4c !important;
}

.border-left-success {
    border-left: 0.25rem solid #1cc88a !important;
}

.border-left-info {
    border-left: 0.25rem solid #36b9cc !important;
}

.border-left-warning {
    border-left: 0.25rem solid #f6c23e !important;
}

.text-gray-800 {
    color: #5a5c69 !important;
}

.text-gray-300 {
    color: #dddfeb !important;
}

.chart-area {
    position: relative;
    height: 320px;
}

.table th {
    border-top: none;
    font-weight: 600;
}

.badge {
    font-size: 0.75em;
}
</style>

<?= $this->endSection() ?>
