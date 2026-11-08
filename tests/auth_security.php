<?php
/** Behavioral regression tests; no Roundcube installation or mailbox needed. */
declare(strict_types=1);
require __DIR__ . '/../TwofactorRateLimiter.php';
function expect($ok, $message) { if (!$ok) { throw new RuntimeException($message); } }
if (($argv[1] ?? '') !== 'child') {
if (($argv[1] ?? '') === 'parallel') {
    $limiter = new TwofactorRateLimiter($argv[2]);
    $limiter->verify('parallel', function () use ($argv) {
        file_put_contents($argv[2] . '/evaluations', 'x', FILE_APPEND);
        usleep(100000);
        return false;
    }, 1000);
    exit;
}
$dir = sys_get_temp_dir() . '/twofactor-test-' . bin2hex(random_bytes(8));
try {
    $limiter = new TwofactorRateLimiter($dir);
    $calls = 0;
    $bad = function () use (&$calls) { ++$calls; return false; };
    $good = function () use (&$calls) { ++$calls; return true; };
    expect(!$limiter->verify('alice', $bad, 100), 'Bad code accepted');
    expect(!(new TwofactorRateLimiter($dir))->verify('alice', $good, 100), 'New session bypassed cooldown');
    expect($calls === 1, 'Blocked code was evaluated');
    expect(!$limiter->verify('alice', $bad, 101), 'Second bad code accepted');
    expect(!$limiter->verify('alice', $good, 102), 'Escalating cooldown missing');
    expect($limiter->verify('bob', $good, 102), 'Different account blocked');
    expect($limiter->verify('alice', $good, 103), 'Correct code rejected after cooldown');
    expect(!$limiter->verify('alice', $bad, 103), 'Bad code accepted after success');
    expect($limiter->verify('alice', $good, 104), 'Success did not reset failures');
    $now = 200;
    foreach ([1, 2, 5, 20, 60, 300, 600, 600] as $delay) {
        expect(!$limiter->verify('attacker', $bad, $now), 'Bad code accepted');
        expect(!$limiter->verify('attacker', $good, $now + $delay - 1), 'Cooldown too short');
        $now += $delay;
    }
    expect($limiter->verify('attacker', $good, $now), 'Cooldown never expires');
    file_put_contents($dir . '/' . hash('sha256', 'corrupt') . '.json', '{');
    $failed = false;
    try { $limiter->verify('corrupt', $good, 100); } catch (Throwable $e) { $failed = true; }
    expect($failed, 'Corrupt storage did not fail closed');
    $processes = [];
    for ($i = 0; $i < 8; ++$i) {
        $command = [PHP_BINARY, __FILE__, 'parallel', $dir];
        $process = proc_open($command, [0 => ['file', '/dev/null', 'r'],
            1 => ['file', '/dev/null', 'w'], 2 => ['file', $dir . '/worker-errors', 'a']], $pipes);
        expect(is_resource($process), 'Cannot start parallel worker');
        $processes[] = $process;
    }
    foreach ($processes as $process) { expect(proc_close($process) === 0, 'Parallel worker failed'); }
    expect(file_get_contents($dir . '/evaluations') === 'x', 'Parallel requests bypassed throttle');
    echo "OK: atomic concurrent verification\n";
    echo "OK: persistent cooldown, escalation, isolation, reset and fail-closed storage\n";
} finally {
    foreach (glob($dir . '/*') ?: [] as $file) { unlink($file); }
    rmdir($dir);
}

}

// Run exit()-based plugin paths in child processes and inspect their final state.
if (($argv[1] ?? '') === 'child') {
    class rcube_plugin {}
    class ConfigStub { function get($key, $default = null) { return $key === 'des_key' ? 'test-key' : $default; } }
    class UserStub {
        public $ID = 1;
        public $data = ['username' => 'test@example.invalid'];
        function get_prefs() { return ['twofactor_gauthenticator' => ['activate' => true]]; }
    }
    class rcmail {
        public static $instance;
        public $user; public $config; public $killed = false;
        static function get_instance() { return self::$instance; }
        function check_request() { return true; }
        function kill_session() { $this->killed = true; $_SESSION = []; $this->user->ID = 0; }
    }
    class rcube_utils {
        const INPUT_POST = 1;
        public static $cleared = false;
        static function setcookie($name, $value, $expires) { self::$cleared = $value === '' && $expires < time(); }
    }
    require __DIR__ . '/../twofactor_gauthenticator.php';
    $r = rcmail::$instance = new rcmail(); $r->user = new UserStub(); $r->config = new ConfigStub();
    $_SESSION = ['twofactor_gauthenticator_login' => time(), 'password' => 'synthetic'];
    register_shutdown_function(function () use ($r) {
        if (!$r->killed || $r->user->ID !== 0 || $_SESSION !== [] || !rcube_utils::$cleared) {
            fwrite(STDERR, "Session teardown failed\n"); exit(1);
        }
    });
    $plugin = new twofactor_gauthenticator();
    if (($argv[2] ?? '') === 'oracle') { $plugin->checkCode(); }
    else { $method = new ReflectionMethod($plugin, '__exitSession'); $method->setAccessible(true); $method->invoke($plugin); }
    throw new RuntimeException('Exit path returned');
}
foreach (['logout', 'oracle'] as $case) {
    $command = escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg(__FILE__) . ' child ' . escapeshellarg($case);
    passthru($command, $status);
    expect($status === 0, $case . ' teardown failed');
}
echo "OK: server-side teardown and pre-2FA oracle rejection\n";
