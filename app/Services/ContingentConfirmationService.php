<?php

namespace App\Services;

use App\Models\ComplaintItemModel;
use App\Models\ContingentConfirmationModel;
use App\Models\ContingentModel;
use App\Models\EventModel;
use CodeIgniter\Database\BaseConnection;
use RuntimeException;

class ContingentConfirmationService
{
    private const ACTIVE_COMPLAINT_STATUSES = ['baru', 'diproses', 'perlu_konfirmasi'];

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

        $contingent = (new ContingentModel())
            ->where('event_id', $event['id'])
            ->where('id', (int)($payload['confirmation_contingent_id'] ?? 0))
            ->first();
        if (! $contingent) throw new RuntimeException('Kontingen tidak valid.');

        $existing = (new ContingentConfirmationModel())->findByEventContingent((int)$event['id'], (int)$contingent['id']);
        if ($existing) {
            throw new RuntimeException('Kontingen ini sudah menginput konfirmasi Tidak Ada Komplain.');
        }

        if ($this->hasActiveComplaint((int)$event['id'], (int)$contingent['id'], (string)$contingent['name'])) {
            throw new RuntimeException('Kontingen ini masih memiliki komplain aktif. Selesaikan komplain terlebih dahulu sebelum konfirmasi Tidak Ada Komplain.');
        }

        $now = date('Y-m-d H:i:s');
        $code = $this->generateConfirmationCode($event['slug'] ?? 'CONFIRM');
        $statement = 'Saya menyatakan data atlet kontingen sudah sesuai dengan data kejuaraan.';

        $this->db->transStart();
        (new ContingentConfirmationModel())->insert([
            'event_id' => (int)$event['id'],
            'contingent_id' => (int)$contingent['id'],
            'confirmation_code' => $code,
            'official_name' => $officialName,
            'official_phone' => $officialPhone,
            'signature_image' => $payload['signature_image'],
            'signature_hash' => $signatureService->hash($payload['signature_image']),
            'contingent_snapshot' => json_encode($contingent, JSON_UNESCAPED_UNICODE),
            'statement' => $statement,
            'confirmed_at' => $now,
        ]);
        $this->db->transComplete();
        if (! $this->db->transStatus()) throw new RuntimeException('Gagal menyimpan konfirmasi kontingen.');

        return $code;
    }

    public function confirmationStatus(int $eventId, int $contingentId): array
    {
        $contingent = (new ContingentModel())
            ->where('event_id', $eventId)
            ->where('id', $contingentId)
            ->first();
        if (! $contingent) {
            return [
                'can_confirm' => false,
                'status' => 'invalid_contingent',
                'message' => 'Kontingen tidak valid.',
            ];
        }

        if ((new ContingentConfirmationModel())->findByEventContingent($eventId, $contingentId)) {
            return [
                'can_confirm' => false,
                'status' => 'already_confirmed',
                'message' => 'Kontingen ini sudah menginput konfirmasi Tidak Ada Komplain.',
            ];
        }

        if ($this->hasActiveComplaint($eventId, $contingentId, (string)$contingent['name'])) {
            return [
                'can_confirm' => false,
                'status' => 'active_complaint',
                'message' => 'Kontingen ini masih memiliki komplain aktif. Selesaikan komplain terlebih dahulu sebelum konfirmasi Tidak Ada Komplain.',
            ];
        }

        return [
            'can_confirm' => true,
            'status' => 'available',
            'message' => '',
        ];
    }

    private function hasActiveComplaint(int $eventId, int $contingentId, string $contingentName): bool
    {
        $items = (new ComplaintItemModel())
            ->select('complaint_items.contingent_id, complaint_items.participant_snapshot, complaint_items.contingent_snapshot')
            ->join('complaint_reports', 'complaint_reports.id = complaint_items.complaint_report_id', 'inner')
            ->where('complaint_reports.event_id', $eventId)
            ->whereIn('complaint_reports.status', self::ACTIVE_COMPLAINT_STATUSES)
            ->findAll();

        $needle = mb_strtolower(trim($contingentName));
        foreach ($items as $item) {
            if ((int)($item['contingent_id'] ?? 0) === $contingentId) {
                return true;
            }

            foreach (['participant_snapshot', 'contingent_snapshot'] as $field) {
                $snapshot = json_decode((string)($item[$field] ?? ''), true) ?: [];
                $name = mb_strtolower(trim((string)($snapshot['contingent_name'] ?? $snapshot['name'] ?? '')));
                if ($needle !== '' && $name === $needle) {
                    return true;
                }
            }
        }

        return false;
    }

    private function generateConfirmationCode(string $slug): string
    {
        $prefix = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $slug)) ?: 'DPS';
        return 'CONF-' . substr($prefix, 0, 12) . '-' . date('ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));
    }
}
