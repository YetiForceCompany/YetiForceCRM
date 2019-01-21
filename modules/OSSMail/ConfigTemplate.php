<?php
/**
 * OSSMail module config.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
return [
	'db_prefix' => [
		'default' => 'roundcube_',
		'description' => 'Set default prefix',
		'validation' => '\App\Validator::standard'
	],
	'default_host' => [
		'default' => ['ssl://imap.gmail.com' => 'ssl://imap.gmail.com'],
		'description' => 'Set default host',
		'validation' => '\App\Validator::standard',
		'sanitization' => function () {
			$values = func_get_arg(0);
			$values = is_array($values) ? array_map('\App\Purifier::encodeHtml', $values) : \App\Purifier::encodeHtml($values);
			if (!is_array($values)) {
				$values = [$values];
			}
			$saveValue = [];
			foreach ($values as $value) {
				$saveValue[$value] = $value;
			}
			return $saveValue;
		}
	],
	'validate_cert' => [
		'default' => false,
		'description' => 'Validate cert',
		'validation' => '\App\Validator::bool',
		'sanitization' => '\App\Purifier::bool'
	],
	'default_port' => [
		'default' => 993,
		'description' => 'Port used to connect to IMAP',
		'validation' => '\App\Validator::naturalNumber'
	],
	'smtp_server' => [
		'default' => 'ssl://smtp.gmail.com',
		'description' => 'Name of SMTP server',
		'validation' => '\App\Validator::standard',
	],
	'smtp_port' => [
		'default' => 465,
		'description' => 'Default smtp port',
		'validation' => '\App\Validator::naturalNumber'
	],
	'smtp_user' => [
		'default' => '%u',
		'description' => 'Login to SMTP server',
	],
	'smtp_pass' => [
		'default' => '%p',
		'description' => 'Password to SMTP server ',
	],
	'support_url' => [
		'default' => 'http://yetiforce.com',
		'description' => 'Support url',
		'validation' => '\App\Validator::standard'
	],
	'des_key' => [
		'default' => 'rGOQ26hR%gxlZk=QA!$HMOvb',
		'description' => 'Encryption key of data',
	],
	'username_domain' => [
		'default' => 'gmail.com',
		'description' => 'User name domain',
		'validation' => '\App\Validator::standard'
	],
	'product_name' => [
		'default' => 'YetiForce',
		'description' => 'Product name',
		'validation' => '\App\Validator::standard'
	],
	'plugins' => [
		'default' => ['identity_smtp', 'yetiforce', 'thunderbird_labels', 'zipdownload', 'archive', 'authres_status'],
		'description' => 'List of plugins',
	],
	'language' => [
		'default' => 'en_US',
		'description' => 'Set default language',
	],
	'mime_param_folding' => [
		'default' => 0,
		'description' => 'Mime param folding',
		'validation' => '\App\Validator::naturalNumber'
	],
	'skin_logo' => [
		'default' => ['*' => '/images/null.png'],
		'description' => 'Skin logo',
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
	],
	'auto_create_user' => [
		'default' => true,
		'description' => 'Auto create user.',
		'validation' => '\App\Validator::bool',
		'sanitization' => '\App\Purifier::bool'
	],
	'mail_pagesize' => [
		'default' => 30,
		'description' => 'Mail page size.',
		'validation' => '\App\Validator::naturalNumber'
	],
	'addressbook_pagesize' => [
		'default' => 50,
		'description' => 'Address book page size.',
		'validation' => '\App\Validator::naturalNumber'
	],
	'prefer_html' => [
		'default' => true,
		'description' => 'Turn on/off prefer html',
		'validation' => '\App\Validator::bool',
		'sanitization' => '\App\Purifier::bool'
	],
	'preview_pane' => [
		'default' => false,
		'description' => 'Turn on/off preview pane',
		'validation' => '\App\Validator::bool',
		'sanitization' => '\App\Purifier::bool'
	],
	'htmleditor' => [
		'default' => 1,
		'description' => 'Html editor',
	],
	'draft_autosave' => [
		'default' => 300,
		'description' => 'Set autosave time',
		'validation' => '\App\Validator::naturalNumber'
	],
	'mdn_requests' => [
		'default' => 0,
		'description' => 'Mdn requests',
		'validation' => '\App\Validator::naturalNumber'
	],
	'session_lifetime' => [
		'default' => 30,
		'description' => 'Set session lifetime',
		'validation' => '\App\Validator::naturalNumber'
	],
	'sendmail_delay' => [
		'default' => 0,
		'description' => 'Send mail delay',
		'validation' => '\App\Validator::naturalNumber'
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
		'description' => 'Set reply mode',
		'validation' => '\App\Validator::naturalNumber'
	],
	'imap_max_retries' => [
		'default' => 0,
		'description' => 'Max retries imap ',
		'validation' => '\App\Validator::naturalNumber'
	],
	'imap_params' => [
		'default' => [],
		'description' => 'Enable this for imap and MS Exchange bug "Kerberos error: Credentials cache file  ... not found "DISABLE_AUTHENTICATOR" => "GSSAPI"',
	],
	'debug_level' => [
		'default' => AppConfig::debug('ROUNDCUBE_DEBUG_LEVEL'),
		'description' => 'Debug level. sum of: 1 = log; 4 = show, 8 = trace',
		'validation' => function () {
			$arg = func_get_arg(0);
			return in_array($arg, [1, 4, 8]);
		}
	],
	'per_user_logging' => [
		'default' => AppConfig::debug('ROUNDCUBE_PER_USER_LOGGING'),
		'description' => 'Per user logging',
	],
	'smtp_log' => [
		'default' => AppConfig::debug('ROUNDCUBE_SMTP_LOG'),
		'description' => 'Log sent messages to cache/logs/sendmail',
	],
	'log_logins' => [
		'default' => AppConfig::debug('ROUNDCUBE_LOG_LOGINS'),
		'description' => 'Logins successful/failed',
	],
	'log_session' => [
		'default' => AppConfig::debug('ROUNDCUBE_LOG_SESSION'),
		'description' => 'Session authentication debug',
	],
	'sql_debug' => [
		'default' => AppConfig::debug('ROUNDCUBE_SQL_DEBUG'),
		'description' => 'Sql queries debug',
	],
	'imap_debug' => [
		'default' => AppConfig::debug('ROUNDCUBE_IMAP_DEBUG'),
		'description' => 'Imap conversation debug',
	],
	'ldap_debug' => [
		'default' => AppConfig::debug('ROUNDCUBE_LDAP_DEBUG'),
		'description' => 'Ldap conversation debug',
	],
	'smtp_debug' => [
		'default' => AppConfig::debug('ROUNDCUBE_SMTP_DEBUG'),
		'description' => 'Smtp conversation debug',
	],
	'devel_mode' => [
		'default' => AppConfig::debug('ROUNDCUBE_DEVEL_MODE'),
		'description' => 'Debugging information about php memory consumption',
	],
	'log_dir' => [
		'default' => RCUBE_INSTALL_PATH . '/../../../../cache/logs/',
		'description' => 'Log dir',
	],
	'temp_dir' => [
		'default' => RCUBE_INSTALL_PATH . '/../../../../cache/mail/',
		'description' => 'Temp dir',
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
		'description' => 'The value to give when sending',
		'validation' => '\App\Validator::time'
	],
	'skin' => [
		'default' => 'yetiforce',
		'description' => 'Set the skin',
		'validation' => '\App\Validator::standard'
	],
	'list_cols' => [
		'default' => ['flag', 'status', 'subject', 'fromto', 'date', 'size', 'attachment', 'authres_status', 'threads'],
		'description' => 'List cols',
	],
	'enable_authres_status_column' => [
		'default' => true,
		'description' => 'Enable authres status column',
		'validation' => '\App\Validator::bool',
		'sanitization' => '\App\Purifier::bool'
	],
	'show_statuses' => [
		'default' => 127,
		'description' => 'Show statuses',
		'validation' => '\App\Validator::naturalNumber'
	],
	'root_directory' => [
		'default' => new \Nette\PhpGenerator\PhpLiteral('ROOT_DIRECTORY . DIRECTORY_SEPARATOR'),
		'description' => 'Root directory',
	],
	'imap_open_add_connection_type' => [
		'default' => true,
		'description' => 'Add connection type',
		'validation' => '\App\Validator::bool',
		'sanitization' => '\App\Purifier::bool'
	],
	'enable_variables_in_signature' => [
		'default' => false,
		'description' => 'Enable variables in signature',
		'validation' => '\App\Validator::bool',
		'sanitization' => '\App\Purifier::bool'
	],
];
