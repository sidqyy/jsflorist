
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Permintaan Terkirim - JS Florist</title>
    <link href="<?= base_url('assets/css/bootstrap.min.css') ?>" rel="stylesheet">
    <link href="<?= base_url('assets/css/style.css') ?>" rel="stylesheet">
</head>
<body>
    <?= $this->include('templates/navbar') ?>
    <div class="container text-center py-5 my-5">
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <i class="fa fa-check-circle text-success fa-5x mb-4"></i>
                <h1 class="display-4">Terima Kasih!</h1>
                <p class="lead"><?= session()->getFlashdata('success') ?></p>
                <hr>
                <p>Tim kami akan mereview permintaan Anda dan akan segera menghubungi Anda untuk konfirmasi harga dan detail lebih lanjut.</p>
                <a href="<?= site_url('/dashboard') ?>" class="btn btn-primary rounded-pill px-4 py-2 mt-4">Kembali ke Beranda</a>
            </div>
        </div>
    </div>
</body>
</html>