<?= $this->extend('admin/layout/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-file-alt me-2"></i>
                        Analisis Halaman Website
                    </h5>
                    <div class="d-flex gap-2">
                        <form method="get" class="d-flex gap-2">
                            <select name="days" class="form-select form-select-sm" onchange="this.form.submit()">
                                <option value="7" <?= $days == 7 ? 'selected' : '' ?>>7 Hari Terakhir</option>
                                <option value="30" <?= $days == 30 ? 'selected' : '' ?>>30 Hari Terakhir</option>
                                <option value="90" <?= $days == 90 ? 'selected' : '' ?>>90 Hari Terakhir</option>
                            </select>
                        </form>
                        <a href="<?= site_url('admin/traffic') ?>" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-chart-line me-1"></i>Dashboard
                        </a>
                        <a href="<?= site_url('admin/traffic/logs') ?>" class="btn btn-outline-info btn-sm">
                            <i class="fas fa-list me-1"></i>Log Detail
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <!-- Halaman Populer -->
        <div class="col-lg-6 col-12 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-star text-warning me-2"></i>
                        Halaman Paling Populer
                    </h6>
                </div>
                <div class="card-body">
                    <?php if (!empty($popular_pages)): ?>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Halaman</th>
                                        <th>Kunjungan</th>
                                        <th>%</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $total_views = array_sum(array_column($popular_pages, 'visits'));
                                    foreach ($popular_pages as $index => $page): 
                                        $percentage = $total_views > 0 ? round(($page['visits'] / $total_views) * 100, 1) : 0;
                                    ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <span class="badge bg-primary me-2"><?= $index + 1 ?></span>
                                                    <div>
                                                        <strong><?= esc($page['page_title']) ?></strong>
                                                        <br>
                                                        <small class="text-muted"><?= esc($page['page_url']) ?></small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-success"><?= number_format($page['visits']) ?></span>
                                            </td>
                                            <td>
                                                <div class="progress" style="height: 8px;">
                                                    <div class="progress-bar bg-warning" 
                                                         style="width: <?= $percentage ?>%"></div>
                                                </div>
                                                <small><?= $percentage ?>%</small>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="fas fa-chart-bar fa-3x text-muted mb-3"></i>
                            <p class="text-muted">Belum ada data halaman untuk periode ini</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Halaman Masuk Utama -->
        <div class="col-lg-6 col-12 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-door-open text-success me-2"></i>
                        Halaman Masuk Utama
                    </h6>
                </div>
                <div class="card-body">
                    <?php if (!empty($entry_pages)): ?>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Halaman</th>
                                        <th>Entry</th>
                                        <th>%</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $total_entries = array_sum(array_column($entry_pages, 'entries'));
                                    foreach ($entry_pages as $index => $page): 
                                        $percentage = $total_entries > 0 ? round(($page['entries'] / $total_entries) * 100, 1) : 0;
                                    ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <span class="badge bg-success me-2"><?= $index + 1 ?></span>
                                                    <div>
                                                        <strong><?= esc($page['page_title']) ?></strong>
                                                        <br>
                                                        <small class="text-muted"><?= esc($page['page_url']) ?></small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-info"><?= number_format($page['entries']) ?></span>
                                            </td>
                                            <td>
                                                <div class="progress" style="height: 8px;">
                                                    <div class="progress-bar bg-success" 
                                                         style="width: <?= $percentage ?>%"></div>
                                                </div>
                                                <small><?= $percentage ?>%</small>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="fas fa-door-open fa-3x text-muted mb-3"></i>
                            <p class="text-muted">Belum ada data entry page untuk periode ini</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <!-- Sumber Traffic -->
        <div class="col-lg-6 col-12 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-external-link-alt text-info me-2"></i>
                        Sumber Traffic
                    </h6>
                </div>
                <div class="card-body">
                    <?php if (!empty($traffic_sources)): ?>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Sumber</th>
                                        <th>Kunjungan</th>
                                        <th>%</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $total_traffic = array_sum(array_column($traffic_sources, 'visits'));
                                    foreach ($traffic_sources as $index => $source): 
                                        $percentage = $total_traffic > 0 ? round(($source['visits'] / $total_traffic) * 100, 1) : 0;
                                        
                                        // Tentukan icon dan warna berdasarkan sumber
                                        $icon = 'fas fa-globe';
                                        $color = 'secondary';
                                        $source_name = $source['referer_domain'] ?: 'Direct/Bookmark';
                                        
                                        if (str_contains($source_name, 'google')) {
                                            $icon = 'fab fa-google';
                                            $color = 'danger';
                                        } elseif (str_contains($source_name, 'facebook')) {
                                            $icon = 'fab fa-facebook';
                                            $color = 'primary';
                                        } elseif (str_contains($source_name, 'instagram')) {
                                            $icon = 'fab fa-instagram';
                                            $color = 'warning';
                                        } elseif ($source_name === 'Direct/Bookmark') {
                                            $icon = 'fas fa-bookmark';
                                            $color = 'success';
                                        }
                                    ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <i class="<?= $icon ?> text-<?= $color ?> me-2"></i>
                                                    <span><?= esc($source_name) ?></span>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?= $color ?>"><?= number_format($source['visits']) ?></span>
                                            </td>
                                            <td>
                                                <div class="progress" style="height: 8px;">
                                                    <div class="progress-bar bg-<?= $color ?>" 
                                                         style="width: <?= $percentage ?>%"></div>
                                                </div>
                                                <small><?= $percentage ?>%</small>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="fas fa-external-link-alt fa-3x text-muted mb-3"></i>
                            <p class="text-muted">Belum ada data sumber traffic untuk periode ini</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Statistik Browser -->
        <div class="col-lg-6 col-12 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-desktop text-primary me-2"></i>
                        Browser Populer
                    </h6>
                </div>
                <div class="card-body">
                    <?php if (!empty($browser_stats)): ?>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Browser</th>
                                        <th>Pengguna</th>
                                        <th>%</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $total_browsers = array_sum(array_column($browser_stats, 'count'));
                                    foreach ($browser_stats as $index => $browser): 
                                        $percentage = $total_browsers > 0 ? round(($browser['count'] / $total_browsers) * 100, 1) : 0;
                                        
                                        // Tentukan icon berdasarkan browser
                                        $icon = 'fas fa-globe';
                                        $color = 'secondary';
                                        
                                        if (str_contains(strtolower($browser['browser']), 'chrome')) {
                                            $icon = 'fab fa-chrome';
                                            $color = 'warning';
                                        } elseif (str_contains(strtolower($browser['browser']), 'firefox')) {
                                            $icon = 'fab fa-firefox';
                                            $color = 'danger';
                                        } elseif (str_contains(strtolower($browser['browser']), 'safari')) {
                                            $icon = 'fab fa-safari';
                                            $color = 'info';
                                        } elseif (str_contains(strtolower($browser['browser']), 'edge')) {
                                            $icon = 'fab fa-edge';
                                            $color = 'primary';
                                        }
                                    ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <i class="<?= $icon ?> text-<?= $color ?> me-2"></i>
                                                    <span><?= esc($browser['browser']) ?></span>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?= $color ?>"><?= number_format($browser['count']) ?></span>
                                            </td>
                                            <td>
                                                <div class="progress" style="height: 8px;">
                                                    <div class="progress-bar bg-<?= $color ?>" 
                                                         style="width: <?= $percentage ?>%"></div>
                                                </div>
                                                <small><?= $percentage ?>%</small>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="fas fa-desktop fa-3x text-muted mb-3"></i>
                            <p class="text-muted">Belum ada data browser untuk periode ini</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.progress {
    background-color: #e9ecef;
}

.table th {
    border-top: none;
    font-weight: 600;
    font-size: 0.875rem;
    color: #6c757d;
}

.card-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
}

.badge {
    font-size: 0.75rem;
}
</style>
<?= $this->endSection() ?>
