<?= $this->extend('templates/main_layout') ?>

<?= $this->section('title') ?>
    Lacak Pesanan | <?= esc($store['name'] ?? 'JS Florist') ?>
<?= $this->endSection() ?>

<?= $this->section('meta_description') ?>
    Cek status pesanan Anda di <?= esc($store['name'] ?? 'JS Florist') ?> dengan memasukkan nomor pesanan dan nomor HP pemesan.
<?= $this->endSection() ?>

<?= $this->section('content') ?>

    <!-- Page Header -->
    <div class="container-fluid page-header py-5" style="background: linear-gradient(rgba(0, 0, 0, 0.35), rgba(0, 0, 0, 0.35)), url(<?= base_url('assets/img/page-header.png') ?>) center center no-repeat; background-size: cover;">
        <h1 class="text-center text-white display-6">Lacak Pesanan</h1>
        <ol class="breadcrumb justify-content-center mb-0">
            <li class="breadcrumb-item"><a href="<?= site_url('dashboard') ?>">Home</a></li>
            <li class="breadcrumb-item active text-white">Lacak Pesanan</li>
        </ol>
    </div>

    <!-- Tracking Form -->
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-7 col-xl-6">
                <div class="bg-light rounded-3 p-4 p-md-5 border border-secondary">
                    <div class="text-center mb-4">
                        <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-secondary" style="width:64px;height:64px;">
                            <i class="fa fa-search text-white" style="font-size:28px;"></i>
                        </div>
                        <h2 class="mt-3 mb-2">Lacak Pesanan Anda</h2>
                        <p class="text-muted mb-0">Masukkan <strong>Nomor Pesanan</strong> dan <strong>Nomor HP Pemesan</strong> untuk melihat status terbaru.</p>
                    </div>

                    <?php if (session()->getFlashdata('error')): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?= esc(session()->getFlashdata('error')) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <form action="<?= site_url('tracking') ?>" method="POST" novalidate>
                        <?= csrf_field() ?>

                        <div class="mb-3">
                            <label for="orderId" class="form-label fw-semibold">Nomor Pesanan</label>
                            <div class="input-group">
                                <span class="input-group-text bg-secondary text-white border-0"><i class="fa fa-receipt"></i></span>
                                <input type="text" class="form-control" id="orderId" name="order_id" placeholder="Contoh: PESW001" required>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="nomorPemesan" class="form-label fw-semibold">Nomor HP Pemesan</label>
                            <div class="input-group">
                                <span class="input-group-text bg-secondary text-white border-0"><i class="fa fa-phone"></i></span>
                                <input type="text" class="form-control" id="nomorPemesan" name="nomor_pemesan" placeholder="Contoh: 081234567890" inputmode="numeric" required>
                            </div>
                            <div class="form-text">Gunakan nomor HP yang Anda masukkan saat checkout, hanya angka tanpa spasi atau simbol.</div>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary border-2 border-secondary rounded-pill py-2">
                                <i class="fa fa-search me-2"></i> Lacak Pesanan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

<?= $this->endSection() ?>

<?= $this->section('extra_js') ?>
<script>
    // Batasi input nomor HP hanya angka
    $(function(){
        $('#nomorPemesan').on('input', function(){
            this.value = this.value.replace(/[^0-9]/g, '');
        });
    });
 </script>
<?= $this->endSection() ?>