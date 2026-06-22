<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\ParticipantModel;
use App\Services\RateLimitService;

class ParticipantSearchController extends BaseController
{
    public function index()
    {
        if (! (new RateLimitService())->hit('participant-search:' . $this->request->getIPAddress(), 60, 60)) {
            return $this->response->setStatusCode(429)->setJSON(['error' => 'Terlalu banyak request.']);
        }
        $eventId = (int)$this->request->getGet('event_id');
        $q = trim((string)$this->request->getGet('q'));
        if ($eventId < 1 || strlen($q) < 2) return $this->response->setJSON([]);
        return $this->response->setJSON((new ParticipantModel())->searchByEvent($eventId, $q));
    }
}
