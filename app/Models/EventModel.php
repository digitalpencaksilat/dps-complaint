<?php

namespace App\Models;

use CodeIgniter\Model;

class EventModel extends Model
{
    protected $table = 'events';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'name','slug','location','start_date','end_date','complaint_deadline','complaint_closed_at','complaint_closed_reason','sla_hours','status','source_db_host','source_db_name','source_db_username','source_db_password_encrypted','source_config'
    ];

    public function activeForPublic(): array
    {
        $now = date('Y-m-d H:i:s');
        return $this->where('status', 'active')
            ->groupStart()
                ->where('complaint_deadline IS NULL', null, false)
                ->orWhere('complaint_deadline >=', $now)
            ->groupEnd()
            ->where('complaint_closed_at IS NULL', null, false)
            ->orderBy('start_date', 'DESC')
            ->findAll();
    }

    public function isComplaintOpen(array $event): bool
    {
        if (($event['status'] ?? '') !== 'active') return false;
        if (! empty($event['complaint_closed_at'])) return false;
        if (! empty($event['complaint_deadline']) && strtotime($event['complaint_deadline']) < time()) return false;
        return true;
    }
}
