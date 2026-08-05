<!DOCTYPE html>
<?= $this->extend('templates/main_layout') ?>
<html lang="en">

<?= $this->section('title') ?>
Pembayaran QRIS - <?= esc($store['name']) ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran QRIS - JS Florist</title>
    <link href="<?= base_url('assets/css/bootstrap.min.css') ?>" rel="stylesheet">
    <link href="<?= base_url('assets/css/style.css') ?>" rel="stylesheet">
    <style>
        .qris-container { max-width: 500px; margin: 50px auto; padding: 30px; background-color: #fff; border-radius: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); text-align: center; }
        .qris-container h1 { color: #d09c4c; margin-bottom: 20px; }
        .qris-container img { max-width: 100%; height: auto; margin-bottom: 20px; }
        .countdown-timer { background-color: #fff3cd; border: 1px solid #ffeeba; border-radius: .25rem; padding: 1rem; margin-top: 1.5rem; }
        .countdown-timer h3 { font-size: 1.2rem; color: #856404; }
        .countdown-timer #countdown { font-size: 2.5rem; font-weight: bold; color: #dc3545; }
    </style>
</head>
<body>
    <div class="container">
        <div class="qris-container">
            <h1>Scan untuk Membayar</h1>
            
            <p>Silakan pindai kode QR di bawah ini untuk menyelesaikan pembayaran Anda.</p>
            <img src="<?= base_url('assets/img/qris.png') ?>" alt="QRIS Payment Code">
            <h4 class="mt-4">ID Pesanan Anda: <strong class="text-primary"><?= esc($order['order_id']) ?></strong></h4>
            <p>Total Pembayaran: <strong>Rp<?= number_format($order['total_harga'] ?? 0, 0, ',', '.') ?></strong></p>


            <?php if (!empty($order['batas_waktu_pembayaran'])): ?>
            <div class="countdown-timer" data-deadline="<?= esc($order['batas_waktu_pembayaran']) ?>">
                <h3>Selesaikan pembayaran dalam:</h3>
                <div id="countdown">05:00</div>
            </div>
            <?php endif; ?>

            <p class="mt-4">Setelah pembayaran berhasil, unggah bukti pembayaran Anda di bawah ini.</p>
            <form action="<?= base_url('payment/upload-proof') ?>" method="POST" enctype="multipart/form-data" class="mt-4">
                <?= csrf_field() ?>
                <input type="hidden" name="order_id" value="<?= esc($order['order_id']) ?>">
                <div class="mb-3 text-start">
                    <label for="bukti_transfer" class="form-label">Unggah Bukti Pembayaran (JPG, PNG, PDF - Max 2MB)<sup>*</sup></label>
                    <input type="file" class="form-control" id="bukti_transfer" name="bukti_transfer" accept=".jpg,.jpeg,.png,.pdf" required>
                </div>
                <button type="submit" class="btn btn-primary w-100 py-2">Kirim Bukti Pembayaran</button>
            </form>
            <a href="<?= site_url('/tracking') ?>" class="btn btn-secondary mt-3">Lacak Pesanan Saya</a>
        </div>
    </div>
</body>
</html>
<?= $this->endSection() ?>

<?= $this->section('extra_js') ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const timerContainer = document.querySelector('.countdown-timer');
    if (!timerContainer) return;

    // Mengambil waktu deadline dari atribut data-deadline
    // Format 'Y-m-d H:i:s' perlu diubah agar kompatibel lintas browser (ganti spasi dengan 'T')
    const deadline = new Date(timerContainer.dataset.deadline.replace(' ', 'T')).getTime();
    const countdownElement = document.getElementById('countdown');

    const interval = setInterval(function() {
        const now = new Date().getTime();
        const distance = deadline - now;

        if (distance < 0) {
            clearInterval(interval);
            timerContainer.innerHTML = `<h3 style="color: red;">WAKTU PEMBAYARAN HABIS</h3><p>Pesanan ini mungkin telah dibatalkan. Silakan cek status pesanan Anda.</p>`;
            // Menonaktifkan form upload
            document.getElementById('bukti_transfer').disabled = true;
            document.querySelector('button[type="submit"]').disabled = true;
            return;
        }

        const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((distance % (1000 * 60)) / 1000);

        // Format agar selalu 2 digit (misal: 04:09)
        countdownElement.innerHTML = `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
    }, 1000);
});
</script>
<?= $this->endSection() ?>
