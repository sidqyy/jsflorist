<div class="container-fluid fixed-top">
    <div class="container topbar bg-primary d-none d-lg-block">
        <div class="d-flex justify-content-between">
            <div class="top-info ps-2">
                <?php if (!empty($store['gmaps_url']) && !empty($store['address'])): ?>
                    <small class="me-3"><i class="fas fa-map-marker-alt me-2 text-secondary"></i> <a href="<?= esc($store['gmaps_url'], 'attr') ?>" class="text-white" target="_blank"><?= esc($store['address']) ?></a></small>
                <?php endif; ?>

            </div>
            <div class="top-link pe-2">
                <?php if (!empty($store['email'])): ?>
                    <small class="me-3"><i class="fas fa-envelope me-2 text-secondary"></i><a href="mailto:<?= esc($store['email'], 'attr') ?>" class="text-white"><?= esc($store['email']) ?></a></small>
                <?php endif; ?>
                <!-- <a href="#" class="text-white"><small class="text-white mx-2">Privacy Policy</small>/</a>
                <a href="#" class="text-white"><small class="text-white mx-2">Terms of Use</small>/</a>
                <a href="#" class="text-white"><small class="text-white ms-2">Sales and Refunds</small></a> -->
            </div>

        </div>
    </div>
    <div class="container px-0">
        <nav class="navbar navbar-light bg-white navbar-expand-xl">
            <a href="/dashboard" class="navbar-brand d-flex align-items-center">
                <img src="<?= base_url($store['logo_url']) ?>" alt="Logo <?= esc($store['name']) ?>" style="height: 50px; vertical-align: middle; margin-right: 10px;">
                <div>
                    <h1 class="text-primary display-6 mb-0" style="font-size: 2.5rem; line-height: 1;"><?= esc($store['name']) ?></h1>
                    <?php if (isset($store['name']) && $store['name'] !== 'JS Florist'): ?>
                        <small class="text-muted" style="font-size: 0.75rem; letter-spacing: 0.5px;">the best solution for your floral need</small>
                    <?php endif; ?>
                </div>
            </a>
            <button class="navbar-toggler py-2 px-3" type="button" data-bs-toggle="collapse" data-bs-target="#navbarCollapse">
                <span class="fa fa-bars text-primary"></span>
            </button>
            <div class="collapse navbar-collapse bg-white" id="navbarCollapse">
                <div class="navbar-nav mx-auto">
                    <?php $uri = service('uri'); ?>
                    <a href="/dashboard" class="nav-item nav-link <?= ($uri->getSegment(1) === 'dashboard' || $uri->getSegment(1) === '') ? 'active' : '' ?>">Home</a>
                    <a href="/shop" class="nav-item nav-link <?= ($uri->getSegment(1) === 'shop') ? 'active' : '' ?>">Shop</a>
                    <a href="/komik" class="nav-item nav-link <?= ($uri->getSegment(1) === 'komik') ? 'active' : '' ?>">Komik</a>

                </div>

                <div class="d-flex m-3 me-0">
                    <a href="/tracking" class="position-relative me-4 my-auto" title="Lacak Pesanan">

                        <i class="fas fa-truck fa-2x"></i>
                    </a>
                    <!-- <button class="btn-search btn border border-secondary btn-md-square rounded-circle bg-white me-4" data-bs-toggle="modal" data-bs-target="#searchModal"><i class="fas fa-search text-primary"></i></button> -->
                    <a href="/cart" class="position-relative me-4 my-auto">

                        <i class="fa fa-shopping-bag fa-2x"></i>
                        <span class="position-absolute bg-secondary rounded-circle d-flex align-items-center justify-content-center text-dark px-1" style="top: -5px; left: 15px; height: 20px; min-width: 20px;">
                            <?php
                            $totalItems = 0;
                            if (session()->has('cart')) {
                                foreach (session('cart') as $item) {
                                    $totalItems += $item['quantity'] ?? 0;
                                }
                            }
                            echo $totalItems;
                            ?>
                        </span>
                    </a>

                    <a href="/admin/login" class="my-auto">

                        <i class="fas fa-user fa-2x"></i>
                    </a>
                </div>
            </div>
        </nav>
    </div>
</div>