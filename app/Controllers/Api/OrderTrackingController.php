<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\OrderItemModel;
use App\Models\OrderModel;
use App\Models\ProductModel;
use CodeIgniter\HTTP\ResponseInterface;

class OrderTrackingController extends BaseController
{
    protected OrderModel $orderModel;
    protected OrderItemModel $orderItemModel;
    protected ProductModel $productModel;
    private array $statusMeta = [
        'Menunggu Pembayaran'         => ['color' => '#ffc107', 'label' => 'Menunggu Pembayaran'],
        'Menunggu Bukti Transfer'     => ['color' => '#ffc107', 'label' => 'Menunggu Bukti Transfer'],
        'Menunggu Verifikasi Admin'   => ['color' => '#ffca2c', 'label' => 'Menunggu Verifikasi Admin'],
        'Dikonfirmasi'                => ['color' => '#0d6efd', 'label' => 'Pesanan Dikonfirmasi'],
        'Diproses'                    => ['color' => '#0d6efd', 'label' => 'Sedang Diproses'],
        'Siap Dikirim'                => ['color' => '#0d6efd', 'label' => 'Siap Dikirim'],
        'Siap Dikirim/Diambil'        => ['color' => '#0d6efd', 'label' => 'Siap Diambil'],
        'Dalam Pengiriman'            => ['color' => '#0dcaf0', 'label' => 'Dalam Pengiriman'],
        'Dikirim'                     => ['color' => '#0dcaf0', 'label' => 'Dikirim'],
        'Selesai'                     => ['color' => '#198754', 'label' => 'Pesanan Selesai'],
        'Dikembalikan'                => ['color' => '#6c757d', 'label' => 'Dikembalikan'],
        'Dibatalkan'                 => ['color' => '#dc3545', 'label' => 'Dibatalkan'],
        'Dibatalkan Sistem'          => ['color' => '#dc3545', 'label' => 'Dibatalkan Sistem'],
    ];

    public function __construct()
    {
        helper(['url']);
        $this->orderModel = new OrderModel();
        $this->orderItemModel = new OrderItemModel();
        $this->productModel = new ProductModel();
    }

    public function lookup(): ResponseInterface
    {
        $payload = $this->getJsonPayload();

        $orderId = trim((string) ($payload['order_id'] ?? ''));
        $nomorPemesan = trim((string) ($payload['nomor_pemesan'] ?? ''));

        if ($orderId === '' || $nomorPemesan === '') {
            return $this->response->setStatusCode(422)->setJSON([
                'status'  => 'error',
                'message' => 'Order ID dan nomor pemesan wajib diisi.',
            ]);
        }

        $order = $this->orderModel
            ->where('order_id', $orderId)
            ->where('nomor_pemesan', $nomorPemesan)
            ->first();

        if (!$order) {
            return $this->response->setStatusCode(404)->setJSON([
                'status'  => 'failed',
                'message' => 'Pesanan tidak ditemukan atau data tidak cocok.',
            ]);
        }

        $items = $this->buildOrderItemsPayload($orderId);
        $orderPayload = $this->buildOrderPayload($order, $items);
        $actions = $this->buildActionPayload($order);

        return $this->response->setJSON([
            'status' => 'success',
            'order'  => $orderPayload,
            'items'  => $items,
            'actions'=> $actions,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function buildOrderPayload(array $order, array $items): array
    {
        $status = $order['status_pesanan'] ?? '';
        $statusMeta = $this->statusMeta[$status] ?? ['color' => '#0d6efd', 'label' => $status ?: 'Status Tidak Diketahui'];
        $payment = $this->buildPaymentSummary($order, $items);

        return [
            'order_id'               => $order['order_id'] ?? null,
            'status'                 => $order['status_pesanan'] ?? null,
            'status_label'           => $statusMeta['label'],
            'status_color'           => $statusMeta['color'],
            'placed_at'              => $order['tanggal_pesan'] ?? null,
            'payment_deadline'       => $order['batas_waktu_pembayaran'] ?? null,
            'payment'                => $payment,
            'delivery'               => $this->buildDeliveryInfo($order),
            'timestamps'             => $this->buildTimestamps($order, $statusMeta['label']),
            'store'                  => [
                'name'  => $order['store_name'] ?? ($this->storeData['name'] ?? 'JS Florist'),
                'phone' => $this->storeData['phone'] ?? null,
            ],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function buildOrderItemsPayload(string $orderId): array
    {
        $items = $this->orderItemModel->where('order_id', $orderId)->findAll();
        $payload = [];

        foreach ($items as $item) {
            $product = $this->productModel->find($item['product_id']);
            $quantity = (int) ($item['kuantitas'] ?? 0);
            $price = (float) ($item['harga_satuan'] ?? 0);

            $payload[] = [
                'nama_produk'   => $product['nama_produk'] ?? 'Produk',
                'gambar_url'    => $this->formatProductImageUrl($product['gambar_url'] ?? null),
                'kuantitas'     => $quantity,
                'harga_satuan'  => $price,
                'subtotal'      => $price * $quantity,
                'custom_details'=> $this->decodeCustomDetails($item['custom_details'] ?? null),
                'catatan'       => null,
            ];
        }

        return $payload;
    }

    /**
     * @return array<string, mixed>
     */
    private function buildActionPayload(array $order): array
    {
        $deadline = $order['batas_waktu_pembayaran'] ?? null;
        $status = $order['status_pesanan'] ?? null;
        $paymentRedirect = null;
        $canUpload = false;
        $canPay = false;

        if ($status === 'Menunggu Bukti Transfer') {
            $deadlineDate = $deadline ? new \DateTime($deadline, new \DateTimeZone($this->appTimezone ?? 'Asia/Makassar')) : null;
            $now = new \DateTime('now', new \DateTimeZone($this->appTimezone ?? 'Asia/Makassar'));

            if ($deadlineDate === null || $now <= $deadlineDate) {
                $canUpload = true;
                $canPay = true;
                $paymentRedirect = $order['metode_pembayaran'] === 'QRIS'
                    ? site_url('checkout/qris/' . $order['order_id'])
                    : site_url('payment/bank-transfer/' . $order['order_id']);
            }
        }

        return [
            'deadline'             => $deadline,
            'can_upload_proof'     => $canUpload,
            'upload_proof_url'     => site_url('api/checkout/upload-proof'),
            'can_pay'              => $canPay,
            'payment_redirect_url' => $paymentRedirect,
            'payment_label'        => $this->getPaymentLabel($order['metode_pembayaran'] ?? null),
        ];
    }

    /**
     * @return array<int|string, mixed>|null
     */
    private function decodeCustomDetails(?string $json): ?array
    {
        if ($json === null || $json === '') {
            return null;
        }

        $decoded = json_decode($json, true);
        return json_last_error() === JSON_ERROR_NONE ? $decoded : null;
    }

    /**
     * @return array<string, mixed>
     */
    private function getJsonPayload(): array
    {
        $json = $this->request->getJSON(true);
        if (is_array($json)) {
            return $json;
        }

        return $this->request->getPost() ?? [];
    }

    private function buildPaymentSummary(array $order, array $items): array
    {
        $subtotal = array_sum(array_map(static fn($item) => (float) ($item['subtotal'] ?? 0), $items));
        $discount = (float) ($order['diskon'] ?? 0);
        $shipping = (float) ($order['biaya_pengiriman'] ?? 0);
        $total = max($subtotal - $discount + $shipping, 0);
        $paidAmount = $this->determinePaidAmount($order, $total);
        $outstanding = max($total - $paidAmount, 0);

        return [
            'subtotal'           => $subtotal,
            'discount'           => $discount,
            'shipping_cost'      => $shipping,
            'total'              => $total,
            'paid_amount'        => $paidAmount,
            'outstanding_amount' => $outstanding,
        ];
    }

    private function determinePaidAmount(array $order, float $total): float
    {
        $status = $order['status_pesanan'] ?? '';
        $paidStatuses = [
            'Dikonfirmasi',
            'Diproses',
            'Siap Dikirim',
            'Siap Dikirim/Diambil',
            'Dalam Pengiriman',
            'Dikirim',
            'Selesai',
            'Dikembalikan',
        ];

        if (in_array($status, $paidStatuses, true) || !empty($order['bukti_bayar'])) {
            return $total;
        }

        return 0.0;
    }

    private function buildDeliveryInfo(array $order): array
    {
        $type = $order['tipe_pengantaran'] ?? 'Delivery';
        $address = $type === 'Delivery'
            ? ($order['alamat_pengiriman_teks'] ?? null)
            : ($order['pickup_location'] ?? ($this->storeData['name'] ?? 'Toko'));

        return [
            'type'            => $type,
            'address'         => $address,
            'recipient_name'  => $order['penerima_nama'] ?? null,
            'recipient_phone' => $order['penerima_nomor_hp'] ?? null,
            'note'            => $order['catatan_penerima'] ?? null,
        ];
    }

    private function buildTimestamps(array $order, string $statusLabel): array
    {
        $timeline = [];
        if (!empty($order['tanggal_pesan'])) {
            $timeline[] = [
                'status'   => 'placed',
                'label'    => 'Pesanan dibuat',
                'datetime' => $order['tanggal_pesan'],
            ];
        }

        if (!empty($order['tanggal_pengantaran'])) {
            $timeline[] = [
                'status'   => 'delivery_eta',
                'label'    => 'Jadwal pengantaran',
                'datetime' => $order['tanggal_pengantaran'],
            ];
        }

        $timeline[] = [
            'status'   => 'current',
            'label'    => $statusLabel,
            'datetime' => $order['updated_at'] ?? $order['tanggal_pesan'] ?? null,
        ];

        return $timeline;
    }

    private function getPaymentLabel(?string $method): string
    {
        if ($method === 'QRIS') {
            return 'Bayar via QRIS';
        }

        if ($method === 'Direct Bank Transfer') {
            return 'Lanjutkan Pembayaran Bank Transfer';
        }

        return 'Lanjutkan Pembayaran';
    }

    private function formatProductImageUrl(?string $path): ?string
    {
        if (empty($path)) {
            return null;
        }

        if (preg_match('#^https?://#i', $path)) {
            return $path;
        }

        return base_url('assets/img/gambar/' . ltrim($path, '/'));
    }
}
