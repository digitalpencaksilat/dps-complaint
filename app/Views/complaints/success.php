<?= $this->extend('layouts/public') ?>
<?= $this->section('content') ?>

<?php $isConfirmation = ($type ?? '') === 'confirmation'; ?>

<section class="public-shell">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-lg-7">
        <div class="complaint-card">
          <div class="complaint-card-header">
            <h1 class="complaint-title"><?= $isConfirmation ? 'Konfirmasi Tersimpan' : 'Complain Tersimpan' ?></h1>
            <p class="mb-0">
              <?= $isConfirmation
                ? 'Simpan kode konfirmasi sebagai bukti data kontingen sudah sesuai.'
                : 'Simpan nomor tiket untuk tracking status complain.' ?>
            </p>
          </div>

          <div class="card-body p-4">
            <p><?= $isConfirmation ? 'Kode konfirmasi kontingen:' : 'Nomor tiket complain:' ?></p>

            <div class="ticket-copy-box mb-3">
              <div class="display-6 text-danger mb-0" id="ticketCode">
                <?= esc($ticket) ?>
              </div>
              <button
                type="button"
                class="btn btn-outline-danger rounded-pill"
                id="copyTicket"
                data-ticket="<?= esc($ticket) ?>"
              >
                <i class="fas fa-copy me-1"></i>Copy
              </button>
            </div>

            <div class="small text-muted mb-3" id="copyTicketStatus" aria-live="polite"></div>

            <?php if($isConfirmation): ?>
              <a class="btn btn-dps" href="<?= base_url('complaints') ?>">
                Kembali ke Form
              </a>
            <?php else: ?>
              <a class="btn btn-dps" href="<?= base_url('complaints/track/' . rawurlencode($ticket)) ?>">
                Tracking Tiket
              </a>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script src="<?= base_url('assets/js/ticket-copy.js') ?>"></script>
<?= $this->endSection() ?>
