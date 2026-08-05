<?= $this->extend('templates/main_layout') ?>

<?= $this->section('title') ?>
Checkout - <?= esc($store['name']) ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
    <head>
        <meta charset="utf-8">
        <title>Checkout - JS Florist</title>
        <meta content="width=device-width, initial-scale=1.0" name="viewport">
        <meta content="" name="keywords">
        <meta content="" name="description">

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600&family=Raleway:wght@600;800&display=swap" rel="stylesheet"> 
        <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
        <link rel="stylesheet" href="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.css" />
        <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css"/>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">

        <link href="<?= base_url('assets/lib/lightbox/css/lightbox.min.css')?>" rel="stylesheet">
        <link href="<?= base_url('assets/lib/owlcarousel/assets/owl.carousel.min.css')?>" rel="stylesheet">

        <link href="<?= base_url('assets/css/bootstrap.min.css') ?>" rel="stylesheet">
        <link href="<?= base_url('assets/css/style.css') ?>" rel="stylesheet">

        <style>
            .form-check-input:checked { background-color: #d09c4c; border-color: #d09c4c; }
            .btn-primary-custom { background-color: #d09c4c; border-color: #d09c4c; color: #fff; }
            .btn-primary-custom:hover { background-color: #bb8a40; border-color: #bb8a40; color: #fff; }
            #map-container { height: 350px; width: 100%; border: 1px solid #ccc; margin-top: 15px; }
            .bg-primary { background-color: #d09c4c !important; }
            .text-primary { color: #d09c4c !important; }
            .border-primary { border-color: #d09c4c !important; }
            .bg-secondary { background-color: #ebd4b6 !important; }
            .text-secondary { color: #ebd4b6 !important; }
            .border-secondary { border-color: #ebd4b6 !important; }
            .table img { width: 80px; height: 80px; object-fit: cover; border-radius: 50%; }
            .form-check-label sup { color: red; }
            
            @media (max-width: 768px) {
                #mobile-validation-alert {
                    font-size: 14px;
                    margin: 10px;
                    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
                    border-radius: 8px;
                }
                .is-invalid {
                    border-color: #dc3545 !important;
                    box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25) !important;
                }
                .invalid-feedback {
                    display: block !important;
                    width: 100%;
                    margin-top: 0.25rem;
                    font-size: 0.875rem;
                    color: #dc3545;
                    background-color: #f8d7da;
                    border: 1px solid #f5c6cb;
                    border-radius: 0.25rem;
                    padding: 0.375rem 0.75rem;
                }
                input[type="datetime-local"] {
                    min-height: 44px;
                    font-size: 16px;
                }
                .form-check-label {
                    padding: 10px;
                    cursor: pointer;
                    border-radius: 5px;
                    transition: background-color 0.2s;
                }
                .form-check-label:hover {
                    background-color: #f8f9fa;
                }
                .alert {
                    border-radius: 8px;
                    margin-bottom: 1rem;
                }
                .form-item {
                    margin-bottom: 1.5rem;
                }
            }
        </style>
    </head>

    <body>
        <div class="container-fluid page-header py-5">
            <h1 class="text-center text-white display-6">Checkout</h1>
            <ol class="breadcrumb justify-content-center mb-0">
                <li class="breadcrumb-item"><a href="/dashboard">Home</a></li>
                <li class="breadcrumb-item"><a href="/cart">Keranjang</a></li>
                <li class="breadcrumb-item active text-white">Checkout</li>
            </ol>
        </div>

        <div class="container-fluid py-5">
            <div class="container py-5">
                <h1 class="mb-4">Detail Pengiriman & Pembayaran</h1>

                <?php if (session()->getFlashdata('errors')): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <strong>Validasi Gagal!</strong>
                        <ul>
                            <?php foreach (session()->getFlashdata('errors') as $error): ?>
                                <li><?= esc($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <form id="checkoutForm" action="/checkout/process" method="POST">
                    <?= csrf_field() ?>
                    <input type="hidden" name="geocode_mismatch" id="geocode_mismatch_input" value="0">
                    <input type="hidden" name="rev_address_json" id="rev_address_json" value="">
                    <input type="hidden" name="pickup_location" id="pickup_location_input">

                    <div class="row g-5">
                        <div class="col-md-12 col-lg-6 col-xl-7">
                            <p class="mb-2">Informasi Penerima Bunga:</p>

                            <div class="row">
                                <div class="col-md-12 col-lg-6">
                                    <div class="form-item w-100">
                                        <label class="form-label my-3">Nama Depan Penerima<sup>*</sup></label>
                                        <input type="text" class="form-control" name="nama_depan" value="<?= old('nama_depan', $loggedInUser['nama_depan'] ?? '') ?>" required>
                                    </div>
                                </div>

                                <div class="col-md-12 col-lg-6">
                                    <div class="form-item w-100">
                                        <label class="form-label my-3">Nama Belakang Penerima<sup>*</sup></label>
                                        <input type="text" class="form-control" name="nama_belakang" value="<?= old('nama_belakang', $loggedInUser['nama_belakang'] ?? '') ?>">
                                    </div>
                                </div>
                            </div>

                            <div class="form-item" id="penerima-nomor-hp-container">
                                <label class="form-label my-3">Nomor Telepon Penerima<sup>*</sup></label>
                                <input type="tel" class="form-control" id="penerima_nomor_hp_input" name="penerima_nomor_hp" value="<?= old('penerima_nomor_hp') ?>">
                            </div>

                            <div class="form-item">
                                <label class="form-label my-3">Nomor Telepon Pemesan<sup>*</sup></label>
                                <input type="tel" class="form-control" name="nomor_pemesan" value="<?= old('nomor_pemesan', $loggedInUser['nomor_hp'] ?? '') ?>" required>
                            </div>

                            <div class="form-item" id="delivery-address-container">
                                <label class="form-label my-3">Alamat Lengkap Pengiriman<sup>*</sup></label>
                                <input type="text" class="form-control" name="alamat_pengiriman_teks" id="map-address-input" placeholder="Alamat dasar (akan terisi dari titik). Isi detail di bawah" value="<?= old('alamat_pengiriman_teks') ?>">
                                <div class="form-text">Alamat dasar akan diisi based on titik; lengkapi <strong>Detail Alamat</strong> (nomor rumah/jalan) di bawah.</div>

                                <div id="rev_address_badge" class="mt-2" style="display:none;">
                                    <small class="text-muted">Alamat dasar terdeteksi: <span id="rev_address_display"></span></small>
                                </div>

                                <div class="mt-2">
                                    <input type="text" class="form-control" name="alamat_detail" id="alamat_detail_input" placeholder="Nomor rumah / Jalan / Blok" value="<?= old('alamat_detail') ?>">
                                </div>

                                <input type="hidden" id="alamat-latitude" name="alamat_latitude" value="<?= old('alamat_latitude') ?>">
                                <input type="hidden" id="alamat-longitude" name="alamat_longitude" value="<?= old('alamat_longitude') ?>">

                                <div id="map-container"></div>
                                <button type="button" id="get-current-location-btn" class="btn btn-sm btn-info mt-2">Gunakan Lokasi Saat Ini</button>
                            </div>

                            <div class="form-item">
                                <label class="form-label my-3">Email Anda (Opsional)</label>
                                <input type="email" class="form-control" name="email_anda" value="<?= old('email_anda', $loggedInUser['email'] ?? '') ?>">
                            </div>

                            <div class="form-item">
                                <label class="form-label my-3">Tanggal & Jam Pengantaran/Pengambilan<sup>*</sup></label>
                                <input type="datetime-local" class="form-control" id="datetime-picker-checkout" name="tanggal_pengantaran" value="<?= old('tanggal_pengantaran') ?>" required>
                            </div>

                            <hr class="my-5">

                            <div class="mb-4">
                                <label class="form-label mb-3">Pilih Tipe Pengantaran<sup>*</sup></label>

                                <div class="form-check text-start my-3">
                                    <input type="radio" class="form-check-input" id="deliveryOption" name="tipe_pengantaran" value="Delivery" <?= (old('tipe_pengantaran') == 'Delivery') ? 'checked' : '' ?> required>
                                    <label class="form-check-label" for="deliveryOption">Antar ke Alamat (Delivery)</label>
                                </div>

                                <div class="form-check text-start my-3">
                                    <input class="form-check-input" type="radio" name="tipe_pengantaran" value="Self-Pickup" id="pickupOption" <?= (old('tipe_pengantaran') == 'Self-Pickup') ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="pickupOption">Ambil Sendiri di Toko JS Florist</label>
                                </div>
                            </div>
                            
                            <div class="form-item">
                                <label class="form-label my-3">Catatan Pesanan / Isi Kartu Ucapan (Opsional)</label>
                                <textarea name="catatan_penerima" class="form-control" spellcheck="false" cols="30" rows="5" placeholder="Tulis pesan untuk penerima atau catatan khusus lainnya."><?= old('catatan_penerima') ?></textarea>
                            </div>
                        </div>

                        <div class="col-md-12 col-lg-6 col-xl-5">
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th scope="col">Produk</th>
                                            <th scope="col">Nama</th>
                                            <th scope="col">Harga</th>
                                            <th scope="col">Qty</th>
                                            <th scope="col">Total</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        <?php
                                            $subtotalProduk = 0;
                                            $subtotalAwal = 0;
                                            $totalDiskon = 0;

                                            $appliedVoucher = $appliedVoucher ?? null;
                                            $voucherDiscount = !empty($appliedVoucher) ? (float)($appliedVoucher['discount_amount'] ?? 0) : 0;
                                            $voucherFreeShipping = !empty($appliedVoucher) ? (int)($appliedVoucher['free_shipping'] ?? 0) : 0;
                                        ?>

                                        <?php if (!empty($cartItems)): ?>
                                            <?php foreach ($cartItems as $item): ?>
                                                <?php 
                                                    $itemTotal = $item['price'] * $item['quantity']; 
                                                    $subtotalProduk += $itemTotal;

                                                    $itemOriginalTotal = (float)($item['original_price'] ?? $item['price']) * $item['quantity'];
                                                    $subtotalAwal += $itemOriginalTotal;
                                                    
                                                    $hasItemDiscount = isset($item['has_discount']) && $item['has_discount'] && isset($item['original_price']);

                                                    if ($hasItemDiscount) {
                                                        $totalDiskon += ($item['original_price'] - $item['price']) * $item['quantity'];
                                                    }
                                                ?>

                                                <tr>
                                                    <th scope="row">
                                                        <div class="d-flex align-items-center mt-2">
                                                            <img src="<?= base_url('assets/img/gambar/' . esc($item['image'])) ?>" class="img-fluid rounded-circle" alt="<?= esc($item['name']) ?>">
                                                        </div>
                                                    </th>

                                                    <td class="py-5">
                                                        <?= esc($item['name']) ?>
                                                        <?php if ($hasItemDiscount): ?>
                                                            <br><span class="badge bg-danger">Diskon</span>
                                                        <?php endif; ?>
                                                    </td>

                                                    <td class="py-5">
                                                        <?php if ($hasItemDiscount): ?>
                                                            <span class="text-decoration-line-through text-muted" style="font-size: 0.85rem;">Rp<?= number_format($item['original_price'], 0, ',', '.') ?></span><br>
                                                            <span class="text-danger fw-bold">Rp<?= number_format($item['price'], 0, ',', '.') ?></span>
                                                        <?php else: ?>
                                                            Rp<?= number_format($item['price'], 0, ',', '.') ?>
                                                        <?php endif; ?>
                                                    </td>

                                                    <td class="py-5"><?= esc($item['quantity']) ?></td>

                                                    <td class="py-5">Rp<?= number_format($itemTotal, 0, ',', '.') ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>

                                        <?php
                                            if ($voucherDiscount > $subtotalProduk) {
                                                $voucherDiscount = $subtotalProduk;
                                            }

                                            $subtotalSetelahVoucher = $subtotalProduk - $voucherDiscount;

                                            if ($subtotalSetelahVoucher < 0) {
                                                $subtotalSetelahVoucher = 0;
                                            }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                            
                            <div class="row g-4 justify-content-end mt-4">
                                <div class="col-sm-12">
                                    <div class="bg-light rounded">
                                        <div class="p-4" id="order_summary_box">
                                            <h1 class="display-6 mb-4">Total <span class="fw-normal">Pesanan</span></h1>

                                            <div class="d-flex justify-content-between mb-2">
                                                <h5 class="mb-0 me-4">Subtotal (Harga Awal):</h5>
                                                <p class="mb-0" id="display_subtotal_awal">Rp<?= number_format($subtotalAwal, 0, ',', '.') ?></p>
                                            </div>

                                            <div class="d-flex justify-content-between mb-4">
                                                <h5 class="mb-0 me-4">Subtotal (Setelah Diskon):</h5>
                                                <p class="mb-0" id="subtotal_produk">Rp<?= number_format($subtotalSetelahVoucher, 0, ',', '.') ?></p>
                                            </div>

                                            <div class="d-flex justify-content-between mb-4" id="discount_section" <?= ($totalDiskon > 0) ? '' : 'style="display:none;"' ?>>
                                                <h5 class="mb-0 me-4 text-success"><i class="fas fa-tag"></i> Diskon Produk:</h5>
                                                <p class="mb-0 text-success fw-bold" id="discount_display">-Rp<?= number_format($totalDiskon, 0, ',', '.') ?></p>
                                            </div>

                                            <div class="dist-voucher-section" id="voucher_container_section">
                                                <?php if (!empty($appliedVoucher) && $voucherDiscount > 0): ?>
                                                    <div class="d-flex justify-content-between mb-4 item-voucher-row">
                                                        <h5 class="mb-0 me-4 text-success">
                                                            <i class="fas fa-ticket-alt"></i> Voucher <?= esc($appliedVoucher['code'] ?? '') ?>:
                                                        </h5>
                                                        <p class="mb-0 text-success fw-bold" id="voucher_display_value">-Rp<?= number_format($voucherDiscount, 0, ',', '.') ?></p>
                                                    </div>
                                                <?php endif; ?>

                                                <?php if (!empty($appliedVoucher) && $voucherFreeShipping === 1): ?>
                                                    <div class="d-flex justify-content-between mb-4 item-voucher-row">
                                                        <h5 class="mb-0 me-4 text-success">
                                                            <i class="fas fa-ticket-alt"></i> Voucher <?= esc($appliedVoucher['code'] ?? '') ?>:
                                                        </h5>
                                                        <p class="mb-0 text-success fw-bold">Gratis Ongkir</p>
                                                    </div>
                                                <?php endif; ?>
                                            </div>

                                            <div class="d-flex justify-content-between">
                                                <h5 class="mb-0 me-4">Pengiriman:</h5>
                                                <p class="mb-0" id="shipping_cost_display">Rp0</p>
                                            </div>

                                            <p class="mb-0 text-end" id="shipping_note" style="font-size: 0.8rem;"></p>
                                        </div>
                                        
                                        <div class="py-4 mb-4 border-top border-bottom d-flex justify-content-between position-relative" id="total_row_container">
                                            <h5 class="mb-0 ps-4 me-4">TOTAL</h5>
                                            <p class="mb-0 pe-4" id="total_keseluruhan">Rp<?= number_format($subtotalSetelahVoucher, 0, ',', '.') ?></p>
                                        </div>
                                        
                                        <div id="distance_warning" class="alert alert-warning mx-4 mb-4" style="display: none;">
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-exclamation-triangle text-warning me-2"></i>
                                                <div class="flex-grow-1">
                                                    <h6 class="alert-heading mb-1">Pengiriman di atas 25km</h6>
                                                    <p class="mb-2 small">Lokasi Anda berada di luar area pengiriman normal. Untuk konfirmasi ongkir, silakan hubungi kami via WhatsApp.</p>
                                                </div>
                                            </div>

                                            <hr class="my-2">

                                            <button type="button" id="whatsapp_order_btn" class="btn btn-success btn-sm">
                                                <i class="fab fa-whatsapp me-1"></i> Pesan via WhatsApp
                                            </button>
                                        </div>
                                        
                                        <div id="payment_method_section" class="p-4">
                                            <h5 class="mb-3 fw-bold">Metode Pembayaran</h5>

                                            <div class="border-bottom py-3">
                                                <div class="form-check">
                                                    <input class="form-check-input payment-method" type="radio" name="metode_pembayaran" id="bankTransfer" value="Direct Bank Transfer" checked>
                                                    <label class="form-check-label fw-semibold" for="bankTransfer">Transfer Bank Langsung</label>
                                                </div>
                                            </div>

                                            <div class="border-bottom py-3">
                                                <div class="form-check">
                                                    <input class="form-check-input payment-method" type="radio" name="metode_pembayaran" id="qrisPayment" value="QRIS">
                                                    <label class="form-check-label fw-semibold" for="qrisPayment">QRIS</label>
                                                </div>
                                            </div>

                                            <div class="pt-4">
                                                <button type="submit" id="checkout_btn" class="btn btn-primary-custom w-100 text-uppercase fw-semibold py-2">Buat Pesanan</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
        <script src="<?= base_url('assets/lib/easing/easing.min.js')?>"></script>
        <script src="<?= base_url('assets/lib/waypoints/waypoints.min.js')?>"></script>
        <script src="<?= base_url('assets/lib/lightbox/js/lightbox.min.js')?>"></script>
        <script src="<?= base_url('assets/lib/owlcarousel/owl.carousel.min.js')?>"></script>
        <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
        <script src="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.js"></script>
        <script src="<?= base_url('assets/js/main.js')?>"></script>
    
        <script>
            $(document).ready(function () {
                let map;
                let marker;

                // REVISI MUTLAK: Teks kurung siku penutup yang berlebih ("];") sudah dihapus bersih dari baris ini.
                const cartItemsData = JSON.parse('<?= json_encode($cartItems ?? []) ?>');
                const subtotalAwal = parseFloat('<?= $subtotalAwal ?? 0 ?>');
                let subtotalProduk = parseFloat('<?= $subtotalSetelahVoucher ?? 0 ?>');
                const pickupLocationName = "<?= esc($pickupLocationName ?? 'Banjarbaru', 'js') ?>";

                function initMap(lat, lng) {
                    const mapContainer = document.getElementById("map-container");

                    if (!map) {
                        map = L.map(mapContainer).setView([lat, lng], 15);
                        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);

                        marker = L.marker([lat, lng], { draggable: true }).addTo(map);

                        marker.on('dragend', function(e) {
                            updateCoordinatesAndFetch(e.target.getLatLng().lat, e.target.getLatLng().lng);
                        });

                        map.on('click', function(e) {
                            marker.setLatLng(e.latlng);
                            updateCoordinatesAndFetch(e.latlng.lat, e.latlng.lng);
                        });

                        try {
                            const nominatimGeocoder = L.Control.Geocoder.nominatim();

                            const geocoderControl = L.Control.geocoder({
                                geocoder: nominatimGeocoder,
                                defaultMarkGeocode: false,
                                placeholder: 'Cari alamat...'
                            }).addTo(map);

                            geocoderControl.on('markgeocode', function(evt) {
                                const center = evt.geocode.center;

                                marker.setLatLng(center);
                                map.setView(center, 15);

                                if (evt.geocode && (evt.geocode.name || evt.geocode.html)) {
                                    $('#map-address-input').val(evt.geocode.name || evt.geocode.html);
                                }

                                updateCoordinatesAndFetch(center.lat, center.lng);
                            });
                        } catch (err) {
                            console.warn('Geocoder control unavailable', err);
                        }
                    } else {
                        map.setView([lat, lng], 15);
                        marker.setLatLng([lat, lng]);
                    }

                    updateCoordinatesAndFetch(lat, lng);
                }

                function updateCoordinatesAndFetch(lat, lng) {
                    $('#alamat-latitude').val(lat);
                    $('#alamat-longitude').val(lng);
                    fetchShippingCost();
                }

                function getCurrentLocation() {
                    if (navigator.geolocation) {
                        $('#shipping_note').text('Mencari lokasi Anda...');

                        navigator.geolocation.getCurrentPosition(function(position) {
                            initMap(position.coords.latitude, position.coords.longitude);
                        }, function() {
                            alert('Gagal mengakses lokasi. Silakan pilih lokasi di peta secara manual.');
                            initMap(-3.4398799, 114.8332947);
                        });
                    } else {
                        alert('Geolocation tidak didukung oleh browser ini.');
                        initMap(-3.4398799, 114.8332947);
                    }
                }

                function fetchShippingCost() {
                    const tipePengantaran = $('input[name="tipe_pengantaran"]:checked').val();
                    const tanggalPengantaran = $('#datetime-picker-checkout').val();

                    if (tipePengantaran !== 'Delivery') {
                        $('#shipping_cost_display').text('Rp0');
                        $('#shipping_note').text('Ambil sendiri di toko.');
                        $('#geocode_mismatch_input').val('0');
                        $('#alamat_detail_input').prop('required', false);
                        $('#distance_warning').hide();
                        $('#payment_method_section').show();
                        $('#checkout_btn').show();

                        triggerRecalculateOnly();
                        return;
                    }

                    const toLat = $('#alamat-latitude').val();
                    const toLon = $('#alamat-longitude').val();

                    if (!toLat || !toLon) {
                        triggerRecalculateOnly();
                        $('#shipping_note').text('Tentukan lokasi di peta untuk menghitung ongkir.');
                        return;
                    }

                    $('#shipping_note').text('Menghitung...');

                    $.ajax({
                        url: '/checkout/estimateShipping',
                        method: 'POST',
                        data: {
                            to_lat: toLat, 
                            to_lon: toLon,
                            address_text: $('#map-address-input').val(),
                            cart_items_json: JSON.stringify(cartItemsData),
                            subtotal_produk: subtotalAwal, 
                            subtotal_awal: subtotalAwal,
                            tanggal_pengantaran: tanggalPengantaran, 
                            '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
                        },
                        dataType: 'json',
                        success: function(response) {
                            if (response.status === 'success') {
                                const shippingCost = response.shipping_cost;
                                const isFreeShipping = response.free_shipping === 1;

                                $('#geocode_mismatch_input').val(response.geocode_mismatch === 1 ? '1' : '0');

                                if (response.rev_address) {
                                    $('#rev_address_json').val(JSON.stringify(response.rev_address));
                                    $('#rev_address_display').text(response.geocoded_display || '');
                                    $('#rev_address_badge').show();
                                } else {
                                    $('#rev_address_json').val('');
                                    $('#rev_address_badge').hide();
                                }

                                if (response.rev_address || response.geocoded_display) {
                                    const base = response.geocoded_display || '';
                                    $('#map-address-input').val(base);
                                }

                                $('#alamat_detail_input').prop('required', true);
                                
                                if (response.over_25km) {
                                    $('#distance_warning').show();
                                    $('#payment_method_section').hide();
                                    $('#checkout_btn').hide();
                                    $('#shipping_cost_display').text('Konsultasi via WA');
                                    $('#shipping_note').text(`Jarak: ${response.distance_km} km - Hubungi WhatsApp untuk konfirmasi ongkir.`);
                                    $('#total_keseluruhan').text('Konsultasi via WhatsApp');
                                } else {
                                    $('#distance_warning').hide();
                                    $('#payment_method_section').show();
                                    $('#checkout_btn').show();

                                    if (isFreeShipping) {
                                        $('#shipping_cost_display').text('Rp0 (Gratis)');
                                        $('#shipping_note').text('Selamat! Gratis ongkir sesuai ketentuan.');
                                    } else {
                                        $('#shipping_cost_display').text(`Rp${shippingCost.toLocaleString('id-ID')}`);
                                        $('#shipping_note').text(`Jarak: ${response.distance_km} km dari toko ${response.nearest_store}.`);
                                    }

                                    const computedDiscount = response.discount_amount;
                                    subtotalProduk = subtotalAwal - computedDiscount;

                                    $('#subtotal_produk').text('Rp' + subtotalProduk.toLocaleString('id-ID'));
                                    
                                    if (computedDiscount > 0) {
                                        $('#discount_display').text(`-Rp${computedDiscount.toLocaleString('id-ID')}`);
                                        $('#discount_section').show();
                                    } else {
                                        $('#discount_section').hide();
                                        $('.item-voucher-row').hide(); 
                                    }

                                    calculateTotal(computedDiscount, shippingCost);
                                }
                            } else {
                                $('#shipping_note').text('Gagal menghitung: ' + response.message);
                                calculateTotal(0, 0);
                            }
                        },
                        error: function() {
                            $('#shipping_note').text('Gagal terhubung ke server.');
                            calculateTotal(0, 0);
                        }
                    });
                }

                function triggerRecalculateOnly() {
                    const tanggalPengantaran = $('#datetime-picker-checkout').val();
                    $.ajax({
                        url: '/checkout/estimateShipping',
                        method: 'POST',
                        data: {
                            to_lat: -3.4398799, 
                            to_lon: 114.8332947, 
                            cart_items_json: JSON.stringify(cartItemsData),
                            subtotal_produk: subtotalAwal,
                            subtotal_awal: subtotalAwal,
                            tanggal_pengantaran: tanggalPengantaran,
                            '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
                        },
                        dataType: 'json',
                        success: function(response) {
                            if (response.status === 'success') {
                                const computedDiscount = response.discount_amount;
                                subtotalProduk = subtotalAwal - computedDiscount;
                                $('#subtotal_produk').text('Rp' + subtotalProduk.toLocaleString('id-ID'));
                                
                                if (computedDiscount > 0) {
                                    $('#discount_display').text(`-Rp${computedDiscount.toLocaleString('id-ID')}`);
                                    $('#discount_section').show();
                                    $('.item-voucher-row').show();
                                } else {
                                    $('#discount_section').hide();
                                    $('#voucher_container_section').html(''); 
                                }
                                calculateTotal(computedDiscount, 0);
                            }
                        }
                    });
                }

                function calculateTotal(discount, shippingCost) {
                    const total = subtotalProduk + shippingCost;
                    $('#total_keseluruhan').text('Rp' + total.toLocaleString('id-ID'));
                }

                function setInitialState() {
                    $('#delivery-address-container').hide();
                    $('#penerima-nomor-hp-container').hide();
                    $('#map-address-input, #penerima_nomor_hp_input').prop('required', false);

                    const oldTipe = "<?= old('tipe_pengantaran') ?>";

                    if (oldTipe) {
                        $(`input[name="tipe_pengantaran"][value="${oldTipe}"]`).prop('checked', true).trigger('change');
                    } else {
                        $('input[name="tipe_pengantaran"]').prop('checked', false);
                    }
                }
                
                $('input[name="tipe_pengantaran"]').on('change', function() {
                    const isDelivery = $(this).val() === 'Delivery';

                    $('#delivery-address-container').toggle(isDelivery);
                    $('#penerima-nomor-hp-container').toggle(isDelivery);
                    $('#map-address-input, #penerima_nomor_hp_input').prop('required', isDelivery);

                    if (!isDelivery) {
                        $('#alamat-latitude').val('');
                        $('#alamat-longitude').val('');
                        $('#map-address-input').val('');
                        $('#alamat_detail_input').val('').prop('required', false);
                        $('#rev_address_json').val('');
                        $('#rev_address_badge').hide();
                    }

                    if (isDelivery) {
                        if (!map) getCurrentLocation();
                        setTimeout(function() {
                            if (map) map.invalidateSize();
                        }, 10);
                    }

                    if ($(this).val() === 'Self-Pickup') {
                        $('#pickup_location_input').val(pickupLocationName);
                    } else {
                        $('#pickup_location_input').val('');
                    }

                    fetchShippingCost();
                });

                $('#get-current-location-btn').on('click', getCurrentLocation);

                function searchAddressFromInput() {
                    const q = $('#map-address-input').val().trim();

                    if (!q) return;

                    $('#shipping_note').text('Mencari alamat...');

                    $.get('https://nominatim.openstreetmap.org/search', {
                        format: 'json',
                        limit: 5,
                        q: q
                    }, function(results) {
                        if (!results || results.length === 0) {
                            $('#shipping_note').text('Alamat tidak ditemukan. Coba kata kunci lain.');
                            return;
                        }

                        const res = results[0];
                        const lat = parseFloat(res.lat);
                        const lon = parseFloat(res.lon);

                        if (marker) {
                            marker.setLatLng([lat, lon]);
                        } else {
                            initMap(lat, lon);
                        }

                        if (map) map.setView([lat, lon], 15);

                        updateCoordinatesAndFetch(lat, lon);

                        $('#shipping_note').text('Alamat ditemukan: ' + (res.display_name || '')); 
                    }, 'json').fail(function() {
                        $('#shipping_note').text('Gagal mencari alamat.');
                    });
                }

                $('#map-address-input').on('keydown', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        searchAddressFromInput();
                    }
                });

                $('#map-address-input').on('blur', function() {
                    setTimeout(searchAddressFromInput, 200);
                });

                function validateForm() {
                    let isValid = true;
                    let errorMessages = [];

                    $('.form-control').removeClass('is-invalid');
                    $('.invalid-feedback').remove();

                    const namaDepan = $('input[name="nama_depan"]').val().trim();

                    if (!namaDepan) {
                        addFieldError('input[name="nama_depan"]', 'Nama depan harus diisi');
                        errorMessages.push('Nama depan harus diisi');
                        isValid = false;
                    }

                    const nomorPemesan = $('input[name="nomor_pemesan"]').val().trim();

                    if (!nomorPemesan) {
                        addFieldError('input[name="nomor_pemesan"]', 'Nomor telepon pemesan harus diisi');
                        errorMessages.push('Nomor telepon pemesan harus diisi');
                        isValid = false;
                    }

                    const tanggalPengantaran = $('#datetime-picker-checkout').val();

                    if (!tanggalPengantaran) {
                        addFieldError('#datetime-picker-checkout', 'Tanggal and jam pengantaran harus dipilih');
                        errorMessages.push('Tanggal and jam pengantaran harus dipilih');
                        isValid = false;
                    } else {
                        const selectedDate = new Date(tanggalPengantaran);
                        const now = new Date();
                        const minAllowedDate = new Date();

                        if (now.getHours() < 22) {
                            minAllowedDate.setHours(now.getHours() + 2, now.getMinutes());
                        } else {
                            minAllowedDate.setDate(now.getDate() + 1);
                            minAllowedDate.setHours(10, 0, 0, 0);
                        }

                        if (selectedDate < minAllowedDate) {
                            addFieldError('#datetime-picker-checkout', 'Waktu pengantaran tidak valid. Minimal 2 jam dari sekarang');
                            errorMessages.push('Waktu pengantaran tidak valid');
                            isValid = false;
                        }
                    }

                    const tipePengantaran = $('input[name="tipe_pengantaran"]:checked').val();

                    if (!tipePengantaran) {
                        showValidationAlert('Mohon pilih tipe pengantaran');
                        errorMessages.push('Tipe pengantaran harus dipilih');
                        isValid = false;
                    }

                    if (tipePengantaran === 'Delivery') {
                        const penerimaHP = $('#penerima_nomor_hp_input').val().trim();

                        if (!penerimaHP) {
                            addFieldError('#penerima_nomor_hp_input', 'Nomor telepon penerima harus diisi');
                            errorMessages.push('Nomor telepon penerima harus diisi');
                            isValid = false;
                        }

                        const alamatTeks = $('#map-address-input').val().trim();

                        if (!alamatTeks) {
                            addFieldError('#map-address-input', 'Alamat pengiriman harus diisi');
                            errorMessages.push('Alamat pengiriman harus diisi');
                            isValid = false;
                        }

                        const alamatDetail = $('#alamat_detail_input').val().trim();

                        if (!alamatDetail) {
                            addFieldError('#alamat_detail_input', 'Detail alamat harus diisi');
                            errorMessages.push('Detail alamat harus diisi');
                            isValid = false;
                        }

                        const lat = $('#alamat-latitude').val();
                        const lon = $('#alamat-longitude').val();

                        if (!lat || !lon) {
                            showValidationAlert('Mohon tentukan lokasi pengiriman di peta');
                            errorMessages.push('Lokasi pengiriman harus dipilih di peta');
                            isValid = false;
                        }
                    }

                    const metodePembayaran = $('input[name="metode_pembayaran"]:checked').val();

                    if (!metodePembayaran && $('#payment_method_section').is(':visible')) {
                        showValidationAlert('Metode pembayaran harus dipilih');
                        errorMessages.push('Metode pembayaran harus dipilih');
                        isValid = false;
                    }

                    return { isValid, errorMessages };
                }

                function addFieldError(selector, message) {
                    let field = (typeof selector === 'string') ? $(selector) : selector;

                    field.addClass('is-invalid');
                    field.after(`<div class="invalid-feedback">${message}</div>`);
                }

                function showValidationAlert(message) {
                    let alertDiv = $('#mobile-validation-alert');

                    if (alertDiv.length === 0) {
                        alertDiv = $(`
                            <div id="mobile-validation-alert" class="alert alert-danger alert-dismissible fade show position-fixed" 
                                 style="top: 70px; left: 15px; right: 15px; z-index: 9999; max-height: 300px; overflow-y: auto;">
                                <strong>Validasi Gagal!</strong>
                                <div id="mobile-validation-content"></div>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        `);

                        $('body').append(alertDiv);
                    }

                    $('#mobile-validation-content').html(`<br>${message}`);
                    alertDiv.show();

                    $('html, body').animate({
                        scrollTop: 0
                    }, 300);

                    setTimeout(() => {
                        alertDiv.fadeOut();
                    }, 5000);
                }

                $('#checkoutForm').on('submit', function(e){
                    e.preventDefault();

                    const validation = validateForm();

                    if (!validation.isValid) {
                        const errorSummary = validation.errorMessages.join('<br>• ');
                        showValidationAlert(`• ${errorSummary}`);

                        const firstInvalidField = $('.is-invalid').first();

                        if (firstInvalidField.length > 0) {
                            firstInvalidField.focus();

                            $('html, body').animate({
                                scrollTop: firstInvalidField.offset().top - 100
                            }, 300);
                        }

                        return false;
                    }

                    const lat = $('#alamat-latitude').val();
                    const lon = $('#alamat-longitude').val();

                    if (lat && lon && !$('#rev_address_json').val()){
                        $('#shipping_note').text('Mengonfirmasi alamat...');

                        $.ajax({
                            url: '/checkout/estimateShipping',
                            method: 'POST',
                            data: {
                                to_lat: lat,
                                to_lon: lon,
                                cart_items_json: JSON.stringify(cartItemsData),
                                subtotal_produk: subtotalAwal,
                                subtotal_awal: subtotalAwal,
                                tanggal_pengantaran: $('#datetime-picker-checkout').val(),
                                address_text: $('#map-address-input').val(),
                                '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
                            },
                            dataType: 'json',
                            success: function(resp){
                                if (resp && resp.status === 'success' && resp.rev_address){
                                    $('#rev_address_json').val(JSON.stringify(resp.rev_address));
                                    $('#rev_address_display').text(resp.geocoded_display || '');
                                    $('#rev_address_badge').show();
                                }

                                $('#checkoutForm')[0].submit();
                            },
                            error: function(){
                                $('#checkoutForm')[0].submit();
                            }
                        });

                        return false;
                    }

                    this.submit();
                });

                function setMinDateTime() {
                    const now = new Date();
                    let minDate = new Date();
                    const pad = (num) => num.toString().padStart(2, '0');

                    if (now.getHours() < 22) {
                        minDate.setHours(now.getHours() + 2, now.getMinutes());
                    } else {
                        minDate.setDate(now.getDate() + 1);
                        minDate.setHours(10, 0, 0, 0);
                    }

                    const year = minDate.getFullYear();
                    const month = pad(minDate.getMonth() + 1);
                    const day = pad(minDate.getDate());
                    const hours = pad(minDate.getHours());
                    const minutes = pad(minDate.getMinutes());
                    const minDateTimeString = `${year}-${month}-${day}T${hours}:${minutes}`;

                    $('#datetime-picker-checkout').attr('min', minDateTimeString);
                    
                    if (!$('#datetime-picker-checkout').val()) {
                        $('#datetime-picker-checkout').val(minDateTimeString);
                    }
                }

                setInitialState();
                setMinDateTime();
                fetchShippingCost();
                
                $('input[required], select[required], textarea[required]').on('blur change', function() {
                    const field = $(this);
                    const value = field.val().trim();

                    field.removeClass('is-invalid');
                    field.siblings('.invalid-feedback').remove();

                    if (!value && field.prop('required')) {
                        const fieldName = field.attr('name') || field.attr('id');
                        let errorMessage = `Field ini harus diisi`;

                        if (fieldName === 'nama_depan') {
                            errorMessage = 'Nama depan harus diisi';
                        } else if (fieldName === 'nomor_pemesan') {
                            errorMessage = 'Nomor telepon pemesan harus diisi';
                        } else if (fieldName === 'tanggal_pengantaran') {
                            errorMessage = 'Tanggal dan jam pengantaran harus dipilih';
                        }

                        addFieldError(field, errorMessage);
                    }
                });
                
                $('#datetime-picker-checkout').on('change', function() {
                    const tanggalSelected = this.value; 

                    $('#box-bonus-hadiah-co').remove();

                    if (!tanggalSelected) return;

                    $.ajax({
                        url: '/checkout/checkBonusAjax',
                        method: 'POST',
                        data: { 
                            tanggal_pengantaran: tanggalSelected,
                            cart_items: JSON.stringify(cartItemsData), 
                            subtotal_produk: subtotalProduk,
                            '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
                        },
                        dataType: 'json',
                        success: function(response) {
                            if (response.status === 'success' && response.bonuses.length > 0) {
                                let htmlBonus = `
                                    <div id="box-bonus-hadiah-co" class="alert alert-success border-success mx-4 my-3 p-3 shadow-sm d-flex align-items-center">
                                        <div class="me-3">
                                            <i class="fas fa-gift fa-2x text-success"></i>
                                        </div>
                                        <div>
                                            <strong class="text-success" style="font-size: 0.95rem;">🎉 Bonus Hadiah Terdeteksi:</strong>
                                            <ul class="mb-0 ps-3 mt-1 text-dark" style="font-size: 0.9rem; list-style-type: square;">`;

                                response.bonuses.forEach(function(b) {
                                    htmlBonus += `<li><strong>${b.bonus_item_name}</strong> (${b.total_pcs} Pcs)</li>`;
                                });
                                
                                htmlBonus += `</ul>
                                            <small class="text-muted d-block mt-1" style="font-size: 0.75rem;">*Otomatis diselipkan ke paket buket bunga saat serah terima.</small>
                                        </div>
                                    </div>`;

                                $('#total_row_container').after(htmlBonus);
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('[Bonus Ajax Error] Gagal memeriksa status promo:', error);
                        }
                    });
                });

                $('#whatsapp_order_btn').on('click', function() {
                    const validation = validateForm();

                    if (!validation.isValid) {
                        const errorSummary = validation.errorMessages.join('<br>• ');
                        showValidationAlert(`• ${errorSummary}`);
                        return;
                    }

                    $(this).prop('disabled', true).text('Memproses...');

                    $.ajax({
                        url: '/checkout/generate-whatsapp',
                        method: 'POST',
                        data: {
                            nama_depan: $('input[name="nama_depan"]').val(),
                            nama_belakang: $('input[name="nama_belakang"]').val(),
                            nomor_pemesan: $('input[name="nomor_pemesan"]').val(),
                            alamat_pengiriman_teks: $('#map-address-input').val(),
                            alamat_detail: $('#alamat_detail_input').val(),
                            tanggal_pengantaran: $('input[name="tanggal_pengantaran"]').val(),
                            catatan_penerima: $('textarea[name="catatan_penerima"]').val(),
                            '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
                        },
                        dataType: 'json',
                        success: function(response) {
                            if (response.status === 'success') {
                                window.open(response.whatsapp_url, '_blank');
                            } else {
                                showValidationAlert('Gagal membuat pesan WhatsApp: ' + response.message);
                            }
                        },
                        error: function() {
                            showValidationAlert('Terjadi kesalahan. Silakan coba lagi.');
                        },
                        complete: function() {
                            $('#whatsapp_order_btn').prop('disabled', false).html('<i class="fab fa-whatsapp me-1"></i> Pesan via WhatsApp');
                        }
                    });
                });
            });
        </script>
    </body>
</html>
<?= $this->endSection() ?>