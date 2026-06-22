<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\ComplaintItemModel;
use App\Models\ComplaintReportModel;
use App\Models\ComplaintStatusHistoryModel;
use App\Models\EventModel;
use App\Services\ComplaintStatusService;

class ComplaintAdminController extends BaseController
{
    private function guard()
    {
        if (! session('is_admin')) return redirect()->to('/admin/login');

        return null;
    }

    public function index()
    {
        if ($redirect = $this->guard()) return $redirect;

        $filters = [
            'event_id' => $this->request->getGet('event_id'),
            'status' => $this->request->getGet('status'),
        ];

        $reportModel = new ComplaintReportModel();

        return view('admin/complaints/index', [
            'reports' => $reportModel->withEvent($filters),
            'pager' => $reportModel->pager,
            'counts' => $reportModel->statusCounts(['event_id' => $filters['event_id'] ?? null]),
            'events' => (new EventModel())->orderBy('name', 'ASC')->findAll(),
            'filters' => $filters,
        ]);
    }

    public function report()
    {
        if ($redirect = $this->guard()) return $redirect;

        $filters = $this->reportFilters();
        $allRows = $this->reportRows($filters);
        $totalRows = count($allRows);

        return view('admin/complaints/report', [
            'rows' => $allRows,
            'events' => (new EventModel())->orderBy('name', 'ASC')->findAll(),
            'filters' => $filters,
            'pagination' => [
                'page' => 1,
                'perPage' => 10,
                'totalRows' => $totalRows,
                'totalPages' => 1,
                'offset' => 0,
            ],
            'printUrl' => current_url() . '/print?' . http_build_query(array_filter($filters)),
            'excelUrl' => current_url() . '/excel?' . http_build_query(array_filter($filters)),
        ]);
    }

    public function reportPrint()
    {
        if ($redirect = $this->guard()) return $redirect;

        $filters = $this->reportFilters();

        return view('admin/complaints/report_print', [
            'rows' => $this->reportRows($filters),
            'filters' => $filters,
            'generatedAt' => date('Y-m-d H:i:s'),
        ]);
    }

    public function reportExcel()
    {
        if ($redirect = $this->guard()) return $redirect;

        $rows = $this->reportRows($this->reportFilters());
        $filename = 'rekap-complain-' . date('Ymd-His') . '.xls';

        return $this->response
            ->setHeader('Content-Type', 'application/vnd.ms-excel; charset=UTF-8')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setBody(view('admin/complaints/report_excel', ['rows' => $rows]));
    }

    public function show(int $id)
    {
        if ($redirect = $this->guard()) return $redirect;

        $report = (new ComplaintReportModel())
            ->select('complaint_reports.*, events.name AS event_name')
            ->join('events', 'events.id = complaint_reports.event_id', 'left')
            ->find($id);

        return view('admin/complaints/show', [
            'report' => $report,
            'items' => (new ComplaintItemModel())->where('complaint_report_id', $id)->findAll(),
            'histories' => (new ComplaintStatusHistoryModel())->where('complaint_report_id', $id)->orderBy('changed_at', 'ASC')->findAll(),
        ]);
    }

    public function updateStatus(int $id)
    {
        if ($redirect = $this->guard()) return $redirect;

        (new ComplaintStatusService())->changeStatus(
            $id,
            (string) $this->request->getPost('status'),
            $this->request->getPost('note'),
            $this->request->getPost('public_note'),
            (int) session('admin_id'),
        );

        return redirect()->back()->with('success', 'Status diperbarui.');
    }

    public function export()
    {
        if ($redirect = $this->guard()) return $redirect;

        $reports = (new ComplaintReportModel())->withEvent([
            'event_id' => $this->request->getGet('event_id'),
            'status' => $this->request->getGet('status'),
        ]);
        $csv = "ticket,event,official,phone,status,submitted_at,sla_due_at\n";

        foreach ($reports as $report) {
            $csv .= sprintf(
                "%s,%s,%s,%s,%s,%s,%s\n",
                $report['ticket_code'],
                $report['event_name'],
                $report['official_name'],
                $report['official_phone'],
                $report['status'],
                $report['submitted_at'],
                $report['sla_due_at'],
            );
        }

        return $this->response
            ->setHeader('Content-Type', 'text/csv')
            ->setHeader('Content-Disposition', 'attachment; filename="complaints.csv"')
            ->setBody($csv);
    }

    private function reportFilters(): array
    {
        return [
            'event_id' => $this->request->getGet('event_id'),
            'status' => $this->request->getGet('status'),
            'ticket' => trim((string) $this->request->getGet('ticket')),
            'contingent' => trim((string) $this->request->getGet('contingent')),
        ];
    }

    private function reportRows(array $filters): array
    {
        $builder = (new ComplaintItemModel())
            ->select('complaint_items.*, complaint_reports.ticket_code, complaint_reports.official_name, complaint_reports.official_phone, complaint_reports.status, complaint_reports.submitted_at, complaint_reports.signature_image, events.name AS event_name')
            ->join('complaint_reports', 'complaint_reports.id = complaint_items.complaint_report_id', 'left')
            ->join('events', 'events.id = complaint_reports.event_id', 'left')
            ->orderBy('complaint_reports.submitted_at', 'DESC')
            ->orderBy('complaint_items.id', 'ASC');

        if (! empty($filters['event_id'])) {
            $builder->where('complaint_reports.event_id', (int) $filters['event_id']);
        }

        if (! empty($filters['status'])) {
            $builder->where('complaint_reports.status', $filters['status']);
        }

        if (! empty($filters['ticket'])) {
            $builder->like('complaint_reports.ticket_code', $filters['ticket']);
        }

        $rows = $builder->findAll();

        if (! empty($filters['contingent'])) {
            $needle = mb_strtolower($filters['contingent']);
            $rows = array_values(array_filter($rows, static function (array $row) use ($needle): bool {
                $subject = complaint_item_subject($row);
                $contingent = mb_strtolower((string) ($subject['rows']['Kontingen'] ?? ''));

                return str_contains($contingent, $needle);
            }));
        }

        return $rows;
    }
}
