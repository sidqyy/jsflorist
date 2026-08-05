// app/Views/custom_checkout.php

<!DOCTYPE html>

<html lang="en">



<head>

    <meta charset="utf-8">

    <title>Konfirmasi Permintaan Kustom - JS Florist</title>

    <meta content="width=device-width, initial-scale=1.0" name="viewport">

    <link href="<?= base_url('assets/css/bootstrap.min.css') ?>" rel="stylesheet">

    <link href="<?= base_url('assets/css/style.css') ?>" rel="stylesheet">

    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css"/>

    <style>

        .bg-primary { background-color: #d09c4c !important; }

        .text-primary { color: #d09c4c !important; }

        .border-primary { border-color: #d09c4c !important; }

        .bg-secondary { background-color: #ebd4b6 !important; }

        .border-secondary { border-color: #ebd4b6 !important; }

        .summary-box { background-color: #f8f9fa; border: 1px solid #dee2e6; border-radius: 10px; }

        .summary-box h4 { border-bottom: 1px solid #dee2e6; padding-bottom: 0.5rem; margin-bottom: 1rem; }

    </style>

</head>



<body>

    <?= $this->include('templates/navbar') ?>



    <div class="container-fluid page-header py-5" style="background: linear-gradient(rgba(0, 0, 0, 0.3), rgba(0, 0, 0, 0.3)), url(<?= base_url('assets/img/page-header.webp') ?>) center center no-repeat; background-size: cover;">

        <h1 class="text-center text-white display-6">Konfirmasi Permintaan</h1>

        <ol class="breadcrumb justify-content-center mb-0">

            <li class="breadcrumb-item"><a href="<?= site_url('dashboard') ?>">Home</a></li>

            <li class="breadcrumb-item"><a href="<?= site_url('shop') ?>">Shop</a></li>

            <li class="breadcrumb-item active text-white">Permintaan Kustom</li>

        </ol>

    </div>



    <div class="container-fluid py-5">

        <div class="container py-5">

            <h1 class="mb-4">Konfirmasi Detail Permintaan Anda</h1>



            <?php if (session()->getFlashdata('errors')): ?>

                <div class="alert alert-danger">

                    <ul>

                        <?php foreach (session()->getFlashdata('errors') as $error): ?>

                            <li><?= esc($error) ?></li>

                        <?php endforeach; ?>

                    </ul>

                </div>

            <?php endif; ?>



            <form action="<?= site_url('custom/save') ?>" method="POST">

                <?= csrf_field() ?>



                <input type="hidden" name="product_id" value="<?= esc($product['product_id']) ?>">

                <?php
                $isCustomProduct = in_array($product['product_id'], ['PRDKCUST', 'PRDKCUST1'], true);
                // Hanya PRDKCUST/PRDKCUST1 yang akan sampai di sini

                if ($isCustomProduct): ?>

                    <input type="hidden" name="custom_details[jenis_item]" value="<?= esc($customDetails['jenis_item']) ?>">

                    <input type="hidden" name="custom_details[jumlah_item]" value="<?= esc($customDetails['jumlah_item']) ?>">

                    <?php if (isset($customDetails['bunga']) && is_array($customDetails['bunga'])): ?>

                        <?php foreach ($customDetails['bunga'] as $bunga): ?>

                            <input type="hidden" name="custom_details[bunga][]" value="<?= esc($bunga) ?>">

                        <?php endforeach; ?>

                    <?php endif; ?>

                <?php endif; ?>



                <div class="row g-5">

                    <div class="col-md-12 col-lg-7">

                        <?php // Informasi penting ini hanya untuk PRDKCUST/PRDKCUST1 sekarang

                        if ($isCustomProduct): ?>

                        <div class="alert alert-info" role="alert">

                            <h4 class="alert-heading"><i class="fa fa-info-circle"></i> Informasi Penting!</h4>

                            <p>Untuk item kustom (seperti snack, mainan, dll.), mohon untuk diantarkan ke toko kami **maksimal 2 jam sebelum** waktu pengambilan/pengantaran yang Anda pilih.</p>

                        </div>

                        <?php endif; ?>



                        <h4 class="mb-3">Detail Pengiriman & Kontak</h4>

                        <div class="form-item">

                            <label class="form-label my-3">Nama Anda<sup>*</sup></label>

                            <input type="text" class="form-control" name="nama_pemesan" value="<?= old('nama_pemesan', $loggedInUser['nama_depan'] ?? '') ?>" required>

                        </div>

                        <div class="form-item">

                            <label class="form-label my-3">Nomor Telepon Anda<sup>*</sup></label>

                            <input type="tel" class="form-control" name="nomor_pemesan" value="<?= old('nomor_pemesan', $loggedInUser['nomor_hp'] ?? '') ?>" required>

                        </div>

                        <div class="form-item">

                            <label class="form-label my-3">Tanggal & Jam Pengantaran yang Diinginkan<sup>*</sup></label>

                            <small class="d-block text-muted mb-1">Waktu yang ditampilkan dan dipilih adalah Waktu Indonesia Tengah (WITA).</small>

                            <input type="datetime-local" class="form-control" id="datetime-picker-custom" name="tanggal_pengantaran" value="<?= old('tanggal_pengantaran') ?>" required>

                        </div>

                        <div class="form-item">

                            <label class="form-label my-3">Catatan Tambahan / Isi Kartu Ucapan (Opsional)</label>

                            <textarea name="additional_notes" class="form-control" spellcheck="false" cols="30" rows="5" placeholder="Tulis pesan atau catatan khusus lainnya."><?= old('additional_notes') ?></textarea>

                        </div>

                    </div>



                    <div class="col-md-12 col-lg-5">

                        <div class="p-4 summary-box">

                            <h4>Ringkasan Permintaan</h4>

                            <div class="d-flex justify-content-between mb-3">

                                <h5 class="mb-0 me-4">Produk:</h5>

                                <p class="mb-0"><?= esc($product['nama_produk']) ?></p>

                            </div>



                            <?php // Hanya PRDKCUST/PRDKCUST1 yang akan sampai di sini

                            if ($isCustomProduct): ?>

                                <div class="d-flex justify-content-between mb-3">

                                    <h5 class="mb-0 me-4">Jenis Item:</h5>

                                    <p class="mb-0 text-end"><?= esc($customDetails['jenis_item']) ?></p>

                                </div>

                                <div class="d-flex justify-content-between mb-3">

                                    <h5 class="mb-0 me-4">Jumlah Item:</h5>

                                    <p class="mb-0 text-end"><?= esc($customDetails['jumlah_item']) ?></p>

                                </div>

                                <?php if (isset($customDetails['bunga']) && !empty($customDetails['bunga'])): ?>

                                <div class="d-flex justify-content-between mb-4">

                                    <h5 class="mb-0 me-4">Request Bunga:</h5>

                                    <p class="mb-0 text-end"><?= esc(implode(', ', $customDetails['bunga'])) ?></p>

                                </div>

                                <?php endif; ?>

                            <?php endif; ?>



                            <div class="py-4 border-top border-bottom">

                                <p class="text-muted">Harga akan diinformasikan oleh admin setelah permintaan Anda direview. Anda akan dihubungi melalui nomor telepon yang Anda berikan.</p>

                            </div>



                            <button type="submit" class="btn border-secondary rounded-pill px-4 py-3 text-primary text-uppercase w-100 mt-4">Kirim Permintaan Saya</button>

                        </div>

                    </div>

                </div>

            </form>

        </div>

    </div>



    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>

    <script src="<?= base_url('assets/js/main.js')?>"></script>

    <script>

        document.addEventListener('DOMContentLoaded', function() {

            const dateTimePicker = document.getElementById('datetime-picker-custom');



            function setMinDateTime() {

                const now = new Date();

                // Tambah 2 jam dari waktu sekarang

                now.setHours(now.getHours() + 2);



                // Format ke YYYY-MM-DDTHH:mm yang dibutuhkan oleh input datetime-local

                const year = now.getFullYear();

                const month = (now.getMonth() + 1).toString().padStart(2, '0');

                const day = now.getDate().toString().padStart(2, '0');

                const hours = now.getHours().toString().padStart(2, '0');

                const minutes = now.getMinutes().toString().padStart(2, '0');



                const minDateTime = `${year}-${month}-${day}T${hours}:${minutes}`;



                // Atur atribut 'min' pada elemen input

                dateTimePicker.setAttribute('min', minDateTime);

            }



            // Panggil fungsi saat halaman dimuat

            setMinDateTime();

        });

    </script>

</body>

</html>