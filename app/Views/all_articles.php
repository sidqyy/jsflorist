<?= $this->extend('templates/main_layout') ?>

<?= $this->section('title') ?>
Semua Artikel - <?= esc($store['name']) ?>
<?= $this->endSection() ?>

<?= $this->section('meta_description') ?>
Jelajahi semua artikel, tips, dan cerita menarik seputar bunga dan florist dari <?= esc($store['name']) ?>.
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<style>
    .artikel-item img {
        width: 100%;
        height: 200px; /* Tinggi gambar artikel seragam */
        object-fit: cover;
    }
    .artikel-item {
        display: flex;
        flex-direction: column;
        height: 100%;
    }
    .artikel-item .p-4 {
        flex-grow: 1;
        display: flex;
        flex-direction: column;
    }
    .artikel-item h4 {
        flex-grow: 1; /* Agar judul artikel bisa mengambil sisa ruang */
    }
</style>

    <div class="container-fluid page-header py-5" style="background: linear-gradient(rgba(0, 0, 0, 0.3), rgba(0, 0, 0, 0.3)), url(<?= base_url('assets/img/page-header.webp') ?>) center center no-repeat; background-size: cover;">
        <h1 class="text-center text-white display-6">Semua Artikel</h1>
        <ol class="breadcrumb justify-content-center mb-0">
            <li class="breadcrumb-item"><a href="<?= site_url('dashboard') ?>">Home</a></li>
            <li class="breadcrumb-item active text-white">Artikel</li>
        </ol>
    </div>
    <div class="container-fluid py-5">
        <div class="container py-5">
            <h1 class="mb-4">Daftar Semua Artikel</h1>
            <div class="row g-4 justify-content-center">
                <?php if (!empty($artikels)): ?>
                    <?php foreach ($artikels as $artikel): ?>
                        <div class="col-md-6 col-lg-4 col-xl-3">
                            <div class="rounded position-relative artikel-item h-100 d-flex flex-column">
                                <div class="artikel-img">
                                    <a href="<?= site_url('artikel/' . $artikel['slug']) ?>">
                                        <img src="<?= base_url('assets/img/artikel/' . esc($artikel['gambar'])) ?>"
                                             class="img-fluid w-100 rounded-top" alt="<?= esc($artikel['judul']) ?>">
                                    </a>
                                </div>
                                <div class="p-4 border border-secondary border-top-0 rounded-bottom d-flex flex-column flex-grow-1">
                                    <a href="<?= site_url('artikel/' . $artikel['slug']) ?>" class="text-dark">
                                        <h4 class="flex-grow-1"><?= esc($artikel['judul']) ?></h4>
                                    </a>
                                    <p class="text-muted small mb-2">Dipublikasikan: <?= date('d M Y', strtotime($artikel['tanggal_dibuat'])) ?></p>
                                    <p><?= esc(substr(strip_tags($artikel['isi']), 0, 100)) . (strlen(strip_tags($artikel['isi'])) > 100 ? '...' : '') ?></p>
                                    <div class="d-flex justify-content-end mt-auto">
                                        <a href="<?= site_url('artikel/' . $artikel['slug']) ?>" class="btn border border-primary rounded-pill px-3 text-primary">Baca Selengkapnya <i class="fa fa-arrow-right ms-2"></i></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12">
                        <div class="alert alert-warning text-center" role="alert">
                            Tidak ada artikel yang tersedia.
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
   
        
    <a href="#" class="btn btn-primary border-3 border-primary rounded-circle back-to-top"><i class="fa fa-arrow-up"></i></a>

<?= $this->endSection() ?>
