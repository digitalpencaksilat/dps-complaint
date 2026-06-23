<?php

namespace App\Models;

use CodeIgniter\Model;

class ContingentConfirmationModel extends Model
{
    protected $table = 'contingent_confirmations';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'event_id',
        'contingent_id',
        'confirmation_code',
        'official_name',
        'official_phone',
        'signature_image',
        'signature_hash',
        'contingent_snapshot',
        'statement',
        'confirmed_at',
    ];

    public function findByEventContingent(int $eventId, int $contingentId): ?array
    {
        return $this->where('event_id', $eventId)
            ->where('contingent_id', $contingentId)
            ->first();
    }

    public function byEventKeyed(int $eventId): array
    {
        $rows = $this->where('event_id', $eventId)->findAll();
        $keyed = [];
        foreach ($rows as $row) {
            $keyed[(int) $row['contingent_id']] = $row;
        }

        return $keyed;
    }
}
