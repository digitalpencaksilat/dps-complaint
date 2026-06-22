<?php

namespace App\Models;

use CodeIgniter\Model;

class ParticipantModel extends Model
{
    protected $table = 'participants';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = ['event_id','source_participant_id','source_competition_type','source_registrant_id','full_name','contingent_id','contingent_name','gender','age_category','competition_category','class_or_art_name','source_event_id','raw_payload','imported_at'];

    public function searchByEvent(int $eventId, string $query, int $limit = 20): array
    {
        return $this->where('event_id', $eventId)
            ->groupStart()
                ->like('full_name', $query)
                ->orLike('contingent_name', $query)
            ->groupEnd()
            ->orderBy('full_name', 'ASC')
            ->findAll($limit);
    }
}
