<!DOCTYPE html>
<?= $this->extend('templates/main_layout') ?>
<html lang="en">

<?= $this->section('title') ?>
Shop - <?= esc($store['name']) ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

    <head>
        <meta charset="utf-8">
        <title>Keranjang Belanja - JS Florist</title>
        <meta content="width=device-width, initial-scale=1.0" name="viewport">
        <meta content="" name="keywords">
        <meta content="" name="description">

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600&family=Raleway:wght@600;800&display=swap" rel="stylesheet">

        <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css"/>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">

        <link href="<?= base_url('assets/lib/lightbox/css/lightbox.min.css')?>" rel="stylesheet">
        <link href="<?= base_url('assets/lib/owlcarousel/assets/owl.carousel.min.css')?>" rel="stylesheet">

        <link href="<?= base_url('assets/css/bootstrap.min.css') ?>" rel="stylesheet">
        <link href="<?= base_url('assets/css/style.css') ?>" rel="stylesheet">

        <style>
            .bg-primary {
                background-color: #d09c4c !important;
            }

            .text-primary {
                color: #d09c4c !important;
            }

            .border-primary {
                border-color: #d09c4c !important;
            }

            .bg-secondary {
                background-color: #ebd4b6 !important;
            }

            .text-secondary {
                color: #ebd4b6 !important;
            }

            .border-secondary {
                border-color: #ebd4b6 !important;
            }

            .featurs-item .featurs-icon::after {
                border-top-color: #b0853e !important;
            }

            .table img {
                width: 80px;
                height: 80px;
                object-fit: cover;
                border-radius: 50%;
            }

            .input-group.quantity .btn-sm {
                width: 30px;
                height: 30px;
                display: flex;
                align-items: center;
                justify-content: center;
            }

            .input-group.quantity .form-control-sm {
                height: 30px;
            }

            .table button.btn.btn-md.rounded-circle.bg-light.border {
                background-color: #f8f9fa !important;
                border-color: #e2e3e5 !important;
            }

            .table button.btn.btn-md.rounded-circle.bg-light.border i.fa-times {
                color: #dc3545 !important;
            }
        </style>
    </head>

    <body>

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
                            <span id="search-icon-1" class="input-group-text p-3">
                                <i class="fa fa-search"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="container-fluid page-header py-5">
            <h1 class="text-center text-white display-6">Keranjang Belanja</h1>
            <ol class="breadcrumb justify-content-center mb-0">
                <li class="breadcrumb-item"><a href="/dashboard">Home</a></li>
                <li class="breadcrumb-item"><a href="#">Pages</a></li>
                <li class="breadcrumb-item active text-white">Keranjang</li>
            </ol>
        </div>

        <div class="container-fluid py-5">
            <div class="container py-5">

                <?php
                    $grandTotal = 0;
                    $appliedVoucher = $appliedVoucher ?? null;
                    $voucherDiscount = !empty($appliedVoucher) ? (float)($appliedVoucher['discount_amount'] ?? 0) : 0;
                ?>

                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th scope="col">Produk</th>
                                <th scope="col">Nama</th>
                                <th scope="col">Harga</th>
                                <th scope="col">Kuantitas</th>
                                <th scope="col">Total</th>
                                <th scope="col">Aksi</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php if (!empty($cartItems)): ?>
                                <?php foreach ($cartItems as $item): ?>
                                    <?php
                                        $itemPrice = (float)($item['price'] ?? 0);
                                        $itemQty = (int)($item['quantity'] ?? 1);
                                        $itemTotal = $itemPrice * $itemQty;
                                        $grandTotal += $itemTotal;

                                        $cartId = $item['cart_id'] ?? ($item['id'] ?? '');
                                        $itemImage = $item['image'] ?? '';
                                        $itemName = $item['name'] ?? 'Produk';
                                    ?>

                                    <tr data-product-id="<?= esc($cartId) ?>">
                                        <th scope="row">
                                            <div class="d-flex align-items-center">
                                                <img src="<?= base_url('assets/img/gambar/' . esc($itemImage)) ?>"
                                                     class="img-fluid me-5 rounded-circle"
                                                     alt="<?= esc($itemName) ?>">
                                            </div>
                                        </th>

                                        <td>
                                            <p class="mb-0 mt-4"><?= esc($itemName) ?></p>

                                            <?php if (!empty($item['options']['custom_details'])): ?>
                                                <?php
                                                    $details = json_decode($item['options']['custom_details'], true);

                                                    if (!is_array($details)) {
                                                        $details = [];
                                                    }
                                                ?>

                                                <?php if (!empty($details)): ?>
                                                    <div class="mt-2 text-muted small">
                                                        <?php if (!empty($details['jenis_item'])): ?>
                                                            <div><strong>Jenis:</strong> <?= esc($details['jenis_item']) ?></div>
                                                        <?php endif; ?>

                                                        <?php if (!empty($details['jumlah_item'])): ?>
                                                            <div><strong>Jumlah:</strong> <?= esc($details['jumlah_item']) ?></div>
                                                        <?php endif; ?>

                                                        <?php if (!empty($details['bunga']) && is_array($details['bunga'])): ?>
                                                            <div><strong>Bunga:</strong> <?= esc(implode(', ', $details['bunga'])) ?></div>
                                                        <?php endif; ?>
                                                    </div>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </td>

                                        <td>
                                            <p class="mb-0 mt-4">Rp<?= number_format($itemPrice, 0, ',', '.') ?></p>
                                        </td>

                                        <td>
                                            <div class="input-group quantity mt-4" style="width: 100px;">
                                                <div class="input-group-btn">
                                                    <button class="btn btn-sm btn-minus rounded-circle bg-light border quantity-btn" data-action="minus">
                                                        <i class="fa fa-minus"></i>
                                                    </button>
                                                </div>

                                                <input type="text"
                                                       class="form-control form-control-sm text-center border-0 quantity-input"
                                                       value="<?= esc($itemQty) ?>"
                                                       min="1">

                                                <div class="input-group-btn">
                                                    <button class="btn btn-sm btn-plus rounded-circle bg-light border quantity-btn" data-action="plus">
                                                        <i class="fa fa-plus"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </td>

                                        <td>
                                            <p class="mb-0 mt-4">Rp<?= number_format($itemTotal, 0, ',', '.') ?></p>
                                        </td>

                                        <td>
                                            <button class="btn btn-md rounded-circle bg-light border mt-4 remove-from-cart-btn">
                                                <i class="fa fa-times text-danger"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center py-4">
                                        Keranjang belanja Anda kosong.
                                        <a href="/dashboard">Mulai Belanja Sekarang!</a>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>

                        <?php
                            if ($voucherDiscount > $grandTotal) {
                                $voucherDiscount = $grandTotal;
                            }

                            $finalTotal = $grandTotal - $voucherDiscount;

                            if ($finalTotal < 0) {
                                $finalTotal = 0;
                            }
                        ?>

                        <tfoot>
                            <tr>
                                <th colspan="4" class="text-end">Total Belanja:</th>
                                <th class="text-start">Rp<?= number_format($grandTotal, 0, ',', '.') ?></th>
                                <th></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div class="mt-5">
                    <?php if (!empty($appliedVoucher)): ?>
                        <div class="alert alert-success">
                            Voucher <strong><?= esc($appliedVoucher['code'] ?? '-') ?></strong> berhasil diterapkan.

                            <?php if (!empty($appliedVoucher['free_shipping'])): ?>
                                <br>Benefit: Gratis Ongkir
                            <?php else: ?>
                                <br>Potongan: Rp<?= number_format($voucherDiscount, 0, ',', '.') ?>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <input type="text"
                               id="voucher_code"
                               name="voucher_code"
                               class="border-0 border-bottom rounded me-5 py-3 mb-4"
                               placeholder="Kode Kupon">

                        <button id="applyVoucherBtn"
                                class="btn border-secondary rounded-pill px-4 py-3 text-primary"
                                type="button">
                            Terapkan Kupon
                        </button>

                        <div id="voucherMessage" class="mt-2"></div>
                    <?php endif; ?>
                </div>

                <div class="row g-4 justify-content-end">
                    <div class="col-8"></div>

                    <div class="col-sm-8 col-md-7 col-lg-6 col-xl-4">
                        <div class="bg-light rounded">
                            <div class="p-4">
                                <h1 class="display-6 mb-4">
                                    Ringkasan <span class="fw-normal">Belanja</span>
                                </h1>

                                <div class="d-flex justify-content-between mb-4">
                                    <h5 class="mb-0 me-4">Subtotal:</h5>
                                    <p class="mb-0">Rp<?= number_format($grandTotal, 0, ',', '.') ?></p>
                                </div>

                                <?php if (!empty($appliedVoucher) && $voucherDiscount > 0): ?>
                                    <div class="d-flex justify-content-between mb-4">
                                        <h5 class="mb-0 me-4">Voucher:</h5>
                                        <p class="mb-0 text-success">- Rp<?= number_format($voucherDiscount, 0, ',', '.') ?></p>
                                    </div>
                                <?php endif; ?>

                                <?php if (!empty($appliedVoucher) && !empty($appliedVoucher['free_shipping'])): ?>
                                    <div class="d-flex justify-content-between mb-4">
                                        <h5 class="mb-0 me-4">Voucher:</h5>
                                        <p class="mb-0 text-success">Gratis Ongkir</p>
                                    </div>
                                <?php endif; ?>

                                <div class="d-flex justify-content-between">
                                    <h5 class="mb-0 me-4">Pengiriman:</h5>
                                    <div>
                                        <p class="mb-0">Akan dihitung saat Checkout</p>
                                    </div>
                                </div>
                            </div>

                            <div class="py-4 mb-4 border-top border-bottom d-flex justify-content-between">
                                <h5 class="mb-0 ps-4 me-4">Total:</h5>
                                <p class="mb-0 pe-4">Rp<?= number_format($finalTotal, 0, ',', '.') ?></p>
                            </div>

                            <a href="/checkout"
                               class="btn border-secondary rounded-pill px-4 py-3 text-primary text-uppercase mb-4 ms-4"
                               type="button">
                                Lanjutkan ke Checkout
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <a href="#" class="btn btn-primary border-3 border-primary rounded-circle back-to-top">
            <i class="fa fa-arrow-up"></i>
        </a>

        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
        <script src="<?= base_url('assets/lib/easing/easing.min.js')?>"></script>
        <script src="<?= base_url('assets/lib/waypoints/waypoints.min.js')?>"></script>
        <script src="<?= base_url('assets/lib/lightbox/js/lightbox.min.js')?>"></script>
        <script src="<?= base_url('assets/lib/owlcarousel/owl.carousel.min.js')?>"></script>
        <script src="<?= base_url('assets/js/main.js')?>"></script>

        <script>
            $(document).ready(function() {

                $(document).on('click', '.remove-from-cart-btn', function() {
                    var productId = $(this).closest('tr').data('product-id');

                    if (confirm('Apakah Anda yakin ingin menghapus produk ini dari keranjang?')) {
                        $.ajax({
                            url: '/cart/remove/' + productId,
                            method: 'POST',
                            dataType: 'json',
                            success: function(response) {
                                if (response.status === 'success') {
                                    alert(response.message);
                                    location.reload();
                                } else {
                                    alert('Gagal menghapus produk: ' + response.message);
                                }
                            },
                            error: function(xhr, status, error) {
                                console.error("AJAX Error:", status, error, xhr.responseText);
                                alert('Terjadi kesalahan saat menghapus produk.');
                            }
                        });
                    }
                });

                $(document).on('click', '.quantity-btn', function() {
                    var $row = $(this).closest('tr');
                    var productId = $row.data('product-id');
                    var $qtyInput = $row.find('.quantity-input');
                    var currentQuantity = parseInt($qtyInput.val());
                    var action = $(this).data('action');

                    var newQuantity = currentQuantity;

                    if (action === 'minus') {
                        newQuantity = Math.max(1, currentQuantity - 1);
                    } else if (action === 'plus') {
                        newQuantity = currentQuantity + 1;
                    }

                    $qtyInput.val(newQuantity);

                    if (newQuantity !== currentQuantity) {
                        sendQuantityUpdate(productId, newQuantity);
                    }
                });

                $(document).on('change', '.quantity-input', function() {
                    var $row = $(this).closest('tr');
                    var productId = $row.data('product-id');
                    var newQuantity = parseInt($(this).val());

                    if (isNaN(newQuantity) || newQuantity < 1) {
                        alert('Kuantitas harus angka positif.');
                        $(this).val(1);
                        return;
                    }

                    sendQuantityUpdate(productId, newQuantity);
                });

                function sendQuantityUpdate(productId, newQuantity) {
                    $.ajax({
                        url: '/cart/update',
                        method: 'POST',
                        data: {
                            product_id: productId,
                            quantity: newQuantity
                        },
                        dataType: 'json',
                        success: function(response) {
                            if (response.status === 'success') {
                                location.reload();
                            } else {
                                alert('Gagal memperbarui kuantitas: ' + response.message);
                                location.reload();
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error("AJAX Error:", status, error, xhr.responseText);
                            alert('Terjadi kesalahan saat memperbarui kuantitas.');
                            location.reload();
                        }
                    });
                }

                $(document).on('click', '#applyVoucherBtn', function() {
                    var voucherCode = $('#voucher_code').val().trim();

                    if (voucherCode === '') {
                        $('#voucherMessage').html('<span class="text-danger">Kode kupon wajib diisi.</span>');
                        return;
                    }

                    $('#applyVoucherBtn').prop('disabled', true).text('Memproses...');

                    $.ajax({
                        url: '/cart/apply-voucher',
                        method: 'POST',
                        data: {
                            voucher_code: voucherCode
                        },
                        dataType: 'json',
                        success: function(response) {
                            if (response.status === 'success') {
                                $('#voucherMessage').html('<span class="text-success">' + response.message + '</span>');

                                setTimeout(function() {
                                    location.reload();
                                }, 800);
                            } else {
                                $('#voucherMessage').html('<span class="text-danger">' + response.message + '</span>');
                                $('#applyVoucherBtn').prop('disabled', false).text('Terapkan Kupon');
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error("AJAX Error:", status, error, xhr.responseText);
                            $('#voucherMessage').html('<span class="text-danger">Terjadi kesalahan saat menerapkan kupon.</span>');
                            $('#applyVoucherBtn').prop('disabled', false).text('Terapkan Kupon');
                        }
                    });
                });

            });
        </script>
    </body>
</html>
<?= $this->endSection() ?>