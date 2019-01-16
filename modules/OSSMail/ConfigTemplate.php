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
		'description' => '',
		'validation' => '\App\Validator::standard'
	],
	'default_host' => [
		'default' => ['ssl://imap.gmail.com' => 'ssl://imap.gmail.com'],
		'description' => '',
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
		'description' => '',
		'validation' => '\App\Validator::bool'
	],
	'default_port' => [
		'default' => 993,
		'description' => 'Port used to connect to IMAP',
		'validation' => '\App\Validator::naturalNumber'
	],
	'smtp_server' => [
		'default' => 'ssl://smtp.gmail.com',
		'description' => 'Name of SMTP server',
		'validation' => ''
	],
	'smtp_port' => [
		'default' => 465,
		'description' => 'Default smtp port',
		'validation' => '\App\Validator::naturalNumber'
	],
	'smtp_user' => [
		'default' => '%u',
		'description' => '',
		'validation' => ''
	],
	'smtp_pass' => [
		'default' => '%p',
		'description' => '',
		'validation' => ''
	],
	'support_url' => [
		'default' => 'http://yetiforce.com',
		'description' => '',
		'validation' => ''
	],
	'des_key' => [
		'default' => 'rGOQ26hR%gxlZk=QA!$HMOvb',
		'description' => '',
		'validation' => ''
	],
	'username_domain' => [
		'default' => 'gmail.com',
		'description' => '',
		'validation' => ''
	],
	'product_name' => [
		'default' => 'YetiForce',
		'description' => '',
		'validation' => ''
	],
	'plugins' => [
		'default' => ['identity_smtp', 'ical_attachments', 'yetiforce', 'thunderbird_labels', 'zipdownload', 'archive', 'authres_status'],
		'description' => '',
		'validation' => ''
	],
	'language' => [
		'default' => 'en_US',
		'description' => '',
		'validation' => ''
	],
	'mime_param_folding' => [
		'default' => 0,
		'description' => '',
		'validation' => '\App\Validator::naturalNumber'
	],
	'skin_logo' => [
		'default' => ['*' => '/images/null.png'],
		'description' => '',
		'validation' => ''
	],
	'ip_check' => [
		'default' => false,
		'description' => '',
		'validation' => '\App\Validator::bool'
	],
	'enable_spellcheck' => [
		'default' => true,
		'description' => '',
		'validation' => '\App\Validator::bool'
	],
	'identities_level' => [
		'default' => '0',
		'description' => '',
		'validation' => ''
	],
	'auto_create_user' => [
		'default' => true,
		'description' => '',
		'validation' => '\App\Validator::bool'
	],
	'mail_pagesize' => [
		'default' => 30,
		'description' => '',
		'validation' => '\App\Validator::naturalNumber'
	],
	'addressbook_pagesize' => [
		'default' => 50,
		'description' => '',
		'validation' => '\App\Validator::naturalNumber'
	],
	'prefer_html' => [
		'default' => true,
		'description' => '',
		'validation' => '\App\Validator::bool'
	],
	'preview_pane' => [
		'default' => false,
		'description' => '',
		'validation' => '\App\Validator::bool'
	],
	'htmleditor' => [
		'default' => '1',
		'description' => '',
		'validation' => ''
	],
	'draft_autosave' => [
		'default' => 300,
		'description' => '',
		'validation' => '\App\Validator::naturalNumber'
	],
	'mdn_requests' => [
		'default' => '0',
		'description' => '',
		'validation' => ''
	],
	'session_lifetime' => [
		'default' => 30,
		'description' => '',
		'validation' => '\App\Validator::naturalNumber'
	],
	'sendmail_delay' => [
		'default' => 0,
		'description' => '',
		'validation' => '\App\Validator::naturalNumber'
	],
	'date_long' => [
		'default' => 'Y-m-d H:i',
		'description' => '',
		'validation' => ''
	],
	'date_format' => [
		'default' => 'Y-m-d',
		'description' => '',
		'validation' => ''
	],
	'time_format' => [
		'default' => 'H:i',
		'description' => '',
		'validation' => ''
	],
	'show_images' => [
		'default' => '0',
		'description' => '',
		'validation' => ''
	],
	'imap_cache' => [
		'default' => 'db',
		'description' => '',
		'validation' => ''
	],
	'messages_cache' => [
		'default' => 'db',
		'description' => '',
		'validation' => ''
	],
	'reply_mode' => [
		'default' => 1,
		'description' => '',
		'validation' => '\App\Validator::naturalNumber'
	],
	'imap_max_retries' => [
		'default' => 0,
		'description' => '',
		'validation' => '\App\Validator::naturalNumber'
	],
	'imap_params' => [
		'default' => [],
		'description' => 'Enable this for imap and MS Exchange bug "Kerberos error: Credentials cache file  ... not found "DISABLE_AUTHENTICATOR" => "GSSAPI"',
		'validation' => ''
	],
	'debug_level' => [
		'default' => AppConfig::debug('ROUNDCUBE_DEBUG_LEVEL'),
		'description' => '',
		'validation' => ''
	],
	'per_user_logging' => [
		'default' => AppConfig::debug('ROUNDCUBE_PER_USER_LOGGING'),
		'description' => '',
		'validation' => ''
	],
	'smtp_log' => [
		'default' => AppConfig::debug('ROUNDCUBE_SMTP_LOG'),
		'description' => '',
		'validation' => ''
	],
	'log_logins' => [
		'default' => AppConfig::debug('ROUNDCUBE_LOG_LOGINS'),
		'description' => '',
		'validation' => ''
	],
	'log_session' => [
		'default' => AppConfig::debug('ROUNDCUBE_LOG_SESSION'),
		'description' => '',
		'validation' => ''
	],
	'sql_debug' => [
		'default' => AppConfig::debug('ROUNDCUBE_SQL_DEBUG'),
		'description' => '',
		'validation' => ''
	],
	'imap_debug' => [
		'default' => AppConfig::debug('ROUNDCUBE_IMAP_DEBUG'),
		'description' => '',
		'validation' => ''
	],
	'ldap_debug' => [
		'default' => AppConfig::debug('ROUNDCUBE_LDAP_DEBUG'),
		'description' => '',
		'validation' => ''
	],
	'smtp_debug' => [
		'default' => AppConfig::debug('ROUNDCUBE_SMTP_DEBUG'),
		'description' => '',
		'validation' => ''
	],
	'devel_mode' => [
		'default' => AppConfig::debug('ROUNDCUBE_DEVEL_MODE'),
		'description' => '',
		'validation' => ''
	],
	'log_dir' => [
		'default' => RCUBE_INSTALL_PATH . '/../../../../cache/logs/',
		'description' => '',
		'validation' => ''
	],
	'temp_dir' => [
		'default' => RCUBE_INSTALL_PATH . '/../../../../cache/mail/',
		'description' => '',
		'validation' => ''
	],
	'imap_conn_options' => [
		'default' => [
			'ssl' => [
				'verify_peer' => false,
				'verify_peer_name' => false,
			],
		],
		'description' => '',
		'validation' => ''
	],
	'smtp_conn_options' => [
		'default' => [
			'ssl' => [
				'verify_peer' => false,
				'verify_peer_name' => false,
			],
		],
		'description' => '',
		'validation' => ''
	],
	'smtp_timeout' => [
		'default' => 5,
		'description' => '',
		'validation' => ''
	],
	'smtp_helo_host' => [
		'default' => 'YetiForceCRM',
		'description' => '',
		'validation' => ''
	],
	'skin' => [
		'default' => 'yetiforce',
		'description' => '',
		'validation' => ''
	],
	'list_cols' => [
		'default' => ['flag', 'status', 'subject', 'fromto', 'date', 'size', 'attachment', 'authres_status', 'threads'],
		'description' => '',
		'validation' => ''
	],
	'enable_authres_status_column' => [
		'default' => true,
		'description' => '',
		'validation' => '\App\Validator::bool'
	],
	'show_statuses' => [
		'default' => 127,
		'description' => '',
		'validation' => '\App\Validator::naturalNumber'
	],
	'root_directory' => [
		'default' => ROOT_DIRECTORY . DIRECTORY_SEPARATOR,
		'description' => '',
		'validation' => ''
	],
	'imap_open_add_connection_type' => [
		'default' => true,
		'description' => '',
		'validation' => '\App\Validator::bool'
	],
	'enable_variables_in_signature' => [
		'default' => false,
		'description' => '',
		'validation' => '\App\Validator::bool'
	],
];
