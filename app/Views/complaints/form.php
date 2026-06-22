<?= $this->extend('layouts/public') ?>
<?= $this->section('content') ?>

<?php
$oldItems = old('items') ?: [[
    'complaint_type' => 'name_error',
    'participant_id' => '',
    'participant_label' => '',
    'contingent_id' => '',
    'contingent_label' => '',
    'description' => '',
]];
?>

<section class="public-shell">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-xxl-11">
        <div class="complaint-card complaint-form-card">
          <div class="complaint-card-header complaint-hero">
            <div>
              <div class="eyebrow mb-2">Layanan Koreksi Data Kejuaraan</div>
              <h1 class="complaint-title mb-2">Form Complain Peserta</h1>
              <p class="mb-0">
                Pilih kejuaraan dulu. Setelah itu form complain dan data official akan muncul.
              </p>
            </div>
            <div class="hero-badge">
              <i class="fas fa-shield-alt"></i>
              <span>Data import panitia</span>
            </div>
          </div>

          <div class="card-body p-3 p-lg-5">
            <?php if(session('error')): ?>
              <div class="alert alert-danger rounded-4 mb-4"><?= esc(session('error')) ?></div>
            <?php endif; ?>

            <form
              method="post"
              action="<?= base_url('complaints') ?>"
              id="complaintForm"
              data-participant-url="<?= base_url('api/participants/search') ?>"
              data-contingent-url="<?= base_url('api/contingents/search') ?>"
            >
              <?= csrf_field() ?>

              <div class="form-step-card mb-4">
                <div class="step-marker">1</div>
                <div class="flex-grow-1">
                  <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                    <div>
                      <h3 class="h5 mb-1">Pilih Kejuaraan</h3>
                      <p class="text-muted mb-0 small">
                        Form berikutnya dibuka setelah kejuaraan dipilih agar data peserta dan kontingen tidak tertukar.
                      </p>
                    </div>
                  </div>

                  <select class="form-select form-select-lg" name="event_id" required>
                    <option value="">Pilih kejuaraan</option>
                    <?php foreach($events as $event): ?>
                      <option value="<?= esc($event['id']) ?>" <?= old('event_id') == $event['id'] ? 'selected' : '' ?>>
                        <?= esc($event['name']) ?><?= $event['complaint_deadline'] ? ' — Complain sampai '.esc($event['complaint_deadline']) : '' ?>
                      </option>
                    <?php endforeach; ?>
                  </select>

                  <div class="event-helper mt-3">
                    <i class="fas fa-circle-info me-1"></i>Pilih kejuaraan dulu untuk membuka daftar item complain.
                  </div>
                </div>
              </div>

              <div id="complaintDetails" class="complaint-details <?= old('event_id') ? '' : 'd-none' ?>">
                <div class="form-step-card mb-4">
                  <div class="step-marker">2</div>
                  <div class="flex-grow-1">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
                      <div>
                        <h3 class="h5 mb-1">Daftar Item Complain</h3>
                        <p class="text-muted mb-0 small">
                          Tambah item jika official komplain lebih dari satu peserta atau masalah.
                        </p>
                      </div>
                      <button type="button" class="btn btn-outline-danger rounded-pill" id="addComplaintItem">
                        <i class="fas fa-plus me-1"></i>Tambah Item
                      </button>
                    </div>

                    <div id="complaintItems" class="complaint-items">
                      <?php foreach($oldItems as $i => $item): ?>
                        <?php $type = $item['complaint_type'] ?? 'name_error'; ?>
                        <div class="complaint-item" data-index="<?= (int)$i ?>">
                          <div class="complaint-item-head">
                            <div>
                              <span class="item-count">Complain #<?= (int)$i + 1 ?></span>
                              <h4 class="h6 mb-0">Data yang dikomplain</h4>
                            </div>
                            <button
                              type="button"
                              class="btn btn-sm btn-outline-secondary rounded-pill remove-item <?= count($oldItems) === 1 ? 'd-none' : '' ?>"
                            >
                              Hapus
                            </button>
                          </div>

                          <div class="row g-3">
                            <div class="col-lg-4">
                              <label class="form-label">Jenis Complain</label>
                              <select
                                class="form-select complaint-type"
                                name="items[<?= (int)$i ?>][complaint_type]"
                                required
                              >
                                <option value="name_error" <?= $type === 'name_error' ? 'selected' : '' ?>>Kesalahan Nama</option>
                                <option value="gender_error" <?= $type === 'gender_error' ? 'selected' : '' ?>>Kesalahan Jenis Kelamin</option>
                                <option value="category_error" <?= $type === 'category_error' ? 'selected' : '' ?>>Kesalahan Kategori Yang Diikuti</option>
                                <option value="missing_participant" <?= $type === 'missing_participant' ? 'selected' : '' ?>>Tidak Ada Peserta</option>
                              </select>
                            </div>

                            <div class="col-lg-8">
                              <div class="entity-search participant-search-box">
                                <label class="form-label">Cari Peserta</label>
                                <input
                                  type="text"
                                  class="form-control search-input participant-search"
                                  name="items[<?= (int)$i ?>][participant_label]"
                                  value="<?= esc($item['participant_label'] ?? '') ?>"
                                  placeholder="Ketik minimal 2 huruf nama peserta atau kontingen"
                                  autocomplete="off"
                                >
                                <input
                                  type="hidden"
                                  name="items[<?= (int)$i ?>][participant_id]"
                                  value="<?= esc($item['participant_id'] ?? '') ?>"
                                  class="participant-id"
                                >
                                <div class="search-results mt-2 participant-results"></div>
                              </div>

                              <div class="entity-search contingent-search-box d-none">
                                <label class="form-label">Cari Kontingen</label>
                                <input
                                  type="text"
                                  class="form-control search-input contingent-search"
                                  name="items[<?= (int)$i ?>][contingent_label]"
                                  value="<?= esc($item['contingent_label'] ?? '') ?>"
                                  placeholder="Ketik minimal 2 huruf nama kontingen"
                                  autocomplete="off"
                                >
                                <input
                                  type="hidden"
                                  name="items[<?= (int)$i ?>][contingent_id]"
                                  value="<?= esc($item['contingent_id'] ?? '') ?>"
                                  class="contingent-id"
                                >
                                <div class="search-results mt-2 contingent-results"></div>
                              </div>
                            </div>

                            <div class="col-12">
                              <label class="form-label">Keterangan</label>
                              <div class="description-helper mb-2 small"></div>
                              <textarea
                                class="form-control complaint-description"
                                name="items[<?= (int)$i ?>][description]"
                                rows="4"
                                minlength="10"
                                required
                                placeholder="Jelaskan complain dengan jelas."
                              ><?= esc($item['description'] ?? '') ?></textarea>
                            </div>
                          </div>
                        </div>
                      <?php endforeach; ?>
                    </div>
                  </div>
                </div>

                <div class="form-step-card mb-4">
                  <div class="step-marker">3</div>
                  <div class="flex-grow-1">
                    <h3 class="h5 mb-3">Data Official</h3>
                    <div class="row g-3">
                      <div class="col-md-6">
                        <label class="form-label">Nama Official</label>
                        <input class="form-control" name="official_name" value="<?= esc(old('official_name')) ?>" required>
                      </div>
                      <div class="col-md-6">
                        <label class="form-label">Nomor Telepon</label>
                        <input
                          class="form-control"
                          name="official_phone"
                          value="<?= esc(old('official_phone')) ?>"
                          placeholder="08xxxxxxxxxx"
                          required
                        >
                      </div>
                    </div>

                    <div class="mt-4">
                      <label class="form-label">Tanda Tangan Official</label>
                      <p class="text-muted small">
                        Tanda tangan langsung pada kotak di bawah ini memakai jari atau mouse.
                      </p>
                      <canvas id="signatureCanvas"></canvas>
                      <input type="hidden" name="signature_image" id="signatureInput" required>
                      <button type="button" class="btn btn-outline-danger rounded-pill mt-2" id="clearSignature">
                        Hapus Tanda Tangan
                      </button>
                    </div>
                  </div>
                </div>

                <div class="submit-bar">
                  <div>
                    <strong>Pastikan semua data sudah terisi.</strong>
                    <div class="small text-muted">Tiket complain dibuat setelah form lengkap dan tersimpan.</div>
                  </div>
                  <button class="btn btn-dps px-5 py-3" type="submit">Simpan Complain</button>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script src="<?= base_url('assets/js/complaint-form.js') ?>"></script>
<script src="<?= base_url('assets/js/signature-pad.js') ?>"></script>
<?= $this->endSection() ?>
