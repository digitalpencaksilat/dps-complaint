<?php

namespace App\Services;

use App\Models\ContingentModel;
use App\Models\EventModel;
use App\Models\ParticipantModel;
use CodeIgniter\Database\Config;
use RuntimeException;

class ParticipantSyncService
{
    public function sync(int $eventId, bool $fresh = false): array
    {
        $event = (new EventModel())->find($eventId);
        if (! $event) throw new RuntimeException('Event tidak ditemukan.');
        $source = $this->sourceDb($event);
        $contingents = new ContingentModel();
        $participants = new ParticipantModel();
        if ($fresh) {
            $participants->where('event_id', $eventId)->delete();
            $contingents->where('event_id', $eventId)->delete();
        }

        $counts = ['contingents' => 0, 'tanding' => 0, 'seni' => 0];
        foreach ($source->query('SELECT id_kontingen AS source_contingent_id, nama_kontingen AS name FROM kontingen')->getResultArray() as $row) {
            $existing = $contingents->where('event_id', $eventId)->where('source_contingent_id', $row['source_contingent_id'])->first();
            $data = ['event_id' => $eventId, 'source_contingent_id' => (string)$row['source_contingent_id'], 'name' => $row['name'] ?: '-'];
            $existing ? $contingents->update($existing['id'], $data) : $contingents->insert($data);
            $counts['contingents']++;
        }

        $tandingSql = "SELECT pt.id_peserta_tanding AS source_participant_id, p.id_pendaftar AS source_registrant_id, p.nama_pendaftar AS full_name, p.jenis_kelamin AS gender, k.id_kontingen AS source_contingent_id, k.nama_kontingen AS contingent_name, ku.nama_kategori_usia AS age_category, kl.nama_kategori_lomba AS competition_category, kt.label AS class_or_art_name FROM peserta_tanding pt JOIN pendaftar p ON p.id_pendaftar = pt.id_pendaftar LEFT JOIN kontingen k ON k.id_kontingen = p.id_kontingen LEFT JOIN kompetisi_tanding kom ON kom.id_kompetisi_tanding = pt.id_kompetisi_tanding LEFT JOIN kelas_tanding kt ON kt.id_kelas_tanding = kom.id_kelas_tanding LEFT JOIN kategori_lomba kl ON kl.id_kategori_lomba = kt.id_kategori_lomba LEFT JOIN kategori_usia ku ON ku.id_kategori_usia = kl.id_kategori_usia";
        foreach ($source->query($tandingSql)->getResultArray() as $row) {
            $this->upsertParticipant($eventId, 'tanding', $row);
            $counts['tanding']++;
        }

        $seniSql = "SELECT ps.id_peserta_seni AS source_participant_id, p.id_pendaftar AS source_registrant_id, p.nama_pendaftar AS full_name, p.jenis_kelamin AS gender, k.id_kontingen AS source_contingent_id, k.nama_kontingen AS contingent_name, ku.nama_kategori_usia AS age_category, kl.nama_kategori_lomba AS competition_category, CONCAT(sks.jenis_seni, ' - ', sks.nama_seni, ' - ', sks.sistem_penampilan) AS class_or_art_name FROM peserta_seni ps JOIN pendaftar p ON p.id_pendaftar = ps.id_pendaftar LEFT JOIN kontingen k ON k.id_kontingen = p.id_kontingen LEFT JOIN kelompok_peserta_seni kps ON kps.id_kelompok_peserta_seni = ps.id_kelompok_peserta_seni LEFT JOIN kompetisi_seni ks ON ks.id_kompetisi_seni = kps.id_kompetisi_seni LEFT JOIN sub_kategori_seni sks ON sks.id_sub_kategori_seni = ks.id_sub_kategori_seni LEFT JOIN kategori_lomba kl ON kl.id_kategori_lomba = sks.id_kategori_lomba LEFT JOIN kategori_usia ku ON ku.id_kategori_usia = kl.id_kategori_usia";
        foreach ($source->query($seniSql)->getResultArray() as $row) {
            $this->upsertParticipant($eventId, 'seni', $row);
            $counts['seni']++;
        }
        return $counts;
    }

    private function sourceDb(array $event)
    {
        $config = [
            'DSN' => '',
            'hostname' => $event['source_db_host'] ?: '127.0.0.1',
            'username' => $event['source_db_username'] ?: 'root',
            'password' => $event['source_db_password_encrypted'] ? base64_decode($event['source_db_password_encrypted']) : '',
            'database' => $event['source_db_name'] ?: 'db_testing_event',
            'DBDriver' => 'MySQLi',
            'DBPrefix' => '',
            'pConnect' => false,
            'DBDebug' => true,
            'charset' => 'utf8mb4',
            'DBCollat' => 'utf8mb4_general_ci',
        ];
        return Config::connect($config, false);
    }

    private function upsertParticipant(int $eventId, string $type, array $row): void
    {
        $participants = new ParticipantModel();
        $contingent = (new ContingentModel())->where('event_id', $eventId)->where('source_contingent_id', $row['source_contingent_id'])->first();
        $sourceId = $type . ':' . $row['source_participant_id'];
        $data = [
            'event_id' => $eventId,
            'source_participant_id' => $sourceId,
            'source_competition_type' => $type,
            'source_registrant_id' => (string)$row['source_registrant_id'],
            'full_name' => $row['full_name'] ?: '-',
            'contingent_id' => $contingent['id'] ?? null,
            'contingent_name' => $row['contingent_name'],
            'gender' => $row['gender'],
            'age_category' => $row['age_category'],
            'competition_category' => $row['competition_category'],
            'class_or_art_name' => $row['class_or_art_name'],
            'raw_payload' => json_encode($row, JSON_UNESCAPED_UNICODE),
            'imported_at' => date('Y-m-d H:i:s'),
        ];
        $existing = $participants->where('event_id', $eventId)->where('source_participant_id', $sourceId)->first();
        $existing ? $participants->update($existing['id'], $data) : $participants->insert($data);
    }
}
