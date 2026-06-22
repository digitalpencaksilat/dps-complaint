<?= $this->extend('layouts/public') ?>
<?= $this->section('content') ?>

<section class="public-shell">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-lg-7">
        <div class="complaint-card">
          <div class="complaint-card-header">
            <h1 class="complaint-title">Complain Tersimpan</h1>
            <p class="mb-0">Simpan nomor tiket untuk tracking status complain.</p>
          </div>

          <div class="card-body p-4">
            <p>Nomor tiket complain:</p>

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

            <a class="btn btn-dps" href="<?= base_url('complaints/track/' . rawurlencode($ticket)) ?>">
              Tracking Tiket
            </a>
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
