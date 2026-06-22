<?php

namespace App\Controllers;

use App\Models\ComplaintItemModel;
use App\Models\ComplaintReportModel;
use App\Models\ComplaintStatusHistoryModel;
use App\Services\RateLimitService;

class TrackingController extends BaseController
{
    public function form()
    {
        return view('complaints/track');
    }

    public function search()
    {
        $ticket = trim((string)$this->request->getPost('ticket_code'));
        return redirect()->to('/complaints/track/' . rawurlencode($ticket));
    }

    public function show(string $ticket)
    {
        if (! (new RateLimitService())->hit('track:' . $this->request->getIPAddress(), 20, 600)) {
            return $this->response->setStatusCode(429)->setBody('Terlalu banyak request tracking.');
        }
        $report = (new ComplaintReportModel())
            ->select('complaint_reports.*, events.name AS event_name')
            ->join('events', 'events.id = complaint_reports.event_id', 'left')
            ->where('ticket_code', $ticket)
            ->first();
        if (! $report) return view('complaints/track', ['error' => 'Tiket tidak ditemukan.']);
        $items = (new ComplaintItemModel())->where('complaint_report_id', $report['id'])->findAll();
        $histories = (new ComplaintStatusHistoryModel())->where('complaint_report_id', $report['id'])->orderBy('changed_at', 'ASC')->findAll();
        return view('complaints/track_detail', compact('report', 'items', 'histories'));
    }
}
