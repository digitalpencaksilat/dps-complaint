<?php

namespace App\Controllers;

use App\Models\EventModel;
use App\Services\ComplaintSubmissionService;
use App\Services\ContingentConfirmationService;
use App\Services\RateLimitService;
use RuntimeException;

class ComplaintController extends BaseController
{
    public function form()
    {
        return view('complaints/form', ['events' => (new EventModel())->activeForPublic()]);
    }

    public function submit()
    {
        if (! (new RateLimitService())->hit('submit:' . $this->request->getIPAddress(), 5, 600)) {
            return redirect()->back()->withInput()->with('error', 'Terlalu banyak submit. Coba lagi nanti.');
        }
        try {
            $payload = $this->request->getPost();
            $mode = (string)($payload['submission_mode'] ?? 'complaint');
            if ($mode === 'no_complaint') {
                $code = (new ContingentConfirmationService())->submit($payload);
                return redirect()->to('/complaints/success/' . $code . '?type=confirmation');
            }

            $ticket = (new ComplaintSubmissionService())->submit($payload);
            return redirect()->to('/complaints/success/' . $ticket);
        } catch (RuntimeException $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function success(string $ticket)
    {
        return view('complaints/success', [
            'ticket' => $ticket,
            'type' => $this->request->getGet('type'),
        ]);
    }
}
