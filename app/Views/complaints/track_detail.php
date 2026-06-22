<?= $this->extend('layouts/public') ?>
<?= $this->section('content') ?>

<?php $masked = preg_replace('/(\d{4})\d+(\d{3,4})/', '$1****$2', $report['official_phone']); ?>

<section class="public-shell">
  <div class="container">
    <div class="complaint-card">
      <div class="complaint-card-header">
        <h1 class="complaint-title">Tracking Tiket <?= esc($report['ticket_code']) ?></h1>
        <p class="mb-0"><?= esc($report['event_name']) ?></p>
      </div>

      <div class="card-body p-4">
        <div class="row g-3 mb-4">
          <div class="col-md-4">
            <div class="section-box">
              <small>Status</small>
              <div><span class="badge badge-status <?= esc(status_badge_class($report['status'])) ?>"><?= esc(status_label($report['status'])) ?></span></div>
            </div>
          </div>

          <div class="col-md-4">
            <div class="section-box">
              <small>Batas Proses</small>
              <div><?= esc($report['sla_due_at']) ?></div>
            </div>
          </div>

          <div class="col-md-4">
            <div class="section-box">
              <small>Official</small>
              <div><?= esc($report['official_name']) ?></div>
              <div class="phone-masked"><?= esc($masked) ?></div>
            </div>
          </div>
        </div>

        <h3 class="h5">Timeline</h3>
        <?php foreach($histories as $history): ?>
          <div class="timeline-item">
            <strong><span class="badge <?= esc(status_badge_class($history['new_status'])) ?>"><?= esc(status_label($history['new_status'])) ?></span></strong>
            <div class="small text-muted"><?= esc($history['changed_at']) ?></div>
            <?php if($history['public_note']): ?>
              <div><?= esc($history['public_note']) ?></div>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>

        <h3 class="h5 mt-4">Item Complain</h3>
        <?php foreach($items as $item): ?>
          <?php $subject = complaint_item_subject($item); ?>
          <div class="section-box mb-3">
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
    </div>
  </div>
</section>

<?= $this->endSection() ?>
