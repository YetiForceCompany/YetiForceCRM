<?php
/**
 * OSSMail module config.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
return [
	'product_name' => [
		'default' => 'YetiForce',
		'description' => 'Product name',
		'validation' => function () {
			$arg = func_get_arg(0);
			return $arg && \App\Purifier::purify($arg);
		},
		'sanitization' => '\App\Purifier::purify'
	],
	'validate_cert' => [
		'default' => false,
		'description' => 'Validate cert',
		'validation' => '\App\Validator::bool',
		'sanitization' => '\App\Purifier::bool'
	],
	'imap_open_add_connection_type' => [
		'default' => true,
		'description' => 'Add connection type',
		'validation' => '\App\Validator::bool',
		'sanitization' => '\App\Purifier::bool'
	],
	'default_host' => [
		'default' => ['ssl://imap.gmail.com' => 'ssl://imap.gmail.com'],
		'description' => 'Default host.',
		'validation' => function () {
			$arg = func_get_arg(0);
			if (!$arg) {
				return false;
			}
			$arg = (array) \App\Purifier::purify($arg);
			foreach ($arg as $url) {
				if (!\App\Validator::url($url)) {
					return false;
				}
			}
			return true;
		},
		'sanitization' => function () {
			$values = func_get_arg(0);
			if (!is_array($values)) {
				$values = [$values];
			}
			$saveValue = [];
			foreach ($values as $value) {
				$value = \App\Purifier::purify($value);
				$saveValue[$value] = $value;
			}
			return $saveValue;
		}
	],
	'default_port' => [
		'default' => 993,
		'description' => 'Port used to connect to IMAP.',
		'validation' => '\App\Validator::port',
		'sanitization' => function () {
			return (int) func_get_arg(0);
		}
	],
	'smtp_server' => [
		'default' => 'ssl://smtp.gmail.com',
		'description' => 'Name of SMTP server',
		'validation' => function () {
			$arg = func_get_arg(0);
			return $arg && \App\Purifier::purify($arg);
		},
		'sanitization' => '\App\Purifier::purify'
	],
	'smtp_user' => [
		'default' => '%u',
		'description' => 'Login to SMTP server',
		'validation' => function () {
			$arg = func_get_arg(0);
			return $arg && !is_numeric($arg) && is_string($arg) && $arg === strip_tags($arg) && 256 > \App\TextParser::getTextLength($arg);
		},
		'sanitization' => '\App\Purifier::encodeHtml'
	],
	'smtp_pass' => [
		'default' => '%p',
		'description' => "SMTP password (if required) if you use %p as the password Roundcube will use the current user's password for login",
		'validation' => function () {
			$arg = func_get_arg(0);
			return !empty($arg) && 256 > \App\TextParser::getTextLength($arg);
		}
	],
	'smtp_port' => [
		'default' => 465,
		'description' => 'Default smtp port',
		'validation' => '\App\Validator::port',
		'sanitization' => function () {
			return (int) func_get_arg(0);
		}
	],
	'language' => [
		'default' => 'en_US',
		'description' => 'Set default language',
		'validation' => function () {
			$arg = func_get_arg(0);
			return $arg && in_array($arg, \Settings_OSSMail_Config_Model::LANGUAGES);
		}
	],
	'username_domain' => [
		'default' => 'gmail.com',
		'description' => 'User name domain',
		'validation' => function () {
			$arg = func_get_arg(0);
			return '' === $arg || \App\Validator::domain($arg);
		}
	],
	'skin_logo' => [
		'default' => ['*' => '/images/null.png'],
		'description' => 'Skin logo',
		'validation' => function () {
			$arg = func_get_arg(0);
			return !empty($arg) && \App\Purifier::purify($arg);
		},
		'sanitization' => function () {
			$arg = func_get_arg(0);
			return ['*' => \App\Purifier::encodeHtml($arg)];
		}
	],
	'ip_check' => [
		'default' => false,
		'description' => 'Ip check.',
		'validation' => '\App\Validator::bool',
		'sanitization' => '\App\Purifier::bool'
	],
	'enable_spellcheck' => [
		'default' => true,
		'description' => 'Enable spell check',
		'validation' => '\App\Validator::bool',
		'sanitization' => '\App\Purifier::bool'
	],
	'identities_level' => [
		'default' => 0,
		'description' => 'Identities level.',
		'validation' => function () {
			$arg = func_get_arg(0);
			return is_numeric($arg) && in_array($arg, [0, 1, 2, 3, 4]);
		},
		'sanitization' => function () {
			return (int) func_get_arg(0);
		}
	],
	'session_lifetime' => [
		'default' => 30,
		'description' => 'Set session lifetime',
		'validation' => '\App\Validator::naturalNumber',
		'sanitization' => function () {
			return (int) func_get_arg(0);
		}
	],
	//------------------------------------------------------------------------------------------------------------
	'db_prefix' => [
		'default' => 'roundcube_',
		'description' => 'Set default prefix'
	],
	'support_url' => [
		'default' => 'http://yetiforce.com',
		'description' => 'Support url'
	],
	'des_key' => [
		'default' => 'rGOQ26hR%gxlZk=QA!$HMOvb',
		'description' => 'Encryption key of data',
	],
	'plugins' => [
		'default' => ['identity_smtp', 'yetiforce', 'thunderbird_labels', 'zipdownload', 'archive', 'authres_status'],
		'description' => 'List of plugins',
	],
	'mime_param_folding' => [
		'default' => 0,
		'description' => 'Mime param folding'
	],
	'auto_create_user' => [
		'default' => true,
		'description' => 'Auto create user.'
	],
	'mail_pagesize' => [
		'default' => 30,
		'description' => 'Mail page size.'
	],
	'addressbook_pagesize' => [
		'default' => 50,
		'description' => 'Address book page size.'
	],
	'prefer_html' => [
		'default' => true,
		'description' => 'Turn on/off prefer html'
	],
	'preview_pane' => [
		'default' => false,
		'description' => 'Turn on/off preview pane'
	],
	'htmleditor' => [
		'default' => 1,
		'description' => 'Html editor',
	],
	'draft_autosave' => [
		'default' => 300,
		'description' => 'Set autosave time'
	],
	'mdn_requests' => [
		'default' => 0,
		'description' => 'Mdn requests'
	],
	'sendmail_delay' => [
		'default' => 0,
		'description' => 'Send mail delay'
	],
	'date_long' => [
		'default' => 'Y-m-d H:i',
		'description' => 'Set the long date format',
	],
	'date_format' => [
		'default' => 'Y-m-d',
		'description' => 'Set date format',
	],
	'time_format' => [
		'default' => 'H:i',
		'description' => 'Set time format',
	],
	'show_images' => [
		'default' => 0,
		'description' => 'Turn on/off show images. Value: 0/1',
	],
	'imap_cache' => [
		'default' => 'db',
		'description' => 'Imap cache',
	],
	'messages_cache' => [
		'default' => 'db',
		'description' => 'messages_cache',
	],
	'reply_mode' => [
		'default' => 1,
		'description' => 'Set reply mode'
	],
	'imap_max_retries' => [
		'default' => 0,
		'description' => 'Max retries imap '
	],
	'imap_params' => [
		'default' => [],
		'description' => 'Enable this for imap and MS Exchange bug "Kerberos error: Credentials cache file  ... not found "DISABLE_AUTHENTICATOR" => "GSSAPI"',
	],
	'debug_level' => [
		'type' => 'function',
		'default' => 'return \Config\Debug::$ROUNDCUBE_DEBUG_LEVEL;',
		'description' => 'Debug level. sum of: 1 = log; 4 = show, 8 = trace'
	],
	'per_user_logging' => [
		'type' => 'function',
		'default' => 'return \Config\Debug::$ROUNDCUBE_PER_USER_LOGGING;',
		'description' => 'Per user logging',
	],
	'smtp_log' => [
		'type' => 'function',
		'default' => 'return \Config\Debug::$ROUNDCUBE_SMTP_LOG;',
		'description' => 'Log sent messages to cache/logs/sendmail',
	],
	'log_logins' => [
		'type' => 'function',
		'default' => 'return \Config\Debug::$ROUNDCUBE_LOG_LOGINS;',
		'description' => 'Logins successful/failed',
	],
	'log_session' => [
		'type' => 'function',
		'default' => 'return \Config\Debug::$ROUNDCUBE_LOG_SESSION;',
		'description' => 'Session authentication debug',
	],
	'sql_debug' => [
		'type' => 'function',
		'default' => 'return \Config\Debug::$ROUNDCUBE_SQL_DEBUG;',
		'description' => 'Sql queries debug',
	],
	'imap_debug' => [
		'type' => 'function',
		'default' => 'return \Config\Debug::$ROUNDCUBE_IMAP_DEBUG;',
		'description' => 'Imap conversation debug',
	],
	'ldap_debug' => [
		'type' => 'function',
		'default' => 'return \Config\Debug::$ROUNDCUBE_LDAP_DEBUG;',
		'description' => 'Ldap conversation debug',
	],
	'smtp_debug' => [
		'type' => 'function',
		'default' => 'return \Config\Debug::$ROUNDCUBE_SMTP_DEBUG;',
		'description' => 'Smtp conversation debug',
	],
	'devel_mode' => [
		'type' => 'function',
		'default' => 'return \Config\Debug::$ROUNDCUBE_DEVEL_MODE;',
		'description' => 'Debugging information about php memory consumption',
	],
	'log_dir' => [
		'type' => 'function',
		'default' => 'if (!defined(\'RCUBE_INSTALL_PATH\')) {
	define(\'RCUBE_INSTALL_PATH\', realpath(ROOT_DIRECTORY . DIRECTORY_SEPARATOR . \'public_html\' . DIRECTORY_SEPARATOR . \'modules\' . DIRECTORY_SEPARATOR . \'OSSMail\' . DIRECTORY_SEPARATOR . \'roundcube\'));
}
return RCUBE_INSTALL_PATH . "/../../../../cache/logs/";',
		'description' => 'Log dir',
	],
	'temp_dir' => [
		'type' => 'function',
		'default' => 'if (!defined(\'RCUBE_INSTALL_PATH\')) {
	define(\'RCUBE_INSTALL_PATH\', realpath(ROOT_DIRECTORY . DIRECTORY_SEPARATOR . \'public_html\' . DIRECTORY_SEPARATOR . \'modules\' . DIRECTORY_SEPARATOR . \'OSSMail\' . DIRECTORY_SEPARATOR . \'roundcube\'));
}
return RCUBE_INSTALL_PATH . "/../../../../cache/mail/";',
		'description' => 'Temp dir',
	],
	'db_dsnw' => [
		'type' => 'function',
		'default' => "return 'mysql://' . \Config\Db::\$db_username . ':' . \Config\Db::\$db_password . '@' . \Config\Db::\$db_server . ':' . \Config\Db::\$db_port . '/' . \Config\Db::\$db_name;",
		'description' => 'Database connection string (DSN) for read+write operations'
	],
	'imap_conn_options' => [
		'default' => [
			'ssl' => [
				'verify_peer' => false,
				'verify_peer_name' => false,
			],
		],
		'description' => 'Connection options imap.',
	],
	'smtp_conn_options' => [
		'default' => [
			'ssl' => [
				'verify_peer' => false,
				'verify_peer_name' => false,
			],
		],
		'description' => 'Connection options smtp.',
	],
	'smtp_timeout' => [
		'default' => 5,
		'description' => 'Smtp time out',
	],
	'smtp_helo_host' => [
		'default' => 'YetiForceCRM',
		'description' => 'The value to give when sending'
	],
	'skin' => [
		'default' => 'yetiforce',
		'description' => 'Set the skin'
	],
	'list_cols' => [
		'default' => ['flag', 'status', 'subject', 'fromto', 'date', 'size', 'attachment', 'authres_status', 'threads'],
		'description' => 'List cols',
	],
	'enable_authres_status_column' => [
		'default' => true,
		'description' => 'Enable authres status column'
	],
	'show_statuses' => [
		'default' => 127,
		'description' => 'Show statuses'
	],
	'root_directory' => [
		'default' => new \Nette\PhpGenerator\PhpLiteral('ROOT_DIRECTORY . DIRECTORY_SEPARATOR'),
		'description' => 'Root directory',
	],
	'enable_variables_in_signature' => [
		'default' => false,
		'description' => 'Enable variables in signature'
	],
];
