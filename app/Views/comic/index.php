<?= $this->extend('templates/main_layout') ?>

<?= $this->section('title') ?>
Komik - <?= esc($store['name']) ?>
<?= $this->endSection() ?>

<?= $this->section('meta_description') ?>
Baca komik original dari <?= esc($store['name']) ?>. Tiap episode berisi panel-panel gambar yang seru dan menarik.
<?= $this->endSection() ?>

<?= $this->section('meta_keywords') ?>
komik, episode komik, komik original, cerita bergambar
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<style>
    .comic-card { height: 100%; display: flex; flex-direction: column; }
    .comic-cover { width: 100%; height: 220px; object-fit: cover; border-top-left-radius: .5rem; border-top-right-radius: .5rem; }
    .comic-cover-placeholder {
        height: 220px;
        background: linear-gradient(135deg, #f7f2ea, #efe2c9);
        display: flex;
        align-items: center;
        justify-content: center;
        color: #6c4d1d;
        font-weight: 600;
        border-top-left-radius: .5rem;
        border-top-right-radius: .5rem;
    }
</style>

<div class="container-fluid page-header py-5" style="background: linear-gradient(rgba(0, 0, 0, 0.3), rgba(0, 0, 0, 0.3)), url(<?= base_url('assets/img/page-header.webp') ?>) center center no-repeat; background-size: cover;">
    <h1 class="text-center text-white display-6">Komik</h1>
    <ol class="breadcrumb justify-content-center mb-0">
        <li class="breadcrumb-item"><a href="<?= site_url('dashboard') ?>">Home</a></li>
        <li class="breadcrumb-item active text-white">Komik</li>
    </ol>
</div>

<div class="container-fluid py-5">
    <div class="container py-4">
        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between mb-4">
            <div>
                <h2 class="mb-1">Daftar Episode</h2>
                <p class="text-muted mb-0">Nikmati cerita bergambar per episode.</p>
            </div>
        </div>

        <div class="row g-4">
            <?php if (!empty($episodes)): ?>
                <?php foreach ($episodes as $episode): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card shadow-sm border-0 comic-card">
                            <?php $cover = $coverMap[$episode['id']] ?? null; ?>
                            <?php if (!empty($cover)): ?>
                                <img src="<?= base_url('uploads/comics/' . (empty($episode['cover_image']) ? 'panels/' : 'episodes/') . $cover) ?>"
                                     alt="<?= esc($episode['title']) ?>"
                                     class="comic-cover">
                            <?php else: ?>
                                <div class="comic-cover-placeholder">
                                    <i class="fas fa-book-open me-2"></i> Belum ada cover
                                </div>
                            <?php endif; ?>

                            <div class="card-body d-flex flex-column">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="badge bg-secondary">Episode <?= esc($episode['episode_number']) ?></span>
                                    <span class="text-muted small">
                                        <?= (int) ($panelCounts[$episode['id']] ?? 0) ?> panel
                                    </span>
                                </div>
                                <h5 class="card-title mb-2"><?= esc($episode['title']) ?></h5>
                                <p class="text-muted flex-grow-1 mb-3">
                                    <?= esc(mb_strimwidth(strip_tags($episode['description'] ?? ''), 0, 120, '...')) ?>
                                </p>
                                <a href="<?= site_url('komik/' . $episode['slug']) ?>" class="btn btn-primary btn-sm align-self-start">
                                    Baca Episode <i class="fa fa-arrow-right ms-2"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="alert alert-warning text-center">Belum ada episode komik.</div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
