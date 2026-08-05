<?php
// File: app/Views/templates/main_layout.php
?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
     <title><?= $this->renderSection('title') ?></title>
  <meta name="description" content="<?= $this->renderSection('meta_description') ?>">
  <meta name="keywords" content="<?= $this->renderSection('meta_keywords') ?>">
<!-- Google Tag Manager -->
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
})(window,document,'script','dataLayer','GTM-T7FQ7F5H');</script>
<!-- End Google Tag Manager -->
<!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-2XPBMTZ4HW"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'G-2XPBMTZ4HW');
</script>
 <link rel="canonical" href="<?= current_url(true) ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600&family=Raleway:wght@600;800&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">
 <link rel="icon" type="<?= $currentFaviconType ?>" href="<?= base_url($store['favicon_url']) ?>"/>

    <link href="<?= base_url('assets/lib/lightbox/css/lightbox.min.css') ?>" rel="stylesheet">
    <link href="<?= base_url('assets/lib/owlcarousel/assets/owl.carousel.min.css') ?>" rel="stylesheet">

    <link href="<?= base_url('assets/css/bootstrap.min.css') ?>" rel="stylesheet">

    <link href="<?= base_url('assets/css/style.css') ?>" rel="stylesheet">

    <style>
        .see-more-card {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
    height: 100%;
    transition: all 0.3s ease;
}
.see-more-card:hover {
    background-color: #f8f9fa;
    transform: scale(1.05);
}
.owl-carousel .owl-stage {
    display: flex;
    align-items: stretch; /* Membuat semua item memiliki tinggi yang sama */
}
.owl-carousel .owl-item > div {
    height: 100%;
}
.fruite-item {
    height: 100%;
    display: flex;
    flex-direction: column;
}
.fruite-item .p-4 {
    flex-grow: 1; /* Membuat konten teks mengisi ruang yang tersedia */
}
        /* [ ... SEMUA CSS YANG SUDAH ADA SEBELUMNYA TETAP DI SINI ... ] */
        .service-item img { width: 100%; height: 250px; object-fit: cover; object-position: center; }
        .fruite-item .fruite-img { width: 100%; height: 250px; overflow: hidden; }
        .fruite-item .fruite-img img { width: 100%; height: 100%; object-fit: cover; object-position: center; }
        .hero-header .carousel-item { height: 450px; overflow: hidden; }
        .hero-header .carousel-item img { width: 100%; height: 100%; object-fit: cover; object-position: center; }
        .vesitable-item .vesitable-img { width: 100%; height: 200px; overflow: hidden; }
        .vesitable-item .vesitable-img img { width: 100%; height: 100%; object-fit: cover; object-position: center; }
        .fa-shopping-bag.fa-2x { color: #d09c4c!important; }
        .position-absolute.bg-secondary { background-color: #ebd4b6 !important; }
        .position-absolute.bg-secondary.text-dark { color: #ffffff !important; }
        .fas.fa-user.fa-2x { color:  #d09c4c  !important; }
        .carousel-control-prev, .carousel-control-next { background-color: #d09c4c !important; width: 40px; height: 40px; border-radius: 50%; top: 50%; transform: translateY(-50%); }
        .carousel-control-prev-icon, .carousel-control-next-icon { filter: invert(100%) sepia(100%) saturate(0%) hue-rotate(288deg) brightness(102%) contrast(102%) !important; }
        .carousel-control-prev-icon { background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='%23ffffff'%3e%3cpath d='M11.354 1.646a.5.5 0 0 1 0 .708L5.707 8l5.647 5.646a.5.5 0 0 1-.708.708l-6-6a.5.5 0 0 1 0-.708l6-6a.5.5 0 0 1 .708 0z'/%3e%3c/svg%3e") !important; }
        .carousel-control-next-icon { background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='%23ffffff'%3e%3cpath d='M4.646 1.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1 0 .708l-6 6a.5.5 0 0 1-.708-.708L10.293 8 4.646 2.354a.5.5 0 0 1 0-.708z'/%3e%3c/svg%3e") !important; }
        .carousel-control-prev:hover .carousel-control-prev-icon, .carousel-control-next:hover .carousel-control-next-icon { filter: invert(0%) sepia(0%) saturate(0%) hue-rotate(0deg) brightness(0%) contrast(0%) !important; }
        .btn.btn-primary.border-2.border-secondary { background-color: #d09c4c !important; border-color: #d09c4c !important; color: #ffffff !important; }
        .btn.btn-primary.border-2.border-secondary:hover { background-color: #b0853e !important; border-color: #b0853e !important; }
    /* Specific cart button styling */
    .btn-cart { background-color: #d09c4c !important; border-color: #d09c4c !important; color: #ffffff !important; }
    .btn-cart:hover, .btn-cart:focus { background-color: #b0853e !important; border-color: #b0853e !important; color: #ffffff !important; }
        .featurs-item .featurs-icon.bg-secondary { background-color: #d09c4c !important; border: 1px solid #d09c4c !important; }
        .featurs-item .featurs-icon i.text-white { color: #ffffff !important; }
        .featurs-item .featurs-icon::after { border-top-color: #b0853e !important; }
        .pagination { display: flex; flex-wrap: wrap; justify-content: center; padding-left: 0; list-style: none; }
        .pagination .page-item .page-link { position: relative; display: block; padding: 0.375rem 0.75rem; text-decoration: none; line-height: 1.5; color: var(--bs-dark); background-color: var(--bs-light); border: 1px solid var(--bs-secondary); transition: color .15s ease-in-out, background-color .15s ease-in-out, border-color .15s ease-in-out, box-shadow .15s ease-in-out; }
        .pagination .page-item .page-link:hover { color: var(--bs-light); background-color: var(--bs-primary); border-color: var(--bs-primary); }
        .pagination .page-item.active .page-link { z-index: 3; color: var(--bs-light); background-color: var(--bs-primary); border-color: var(--bs-primary); }
        .pagination .page-item:not(:first-child) .page-link { margin-left: -1px; }
        .pagination .page-item:first-child .page-link { border-top-left-radius: 0.25rem; border-bottom-left-radius: 0.25rem; }
        .pagination .page-item:last-child .page-link { border-top-right-radius: 0.25rem; border-bottom-right-radius: 0.25rem; }
        .fruite-img img { height: 300px; object-fit: cover; }
        .fruite-item { display: flex; flex-direction: column; height: 100%; }
        .fruite-item .p-4 { flex-grow: 1; display: flex; flex-direction: column; }
        .fruite-item h4 { flex-grow: 1; }
        .summary-box { background-color: #f8f9fa; border: 1px solid #dee2e6; border-radius: 10px; }
        .summary-box h4 { border-bottom: 1px solid #dee2e6; padding-bottom: 0.5rem; margin-bottom: 1rem; }
        .input-group.quantity .btn-sm { width: 30px; height: 30px; display: flex; align-items: center; justify-content: center; }
        .input-group.quantity .form-control-sm { height: 30px; }
        .table button.btn.btn-md.rounded-circle.bg-light.border { background-color: #f8f9fa !important; border-color: #e2e3e5 !important; }
        .table button.btn.btn-md.rounded-circle.bg-light.border i.fa-times { color: #dc3545 !important; }
        .product-thumbnail { width: 70px; height: 70px; object-fit: cover; border-radius: 5px; }
        .bg-primary { background-color: #d09c4c !important; }
        .text-primary { color: #d09c4c !important; }
        .border-primary { border-color: #d09c4c !important; }
        .bg-secondary { background-color: #ebd4b6 !important; }
        .text-secondary { color: #ebd4b6 !important; }
        .border-secondary { border-color: #ebd4b6 !important; }
        .btn-primary.border-2.border-secondary:hover, .btn.btn-primary:hover { background-color: #b0853e !important; border-color: #b0853e !important; }
        

          .floating-quote {
            position: fixed;
            left: 50%;
            background-color: rgba(40, 40, 40, 0.92);
            color: #fff;
            padding: 12px 25px;
            border-radius: 30px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.2);
            /* Z-INDEX DITINGKATKAN MENJADI 1031 AGAR DI ATAS NAVBAR (1030) */
            z-index: 1031; 
            font-family: 'Raleway', sans-serif;
            font-style: italic;
            max-width: 90%;
            text-align: center;
            border: 1px solid rgba(255, 255, 255, 0.2);
            opacity: 0;
            animation: fadeInDown 1s ease-in-out forwards;
        }

        @keyframes fadeInDown {
            from { opacity: 0; transform: translate(-50%, -25px); }
            to { opacity: 1; transform: translate(-50%, 0); }
        }

        @keyframes fadeOutUp {
            from { opacity: 1; transform: translate(-50%, 0); }
            to { opacity: 0; transform: translate(-50%, -25px); }
        }

        .floating-quote.fade-out {
            animation: fadeOutUp 1.2s ease-in-out forwards;
        }
        .floating-quote p { margin: 0; font-size: 0.95rem; }
        .floating-quote i { color: #ebd4b6; font-size: 0.8rem; vertical-align: middle; }
        
        @media (max-width: 768px) {
            .floating-quote { padding: 10px 20px; max-width: 92%; }
            .floating-quote p { font-size: 0.85rem; }
        }
        /** 🌟 [BARU] CSS untuk Floating Action Button (FAB) 🌟 **/
          .fab-container {
            position: fixed;
            bottom: 25px;
            right: 25px;
            z-index: 1050;
            display: flex;
            flex-direction: column-reverse; /* Tombol utama (FAB) selalu di bawah */
            align-items: center;
            gap: 15px; /* Jarak antara tombol FAB dan tombol Back-to-Top */
        }
        .fab-wrapper {
            position: relative;
        }

        .fab-main {
            width: 60px; height: 60px; border-radius: 50%; background-color: #d09c4c; color: white; display: flex; align-items: center; justify-content: center; font-size: 28px; cursor: pointer; box-shadow: 0 4px 12px rgba(0,0,0,0.25); transition: transform 0.3s ease;
        }

        .fab-main i { transition: transform 0.3s ease; }

        .fab-options {
            list-style: none; padding: 0; margin: 0;
            position: absolute; /* Opsi muncul di atas tombol utama */
            bottom: 75px; /* (Tinggi tombol utama 60px + gap 15px) */
            display: flex; flex-direction: column; align-items: center; gap: 15px;
            transform: scale(0); transform-origin: bottom; opacity: 0;
            transition: transform 0.2s ease, opacity 0.2s ease;
        }

        .fab-wrapper.active .fab-options { transform: scale(1); opacity: 1; }
        .fab-wrapper.active .fab-main i { transform: rotate(45deg); }

        .fab-options a { width: 55px; height: 55px; border-radius: 50%; color: #fff; display: flex; align-items: center; justify-content: center; font-size: 24px; text-decoration: none; box-shadow: 0 4px 8px rgba(0,0,0,0.2); transition: transform 0.2s ease; }
        .fab-options a:hover { transform: scale(1.1); }
        .fab-options a.whatsapp { background-color: #25D366; }
        .fab-options a.instagram { background: radial-gradient(circle at 30% 107%, #fdf497 0%, #fdf497 5%, #fd5949 45%, #d6249f 60%, #285AEB 90%); }
        
        /* Tombol back-to-top tidak lagi memerlukan style aneh */
        .back-to-top { width: 50px; height: 50px; }


    </style>


    
    <?php if ($this->renderSection('extra_css_leaflet')): ?>
        <?= $this->renderSection('extra_css_leaflet') ?>
    <?php endif; ?>
   
</head>

<body>
<!-- Google Tag Manager (noscript) -->
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-T7FQ7F5H"
height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<!-- End Google Tag Manager (noscript) -->
    <div id="main-navbar-container" class="container-fluid fixed-top">
        <?= $this->include('templates/navbar') ?>
    </div>
<div id="spinner" class="show w-100 vh-100 bg-white position-fixed translate-middle top-50 start-50  d-flex align-items-center justify-content-center">
            <div class="spinner-grow text-primary" role="status"></div>
        </div>
    <div id="navbar-spacer"></div>
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
    <div class="modal fade" id="addToCartModal" tabindex="-1" aria-labelledby="addToCartModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addToCartModalLabel">Berhasil Ditambahkan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="d-flex flex-column align-items-center text-center">
                        <div id="addToCartAnimation" style="width: 180px; height: 180px;"></div>
                        <p class="mt-2 mb-0" id="addToCartModalBody">Produk berhasil ditambahkan ke keranjang.</p>
                    </div>
                </div>
                <div class="modal-footer d-flex justify-content-between">
                    <a href="<?= site_url('cart') ?>" class="btn btn-primary btn-cart"><i class="fa fa-shopping-bag me-2"></i>Lihat Keranjang</a>
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Lanjut Belanja</button>
                </div>

            </div>
        </div>
    </div>
    <?= $this->renderSection('content') ?>

   

    <?= $this->include('templates/footer') ?>

    <div class="fab-container">
        <div class="fab-wrapper">
            <ul class="fab-options">
                <li>
                    <a href="https://wa.me/<?= esc(preg_replace('/[^0-9]/', '', $store['phone']), 'attr') ?>" class="whatsapp" target="_blank" title="Hubungi via WhatsApp">
                        <i class="fab fa-whatsapp"></i>
                    </a>
                </li>
                <li>
                     <a href="<?= esc($store['instagram'], 'attr') ?>" class="instagram" target="_blank" title="Kunjungi Instagram kami">
                        <i class="fab fa-instagram"></i>
                    </a>
                </li>
            </ul>
            <div class="fab-main">
                <i class="fa fa-plus"></i>
            </div>
        </div>

        <!-- <a href="#" class="btn btn-primary border-3 border-primary rounded-circle back-to-top"><i class="fa fa-arrow-up"></i></a> -->
    </div>


    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= base_url('assets/lib/easing/easing.min.js') ?>"></script>
    <script src="<?= base_url('assets/lib/waypoints/waypoints.min.js') ?>"></script>
    <script src="<?= base_url('assets/lib/lightbox/js/lightbox.min.js') ?>"></script>
    <script src="<?= base_url('assets/lib/owlcarousel/owl.carousel.min.js') ?>"></script>

    <script src="<?= base_url('assets/js/main.js') ?>"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lottie-web/5.12.2/lottie.min.js"></script>

    <script>
    $(document).ready(function() {


            // Re-initialize Owl Carousel for "Tips & Cerita Florist" on dashboard
            // Note: If you have multiple Owl Carousels, give them unique IDs
            // The existing dashboard.php code initializes testimonial-carousel.
            // If main.js already initializes it, you might need to destroy then re-init
            // or ensure main.js only runs once for this selector.
            // Or, for specific carousels, move their init to their respective section.
            // For now, let's assume main.js handles default Owl carousels.

            // Initialize the testimonial carousel (used on the dashboard and other pages)
            // $(".testimonial-carousel").owlCarousel({
            //     autoplay: true,
            //     smartSpeed: 1000,
            //     center: false,
            //     dots: true,
            //     loop: true,
            //     margin: 25,
            //     nav : true,
            //     navText : [
            //         '<i class="bi bi-arrow-left"></i>',
            //         '<i class="bi bi-arrow-right"></i>'
            //     ],
            //     responsive: {
            //         0:{
            //             items:1
            //         },
            //         768:{
            //             items:2
            //         },
            //         992:{
            //             items:2
            //         }
            //     }
            // });

                $(".artikel-carousel").owlCarousel({
            autoplay: true,
            smartSpeed: 1500, // sedikit lebih lambat untuk kesan elegan
            center: false,
            dots: true,
            loop: false,
            margin: 25,
            nav : true,
            navText : [
                '<i class="bi bi-arrow-left"></i>',
                '<i class="bi bi-arrow-right"></i>'
            ],
            responsive: {
                0:{
                    items:1 // 1 artikel di layar mobile
                },
                768:{
                    items:2 // 2 artikel di layar tablet
                },
                992:{
                    items:3 // 3 artikel di layar desktop
                }
            }
        });

            // Quantity buttons for product detail (if not PRDKUANG/PRDKCUST)

            $(document).on('click', '.input-group.quantity .btn-plus', function() {
                var input = $(this).closest('.quantity').find('input[name="quantity"]');
                input.val(parseInt(input.val()) + 1);
            });
            $(document).on('click', '.input-group.quantity .btn-minus', function() {
                var input = $(this).closest('.quantity').find('input[name="quantity"]');
                var value = parseInt(input.val());
                if (value > 1) {
                    input.val(value - 1);
                }
            });
            
            /** 🌟 [BARU] JavaScript untuk Floating Action Button (FAB) 🌟 **/
            $('.fab-main').on('click', function(e) {
                e.stopPropagation(); // Mencegah event bubbling ke document
                $('.fab-wrapper').toggleClass('active');
            });

            // Menutup FAB saat klik di luar area
            $(document).on('click', function(e) {
                if ($('.fab-wrapper').hasClass('active')) {
                    $('.fab-wrapper').removeClass('active');
                }
            });
 $(".occasion-carousel").owlCarousel({
        autoplay: false, // Disarankan false agar pengguna bisa melihat dengan tenang
        smartSpeed: 1000,
        margin: 25,
        loop: false, // Loop harus false agar "Lihat Selengkapnya" tetap di akhir
        center: false,
        dots: true,
        nav: true,
        navText : [
            '<i class="bi bi-arrow-left"></i>',
            '<i class="bi bi-arrow-right"></i>'
        ],
        responsiveClass: true,
        responsive: {
            0:{
                items:1
            },
            768:{
                items:2
            },
            992:{
                items:3
            },
            1200:{
                items:4
            }
        }
    });
     function initOccasionCarousel(selector) {
        $(selector).owlCarousel({
            autoplay: false,
            smartSpeed: 1000,
            margin: 25,
            loop: false,
            center: false,
            dots: true,
            nav: true,
            navText : ['<i class="bi bi-arrow-left"></i>', '<i class="bi bi-arrow-right"></i>'],
            responsive: { 0:{ items:1 }, 768:{ items:2 }, 992:{ items:3 }, 1200:{ items:4 } }
        });
    }

    // Inisialisasi carousel yang aktif pertama kali
    initOccasionCarousel('.tab-pane.active .occasion-carousel');

    // Menangani klik pada tombol filter occasion
    $('.occasion-filter-btn').on('shown.bs.tab', function (e) {
        // Hancurkan carousel lama untuk mencegah duplikasi
        $('.occasion-carousel').trigger('destroy.owl.carousel');
        
        // Inisialisasi carousel baru yang sekarang aktif
        var newCarouselSelector = $(e.target).attr('href') + ' .occasion-carousel';
        initOccasionCarousel(newCarouselSelector);
    });
       function adjustQuotePosition() {
                if ($('.floating-quote').length) {
                    var navbarHeight = $('#main-navbar-container').outerHeight();
                    // Atur posisi 'top' dengan tambahan 15px sebagai margin
                    $('.floating-quote').css('top', navbarHeight + 15 + 'px');
                }
            }

            // Panggil fungsi saat halaman pertama kali dimuat
            adjustQuotePosition();
            
            // Panggil kembali saat ukuran window berubah (penting untuk responsivitas)
            $(window).on('resize', adjustQuotePosition);
            
            // Logika untuk membuat quote menghilang setelah 7 detik
            if ($('.floating-quote').length) {
                setTimeout(function() {
                    $('.floating-quote').addClass('fade-out');
                }, 7000); 

                $('.floating-quote').on('animationend', function(e) {
                    if (e.originalEvent.animationName === "fadeOutUp") {
                        $(this).remove();
                    }
                });
            }


              /** 🌟 [DIPERBARUI] Logika untuk Layout Dinamis 🌟 **/
          function adjustLayout() {
                var navbarHeight = $('#main-navbar-container').outerHeight();
                
                // Mengatur tinggi spacer agar konten tidak tertutup
                $('#navbar-spacer').css('height', navbarHeight + 'px');
                
                // Mengatur posisi floating quote tepat di bawah navbar
                if ($('.floating-quote').length) {
                    $('.floating-quote').css('top', navbarHeight + 15 + 'px');
                }
            }

            // Panggil fungsi saat halaman dimuat dan saat ukuran window berubah
            adjustLayout();
            $(window).on('resize', adjustLayout);
            
        });
    </script>

    <script>
        // Global helpers: update cart count and show modal with message
        window.updateCartCount = function(totalItems) {
            if (typeof totalItems === 'number' && !isNaN(totalItems)) {
                $('.navbar .position-relative .rounded-circle').text(totalItems);
            }
        };
        window.showAddToCartModal = function(message, cartTotalItems) {
            if (message) $('#addToCartModalBody').text(message);
            if (typeof cartTotalItems !== 'undefined') window.updateCartCount(cartTotalItems);
            var modalEl = document.getElementById('addToCartModal');
            var modal = (bootstrap.Modal.getInstance ? bootstrap.Modal.getInstance(modalEl) : null) || new bootstrap.Modal(modalEl);
            modal.show();
        };

        // Lottie animation lifecycle for Add-to-Cart modal
        (function() {
            var lottieInstance = null;
            var modalEl = document.getElementById('addToCartModal');
            if (!modalEl) return;

            modalEl.addEventListener('shown.bs.modal', function () {
                var container = document.getElementById('addToCartAnimation');
                if (!container || !window.lottie) return;
                if (lottieInstance) { try { lottieInstance.destroy(); } catch(e) {} lottieInstance = null; }
                lottieInstance = lottie.loadAnimation({
                    container: container,
                    renderer: 'svg',
                    loop: false,
                    autoplay: true,
                    path: '<?= base_url('assets/keranjang.json') ?>'
                });
            });

            modalEl.addEventListener('hidden.bs.modal', function () {
                if (lottieInstance) { try { lottieInstance.destroy(); } catch(e) {} lottieInstance = null; }
                var container = document.getElementById('addToCartAnimation');
                if (container) container.innerHTML = '';
            });
        })();
    </script>

    <?= $this->renderSection('extra_js') ?>

    
    <?= $this->renderSection('extra_js_leaflet') ?>

</body>
</html>
