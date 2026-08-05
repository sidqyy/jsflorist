<?php



namespace App\Controllers;



use App\Controllers\BaseController;

use App\Models\ProductModel;

use App\Models\CustomProductRequestModel;



class CustomOrderController extends BaseController

{

    /**

     * Menerima data dari form shop-detail, lalu menampilkan halaman konfirmasi (custom checkout).

     * Metode ini sekarang HANYA untuk PRDKCUST. PRDKUANG akan bypass ini dan langsung ke cart.

     */

    public function checkout()

    {

        $productId = $this->request->getPost('product_id');

        $customDetails = $this->request->getPost('custom_details');



        // Ini hanya untuk PRDKCUST/PRDKCUST1

        $allowedCustomProducts = ['PRDKCUST', 'PRDKCUST1'];

        if (in_array($productId, $allowedCustomProducts, true) && (empty($customDetails['jenis_item']) || empty($customDetails['jumlah_item']))) {

            return redirect()->back()->with('error', 'Harap isi Jenis Item dan Jumlah Item.');

        } elseif (!in_array($productId, $allowedCustomProducts, true)) {

            // Jika bukan PRDKCUST, kemungkinan ada kesalahan routing atau upaya bypass

            // atau PRDKUANG yang tidak seharusnya ke sini lagi.

            return redirect()->to(site_url('shop'))->with('error', 'Tipe produk tidak valid untuk permintaan kustom yang perlu ditinjau.');

        }



        $productModel = new ProductModel();

        $product = $productModel->find($this->request->getPost('product_id'));



        if (!$product) {

            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("Produk kustom tidak ditemukan.");

        }



        $data = [

            'product' => $product,

            'customDetails' => $customDetails,

            'moneySourceType' => null, // Tidak relevan untuk PRDKCUST

            'upah' => null, // Tidak relevan untuk PRDKCUST

            'loggedInUser' => session()->get('user')

        ];

     $data['store'] = $this->storeData;

        return view('custom_checkout', $data);

    }



    /**

     * Menyimpan permintaan kustom ke database.

     * Metode ini sekarang HANYA menangani permintaan kustom PRDKCUST.

     * Buket Uang (PRDKUANG) sepenuhnya ditangani melalui alur keranjang.

     */

    public function saveRequest()

    {

        $productId = $this->request->getPost('product_id');



        // Ini hanya akan menangani PRDKCUST/PRDKCUST1

        $allowedCustomProducts = ['PRDKCUST', 'PRDKCUST1'];

        if (!in_array($productId, $allowedCustomProducts, true)) {

            return redirect()->to(site_url('shop'))->with('error', 'Tipe produk tidak valid untuk disimpan sebagai permintaan kustom.');

        }



        $rules = [

            'product_id' => 'required',

            'additional_notes' => 'permit_empty|string',

            'tanggal_pengantaran' => 'required|valid_date[Y-m-d\TH:i]',

            'nama_pemesan' => 'required|string|max_length[100]',

            'nomor_pemesan' => 'required|string|max_length[25]',

            'custom_details.jenis_item' => 'required|string|max_length[255]',

            'custom_details.jumlah_item' => 'required|string|max_length[255]',

        ];



        if (!$this->validate($rules)) {

            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());

        }



        try {

            $witaTimeZone = new \DateTimeZone('Asia/Makassar');

            $requestedDateTime = new \DateTime($this->request->getPost('tanggal_pengantaran'), $witaTimeZone);

            $now = new \DateTime('now', $witaTimeZone);

            $now->add(new \DateInterval('PT2H'));



            if ($requestedDateTime < $now) {

                return redirect()->back()->withInput()->with('errors', ['tanggal_pengantaran' => 'Waktu pengantaran minimal harus 2 jam dari sekarang (Waktu Indonesia Tengah).']);

            }

        } catch (\Exception $e) {

            return redirect()->back()->withInput()->with('errors', ['tanggal_pengantaran' => 'Format tanggal dan waktu tidak valid.']);

        }



        $model = new CustomProductRequestModel();

        $customDetails = $this->request->getPost('custom_details');



        $dataToSave = [

            'user_id' => session()->get('user')['id'] ?? null,

            'product_template_id' => $productId,

            'item_type' => $customDetails['jenis_item'],

            'item_quantity' => $customDetails['jumlah_item'],

            'requested_flowers' => isset($customDetails['bunga']) ? json_encode($customDetails['bunga']) : null,

            'additional_notes' => $this->request->getPost('additional_notes'),

            'delivery_date_requested' => $this->request->getPost('tanggal_pengantaran'),

            'nama_pemesan' => $this->request->getPost('nama_pemesan'),

            'nomor_pemesan' => $this->request->getPost('nomor_pemesan'),

            'request_status' => 'Menunggu Review',

            'service_fee' => null, // Tidak ada upah khusus untuk PRDKCUST

            'money_source_type' => null, // Tidak ada sumber uang khusus untuk PRDKCUST

        ];



        if ($model->save($dataToSave)) {

            return redirect()->to('/shop')->with('custom_success', 'Pesanan custom anda kami terima, silahkan tunggu pesan whatsapp dari js florist untuk konfirmasi pemesanan dan harga.');

        } else {

            return redirect()->back()->withInput()->with('error', 'Gagal menyimpan permintaan. Silakan coba lagi.');

        }

    }



    // Helper function (still used by CartController now)

    private function calculateUpahBuketUang(int $lembar): int

    {

        if ($lembar >= 5 && $lembar <= 20) {

            return 250000;

        } elseif ($lembar >= 21 && $lembar <= 40) {

            return 400000;

        } elseif ($lembar >= 41 && $lembar <= 60) {

            return 600000;

        } elseif ($lembar >= 61 && $lembar <= 80) {

            return 800000;

        } elseif ($lembar >= 81 && $lembar <= 100) {

            return 1000000;

        }

        return 0;

    }

}