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
          <th class="text-end">Aksi</th>
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
            <td class="text-end text-uppercase">
              <div class="dropdown">
                <button class="btn btn-sm btn-danger rounded-pill dropdown-toggle px-3" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                  Aksi
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                  <li>
                    <a class="dropdown-item" href="<?= base_url('admin/events/'.$event['id'].'/edit') ?>">
                      <i class="fas fa-pen-to-square me-2"></i>Edit
                    </a>
                  </li>
                  <li>
                    <form method="post" action="<?= base_url('admin/events/'.$event['id'].'/sync') ?>">
                      <?= csrf_field() ?>
                      <button class="dropdown-item" type="submit">
                        <i class="fas fa-rotate me-2"></i>Sync
                      </button>
                    </form>
                  </li>
                  <li>
                    <form
                      method="post"
                      action="<?= base_url('admin/events/'.$event['id'].'/close-complaints') ?>"
                      data-confirm="true"
                      data-confirm-title="Tutup complain kejuaraan?"
                      data-confirm-text="Form complain untuk <?= esc($event['name'], 'attr') ?> akan ditutup manual. Peserta tidak bisa submit complain baru untuk kejuaraan ini."
                      data-confirm-button="Tutup"
                    >
                      <?= csrf_field() ?>
                      <button class="dropdown-item" type="submit">
                        <i class="fas fa-lock me-2"></i>Tutup
                      </button>
                    </form>
                  </li>
                  <li><hr class="dropdown-divider"></li>
                  <li>
                    <form
                      method="post"
                      action="<?= base_url('admin/events/'.$event['id'].'/delete') ?>"
                      data-confirm="true"
                      data-confirm-title="Hapus kejuaraan?"
                      data-confirm-text="Kejuaraan <?= esc($event['name'], 'attr') ?> beserta semua data complain, peserta, kontingen, dan konfirmasi terkait akan dihapus permanen."
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
  </div>
</div>

<?= $this->endSection() ?>
