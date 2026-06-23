<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<?php
$selectedEventName = 'Semua Kejuaraan';
foreach (($events ?? []) as $event) {
    if ((string)($filters['event_id'] ?? '') === (string)$event['id']) {
        $selectedEventName = (string)$event['name'];
        break;
    }
}
$exportTitle = 'DASHBOARD COMPLAIN';
$exportSubtitle = $selectedEventName;
$exportFilename = 'Dashboard Complain - ' . $selectedEventName;
?>

<div class="row g-3 mb-3">
  <?php foreach(['total' => 'Total', 'baru' => 'Baru', 'diproses' => 'Diproses', 'perlu_konfirmasi' => 'Perlu Konfirmasi', 'selesai' => 'Selesai', 'ditolak' => 'Ditolak'] as $key => $label): ?>
    <div class="col-md-2">
      <div class="stat-card stat-card-<?= esc(str_replace('_', '-', $key)) ?>">
        <small><?= $label ?></small>
        <div class="h3"><?= esc($counts[$key] ?? 0) ?></div>
      </div>
    </div>
  <?php endforeach; ?>
</div>

<div class="admin-card">
  <form class="row g-2 mb-3">
    <div class="col-md-4">
      <select class="form-select" name="event_id">
        <option value="">Semua Kejuaraan</option>
        <?php foreach($events as $event): ?>
          <option value="<?= $event['id'] ?>" <?= (string)($filters['event_id'] ?? '') === (string)$event['id'] ? 'selected' : '' ?>>
            <?= esc($event['name']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="col-md-3">
      <select class="form-select" name="status">
        <option value="">Semua Status</option>
        <?php foreach(['baru', 'diproses', 'perlu_konfirmasi', 'selesai', 'ditolak'] as $status): ?>
          <option value="<?= $status ?>" <?= ($filters['status'] ?? '') === $status ? 'selected' : '' ?>>
            <?= esc(status_label($status)) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="col-md-5 d-flex flex-wrap gap-2">
      <button class="btn btn-danger rounded-pill">Filter</button>
      <a class="btn btn-outline-secondary rounded-pill" href="<?= base_url('admin/complaints') ?>">Reset</a>
    </div>
  </form>

  <div class="admin-table-wrap">
    <table class="table align-middle admin-report-table" id="tabelDashboardComplain">
      <thead>
        <tr>
          <th>No</th>
          <th>Tiket</th>
          <th>Kejuaraan</th>
          <th>Official</th>
          <th>Status</th>
          <th>Batas Proses</th>
          <th>Dikirim</th>
          <th class="text-end no-export">Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach($reports as $index => $report): ?>
          <tr>
            <td class="text-center"><?= $index + 1 ?></td>
            <td><?= esc($report['ticket_code']) ?></td>
            <td><?= esc($report['event_name']) ?></td>
            <td>
              <?= esc($report['official_name']) ?><br>
              <small><?= esc($report['official_phone']) ?></small>
            </td>
            <td><span class="badge <?= esc(status_badge_class($report['status'])) ?>"><?= esc(status_label($report['status'])) ?></span></td>
            <td>
              <?= esc($report['sla_due_at']) ?>
              <?= strtotime($report['sla_due_at']) < time() && ! in_array($report['status'], ['selesai', 'ditolak'], true) ? ' <span class="badge bg-danger">Terlambat</span>' : '' ?>
            </td>
            <td><?= esc($report['submitted_at']) ?></td>
            <td class="text-end text-uppercase no-export">
              <div class="dropdown">
                <button class="btn btn-sm btn-danger rounded-pill dropdown-toggle px-3" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                  Aksi
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                  <li>
                    <a class="dropdown-item" href="<?= base_url('admin/complaints/'.$report['id']) ?>">
                      <i class="fas fa-eye me-2"></i>Detail
                    </a>
                  </li>
                  <li><hr class="dropdown-divider"></li>
                  <li>
                    <form
                      method="post"
                      action="<?= base_url('admin/complaints/'.$report['id'].'/delete') ?>"
                      data-confirm="true"
                      data-confirm-title="Hapus complain?"
                      data-confirm-text="Complain <?= esc($report['ticket_code'], 'attr') ?> akan dihapus. Data item dan riwayat status ikut terhapus."
                      data-confirm-button="Hapus"
                    >
                      <?= csrf_field() ?>
                      <button class="dropdown-item text-danger" type="submit">
                        <i class="fas fa-trash-alt me-2"></i>Hapus
                      </button>
                    </form>
                  </li>
                </ul>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <?php if(empty($reports)): ?>
      <div class="datatable-empty-message text-center text-muted py-4">Belum ada complain sesuai filter.</div>
    <?php endif; ?>
  </div>

</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
  $(document).ready(function() {
    window.initAdminExportTable('#tabelDashboardComplain', {
      title: <?= json_encode($exportTitle) ?>,
      filename: <?= json_encode($exportFilename) ?>,
      orientation: 'landscape',
      preset: 'wide-report',
      themedExport: true,
      excelUppercase: false,
      printHeader: {
        title: <?= json_encode($exportTitle) ?>,
        subtitle: <?= json_encode($exportSubtitle) ?>,
      },
      watermark: {
        logoUrl: <?= json_encode(base_url('assets/img/dps-logo.png')) ?>,
        text: 'Powered by <strong>Digital Pencak Silat</strong> &copy; ' + new Date().getFullYear(),
      },
      dataTable: {
        pageLength: 10,
        order: [],
      },
      excel: {
        columnWidths: {
          A: 8,
          B: 18,
          C: 28,
          D: 30,
          E: 18,
          F: 26,
          G: 24,
        },
      },
    });
  });
</script>
<?= $this->endSection() ?>
