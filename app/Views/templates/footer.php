<div class="container-fluid bg-dark text-white-50 footer pt-5 mt-5">
    <div class="container-fluid py-5">

        <div class="pb-4 mb-4" style="border-bottom: 1px solid rgba(226, 175, 24, 0.5) ;">
            <div class="row g-4">
                <div class="col-lg-3">
                    <a href="#">
                        <h1 class="text-primary mb-0"><?= esc($store['name']) ?></h1>
                        <p class="text-secondary mb-0">Produk dan Bunga Segar</p>
                    </a>
                </div>

                <div class="col-lg-3">
                    <div class="d-flex justify-content-end pt-1">
                        <?php if (!empty($store['instagram'])): ?>
                            <a class="btn  btn-outline-secondary me-2 btn-md-square rounded-circle" href="<?= esc($store['instagram']) ?>"><i class="fab fa-instagram"></i></a>
                        <?php endif; ?>
                        <!-- <a class="btn btn-outline-secondary me-2 btn-md-square rounded-circle" href="<?= esc($store['facebook']) ?>"><i class="fab fa-facebook-f"></i></a>
                        <a class="btn btn-outline-secondary me-2 btn-md-square rounded-circle" href="<?= esc($store['youtube']) ?>"><i class="fab fa-youtube"></i></a>
                        <a class="btn btn-outline-secondary btn-md-square rounded-circle" href="<?= esc($store['linkedin']) ?>"><i class="fab fa-linkedin-in"></i></a> -->
                    </div>
                </div>
            </div>
        </div>
        <div class="row g-5">
            <div class="col-lg-3 col-md-6">
                <div class="footer-item">
                    <h4 class="text-light mb-3">Total Pesanan</h4>
                    <p>Telah melayani lebih dari:</p>
                    <h2 class="text-primary"><?= number_format($displayVisitorCount, 0, ',', '.') ?></h2>
                    <p>pesanan di seluruh Indonesia.</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="footer-item">
                    <h4 class="text-light mb-3">Mengapa Memilih <?= esc($store['name']) ?>?</h4>
                    <p class="mb-4">Kami menghadirkan koleksi bunga segar pilihan, dirangkai sepenuh hati oleh florist ahli kami untuk setiap momen istimewa Anda. Kepuasan Anda adalah prioritas utama.</p>
                    </div>
            </div>
           <div class="col-lg-3 col-md-6">
                <div class="footer-item">
                    <h4 class="text-light mb-3">Jam Operasional</h4>
                    <p>Setiap Hari: 08:00 - 22:00 WITA</p>
                    <h4 class="text-light mb-3 mt-4">Peta Lokasi</h4>
                    <?php if (!empty($store['gmaps_url'])): ?>
                        <iframe src="<?= esc($store['gmaps_url']) ?>" width="100%" height="150" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                    <?php endif; ?>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="footer-item">
                    <h4 class="text-light mb-3">Kontak</h4>
                    <?php if (!empty($store['address'])): ?>
                        <p><?= esc($store['address']) ?></p>
                    <?php endif; ?>
                    <?php if (!empty($store['email'])): ?>
                        <p><?= esc($store['email']) ?></p>
                    <?php endif; ?>
                    <?php if (!empty($store['phone'])): ?>
                        <p>Phone : <?= esc($store['phone']) ?></p>
                    <?php endif; ?>
                    <p>Pembayaran yang diterima</p>
                    <img src="<?= base_url('assets/img/payment.png')?>" class="img-fluid" alt="Metode Pembayaran" style="max-width: 200px; height: auto;">
                </div>
            </div>
        </div>

    </div>
</div>
<div class="container-fluid copyright bg-dark py-4">
    <div class="container-fluid">

        <div class="row">
            <div class="col-md-6 text-center text-md-start mb-3 mb-md-0">
                <span class="text-light">
                    <a href="#"><i class="fas fa-copyright text-light me-2"></i><?= esc($store['name']) ?></a>, All right reserved.
                    · <a href="<?= site_url('return-policy') ?>" class="text-light">Return Policy</a>
                </span>
            </div>
            </div>
    </div>
</div>
<a href="#" class="btn btn-primary border-3 border-primary rounded-circle back-to-top"><i class="fa fa-arrow-up"></i></a>
