<!DOCTYPE html>

<?= $this->extend('templates/main_layout') ?>

<html lang="en">



<?= $this->section('title') ?>

Order Success- <?= esc($store['name']) ?>

<?= $this->endSection() ?>



<?= $this->section('content') ?>

<head>

    <meta charset="utf-8">

    <title>Pesanan Berhasil! - JS Florist</title>

    <link href="<?= base_url('assets/css/bootstrap.min.css') ?>" rel="stylesheet">

    <link href="<?= base_url('assets/css/style.css') ?>" rel="stylesheet">

    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css"/>

</head>

<body>

    <div class="container-fluid fixed-top">

        </div>



    <div class="container-fluid page-header py-5">

        <h1 class="text-center text-white display-6">Pesanan Berhasil!</h1>

        <ol class="breadcrumb justify-content-center mb-0">

            <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Home</a></li>

            <li class="breadcrumb-item"><a href="<?= base_url('cart') ?>">Keranjang</a></li>

            <li class="breadcrumb-item active text-white">Pesanan Berhasil</li>

        </ol>

    </div>



    <div class="container py-5">

        <div class="row justify-content-center">

            <div class="col-lg-8 text-center">

                <div class="bg-light rounded p-5">

                    <?php if (session()->getFlashdata('success')): ?>

                        <div class="alert alert-success alert-dismissible fade show" role="alert">

                            <?= esc(session()->getFlashdata('success')) ?>

                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>

                        </div>

                    <?php endif; ?>



                    <h2 class="mb-4">Terima Kasih Atas Pesanan Anda!</h2>

                    <p class="lead">Pesanan Anda telah berhasil ditempatkan.</p>

                    <p class="display-4 text-primary mb-4">Nomor Pesanan Anda: #<?= esc($order['order_id']) ?></p>



                    <p class="mb-4">Harap catat Nomor Pesanan ini untuk melacak status pesanan Anda. Kami akan segera memproses pesanan Anda.</p>

                    

                    <a href="<?= base_url('tracking') ?>" class="btn btn-primary btn-lg px-5 py-3 rounded-pill">Lacak Pesanan Anda</a>

                    <a href="<?= base_url('dashboard') ?>" class="btn btn-secondary btn-lg px-5 py-3 rounded-pill ms-3">Kembali ke Beranda</a>

                </div>

            </div>

        </div>

    </div>



 

   



    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>

    </body>

</html>
<?= $this->section('extra_js') ?>

<!-- Google tag (gtag.js) event -->
<script>
  gtag('event', 'conversion_event_purchase', {
    // <event_parameters>
  });
</script>

<?= $this->endSection() ?>

<?= $this->endSection() ?>