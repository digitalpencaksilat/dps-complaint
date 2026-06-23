<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title>Print Konfirmasi Kontingen</title>
  <style>
    body { font-family: Arial, sans-serif; color: #111; }
    h1 { margin: 0 0 4px; font-size: 20px; text-transform: uppercase; }
    .meta { margin-bottom: 14px; color: #555; font-size: 12px; }
    table { width: 100%; border-collapse: collapse; font-size: 11px; }
    th, td { border: 1px solid #999; padding: 6px; vertical-align: top; }
    th { background: #f1f1f1; text-transform: uppercase; }
    .badge { display: inline-block; padding: 3px 6px; border-radius: 999px; color: #fff; font-size: 10px; font-weight: bold; }
    .ok { background: #198754; }
    .empty { background: #6c757d; }
    .signature { max-width: 120px; max-height: 56px; display: block; margin-top: 4px; border: 1px solid #ddd; }
    .signature-cell { min-width: 130px; }
    @media print { .no-print { display: none; } body { margin: 0; } }
  </style>
</head>
<body>
  <button class="no-print" onclick="window.print()">Print</button>
  <h1>Konfirmasi Kontingen</h1>
  <div class="meta">Dicetak: <?= esc($generatedAt) ?></div>

  <table>
    <thead>
      <tr>
        <th>No</th>
        <th>Kejuaraan</th>
        <th>Kontingen</th>
        <th>ID Sync</th>
        <th>Status</th>
        <th>Kode Konfirmasi</th>
        <th>Official & Bukti TTD</th>
        <th>Dikonfirmasi</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach($rows as $index => $row): ?>
        <?php $confirmed = ($row['status'] ?? '') === 'confirmed'; ?>
        <tr>
          <td><?= $index + 1 ?></td>
          <td><?= esc($row['event_name']) ?></td>
          <td><?= esc($row['contingent_name']) ?></td>
          <td><?= esc($row['source_contingent_id']) ?></td>
          <td><span class="badge <?= $confirmed ? 'ok' : 'empty' ?>"><?= esc($row['status_label']) ?></span></td>
          <td><?= esc($row['confirmation_code']) ?></td>
          <td class="signature-cell">
            <?= esc($row['official_name']) ?><br>
            <?= esc($row['official_phone']) ?>
            <?php if(! empty($row['signature_image'])): ?>
              <img class="signature" src="<?= esc($row['signature_image']) ?>" alt="Tanda tangan">
            <?php endif; ?>
          </td>
          <td><?= esc($row['confirmed_at']) ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <script>window.addEventListener('load', () => window.print());</script>
</body>
</html>
