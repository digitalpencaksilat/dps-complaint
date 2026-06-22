<?php

namespace App\Commands;

use App\Services\ParticipantSyncService;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class SyncParticipants extends BaseCommand
{
    protected $group = 'Complaints';
    protected $name = 'complaints:sync-participants';
    protected $description = 'Sync peserta/kontingen dari database kejuaraan sumber per event.';

    public function run(array $params)
    {
        $eventId = (int)(CLI::getOption('event') ?? 0);
        if ($eventId < 1) {
            CLI::error('Gunakan --event=ID');
            return;
        }
        $counts = (new ParticipantSyncService())->sync($eventId, (bool)CLI::getOption('fresh'));
        CLI::write('Sync selesai: ' . json_encode($counts), 'green');
    }
}
