Roundcube Webmail plugin: 2-Factor Authentication
==============================================

**Requirements**
- Roundcube Webmail 1.7.0 or newer
- PHP 8.0 or newer with the OpenSSL extension

**Changelog**
 - fixed UI using bootstrap 4.5.x
 - improved UI form functionalities

**Features difference from `alexandregz/twofactor_gauthenticator`**
- Remember Device in days can be configured. It was 30 days before, now the default is 7 days. 30 days is too much.
- When you enable the config `force_enrollment_users` to enforce all users to use 2FA, I have added an option to exclude some users/domain enforcing 2FA.
- When you click the Activate checkbox, will generate the `secret key` and `recovery codes` right away
- You can also regenerate the `secret key`
- QR Code always display
- In testing the codes from you app, will display the server time.
- Settings > 2FA form = fixed to work or display well in Roundcube Webmail v1.5.x
- Cleaner code, better indention, reformat codes using PHPStorm

**How to install this plugin?**
- download the package and extract the contents
- should have a folder called `twofactor_gauthenticator`
- move the folder to roundube install destination under `plugins` folder: `{ROUNDCUBE_INSTALL}/plugins/`
- then define your configuration, copy `{ROUNDCUBE_INSTALL}/plugins/config.inc.php.dist` as `{ROUNDCUBE_INSTALL}/plugins/config.inc.php`
- that's it. login to you account, go to config and `2-factor Authentication` option should appear
- activate the 2FA in your account, fill up the form and you're good to go.

![Login](https://raw.github.com/camilord/twofactor_gauthenticator/master/screenshots/rcube1.5_001.png)

![2Steps](https://raw.github.com/camilord/twofactor_gauthenticator/master/screenshots/rcube1.5_002.png)

![2Steps](https://raw.github.com/camilord/twofactor_gauthenticator/master/screenshots/rcube1.5_003.png)

## NOTES
- before you save the generated secret key, make sure you tested it using the "Test the generated PIN here"
- If you keep getting "incorrect code", meaning your PC time and the server time is not the same.

---
---
---

2Steps verification
==========================

This RoundCube plugin adds the 2-step verification(OTP) to the login proccess.

It works with all TOTP applications [RFC 6238](https://www.rfc-editor.org/info/rfc6238)

Some code by:
[Ricardo Signes](https://github.com/rjbs)
[Justin Buchanan](https://github.com/jusbuc2k)
[Ricardo Iván Vieitez Parra](https://github.com/corrideat)


[GoogleAuthenticator class](https://github.com/PHPGangsta/GoogleAuthenticator/) by Michael Kliewe (to *see* secrets)

[qrcode.js](https://github.com/davidshimjs/qrcodejs) by ShimSangmin

Also thx to [Victor R. Rodriguez Dominguez](https://github.com/vrdominguez) for some ideas and support  



![Login](https://raw.github.com/alexandregz/twofactor_gauthenticator/master/screenshots/001-login.png)

![2Steps](https://raw.github.com/alexandregz/twofactor_gauthenticator/master/screenshots/002-2steps.png)


Installation
------------
- Clone from github:
    HOME_RC/plugins$ git clone [https://github.com/alexandregz/twofactor_gauthenticator.git](https://github.com/alexandregz/twofactor_gauthenticator.git)
    

(Or use composer
     HOME_RC$ composer require alexandregz/twofactor_gauthenticator:dev-master
     
 NOTE: Answer **N** when composer ask you about plugin activation)

- Activate the plugin into HOME_RC/config/config.inc.php:
    $config['plugins'] = array('twofactor_gauthenticator');


Configuration
-------------
Go to the Settings task and in the "2steps Google verification" menu, click 'Setup all fields (needs Save)'.

The plugin automatically creates the secret for you.

NOTE: plugin must be base32 valid characters ([A-Z][2-7]), see https://github.com/alexandregz/twofactor_gauthenticator/blob/master/PHPGangsta/GoogleAuthenticator.php#L18

From https://github.com/alexandregz/twofactor_gauthenticator/issues/139


	
To add accounts to the app, you can use the QR-Code (easy-way) or type the secret.
After checking the first code click 'Save'.

![Settings by default](https://raw.github.com/alexandregz/twofactor_gauthenticator/master/screenshots/003-settings_default.png)

![Settings OK](https://raw.github.com/alexandregz/twofactor_gauthenticator/master/screenshots/004-settings_ok.png)

![QR-Code example](https://raw.github.com/alexandregz/twofactor_gauthenticator/master/screenshots/005-settings_qr_code.png)


Also, you can add "Recovery codes" for use one time (they delete when are used). Recovery codes are OPTIONAL, so they can be left blank.

![Recovery codes](https://raw.github.com/alexandregz/twofactor_gauthenticator/master/screenshots/006-recovery_codes.png) 


![Check codes](https://raw.github.com/alexandregz/twofactor_gauthenticator/master/screenshots/007-check_code.png) 



![Recovery codes](https://raw.github.com/alexandregz/twofactor_gauthenticator/master/screenshots/008-msg_infor_about_enrollment.png) 



Enrollment Users
----------------
If config value *force_enrollment_users* is true, **ALL** users needs to login with 2-step method. They receive alert message about that, and they can't skip without save configuration


Samefield
---------
If config value *2step_codes_on_login_form* is true, 2-step codes (and recovery) must be sended with password value, append to this, from the login screen: "Normal" codes just following password (passswordCODE), recovery codes after two pipes (passsword||RECOVERYCODE)

Actually only into samefield branch


Codes
-----
Codes have a 2*30 seconds clock tolerance, like by default with Google app (Maybe editable in future versions)


License
-------
MIT, see License

Notes
-----
Tested with RoundCube 0.9.5 and Google app. Also with Roundcube 1.0.4

Remember, sync time it's essential for TOTP: "For this to work, the clocks of the user's device and the server need to be roughly synchronized (the server will typically accept one-time passwords generated from timestamps that differ by ±1 from the client's timestamp)" (from http://en.wikipedia.org/wiki/Time-based_One-time_Password_Algorithm)

Author
------
Alexandre Espinosa Menor <aemenor@gmail.com>

Issues
------
Open issues using github, don't send me emails about that, please -usually Gmail marks messages like SPAM

Testing
-------
- Vagrant: https://github.com/alexandregz/vagrant-twofactor_gauthenticator
- Docker: https://hub.docker.com/r/alexandregz/twofactor_gauthenticator/

Using with Kolab
----------------
Add a symlink into the public_html/assets directory

Show explained https://github.com/alexandregz/twofactor_gauthenticator/issues/29#issuecomment-156838186 by https://github.com/d7415

Client implementations
----------------------

You can use various [OTP clients](https://en.wikipedia.org/wiki/HMAC-based_One-time_Password_Algorithm#Applications) -link by https://github.com/helmo


Logs
----

Suggested by simon@magrin.com

To log errors with bad codes, change the $_enable_logs variable to true.

The logs are stored to the file HOME_RC/logs/log_errors_2FA.txt -directory must be created



Whitelist
---------

You can define whitelist IPs into config file (see config.inc.php.dist) to automatic login -the plugin don't ask you for code


Uninstall
---------

To deactivate the plugin, you can use two methods:

- To only one user: restore the user prefs from DB to null (rouncubeDB.users.preferences) -the user plugin options stored there.

- To all: remove the plugin from config.inc.php/remove the plugin itself


Activate only for specific users
--------------------------------

- Use config.inc.php file (see config.inc.php.dist example file)

- Modify array  **users_allowed_2FA** with users that you want to use plugin. NOTE: you can use regular expressions



## Use with 1.3.x version

Use *1.3.9-version* branch

`$ git checkout 1.3.9-version`

If you download 1.4.x RC version (with *elastic skin*), use *master* version normally (thx to [tborgans](https://github.com/tborgans))

![Elastic Skin start](https://raw.githubusercontent.com/alexandregz/twofactor_gauthenticator/master/screenshots/009-elastic_skin_start.png)

![Elastic Skin config](https://raw.githubusercontent.com/alexandregz/twofactor_gauthenticator/master/screenshots/010-elastic_skin_config.png)





## Security incident 2022-04-02

Reported by kototilt@haiiro.dev (thx for the report and the PoC script)

I make a little modification on script to not allow to save config without param session generated from a rendered page, to force user to introduce previously 2FA code and navigate across site.

NOTE: Also I check if user have 2FA activated because with only first condition -check SESSION- app kick out me before to activate 2FA.


#### `twofactor_gauthenticator_save()`

On function `twofactor_gauthenticator_save()` I added this code:

```php
    // save config
    function twofactor_gauthenticator_save() 
    {
        $rcmail = rcmail::get_instance();

		// 2022-04-03: Corrected security incidente reported by kototilt@haiiro.dev
		//					"2FA in twofactor_gauthenticator can be bypassed allowing an attacker to disable 2FA or change the TOTP secret."
		//
		// Solution: if user don't have session created by any rendered page, we kick out
		$config_2FA = self::__get2FAconfig();
		if(!$_SESSION['twofactor_gauthenticator_2FA_login'] && $config_2FA['activate']) {
			$this->__exitSession();
		}
```


The idea is to create a session variable from a rendered page, redirected from `__goingRoundcubeTask` function (redirector to `roundcube tasks`)


#### tests with PoC python script

Previously, with security compromised:

```bash
alex@vosjod:~/Desktop/report$ ./poc.py
Password:xxxxxxxx
1. Fetching login page (http://localhost:8888/roundcubemail-1.4.8)
2. Logging in
  POST http://localhost:8888/roundcubemail-1.4.8/?_task=login
3. Disabling 2FA
  POST http://localhost:8888/roundcubemail-1.4.8/?_task=settings&_action=plugin.twofactor_gauthenticator-save
  POST returned task "settings"
2FA disabled!
``` 

Modified code and tested again, not allowed to deactivated/modified without going to a RC task (with 2FA authentication):

```bash
alex@vosjod:~/Desktop/report$ ./poc.py
Password:xxxxxxxxx
1. Fetching login page (http://localhost:8888/roundcubemail-1.4.8)
2. Logging in
  POST http://localhost:8888/roundcubemail-1.4.8/?_task=login
3. Disabling 2FA
  POST http://localhost:8888/roundcubemail-1.4.8/?_task=settings&_action=plugin.twofactor_gauthenticator-save
  POST returned task "login"
Expected "settings" task, something went wrong
``` 

## Local security regression test

The repository includes a dependency-free regression test for the TOTP
implementation, CIDR matching, the 2FA session gate, trusted client IP
handling, and remember-device signature verification.

Run it with PHP 8.0 or newer:

```sh
php tests/security_regression.php
```

If PHP is not installed locally, run it in Docker:

```sh
docker run --rm -v "$PWD":/src -w /src php:8.2-cli php tests/security_regression.php
```

The plugin must still be tested against a real Roundcube 1.7 installation;
the regression test does not replace an integration test.

## Security release 2.0.1 (2026-11-08)

This fork backports protection for CVE-2026-107145 and CVE-2026-107146.
CVE-2026-107147 was already addressed by using only `REMOTE_ADDR` for IP
whitelisting (forwarded headers remain untrusted).

- Failed OTP/recovery-code attempts impose per-account cooldowns of 1, 2, 5,
  20, 60, 300 and then 600 seconds. Counters survive logout and IP changes;
  verification and updates are serialized with a file lock. Success resets
  the counter; penalties expire after 24 hours without an evaluated attempt.
- Every rejected login code destroys the server-side Roundcube session and
  clears the remember-device cookie before redirecting to the login screen.
- A startup gate protects action handlers (including AJAX) before successful
  2FA. The enrollment-code endpoint requires an authenticated user, a valid
  request token, an explicit secret, and completed 2FA when already enabled.
- Unavailable or corrupt rate-limit storage denies verification.

The rate-limit directory defaults to `temp_dir/twofactor-rate-limit`; it must
be private, writable by PHP, outside the public document root, and persistent
across container recreation. Override it with `twofactor_rate_limit_dir`.
Multiple PHP replicas must share the same storage with working `flock`
semantics. Back up this directory; clearing it resets all throttles. Do not
routinely delete it with temporary-file cleanup. Individual state files may
be removed by an administrator to recover from corrupted state.

Run both regression suites:

```sh
php tests/security_regression.php
php tests/auth_security.php
```

Upstream advisory: https://cert.pl/en/posts/2026/10/CVE-2026-107145/

## Roundcube 1.7.5 compatibility

The current plugin (version 2.0.0) was checked against Roundcube 1.7.5
with PHP 8.2.35 on 2026-11-08. No plugin code changes were required for API compatibility; see the
subsequent 2.0.1 security fixes above.
The security regression test passed, along with targeted tests using the
actual Roundcube API and an in-memory test user:

- plugin loading;
- valid TOTP acceptance and invalid/non-decimal TOTP rejection;
- strict recovery-code matching and one-time consumption;
- access through the page gate after successful 2FA verification.

Existing users' 2FA preferences were unchanged after the Roundcube upgrade.
These checks did not include an interactive login with a real mailbox and
an authenticator app, and do not constitute a complete security audit.

## Isolated Roundcube 1.7.1 test environment

The `tests/docker` directory contains an isolated Roundcube 1.7.1 and
MariaDB environment. It binds only to `127.0.0.1:18080` and does not connect
to any production mail service.

```sh
cd tests/docker
docker compose up -d
open http://127.0.0.1:18080/installer
```

When finished:

```sh
docker compose down -v
```

## Targeted functional security tests

The plugin was also tested against the isolated Roundcube environment with a
dedicated test mailbox. The following flows passed:

- an invalid TOTP code is rejected and the 2FA challenge remains active;
- a recovery code is accepted once;
- reusing the same recovery code is rejected;
- enabling and disabling 2FA through the authenticated settings flow works;
- normal login and message sending continue to work with 2FA disabled.

The “remember this device for 7 days” option successfully skips the challenge
for the current authenticated session and on a subsequent login after logout.
The test covered the token persistence fix and a PHP-FPM reload on the isolated
mail.itbros.email test server.

The remember-device duration can be selected at login from the durations
allowed by `remember_device_options`. The default configuration offers 7, 30,
and 180 days, with 7 days selected by default. Values are capped at 180 days;
an unlimited lifetime is intentionally not supported.

These checks are targeted integration tests, not a complete security audit.
