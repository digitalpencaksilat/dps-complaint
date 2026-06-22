<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title>Print Rekap Complain</title>
  <style>
    body { font-family: Arial, sans-serif; color: #111; }
    h1 { margin: 0 0 4px; font-size: 20px; text-transform: uppercase; }
    .meta { margin-bottom: 14px; color: #555; font-size: 12px; }
    table { width: 100%; border-collapse: collapse; font-size: 11px; }
    th, td { border: 1px solid #999; padding: 6px; vertical-align: top; }
    th { background: #f1f1f1; text-transform: uppercase; }
    .signature { max-width: 120px; max-height: 56px; display: block; margin-top: 4px; border: 1px solid #ddd; }
    .signature-cell { min-width: 130px; }
    @media print { .no-print { display: none; } body { margin: 0; } }
  </style>
</head>
<body>
  <button class="no-print" onclick="window.print()">Print</button>
  <h1>Rekap Complain Lengkap</h1>
  <div class="meta">Dicetak: <?= esc($generatedAt) ?></div>

  <table>
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
        <th>Official & Bukti TTD</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach($rows as $index => $row): ?>
        <?php $subject = complaint_item_subject($row); ?>
        <tr>
          <td><?= $index + 1 ?></td>
          <td><?= esc($row['ticket_code']) ?></td>
          <td><?= esc($row['event_name']) ?></td>
          <td><?= esc($subject['rows']['Kontingen'] ?? '-') ?></td>
          <td><?= esc($subject['rows']['Nama Peserta'] ?? '-') ?></td>
          <td><?= esc($subject['rows']['Kategori Pertandingan'] ?? '-') ?></td>
          <td><?= esc(complaint_type_label($row['complaint_type'])) ?></td>
          <td><?= esc($row['description']) ?></td>
          <td><?= esc(status_label($row['status'])) ?></td>
          <td class="signature-cell">
            <?= esc($row['official_name']) ?><br>
            <?= esc($row['official_phone']) ?>
            <?php if(! empty($row['signature_image'])): ?>
              <img class="signature" src="<?= esc($row['signature_image']) ?>" alt="Tanda tangan">
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <script>window.addEventListener('load', () => window.print());</script>
</body>
</html>
