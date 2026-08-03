<?php

declare(strict_types=1);

require_once __DIR__ . '/../PHPGangsta/GoogleAuthenticator.php';
require_once __DIR__ . '/../CIDR.php';

function assert_true(bool $condition, string $message): void
{
    if (!$condition) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
}

// RFC 6238 SHA-1 test vector (59 seconds, 8-digit OTP).
$totp = new PHPGangsta_GoogleAuthenticator();
$totp->setCodeLength(8);
assert_true(
    $totp->getCode('GEZDGNBVGY3TQOJQGEZDGNBVGY3TQOJQ', 1) === '94287082',
    'TOTP RFC 6238 vector failed'
);

assert_true(CIDR::match('192.168.1.42', '192.168.1.0/24'), 'IPv4 CIDR match failed');
assert_true(!CIDR::match('192.168.2.42', '192.168.1.0/24'), 'IPv4 CIDR negative match failed');
assert_true(CIDR::match('2001:db8::42', '2001:db8::/64'), 'IPv6 CIDR match failed');

$plugin = file_get_contents(__DIR__ . '/../twofactor_gauthenticator.php');
assert_true(
    strpos($plugin, "empty(\$_SESSION['twofactor_gauthenticator_2FA_login'])") !== false,
    '2FA session gate is missing'
);
$ip_block = '';
preg_match('/This installation is expected.*?(?=\/\/ settings we will export)/s', $plugin, $ip_match);
$ip_block = $ip_match[0] ?? '';
assert_true(
    strpos($ip_block, 'HTTP_X_FORWARDED_FOR') === false && strpos($ip_block, 'HTTP_CLIENT_IP') === false,
    'untrusted forwarding headers are still used for the whitelist'
);
assert_true(
    strpos($plugin, 'hash_equals($signature_verification, $signature)') !== false,
    'remember-device cookie uses no constant-time HMAC comparison'
);
assert_true(
    strpos($plugin, "INPUT_POST") !== false,
    '2FA check-code action does not read its secret from POST'
);

fwrite(STDOUT, "OK\n");
