<?php

namespace App\Commands;

use App\Models\ComplaintItemModel;
use App\Models\ComplaintReportModel;
use App\Models\ContingentModel;
use App\Models\EventModel;
use App\Models\ParticipantModel;
use App\Services\ComplaintSubmissionService;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Throwable;

class SimulateComplaints extends BaseCommand
{
    protected $group = 'Complaints';
    protected $name = 'complaints:simulate';
    protected $description = 'Buat simulasi beberapa tiket complain multi-item untuk kejuaraan aktif.';

    public function run(array $params)
    {
        $count = max(1, (int)($params[0] ?? 3));
        $itemsPerTicket = max(5, (int)($params[1] ?? 6));
        $sourceType = strtolower((string)($params[2] ?? ''));

        $eventModel = new EventModel();
        $event = $eventModel->activeForPublic()[0] ?? null;
        if (! $event) {
            CLI::error('Tidak ada kejuaraan aktif yang bisa menerima complain.');
            return EXIT_ERROR;
        }

        $participantModel = (new ParticipantModel())->where('event_id', $event['id']);
        if (in_array($sourceType, ['seni', 'tanding'], true)) {
            $participantModel->where('source_competition_type', $sourceType);
        }

        $participants = $participantModel
            ->orderBy('id', 'ASC')
            ->findAll(max($count * $itemsPerTicket, $itemsPerTicket));

        $contingents = (new ContingentModel())
            ->where('event_id', $event['id'])
            ->orderBy('id', 'ASC')
            ->findAll(max($count, 5));

        if (count($participants) < 1 || count($contingents) < 1) {
            CLI::error('Data peserta/kontingen belum tersedia. Jalankan sync import dulu.');
            return EXIT_ERROR;
        }

        $signature = $this->signatureImage();
        $service = new ComplaintSubmissionService();
        $tickets = [];

        for ($ticketIndex = 0; $ticketIndex < $count; $ticketIndex++) {
            $items = [];

            for ($itemIndex = 0; $itemIndex < $itemsPerTicket; $itemIndex++) {
                $type = $this->typeFor($itemIndex);

                if ($type === 'missing_participant') {
                    $contingent = $contingents[($ticketIndex + $itemIndex) % count($contingents)];
                    $items[] = [
                        'complaint_type' => $type,
                        'contingent_id' => $contingent['id'],
                        'description' => sprintf(
                            'Simulasi peserta belum muncul dari kontingen %s untuk pengecekan alur complain multi item.',
                            $contingent['name'],
                        ),
                    ];
                    continue;
                }

                $participant = $participants[($ticketIndex * $itemsPerTicket + $itemIndex) % count($participants)];
                $items[] = [
                    'complaint_type' => $type,
                    'participant_id' => $participant['id'],
                    'description' => $this->descriptionFor($type, $participant),
                ];
            }

            try {
                $ticket = $service->submit([
                    'event_id' => $event['id'],
                    'official_name' => 'Official Simulasi ' . ($ticketIndex + 1),
                    'official_phone' => '08123000' . str_pad((string)($ticketIndex + 1), 4, '0', STR_PAD_LEFT),
                    'signature_image' => $signature,
                    'items' => $items,
                ]);
                $tickets[] = $ticket;
                CLI::write(sprintf('OK %s: %d item complain dibuat.', $ticket, count($items)), 'green');
            } catch (Throwable $e) {
                CLI::error(sprintf('Gagal membuat tiket simulasi #%d: %s', $ticketIndex + 1, $e->getMessage()));
                return EXIT_ERROR;
            }
        }

        $this->verifyTickets($tickets, $itemsPerTicket);

        CLI::write('Kejuaraan: ' . $event['name']);
        CLI::write('Mode peserta: ' . ($sourceType ?: 'campuran'));
        CLI::write('Tiket simulasi:');
        foreach ($tickets as $ticket) {
            CLI::write('- ' . $ticket . ' | Tracking: ' . site_url('complaints/track/' . rawurlencode($ticket)));
        }

        return EXIT_SUCCESS;
    }

    private function typeFor(int $index): string
    {
        return ['name_error', 'gender_error', 'category_error', 'missing_participant', 'name_error', 'category_error'][$index % 6];
    }

    private function descriptionFor(string $type, array $participant): string
    {
        return match ($type) {
            'name_error' => sprintf('Simulasi koreksi nama peserta %s agar sesuai data resmi kontingen.', $participant['full_name']),
            'gender_error' => sprintf('Simulasi koreksi jenis kelamin peserta %s karena tidak sesuai data pendaftaran.', $participant['full_name']),
            'category_error' => sprintf('Simulasi koreksi kategori pertandingan peserta %s agar masuk kelas/kategori yang benar.', $participant['full_name']),
            default => 'Simulasi complain peserta untuk pengecekan sistem.',
        };
    }

    private function verifyTickets(array $tickets, int $itemsPerTicket): void
    {
        $reportModel = new ComplaintReportModel();
        $itemModel = new ComplaintItemModel();

        foreach ($tickets as $ticket) {
            $report = $reportModel->where('ticket_code', $ticket)->first();
            if (! $report) {
                CLI::error('Verifikasi gagal: tiket tidak ditemukan ' . $ticket);
                continue;
            }

            $itemCount = $itemModel->where('complaint_report_id', $report['id'])->countAllResults();
            $status = $itemCount >= $itemsPerTicket ? 'OK' : 'KURANG';
            CLI::write(sprintf('VERIFY %s: %s item=%d expected>=%d', $ticket, $status, $itemCount, $itemsPerTicket), $status === 'OK' ? 'green' : 'yellow');
        }
    }

    private function signatureImage(): string
    {
        return 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAASwAAABkCAYAAABkW7XSAAAAAXNSR0IArs4c6QAAAWZJREFUeF7t1TEOwjAQRFGn+5+5gS2RkqAkgQy3uK8gY9I1bGZ2j8cAAAAAAAAAAAAAAAAAANzW9wG8W7fb7Xq9Xq/X63a7Xb/f7/39/T2bzebxeLzW63W73W7v9/v7+/v9fr/f7/f9fr/f7/f9fr/f7/f9fr/f7/f9fr/f7/f9fr/f7/f9fr/f7/f9fr/f7/f9fr/f7/f9fr/f7/f9fr/f7/f9fr/f7/f9fr/f7/f9fr/f7/f9fr/f7/f9fr/f7/f9fr/f7/f9fr/f7/f9fr/f7/f9fr/f7/f9fr/f7/f9fr/f7/f9fr/f7/f9fr/f7/f9fr/f7/f9fr/f7/f9fr/f7/f9fr/f7/f9fr/f7/f9fr/f7/f9fr/f7/f9fr/f7/f9fr/f7/f9fr/f7/f9fr/f7/f9fr/f7/f9fr/f7/f9fr/f7/f9fr/f7/f9fr/f7/f9fr/f7/f9fr/f7/f9fr/f7/f9fr/f7/f9fr/f7/f9fr/f7/f9fr/f7/f9fr/f7/f9fr/f7/f9fr/f7/f9fr/f7/f9fr/f7/f9fr/f7/f9fr/f7/f9fr/f7/f9fr/f7/f9fr/f7/f9fr/f7/f9fr/f7/f9fr/f7/f9fr/f7/f9fr/f7/f9fr/f7/f9fr/f7/f9fr/f7/f9fr/f7/f9fr/f7/f9fr/f7/f9fr/f7/f9fr/f7/f9fr/f7/fwEAAAAAAAAAAAAAAAAAAD6NAx0pAAG0x2p6AAAAAElFTkSuQmCC';
    }
}
