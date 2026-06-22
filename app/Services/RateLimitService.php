<?php

namespace App\Services;

class RateLimitService
{
    public function hit(string $key, int $maxAttempts, int $windowSeconds): bool
    {
        $cache = cache();
        $bucket = 'rate_limit_' . sha1($key);
        $data = $cache->get($bucket) ?: ['count' => 0, 'reset' => time() + $windowSeconds];
        if (($data['reset'] ?? 0) < time()) {
            $data = ['count' => 0, 'reset' => time() + $windowSeconds];
        }
        $data['count']++;
        $cache->save($bucket, $data, max(1, ($data['reset'] - time())));
        return $data['count'] <= $maxAttempts;
    }
}
