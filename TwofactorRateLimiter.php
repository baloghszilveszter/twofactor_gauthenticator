<?php

/** Persistent, atomic per-account OTP throttling (shared across sessions and IPs). */
final class TwofactorRateLimiter
{
    private string $directory;

    public function __construct(string $directory)
    {
        $this->directory = rtrim($directory, '/');
        if (!is_dir($this->directory)
            && !@mkdir($this->directory, 0700, true) && !is_dir($this->directory)) {
            throw new RuntimeException('Cannot create 2FA rate-limit directory');
        }
    }

    /** The lock covers both verification and updating state, including parallel requests. */
    public function verify(string $account, callable $verifier, ?int $now = null): bool
    {
        if ($account === '') {
            throw new RuntimeException('Missing 2FA account');
        }
        $now = $now ?? time();
        $path = $this->directory . '/' . hash('sha256', $account) . '.json';
        $file = @fopen($path, 'c+');
        if (!$file) {
            throw new RuntimeException('Cannot open 2FA rate-limit state');
        }
        try {
            if (!flock($file, LOCK_EX)) {
                throw new RuntimeException('Cannot lock 2FA rate-limit state');
            }
            $raw = stream_get_contents($file);
            $state = $raw === '' ? ['failures' => 0, 'blocked_until' => 0, 'updated' => 0]
                : json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
            foreach (['failures', 'blocked_until', 'updated'] as $field) {
                if (!isset($state[$field]) || !is_int($state[$field]) || $state[$field] < 0) {
                    throw new RuntimeException('Invalid 2FA rate-limit state');
                }
            }
            // Expire old penalties, without permitting session/IP rotation to reset them.
            if ($now - $state['updated'] >= 86400) {
                $state = ['failures' => 0, 'blocked_until' => 0, 'updated' => 0];
            }
            if ($state['blocked_until'] > $now) {
                return false;
            }
            $accepted = (bool) $verifier();
            $failures = $accepted ? 0 : min($state['failures'] + 1, 7);
            $delays = [1, 2, 5, 20, 60, 300, 600];
            $state = ['failures' => $failures,
                'blocked_until' => $accepted ? 0 : $now + $delays[$failures - 1],
                'updated' => $now];
            $json = json_encode($state, JSON_THROW_ON_ERROR);
            rewind($file);
            if (!ftruncate($file, 0) || fwrite($file, $json) !== strlen($json) || !fflush($file)) {
                throw new RuntimeException('Cannot persist 2FA rate-limit state');
            }
            return $accepted;
        } finally {
            flock($file, LOCK_UN);
            fclose($file);
        }
    }
}
