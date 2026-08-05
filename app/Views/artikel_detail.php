<?= $this->extend('templates/main_layout') ?>


<?php
$articleTitle = $artikel['judul'] . ' | Tips Bunga ' . $store['name'];
$articleMetaDesc = substr(strip_tags($artikel['isi']), 0, 150) . '... Baca selengkapnya tentang "' . $artikel['judul'] . '".'; // Ambil 150 karakter pertama dari isi artikel
?>

<?= $this->section('title') ?>
    <?= esc($articleTitle) ?>
<?= $this->endSection() ?>

<?= $this->section('meta_description') ?>
    <?= esc($articleMetaDesc) ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>


    <!-- Single Page Header start -->
    <div class="container-fluid page-header py-5">
        <h1 class="text-center text-white display-6">Detail Artikel</h1>
        <ol class="breadcrumb justify-content-center mb-0">
            <li class="breadcrumb-item"><a href="<?= base_url() ?>">Home</a></li>
            <li class="breadcrumb-item active text-white">Detail Artikel</li>
        </ol>
    </div>
    <!-- Single Page Header End -->

    <!-- Artikel Detail Start -->
    <div class="container-fluid py-5">
        <div class="container py-5">


            <div class="row g-4 justify-content-center">
                <div class="col-lg-12">
                    <div class="mb-4">

                        <h1 class="mb-4"><?= esc($artikel['judul']) ?></h1>
                        <p class="text-muted">Dipublikasikan pada: <?= date('d F Y', strtotime($artikel['tanggal_dibuat'])) ?></p>
                    </div>
                    <div class="mb-4">
                        <img src="<?= base_url('assets/img/artikel/' . esc($artikel['gambar'])) ?>" class="img-fluid rounded w-100" alt="<?= esc($artikel['judul']) ?>">
                    </div>
                    <div class="artikel-content">


                        <?= $artikel['isi'] // Tampilkan isi artikel lengkap ?>


                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Artikel Detail End -->

    <!-- Produk Terkait Start -->
    <div class="container-fluid py-5">
        <div class="container py-5">
            <h1 class="fw-bold mb-4">Produk Terkait</h1>
          
            <div class="owl-carousel artikel-carousel justify-content-center">

                <?php if (!empty($relatedProducts)): ?>
                    <?php foreach ($relatedProducts as $product): ?>
                        <div class="border border-primary rounded position-relative vesitable-item">
                            <div class="vesitable-img" style="height: 250px; overflow: hidden;">
                                <a href="<?= site_url('shop/product/' . $product['product_id']) ?>">
                                    <img src="<?= base_url('assets/img/gambar/' . esc($product['gambar_url'])) ?>" class="img-fluid w-100 rounded-top" alt="<?= esc($product['nama_produk']) ?>" style="height: 100%; object-fit: cover;">
                                </a>
                            </div>
                             <div class="p-4 pb-0 rounded-bottom">
                                <h4><?= esc($product['nama_produk']) ?></h4>
                                <p><?= esc(substr($product['deskripsi_produk'], 0, 50)) . '...' ?></p>
                                <div class="d-flex justify-content-between flex-lg-wrap">
                                    <p class="text-dark fs-5 fw-bold">Rp<?= number_format($product['harga'], 0, ',', '.') ?></p>
                                    <a href="#" class="btn border border-secondary rounded-pill px-3 text-primary add_to_cart_btn" data-product-id="<?= htmlspecialchars($product['product_id']) ?>"><i class="fa fa-shopping-bag me-2 text-primary"></i> Add to cart</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12">
                        <p class="text-center">Tidak ada produk terkait untuk artikel ini.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <!-- Produk Terkait End -->


   

<?= $this->endSection() ?>

<?= $this->section('extra_js') ?>
<script>
$(document).ready(function() {
    // Mengambil jumlah produk terkait dari variabel PHP
    var relatedProductCount = <?= count($relatedProducts) ?>;

    // Menargetkan carousel produk terkait di halaman ini
    var relatedProductsCarousel = $('.artikel-carousel');

    // Hanya jalankan jika carousel dan produknya ada di halaman ini
    if (relatedProductsCarousel.length > 0 && relatedProductCount > 0) {
        
        // Asumsi carousel menampilkan hingga 4 item di layar besar.
        // Jika jumlah produk 4 atau kurang, kita nonaktifkan 'loop' agar tidak ada duplikasi.
        if (relatedProductCount <= 4) {
            // Hancurkan instance carousel yang ada (yang mungkin diinisialisasi oleh main.js)
            relatedProductsCarousel.trigger('destroy.owl.carousel');

            // Inisialisasi ulang carousel dengan `loop: false`
            relatedProductsCarousel.owlCarousel({
                autoplay: true,
                smartSpeed: 1000,
                center: false,
                dots: true,
                loop: false, // <-- Perubahan utamanya di sini
                margin: 25,
                nav : true,
                navText : [
                    '<i class="bi bi-arrow-left"></i>',
                    '<i class="bi bi-arrow-right"></i>'
                ],
                responsive: { 0: { items:1 }, 576: { items:1 }, 768: { items:2 }, 992: { items:3 }, 1200: { items:4 } }
            });
        }
        // Jika jumlah produk lebih dari 4, kita biarkan pengaturan default dari main.js (dengan loop: true) berjalan.
    }
});
</script>
<?= $this->endSection() ?>
