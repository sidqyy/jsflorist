<?= $this->extend('admin/layout/main') ?>

<?= $this->section('title') ?>
Custom Product Requests
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<h1 class="mb-4">Daftar Pesanan Custom</h1>

<?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= esc(session()->getFlashdata('success')) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>
<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= esc(session()->getFlashdata('error')) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<?php
    $status_classes = [
        'Baru' => ['bg-primary', 'bi-star-fill'],
        'Dikerjakan' => ['bg-info text-dark', 'bi-hammer'],
        'Selesai' => ['bg-success', 'bi-check-circle'],
        'Dibatalkan' => ['bg-danger', 'bi-x-circle'],
    ];
    $available_statuses = array_keys($status_classes);
?>

    <div class="table-responsive">
            <table class="table table-striped table-bordered">
        <thead class="bg-primary text-white">
            <tr>
                <th>Tanggal Request</th>
                <th>Pemesan</th>
                <th>Detail Pesanan</th>
                <th>Tgl Pengiriman</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($requests)): ?>
                <?php foreach ($requests as $request): ?>
                    <?php
                        $status = $request['request_status'];
                        $badgeClass = $status_classes[$status][0] ?? 'bg-secondary';
                        $iconClass = $status_classes[$status][1] ?? 'bi-question-circle';

                        $phoneNumber = esc($request['nomor_pemesan']);
                        $waNumber = preg_replace('/[^0-9]/', '', $phoneNumber);
                        if (substr($waNumber, 0, 1) === '0') {
                            $waNumber = '62' . substr($waNumber, 1);
                        }
                        $message = "Halo " . esc($request['nama_pemesan']) . ", kami dari JS Florist ingin mengkonfirmasi pesanan custom Anda: " . esc($request['item_type']) . " sejumlah " . esc($request['item_quantity']) . " untuk tanggal " . esc(date('d M Y', strtotime($request['delivery_date_requested']))) . ". Mohon konfirmasi ketersediaan dan detail selanjutnya.";
                        $waLink = "https://wa.me/" . $waNumber . "?text=" . urlencode($message);
                    ?>
                    <tr>
                        <td><?= esc(date('d M Y H:i', strtotime($request['created_at']))) ?></td>
                        <td>
                            <strong><?= esc($request['nama_pemesan']) ?></strong><br>
                            <small><?= esc($request['nomor_pemesan']) ?></small>
                        </td>
                        <td>
                            <strong>Tipe:</strong> <?= esc($request['item_type']) ?> (Qty: <?= esc($request['item_quantity']) ?>)<br>
                            <strong>Bunga:</strong> <?= esc($request['requested_flowers']) ?><br>
                            <strong>Catatan:</strong> <?= esc($request['additional_notes']) ?>
                        </td>
                        <td><?= esc(date('d M Y', strtotime($request['delivery_date_requested']))) ?></td>
                        <td>
                            <span class="badge <?= $badgeClass ?> text-white badge-status">
                                <i class="bi <?= $iconClass ?>"></i> <?= esc($status) ?>
                            </span>
                        </td>
                        <td>
                            <div class="d-flex flex-column flex-md-row gap-2">
                                <a href="<?= $waLink ?>" class="btn btn-sm btn-success" target="_blank">
                                    <i class="bi bi-whatsapp"></i> Hubungi
                                </a>
                                <form action="<?= base_url('admin/custom-requests/update-status/' . $request['id']) ?>" method="post" class="d-flex gap-1">
                                    <select name="request_status" class="form-select form-select-sm" style="width: 120px;">
                                        <?php foreach ($available_statuses as $s): ?>
                                            <option value="<?= $s ?>" <?= ($s == $request['request_status']) ? 'selected' : '' ?>>
                                                <?= $s ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button type="submit" class="btn btn-sm btn-outline-primary">Update</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" class="text-center">Tidak ada pesanan custom yang ditemukan.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<?= $this->endSection() ?>
