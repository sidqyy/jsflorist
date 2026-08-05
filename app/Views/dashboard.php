<?php
// 🌟 [BARU] Daftar kata-kata mutiara dan jokes untuk ditampilkan secara acak
$quotes_and_jokes = [
    "Bunga adalah puisi yang ditulis oleh bumi.",
    "Di mana bunga mekar, di situ ada harapan.",
    "Hidup itu seperti karangan bunga, setiap momen adalah bunga yang berbeda.",
    "Kenapa zombie kalau nyerang rombongan? Karena kalau sendirian namanya zomblo.",
    "Apa bedanya jam 12 siang sama kamu? Kalau jam 12 itu 'kesiangan', kalau kamu itu 'kesayangan'.",
    "Jadilah seperti bunga yang memberikan keharuman bahkan kepada tangan yang telah menghancurkannya.",
    "Cintaku ke kamu tuh kayak utang, awalnya kecil, didiemin, tau-tau gede sendiri.",
    "Jika mawar bisa tumbuh di surga, Tuhan akan memetik seikat untukmu."
];

// Memilih satu item secara acak dari daftar
$random_text = $quotes_and_jokes[array_rand($quotes_and_jokes)];
?>
<?= $this->extend('templates/main_layout') ?>

<?= $this->section('title') ?>
<?= esc($store['name']) ?>: Rangkaian Bunga Segar & Buket Eksklusif
<?= $this->endSection() ?>
<?= $this->section('meta_description') ?>
Florist Banjarbaru penyedia bunga segar, buket wisuda, dan karangan bunga dengan desain modern yang elegan. Layanan terpercaya untuk pengiriman cepat dengan kualitas bunga pilihan yang terjaga kesegarannya.
<?= $this->endSection() ?>

<?= $this->section('meta_keywords') ?>
Florist Banjarbaru, Toko Bunga Banjarbaru, Karangan Bunga Banjarbaru, Buket Wisuda Banjarbaru, Bunga Segar Banjarbaru, Buket Bunga Eksklusif, Toko Bunga Kalimantan Selatan, Florist Banjarbaru Terpercaya, Pengiriman Bunga Banjarbaru, Toko Bunga Dekat ULM Banjarbaru, Karangan Bunga Murjani.
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- Event Modal Start -->
<?php if (!empty($eventBanners)): ?>
    <div class="modal fade" id="eventModal" tabindex="-1" aria-labelledby="eventModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="eventModalLabel">
                        <?php if (count($eventBanners) > 1): ?>
                            Event Spesial (<?= count($eventBanners) ?> Event)
                        <?php else: ?>
                            <?= esc($eventBanners[0]['title']) ?>
                        <?php endif; ?>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-2">
                    <?php if (count($eventBanners) > 1): ?>
                        <!-- Carousel untuk multiple event banners -->
                        <div id="eventCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="4000">
                            <!-- Indicators -->
                            <div class="carousel-indicators">
                                <?php foreach ($eventBanners as $index => $banner): ?>
                                    <button type="button"
                                        data-bs-target="#eventCarousel"
                                        data-bs-slide-to="<?= $index ?>"
                                        class="<?= $index === 0 ? 'active' : '' ?>"
                                        aria-current="<?= $index === 0 ? 'true' : 'false' ?>"
                                        aria-label="Slide <?= $index + 1 ?>">
                                    </button>
                                <?php endforeach; ?>
                            </div>

                            <!-- Carousel Items -->
                            <div class="carousel-inner">
                                <?php foreach ($eventBanners as $index => $banner): ?>
                                    <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
                                        <div class="text-center position-relative">
                                            <div class="event-banner-container" style="position: relative;">
                                                <img src="<?= base_url('uploads/event_banners/' . esc($banner['image_url'])) ?>"
                                                    class="d-block w-100 event-banner-image"
                                                    alt="<?= esc($banner['title']) ?>"
                                                    data-link="<?= !empty($banner['link_url']) ? esc($banner['link_url']) : '' ?>"
                                                    style="<?= !empty($banner['link_url']) ? 'cursor: pointer;' : '' ?> max-height: 400px; object-fit: contain; width: 100%; height: auto; display: block;">

                                                <?php if (!empty($banner['link_url'])): ?>
                                                    <div class="position-absolute bottom-0 end-0 m-2" style="z-index: 10;">
                                                        <small class="badge bg-primary">
                                                            <i class="fa fa-external-link-alt"></i> Klik untuk membuka
                                                        </small>
                                                    </div>
                                                <?php endif; ?>
                                            </div>

                                            <!-- Title di bawah gambar -->
                                            <div class="mt-2">
                                                <small class="text-muted fw-bold"><?= esc($banner['title']) ?></small>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <!-- Carousel Controls -->
                            <button class="carousel-control-prev" type="button" data-bs-target="#eventCarousel" data-bs-slide="prev">
                                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                <span class="visually-hidden">Previous</span>
                            </button>
                            <button class="carousel-control-next" type="button" data-bs-target="#eventCarousel" data-bs-slide="next">
                                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                <span class="visually-hidden">Next</span>
                            </button>
                        </div>
                    <?php else: ?>
                        <!-- Single event banner -->
                        <div class="text-center">
                            <div class="event-banner-container" style="position: relative;">
                                <img src="<?= base_url('uploads/event_banners/' . esc($eventBanners[0]['image_url'])) ?>"
                                    class="img-fluid event-banner-image"
                                    alt="<?= esc($eventBanners[0]['title']) ?>"
                                    data-link="<?= !empty($eventBanners[0]['link_url']) ? esc($eventBanners[0]['link_url']) : '' ?>"
                                    style="<?= !empty($eventBanners[0]['link_url']) ? 'cursor: pointer;' : '' ?> max-height: 400px; object-fit: contain; width: 100%; height: auto; display: block;">

                                <?php if (!empty($eventBanners[0]['link_url'])): ?>
                                    <div class="position-absolute bottom-0 end-0 m-2">
                                        <small class="badge bg-primary">
                                            <i class="fa fa-external-link-alt"></i> Klik untuk membuka
                                        </small>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="modal-footer">
                    <?php if (count($eventBanners) > 1): ?>
                        <small class="text-muted me-auto">
                            <i class="fa fa-info-circle"></i>
                            <?= count($eventBanners) ?> event aktif •
                            <i class="fa fa-mouse-pointer"></i> Klik gambar untuk membuka link
                        </small>
                    <?php endif; ?>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fa fa-times"></i> Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>
<!-- Event Modal End -->

<!-- floating quote -->
<div class="floating-quote">
    <p><i class="fa fa-quote-left me-2"></i><?= esc($random_text) ?><i class="fa fa-quote-right ms-2"></i></p>
</div>
<!-- floating quote end -->
<div class="modal fade" id="searchModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content rounded-0">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Search by keyword</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body d-flex align-items-center">
                <div class="input-group w-75 mx-auto d-flex">
                    <input type="search" class="form-control p-3" placeholder="keywords" aria-describedby="search-icon-1">
                    <span id="search-icon-1" class="input-group-text p-3"><i class="fa fa-search"></i></span>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="container-fluid py-5 mb-5 hero-header">
    <div class="container py-5">
        <div class="row g-5 align-items-center">
            <div class="col-md-12 col-lg-7">
                <h4 class="mb-3 text-secondary">Hadiah Sempurna untuk Setiap Momen</h4>
                <h1 class="mb-5 display-3 text-primary">Bunga Segar untuk Buket, Ucapan & Acara Anda</h1>
            </div>
            <div class="col-md-12 col-lg-5">
                <div id="carouselId" class="carousel slide position-relative" data-bs-ride="carousel" data-bs-interval="3000">
                    <div class="carousel-indicators">
                        <button type="button" data-bs-target="#carouselId" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
                        <button type="button" data-bs-target="#carouselId" data-bs-slide-to="1" aria-label="Slide 2"></button>
                        <button type="button" data-bs-target="#carouselId" data-bs-slide-to="2" aria-label="Slide 3"></button>
                        <button type="button" data-bs-target="#carouselId" data-bs-slide-to="3" aria-label="Slide 4"></button>
                        <button type="button" data-bs-target="#carouselId" data-bs-slide-to="4" aria-label="Slide 5"></button>
                        <button type="button" data-bs-target="#carouselId" data-bs-slide-to="5" aria-label="Slide 6"></button>
                        <button type="button" data-bs-target="#carouselId" data-bs-slide-to="6" aria-label="Slide 7"></button>
                        <button type="button" data-bs-target="#carouselId" data-bs-slide-to="7" aria-label="Slide 8"></button>
                    </div>
                    <div class="carousel-inner">
                        <div class="carousel-item active rounded">
                            <img src="<?= base_url('assets/img/hand_baket.jpg') ?>" class="img-fluid w-100 h-100 bg-secondary rounded" alt="First slide">
                            <a href="#" class="btn px-4 py-2 text-white rounded" style="position: absolute; top: 20px; left: 50%; transform: translateX(-50%); font-size: 16px; background: rgba(208, 156, 76, 0.8);">Hand-Bouquet</a>
                        </div>
                        <div class="carousel-item rounded">
                            <img src="<?= base_url('assets/img/balon_baket.jpeg') ?>" class="img-fluid w-100 h-100 rounded" alt="Second slide">
                            <a href="#" class="btn px-4 py-2 text-white rounded" style="position: absolute; top: 20px; left: 50%; transform: translateX(-50%); font-size: 16px; background: rgba(208, 156, 76, 0.8);">Ballon-Bouquet</a>
                        </div>
                        <div class="carousel-item rounded">
                            <img src="<?= base_url('assets/img/stending_baket.jpeg') ?>" class="img-fluid w-100 h-100 rounded" alt="Second slide">
                            <a href="#" class="btn px-4 py-2 text-white rounded" style="position: absolute; top: 20px; left: 50%; transform: translateX(-50%); font-size: 16px; background: rgba(208, 156, 76, 0.8);">Standing-Bouquet</a>
                        </div>
                        <div class="carousel-item rounded">
                            <img src="<?= base_url('assets/img/vas_bunga.jpg') ?>" class="img-fluid w-100 h-100 rounded" alt="Second slide">
                            <a href="#" class="btn px-4 py-2 text-white rounded" style="position: absolute; top: 20px; left: 50%; transform: translateX(-50%); font-size: 16px; background: rgba(208, 156, 76, 0.8);">Vase-Flowers</a>
                        </div>
                        <div class="carousel-item rounded">
                            <img src="<?= base_url('assets/img/flower_box.jpeg') ?>" class="img-fluid w-100 h-100 rounded" alt="Second slide">
                            <a href="#" class="btn px-4 py-2 text-white rounded" style="position: absolute; top: 20px; left: 50%; transform: translateX(-50%); font-size: 16px; background: rgba(208, 156, 76, 0.8);">Flower-Box</a>
                        </div>
                        <div class="carousel-item rounded">
                            <img src="<?= base_url('assets/img/bunga_mobil.jpeg') ?>" class="img-fluid w-100 h-100 rounded" alt="Second slide">
                            <a href="#" class="btn px-4 py-2 text-white rounded" style="position: absolute; top: 20px; left: 50%; transform: translateX(-50%); font-size: 16px; background: rgba(208, 156, 76, 0.8);">Bunga-Mobil</a>
                        </div>
                        <div class="carousel-item rounded">
                            <img src="<?= base_url('assets/img/bunga_salib.jpg') ?>" class="img-fluid w-100 h-100 rounded" alt="Second slide">
                            <a href="#" class="btn px-4 py-2 text-white rounded" style="position: absolute; top: 20px; left: 50%; transform: translateX(-50%); font-size: 16px; background: rgba(208, 156, 76, 0.8);">Bunga-Salib</a>
                        </div>
                        <div class="carousel-item rounded">
                            <img src="<?= base_url('assets/img/bunga_papan.jpg') ?>" class="img-fluid w-100 h-100 rounded" alt="Second slide">
                            <a href="#" class="btn px-4 py-2 text-white rounded" style="position: absolute; top: 20px; left: 50%; transform: translateX(-50%); font-size: 16px; background: rgba(208, 156, 76, 0.8);">Bunga-Papan</a>
                        </div>
                    </div>
                    <button class="carousel-control-prev" type="button" data-bs-target="#carouselId" data-bs-slide="prev">
                        <span class="carousel-control-prev-icon bg-primary rounded-circle" aria-hidden="true"></span>
                        <span class="visually-hidden">Previous</span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#carouselId" data-bs-slide="next">
                        <span class="carousel-control-next-icon bg-primary rounded-circle" aria-hidden="true"></span>
                        <span class="visually-hidden">Next</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid fruite py-5">
    <div class="container py-5">
        <div class="tab-class text-center">
            <div class="row g-4">
                <div class="col-lg-4 text-start">
                    <h1>Produk Berdasarkan Momen</h1>
                </div>
                <div class="col-lg-8 text-end">
                    <ul class="nav nav-pills d-inline-flex text-center mb-5">
                        <?php $firstOccasion = true; ?>
                        <?php if (!empty($occasions)): ?>
                            <?php foreach ($occasions as $occasion): ?>
                                <?php if (isset($productsByOccasion[$occasion['occasion_id']]) && !empty($productsByOccasion[$occasion['occasion_id']])): ?>
                                    <li class="nav-item">
                                        <a class="d-flex m-2 py-2 bg-light rounded-pill occasion-filter-btn <?= $firstOccasion ? 'active' : '' ?>"
                                            data-bs-toggle="pill" href="#tab-occasion-<?= esc($occasion['occasion_id']) ?>">
                                            <span class="text-dark" style="width: 130px;"><?= esc($occasion['occasion_name']) ?></span>
                                        </a>
                                    </li>
                                    <?php $firstOccasion = false; ?>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>

            <div class="tab-content">
                <?php $firstOccasionContent = true; ?>
                <?php if (!empty($occasions)): ?>
                    <?php foreach ($occasions as $occasion): ?>
                        <?php if (isset($productsByOccasion[$occasion['occasion_id']]) && !empty($productsByOccasion[$occasion['occasion_id']])): ?>
                            <div id="tab-occasion-<?= esc($occasion['occasion_id']) ?>"
                                class="tab-pane fade show p-0 <?= $firstOccasionContent ? 'active' : '' ?>">
                                <div class="owl-carousel occasion-carousel">

                                    <?php foreach ($productsByOccasion[$occasion['occasion_id']] as $product): ?>
                                        <div class="d-block text-decoration-none text-dark">
                                            <div class="rounded position-relative fruite-item">
                                                <div class="fruite-img">
                                                    <a href="<?= base_url('shop/product/' . $product['product_id']) ?>">
                                                        <img src="<?= base_url('assets/img/gambar/' . esc($product['gambar_url'])) ?>" class="img-fluid w-100 rounded-top" alt="<?= esc($product['nama_produk']) ?>">
                                                    </a>
                                                </div>
                                                <div class="text-white bg-secondary px-3 py-1 rounded position-absolute" style="top: 10px; left: 10px;">
                                                    <?= esc($product['category_display']) ?>
                                                </div>
                                                <div class="p-4 border border-secondary border-top-0 rounded-bottom">
                                                    <h4><?= esc($product['nama_produk']) ?></h4>
                                                    <p><?= esc(strlen($product['deskripsi_produk']) > 60 ? substr($product['deskripsi_produk'], 0, 60) . '...' : $product['deskripsi_produk']) ?></p>
                                                    <div class="d-flex justify-content-between flex-lg-wrap">
                                                        <p class="text-dark fs-5 fw-bold mb-0">Rp<?= number_format($product['harga'], 0, ',', '.') ?></p>
                                                        <form action="/cart/add" method="post" class="add-to-cart-form">
                                                            <?= csrf_field() ?>
                                                            <input type="hidden" name="product_id" value="<?= esc($product['product_id']) ?>">
                                                            <input type="hidden" name="product_name" value="<?= esc($product['nama_produk']) ?>">
                                                            <input type="hidden" name="product_price" value="<?= esc($product['harga']) ?>">
                                                            <input type="hidden" name="quantity" value="1">
                                                            <button type="submit" class="btn border border-secondary rounded-pill px-3 text-primary"><i class="fa fa-shopping-bag me-2 text-primary"></i> Add to cart</button>
                                                        </form>
                                                    </div>

                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>

                                    <div class="d-flex align-items-center justify-content-center h-100">
                                        <a href="<?= site_url('shop?occasion=' . $occasion['occasion_id']) ?>" class="see-more-card btn border border-secondary rounded-pill text-primary p-5">
                                            <i class="fa fa-arrow-right fa-3x mb-3"></i>
                                            <h5 class="fw-bold">Lihat Selengkapnya</h5>
                                        </a>
                                    </div>

                                </div>
                            </div>
                            <?php $firstOccasionContent = false; ?>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid py-5">
    <div class="container py-5">
        <div class="text-center mx-auto mb-5" style="max-width: 700px;">
            <h1 class="display-4">Produk Terlaris Kami</h1>
            <p>Pilihan Produk paling populer dan favorit dari pelanggan kami.</p>
        </div>
        <div class="row g-4">
            <?php if (!empty($bestsellerBouquetProducts)): ?>
                <?php foreach ($bestsellerBouquetProducts as $product): ?>
                    <div class="col-lg-6 col-xl-4">
                        <div class="p-4 rounded bg-light">
                            <div class="row align-items-center">
                                <div class="col-6">
                                    <img src="<?= base_url('assets/img/gambar/' . esc($product['gambar_url'])) ?>"
                                        class="img-fluid rounded-circle w-100" alt="<?= esc($product['nama_produk']) ?>">
                                </div>
                                <div class="col-6">
                                    <a href="#" class="h5"><?= esc($product['nama_produk']) ?></a>
                                    <div class="d-flex my-3">
                                        <i class="fas fa-star text-primary"></i>
                                        <i class="fas fa-star text-primary"></i>
                                        <i class="fas fa-star text-primary"></i>
                                        <i class="fas fa-star text-primary"></i>
                                        <i class="fas fa-star"></i>
                                    </div>
                                    <h4 class="mb-3">Rp<?= number_format($product['harga'], 0, ',', '.') ?></h4>
                                    <form action="/cart/add" method="post" class="add-to-cart-form">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="product_id" value="<?= esc($product['product_id']) ?>">
                                        <input type="hidden" name="product_name" value="<?= esc($product['nama_produk']) ?>">
                                        <input type="hidden" name="product_price" value="<?= esc($product['harga']) ?>">
                                        <input type="hidden" name="quantity" value="1">
                                        <button type="submit" class="btn border border-secondary rounded-pill px-3 text-primary"><i class="fa fa-shopping-bag me-2 text-primary"></i> Add to cart</button>
                                    </form>
                                </div>

                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12 text-center mt-4">
                    <p>Belum ada produk terlaris dari kategori bouquet.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<div class="container-fluid testimonial py-5">
    <div class="container py-5">
        <div class="testimonial-header text-center">
            <h4 class="text-primary">Review Pelanggan Kami</h4>
            <h1 class="display-5 mb-5 text-dark">Apa Kata Mereka!</h1>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="embedsocial-widget" data-ref="94860c209f643d26bbcc5ed56fa69880">
                    <a href="https://embedsocial.com/google-reviews-widget/" title="Add Google reviews on a website" target="_blank" class="powered-by-es es-slider">
                        <img src="https://embedsocial.com/cdn/icon/embedsocial-logo.webp" alt="EmbedSocial">
                        <span> Google reviews widget </span>
                    </a>
                </div>
                <script>
                    (function(d, s, id) {
                        var js;
                        if (d.getElementById(id)) {
                            return;
                        }
                        js = d.createElement(s);
                        js.id = id;
                        js.src = "https://embedsocial.com/cdn/aht.js";
                        d.getElementsByTagName("head")[0].appendChild(js);
                    }(document, "script", "EmbedSocialWidgetScript"));
                </script>
            </div>
        </div>
    </div>
</div>
<div class="container-fluid py-5">
    <div class="container py-5">
        <div class="text-center mx-auto mb-5" style="max-width: 700px;">
            <h1 class="display-4">Tips & Cerita Florist</h1>
            <p>Baca artikel terbaru kami untuk mendapatkan inspirasi, tips perawatan bunga, dan cerita menarik seputar dunia florist.</p>
        </div>
        <?php if (!empty($artikels)): ?>
            <div class="owl-carousel artikel-carousel">
                <?php foreach ($artikels as $artikel): ?>
                    <div class="col-12">

                        <a href="<?= base_url('artikel/' . esc($artikel['slug'])) ?>" class="d-block h-100 text-decoration-none text-dark">
                            <div class="rounded position-relative fruite-item h-100 d-flex flex-column">
                                <div class="fruite-img">
                                    <img src="<?= base_url('assets/img/artikel/' . esc($artikel['gambar'])) ?>" class="img-fluid w-100 rounded-top" alt="<?= esc($artikel['judul']) ?>" style="height: 200px; object-fit: cover;">
                                </div>
                                <div class="p-4 border border-secondary border-top-0 rounded-bottom d-flex flex-column flex-grow-1">
                                    <h4 class="flex-grow-1"><?= esc($artikel['judul']) ?></h4>
                                    <p class="text-muted small mb-2">Dipublikasikan: <?= date('d M Y', strtotime($artikel['tanggal_dibuat'])) ?></p>
                                    <p>
                                        <?php
                                        $isi_singkat = substr(strip_tags($artikel['isi']), 0, 100);
                                        echo esc($isi_singkat) . (strlen(strip_tags($artikel['isi'])) > 100 ? '...' : '');
                                        ?>
                                    </p>

                                    <div class="d-flex justify-content-end mt-auto">
                                        <span class="btn border border-primary rounded-pill px-3 text-primary">Baca Selengkapnya <i class="fa fa-arrow-right ms-2"></i></span>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="col-12 text-center mt-4">
                <p>Belum ada artikel yang tersedia.</p>
            </div>
        <?php endif; ?>
        <div class="text-center mt-4">
            <a href="/artikel" class="btn border-secondary rounded-pill px-4 py-3 text-primary">Lihat Semua Artikel <i class="fa fa-arrow-right ms-2"></i></a>
        </div>
    </div>
</div>


<div class="container-fluid featurs py-5">
    <div class="container py-5">
        <div class="row g-4">
            <div class="col-md-6 col-lg-3">
                <div class="featurs-item text-center rounded bg-light p-4">
                    <div class="featurs-icon btn-square rounded-circle bg-secondary mb-5 mx-auto">
                        <i class="fas fa-car-side fa-3x text-white"></i>
                    </div>
                    <div class="featurs-content text-center">
                        <h5>Pengiriman Cepat</h5>
                        <p class="mb-0">Bunga Tiba di Hari yang Sama</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="featurs-item text-center rounded bg-light p-4">
                    <div class="featurs-icon btn-square rounded-circle bg-secondary mb-5 mx-auto">
                        <i class="fas fa-user-shield fa-3x text-white"></i>
                    </div>
                    <div class="featurs-content text-center">
                        <h5>Pembayaran Aman</h5>
                        <p class="mb-0">Transaksi Online Terlindungi</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="featurs-item text-center rounded bg-light p-4">
                    <div class="featurs-icon btn-square rounded-circle bg-secondary mb-5 mx-auto">
                        <img src="<?= base_url('assets/img/bunga.svg') ?>" alt="Flower Icon" style="width: 48px; height: 48px; filter: invert(100%);">
                    </div>
                    <div class="featurs-content text-center">
                        <h5>Bunga Segar Terjamin</h5>
                        <p class="mb-0">Garansi Kesegaran & Kualitas Terbaik</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="featurs-item text-center rounded bg-light p-4">
                    <div class="featurs-icon btn-square rounded-circle bg-secondary mb-5 mx-auto">
                        <i class="fa fa-phone-alt fa-3x text-white"></i>
                    </div>
                    <div class="featurs-content text-center">
                        <h5>Layanan Konsultasi</h5>
                        <p class="mb-0">Bantu Pilih Bunga Sempurna</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('extra_js') ?>
<script>
    $(document).ready(function() {
        // Show the event modal on page load only if there's an active event banner
        <?php if (!empty($eventBanners)): ?>
            var eventModal = new bootstrap.Modal(document.getElementById('eventModal'));
            eventModal.show();

            <?php if (count($eventBanners) > 1): ?>
                // Initialize carousel if multiple banners
                var eventCarousel = new bootstrap.Carousel(document.getElementById('eventCarousel'), {
                    interval: 4000,
                    wrap: true
                });
            <?php endif; ?>

            // Handle event banner image clicks
            $('.event-banner-image').click(function() {
                var link = $(this).data('link');
                if (link && link !== '') {
                    window.open(link, '_blank');
                }
            });

        <?php endif; ?>

        $(document).on('submit', '.add-to-cart-form', function(e) {

            e.preventDefault();

            const form = $(this);
            const button = form.find('button[type="submit"]');
            const originalButtonText = button.html();

            button.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>');

            $.ajax({
                url: form.attr('action'),
                method: form.attr('method'),
                data: form.serialize(),
                dataType: 'json',
                success: function(response) {
                    // Use global helper to show modal and update cart count (with Lottie)
                    window.showAddToCartModal(response.message || 'Produk berhasil ditambahkan ke keranjang.', response.cart_total_items);
                },
                error: function(xhr, status, error) {
                    console.error("AJAX Error:", xhr.responseText);
                    window.showAddToCartModal('Terjadi kesalahan saat menambahkan produk ke keranjang.');
                },
                complete: function() {
                    button.prop('disabled', false).html(originalButtonText);
                }
            });
        });
    });
</script>

<?= $this->endSection() ?>