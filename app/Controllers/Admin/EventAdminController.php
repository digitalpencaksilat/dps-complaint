<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\EventModel;
use App\Services\ParticipantSyncService;
use CodeIgniter\Encryption\Exceptions\EncryptionException;
use Config\Database;
use RuntimeException;

class EventAdminController extends BaseController
{
    private function guard() { if (! session('is_admin')) return redirect()->to('/admin/login'); return null; }

    public function index()
    {
        if ($r = $this->guard()) return $r;
        return view('admin/events/index', ['events' => (new EventModel())->orderBy('created_at', 'DESC')->findAll()]);
    }

    public function create()
    {
        if ($r = $this->guard()) return $r;
        return view('admin/events/form', ['event' => null]);
    }

    public function store()
    {
        if ($r = $this->guard()) return $r;
        (new EventModel())->insert($this->eventPayload());
        return redirect()->to('/admin/events')->with('success', 'Kejuaraan tersimpan.');
    }

    public function edit(int $id)
    {
        if ($r = $this->guard()) return $r;
        return view('admin/events/form', ['event' => (new EventModel())->find($id)]);
    }

    public function update(int $id)
    {
        if ($r = $this->guard()) return $r;
        (new EventModel())->update($id, $this->eventPayload());
        return redirect()->to('/admin/events')->with('success', 'Kejuaraan diperbarui.');
    }

    public function closeComplaints(int $id)
    {
        if ($r = $this->guard()) return $r;
        (new EventModel())->update($id, ['complaint_closed_at' => date('Y-m-d H:i:s'), 'complaint_closed_reason' => 'Ditutup manual oleh admin']);
        return redirect()->back()->with('success', 'Complain kejuaraan ditutup.');
    }

    public function sync(int $id)
    {
        if ($r = $this->guard()) return $r;
        try {
            $counts = (new ParticipantSyncService())->sync($id, false);
            return redirect()->back()->with(
                'success',
                sprintf(
                    'Sync data selesai. Kontingen: %s, Peserta Tanding: %s, Peserta Seni: %s.',
                    number_format((int) ($counts['contingents'] ?? 0), 0, ',', '.'),
                    number_format((int) ($counts['tanding'] ?? 0), 0, ',', '.'),
                    number_format((int) ($counts['seni'] ?? 0), 0, ',', '.')
                )
            );
        } catch (RuntimeException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function delete(int $id)
    {
        if ($r = $this->guard()) return $r;

        $eventModel = new EventModel();
        $event = $eventModel->find($id);
        if (! $event) {
            return redirect()->to('/admin/events')->with('error', 'Kejuaraan tidak ditemukan.');
        }

        $db = Database::connect();
        $reportIds = array_column(
            $db->table('complaint_reports')
                ->select('id')
                ->where('event_id', $id)
                ->get()
                ->getResultArray(),
            'id'
        );

        $counts = [
            'items' => 0,
            'histories' => 0,
            'reports' => 0,
            'confirmations' => 0,
            'participants' => 0,
            'contingents' => 0,
        ];

        $db->transStart();

        if ($reportIds !== []) {
            $db->table('complaint_items')->whereIn('complaint_report_id', $reportIds)->delete();
            $counts['items'] = $db->affectedRows();

            $db->table('complaint_status_histories')->whereIn('complaint_report_id', $reportIds)->delete();
            $counts['histories'] = $db->affectedRows();
        }

        $db->table('complaint_reports')->where('event_id', $id)->delete();
        $counts['reports'] = $db->affectedRows();

        $db->table('contingent_confirmations')->where('event_id', $id)->delete();
        $counts['confirmations'] = $db->affectedRows();

        $db->table('participants')->where('event_id', $id)->delete();
        $counts['participants'] = $db->affectedRows();

        $db->table('contingents')->where('event_id', $id)->delete();
        $counts['contingents'] = $db->affectedRows();

        $eventModel->delete($id);

        $db->transComplete();

        if (! $db->transStatus()) {
            return redirect()->to('/admin/events')->with('error', 'Gagal menghapus kejuaraan. Data tidak diubah.');
        }

        return redirect()->to('/admin/events')->with(
            'success',
            sprintf(
                'Kejuaraan "%s" dihapus beserta %d tiket, %d item complain, %d riwayat status, %d konfirmasi, %d peserta, dan %d kontingen.',
                $event['name'],
                $counts['reports'],
                $counts['items'],
                $counts['histories'],
                $counts['confirmations'],
                $counts['participants'],
                $counts['contingents']
            )
        );
    }

    private function eventPayload(): array
    {
        $password = (string)$this->request->getPost('source_db_password');
        $payload = [
            'name' => $this->request->getPost('name'),
            'slug' => url_title((string)$this->request->getPost('slug'), '-', true),
            'location' => $this->request->getPost('location'),
            'start_date' => $this->request->getPost('start_date') ?: null,
            'end_date' => $this->request->getPost('end_date') ?: null,
            'complaint_deadline' => $this->request->getPost('complaint_deadline') ?: null,
            'sla_hours' => (int)($this->request->getPost('sla_hours') ?: 24),
            'status' => $this->request->getPost('status') ?: 'draft',
            'source_db_host' => $this->request->getPost('source_db_host') ?: '127.0.0.1',
            'source_db_name' => $this->request->getPost('source_db_name') ?: 'db_testing_event',
            'source_db_username' => $this->request->getPost('source_db_username') ?: 'root',
        ];
        if ($password !== '') {
            try {
                $payload['source_db_password_encrypted'] = base64_encode(service('encrypter')->encrypt($password));
            } catch (EncryptionException $e) {
                throw new RuntimeException('Encryption key belum dikonfigurasi. Jalankan php spark key:generate sebelum menyimpan password DB sumber.', 0, $e);
            }
        }

        return $payload;
    }
}
