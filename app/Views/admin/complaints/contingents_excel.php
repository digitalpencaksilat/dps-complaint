<table border="1">
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
      <tr>
        <td><?= $index + 1 ?></td>
        <td><?= esc($row['event_name']) ?></td>
        <td><?= esc($row['contingent_name']) ?></td>
        <td><?= esc($row['source_contingent_id']) ?></td>
        <td><?= esc($row['status_label']) ?></td>
        <td><?= esc($row['confirmation_code']) ?></td>
        <td><?= esc($row['official_name']) ?></td>
        <td><?= esc($row['official_phone']) ?></td>
        <td><?= esc($row['confirmed_at']) ?></td>
        <td><?= ! empty($row['signature_image']) ? 'Ada' : '-' ?></td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>
