<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<div class="admin-card">
  <div class="d-flex justify-content-between mb-3">
    <h2 class="section-title h4">Kejuaraan</h2>
    <a class="btn btn-danger rounded-pill" href="<?= base_url('admin/events/create') ?>">Tambah Kejuaraan</a>
  </div>

  <div class="table-responsive">
    <table class="table align-middle">
      <thead>
        <tr>
          <th>Nama</th>
          <th>Status</th>
          <th>Deadline</th>
          <th>Batas Proses</th>
          <th>DB Source</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach($events as $event): ?>
          <tr>
            <td><?= esc($event['name']) ?></td>
            <td><span class="badge <?= esc(status_badge_class($event['status'])) ?>"><?= esc($event['status']) ?></span></td>
            <td><?= esc($event['complaint_deadline']) ?></td>
            <td><?= esc($event['sla_hours']) ?> jam</td>
            <td><?= esc($event['source_db_name']) ?></td>
            <td>
              <a class="btn btn-sm btn-outline-danger" href="<?= base_url('admin/events/'.$event['id'].'/edit') ?>">Edit</a>
              <a class="btn btn-sm btn-outline-primary" href="<?= base_url('admin/events/'.$event['id'].'/sync') ?>">Sync</a>
              <form class="d-inline" method="post" action="<?= base_url('admin/events/'.$event['id'].'/close-complaints') ?>">
                <?= csrf_field() ?>
                <button class="btn btn-sm btn-outline-secondary">Tutup</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?= $this->endSection() ?>
