<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('view-proof/(:segment)', 'FileViewer::viewProof/$1');

// == RUTE PUBLIK & PENGGUNA ==
$routes->get('/', 'Home::index'); 
$routes->get('/dashboard', 'Home::index'); 
$routes->get('/debug-event-domain', 'Home::debugEventDomain'); 
$routes->get('shop', 'ShopController::index');
$routes->get('shop/product/(:segment)', 'Home::productDetail/$1');
$routes->get('artikel', 'Home::allArticles');
$routes->get('artikel/(:any)', 'Home::artikel/$1');
$routes->get('cek-database', 'DbTest::index');

// Halaman kebijakan pengembalian (Return Policy) untuk Google Merchant Center
$routes->get('return-policy', 'Home::returnPolicy');
$routes->get('kebijakan-pengembalian', 'Home::returnPolicy');

// Rute Keranjang Belanja
$routes->post('cart/add', 'CartController::add');
$routes->get('cart', 'CartController::index');
$routes->post('cart/remove/(:segment)', 'CartController::remove/$1');
$routes->post('cart/update', 'CartController::update');
$routes->get('shop/product/cart', 'CartController::index');
$routes->post('cart/apply-voucher', 'CartController::applyVoucher');

// Rute Permintaan Produk Kustom
$routes->post('custom/checkout', 'CustomOrderController::checkout');
$routes->post('custom/save', 'CustomOrderController::saveRequest');

// Rute Checkout & Pelacakan Web
$routes->get('checkout', 'CheckoutController::index');
$routes->post('checkout/process', 'CheckoutController::process');
$routes->post('checkout/estimateShipping', 'CheckoutController::estimateShipping');
$routes->post('checkout/generate-whatsapp', 'CheckoutController::generateWhatsAppUrl');
$routes->get('checkout/qris/(:any)', 'CheckoutController::showQrisPage/$1');
$routes->post('checkout/checkBonusAjax', 'CheckoutController::checkBonusAjax');
$routes->get('order-success/(:any)', 'CheckoutController::orderSuccess/$1');
$routes->get('tracking', 'OrderTracking::index');
$routes->post('tracking', 'OrderTracking::track');

// Rute Komik
$routes->get('komik', 'ComicController::index');
$routes->get('komik/(:segment)', 'ComicController::show/$1');

// Rute Pembayaran
$routes->get('payment/bank-transfer/(:any)', 'Payment::showBankTransfer/$1');
$routes->post('payment/upload-proof', 'Payment::uploadProof');

// == RUTE ADMIN ==
$routes->get('admin/login', 'Admin\AuthController::login');
$routes->post('admin/login/authenticate', 'Admin\AuthController::authenticate'); 

// --- Grup Rute Admin Perlindungan Login (Semua Role) ---
$routes->group('admin', ['filter' => 'role'], function ($routes) {
    $routes->get('/', 'Admin\DashboardController::dashboard');
    $routes->get('dashboard', 'Admin\DashboardController::dashboard');
    $routes->get('check-new-orders', 'Admin\DashboardController::checkNewOrders'); 
    $routes->get('logout', 'Admin\AuthController::logout');

    // Orders
    $routes->get('orders', 'Admin\OrderController::index');
    $routes->get('orders/detail/(:any)', 'Admin\OrderController::detail/$1');
    $routes->post('orders/update-status/(:any)', 'Admin\OrderController::updateStatus/$1');

    // Custom Requests
    $routes->get('custom-requests', 'Admin\CustomRequestController::index');
    $routes->post('custom-requests/update-status/(:num)', 'Admin\CustomRequestController::updateStatus/$1');
    
    // Articles
    $routes->get('articles', 'Admin\ArticleController::index');
    $routes->get('articles/create', 'Admin\ArticleController::create');
    $routes->post('articles/store', 'Admin\ArticleController::store');
    $routes->get('articles/edit/(:num)', 'Admin\ArticleController::edit/$1');
    $routes->post('articles/update/(:num)', 'Admin\ArticleController::update/$1');
    $routes->get('articles/delete/(:num)', 'Admin\ArticleController::delete/$1');
    $routes->post('articles/get-products', 'Admin\ArticleController::getProductsByCategory');

    // Comics
    $routes->get('comics', 'Admin\ComicEpisodeController::index');
    $routes->get('comics/create', 'Admin\ComicEpisodeController::create');
    $routes->post('comics/store', 'Admin\ComicEpisodeController::store');
    $routes->get('comics/edit/(:num)', 'Admin\ComicEpisodeController::edit/$1');
    $routes->post('comics/update/(:num)', 'Admin\ComicEpisodeController::update/$1');
    $routes->get('comics/delete/(:num)', 'Admin\ComicEpisodeController::delete/$1');
    $routes->post('comics/toggle-status/(:num)', 'Admin\ComicEpisodeController::toggleStatus/$1');

    $routes->get('comics/(:num)/panels', 'Admin\ComicPanelController::index/$1');
    $routes->get('comics/(:num)/panels/create', 'Admin\ComicPanelController::create/$1');
    $routes->post('comics/(:num)/panels/store', 'Admin\ComicPanelController::store/$1');
    $routes->get('comics/(:num)/panels/edit/(:num)', 'Admin\ComicPanelController::edit/$1/$2');
    $routes->post('comics/(:num)/panels/update/(:num)', 'Admin\ComicPanelController::update/$1/$2');
    $routes->get('comics/(:num)/panels/delete/(:num)', 'Admin\ComicPanelController::delete/$1/$2');
});

// --- GRUP KHUSUS UNTUK ROLE 'MANAGEMENT' ---
$routes->group('admin', ['filter' => 'role:management'], function ($routes) {
    // Products
    $routes->get('products', 'Admin\ProductController::index');
    $routes->get('products/create', 'Admin\ProductController::create');
    $routes->post('products/store', 'Admin\ProductController::store');
    $routes->get('products/edit/(:segment)', 'Admin\ProductController::edit/$1');
    $routes->put('products/update/(:segment)', 'Admin\ProductController::update/$1');
    $routes->get('products/delete/(:any)', 'Admin\ProductController::delete/$1');
    $routes->get('products/analysis', 'Admin\OrderController::productAnalysis');
    $routes->get('get-subcategories', 'Admin\ProductController::getSubcategories');
    $routes->get('get-subcategories/(:num)', 'Admin\ProductController::getSubcategoriesByCategoryId/$1');

    // Revenue
    $routes->get('revenue', 'Admin\OrderController::revenue');

    // Product-Occasions
    $routes->get('product-occasions', 'Admin\ProductOccasionController::index');
    $routes->get('product-occasions/products/(:num)', 'Admin\ProductOccasionController::products/$1');
    $routes->post('product-occasions/add-products', 'Admin\ProductOccasionController::addProducts');

    // Discount Rules CRUD
    $routes->get('discounts', 'Admin\\DiscountController::index');
    $routes->get('discounts/create', 'Admin\\DiscountController::create');
    $routes->post('discounts/store', 'Admin\\DiscountController::store');
    $routes->get('discounts/test-store', 'Admin\\DiscountController::testStore'); 
    $routes->post('discounts/test-store', 'Admin\\DiscountController::testStore'); 
    $routes->get('discounts/edit/(:num)', 'Admin\\DiscountController::edit/$1');
    $routes->post('discounts/update/(:num)', 'Admin\\DiscountController::update/$1');
    $routes->get('discounts/delete/(:num)', 'Admin\\DiscountController::delete/$1');
    $routes->post('discounts/toggle-status/(:num)', 'Admin\\DiscountController::toggleStatus/$1');
    
    // Bonus Rules (Promo Ferrero) CRUD
    $routes->get('bonus/rules', 'Admin\BonusController::index');
    $routes->get('bonus/rules/create', 'Admin\BonusController::create');
    $routes->post('bonus/rules/store', 'Admin\BonusController::store');
    $routes->get('bonus/rules/edit/(:num)', 'Admin\BonusController::edit/$1');
    $routes->post('bonus/rules/update/(:num)', 'Admin\BonusController::update/$1');
    $routes->get('bonus/rules/delete/(:num)', 'Admin\BonusController::delete/$1');
    $routes->post('bonus/rules/toggle-status/(:num)', 'Admin\BonusController::toggleStatus/$1');
    
    // Free Shipping Rules CRUD
    $routes->get('free-shipping', 'Admin\FreeShippingController::index');
    $routes->get('free-shipping/create', 'Admin\FreeShippingController::create');
    $routes->post('free-shipping/store', 'Admin\FreeShippingController::store');
    $routes->get('free-shipping/edit/(:num)', 'Admin\FreeShippingController::edit/$1');
    $routes->post('free-shipping/update/(:num)', 'Admin\FreeShippingController::update/$1');
    $routes->post('free-shipping/delete/(:num)', 'Admin\FreeShippingController::delete/$1');
    $routes->post('free-shipping/toggle-status/(:num)', 'Admin\FreeShippingController::toggleStatus/$1');

    // Event Banner CRUD
    $routes->get('event-banners', 'Admin\EventBannerController::index');
    $routes->get('event-banners/create', 'Admin\EventBannerController::create');
    $routes->post('event-banners/store', 'Admin\EventBannerController::store');
    $routes->get('event-banners/edit/(:num)', 'Admin\EventBannerController::edit/$1');
    $routes->post('event-banners/update/(:num)', 'Admin\EventBannerController::update/$1');
    $routes->get('event-banners/delete/(:num)', 'Admin\EventBannerController::delete/$1');
    $routes->post('event-banners/toggle-status/(:num)', 'Admin\EventBannerController::toggleStatus/$1');

    // Voucher Catalog (Admin)
    $routes->get('vouchers', 'Admin\VoucherController::index');
    $routes->get('vouchers/create', 'Admin\VoucherController::create');
    $routes->post('vouchers/store', 'Admin\VoucherController::store');
    $routes->get('vouchers/edit/(:num)', 'Admin\VoucherController::edit/$1');
    $routes->post('vouchers/update/(:num)', 'Admin\VoucherController::update/$1');
    $routes->get('vouchers/delete/(:num)', 'Admin\VoucherController::delete/$1');
    $routes->get('vouchers/toggle-status/(:num)', 'Admin\VoucherController::toggleStatus/$1');

    // Traffic Analysis
    $routes->get('traffic', 'Admin\TrafficController::index');
    $routes->get('traffic/pages', 'Admin\TrafficController::pages');
    $routes->get('traffic/logs', 'Admin\TrafficController::logs');
    $routes->get('traffic/get-cities', 'Admin\TrafficController::getCities');
    $routes->get('traffic/api-data', 'Admin\TrafficController::apiData');
    $routes->get('traffic/debug', 'Admin\TrafficController::debug');
    $routes->get('traffic/test', 'Admin\TrafficController::testTracking');
    $routes->get('traffic/referer', 'Admin\TrafficController::refererDetails');
    $routes->get('traffic/cleanup', 'Admin\TrafficController::cleanup');
});

$routes->get('sitemap.xml', 'SitemapController::index');

// == PUBLIC API ROUTES ==
$routes->group('api', ['namespace' => 'App\Controllers\Api', 'filter' => 'apicors'], function ($routes) {
    $routes->options('(:any)', static function () {
        return service('response')->setStatusCode(200);
    });
    
    $routes->get('dashboard', 'DashboardController::index');
    $routes->get('dashboard/occasions', 'DashboardController::occasions');
    $routes->get('dashboard/articles', 'DashboardController::articles');
    $routes->get('dashboard/best-sellers', 'DashboardController::bestSellers');
    $routes->get('event-banners', 'EventBannerController::index');
    $routes->get('shop', 'ShopController::index');
    $routes->get('shop/products/(:segment)', 'ShopController::show/$1');
    $routes->post('custom/checkout', 'CustomOrderController::checkout');
    $routes->post('custom/requests', 'CustomOrderController::create');
    $routes->get('articles', 'ArticleController::index');
    $routes->get('articles/(:segment)', 'ArticleController::show/$1');
    
    // Discount & Config API (DIPERSIAPKAN BERSIH)
    $routes->get('discounts/rules', 'CheckoutController::config');
    $routes->get('discounts', 'DiscountController::index');
    $routes->get('discounts/products-with-discount', 'DiscountController::productsWithDiscount');
    $routes->get('discounts/product/(:segment)', 'DiscountController::forProduct/$1');
    $routes->get('discounts/(:num)', 'DiscountController::show/$1');
    $routes->post('discounts/calculate', 'DiscountController::calculate');
    
    // Checkout, Voucher & Bonus Core API
    $routes->post('cart/apply-voucher', 'VoucherController::applyVoucher');
    $routes->get('checkout/config', 'CheckoutController::config');
    $routes->post('checkout/estimate-shipping', 'CheckoutController::estimateShipping');
    $routes->post('checkout/whatsapp-link', 'CheckoutController::whatsappLink');
    $routes->post('checkout/orders', 'CheckoutController::placeOrder');
    $routes->get('checkout/orders/(:segment)', 'CheckoutController::showOrder/$1');
    $routes->get('orders/history', 'CheckoutController::orderHistory');
    $routes->post('checkout/upload-proof', 'CheckoutController::uploadProof');
    $routes->post('checkout/check-bonus', 'CheckoutController::checkBonusAjax');
    
    // Member & Pelacakan
    $routes->post('member/register', 'MemberAuthController::register');
    $routes->post('member/login', 'MemberAuthController::login');
    $routes->post('member/logout', 'MemberAuthController::logout');
    $routes->post('member/request-reset', 'MemberAuthController::requestPasswordReset');
    $routes->post('member/reset-password', 'MemberAuthController::resetPassword');
    $routes->post('tracking/lookup', 'OrderTrackingController::lookup');
    $routes->get('member/profile', 'MemberController::profile');
    $routes->get('member/points', 'MemberPointController::index');
    $routes->get('member/vouchers', 'MemberController::vouchers');
    $routes->get('member/vouchers/catalog', 'MemberController::voucherCatalog');
    $routes->post('member/vouchers/claim', 'MemberController::claimVoucher');
    $routes->get('member/games', 'MemberController::games');
    $routes->post('member/games/play', 'MemberController::play');
    $routes->get('member/purchases', 'MemberController::purchaseHistory');
    $routes->get('member/user-profile', 'MemberController::userProfile');
    $routes->post('member/user-profile', 'MemberController::updateProfile');
    $routes->post('member/user-profile/photo', 'MemberController::uploadProfilePhoto');
    $routes->post('member-game/award', 'MemberGameController::award');
    $routes->get('member-game/status', 'MemberGameController::status');
    $routes->post('member-voucher/redeem', 'MemberVoucherController::redeem');
    $routes->get('auth/me', 'AuthController::me');
    $routes->get('game/progress', 'GameProgressController::show', ['filter' => 'apiauth']);
    $routes->post('game/progress', 'GameProgressController::store', ['filter' => 'apiauth']);
});