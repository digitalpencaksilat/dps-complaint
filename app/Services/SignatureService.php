<?php

namespace App\Services;

class SignatureService
{
    public function validate(?string $signature): bool
    {
        if (! is_string($signature) || $signature === '') return false;
        if (! str_starts_with($signature, 'data:image/png;base64,')) return false;
        if (strlen($signature) > 1024 * 1024) return false;
        $payload = substr($signature, strlen('data:image/png;base64,'));
        return base64_decode($payload, true) !== false;
    }

    public function hash(string $signature): string
    {
        return hash('sha256', $signature);
    }
}
