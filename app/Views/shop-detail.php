<?= $this->extend('templates/main_layout') ?>


<?php
// Variabel $variants dan $images diasumsikan sudah dikirim dari controller
$productTitle = $product['nama_produk'] . ' - Beli ' . ($product['category_display'] ?? '') . ' | ' . ($store['name'] ?? 'JS Florist');
$productMetaDesc = 'Pesan ' . $product['nama_produk'] . ' dengan harga Rp ' . number_format($product['harga'], 0, ',', '.') . '. Cocok untuk ' . ($product['occasion_names'] ?? '') . '. Beli sekarang di ' . ($store['name'] ?? 'JS Florist') . '.';
?>

<?= $this->section('title') ?>
    <?= esc($productTitle) ?>
<?= $this->endSection() ?>

<?= $this->section('meta_description') ?>
    <?= esc($productMetaDesc) ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
        <style>

            /* Konsistensi warna */
            .bg-primary { background-color: #d09c4c !important; }
            .text-primary { color: #d09c4c !important; }
            .border-primary { border-color: #d09c4c !important; }
            .bg-secondary { background-color: #ebd4b6 !important; }
            .text-secondary { color: #ebd4b6 !important; }
            .border-secondary { border-color: #ebd4b6 !important; }
            
            /* Style untuk galeri gambar */
            .thumbnail-gallery img {
                cursor: pointer;
                transition: transform 0.2s;
                border: 2px solid transparent;
            }
            .thumbnail-gallery img:hover, .thumbnail-gallery img.active-thumb {
                transform: scale(1.05);
                border-color: #d09c4c;
            }
            /* Style untuk varian */
            .variant-options .form-check-input:checked + .form-check-label {
                border-color: #d09c4c;
                background-color: #fcf8f3;
                font-weight: bold;
            }
            .variant-options .form-check-label {
                border: 2px solid #eee;
                padding: 0.5rem 1rem;
                border-radius: 20px;
                cursor: pointer;
                transition: all 0.2s;
            }
            .variant-options .form-check-input {
                display: none;
            }
            /* Style untuk container gambar utama agar overlay bisa diposisikan */
            #main-image-container {
                position: relative; /* Penting untuk penempatan absolut overlay */
                display: inline-block; /* Agar div menyesuaikan ukuran gambar */
            }
            #variantNameOverlay {
                position: absolute;
                bottom: 10px;
                left: 10px;
                background-color: rgba(0,0,0,0.6);
                color: white;
                padding: 5px 10px;
                border-radius: 5px;
                font-size: 0.9em;
                display: none; /* Sembunyikan secara default */
            }
        </style>


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
        <div class="container-fluid page-header py-5" style="background: linear-gradient(rgba(0, 0, 0, 0.3), rgba(0, 0, 0, 0.3)), url(<?= base_url('assets/img/page-header.webp') ?>) center center no-repeat; background-size: cover;">
            <h1 class="text-center text-white display-6">Detail Produk</h1>
            <ol class="breadcrumb justify-content-center mb-0">
                <li class="breadcrumb-item"><a href="<?= site_url('dashboard') ?>">Home</a></li>
                <li class="breadcrumb-item"><a href="<?= site_url('shop') ?>">Shop</a></li>
                <li class="breadcrumb-item active text-white">Detail Produk</li>
            </ol>
        </div>
        <div class="container-fluid py-5 mt-5">
            <div class="container py-5">
                <div class="row g-4 mb-5">
                    <div class="col-lg-12">
                        <div class="row g-4">
                            <div class="col-lg-6">
                                <div class="border rounded">
                                    <div id="main-image-container">
                                        <a id="main-image-link" href="<?= base_url('assets/img/gambar/' . esc($product['gambar_url'])) ?>" data-lightbox="product-image">
                                            <img id="main-image" src="<?= base_url('assets/img/gambar/' . esc($product['gambar_url'])) ?>" class="img-fluid rounded" alt="<?= esc($product['nama_produk']) ?>" style="width:100%; height:auto; object-fit: cover;">
                                        </a>
                                        <div id="variantNameOverlay"></div>
                                    </div>
                                </div>
                                <div class="row g-2 mt-2 thumbnail-gallery">
                                    <div class="col-3">
                                        <img src="<?= base_url('assets/img/gambar/' . esc($product['gambar_url'])) ?>" class="img-fluid rounded thumb-image active-thumb" alt="Thumbnail Produk Utama">
                                    </div>
                                    <?php if (!empty($images)): foreach ($images as $img): ?>
                                        <div class="col-3">
                                            <img src="<?= base_url('assets/img/products/' . esc($img['image_url'])) ?>" class="img-fluid rounded thumb-image" alt="Thumbnail Tambahan">
                                        </div>
                                    <?php endforeach; endif; ?>

                                    <?php // NEW: Loop through variants to display their images as thumbnails ?>
                                    <?php if (!empty($variants)): ?>
                                        <?php foreach ($variants as $key => $variant): ?>
                                            <?php if (!empty($variant['gambar_varian_url'])): ?>
                                                <div class="col-3">
                                                    <img src="<?= base_url('assets/img/variants/' . esc($variant['gambar_varian_url'])) ?>" 
                                                         class="img-fluid rounded thumb-image variant-thumb-image" 
                                                         alt="Thumbnail Varian: <?= esc($variant['name']) ?>"
                                                         data-variant-id="variant-<?= $key ?>"> </div>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <h1 class="fw-bold mb-3"><?= esc($product['nama_produk']) ?></h1>
                                <p class="mb-3">Kategori: <?= esc($product['category_display']) ?></p>
                                
                                <?php 
                                // Cek apakah produk ini punya diskon
                                $hasDiscount = isset($productDiscount) && $productDiscount !== null;
                                
                                // Harga default dari varian atau produk
                                $defaultPrice = isset($variants[0]) ? $variants[0]['price'] : $product['harga'];
                                
                                // Jika ada diskon, gunakan data dari diskon
                                // Note: discounted_price di database adalah harga setelah diskon yang sudah ditetapkan admin
                                $originalPrice = $hasDiscount ? $productDiscount['original_price'] : $defaultPrice;
                                $displayPrice = $hasDiscount && isset($productDiscount['discounted_price']) && $productDiscount['discounted_price'] > 0 ? $productDiscount['discounted_price'] : $defaultPrice;
                                
                                $isFutureDiscount = false;
                                if ($hasDiscount) {
                                    if (!empty($productDiscount['valid_pickup_start_date']) && date('Y-m-d') < $productDiscount['valid_pickup_start_date']) {
                                        $isFutureDiscount = true;
                                        $displayPrice = $originalPrice;
                                    }
                                }
                                ?>
                                
                                <?php if ($hasDiscount && $isFutureDiscount): ?>
                                    <div class="mb-4 mt-2">
                                        <?php 
                                            $dt = date('d F Y', strtotime($productDiscount['valid_pickup_start_date']));
                                            $pct = round($productDiscount['discount_percentage'] ?? 0);
                                        ?>
                                        <div class="alert alert-warning p-3 mb-3 shadow-sm" style="border-left: 5px solid #ffc107 !important; background-color: #fff8e1;" role="alert">
                                            <div class="d-flex align-items-start">
                                                <i class="fas fa-gift fa-2x text-danger me-3 mt-1"></i>
                                                <div>
                                                    <h6 class="alert-heading fw-bold mb-1 text-dark" style="font-size: 1.1rem;">🎉 Promo Spesial! Diskon <?= $pct ?>%</h6>
                                                    <p class="mb-0 text-dark" style="font-size: 0.9rem;">
                                                        Pilih tanggal pengiriman <strong><?= $dt ?></strong> saat <em>Checkout</em> untuk menikmati potongan harga ini.
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                        <h5 class="fw-bold mb-3 price-display" id="product-price">
                                            Rp<?= number_format($originalPrice, 0, ',', '.') ?>
                                        </h5>
                                    </div>
                                <?php elseif ($hasDiscount && !$isFutureDiscount && isset($productDiscount['discounted_price']) && $productDiscount['discounted_price'] > 0): ?>
                                    <div class="mb-3">
                                        <span class="badge bg-danger mb-2">DISKON <?= round($productDiscount['discount_percentage'] ?? 0) ?>%</span>
                                        <?php if (!empty($productDiscount['valid_pickup_start_time']) && !empty($productDiscount['valid_pickup_end_time'])): ?>
                                            <span class="badge bg-warning text-dark mb-2 ms-1"><i class="fas fa-clock"></i> Khusus Jam <?= date('H:i', strtotime($productDiscount['valid_pickup_start_time'])) ?> - <?= date('H:i', strtotime($productDiscount['valid_pickup_end_time'])) ?></span>
                                        <?php endif; ?>
                                        <br>
                                        <span class="text-muted text-decoration-line-through fs-5">
                                            Rp<?= number_format($originalPrice, 0, ',', '.') ?>
                                        </span>
                                        <h5 class="fw-bold text-danger price-display" id="product-price" data-discounted="true" data-discount-price="<?= $displayPrice ?>">
                                            Rp<?= number_format($displayPrice, 0, ',', '.') ?>
                                        </h5>
                                        <small class="text-success">Hemat Rp<?= number_format($productDiscount['discount_amount'] ?? 0, 0, ',', '.') ?></small>
                                    </div>
                                <?php else: ?>
                                    <h5 class="fw-bold mb-3 price-display" id="product-price">
                                        Rp<?= number_format($defaultPrice, 0, ',', '.') ?>
                                    </h5>
                                <?php endif; ?>

                                <?php if (isset($variants) && count($variants) > 0): ?>
                                    <div class="mb-4 variant-options">
                                        <label class="form-label fw-bold d-block mb-2">Pilih Varian:</label>
                                        <div class="d-flex flex-wrap gap-2">
                                            <?php foreach ($variants as $key => $variant): ?>
                                                <div class="form-check">
                                                    <input type="radio" class="form-check-input variant-radio-option" 
                                                           id="variant-<?= $key ?>" name="variant_option" 
                                                           value="<?= esc($variant['price']) ?>"
                                                           data-name="<?= esc($variant['name']) ?>"
                                                           data-variant-image="<?= !empty($variant['gambar_varian_url']) ? base_url('assets/img/variants/' . esc($variant['gambar_varian_url'])) : base_url('assets/img/gambar/' . esc($product['gambar_url'])) ?>"
                                                           <?= $key == 0 ? 'checked' : '' ?>>
                                                    <label class="form-check-label" for="variant-<?= $key ?>"><?= esc($variant['name']) ?></label>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <?php if ($product['product_id'] === 'PRDKUANG'): ?>
                                <small class="text-muted fw-normal fs-6">(Biaya Jasa Rangkai)</small>
                                <?php endif; ?>
                                
                                <?php if ($product['product_id'] === 'PRDKUANG'): ?>
                                    <form id="money-bouquet-form" action="<?= site_url('cart/add') ?>" method="post" class="add-to-cart-ajax-form">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="product_id" value="<?= esc($product['product_id']) ?>">
                                        <input type="hidden" name="product_name" value="<?= esc($product['nama_produk']) ?>">
                                        <input type="hidden" name="quantity" value="1">
                                        <input type="hidden" name="product_price" id="hidden-total-price">
                                        
                                        <input type="hidden" name="custom_details[nominal]" id="hidden-nominal-uang">
                                        <input type="hidden" name="custom_details[pecahan]" id="hidden-pecahan">
                                        <input type="hidden" name="custom_details[upah]" id="hidden-upah-jasa">
                                        <input type="hidden" name="custom_details[biaya_penukaran]" id="hidden-biaya-penukaran">
                                        
                                        <input type="hidden" name="custom_details[money_source_type]" value="uang_dari_toko">

                                        <div class="mb-3">
                                            <label for="nominal-uang-input" class="form-label fw-bold">1. Masukkan Nominal Uang:</label>
                                            <input type="number" class="form-control" id="nominal-uang-input" placeholder="Contoh: 1000000" required>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label fw-bold">2. Pilih Pecahan Uang:</label>
                                            <div class="input-group mb-2"><div class="input-group-text"><input class="form-check-input mt-0" type="radio" name="pecahan_radio" value="100000" id="pecahan-100k" checked></div><label class="form-control" for="pecahan-100k">Rp 100.000</label><div class="input-group-text"><input class="form-check-input" type="checkbox" id="uang-baru-100k"><label class="form-check-label ms-2" for="uang-baru-100k">Uang Baru</label></div></div>
                                            <div class="input-group mb-2"><div class="input-group-text"><input class="form-check-input mt-0" type="radio" name="pecahan_radio" value="50000" id="pecahan-50k"></div><label class="form-control" for="pecahan-50k">Rp 50.000</label><div class="input-group-text"><input class="form-check-input" type="checkbox" id="uang-baru-50k"><label class="form-check-label ms-2" for="uang-baru-50k">Uang Baru</label></div></div>
                                            <div class="form-check"><input class="form-check-input" type="radio" name="pecahan_radio" value="20000" id="pecahan-20k"><label class="form-check-label" for="pecahan-20k">Rp 20.000</label></div>
                                            <div class="form-check"><input class="form-check-input" type="radio" name="pecahan_radio" value="10000" id="pecahan-10k"><label class="form-check-label" for="pecahan-10k">Rp 10.000</label></div>
                                            <div class="form-check"><input class="form-check-input" type="radio" name="pecahan_radio" value="5000" id="pecahan-5k"><label class="form-check-label" for="pecahan-5k">Rp 5.000</label></div>
                                            <div class="form-check"><input class="form-check-input" type="radio" name="pecahan_radio" value="2000" id="pecahan-2k"><label class="form-check-label" for="pecahan-2k">Rp 2.000</label></div>
                                        </div>

                                        <div class="card bg-light p-3 mt-4">
                                            <h6 class="fw-bold">Rincian Biaya:</h6>
                                            <ul class="list-unstyled mb-2">
                                                <li>Jumlah Lembar: <span class="float-end"><span id="summary-lembar">0</span> Lembar</span></li>
                                                <li>Upah Jasa Rangkai: <span class="float-end">Rp <span id="summary-upah">0</span></span></li>
                                                <li>Nominal Uang: <span class="float-end">Rp <span id="summary-nominal">0</span></span></li>
                                                <li>Biaya Penukaran Uang: <span class="float-end">Rp <span id="summary-penukaran">0</span></span></li>
                                            </ul>
                                            <hr class="my-2">
                                            <h5 class="fw-bold">Total: <span class="float-end text-primary">Rp <span id="summary-total">0</span></span></h5>
                                        </div>
                                        
                                        <div id="limit_alert" class="alert alert-warning mt-3" style="display: none;">Untuk pesanan di atas 100 lembar, silakan hubungi CS kami.</div>
                                        
                                        <button type="submit" class="btn border-secondary rounded-pill px-4 py-2 mb-4 text-primary mt-4"><i class="fa fa-shopping-bag me-2 text-primary"></i> Tambah ke Keranjang</button>
                                    </form>
                                <?php elseif ($product['product_id'] === 'PRDKCUST' || $product['product_id'] === 'PRDKCUST1'): ?>
                                    <p class="mb-4">Silakan isi form di bawah ini untuk membuat pesanan kustom Anda.</p>
                                    <form action="<?= site_url('custom/checkout') ?>" method="post">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="product_id" value="<?= esc($product['product_id']) ?>">
                                        <input type="hidden" name="product_name" value="<?= esc($product['nama_produk']) ?>">
                                        <input type="hidden" name="product_price" value="<?= esc($product['harga']) ?>">
                                        <input type="hidden" name="quantity" value="1">

                                        <div class="form-group mb-3">
                                            <label for="jenis_item" class="form-label"><strong>Jenis Item</strong> (misal: beng-beng, hotwheels)</label>
                                            <input type="text" id="jenis_item" name="custom_details[jenis_item]" class="form-control" required>
                                        </div>

                                        <div class="form-group mb-3">
                                            <label for="jumlah_item" class="form-label"><strong>Jumlah Item</strong> </label>
                                            <input type="text" id="jumlah_item" name="custom_details[jumlah_item]" class="form-control" required>
                                        </div>

                                        <div class="form-group mb-4">
                                            <label class="form-label"><strong>Request Bunga</strong> (pilih satu atau lebih)</label>
                                            <div class="row">
                                                <?php
                                                    $available_flowers = ['Mawar Merah', 'Mawar Putih', 'Lily', 'Baby Breath', 'Anggrek', 'Aster'];
                                                    foreach ($available_flowers as $flower):
                                                ?>
                                                <div class="col-md-6">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" name="custom_details[bunga][]" value="<?= esc($flower) ?>" id="flower_<?= str_replace(' ', '_', $flower) ?>">
                                                        <label class="form-check-label" for="flower_<?= str_replace(' ', '_', $flower) ?>"><?= esc($flower) ?></label>
                                                    </div>
                                                </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>

                                        <button type="submit" class="btn border-secondary rounded-pill px-4 py-2 mb-4 text-primary"><i class="fa fa-paper-plane me-2 text-primary"></i> Lanjutkan & Ajukan Permintaan</button>
                                    </form>
                                <?php else: ?>
                                    <p class="mb-4"><?= nl2br(esc($product['deskripsi_produk'])) ?></p>
                                    <form action="<?= site_url('cart/add') ?>" method="post" class="add-to-cart-ajax-form">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="product_id" value="<?= esc($product['product_id']) ?>">
                                        <input type="hidden" name="product_name" value="<?= esc($product['nama_produk']) ?>">
                                        <input type="hidden" name="product_price" id="selected-price" value="<?= esc($displayPrice) ?>">
                                        <input type="hidden" name="custom_details[variant_name]" id="selected-variant-name" value="<?= isset($variants[0]) && count($variants) > 0 ? esc($variants[0]['name']) : '' ?>">
                                        <input type="hidden" name="original_price" id="original-price" value="<?= esc($originalPrice) ?>">
                                        <input type="hidden" name="has_discount" value="<?= $hasDiscount && isset($productDiscount['discounted_price']) && $productDiscount['discounted_price'] > 0 ? '1' : '0' ?>">

                                        <div class="input-group quantity mb-5" style="width: 120px;">
                                            <div class="input-group-btn">
                                                <button class="btn btn-sm btn-minus rounded-circle bg-light border" type="button">
                                                    <i class="fa fa-minus"></i>
                                                </button>
                                            </div>
                                            <input type="text" name="quantity" class="form-control form-control-sm text-center border-0" value="1" min="1">
                                            <div class="input-group-btn">
                                                <button class="btn btn-sm btn-plus rounded-circle bg-light border" type="button">
                                                    <i class="fa fa-plus"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <button type="submit" class="btn border border-secondary rounded-pill px-4 py-2 mb-4 text-primary"><i class="fa fa-shopping-bag me-2 text-primary"></i> Tambah ke Keranjang</button>
                                    </form>
                                <?php endif; ?>
                            </div>
                            <div class="col-lg-12">
                                <nav>
                                    <div class="nav nav-tabs mb-3">
                                        <button class="nav-link active border-white border-bottom-0" type="button" role="tab"
                                            id="nav-about-tab" data-bs-toggle="tab" data-bs-target="#nav-about"
                                            aria-controls="nav-about" aria-selected="true">Description</button>
                                    </div>
                                </nav>
                                <div class="tab-content mb-5">
                                    <div class="tab-pane active" id="nav-about" role="tabpanel" aria-labelledby="nav-about-tab">
                                        <p><?= nl2br(esc($product['deskripsi_produk'])) ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <h1 class="fw-bold mb-0">Produk Terkait</h1>
                <div class="vesitable">
                    <div class="owl-carousel artikel-carousel justify-content-center">
                        <?php if (!empty($relatedProducts)): ?>
                            <?php foreach ($relatedProducts as $related): 
                                // Cek diskon untuk related product
                                  $relatedDiscount = isset($productDiscounts[$related['product_id']]) ? $productDiscounts[$related['product_id']] : null;
                                  $relatedHasDiscount = $relatedDiscount !== null && isset($relatedDiscount['discounted_price']) && $relatedDiscount['discounted_price'] > 0;
                                  
                                  $isRelatedFutureDiscount = false;
                                  if ($relatedHasDiscount && !empty($relatedDiscount['valid_pickup_start_date']) && date('Y-m-d') < $relatedDiscount['valid_pickup_start_date']) {
                                      $isRelatedFutureDiscount = true;
                                  }
                                  
                                  $relatedOriginalPrice = $related['harga'];
                                  $relatedDisplayPrice = ($relatedHasDiscount && !$isRelatedFutureDiscount) ? $relatedDiscount['discounted_price'] : $related['harga'];
                              ?>
                                  <div class="border border-primary rounded position-relative vesitable-item">
                                      <div class="vesitable-img" style="height: 250px; overflow: hidden;">
                                          <a href="<?= site_url('shop/product/' . $related['product_id']) ?>">
                                              <img src="<?= base_url('assets/img/gambar/' . esc($related['gambar_url'])) ?>" class="img-fluid w-100 rounded-top" alt="<?= esc($related['nama_produk']) ?>" style="height: 100%; object-fit: cover;">
                                          </a>
                                      </div>
                                      <div class="text-white bg-primary px-3 py-1 rounded position-absolute" style="top: 10px; right: 10px;"><?= esc($related['category_display']) ?></div>
                                      <?php if ($relatedHasDiscount && $isRelatedFutureDiscount): ?>
                                          <?php 
                                              $dtR = date('d M Y', strtotime($relatedDiscount['valid_pickup_start_date'])); 
                                              $pctR = round($relatedDiscount['discount_percentage'] ?? 0);
                                          ?>
                                          <div class="position-absolute bg-warning text-dark px-2 py-1 shadow-sm border border-warning" style="top: 10px; left: 10px; font-size: 0.75rem; border-radius: 4px;">
                                              <i class="fas fa-gift text-danger me-1"></i> <strong><?= $pctR ?>%</strong> Khusus <?= $dtR ?>
                                          </div>
                                      <?php elseif ($relatedHasDiscount && !$isRelatedFutureDiscount): ?>
                                          <span class="badge bg-danger position-absolute" style="top: 10px; left: 10px;">-<?= round($relatedDiscount['discount_percentage'] ?? 0) ?>%</span>
                                      <?php endif; ?>
                                      <div class="p-4 pb-0 rounded-bottom">
                                          <h4><?= esc($related['nama_produk']) ?></h4>
                                          <p><?= esc(substr($related['deskripsi_produk'], 0, 50)) . '...' ?></p>
                                          <div class="d-flex justify-content-between flex-lg-wrap">
                                              <?php if ($relatedHasDiscount && !$isRelatedFutureDiscount): ?>
                                                  <div>
                                                      <p class="text-muted text-decoration-line-through mb-0" style="font-size: 0.9rem;">Rp<?= number_format($relatedOriginalPrice, 0, ',', '.') ?></p>
                                                      <p class="text-danger fs-5 fw-bold mb-0">Rp<?= number_format($relatedDisplayPrice, 0, ',', '.') ?></p>
                                                  </div>
                                              <?php else: ?>
                                                  <p class="text-dark fs-5 fw-bold">Rp<?= number_format($relatedOriginalPrice, 0, ',', '.') ?></p>
                                              <?php endif; ?>
                                            <a href="<?= site_url('shop/product/' . $related['product_id']) ?>" class="btn border border-secondary rounded-pill px-3 py-1 mb-4 text-primary"><i class="fa fa-eye me-2 text-primary"></i> Lihat Detail</a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p>Tidak ada produk terkait.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
       
        <a href="#" class="btn btn-primary border-3 border-primary rounded-circle back-to-top"><i class="fa fa-arrow-up"></i></a>

<?= $this->endSection() ?>

<?= $this->section('extra_js') ?>
    <script>
    $(document).ready(function() {
        // --- Interaksi Galeri Gambar (Thumbnail) ---
        // Menambahkan listener untuk semua thumbnail
        $('.thumbnail-gallery img').on('click', function() {
            var newSrc = $(this).attr('src');
            var isVariantThumb = $(this).hasClass('variant-thumb-image'); // Check if it's a variant thumbnail
            var variantId = $(this).data('variant-id'); // Get variant ID if it's a variant thumbnail

            // Update main image and lightbox link
            $('#main-image').attr('src', newSrc);
            $('#main-image-link').attr('href', newSrc);
            
            // Remove active class from all thumbnails and add to the clicked one
            $('.thumb-image').removeClass('active-thumb');
            $(this).addClass('active-thumb');

            // Handle variant radio button and overlay
            if (isVariantThumb && variantId) {
                // Find and check the corresponding variant radio button
                var correspondingRadio = $('#' + variantId);
                if (correspondingRadio.length > 0) {
                    correspondingRadio.prop('checked', true).trigger('change'); // Trigger change to update price and overlay
                }
            } else {
                // If it's the main product image or an additional image, hide overlay
                $('#variantNameOverlay').hide();
                // Also, uncheck all variant radio buttons
                $('.variant-radio-option').prop('checked', false);
            }
        });

        // --- Interaksi Pilihan Varian (Radio Button) ---
        $('.variant-options input[type=radio]').on('change', function() {
            if ($(this).is(':checked')) {
                var newPrice = parseFloat($(this).val());
                var variantName = $(this).data('name');
                var variantImage = $(this).data('variant-image'); // Get the variant specific image

                // 1. Update tampilan harga di halaman (tetap pakai harga diskon jika ada)
                const priceEl = $('#product-price');
                const hasDiscount = priceEl.data('discounted') === true || priceEl.data('discounted') === 'true';
                const discountPrice = parseFloat(priceEl.data('discount-price'));
                const finalPrice = (hasDiscount && !isNaN(discountPrice) && discountPrice > 0) ? discountPrice : newPrice;
                priceEl.text('Rp' + finalPrice.toLocaleString('id-ID'));

                // 2. Update nilai di dalam form yang akan dikirim ke keranjang (harga asli)
                $('#selected-price').val(finalPrice);
                $('#selected-variant-name').val(variantName);
                $('#original-price').val(newPrice);

                // 3. Update main product image and lightbox link with variant image
                $('#main-image').attr('src', variantImage);
                $('#main-image-link').attr('href', variantImage);

                // 4. Update and show variant name overlay
                if (variantName) {
                    $('#variantNameOverlay').text(variantName).show();
                } else {
                    $('#variantNameOverlay').hide();
                }
                
                // Remove active class from all thumbnails since a variant is selected
                $('.thumb-image').removeClass('active-thumb');
                // Optional: add active-thumb to the corresponding variant thumbnail
                // This would require matching the variant-image to a thumb-image
                // For simplicity, just remove active-thumb from others for now.
            }
        });
        
        // --- Logika inisialisasi tampilan saat halaman dimuat ---
        // Ini akan memastikan gambar dan harga varian pertama terpilih secara default
        // dan overlay ditampilkan jika varian memiliki nama
        const initialSelectedVariant = $('.variant-options input[type=radio]:checked');
        if (initialSelectedVariant.length > 0) {
            const initialPrice = parseFloat(initialSelectedVariant.val());
            const initialVariantName = initialSelectedVariant.data('name');
            const initialVariantImage = initialSelectedVariant.data('variant-image');

            const initialPriceEl = $('#product-price');
            const initialHasDiscount = initialPriceEl.data('discounted') === true || initialPriceEl.data('discounted') === 'true';
            const initialDiscountPrice = parseFloat(initialPriceEl.data('discount-price'));
            const initialFinalPrice = (initialHasDiscount && !isNaN(initialDiscountPrice) && initialDiscountPrice > 0)
                ? initialDiscountPrice
                : initialPrice;

            initialPriceEl.text('Rp' + initialFinalPrice.toLocaleString('id-ID'));
            $('#selected-price').val(initialFinalPrice);
            $('#selected-variant-name').val(initialVariantName);
            $('#original-price').val(initialPrice);

            $('#main-image').attr('src', initialVariantImage);
            $('#main-image-link').attr('href', initialVariantImage);

            if (initialVariantName) {
                $('#variantNameOverlay').text(initialVariantName).show();
            } else {
                $('#variantNameOverlay').hide();
            }
            // Ensure the main product thumbnail is not active if a variant is selected initially
            $('.thumb-image').removeClass('active-thumb');
            // If you want to highlight the variant's thumbnail on load, find it and add active-thumb
            $('.variant-thumb-image[data-variant-id="' + initialSelectedVariant.attr('id') + '"]').addClass('active-thumb');

        } else {
            // If no variants or no variant is checked, hide overlay
            $('#variantNameOverlay').hide();
            // Ensure main product thumbnail is active if no variant is selected
            $('.thumbnail-gallery img:first').addClass('active-thumb');
        }


        // --- Kode AJAX Anda yang sudah ada (untuk form buket uang) ---
        if ($('#money-bouquet-form').length) {
            function calculateUpah(lembar) { if (lembar >= 81 && lembar <= 100) return 1000000; if (lembar >= 61 && lembar <= 80) return 800000; if (lembar >= 41 && lembar <= 60) return 600000; if (lembar >= 21 && lembar <= 40) return 400000; if (lembar >= 5 && lembar <= 20) return 250000; return 0; }
            function calculateMoneyBouquet() {
                const FATOR_BIAYA_PENUKARAN = 0.20;
                let nominalUang = parseFloat($('#nominal-uang-input').val()) || 0;
                let pecahan = parseFloat($('input[name="pecahan_radio"]:checked').val());
                let lembar = (nominalUang > 0 && pecahan > 0) ? Math.floor(nominalUang / pecahan) : 0;
                let upahJasa = calculateUpah(lembar);
                let biayaPenukaran = 0;

                $('#limit_alert').toggle(lembar > 100);

                // Tentukan apakah biaya penukaran berlaku
                const isUangBaru100k = $('#uang-baru-100k').is(':checked');
                const isUangBaru50k = $('#uang-baru-50k').is(':checked');

                if ((pecahan < 50000) || (pecahan === 100000 && isUangBaru100k) || (pecahan === 50000 && isUangBaru50k)) {
                    biayaPenukaran = nominalUang * FATOR_BIAYA_PENUKARAN;
                }
                
                const totalHarga = upahJasa + nominalUang + biayaPenukaran;
                
                // Update tampilan ringkasan
                $('#summary-lembar').text(lembar);
                $('#summary-upah').text(upahJasa.toLocaleString('id-ID'));
                $('#summary-nominal').text(nominalUang.toLocaleString('id-ID'));
                $('#summary-penukaran').text(biayaPenukaran.toLocaleString('id-ID'));
                $('#summary-total').text(totalHarga.toLocaleString('id-ID'));
                
                // Update hidden fields untuk form submission
                $('#hidden-total-price').val(totalHarga);
                $('#hidden-nominal-uang').val(nominalUang);
                $('#hidden-pecahan').val(pecahan);
                $('#hidden-upah-jasa').val(upahJasa);
                $('#hidden-biaya-penukaran').val(biayaPenukaran);
            }

            $('#nominal-uang-input, input[name="pecahan_radio"], #uang-baru-100k, #uang-baru-50k').on('input change', calculateMoneyBouquet);
            calculateMoneyBouquet();
        }

        $(document).on('submit', '.add-to-cart-ajax-form', function(e) {
            e.preventDefault();
            var form = $(this);

            // Cek apakah ini form buket uang untuk validasi khusus
            if (form.is('#money-bouquet-form')) {
                const nominalUang = parseFloat($('#nominal-uang-input').val()) || 0;
                const pecahan = parseFloat($('input[name="pecahan_radio"]:checked').val());
                const lembar = (nominalUang > 0 && pecahan > 0) ? Math.floor(nominalUang / pecahan) : 0;

                if (nominalUang <= 0) {
                    alert('Silakan masukkan nominal uang terlebih dahulu.');
                    return; // Hentikan proses
                }
                if (nominalUang % pecahan !== 0) {
                    alert(`Nominal yang Anda masukkan (Rp ${nominalUang.toLocaleString('id-ID')}) harus merupakan kelipatan dari pecahan Rp ${pecahan.toLocaleString('id-ID')}.`);
                    return; // Hentikan proses
                }
                if (lembar < 5) {
                    alert('Jumlah lembar uang minimal untuk dibuat buket adalah 5 lembar.');
                    return; // Hentikan proses
                }
                if (lembar > 100) {
                    alert('Pesanan melebihi 100 lembar. Silakan hubungi CS kami.');
                    return; // Hentikan proses
                }
            }
            
            // Jika validasi lolos (atau bukan form buket uang), lanjutkan AJAX
            $.ajax({
                type: "POST", url: form.attr('action'), data: form.serialize(), dataType: "json",
                success: function(response) {
                    window.showAddToCartModal(response.message || 'Produk berhasil ditambahkan ke keranjang.', response.cart_total_items);
                },
                error: function() {
                    window.showAddToCartModal('Terjadi kesalahan. Silakan coba lagi.');
                }
            });
        });
    });

    </script>
<?= $this->endSection() ?>
