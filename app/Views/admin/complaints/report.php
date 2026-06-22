<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<?php
$queryBase = array_filter($filters ?? [], static fn($value) => $value !== null && $value !== '');
$page = (int)($pagination['page'] ?? 1);
$totalPages = (int)($pagination['totalPages'] ?? 1);
$totalRows = (int)($pagination['totalRows'] ?? 0);
$offset = (int)($pagination['offset'] ?? 0);
$selectedEventName = 'Semua Kejuaraan';
foreach (($events ?? []) as $event) {
    if ((string)($filters['event_id'] ?? '') === (string)$event['id']) {
        $selectedEventName = (string)$event['name'];
        break;
    }
}
$exportTitle = 'REKAP COMPLAIN LENGKAP';
$exportSubtitle = $selectedEventName;
$exportFilename = 'Rekap Complain Lengkap - ' . $selectedEventName;
?>

<div class="admin-card mb-3">
  <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
    <div>
      <h2 class="section-title h4 mb-1">Rekap Complain Lengkap</h2>
      <div class="text-muted small">Data ditampilkan per item complain berdasarkan tiket dan kontingen.</div>
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
    <div class="col-md-2">
      <select class="form-select" name="status">
        <option value="">Semua Status</option>
        <?php foreach(['baru', 'diproses', 'perlu_konfirmasi', 'selesai', 'ditolak'] as $status): ?>
          <option value="<?= $status ?>" <?= ($filters['status'] ?? '') === $status ? 'selected' : '' ?>>
            <?= esc(status_label($status)) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-2">
      <input class="form-control" name="ticket" value="<?= esc($filters['ticket'] ?? '') ?>" placeholder="Cari tiket">
    </div>
    <div class="col-md-3">
      <input class="form-control" name="contingent" value="<?= esc($filters['contingent'] ?? '') ?>" placeholder="Cari kontingen">
    </div>
    <div class="col-md-2 d-flex gap-2">
      <button class="btn btn-danger rounded-pill flex-fill">Filter</button>
      <a class="btn btn-outline-secondary rounded-pill" href="<?= base_url('admin/complaints/report') ?>">Reset</a>
    </div>
  </form>
</div>

<div class="admin-card">
  <div class="admin-table-wrap">
    <table class="table table-bordered align-middle admin-report-table" id="tabelRekapComplain">
      <thead>
        <tr>
          <th>No</th>
          <th>Tiket</th>
          <th>Kejuaraan</th>
          <th>Kontingen</th>
          <th>Peserta</th>
          <th>Kategori Pertandingan</th>
          <th>Jenis Complain</th>
          <th>Keterangan</th>
          <th>Status</th>
          <th>Official</th>
          <th>Nomor Official</th>
          <th>Tanda Tangan</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach($rows as $index => $row): ?>
          <?php
            $subject = complaint_item_subject($row);
            $phoneRaw = trim((string)($row['official_phone'] ?? ''));
            $phoneDigits = preg_replace('/\D+/', '', $phoneRaw) ?? '';
            if (str_starts_with($phoneDigits, '0')) {
                $phoneDigits = '62' . substr($phoneDigits, 1);
            }
          ?>
          <tr>
            <td><?= $offset + $index + 1 ?></td>
            <td><?= esc($row['ticket_code']) ?></td>
            <td><?= esc($row['event_name']) ?></td>
            <td><?= esc($subject['rows']['Kontingen'] ?? '-') ?></td>
            <td><?= esc($subject['rows']['Nama Peserta'] ?? '-') ?></td>
            <td><?= esc($subject['rows']['Kategori Pertandingan'] ?? '-') ?></td>
            <td><?= esc(complaint_type_label($row['complaint_type'])) ?></td>
            <td><?= esc($row['description']) ?></td>
            <td><span class="badge <?= esc(status_badge_class($row['status'])) ?>"><?= esc(status_label($row['status'])) ?></span></td>
            <td><?= esc($row['official_name']) ?></td>
            <td>
              <?php if($phoneRaw !== '' && $phoneDigits !== ''): ?>
                <a href="https://wa.me/<?= esc($phoneDigits, 'attr') ?>" target="_blank" rel="noopener" class="text-decoration-none text-danger fw-semibold">
                  <i class="fab fa-whatsapp me-1"></i><?= esc($phoneRaw) ?>
                </a>
              <?php else: ?>
                -
              <?php endif; ?>
            </td>
            <td class="signature-export-cell" data-signature="<?= esc((string)($row['signature_image'] ?? ''), 'attr') ?>">
              <?php if(! empty($row['signature_image'])): ?>
                <img class="signature-export-img" src="<?= esc($row['signature_image']) ?>" alt="Tanda tangan official">
              <?php else: ?>
                -
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if(empty($rows)): ?>
          <tr>
            <td colspan="12" class="text-center text-muted py-4">Data complain tidak ditemukan.</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mt-3 d-none">
    <div class="small text-muted">
      Menampilkan <?= count($rows) ?> dari <?= esc($totalRows) ?> item complain. Maksimal 10 data per halaman.
    </div>

    <?php if($totalPages > 1): ?>
      <nav aria-label="Pagination Rekap Complain">
        <ul class="pagination">
          <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
            <a class="page-link" href="<?= current_url() . '?' . http_build_query(array_merge($queryBase, ['page_report' => max(1, $page - 1)])) ?>">‹</a>
          </li>

          <?php for($pageNumber = 1; $pageNumber <= $totalPages; $pageNumber++): ?>
            <li class="page-item <?= $pageNumber === $page ? 'active' : '' ?>">
              <a class="page-link" href="<?= current_url() . '?' . http_build_query(array_merge($queryBase, ['page_report' => $pageNumber])) ?>">
                <?= $pageNumber ?>
              </a>
            </li>
          <?php endfor; ?>

          <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
            <a class="page-link" href="<?= current_url() . '?' . http_build_query(array_merge($queryBase, ['page_report' => min($totalPages, $page + 1)])) ?>">›</a>
          </li>
        </ul>
      </nav>
    <?php endif; ?>
  </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
  $(document).ready(function() {
    window.initAdminExportTable('#tabelRekapComplain', {
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
          D: 26,
          E: 28,
          F: 34,
          G: 24,
          H: 42,
          I: 18,
          J: 28,
          K: 22,
          L: 18,
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
        $(win.document.head).append('<style>.signature-print-img{width:58px;height:58px;object-fit:contain;display:block;margin:auto;border:1px solid #e3c9cb;padding:2px;background:#fff;}table.dps-data-table thead th:first-child,table.dps-data-table tbody td:first-child{white-space:nowrap!important;width:34px!important;min-width:34px!important;max-width:34px!important;text-align:center!important;}table.dps-data-table tbody td:nth-child(12){text-align:center!important;min-width:70px;}</style>');
      },
    });
  });
</script>
<?= $this->endSection() ?>
