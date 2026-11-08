<?php
if (empty($argv[1])) { fwrite(STDERR, "Usage: php tests/roundcube_compat.php /path/to/roundcube/\n"); exit(1); }
$base=$argv[1];
define('INSTALL_PATH',rtrim($base,'/').'/');
require INSTALL_PATH.'program/include/clisetup.php';
class CompatUser extends rcube_user {
 public $test_prefs=[];
 public function __construct() { $this->ID=PHP_INT_MAX; $this->data=['username'=>'compatibility-test@example.invalid','user_id'=>0]; }
 public function get_prefs(){return $this->test_prefs;}
 public function save_prefs($p,$no_session=false){$this->test_prefs=$p;return true;}
}
function check($v,$m){if(!$v)throw new Exception($m);echo 'OK: '.$m.PHP_EOL;}
$rcmail=rcmail::get_instance();
$rcmail->user=new CompatUser();
$rcmail->task='login';
$_SERVER['REMOTE_ADDR']='192.0.2.55';
$rcmail->plugins->load_plugin('twofactor_gauthenticator',true);
$plugin=$rcmail->plugins->get_plugin('twofactor_gauthenticator');
check($plugin instanceof twofactor_gauthenticator,'Plugin loads through Roundcube '.RCMAIL_VERSION.' API');
$secret='JBSWY3DPEHPK3PXP';
$rcmail->user->test_prefs=['twofactor_gauthenticator'=>['activate'=>true,'secret'=>$secret,'recovery_codes'=>['0123456789','test-recovery-2']]];
$method=new ReflectionMethod($plugin,'__checkCode');$method->setAccessible(true);
$ga=new PHPGangsta_GoogleAuthenticator();$code=$ga->getCode($secret);
check($method->invoke($plugin,$code,$secret),'Valid TOTP accepted');
$bad='0000000';
check(!$method->invoke($plugin,$bad,$secret),'Invalid TOTP rejected');
check(!$method->invoke($plugin,'1e1234',$secret),'Non-decimal OTP rejected');
$recovery=new ReflectionMethod($plugin,'__isRecoveryCode');$recovery->setAccessible(true);
check($recovery->invoke($plugin,'0123456789'),'Recovery code accepted');
check(!$recovery->invoke($plugin,123456789),'Numeric recovery-code coercion rejected');
$consume=new ReflectionMethod($plugin,'__consumeRecoveryCode');$consume->setAccessible(true);$consume->invoke($plugin,'0123456789');
check(!$recovery->invoke($plugin,'0123456789'),'Recovery code consumed after use');
$_SESSION['twofactor_gauthenticator_2FA_login']=time();$rcmail->task='mail';
check($plugin->check_2FAlogin(['template'=>'mail'])===['template'=>'mail'],'Verified session passes the page gate');
$dir = sys_get_temp_dir().'/rc-2fa-compat-'.bin2hex(random_bytes(8));
$rcmail->config->set('twofactor_rate_limit_dir',$dir);
$verify = new ReflectionMethod($plugin,'__verifyLoginCode'); $verify->setAccessible(true);
try {
 check($verify->invoke($plugin,$code),'Login verifier accepts a valid TOTP');
 check(!$verify->invoke($plugin,'invalid'),'Login verifier rejects a malformed code');
 check(!$verify->invoke($plugin,$code),'Persisted cooldown rejects even a valid code');
} finally {
 foreach (glob($dir.'/*') ?: [] as $file) unlink($file);
 if (is_dir($dir)) rmdir($dir);
}
echo 'COMPATIBILITY_TESTS_PASSED'.PHP_EOL;
