<?php

namespace App\Services;

use App\Models\ComplaintItemModel;
use App\Models\ComplaintReportModel;
use App\Models\ComplaintStatusHistoryModel;
use App\Models\ContingentModel;
use App\Models\EventModel;
use App\Models\ParticipantModel;
use CodeIgniter\Database\BaseConnection;
use RuntimeException;

class ComplaintSubmissionService
{
    public function __construct(private ?BaseConnection $db = null)
    {
        $this->db = $db ?? db_connect();
    }

    public function submit(array $payload): string
    {
        $events = new EventModel();
        $event = $events->find((int)($payload['event_id'] ?? 0));
        if (! $event || ! $events->isComplaintOpen($event)) throw new RuntimeException('Kejuaraan tidak aktif atau complain sudah ditutup.');
        $officialName = trim((string)($payload['official_name'] ?? ''));
        $officialPhone = trim((string)($payload['official_phone'] ?? ''));
        if ($officialName === '') throw new RuntimeException('Nama official wajib diisi.');
        if ($officialPhone === '') throw new RuntimeException('Nomor telepon official wajib diisi.');
        $signatureService = new SignatureService();
        if (! $signatureService->validate($payload['signature_image'] ?? null)) throw new RuntimeException('Tanda tangan wajib diisi.');
        $items = $payload['items'] ?? [];
        if (! is_array($items) || count($items) < 1) throw new RuntimeException('Minimal 1 complain wajib diisi.');

        $now = date('Y-m-d H:i:s');
        $ticket = $this->generateTicket($event['slug'] ?? 'TICKET');
        $this->db->transStart();
        $reportModel = new ComplaintReportModel();
        $reportId = $reportModel->insert([
            'event_id' => (int)$event['id'],
            'ticket_code' => $ticket,
            'official_name' => $officialName,
            'official_phone' => $officialPhone,
            'signature_image' => $payload['signature_image'],
            'signature_hash' => $signatureService->hash($payload['signature_image']),
            'signed_at' => $now,
            'status' => 'baru',
            'submitted_at' => $now,
            'sla_due_at' => date('Y-m-d H:i:s', time() + ((int)($event['sla_hours'] ?? 24) * 3600)),
        ], true);

        $participantModel = new ParticipantModel();
        $contingentModel = new ContingentModel();
        $itemModel = new ComplaintItemModel();
        foreach ($items as $item) {
            $type = (string)($item['complaint_type'] ?? '');
            $description = trim((string)($item['description'] ?? ''));
            if (! in_array($type, ['name_error', 'gender_error', 'category_error', 'missing_participant'], true)) {
                throw new RuntimeException('Jenis complain tidak valid.');
            }
            if ($description === '' || strlen($description) < 10) throw new RuntimeException('Keterangan complain minimal 10 karakter.');
            $row = [
                'complaint_report_id' => $reportId,
                'complaint_type' => $type,
                'description' => $description,
            ];
            if ($type === 'missing_participant') {
                $contingent = $contingentModel->where('event_id', $event['id'])->find((int)($item['contingent_id'] ?? 0));
                if (! $contingent) throw new RuntimeException('Kontingen tidak valid.');
                $row['contingent_id'] = $contingent['id'];
                $row['contingent_snapshot'] = json_encode($contingent, JSON_UNESCAPED_UNICODE);
            } else {
                $participant = $participantModel->where('event_id', $event['id'])->find((int)($item['participant_id'] ?? 0));
                if (! $participant) throw new RuntimeException('Peserta tidak valid.');
                $row['participant_id'] = $participant['id'];
                $row['contingent_id'] = $participant['contingent_id'] ?: null;
                $row['participant_snapshot'] = json_encode($participant, JSON_UNESCAPED_UNICODE);
            }
            $itemModel->insert($row);
        }
        (new ComplaintStatusHistoryModel())->insert([
            'complaint_report_id' => $reportId,
            'old_status' => null,
            'new_status' => 'baru',
            'public_note' => 'Complain berhasil diterima oleh sistem.',
            'changed_at' => $now,
            'created_at' => $now,
        ]);
        $this->db->transComplete();
        if (! $this->db->transStatus()) throw new RuntimeException('Gagal menyimpan complain.');
        return $ticket;
    }

    private function generateTicket(string $slug): string
    {
        $prefix = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $slug)) ?: 'DPS';
        return substr($prefix, 0, 12) . '-' . date('ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));
    }
}
