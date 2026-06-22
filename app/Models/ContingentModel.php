<?php

namespace App\Models;

use CodeIgniter\Model;

class ContingentModel extends Model
{
    protected $table = 'contingents';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = ['event_id','source_contingent_id','name','source_event_id'];

    public function searchByEvent(int $eventId, string $query, int $limit = 20): array
    {
        return $this->where('event_id', $eventId)
            ->like('name', $query)
            ->orderBy('name', 'ASC')
            ->findAll($limit);
    }
}
