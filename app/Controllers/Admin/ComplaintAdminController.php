<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\ComplaintItemModel;
use App\Models\ComplaintReportModel;
use App\Models\ComplaintStatusHistoryModel;
use App\Models\ContingentConfirmationModel;
use App\Models\ContingentModel;
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

    public function contingents()
    {
        if ($redirect = $this->guard()) return $redirect;

        $filters = $this->contingentFilters();
        $rows = $this->contingentRows($filters);

        return view('admin/complaints/contingents', [
            'rows' => $rows,
            'events' => (new EventModel())->orderBy('name', 'ASC')->findAll(),
            'filters' => $filters,
            'counts' => $this->contingentCounts($rows),
        ]);
    }

    public function contingentsPrint()
    {
        if ($redirect = $this->guard()) return $redirect;

        $filters = $this->contingentFilters();

        return view('admin/complaints/contingents_print', [
            'rows' => $this->contingentRows($filters),
            'filters' => $filters,
            'generatedAt' => date('Y-m-d H:i:s'),
        ]);
    }

    public function contingentsExcel()
    {
        if ($redirect = $this->guard()) return $redirect;

        $rows = $this->contingentRows($this->contingentFilters());
        $filename = 'konfirmasi-kontingen-' . date('Ymd-His') . '.xls';

        return $this->response
            ->setHeader('Content-Type', 'application/vnd.ms-excel; charset=UTF-8')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setBody(view('admin/complaints/contingents_excel', ['rows' => $rows]));
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

    public function delete(int $id)
    {
        if ($redirect = $this->guard()) return $redirect;

        $reportModel = new ComplaintReportModel();
        $report = $reportModel->find($id);
        if (! $report) {
            return redirect()->back()->with('error', 'Data complain tidak ditemukan.');
        }

        $db = db_connect();
        $db->transStart();
        (new ComplaintItemModel())->where('complaint_report_id', $id)->delete();
        (new ComplaintStatusHistoryModel())->where('complaint_report_id', $id)->delete();
        $reportModel->delete($id);
        $db->transComplete();

        if (! $db->transStatus()) {
            return redirect()->back()->with('error', 'Gagal menghapus complain.');
        }

        return redirect()->to('/admin/complaints')->with('success', 'Complain ' . ($report['ticket_code'] ?? '') . ' berhasil dihapus.');
    }

    public function export()
    {
        if ($redirect = $this->guard()) return $redirect;

        $reports = (new ComplaintReportModel())->withEvent([
            'event_id' => $this->request->getGet('event_id'),
            'status' => $this->request->getGet('status'),
        ]);
        $handle = fopen('php://temp', 'r+');
        fputcsv($handle, ['ticket', 'event', 'official', 'phone', 'status', 'submitted_at', 'sla_due_at']);

        foreach ($reports as $report) {
            fputcsv($handle, [
                $report['ticket_code'] ?? '',
                $report['event_name'] ?? '',
                $report['official_name'] ?? '',
                $report['official_phone'] ?? '',
                $report['status'] ?? '',
                $report['submitted_at'] ?? '',
                $report['sla_due_at'] ?? '',
            ]);
        }

        rewind($handle);
        $csv = stream_get_contents($handle) ?: '';
        fclose($handle);

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

    private function contingentFilters(): array
    {
        return [
            'event_id' => $this->request->getGet('event_id'),
            'status' => $this->request->getGet('status'),
            'contingent' => trim((string) $this->request->getGet('contingent')),
        ];
    }

    private function contingentRows(array $filters): array
    {
        $builder = (new ContingentModel())
            ->select('contingents.*, events.name AS event_name')
            ->join('events', 'events.id = contingents.event_id', 'left')
            ->orderBy('events.name', 'ASC')
            ->orderBy('contingents.name', 'ASC');

        if (! empty($filters['event_id'])) {
            $builder->where('contingents.event_id', (int) $filters['event_id']);
        }

        if (! empty($filters['contingent'])) {
            $builder->like('contingents.name', $filters['contingent']);
        }

        $confirmations = [];
        foreach ((new ContingentConfirmationModel())->findAll() as $confirmation) {
            $key = (int) $confirmation['event_id'] . ':' . (int) $confirmation['contingent_id'];
            $confirmations[$key] = $confirmation;
        }

        $rows = [];
        foreach ($builder->findAll() as $contingent) {
            $key = (int) $contingent['event_id'] . ':' . (int) $contingent['id'];
            $confirmation = $confirmations[$key] ?? null;
            $row = [
                'event_id' => (int) $contingent['event_id'],
                'event_name' => $contingent['event_name'] ?? '-',
                'contingent_id' => (int) $contingent['id'],
                'contingent_name' => $contingent['name'] ?? '-',
                'source_contingent_id' => $contingent['source_contingent_id'] ?? '-',
                'status' => $confirmation ? 'confirmed' : 'unconfirmed',
                'status_label' => $confirmation ? 'Tidak Ada Complain' : 'Tidak Ada Konfirmasi',
                'confirmation_code' => $confirmation['confirmation_code'] ?? '-',
                'official_name' => $confirmation['official_name'] ?? '-',
                'official_phone' => $confirmation['official_phone'] ?? '-',
                'signature_image' => $confirmation['signature_image'] ?? '',
                'confirmed_at' => $confirmation['confirmed_at'] ?? '-',
            ];

            if (! empty($filters['status']) && $filters['status'] !== $row['status']) {
                continue;
            }

            $rows[] = $row;
        }

        return $rows;
    }

    private function contingentCounts(array $rows): array
    {
        $counts = ['total' => count($rows), 'confirmed' => 0, 'unconfirmed' => 0];
        foreach ($rows as $row) {
            if (($row['status'] ?? '') === 'confirmed') {
                $counts['confirmed']++;
            } else {
                $counts['unconfirmed']++;
            }
        }

        return $counts;
    }
}
