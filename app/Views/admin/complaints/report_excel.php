<html>
<head>
  <meta charset="utf-8">
</head>
<body>
  <table border="1">
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
        <th>Telepon</th>
        <th>Submitted At</th>
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
          <td><?= esc($row['official_name']) ?></td>
          <td><?= esc($row['official_phone']) ?></td>
          <td><?= esc($row['submitted_at']) ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</body>
</html>
