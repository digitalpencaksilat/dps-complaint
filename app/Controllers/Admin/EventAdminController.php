<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\EventModel;
use App\Services\ParticipantSyncService;

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
        $counts = (new ParticipantSyncService())->sync($id, (bool)$this->request->getGet('fresh'));
        return redirect()->back()->with('success', 'Sync selesai: ' . json_encode($counts));
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
        if ($password !== '') $payload['source_db_password_encrypted'] = base64_encode($password);
        return $payload;
    }
}
