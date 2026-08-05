<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $this->renderSection('title') ?> - Admin JS Florist</title>
    <link href="<?= base_url('assets/css/bootstrap.min.css') ?>" rel="stylesheet">
    <link href="<?= base_url('assets/css/style.css') ?>" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css"/>
    <style>

:root {
    /* Brand palette overrides for Bootstrap tokens */
    --bs-primary: #d09c4c;
    --bs-secondary: #ebd4b6;
}

/* Ganti seluruh blok ini di file main.php */
.pagination {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    padding-left: 0;
    list-style: none;
}

/* Style untuk setiap tautan pager agar sesuai tema */
.pagination .page-item .page-link {
    position: relative;
    display: block;
    padding: 0.375rem 0.75rem;
    text-decoration: none;
    line-height: 1.5;
    color: var(--bs-dark); /* Menggunakan warna teks dari tema Anda */
    background-color: var(--bs-light); /* Warna latar belakang dari tema Anda */
    border: 1px solid var(--bs-secondary); /* Menggunakan border dari tema Anda */
    transition: color .15s ease-in-out, background-color .15s ease-in-out, border-color .15s ease-in-out, box-shadow .15s ease-in-out;
}

/* Style untuk efek hover */
.pagination .page-item .page-link:hover {
    color: var(--bs-light); /* Mengubah warna teks saat hover */
    background-color: var(--bs-primary); /* Mengubah warna latar belakang saat hover */
    border-color: var(--bs-primary); /* Mengubah warna border saat hover */
}

/* Style untuk halaman yang sedang aktif */
.pagination .page-item.active .page-link {
    z-index: 3;
    color: var(--bs-light); /* Warna teks putih/cerah dari tema Anda */
    background-color: var(--bs-primary); /* Menggunakan warna primer tema Anda */
    border-color: var(--bs-primary); /* Menggunakan border warna primer tema Anda */
}

/* Penyesuaian border untuk tampilan yang rapi */
.pagination .page-item:not(:first-child) .page-link {
    margin-left: -1px;
}

.pagination .page-item:first-child .page-link {
    border-top-left-radius: 0.25rem;
    border-bottom-left-radius: 0.25rem;
}

.pagination .page-item:last-child .page-link {
    border-top-right-radius: 0.25rem;
    border-bottom-right-radius: 0.25rem;
}
.product-thumbnail {
    width: 80px;      /* Atur lebar gambar */
    height: 80px;     /* Atur tinggi gambar */
    object-fit: cover; /* Membuat gambar tetap proporsional */
    border-radius: 0.25rem; /* Sudut sedikit melengkung (opsional) */
}

        /* General Body & Wrapper Styles */
       body {
    background-color: #f8f9fa;
    padding-top: 100px; /* <-- TAMBAHKAN PADDING DI SINI */
}
#wrapper {
    display: flex;
    /* padding-top: 56px; <-- Padding sudah dipindah ke body */
}
        /* Sidebar Styles */
        #sidebar-wrapper {
            min-width: 250px;
            max-width: 250px;
            background-color: #f8f9fa;
            border-right: 1px solid #dee2e6;
            transition: transform 0.3s ease-in-out;
            position: sticky; /* Sticky on desktop */
            top: 56px; /* Align with bottom of navbar */
            height: calc(100vh - 56px); /* Full height minus navbar */
            overflow-y: auto;
        }
        .sidebar-heading {
            padding: 1rem 1.25rem;
            font-size: 1.1rem;
            font-weight: 700;
            text-align: center;
            border-bottom: 1px solid #eadcc6;
            margin-bottom: 0.5rem;
            color: #6c4d1d;
            background: linear-gradient(180deg, #ffffff 0%, #faf5ec 100%);
        }
        .list-group-item {
            border: none;
            background-color: transparent;
            color: #212529;
            padding: 1rem 1.5rem;
            transition: all 0.2s;
            border-radius: 0.25rem;
            margin: 0 1rem 0.25rem 1rem;
            width: calc(100% - 2rem);
        }
        .list-group-item:hover {
            background-color: rgba(208,156,76,0.12) !important;
            color: #6c4d1d !important;
        }
        .list-group-item.active {
            background-color: rgba(208,156,76,0.18) !important;
            color: #6c4d1d !important;
            border-left: 4px solid #d09c4c;
            box-shadow: inset 0 0 0 1px rgba(208,156,76,0.25);
        }
        .list-group-item i {
            transition: color 0.2s;
        }
        .list-group-item:hover i, .list-group-item.active i {
            color: #d09c4c !important;
        }

        /* Page Content Styles */
      #page-content-wrapper {
    width: 100%;
    padding: 1.5rem;
    min-width: 0; /* <-- TAMBAHKAN BARIS INI */
}

        /* Mobile Responsive Styles (Off-canvas) */
        @media (max-width: 991.98px) {
            #sidebar-wrapper {
                position: fixed; /* Fixed position for overlay */
                top: 0;
                left: 0;
                height: 100vh;
                z-index: 1040; /* Higher than navbar */
                transform: translateX(-100%); /* Hidden by default */
            }
            #wrapper.toggled #sidebar-wrapper {
                transform: translateX(0); /* Shown when toggled */
            }
            .sidebar-overlay {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background-color: rgba(0, 0, 0, 0.5);
                z-index: 1030; /* Below sidebar, above content */
                display: none;
            }
            #wrapper.toggled .sidebar-overlay {
                display: block;
            }
        }

        /* Navbar Styles */
        .navbar { z-index: 1035; }
        .navbar-brand h1 { font-weight: 800; letter-spacing: .2px; }
        .navbar .btn-outline-danger.btn-sm { border-width: 2px; }

        /* Toast tweaks */
        .toast { border-radius: .75rem; overflow: hidden; box-shadow: 0 .75rem 1.5rem rgba(0,0,0,.1); }
        .toast-header { border-bottom: none; }

        /* Pulse for new order badge */
        @keyframes pulseBadge {
            0% { transform: scale(1); }
            50% { transform: scale(1.12); }
            100% { transform: scale(1); }
        }
        #new-order-badge { animation: pulseBadge 1.5s ease-in-out infinite; }
    </style>
    <link href="<?= base_url('assets/summernote/summernote-lite.min.css') ?>" rel="stylesheet">
</head>
<body>
     <div id="spinner" class="show w-100 vh-100 bg-white position-fixed translate-middle top-50 start-50  d-flex align-items-center justify-content-center">
            <div class="spinner-grow text-primary" role="status"></div>
        </div>
    <nav class="navbar navbar-expand-lg navbar-light bg-white fixed-top shadow-sm">
        <div class="container-fluid">
            <button class="btn btn-outline-secondary d-lg-none me-2" type="button" id="sidebarToggle">
                <i class="fas fa-bars"></i>
            </button>

            <a href="<?= base_url('admin/orders') ?>" class="navbar-brand">
                <h1 class="text-primary" style="font-size: 1.5rem; margin: 0;">JS Florist Admin</h1>
            </a>
            
            <div class="collapse navbar-collapse">
                <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-center">
                    <li class="nav-item">
                        <span class="navbar-text me-3 text-dark">Selamat Datang, Admin!</span>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-outline-danger btn-sm" href="<?= base_url('admin/logout') ?>">
                            <i class="fas fa-sign-out-alt me-1"></i>Logout
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div id="wrapper">
        <div class="sidebar-overlay"></div>

        <div id="sidebar-wrapper">
            <div class="sidebar-heading">Navigasi Admin</div>
           <div class="list-group list-group-flush">
                <a href="<?= base_url('admin/dashboard') ?>" class="list-group-item list-group-item-action <?= (service('uri')->getSegment(2) == 'dashboard' || service('uri')->getSegment(2) == '') ? 'active' : '' ?>">
                    <i class="fas fa-fw fa-tachometer-alt me-2"></i> Dashboard
                </a>
                <a href="<?= base_url('admin/orders') ?>" class="list-group-item list-group-item-action <?= service('uri')->getSegment(2) == 'orders' ? 'active' : '' ?>">
                    <i class="fas fa-clipboard-list me-2"></i> 
                    Pemesanan 
                    <span id="new-order-badge" class="badge bg-danger ms-2" style="display: none;">Baru!</span>
                </a>
                <a href="<?= base_url('admin/custom-requests') ?>" class="list-group-item list-group-item-action <?= service('uri')->getSegment(2) == 'custom-requests' ? 'active' : '' ?>">
                    <i class="fas fa-comments me-2"></i> Custom Request
                </a>
                <a href="<?= base_url('admin/articles') ?>" class="list-group-item list-group-item-action <?= service('uri')->getSegment(2) == 'articles' ? 'active' : '' ?>">
                    <i class="fas fa-newspaper me-2"></i> Artikel
                </a>
                <a href="<?= base_url('admin/comics') ?>" class="list-group-item list-group-item-action <?= service('uri')->getSegment(2) == 'comics' ? 'active' : '' ?>">
                    <i class="fas fa-book-open me-2"></i> Komik
                </a>

                <?php if (session()->get('role') === 'management'): ?>
                    <a href="<?= base_url('admin/revenue') ?>" class="list-group-item list-group-item-action  <?= service('uri')->getSegment(2) == 'revenue' ? 'active' : '' ?>">
                        <i class="fas fa-chart-line me-2"></i> Pendapatan
                    </a>
                    <a href="<?= base_url('admin/products') ?>" class="list-group-item list-group-item-action  <?= service('uri')->getSegment(2) == 'products' && service('uri')->getSegment(3) != 'analysis' ? 'active' : '' ?>">
                        <i class="fas fa-boxes me-2"></i> Produk
                    </a>
                    <a href="<?= base_url('admin/products/analysis') ?>" class="list-group-item list-group-item-action  <?= service('uri')->getSegment(3) == 'analysis' ? 'active' : '' ?>">
                        <i class="fas fa-chart-pie me-2"></i> Analisis Produk
                    </a>
                    <a href="<?= base_url('admin/product-occasions') ?>" class="list-group-item list-group-item-action <?= service('uri')->getSegment(2) == 'product-occasions' ? 'active' : '' ?>">
                        <i class="fas fa-calendar-plus me-2"></i> Produk ke Occasion
                    </a>
                    <a href="<?= base_url('admin/discounts') ?>" class="list-group-item list-group-item-action <?= service('uri')->getSegment(2) == 'discounts' ? 'active' : '' ?>">
                        <i class="fas fa-percent me-2"></i> Aturan Diskon
                    </a>
                    <a href="<?= base_url('admin/bonus/rules') ?>" class="list-group-item list-group-item-action <?= service('uri')->getSegment(3) == 'rules' ? 'active' : '' ?>">
                    <i class="fas fa-gift me-2"></i> Pengaturan Bonus Promo
                    </a>
                    <a href="<?= base_url('admin/vouchers') ?>" class="list-group-item list-group-item-action <?= service('uri')->getSegment(2) == 'vouchers' ? 'active' : '' ?>">
                        <i class="fas fa-ticket-alt me-2"></i> Voucher
                    </a>
                    <a href="<?= base_url('admin/free-shipping') ?>" class="list-group-item list-group-item-action <?= service('uri')->getSegment(2) == 'free-shipping' ? 'active' : '' ?>">
                        <i class="fas fa-shipping-fast me-2"></i> Gratis Ongkir
                    </a>
                    <a href="<?= base_url('admin/event-banners') ?>" class="list-group-item list-group-item-action <?= service('uri')->getSegment(2) == 'event-banners' ? 'active' : '' ?>">
                        <i class="fas fa-bullhorn me-2"></i> Event Banner
                    </a>
                    <a href="<?= base_url('admin/traffic') ?>" class="list-group-item list-group-item-action <?= (service('uri')->getSegment(2) == 'traffic' && service('uri')->getSegment(3) != 'logs') ? 'active' : '' ?>">
                        <i class="fas fa-chart-line me-2"></i> Analisis Traffic
                    </a>
                    <a href="<?= base_url('admin/traffic/logs') ?>" class="list-group-item list-group-item-action <?= service('uri')->getSegment(3) == 'logs' ? 'active' : '' ?>">
                        <i class="fas fa-list-alt me-2"></i> Log Pengunjung
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <div id="page-content-wrapper">
            <?= $this->renderSection('content') ?>
        </div>
    </div>

    <!-- [PERBAIKAN] Menambahkan kerangka HTML untuk Notifikasi Toast -->
    <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1100">
        <div id="notificationToast" class="toast hide" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header bg-primary text-white">
                <i class="fas fa-bell me-2"></i>
                <strong class="me-auto">Notifikasi Baru</strong>
                <small>Baru saja</small>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body" id="toast-body">
                <!-- Pesan notifikasi akan muncul di sini -->
            </div>
        </div>
    </div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sidebarToggle = document.getElementById('sidebarToggle');
            const wrapper = document.getElementById('wrapper');
            const overlay = document.querySelector('.sidebar-overlay');

            if (sidebarToggle) {
                sidebarToggle.addEventListener('click', function() {
                    wrapper.classList.toggle('toggled');
                });
            }

            if (overlay) {
                overlay.addEventListener('click', function() {
                    wrapper.classList.remove('toggled');
                });
            }
              var spinnerElement = document.getElementById('spinner');
            if (spinnerElement) {
                spinnerElement.classList.remove('show');
                // Opsional: hapus elemen spinner sepenuhnya dari DOM setelah disembunyikan
                // setTimeout(function() {
                //     spinnerElement.remove();
                // }, 500); // Tunggu sebentar agar transisi selesai
            }

            // --- SISTEM NOTIFIKASI PESANAN BARU ---

            let lastOrderCount = null;
            const notificationSound = new Audio('<?= base_url('/assets/sounds/notification.mp3') ?>');
            const $newOrderBadge = $('#new-order-badge');

            // [PERBAIKAN] Inisialisasi toast dengan cara yang lebih aman untuk menghindari error.
            // Kita ambil elemennya dulu, lalu cek apakah ada sebelum membuat objek Toast.
            const toastElement = document.getElementById('notificationToast');
            let notificationToast = null;

            if (toastElement) {
                notificationToast = new bootstrap.Toast(toastElement);
                console.log('Toast component initialized successfully.');
            } else {
                console.error('Error: Elemen Toast dengan id="notificationToast" tidak ditemukan di dalam DOM.');
            }

            // [PERBAIKAN] Browser modern memblokir autoplay audio sampai ada interaksi dari pengguna.
            // Kode ini akan mencoba 'membuka kunci' audio saat pengguna mengklik halaman untuk pertama kalinya.
            $(document).one('click', function() {
                notificationSound.play().then(() => {
                    notificationSound.pause();
                    notificationSound.currentTime = 0; // Reset audio ke awal
                    console.log('Izin audio berhasil didapatkan oleh browser.');
                }).catch(error => {
                    console.warn('Gagal mendapatkan izin audio pada klik pertama, browser mungkin memiliki aturan yang lebih ketat.');
                });
            });

            function checkNewOrders() {
                $.ajax({
                    url: "<?= site_url('admin/check-new-orders') ?>",
                    method: 'GET',
                    dataType: 'json',
                    cache: false, // [PERBAIKAN 1] Mencegah browser menggunakan data cache untuk request ini.
                    success: function(response) {
                        if (response && typeof response.order_count !== 'undefined') {
                            const newCount = parseInt(response.order_count, 10);

                            // [PERBAIKAN 2] Tambahkan log untuk debugging pada setiap pengecekan.
                            console.log(`Polling... Last count: ${lastOrderCount}, New count from server: ${newCount}`);

                            // Jika ini adalah pengecekan pertama, simpan saja jumlahnya
                            if (lastOrderCount === null) {
                                lastOrderCount = newCount;
                                console.log('Jumlah pesanan awal berhasil diinisialisasi:', lastOrderCount);
                                return;
                            }

                            // Jika jumlah baru lebih besar dari yang terakhir disimpan
                            if (newCount > lastOrderCount) {
                                console.log('%cPesanan baru terdeteksi!', 'color: #28a745; font-weight: bold;', 'Jumlah lama:', lastOrderCount, 'Jumlah baru:', newCount);
                                
                                // 1. Mainkan suara notifikasi
                                const playPromise = notificationSound.play();
                                if (playPromise !== undefined) {
                                    playPromise.catch(error => {
                                        console.error("Gagal memutar suara notifikasi:", error);
                                        // Tampilkan pesan di console jika suara gagal, karena alert sudah pasti muncul.
                                        console.log("Tips: Klik di mana saja pada halaman untuk mengizinkan browser memutar suara.");
                                    });
                                }

                                // 2. Tampilkan notifikasi visual (badge)
                                // [DIUBAH] Ganti alert() dengan toast
                                $('#toast-body').text('Ada Pesanan Baru Masuk!'); // Set pesan di toast
                                if (notificationToast) {
                                    notificationToast.show(); // Tampilkan toast hanya jika berhasil diinisialisasi
                                }
                                $newOrderBadge.show();

                                // 3. Perbarui jumlah terakhir
                                lastOrderCount = newCount;
                            }
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error("Gagal memeriksa pesanan baru:", error);
                    }
                });
            }

            // Lakukan pengecekan pertama kali saat halaman dimuat, lalu setiap 10 detik
            checkNewOrders(); 
            setInterval(checkNewOrders, 10000); // Mengembalikan ke 10 detik sesuai permintaan awal.
        });
    </script>
    <?= $this->renderSection('extra_js') ?>
</body>
</html>
