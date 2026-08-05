<?= $this->extend('admin/layout/main') ?>
<?= $this->section('title') ?>Gratis Ongkir - <?= esc($store['name'] ?? 'Admin') ?><?= $this->endSection() ?>
<?= $this->section('content') ?>
<div class="container-fluid py-3">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3>Aturan Gratis Ongkir</h3>
    <a href="<?= base_url('admin/free-shipping/create') ?>" class="btn btn-sm btn-primary">Tambah Rule</a>
  </div>

  <?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success"><?= esc(session('success')) ?></div>
  <?php endif; ?>
  <?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger"><?= esc(session('error')) ?></div>
  <?php endif; ?>

  <div class="table-responsive">
    <table class="table table-striped align-middle">
      <thead>
        <tr>
          <th>#</th>
          <th>Cakupan Produk</th>
          <th>Masa Berlaku</th>
          <th>Min Subtotal</th>
          <th>Max Jarak</th>
          <th>Status</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!empty($rules)): $no=1; foreach ($rules as $r): ?>
        <tr>
          <td><?= $no++ ?></td>
          <td>
    <?php if($r['apply_to_all']): ?>
        <span class="badge bg-primary">Semua Produk</span>
    <?php else: ?>
        <span class="badge bg-info text-dark">Produk Tertentu</span>
        <div class="mt-1" style="max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="<?= esc($r['product_ids']) ?>">
            <small class="text-muted">
                <strong>ID:</strong> <?= esc($r['product_ids']) ?>
            </small>
        </div>
        <?php 
            $count = count(explode(',', $r['product_ids']));
            echo "<small class='text-primary' style='font-size: 0.7rem;'>($count Produk)</small>";
        ?>
    <?php endif; ?>
</td>
          <td>
            <small>
                <strong>Mulai:</strong> <?= date('d/m/Y H:i', strtotime($r['start_date'])) ?><br>
                <strong>Selesai:</strong> <?= date('d/m/Y H:i', strtotime($r['end_date'])) ?>
            </small>
            <?php 
                // Logika sederhana untuk cek apakah promo sudah expired atau belum mulai
                $now = time();
                $start = strtotime($r['start_date']);
                $end = strtotime($r['end_date']);
                if ($now < $start) echo '<br><span class="badge bg-warning text-dark">Belum Mulai</span>';
                if ($now > $end) echo '<br><span class="badge bg-danger">Expired</span>';
            ?>
          </td>
          <td>
            Rp<?= number_format($r['min_amount'], 0, ',', '.') ?>
            <?php if($r['max_amount']): ?>
                <br><small class="text-muted">Max: Rp<?= number_format($r['max_amount'], 0, ',', '.') ?></small>
            <?php endif; ?>
          </td>
          <td><?= $r['max_distance_km'] === null ? '-' : number_format($r['max_distance_km'], 1, ',', '.') . ' km' ?></td>
          <td>
            <span class="badge <?= $r['is_active'] ? 'bg-success' : 'bg-secondary' ?>"><?= $r['is_active'] ? 'Aktif' : 'Nonaktif' ?></span>
          </td>
          <td>
            <div class="btn-group">
                <a class="btn btn-sm btn-warning" href="<?= base_url('admin/free-shipping/edit/' . $r['rule_id']) ?>">Edit</a>
                <button class="btn btn-sm btn-info text-white" onclick="toggleStatus(<?= (int)$r['rule_id'] ?>)">Toggle</button>
                <form action="<?= base_url('admin/free-shipping/delete/' . $r['rule_id']) ?>" method="post" class="d-inline" onsubmit="return confirm('Hapus rule ini?')">
                  <?= csrf_field() ?>
                  <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                </form>
            </div>
          </td>
        </tr>
        <?php endforeach; else: ?>
          <tr><td colspan="7" class="text-center">Belum ada aturan.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<script>
function toggleStatus(id){
  fetch('<?= base_url('admin/free-shipping/toggle-status/') ?>'+id,{
    method:'POST',
    headers:{
        'X-Requested-With':'XMLHttpRequest',
        'Content-Type':'application/x-www-form-urlencoded'
    },
    body:'<?= csrf_token() ?>=<?= csrf_hash() ?>'
  })
  .then(r=>r.json())
  .then(j=>{
    if(j.status==='success'){
        location.reload()
    }else{
        alert(j.message||'Gagal')
    }
  })
  .catch(()=>alert('Gagal koneksi'))
}
</script>
<?= $this->endSection() ?>