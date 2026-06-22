<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<?php
$isEdit = ! empty($event);
$action = $isEdit ? base_url('admin/events/'.$event['id']) : base_url('admin/events');
?>

<div class="admin-card">
  <h2 class="section-title h4 mb-3"><?= $isEdit ? 'Edit' : 'Tambah' ?> Kejuaraan</h2>

  <form method="post" action="<?= $action ?>">
    <?= csrf_field() ?>

    <div class="row g-3">
      <div class="col-md-6">
        <label>Nama</label>
        <input class="form-control" name="name" value="<?= esc($event['name'] ?? '') ?>" required>
      </div>

      <div class="col-md-6">
        <label>Slug</label>
        <input class="form-control" name="slug" value="<?= esc($event['slug'] ?? '') ?>" required>
      </div>

      <div class="col-md-4">
        <label>Lokasi</label>
        <input class="form-control" name="location" value="<?= esc($event['location'] ?? '') ?>">
      </div>

      <div class="col-md-4">
        <label>Tanggal Mulai</label>
        <input type="date" class="form-control" name="start_date" value="<?= esc($event['start_date'] ?? '') ?>">
      </div>

      <div class="col-md-4">
        <label>Tanggal Selesai</label>
        <input type="date" class="form-control" name="end_date" value="<?= esc($event['end_date'] ?? '') ?>">
      </div>

      <div class="col-md-4">
        <label>Deadline Complain</label>
        <input
          type="datetime-local"
          class="form-control"
          name="complaint_deadline"
          value="<?= ! empty($event['complaint_deadline']) ? date('Y-m-d\TH:i', strtotime($event['complaint_deadline'])) : '' ?>"
        >
      </div>

      <div class="col-md-4">
        <label>Batas Proses (Jam)</label>
        <input type="number" class="form-control" name="sla_hours" value="<?= esc($event['sla_hours'] ?? 24) ?>">
      </div>

      <div class="col-md-4">
        <label>Status</label>
        <select class="form-select" name="status">
          <?php foreach(['draft', 'active', 'closed', 'archived'] as $status): ?>
            <option value="<?= $status ?>" <?= ($event['status'] ?? '') === $status ? 'selected' : '' ?>>
              <?= esc($status) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="col-md-3">
        <label>DB Host</label>
        <input class="form-control" name="source_db_host" value="<?= esc($event['source_db_host'] ?? '127.0.0.1') ?>">
      </div>

      <div class="col-md-3">
        <label>DB Name</label>
        <input class="form-control" name="source_db_name" value="<?= esc($event['source_db_name'] ?? 'db_testing_event') ?>">
      </div>

      <div class="col-md-3">
        <label>DB User</label>
        <input class="form-control" name="source_db_username" value="<?= esc($event['source_db_username'] ?? 'root') ?>">
      </div>

      <div class="col-md-3">
        <label>DB Password</label>
        <input type="password" class="form-control" name="source_db_password" placeholder="Kosongkan jika tidak diubah">
      </div>
    </div>

    <div class="text-end mt-4">
      <button class="btn btn-danger rounded-pill px-4">Simpan</button>
    </div>
  </form>
</div>

<?= $this->endSection() ?>
