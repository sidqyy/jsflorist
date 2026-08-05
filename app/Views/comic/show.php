<?= $this->extend('templates/main_layout') ?>

<?= $this->section('title') ?>
<?= esc($episode['title']) ?> - Komik
<?= $this->endSection() ?>

<?= $this->section('meta_description') ?>
<?= esc(mb_strimwidth(strip_tags($episode['description'] ?? ''), 0, 150, '...')) ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<style>
    .comic-panel {
        background: #fff;
        overflow: hidden;
    }
    .comic-panel img {
        width: 100%;
        display: block;
        object-fit: contain;
        background: #f8f9fa;
    }
    .panel-caption { font-size: .95rem; color: #6c757d; }
</style>

<div class="container-fluid page-header py-5" style="background: linear-gradient(rgba(0, 0, 0, 0.3), rgba(0, 0, 0, 0.3)), url(<?= base_url('assets/img/page-header.webp') ?>) center center no-repeat; background-size: cover;">
    <h1 class="text-center text-white display-6"><?= esc($episode['title']) ?></h1>
    <ol class="breadcrumb justify-content-center mb-0">
        <li class="breadcrumb-item"><a href="<?= site_url('dashboard') ?>">Home</a></li>
        <li class="breadcrumb-item"><a href="<?= site_url('komik') ?>">Komik</a></li>
        <li class="breadcrumb-item active text-white">Episode <?= esc($episode['episode_number']) ?></li>
    </ol>
</div>

<div class="container-fluid py-5">
    <div class="container">
        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between mb-4">
            <div>
                <span class="badge bg-secondary mb-2">Episode <?= esc($episode['episode_number']) ?></span>
                <h2 class="mb-2"><?= esc($episode['title']) ?></h2>
                <?php if (!empty($episode['description'])): ?>
                    <p class="text-muted mb-0"><?= esc($episode['description']) ?></p>
                <?php endif; ?>
            </div>
            <a href="<?= site_url('komik') ?>" class="btn btn-outline-secondary mt-3 mt-md-0">
                <i class="fa fa-arrow-left me-2"></i> Kembali ke Daftar Episode
            </a>
        </div>

        <?php if (!empty($panels)): ?>
            <div class="row g-4">
                <?php foreach ($panels as $panel): ?>
                    <div class="col-12">
                        <div class="comic-panel">
                            <img src="<?= base_url('uploads/comics/panels/' . esc($panel['image_path'])) ?>" alt="Panel <?= esc($panel['panel_number']) ?>">
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="alert alert-warning text-center">Belum ada panel untuk episode ini.</div>
        <?php endif; ?>
    </div>
</div>
<?= $this->endSection() ?>
