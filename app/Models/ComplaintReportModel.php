<?php

namespace App\Models;

use CodeIgniter\Model;

class ComplaintReportModel extends Model
{
    protected $table = 'complaint_reports';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = ['event_id','ticket_code','official_name','official_phone','signature_image','signature_hash','signed_at','status','admin_note','submitted_at','sla_due_at','first_processed_at','resolved_at','processed_at'];

    public function withEvent(?array $filters = []): array
    {
        $builder = $this->withEventBuilder($filters);

        return $builder->findAll();
    }

    public function withEventPaginated(?array $filters = [], int $perPage = 10): array
    {
        $builder = $this->withEventBuilder($filters);

        return $builder->paginate($perPage, 'complaints');
    }

    public function statusCounts(?array $filters = []): array
    {
        $builder = $this->select('complaint_reports.status, COUNT(*) AS total')
            ->join('events', 'events.id = complaint_reports.event_id', 'left')
            ->groupBy('complaint_reports.status');

        if (! empty($filters['event_id'])) {
            $builder->where('complaint_reports.event_id', (int) $filters['event_id']);
        }

        $counts = ['total' => 0, 'baru' => 0, 'diproses' => 0, 'perlu_konfirmasi' => 0, 'selesai' => 0, 'ditolak' => 0];
        foreach ($builder->findAll() as $row) {
            $status = (string) ($row['status'] ?? '');
            $total = (int) ($row['total'] ?? 0);
            if (array_key_exists($status, $counts)) {
                $counts[$status] = $total;
            }
            $counts['total'] += $total;
        }

        return $counts;
    }

    private function withEventBuilder(?array $filters = []): self
    {
        $builder = $this->select('complaint_reports.*, events.name AS event_name, events.slug AS event_slug')
            ->join('events', 'events.id = complaint_reports.event_id', 'left')
            ->orderBy('complaint_reports.submitted_at', 'DESC');

        if (! empty($filters['event_id'])) {
            $builder->where('complaint_reports.event_id', (int) $filters['event_id']);
        }

        if (! empty($filters['status'])) {
            $builder->where('complaint_reports.status', $filters['status']);
        }

        return $builder;
    }
}
