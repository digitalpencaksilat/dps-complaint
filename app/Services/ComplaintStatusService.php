<?php

namespace App\Services;

use App\Models\ComplaintReportModel;
use App\Models\ComplaintStatusHistoryModel;

class ComplaintStatusService
{
    public const STATUSES = ['baru', 'diproses', 'perlu_konfirmasi', 'selesai', 'ditolak'];

    public function changeStatus(int $reportId, string $newStatus, ?string $note = null, ?string $publicNote = null, ?int $adminId = null): bool
    {
        if (! in_array($newStatus, self::STATUSES, true)) return false;
        $reports = new ComplaintReportModel();
        $report = $reports->find($reportId);
        if (! $report) return false;
        $now = date('Y-m-d H:i:s');
        $update = ['status' => $newStatus, 'processed_at' => $now, 'admin_note' => $note];
        if ($newStatus === 'diproses' && empty($report['first_processed_at'])) $update['first_processed_at'] = $now;
        if (in_array($newStatus, ['selesai', 'ditolak'], true)) $update['resolved_at'] = $now;
        $reports->update($reportId, $update);
        (new ComplaintStatusHistoryModel())->insert([
            'complaint_report_id' => $reportId,
            'old_status' => $report['status'] ?? null,
            'new_status' => $newStatus,
            'note' => $note,
            'public_note' => $publicNote,
            'changed_by_admin_id' => $adminId,
            'changed_at' => $now,
            'created_at' => $now,
        ]);
        return true;
    }
}
