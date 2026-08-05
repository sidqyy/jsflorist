<?= $this->extend('admin/layout/main') ?>

<?= $this->section('title') ?>
<?= $title ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <!-- Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-list-alt me-2"></i>
                <?= $title ?>
            </h1>
            <p class="mb-0 text-muted">Total: <?= number_format($pagination['total']) ?> pengunjung</p>
        </div>
        <div>
            <a href="<?= base_url('admin/traffic') ?>" class="btn btn-primary">
                <i class="fas fa-chart-line me-2"></i>Dashboard
            </a>
            <a href="<?= base_url('admin/traffic/pages') ?>" class="btn btn-success">
                <i class="fas fa-file-alt me-2"></i>Analisis Halaman
            </a>
            <a href="<?= base_url('admin/traffic/debug') ?>" class="btn btn-info">
                <i class="fas fa-bug me-2"></i>Debug
            </a>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-filter me-2"></i>Filter Pengunjung
            </h6>
        </div>
        <div class="card-body">
            <form method="GET" action="<?= base_url('admin/traffic/logs') ?>" id="filterForm">
                <div class="row">
                    <!-- Filter Tanggal -->
                    <div class="col-md-3">
                        <label for="date_from" class="form-label">Dari Tanggal:</label>
                        <input type="date" 
                               class="form-control" 
                               id="date_from" 
                               name="date_from" 
                               value="<?= $filters['date_from'] ?? '' ?>">
                    </div>
                    
                    <div class="col-md-3">
                        <label for="date_to" class="form-label">Sampai Tanggal:</label>
                        <input type="date" 
                               class="form-control" 
                               id="date_to" 
                               name="date_to" 
                               value="<?= $filters['date_to'] ?? '' ?>">
                    </div>

                    <!-- Filter Negara -->
                    <div class="col-md-3">
                        <label for="country" class="form-label">Negara:</label>
                        <select class="form-select" id="country" name="country">
                            <option value="">-- Semua Negara --</option>
                            <?php foreach ($countries as $countryData): ?>
                                <option value="<?= esc($countryData['country']) ?>" 
                                        <?= (isset($filters['country']) && $filters['country'] === $countryData['country']) ? 'selected' : '' ?>>
                                    <?= esc($countryData['country']) ?> (<?= $countryData['count'] ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Filter Kota -->
                    <div class="col-md-3">
                        <label for="city" class="form-label">Kota:</label>
                        <select class="form-select" id="city" name="city">
                            <option value="">-- Semua Kota --</option>
                            <?php foreach ($cities as $cityData): ?>
                                <option value="<?= esc($cityData['city']) ?>" 
                                        <?= (isset($filters['city']) && $filters['city'] === $cityData['city']) ? 'selected' : '' ?>>
                                    <?= esc($cityData['city']) ?> (<?= $cityData['count'] ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="row mt-3">
                    <!-- Filter IP -->
                    <div class="col-md-6">
                        <label for="ip" class="form-label">IP Address:</label>
                        <input type="text" 
                               class="form-control" 
                               id="ip" 
                               name="ip" 
                               placeholder="Cari berdasarkan IP..." 
                               value="<?= $filters['ip'] ?? '' ?>">
                    </div>

                    <!-- Button Filter -->
                    <div class="col-md-6 d-flex align-items-end">
                        <button type="submit" class="btn btn-success me-2">
                            <i class="fas fa-search"></i> Filter
                        </button>
                        <a href="<?= base_url('admin/traffic/logs') ?>" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Data Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-users me-2"></i>
                Data Pengunjung 
                <span class="badge bg-info ms-2"><?= number_format($pagination['total']) ?></span>
            </h6>
        </div>
        <div class="card-body">
            <?php if (!empty($visitors)): ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover" id="visitorsTable">
                        <thead class="table-light">
                            <tr>
                                <th width="5%">#</th>
                                <th width="15%">IP Address</th>
                                <th width="15%">Negara</th>
                                <th width="15%">Kota</th>
                                <th width="12%">Tanggal</th>
                                <th width="10%">Waktu</th>
                                <th width="15%">Timestamp</th>
                                <th width="13%">Analisis</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $startNum = ($pagination['current_page'] - 1) * $pagination['per_page'] + 1;
                            foreach ($visitors as $index => $visitor): 
                                $isLikelyBot = $visitor['is_likely_bot'] ?? false;
                                $rowClass = $isLikelyBot ? 'table-warning' : '';
                            ?>
                                <tr class="<?= $rowClass ?>">
                                    <td><?= $startNum + $index ?></td>
                                    <td>
                                        <code><?= esc($visitor['ip_address']) ?></code>
                                        <br>
                                        <small>
                                            <a href="?ip=<?= urlencode($visitor['ip_address']) ?>" 
                                               class="text-decoration-none" 
                                               title="Filter berdasarkan IP ini">
                                                <i class="fas fa-search"></i> Filter
                                            </a>
                                        </small>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary"><?= esc($visitor['country']) ?></span>
                                        <?php if ($visitor['country'] !== 'Local' && $visitor['country'] !== 'Unknown'): ?>
                                            <br>
                                            <small>
                                                <a href="?country=<?= urlencode($visitor['country']) ?>" 
                                                   class="text-decoration-none">
                                                    <i class="fas fa-filter"></i> Filter Negara
                                                </a>
                                            </small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary"><?= esc($visitor['city']) ?></span>
                                        <?php if ($visitor['city'] !== 'Local' && $visitor['city'] !== 'Unknown'): ?>
                                            <br>
                                            <small>
                                                <a href="?country=<?= urlencode($visitor['country']) ?>&city=<?= urlencode($visitor['city']) ?>" 
                                                   class="text-decoration-none">
                                                    <i class="fas fa-filter"></i> Filter Kota
                                                </a>
                                            </small>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= date('d/m/Y', strtotime($visitor['access_date'])) ?></td>
                                    <td><?= date('H:i:s', strtotime($visitor['access_time'])) ?></td>
                                    <td>
                                        <small><?= date('d/m/Y H:i', strtotime($visitor['created_at'])) ?></small>
                                    </td>
                                    <td>
                                        <?php if ($isLikelyBot): ?>
                                            <span class="badge bg-warning">
                                                <i class="fas fa-robot"></i> Likely Bot
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-success">
                                                <i class="fas fa-user"></i> Human
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if ($pagination['total_pages'] > 1): ?>
                    <nav aria-label="Pagination">
                        <ul class="pagination justify-content-center">
                            <!-- Previous Button -->
                            <?php if ($pagination['has_prev']): ?>
                                <li class="page-item">
                                    <a class="page-link" href="<?= $pagination_urls['prev'] ?>">
                                        <i class="fas fa-chevron-left"></i> Previous
                                    </a>
                                </li>
                            <?php else: ?>
                                <li class="page-item disabled">
                                    <span class="page-link"><i class="fas fa-chevron-left"></i> Previous</span>
                                </li>
                            <?php endif; ?>

                            <!-- Page Numbers -->
                            <?php
                            $startPage = max(1, $pagination['current_page'] - 2);
                            $endPage = min($pagination['total_pages'], $pagination['current_page'] + 2);
                            ?>

                            <?php if ($startPage > 1 && isset($pagination_urls['first'])): ?>
                                <li class="page-item">
                                    <a class="page-link" href="<?= $pagination_urls['first'] ?>">1</a>
                                </li>
                                <?php if ($startPage > 2): ?>
                                    <li class="page-item disabled"><span class="page-link">...</span></li>
                                <?php endif; ?>
                            <?php endif; ?>

                            <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                                <li class="page-item <?= ($i == $pagination['current_page']) ? 'active' : '' ?>">
                                    <a class="page-link" href="<?= $pagination_urls['pages'][$i] ?>"><?= $i ?></a>
                                </li>
                            <?php endfor; ?>

                            <?php if ($endPage < $pagination['total_pages'] && isset($pagination_urls['last'])): ?>
                                <?php if ($endPage < $pagination['total_pages'] - 1): ?>
                                    <li class="page-item disabled"><span class="page-link">...</span></li>
                                <?php endif; ?>
                                <li class="page-item">
                                    <a class="page-link" href="<?= $pagination_urls['last'] ?>"><?= $pagination['total_pages'] ?></a>
                                </li>
                            <?php endif; ?>

                            <!-- Next Button -->
                            <?php if ($pagination['has_next']): ?>
                                <li class="page-item">
                                    <a class="page-link" href="<?= $pagination_urls['next'] ?>">
                                        Next <i class="fas fa-chevron-right"></i>
                                    </a>
                                </li>
                            <?php else: ?>
                                <li class="page-item disabled">
                                    <span class="page-link">Next <i class="fas fa-chevron-right"></i></span>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>

                    <!-- Pagination Info -->
                    <div class="text-center mt-3">
                        <small class="text-muted">
                            Menampilkan <?= $startNum ?> - <?= min($startNum + count($visitors) - 1, $pagination['total']) ?> 
                            dari <?= number_format($pagination['total']) ?> pengunjung
                        </small>
                    </div>
                <?php endif; ?>

            <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-search fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">Tidak ada data pengunjung</h5>
                    <p class="text-muted">Coba ubah filter pencarian Anda.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
// Dynamic city loading based on country selection
document.getElementById('country').addEventListener('change', function() {
    const country = this.value;
    const citySelect = document.getElementById('city');
    
    // Reset city options
    citySelect.innerHTML = '<option value="">-- Semua Kota --</option>';
    
    if (country) {
        // Fetch cities for selected country
        fetch(`<?= base_url('admin/traffic/get-cities') ?>?country=${encodeURIComponent(country)}`)
            .then(response => response.json())
            .then(cities => {
                cities.forEach(city => {
                    const option = document.createElement('option');
                    option.value = city.city;
                    option.textContent = `${city.city} (${city.count})`;
                    citySelect.appendChild(option);
                });
            })
            .catch(error => {
                console.error('Error loading cities:', error);
            });
    }
});

// Auto-submit form when quick filter links are clicked
document.addEventListener('DOMContentLoaded', function() {
    // Set default date range to last 7 days if no filter is applied
    <?php if (empty($filters)): ?>
    const dateFrom = document.getElementById('date_from');
    const dateTo = document.getElementById('date_to');
    
    if (!dateFrom.value) {
        dateFrom.value = '<?= date('Y-m-d', strtotime('-7 days')) ?>';
    }
    if (!dateTo.value) {
        dateTo.value = '<?= date('Y-m-d') ?>';
    }
    <?php endif; ?>
});
</script>



<style>
.table th {
    background-color: #f8f9fc;
    border-top: none;
    font-weight: 600;
    font-size: 0.85rem;
}

.table td {
    font-size: 0.85rem;
    vertical-align: middle;
}

.table-warning {
    background-color: rgba(255, 243, 205, 0.3);
}

.badge {
    font-size: 0.75em;
}

.pagination .page-link {
    padding: 0.375rem 0.75rem;
}

.card {
    box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
}

.text-gray-800 {
    color: #5a5c69 !important;
}
</style>

<?= $this->endSection() ?>
