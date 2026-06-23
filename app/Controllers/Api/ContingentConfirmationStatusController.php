<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Services\ContingentConfirmationService;
use App\Services\RateLimitService;

class ContingentConfirmationStatusController extends BaseController
{
    public function index()
    {
        if (! (new RateLimitService())->hit('contingent-confirmation-status:' . $this->request->getIPAddress(), 60, 60)) {
            return $this->response->setStatusCode(429)->setJSON(['error' => 'Terlalu banyak request.']);
        }

        $eventId = (int)$this->request->getGet('event_id');
        $contingentId = (int)$this->request->getGet('contingent_id');
        if ($eventId < 1 || $contingentId < 1) {
            return $this->response->setJSON([
                'ok' => true,
                'can_confirm' => false,
                'status' => 'invalid_contingent',
                'message' => 'Kontingen tidak valid.',
            ]);
        }

        return $this->response->setJSON([
            'ok' => true,
            ...((new ContingentConfirmationService())->confirmationStatus($eventId, $contingentId)),
        ]);
    }
}
