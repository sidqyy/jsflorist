<?= $this->extend('templates/main_layout') ?>

<?= $this->section('title') ?>
    Katalog Bunga - Beli Buket & Bunga Papan Online | <?= esc($store['name']) ?>
<?= $this->endSection() ?>

<?= $this->section('meta_description') ?>
    Jelajahi beragam pilihan bunga dan buket cantik di katalog <?= esc($store['name']) ?>. Temukan bunga sempurna untuk setiap momen.
<?= $this->endSection() ?>


<?= $this->section('content') ?>

        <style>
            /* Styles for the active pagination item */
/* Ensure the main pagination container uses flexbox and is centered */
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
            /* Tambahkan CSS kustom dari dashboard.php agar konsisten */
            .bg-primary { background-color: #d09c4c !important; }
            .text-primary { color: #d09c4c !important; }
            .border-primary { border-color: #d09c4c !important; }
            .bg-secondary { background-color: #ebd4b6 !important; }
            .text-secondary { color: #ebd4b6 !important; }
            .border-secondary { border-color: #ebd4b6 !important; }
            .fruite-img img {
                height: 300px; /* Tinggi gambar produk seragam */
                object-fit: cover;
            }
            .fruite-item {
                display: flex;
                flex-direction: column;
                height: 100%;
            }
            .fruite-item .p-4 {
                flex-grow: 1;
                display: flex;
                flex-direction: column;
            }
            .fruite-item h4 {
                flex-grow: 1; /* Agar judul produk bisa mengambil sisa ruang */
            }
        </style>

        <div class="container-fluid page-header py-5" style="background: linear-gradient(rgba(0, 0, 0, 0.3), rgba(0, 0, 0, 0.3)), url(<?= base_url('assets/img/page-header.png') ?>) center center no-repeat; background-size: cover;">
            <h1 class="text-center text-white display-6">Shop</h1>
            <ol class="breadcrumb justify-content-center mb-0">
                <li class="breadcrumb-item"><a href="/dashboard">Home</a></li>
                <li class="breadcrumb-item active text-white">Shop</li>
            </ol>
        </div>
        <div class="container-fluid fruite py-5">
            <div class="container py-5">
                <h1 class="mb-4"><?= esc($store['name']) ?> Shop</h1>
                <div class="row g-4">
                    <div class="col-lg-12">

                        <div class="row g-4">
                            <div class="col-lg-12">
                              <form id="product-filter-form" action="/shop" method="get" class="row g-4 align-items-center">
    <?php if (!empty($selectedOccasion)): ?>
        <input type="hidden" name="occasion" value="<?= esc($selectedOccasion) ?>">
    <?php endif; ?>

    <div class="col-lg-5">
        <div class="input-group w-100 mx-auto d-flex">
            <input type="search" name="keyword" class="form-control p-3" placeholder="Cari produk..." aria-describedby="search-icon-1" value="<?= esc($keyword ?? '') ?>">
            <button type="submit" id="search-icon-1" class="input-group-text p-3"><i class="fa fa-search"></i></button>
        </div>
    </div>
    <div class="col-lg-4">
        <select id="category-filter-select" name="category" class="form-select p-3">
            <option value="">Semua Kategori</option>
            <?php if (!empty($categories)): ?>
                <?php foreach($categories as $category): ?>
                    <option value="<?= $category['category_id'] ?>" <?= (isset($selectedCategory) && $selectedCategory == $category['category_id']) ? 'selected' : '' ?>>
                        <?= esc($category['nama_kategori']) ?>
                    </option>
                <?php endforeach; ?>
            <?php endif; ?>
        </select>
    </div>
    <div class="col-lg-3">
        <div class="bg-light py-3 rounded d-flex justify-content-center">
            <a href="/shop" class="btn btn-danger w-100">Reset Semua Filter</a>
        </div>
    </div>
</form>
                            </div>
                        </div>
                         <div class="row g-4 mt-4">
                            <div class="col-lg-3">
                                <div class="row g-4">
                                    <div class="col-lg-12">
                                        
                                        <div class="mb-3">
                                            <h4>Acara (Occasion)</h4>
                                            <ul class="list-unstyled fruite-categorie">
                                                <li>
                                                    <div class="d-flex justify-content-between fruite-name">
                                                        <a href="/shop" class="<?= empty($selectedOccasion) ? 'text-danger fw-bold' : '' ?>">
                                                            <i class="fas fa-stream me-2"></i>Semua Produk
                                                        </a>
                                                    </div>
                                                </li>
                                                <?php if (!empty($occasions)): ?>
                                                    <?php foreach ($occasions as $occasion): ?>
                                                        <li>
                                                            <div class="d-flex justify-content-between fruite-name">
                                                                <a href="/shop?occasion=<?= esc($occasion['occasion_id']) ?>" class="<?= (isset($selectedOccasion) && $selectedOccasion == $occasion['occasion_id']) ? 'text-danger fw-bold' : '' ?>">
                                                                    <i class="fas fa-gift me-2"></i><?= esc($occasion['occasion_name']) ?>
                                                                </a>
                                                            </div>
                                                        </li>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                            </ul>
                                        </div>
                                        <div class="col-lg-12" style="display:none;"> <div class="mb-3">
                                                <h4>Kategori</h4>
                                                <ul class="list-unstyled fruite-categorie">
                                                    </ul>
                                            </div>
                                        </div>
                                        
                                    </div>
                                </div>
                            </div>


                            <div class="col-lg-9">
                                <div class="row g-4 justify-content-center">
                                    <?php if (!empty($products)): ?>
                                        <?php foreach ($products as $product): ?>
                                            <div class="col-md-6 col-lg-6 col-xl-4">
                                                <div class="rounded position-relative fruite-item h-100 d-flex flex-column">
                                                    <div class="fruite-img">
                                                        <a href="/shop/product/<?= esc($product['product_id']) ?>"> <img src="<?= base_url('assets/img/gambar/' . esc($product['gambar_url'], 'attr')) ?>" class="img-fluid w-100 rounded-top" alt="<?= esc($product['nama_produk'], 'attr') ?>">
                                                        </a>
                                                    </div>


                                                    <div class="p-4 border border-secondary border-top-0 rounded-bottom d-flex flex-column flex-grow-1">
                                                        <a href="/shop/product/<?= esc($product['product_id']) ?>" class="text-dark"><h4 class="flex-grow-1"><?= esc($product['nama_produk']) ?></h4></a>
                                                        <p><?= esc(substr($product['deskripsi_produk'], 0, 70)) . (strlen($product['deskripsi_produk']) > 70 ? '...' : '') ?></p>

                                                        <div class="d-flex justify-content-between flex-lg-wrap mt-auto align-items-center">
                                                        <?php 
                                                        // Cek apakah produk ini memiliki diskon
                                                        $hasDiscount = isset($productDiscounts[$product['product_id']]);
                                                        $discountInfo = $hasDiscount ? $productDiscounts[$product['product_id']] : null;
                                                        $originalPrice = $product['harga'];
                                                        $displayPrice = $originalPrice;
                                                        
                                                        $isFutureDiscount = false;
                                                        $calcPct = 0;
                                                        if ($hasDiscount) {
                                                            if (isset($discountInfo['discounted_price']) && $discountInfo['discounted_price'] > 0) {
                                                                $calcPct = round((($originalPrice - $discountInfo['discounted_price']) / $originalPrice) * 100);
                                                            } else {
                                                                $calcPct = round($discountInfo['discount_percentage'] ?? 0);
                                                            }
                                                            
                                                            if (!empty($discountInfo['valid_pickup_start_date']) && date('Y-m-d') < $discountInfo['valid_pickup_start_date']) {
                                                                $isFutureDiscount = true;
                                                            }
                                                        }

                                                        if ($hasDiscount && !$isFutureDiscount && $discountInfo['discounted_price'] > 0) {
                                                            $displayPrice = $discountInfo['discounted_price'];
                                                            $discountAmount = $originalPrice - $displayPrice;
                                                            $discountPercentage = $calcPct;
                                                        }
                                                        ?>
                                                        <div class="mb-0">
                                                            <?php if ($hasDiscount && $isFutureDiscount): ?>
                                                                <!-- Teaser Badge -->
                                                                <?php 
                                                                    $pct = $calcPct;
                                                                    $dt = date('d M Y', strtotime($discountInfo['valid_pickup_start_date']));
                                                                ?>
                                                                <div class="alert alert-warning px-2 py-1 mb-2 shadow-sm border-warning" style="font-size: 0.75rem; border-left: 4px solid #ffc107 !important;">
                                                                    <i class="fas fa-gift text-danger me-1"></i> Diskon <strong><?= $pct ?>%</strong> khusus krm <strong><?= $dt ?></strong>
                                                                </div>
                                                                <p class="text-dark fs-5 fw-bold mb-0">Rp<?= number_format($originalPrice, 0, ',', '.') ?></p>
                                                            <?php elseif ($hasDiscount && !$isFutureDiscount && isset($discountInfo['discounted_price']) && $discountInfo['discounted_price'] > 0): ?>
                                                                <!-- Badge Diskon -->
                                                                <span class="badge bg-danger mb-1">-<?= $discountPercentage ?>%</span>
                                                                <?php if (!empty($discountInfo['valid_pickup_start_time']) && !empty($discountInfo['valid_pickup_end_time'])): ?>
                                                                    <div style="font-size: 0.7rem;" class="badge bg-warning text-dark mb-1"><i class="fas fa-clock"></i> Khusus <?= date('H:i', strtotime($discountInfo['valid_pickup_start_time'])) ?> - <?= date('H:i', strtotime($discountInfo['valid_pickup_end_time'])) ?></div>
                                                                <?php endif; ?>
                                                                <!-- Harga Asli (dicoret) -->
                                                                <p class="text-muted text-decoration-line-through mb-0" style="font-size: 0.85rem;">
                                                                    Rp<?= number_format($originalPrice, 0, ',', '.') ?>
                                                                </p>
                                                                <!-- Harga Diskon -->
                                                                <p class="text-danger fs-5 fw-bold mb-0">
                                                                    Rp<?= number_format($displayPrice, 0, ',', '.') ?>
                                                                </p>
                                                            <?php elseif (isset($product['min_price']) && $product['min_price'] > 0): ?>
                                                                <?php // Produk punya varian dengan harga. ?>
                                                                <p class="text-dark fs-5 fw-bold mb-0">
                                                                <?php if ($product['min_price'] < $product['max_price']): ?>
                                                                    Rp<?= number_format($product['min_price'], 0, ',', '.') ?> - Rp<?= number_format($product['max_price'], 0, ',', '.') ?>
                                                                <?php else: ?>
                                                                    Rp<?= number_format($product['min_price'], 0, ',', '.') ?>
                                                                <?php endif; ?>
                                                                </p>
                                                            <?php else: ?>
                                                                <?php // Produk tidak punya varian, tampilkan harga utama. ?>
                                                                <p class="text-dark fs-5 fw-bold mb-0">
                                                                    Rp<?= number_format($product['harga'], 0, ',', '.') ?>
                                                                </p>
                                                            <?php endif; ?>
                                                        </div>
                                                        <?php if ($product['product_id'] === 'PRDKCUST' || $product['product_id'] === 'PRDKCUST1'): ?>
                                                                <a href="/shop/product/<?= esc($product['product_id']) ?>" class="btn border border-secondary rounded-pill px-3 text-primary"><i class="fa fa-edit me-2 text-primary"></i> Buat Pesanan</a>
                                                                  
                                                                <?php elseif ($product['product_id'] === 'PRDKUANG'): ?>
                                                                <a href="/shop/product/<?= esc($product['product_id']) ?>" class="btn border border-secondary rounded-pill px-3 text-primary"><i class="fa fa-edit me-2 text-primary"></i> Buat Pesanan</a>
                                                                <?php else: ?>
                                                                <form action="/cart/add" method="post" class="add-to-cart-form">
                                                                    <?= csrf_field() ?>
                                                                    <input type="hidden" name="product_id" value="<?= esc($product['product_id']) ?>">
                                                                    <input type="hidden" name="product_name" value="<?= esc($product['nama_produk']) ?>">
                                                                    <input type="hidden" name="product_price" value="<?= esc($displayPrice) ?>">
                                                                    <input type="hidden" name="original_price" value="<?= esc($originalPrice) ?>">
                                                                    <input type="hidden" name="has_discount" value="<?= $hasDiscount ? '1' : '0' ?>">
                                                                    <input type="hidden" name="quantity" value="1">
                                                                    <button type="submit" class="btn border border-secondary rounded-pill px-3 text-primary"><i class="fa fa-shopping-bag me-2 text-primary"></i> Add to cart</button>
                                                                </form>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <div class="col-12">
                                            <div class="alert alert-warning text-center" role="alert">
                                                Produk tidak ditemukan.
                                            </div>
                                        </div>
                                    <?php endif; ?>

                                    <div class="col-12">
                                        <div class="pagination d-flex justify-content-center mt-5">
                                            <?php if ($pager) : ?>
                                               <?= $pager->links('shop_group', 'bootstrap_pager') ?>
                                            <?php endif; ?>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
      
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= base_url('assets/lib/easing/easing.min.js') ?>"></script>
    <script src="<?= base_url('assets/lib/waypoints/waypoints.min.js') ?>"></script>
    <script src="<?= base_url('assets/lib/lightbox/js/lightbox.min.js') ?>"></script>
    <script src="<?= base_url('assets/lib/owlcarousel/owl.carousel.min.js') ?>"></script>


    <script>
        $(document).ready(function() {
              $('#category-filter-select').on('change', function() {
            $('#product-filter-form').submit();
        });
            // Handle subcategory dropdown filter
            $('.subcategory-filter').on('change', function() {
                const url = $(this).val();
                if (url) {
                    window.location.href = url;
                }
            });
            
            // Tangani submission form "Add to Cart" via AJAX

            $(document).on('submit', '.add-to-cart-form', function(e) {
                e.preventDefault();

                const form = $(this);
                const button = form.find('button[type="submit"]');
                const originalButtonText = button.html();

                button.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Adding...');

                $.ajax({
                    url: form.attr('action'),
                    method: form.attr('method'),
                    data: form.serialize(),
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                            window.showAddToCartModal(response.message || 'Produk berhasil ditambahkan ke keranjang.', response.cart_total_items);
                        } else {
                            window.showAddToCartModal(response.message || 'Terjadi kesalahan saat menambahkan item.');
                        }
                    },
                    error: function(xhr, status, error) {
                        window.showAddToCartModal('Tidak dapat menambahkan item ke keranjang. Silakan coba lagi.');
                        console.error("AJAX Error:", xhr.responseText);
                    },
                    complete: function() {
                        button.prop('disabled', false).html(originalButtonText);
                    }
                });
            });


            // Fungsi untuk mengupdate jumlah item di ikon keranjang navbar
            function updateCartCount(totalItems) {
                // Asumsi elemen span jumlah keranjang di navbar memiliki ID atau bisa ditarget
                // Contoh: <span class="position-absolute ... text-dark px-1" id="navbar-cart-count">3</span>
                // Jika tidak ada ID, Anda perlu menambahkan ID ke span tersebut atau menyesuaikan selektor
                $('.navbar .position-relative .rounded-circle').text(totalItems);
            }

            // Cek jika ada pesan sukses untuk permintaan kustom
            <?php if (session()->getFlashdata('custom_success')): ?>
                var successMessage = "<?= esc(session()->getFlashdata('custom_success'), 'js') ?>";
                $('#alertModalTitle').text('Permintaan Terkirim!');
                $('#alertModalBody').html('<p>' + successMessage + '</p>');
                $('#alertModal').modal('show');
            <?php endif; ?>
        });
    </script>


<?= $this->endSection() ?>
