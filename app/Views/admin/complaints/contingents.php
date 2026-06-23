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
$exportTitle = 'KONFIRMASI KONTINGEN';
$exportSubtitle = $selectedEventName;
$exportFilename = 'Konfirmasi Kontingen - ' . $selectedEventName;
?>

<div class="row g-3 mb-3">
  <?php foreach(['total' => 'Total Kontingen', 'confirmed' => 'Tidak Ada Complain', 'unconfirmed' => 'Tidak Ada Konfirmasi'] as $key => $label): ?>
    <div class="col-md-4">
      <div class="stat-card stat-card-<?= esc(str_replace('_', '-', $key)) ?>">
        <small><?= $label ?></small>
        <div class="h3"><?= esc($counts[$key] ?? 0) ?></div>
      </div>
    </div>
  <?php endforeach; ?>
</div>

<div class="admin-card mb-3">
  <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
    <div>
      <h2 class="section-title h4 mb-1">Konfirmasi Kontingen</h2>
      <div class="text-muted small">Rekap kontingen hasil sync dan status konfirmasi Tidak Ada Complain.</div>
    </div>
  </div>

  <form class="row g-2">
    <div class="col-md-3">
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
        <option value="confirmed" <?= ($filters['status'] ?? '') === 'confirmed' ? 'selected' : '' ?>>Tidak Ada Complain</option>
        <option value="unconfirmed" <?= ($filters['status'] ?? '') === 'unconfirmed' ? 'selected' : '' ?>>Tidak Ada Konfirmasi</option>
      </select>
    </div>
    <div class="col-md-4">
      <input class="form-control" name="contingent" value="<?= esc($filters['contingent'] ?? '') ?>" placeholder="Cari kontingen">
    </div>
    <div class="col-md-2 d-flex gap-2">
      <button class="btn btn-danger rounded-pill flex-fill">Filter</button>
      <a class="btn btn-outline-secondary rounded-pill" href="<?= base_url('admin/complaints/contingents') ?>">Reset</a>
    </div>
  </form>
</div>

<div class="admin-card">
  <div class="admin-table-wrap">
    <table class="table table-bordered align-middle admin-report-table" id="tabelKonfirmasiKontingen">
      <thead>
        <tr>
          <th>No</th>
          <th>Kejuaraan</th>
          <th>Kontingen</th>
          <th>ID Sync</th>
          <th>Status</th>
          <th>Kode Konfirmasi</th>
          <th>Official</th>
          <th>Nomor Official</th>
          <th>Dikonfirmasi</th>
          <th>Tanda Tangan</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach($rows as $index => $row): ?>
          <?php
            $phoneRaw = trim((string)($row['official_phone'] ?? ''));
            $phoneDigits = preg_replace('/\D+/', '', $phoneRaw) ?? '';
            if (str_starts_with($phoneDigits, '0')) {
                $phoneDigits = '62' . substr($phoneDigits, 1);
            }
            $confirmed = ($row['status'] ?? '') === 'confirmed';
          ?>
          <tr>
            <td><?= $index + 1 ?></td>
            <td><?= esc($row['event_name']) ?></td>
            <td><?= esc($row['contingent_name']) ?></td>
            <td><?= esc($row['source_contingent_id']) ?></td>
            <td>
              <span class="badge <?= $confirmed ? 'bg-success' : 'bg-secondary' ?>">
                <?= esc($row['status_label']) ?>
              </span>
            </td>
            <td><?= esc($row['confirmation_code']) ?></td>
            <td><?= esc($row['official_name']) ?></td>
            <td>
              <?php if($phoneRaw !== '' && $phoneRaw !== '-' && $phoneDigits !== ''): ?>
                <a href="https://wa.me/<?= esc($phoneDigits, 'attr') ?>" target="_blank" rel="noopener" class="text-decoration-none text-danger fw-semibold">
                  <i class="fab fa-whatsapp me-1"></i><?= esc($phoneRaw) ?>
                </a>
              <?php else: ?>
                -
              <?php endif; ?>
            </td>
            <td><?= esc($row['confirmed_at']) ?></td>
            <td class="signature-export-cell" data-signature="<?= esc((string)($row['signature_image'] ?? ''), 'attr') ?>">
              <?php if(! empty($row['signature_image'])): ?>
                <img class="signature-export-img" src="<?= esc($row['signature_image']) ?>" alt="Tanda tangan official">
              <?php else: ?>
                -
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <?php if(empty($rows)): ?>
      <div class="datatable-empty-message text-center text-muted py-4">Data kontingen tidak ditemukan.</div>
    <?php endif; ?>
  </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
  $(document).ready(function() {
    window.initAdminExportTable('#tabelKonfirmasiKontingen', {
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
          B: 30,
          C: 30,
          D: 16,
          E: 22,
          F: 28,
          G: 28,
          H: 22,
          I: 22,
          J: 18,
        },
      },
      exportFormatBody: function(data, row, column, node) {
        if (node && node.classList && node.classList.contains('signature-export-cell')) {
          return node.dataset.signature ? 'Ada' : '-';
        }
        if (typeof data === 'string') {
          return data.replace(/<[^>]*>/g, '').replace(/&nbsp;/g, ' ').replace(/\s+/g, ' ').trim();
        }
        return data;
      },
      printFormatBody: function(data, row, column, node) {
        if (node && node.classList && node.classList.contains('signature-export-cell')) {
          var signature = node.dataset.signature || '';
          return signature ? '<img class="signature-print-img" src="' + signature + '" alt="Tanda tangan official">' : '-';
        }
        if (typeof data === 'string') {
          return data.replace(/<a[^>]*>/g, '').replace(/<\/a>/g, '').replace(/<i[^>]*><\/i>/g, '').replace(/&nbsp;/g, ' ').trim();
        }
        return data;
      },
      printCustomize: function(win) {
        window.dpsReportPrintCustomize(win, {
          watermark: {
            logoUrl: <?= json_encode(base_url('assets/img/dps-logo.png')) ?>,
            text: 'Powered by <strong>Digital Pencak Silat</strong> &copy; ' + new Date().getFullYear(),
          },
        });
        $(win.document.head).append('<style>.signature-print-img{width:58px;height:58px;object-fit:contain;display:block;margin:auto;border:1px solid #e3c9cb;padding:2px;background:#fff;}table.dps-data-table thead th:first-child,table.dps-data-table tbody td:first-child{white-space:nowrap!important;width:34px!important;min-width:34px!important;max-width:34px!important;text-align:center!important;}</style>');
      },
    });
  });
</script>
<?= $this->endSection() ?>
