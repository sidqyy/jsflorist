<?= $this->extend('templates/main_layout') ?>

<?= $this->section('title') ?>
Kebijakan Pengembalian & Pembatalan | <?= esc($store['name']) ?>
<?= $this->endSection() ?>

<?= $this->section('meta_description') ?>
Baca kebijakan pengembalian, pembatalan pesanan, dan pengembalian dana di <?= esc($store['name']) ?>. Informasi resmi untuk pelanggan.
<?= $this->endSection() ?>

<?= $this->section('meta_keywords') ?>
return policy, kebijakan pengembalian, pengembalian dana, refund, pembatalan pesanan, florist, <?= esc($store['name']) ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-lg-10">
      <h1 class="mb-4">Kebijakan Pengembalian & Pembatalan</h1>
      <p class="text-muted">Terakhir diperbarui: <?= date('F j, Y') ?></p>

      <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">
          <h3 class="h5">1. Produk Bunga Segar & Custom</h3>
          <p>
            Karena sifat produk kami yang berupa bunga segar dan rangkaian khusus (custom), 
            pengembalian barang tidak dapat dilakukan setelah pesanan dirangkai/ diproses. 
            Namun, kami berkomitmen penuh pada kualitas. Jika terjadi masalah pada kualitas 
            (misal bunga layu parah saat diterima, kerusakan signifikan akibat pengiriman), 
            silakan hubungi kami maksimal 24 jam setelah pesanan diterima dengan menyertakan
            foto/ video sebagai bukti.
          </p>

          <h3 class="h5 mt-4">2. Pembatalan Pesanan</h3>
          <ul>
            <li>Pembatalan gratis dapat dilakukan sebelum proses perakitan dimulai.</li>
            <li>
              Jika perakitan sudah dimulai, kami dapat mengenakan biaya penggantian material 
              yang telah digunakan.
            </li>
            <li>Pembatalan pada hari H pengantaran mungkin tidak dapat diproses.</li>
          </ul>

          <h3 class="h5 mt-4">3. Pengembalian Dana (Refund)</h3>
          <ul>
            <li>
              Refund penuh diberikan jika pesanan dibatalkan sebelum perakitan dimulai atau jika 
              kami tidak dapat memenuhi pesanan karena stok/ kendala internal.
            </li>
            <li>
              Refund sebagian dapat dipertimbangkan apabila sebagian material telah digunakan.
            </li>
            <li>
              Proses refund diperkirakan 1–3 hari kerja setelah disetujui, 
              dikirim ke rekening/ metode pembayaran yang sama.
            </li>
          </ul>

          <h3 class="h5 mt-4">4. Ketidaksesuaian & Substitusi</h3>
          <p>
            Tersedia kemungkinan substitusi bunga/ warna/ kemasan dengan kualitas setara/ lebih baik
            mengikuti ketersediaan pasar. Kami akan tetap menjaga estetika dan nilai produk.
          </p>

          <h3 class="h5 mt-4">5. Cara Mengajukan Komplain</h3>
          <p>
            Ajukan kendala maksimal 24 jam setelah barang diterima melalui kontak berikut:
          </p>
          <ul class="mb-0">
            <?php if (!empty($store['phone'])): ?>
              <li>WhatsApp: <a href="https://wa.me/<?= esc(preg_replace('/[^0-9]/', '', $store['phone']), 'attr') ?>" target="_blank"><?= esc($store['phone']) ?></a></li>
            <?php endif; ?>
            <?php if (!empty($store['email'])): ?>
              <li>Email: <a href="mailto:<?= esc($store['email'], 'attr') ?>"><?= esc($store['email']) ?></a></li>
            <?php endif; ?>
            <?php if (!empty($store['address'])): ?>
              <li>Alamat Toko: <?= esc($store['address']) ?></li>
            <?php endif; ?>
          </ul>

          <h3 class="h5 mt-4">6. Wilayah Layanan & Pengantaran</h3>
          <p>
            Kami melayani pengantaran sesuai jangkauan yang tersedia di halaman checkout. 
            Keterlambatan akibat faktor eksternal (cuaca, lalu lintas, akses alamat) akan kami informasikan.
          </p>

          <h3 class="h5 mt-4">7. Syarat Tambahan</h3>
          <p class="mb-0">
            Dengan melakukan pemesanan, Anda menyetujui kebijakan ini. Pertanyaan lebih lanjut 
            dapat diajukan melalui kanal kontak di atas.
          </p>
        </div>
      </div>

      <p class="small text-muted">Catatan: Kebijakan ini dibuat untuk memenuhi persyaratan Google Merchant Center terkait halaman pengembalian/ refund.</p>
    </div>
  </div>
</div>
<?= $this->endSection() ?>
