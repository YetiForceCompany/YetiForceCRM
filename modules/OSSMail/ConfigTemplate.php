<?php
/**
 * OSSMail module config.
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
return [
	'imap_host' => [
		'default' => ['ssl://imap.gmail.com:993' => 'ssl://imap.gmail.com:993'],
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
			if (!\is_array($values)) {
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
	'smtp_host' => [
		'default' => 'ssl://smtp.gmail.com:465',
		'description' => 'Name of SMTP server',
		'validation' => function () {
			$arg = func_get_arg(0);
			return $arg && \App\Validator::url($arg);
		},
		'sanitization' => '\App\Purifier::purify'
	],
	'username_domain' => [
		'default' => 'gmail.com',
		'description' => 'User name domain',
		'validation' => function () {
			$arg = func_get_arg(0);
			return '' === $arg || \App\Validator::domain($arg);
		}
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
	'imap_params' => [
		'default' => [],
		'description' => 'Enable this for imapConnect and MS Exchange bug "Kerberos error: Credentials cache file  ... not found "DISABLE_AUTHENTICATOR" => "GSSAPI"',
	],
	'smtp_user' => [
		'default' => '%u',
		'description' => 'Login to SMTP server',
		'validation' => function () {
			$arg = func_get_arg(0);
			return $arg && !is_numeric($arg) && \is_string($arg) && $arg === strip_tags($arg) && 256 > \App\TextUtils::getTextLength($arg);
		},
		'sanitization' => '\App\Purifier::encodeHtml'
	],
	'smtp_pass' => [
		'default' => '%p',
		'description' => "SMTP password (if required) if you use %p as the password Roundcube will use the current user's password for login",
		'validation' => function () {
			$arg = func_get_arg(0);
			return !empty($arg) && 256 > \App\TextUtils::getTextLength($arg);
		}
	],
	'language' => [
		'default' => 'en_US',
		'description' => 'Set default language',
		'validation' => function () {
			$arg = func_get_arg(0);
			return $arg && \in_array($arg, \Settings_OSSMail_Config_Model::LANGUAGES);
		}
	],
	'skin_logo' => [
		'default' => '/images/null.png',
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
			return is_numeric($arg) && \in_array($arg, [0, 1, 2, 3, 4]);
		},
		'sanitization' => fn () => (int) func_get_arg(0)
	],
	'session_lifetime' => [
		'default' => 30,
		'description' => 'Session lifetime in minutes',
		'validation' => '\App\Validator::naturalNumber',
		'sanitization' => fn () => (int) func_get_arg(0)
	],
	//------------------------------------------------------------------------------------------------------------
	'db_prefix' => [
		'default' => 'roundcube_',
		'description' => 'Set default prefix'
	],
	'des_key' => [
		'default' => \App\Encryption::generatePassword(24),
		'description' => 'This key is used for encrypting purposes, like storing of imap password in the session. For the default cipher_method a required key length is 24 characters.',
		'validation' => '\App\Validator::alnum',
	],
	'plugins' => [
		'default' => [
			'identity_smtp', 'thunderbird_labels', 'zipdownload', 'archive', 'html5_notifier', 'contextmenu', 'yetiforce', //'enigma'
		],
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
	'imap_cache' => [
		'default' => 'db',
		'description' => "Imap cache, Values: 'db', 'apc' and 'memcache' or 'memcached'",
	],
	'messages_cache' => [
		'default' => 'db',
		'description' => "Enables messages cache. Only 'db' cache is supported.",
	],
	'messages_cache_threshold' => [
		'default' => 1000,
		'description' => "Maximum cached message size in kilobytes.\nNote: On MySQL this should be less than (max_allowed_packet - 30%)",
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
	'time_formats' => [
		'default' => ['G:i', 'H:i', 'g:i a', 'h:i A', 'H:i:s (T P)'],
		'description' => 'give this choice of time formats to the user to select from',
	],
	'show_images' => [
		'default' => 0,
		'description' => 'Display remote resources (inline images, styles). Value: 0 - Never, always ask, 1 - Ask if sender is not in address book, 2 - Always allow',
	],
	'reply_mode' => [
		'default' => 1,
		'description' => 'Set reply mode'
	],
	'default_charset' => [
		'default' => 'UTF-8',
		'description' => 'Use this charset as fallback for message decoding'
	],
	'root_directory' => [
		'type' => 'function',
		'default' => 'return ROOT_DIRECTORY . \DIRECTORY_SEPARATOR;',
		'description' => 'Root directory',
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
	'session_debug' => [
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
		'default' => "return 'mysql://' . \\Config\\Db::\$db_username . ':' . \\Config\\Db::\$db_password . '@' . \\Config\\Db::\$db_server . ':' . \\Config\\Db::\$db_port . '/' . \\Config\\Db::\$db_name;",
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
		'default' => 10,
		'description' => 'Smtp time out',
	],
	'smtp_helo_host' => [
		'default' => 'YetiForceCRM',
		'description' => 'The value to give when sending'
	],
	'product_name' => [
		'default' => '',
		'description' => 'Name your service. This is displayed on the login screen and in the window title',
	],
	'useragent' => [
		'default' => 'YetiForce Webmail',
		'description' => 'Add this user-agent to message headers when sending',
		'validation' => function () {
			$arg = func_get_arg(0);
			return $arg && \App\Purifier::purify($arg);
		},
		'sanitization' => '\App\Purifier::purify'
	],
	'skin' => [
		'default' => 'elastic',
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
	'enable_variables_in_signature' => [
		'default' => false,
		'description' => 'Enable variables in signature'
	],
	'address_book_type' => [
		'default' => '',
		'description' => 'Contact functionality is disabled'
	],
	'message_show_email' => [
		'default' => true,
		'description' => 'Enables display of email address with name instead of a name (and address in title)'
	],
	'addressbook_pagesize' => [
		'default' => 50,
		'description' => 'Address book page size.'
	],
	'junk_mbox' => [
		'default' => '',
		'description' => 'Store spam messages in this mailbox'
	],
];
