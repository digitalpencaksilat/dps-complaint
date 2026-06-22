<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<div class="admin-card mb-3">
  <div class="row g-3">
    <div class="col-md-6">
      <h2 class="section-title h4"><?= esc($report['ticket_code']) ?></h2>
      <p class="mb-1">Kejuaraan: <?= esc($report['event_name']) ?></p>
      <p class="mb-1">Official: <?= esc($report['official_name']) ?> — <?= esc($report['official_phone']) ?></p>
      <p class="mb-1">Status: <span class="badge <?= esc(status_badge_class($report['status'])) ?>"><?= esc(status_label($report['status'])) ?></span></p>
      <p class="mb-0">Batas Proses: <?= esc($report['sla_due_at']) ?></p>
    </div>

    <div class="col-md-6">
      <h3 class="h6">Tanda Tangan Official</h3>
      <?php if($report['signature_image']): ?>
        <img class="signature-preview img-fluid" src="<?= esc($report['signature_image']) ?>" alt="Tanda tangan">
      <?php endif; ?>
    </div>
  </div>
</div>

<div class="admin-card mb-3">
  <h3 class="section-title h5">Item Complain</h3>

  <?php foreach($items as $item): ?>
    <?php $subject = complaint_item_subject($item); ?>
    <div class="border rounded-3 p-3 mb-3">
      <div class="mb-3">
        <div class="small text-muted">Jenis Complain</div>
        <strong><?= esc(complaint_type_label($item['complaint_type'])) ?></strong>
      </div>

      <?php if(! empty($subject['rows'])): ?>
        <div class="row g-2 mb-3">
          <?php foreach($subject['rows'] as $label => $value): ?>
            <div class="col-md-6 col-xl-4">
              <div class="bg-light rounded-3 p-2 h-100">
                <div class="small text-muted"><?= esc($label === 'Kontingen' ? 'Nama Kontingen' : $label) ?></div>
                <div class="fw-semibold"><?= esc($value ?: '-') ?></div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <div class="border-top pt-3">
        <div class="small text-muted">Keterangan Complain</div>
        <p class="mb-0"><?= esc($item['description']) ?></p>
      </div>
    </div>
  <?php endforeach; ?>
</div>

<div class="admin-card mb-3">
  <h3 class="section-title h5">Update Status</h3>
  <form method="post" action="<?= base_url('admin/complaints/'.$report['id'].'/status') ?>">
    <?= csrf_field() ?>
    <div class="row g-3">
      <div class="col-md-3">
        <select class="form-select" name="status">
          <?php foreach(['baru', 'diproses', 'perlu_konfirmasi', 'selesai', 'ditolak'] as $status): ?>
            <option value="<?= $status ?>" <?= $report['status'] === $status ? 'selected' : '' ?>>
              <?= status_label($status) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-4">
        <input class="form-control" name="note" placeholder="Catatan internal">
      </div>
      <div class="col-md-4">
        <input class="form-control" name="public_note" placeholder="Catatan public">
      </div>
      <div class="col-md-1">
        <button class="btn btn-danger">OK</button>
      </div>
    </div>
  </form>
</div>

<div class="admin-card">
  <h3 class="section-title h5">Timeline Status</h3>
  <?php foreach($histories as $history): ?>
    <div class="border-start border-danger ps-3 pb-3">
      <strong><span class="badge <?= esc(status_badge_class($history['new_status'])) ?>"><?= esc(status_label($history['new_status'])) ?></span></strong>
      <div class="small text-muted"><?= esc($history['changed_at']) ?></div>
      <div><?= esc($history['note'] ?: $history['public_note']) ?></div>
    </div>
  <?php endforeach; ?>
</div>

<?= $this->endSection() ?>
