<?php

if (! function_exists('complaint_type_label')) {
    function complaint_type_label(?string $type): string
    {
        return [
            'name_error' => 'Kesalahan Nama',
            'gender_error' => 'Kesalahan Jenis Kelamin',
            'category_error' => 'Kesalahan Kategori Yang Diikuti',
            'missing_participant' => 'Tidak Ada Peserta',
        ][$type ?? ''] ?? 'Complain Lainnya';
    }
}

if (! function_exists('status_label')) {
    function status_label(?string $status): string
    {
        return [
            'baru' => 'Baru',
            'diproses' => 'Diproses',
            'perlu_konfirmasi' => 'Perlu Konfirmasi',
            'selesai' => 'Selesai',
            'ditolak' => 'Ditolak',
        ][$status ?? ''] ?? ucwords(str_replace('_', ' ', (string) $status));
    }
}

if (! function_exists('status_badge_class')) {
    function status_badge_class(?string $status): string
    {
        return [
            'baru' => 'bg-danger',
            'diproses' => 'bg-warning text-dark',
            'perlu_konfirmasi' => 'bg-info text-dark',
            'selesai' => 'bg-success',
            'ditolak' => 'bg-dark',
            'draft' => 'bg-secondary',
            'active' => 'bg-success',
            'closed' => 'bg-dark',
            'archived' => 'bg-light text-dark border',
        ][$status ?? ''] ?? 'bg-secondary';
    }
}

if (! function_exists('complaint_item_subject')) {
    function complaint_item_subject(array $item): array
    {
        $participant = json_decode((string)($item['participant_snapshot'] ?? ''), true) ?: [];
        $contingent = json_decode((string)($item['contingent_snapshot'] ?? ''), true) ?: [];

        if (! empty($participant)) {
            return [
                'mode' => 'participant',
                'title' => $participant['full_name'] ?? '-',
                'rows' => [
                    'Nama Peserta' => $participant['full_name'] ?? '-',
                    'Kontingen' => $participant['contingent_name'] ?? '-',
                    'Kategori Pertandingan' => complaint_competition_category_label($participant),
                ],
            ];
        }

        if (! empty($contingent)) {
            return [
                'mode' => 'contingent',
                'title' => $contingent['name'] ?? '-',
                'rows' => [
                    'Nama Peserta' => '-',
                    'Kontingen' => $contingent['name'] ?? '-',
                    'Kategori Pertandingan' => '-',
                ],
            ];
        }

        return [
            'mode' => 'empty',
            'title' => '-',
            'rows' => [],
        ];
    }
}

if (! function_exists('complaint_competition_category_label')) {
    function complaint_competition_category_label(array $participant): string
    {
        $competition = trim((string)($participant['competition_category'] ?? ''));
        $age = trim((string)($participant['age_category'] ?? ''));
        $gender = trim((string)($participant['gender'] ?? ''));
        $classOrArt = trim((string)($participant['class_or_art_name'] ?? ''));
        $sourceType = strtolower((string)($participant['source_competition_type'] ?? ''));

        if ($sourceType === 'seni' || strcasecmp($competition, 'seni') === 0) {
            $artParts = array_values(array_filter(array_map('trim', explode(' - ', $classOrArt))));
            $artParts = array_values(array_filter($artParts, static function (string $part): bool {
                return ! in_array(strtolower($part), ['pool'], true);
            }));

            $category = $artParts[0] ?? '';
            $system = $artParts[1] ?? '';
            $parts = array_filter([$category, $age, $gender, $system]);

            return $parts ? mb_strtoupper(implode(' - ', $parts), 'UTF-8') : '-';
        }

        $parts = array_filter([$competition, $age, $gender, $classOrArt]);

        return $parts ? mb_strtoupper(implode(' - ', $parts), 'UTF-8') : '-';
    }
}
