<?php

/**
 * Settings ConfReport module model class
 * @package YetiForce.Model
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class Settings_ConfReport_Module_Model extends Settings_Vtiger_Module_Model
{

	/**
	 * variable has all the files and folder that should be writable
	 * @var <Array>
	 */
	public static $writableFilesAndFolders = [
		'Configuration directory' => 'config/',
		'Configuration file' => 'config/config.inc.php',
		'User privileges directory' => 'user_privileges/',
		'Tabdata file' => 'user_privileges/tabdata.php',
		'Menu file' => 'user_privileges/menu_0.php',
		'User privileges file' => 'user_privileges/user_privileges_1.php',
		'Logo directory' => 'public_html/layouts/resources/Logo/',
		'Cache directory' => 'cache/',
		'Address book directory' => 'cache/addressBook/',
		'Image cache directory' => 'cache/images/',
		'Import cache directory' => 'cache/import/',
		'Logs directory' => 'cache/logs/',
		'Session directory' => 'cache/session/',
		'Cache templates directory' => 'cache/templates_c/',
		'Cache upload directory' => 'cache/upload/',
		'Vtlib test directory' => 'cache/vtlib/',
		'Vtlib test HTML directory' => 'cache/vtlib/HTML',
		'Cron modules directory' => 'cron/modules/',
		'Modules directory' => 'modules/',
		'Libraries directory' => 'libraries/',
		'Storage directory' => 'storage/',
		'Product image directory' => 'storage/Products/',
		'User image directory' => 'storage/Users/',
		'Contact image directory' => 'storage/Contacts/',
		'MailView attachments directory' => 'storage/OSSMailView/',
		'Roundcube directory' => 'public_html/modules/OSSMail/',
	];

	/**
	 * List of libraries
	 * @var array
	 */
	public static $library = [
		'LBL_IMAP_SUPPORT' => ['type' => 'f', 'name' => 'imap_open', 'mandatory' => true],
		'LBL_ZLIB_SUPPORT' => ['type' => 'f', 'name' => 'gzinflate', 'mandatory' => true],
		'LBL_PDO_SUPPORT' => ['type' => 'e', 'name' => 'pdo_mysql', 'mandatory' => true],
		'LBL_OPEN_SSL' => ['type' => 'e', 'name' => 'openssl', 'mandatory' => true],
		'LBL_CURL' => ['type' => 'e', 'name' => 'curl', 'mandatory' => true],
		'LBL_GD_LIBRARY' => ['type' => 'e', 'name' => 'gd', 'mandatory' => true],
		'LBL_PCRE_LIBRARY' => ['type' => 'e', 'name' => 'pcre', 'mandatory' => true], //Roundcube
		'LBL_XML_LIBRARY' => ['type' => 'e', 'name' => 'xml', 'mandatory' => true],
		'LBL_JSON_LIBRARY' => ['type' => 'e', 'name' => 'json', 'mandatory' => true],
		'LBL_SESSION_LIBRARY' => ['type' => 'e', 'name' => 'session', 'mandatory' => true],
		'LBL_DOM_LIBRARY' => ['type' => 'e', 'name' => 'dom', 'mandatory' => true],
		'LBL_ZIP_ARCHIVE' => ['type' => 'e', 'name' => 'zip', 'mandatory' => true],
		'LBL_MBSTRING_LIBRARY' => ['type' => 'e', 'name' => 'mbstring', 'mandatory' => true],
		'LBL_SOAP_LIBRARY' => ['type' => 'e', 'name' => 'soap', 'mandatory' => true],
		'LBL_MYSQLND_LIBRARY' => ['type' => 'e', 'name' => 'mysqlnd', 'mandatory' => true],
		'LBL_FILEINFO_LIBRARY' => ['type' => 'e', 'name' => 'fileinfo', 'mandatory' => true],
		'LBL_LIBICONV_LIBRARY' => ['type' => 'e', 'name' => 'iconv', 'mandatory' => true],
		'LBL_EXIF_LIBRARY' => ['type' => 'f', 'name' => 'exif_read_data', 'mandatory' => false],
		'LBL_LDAP_LIBRARY' => ['type' => 'f', 'name' => 'ldap_connect', 'mandatory' => false],
		'LBL_OPCACHE_LIBRARY' => ['type' => 'f', 'name' => 'opcache_get_configuration', 'mandatory' => false],
		'LBL_APCU_LIBRARY' => ['type' => 'e', 'name' => 'apcu', 'mandatory' => false],
	];

	public static function getConfigurationLibrary()
	{
		foreach (static::$library as $k => $v) {
			if ($v['type'] == 'f') {
				$status = function_exists($v['name']);
			} elseif ($v['type'] == 'e') {
				$status = extension_loaded($v['name']);
			}
			static::$library[$k]['status'] = $status ? 'LBL_YES' : 'LBL_NO';
		}
		return static::$library;
	}

	/**
	 * Get system stability configuration
	 * @param bool $instalMode
	 * @return array
	 */
	public static function getStabilityConf($instalMode = false, $onlyError = false)
	{
		$directiveValues = [
			'PHP' => ['prefer' => '7.0.x', 'help' => 'LBL_PHP_HELP_TEXT'],
			'error_reporting' => ['prefer' => 'E_ALL & ~E_NOTICE', 'help' => 'LBL_ERROR_REPORTING_HELP_TEXT'],
			'output_buffering' => ['prefer' => 'On', 'help' => 'LBL_OUTPUT_BUFFERING_HELP_TEXT'],
			'max_execution_time' => ['prefer' => '600', 'help' => 'LBL_MAX_EXECUTION_TIME_HELP_TEXT'],
			'max_input_time' => ['prefer' => '600', 'help' => 'LBL_MAX_INPUT_TIME_HELP_TEXT'],
			'default_socket_timeout' => ['prefer' => '600', 'help' => 'LBL_DEFAULT_SOCKET_TIMEOUT_HELP_TEXT'],
			'memory_limit' => ['prefer' => '512 MB', 'help' => 'LBL_MEMORY_LIMIT_HELP_TEXT'],
			'log_errors' => ['prefer' => 'On', 'help' => 'LBL_LOG_ERRORS_HELP_TEXT'],
			'file_uploads' => ['prefer' => 'On', 'help' => 'LBL_FILE_UPLOADS_HELP_TEXT'],
			'short_open_tag' => ['prefer' => 'On', 'help' => 'LBL_SHORT_OPEN_TAG_HELP_TEXT'],
			'post_max_size' => ['prefer' => '50 MB', 'help' => 'LBL_POST_MAX_SIZE_HELP_TEXT'],
			'upload_max_filesize' => ['prefer' => '100 MB', 'help' => 'LBL_UPLOAD_MAX_FILESIZE_HELP_TEXT'],
			'max_input_vars' => ['prefer' => '10000', 'help' => 'LBL_MAX_INPUT_VARS_HELP_TEXT'],
			'zlib.output_compression' => ['prefer' => 'Off', 'help' => 'LBL_ZLIB_OUTPUT_COMPRESSION_HELP_TEXT'],
			'session.auto_start' => ['prefer' => 'Off', 'help' => 'LBL_SESSION_AUTO_START_HELP_TEXT'],
			'session.gc_maxlifetime' => ['prefer' => '21600', 'help' => 'LBL_SESSION_GC_MAXLIFETIME_HELP_TEXT'],
			'session.gc_divisor' => ['prefer' => '500', 'help' => 'LBL_SESSION_GC_DIVISOR_HELP_TEXT'],
			'session.gc_probability' => ['prefer' => '1', 'help' => 'LBL_SESSION_GC_PROBABILITY_HELP_TEXT'],
			'mbstring.func_overload' => ['prefer' => 'Off', 'help' => 'LBL_MBSTRING_FUNC_OVERLOAD_HELP_TEXT'], //Roundcube
			'date.timezone' => ['prefer' => false], //Roundcube
			'allow_url_fopen' => ['prefer' => 'On', 'help' => 'LBL_ALLOW_URL_FOPEN_HELP_TEXT'], //Roundcube
		];
		if (extension_loaded('suhosin')) {
			$directiveValues['suhosin.session.encrypt'] = ['prefer' => 'Off']; //Roundcube
			$directiveValues['suhosin.request.max_vars'] = ['prefer' => '5000'];
			$directiveValues['suhosin.post.max_vars'] = ['prefer' => '5000'];
			$directiveValues['suhosin.post.max_value_length'] = ['prefer' => '1500000'];
		}
		if (ini_get('file_uploads') != '1' && stripos(ini_get('file_uploads'), 'Off') !== false)
			$directiveValues['file_uploads']['status'] = true;
		$directiveValues['file_uploads']['current'] = static::getFlag(ini_get('file_uploads'));
		if (ini_get('output_buffering') !== 'On') {
			$directiveValues['output_buffering']['status'] = true;
		}
		$directiveValues['output_buffering']['current'] = ini_get('output_buffering');

		if (ini_get('max_execution_time') != 0 && ini_get('max_execution_time') < 600)
			$directiveValues['max_execution_time']['status'] = true;
		$directiveValues['max_execution_time']['current'] = ini_get('max_execution_time');

		if (ini_get('max_input_time') != 0 && ini_get('max_input_time') < 600)
			$directiveValues['max_input_time']['status'] = true;
		$directiveValues['max_input_time']['current'] = ini_get('max_input_time');

		if (ini_get('default_socket_timeout') != 0 && ini_get('default_socket_timeout') < 600)
			$directiveValues['default_socket_timeout']['status'] = true;
		$directiveValues['default_socket_timeout']['current'] = ini_get('default_socket_timeout');

		if (vtlib\Functions::parseBytes(ini_get('memory_limit')) < 536870912)
			$directiveValues['memory_limit']['status'] = true;
		$directiveValues['memory_limit']['current'] = vtlib\Functions::showBytes(ini_get('memory_limit'));

		if (vtlib\Functions::parseBytes(ini_get('post_max_size')) < 52428800) {
			$directiveValues['post_max_size']['status'] = true;
		}
		$directiveValues['post_max_size']['current'] = vtlib\Functions::showBytes(ini_get('post_max_size'));

		if (vtlib\Functions::parseBytes(ini_get('upload_max_filesize')) < 104857600) {
			$directiveValues['upload_max_filesize']['status'] = true;
		}
		$directiveValues['upload_max_filesize']['current'] = vtlib\Functions::showBytes(ini_get('upload_max_filesize'));

		if (ini_get('zlib.output_compression') == '1' || stripos(ini_get('zlib.output_compression'), 'On') !== false) {
			$directiveValues['zlib.output_compression']['status'] = true;
		}
		$directiveValues['zlib.output_compression']['current'] = static::getFlag((ini_get('zlib.output_compression')));

		if (ini_get('session.auto_start') == '1' || stripos(ini_get('session.auto_start'), 'On') !== false) {
			$directiveValues['session.auto_start']['status'] = true;
		}
		$directiveValues['session.auto_start']['current'] = static::getFlag(ini_get('session.auto_start'));
		if (ini_get('mbstring.func_overload') == '1' || stripos(ini_get('mbstring.func_overload'), 'On') !== false) {//Roundcube
			$directiveValues['mbstring.func_overload']['status'] = true;
		}
		$directiveValues['mbstring.func_overload']['current'] = static::getFlag(ini_get('mbstring.func_overload'));

		$errorReporting = stripos(ini_get('error_reporting'), '_') === false ? \App\ErrorHandler::error2string(ini_get('error_reporting')) : ini_get('error_reporting');
		if (in_array('E_NOTICE', $errorReporting)) {
			$directiveValues['error_reporting']['status'] = true;
		}
		$directiveValues['error_reporting']['current'] = implode(' | ', $errorReporting);
		if (ini_get('log_errors') != '1' && stripos(ini_get('log_errors'), 'Off') !== false) {
			$directiveValues['log_errors']['status'] = true;
		}
		$directiveValues['log_errors']['current'] = static::getFlag(ini_get('log_errors'));

		if (ini_get('short_open_tag') != '1' && stripos(ini_get('short_open_tag'), 'Off') !== false) {
			$directiveValues['short_open_tag']['status'] = true;
		}
		$directiveValues['short_open_tag']['current'] = static::getFlag(ini_get('short_open_tag'));

		if (ini_get('session.gc_maxlifetime') < 21600)
			$directiveValues['session.gc_maxlifetime']['status'] = true;
		$directiveValues['session.gc_maxlifetime']['current'] = ini_get('session.gc_maxlifetime');

		if (ini_get('session.gc_divisor') < 500)
			$directiveValues['session.gc_divisor']['status'] = true;
		$directiveValues['session.gc_divisor']['current'] = ini_get('session.gc_divisor');

		if (ini_get('session.gc_probability') < 1)
			$directiveValues['session.gc_probability']['status'] = true;
		$directiveValues['session.gc_probability']['current'] = ini_get('session.gc_probability');

		if (ini_get('max_input_vars') < 10000) {
			$directiveValues['max_input_vars']['status'] = true;
		}
		$directiveValues['max_input_vars']['current'] = ini_get('max_input_vars');

		if (version_compare(PHP_VERSION, '7.0.0', '<')) {
			$directiveValues['PHP']['status'] = true;
		}
		$directiveValues['PHP']['current'] = PHP_VERSION;

		$directiveValues['date.timezone']['current'] = ini_get('date.timezone'); //Roundcube
		try {
			new DateTimeZone(ini_get('date.timezone'));
		} catch (Exception $e) {
			$directiveValues['date.timezone']['current'] = \App\Language::translate('LBL_INVALID_TIME_ZONE', 'Settings::ConfReport') . ini_get('date.timezone');
			$directiveValues['date.timezone']['status'] = true;
		}
		if (ini_get('allow_url_fopen') != '1' && stripos(ini_get('allow_url_fopen'), 'Off') !== false) {
			$directiveValues['allow_url_fopen']['status'] = true;
		}
		$directiveValues['allow_url_fopen']['current'] = static::getFlag(ini_get('allow_url_fopen'));
		if (extension_loaded('suhosin')) {
			if (ini_get('suhosin.session.encrypt') == '1' || stripos(ini_get('suhosin.session.encrypt'), 'On') !== false)//Roundcube
				$directiveValues['suhosin.session.encrypt']['status'] = true;
			$directiveValues['suhosin.session.encrypt']['current'] = static::getFlag(ini_get('suhosin.session.encrypt'));

			if (ini_get('suhosin.request.max_vars') < 5000) {
				$directiveValues['suhosin.request.max_vars']['status'] = true;
			}
			$directiveValues['suhosin.request.max_vars']['current'] = ini_get('suhosin.request.max_vars');

			if (ini_get('suhosin.post.max_vars') < 5000) {
				$directiveValues['suhosin.post.max_vars']['status'] = true;
			}
			$directiveValues['suhosin.post.max_vars']['current'] = ini_get('suhosin.post.max_vars');

			if (ini_get('suhosin.post.max_value_length') < 1500000) {
				$directiveValues['suhosin.post.max_value_length']['status'] = true;
			}
			$directiveValues['suhosin.post.max_value_length']['current'] = ini_get('suhosin.post.max_value_length');
		}
		if ($onlyError) {
			foreach ($directiveValues as $key => $value) {
				if (empty($value['status'])) {
					unset($directiveValues[$key]);
				}
			}
		}
		return $directiveValues;
	}

	/**
	 * Get system security configuration
	 * @param bool $instalMode
	 * @return array
	 */
	public static function getSecurityConf($instalMode = false, $onlyError = false)
	{
		$directiveValues = [
			'display_errors' => [
				'prefer' => 'Off',
				'help' => 'LBL_DISPLAY_ERRORS_HELP_TEXT',
				'current' => static::getFlag(ini_get('display_errors')),
				'status' => \AppConfig::main('systemMode') !== 'demo' && (ini_get('display_errors') == 1 || stripos(ini_get('display_errors'), 'On') !== false)
			],
			'HTTPS' => ['prefer' => 'On', 'help' => 'LBL_HTTPS_HELP_TEXT'],
			'.htaccess' => ['prefer' => 'On', 'help' => 'LBL_HTACCESS_HELP_TEXT'],
			'public_html' => ['prefer' => 'On', 'help' => 'LBL_PUBLIC_HTML_HELP_TEXT'],
			'session.use_strict_mode' => [
				'prefer' => 'On',
				'help' => 'LBL_SESSION_USE_STRICT_MODE_HELP_TEXT',
				'current' => static::getFlag(ini_get('session.use_strict_mode')),
				'status' => ini_get('session.use_strict_mode') != 1 && stripos(ini_get('session.use_strict_mode'), 'Off') !== false
			],
			'session.use_trans_sid' => [
				'prefer' => 'Off',
				'help' => 'LBL_SESSION_USE_TRANS_SID_HELP_TEXT',
				'current' => static::getFlag(ini_get('session.use_trans_sid')),
				'status' => ini_get('session.use_trans_sid') == 1 || stripos(ini_get('session.use_trans_sid'), 'On') !== false
			],
			'session.cookie_httponly' => [
				'prefer' => 'On',
				'help' => 'LBL_SESSION_COOKIE_HTTPONLY_HELP_TEXT',
				'current' => static::getFlag(ini_get('session.cookie_httponly')),
				'status' => ini_get('session.cookie_httponly') != 1 && stripos(ini_get('session.cookie_httponly'), 'Off') !== false
			],
			'session.use_only_cookies' => [
				'prefer' => 'On',
				'help' => 'LBL_SESSION_USE_ONLY_COOKIES_HELP_TEXT',
				'current' => static::getFlag(ini_get('session.use_only_cookies')),
				'status' => ini_get('session.use_only_cookies') != 1 && stripos(ini_get('session.use_only_cookies'), 'Off') !== false
			],
			'expose_php' => [
				'prefer' => 'Off',
				'help' => 'LBL_EXPOSE_PHP_HELP_TEXT',
				'current' => static::getFlag(ini_get('expose_php')),
				'status' => ini_get('expose_php') == 1 || stripos(ini_get('expose_php'), 'On') !== false
			],
			'session_regenerate_id' => [
				'prefer' => 'On',
				'help' => 'LBL_SESSION_REGENERATE_HELP_TEXT',
				'current' => static::getFlag(AppConfig::main('session_regenerate_id')),
				'status' => AppConfig::main('session_regenerate_id') !== null && !AppConfig::main('session_regenerate_id')
			],
			'Header: X-Frame-Options' => ['prefer' => 'SAMEORIGIN', 'help' => 'LBL_HEADER_X_FRAME_OPTIONS_HELP_TEXT', 'current' => '?'],
			'Header: X-XSS-Protection' => ['prefer' => '1; mode=block', 'help' => 'LBL_HEADER_X_XSS_PROTECTION_HELP_TEXT', 'current' => '?'],
			'Header: X-Content-Type-Options' => ['prefer' => 'nosniff', 'help' => 'LBL_HEADER_X_CONTENT_TYPE_OPTIONS_HELP_TEXT', 'current' => '?'],
			'Header: X-Robots-Tag' => ['prefer' => 'none', 'help' => 'LBL_HEADER_X_ROBOTS_TAG_HELP_TEXT', 'current' => '?'],
			'Header: X-Permitted-Cross-Domain-Policies' => ['prefer' => 'none', 'help' => 'LBL_HEADER_X_PERMITTED_CROSS_DOMAIN_POLICIES_HELP_TEXT', 'current' => '?'],
			'Header: X-Powered-By' => ['prefer' => '', 'help' => 'LBL_HEADER_X_POWERED_BY_HELP_TEXT', 'current' => '?'],
			'Header: Server' => ['prefer' => '', 'help' => 'LBL_HEADER_SERVER_HELP_TEXT', 'current' => '?'],
			'Header: Expect-CT' => ['prefer' => 'enforce; max-age=3600', 'help' => 'LBL_HEADER_EXPECT_CT_HELP_TEXT', 'current' => '?'],
			'Header: Referrer-Policy' => ['prefer' => 'same-origin', 'help' => 'LBL_HEADER_REFERRER_POLICY_HELP_TEXT', 'current' => '?'],
			'Header: Strict-Transport-Security' => ['prefer' => 'max-age=31536000; includeSubDomains; preload', 'help' => 'LBL_HEADER_STRICT_TRANSPORT_SECURITY_HELP_TEXT', 'current' => '?'],
		];
		if (IS_PUBLIC_DIR === true) {
			$directiveValues['public_html']['current'] = static::getFlag(true);
		} else {
			$directiveValues['public_html']['status'] = true;
			$directiveValues['public_html']['current'] = static::getFlag(false);
		}
		if (!isset($_SERVER['HTACCESS_TEST'])) {
			$directiveValues['.htaccess']['status'] = true;
			$directiveValues['.htaccess']['current'] = 'Off';
		} else {
			$directiveValues['.htaccess']['current'] = 'On';
		}
		if (App\RequestUtil::getBrowserInfo()->https) {
			$directiveValues['HTTPS']['status'] = false;
			$directiveValues['HTTPS']['current'] = static::getFlag(true);
			$directiveValues['session.cookie_secure'] = ['prefer' => 'On'];
			if (ini_get('session.cookie_secure') != '1' && stripos(ini_get('session.cookie_secure'), 'On') !== false) {
				$directiveValues['session.cookie_secure']['status'] = true;
				$directiveValues['session.cookie_secure']['current'] = static::getFlag(false);
			} else {
				$directiveValues['session.cookie_secure']['current'] = static::getFlag(true);
			}
		} else {
			$directiveValues['HTTPS']['status'] = true;
			$directiveValues['HTTPS']['current'] = static::getFlag(false);
		}
		stream_context_set_default([
			'ssl' => [
				'verify_peer' => false,
				'verify_peer_name' => false,
			],
		]);
		$requestUrl = (\App\RequestUtil::getBrowserInfo()->https ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['SCRIPT_NAME'];
		$rqheaders = get_headers($requestUrl, 1);
		if ($rqheaders) {
			$headers = array_change_key_case($rqheaders, CASE_UPPER);
			if (stripos($headers[0], '200') === false) {
				$headers = [];
			}
		}
		if ($headers) {
			$directiveValues['Header: X-Frame-Options']['status'] = strtolower($headers['X-FRAME-OPTIONS']) !== 'sameorigin';
			$directiveValues['Header: X-Frame-Options']['current'] = $headers['X-FRAME-OPTIONS'];
			$directiveValues['Header: X-XSS-Protection']['status'] = strtolower($headers['X-XSS-PROTECTION']) !== '1; mode=block';
			$directiveValues['Header: X-XSS-Protection']['current'] = $headers['X-XSS-PROTECTION'];
			$directiveValues['Header: X-Content-Type-Options']['status'] = strtolower($headers['X-CONTENT-TYPE-OPTIONS']) !== 'nosniff';
			$directiveValues['Header: X-Content-Type-Options']['current'] = $headers['X-CONTENT-TYPE-OPTIONS'];
			$directiveValues['Header: X-Powered-By']['status'] = !empty($headers['X-POWERED-BY']);
			$directiveValues['Header: X-Powered-By']['current'] = $headers['X-POWERED-BY'];
			$directiveValues['Header: X-Robots-Tag']['status'] = strtolower($headers['X-ROBOTS-TAG']) !== 'none';
			$directiveValues['Header: X-Robots-Tag']['current'] = $headers['X-ROBOTS-TAG'];
			$directiveValues['Header: X-Permitted-Cross-Domain-Policies']['status'] = strtolower($headers['X-PERMITTED-CROSS-DOMAIN-POLICIES']) !== 'none';
			$directiveValues['Header: X-Permitted-Cross-Domain-Policies']['current'] = $headers['X-PERMITTED-CROSS-DOMAIN-POLICIES'];
			$directiveValues['Header: X-Powered-By']['status'] = !empty($headers['X-POWERED-BY']);
			$directiveValues['Header: X-Powered-By']['current'] = $headers['X-POWERED-BY'];
			$directiveValues['Header: Server']['status'] = !empty($headers['SERVER']);
			$directiveValues['Header: Server']['current'] = $headers['SERVER'];
			$directiveValues['Header: Referrer-Policy']['status'] = strtolower($headers['REFERRER-POLICY']) !== 'no-referrer';
			$directiveValues['Header: Referrer-Policy']['current'] = $headers['REFERRER-POLICY'];
			$directiveValues['Header: Expect-CT']['status'] = strtolower($headers['EXPECT-CT']) !== 'enforce; max-age=3600';
			$directiveValues['Header: Expect-CT']['current'] = $headers['EXPECT-CT'];
			$directiveValues['Header: Strict-Transport-Security']['status'] = strtolower($headers['STRICT-TRANSPORT-SECURITY']) !== 'max-age=31536000; includesubdomains; preload';
			$directiveValues['Header: Strict-Transport-Security']['current'] = $headers['STRICT-TRANSPORT-SECURITY'];
		}
		if ($onlyError) {
			foreach ($directiveValues as $key => $value) {
				if (empty($value['status'])) {
					unset($directiveValues[$key]);
				}
			}
		}
		return $directiveValues;
	}

	/**
	 *
	 * @param type $onlyError
	 * @return boolean
	 */
	public static function getDbConf($onlyError = false)
	{
		$db = \App\Db::getInstance();
		$directiveValues = [
			'LBL_DB_DRIVER' => ['prefer' => 'mysql', 'current' => $db->getDriverName(), 'help' => 'LBL_DB_DRIVER_HELP_TEXT'],
			'LBL_DB_SERVER_VERSION' => ['prefer' => false, 'current' => $db->getSlavePdo()->getAttribute(PDO::ATTR_SERVER_VERSION)],
			'LBL_DB_CLIENT_VERSION' => ['prefer' => false, 'current' => $db->getSlavePdo()->getAttribute(PDO::ATTR_CLIENT_VERSION)],
			'LBL_DB_CONNECTION_STATUS' => ['prefer' => false, 'current' => $db->getSlavePdo()->getAttribute(PDO::ATTR_CONNECTION_STATUS)],
			'LBL_DB_SERVER_INFO' => ['prefer' => false, 'current' => $db->getSlavePdo()->getAttribute(PDO::ATTR_SERVER_INFO)],
		];
		if (!in_array($db->getDriverName(), explode(',', $directiveValues['LBL_DB_DRIVER']['prefer']))) {
			$directiveValues['wait_timeout']['status'] = true;
		}
		if ($db->getDriverName() === 'mysql') {
			$directiveValues = array_merge($directiveValues, [
				'innodb_lock_wait_timeout' => ['prefer' => '600', 'help' => 'LBL_INNODB_LOCK_WAIT_TIMEOUT_HELP_TEXT'],
				'wait_timeout' => ['prefer' => '600', 'help' => 'LBL_WAIT_TIMEOUT_HELP_TEXT'],
				'interactive_timeout' => ['prefer' => '600', 'help' => 'LBL_INTERACTIVE_TIMEOUT_HELP_TEXT'],
				'sql_mode' => ['prefer' => '', 'help' => 'LBL_SQL_MODE_HELP_TEXT'],
				'max_allowed_packet' => ['prefer' => '10 MB', 'help' => 'LBL_MAX_ALLOWED_PACKET_HELP_TEXT'],
				'log_error' => ['prefer' => false],
				'max_connections' => ['prefer' => false],
				'thread_cache_size' => ['prefer' => false],
				'key_buffer_size' => ['prefer' => false],
				'query_cache_size' => ['prefer' => false],
				'tmp_table_size' => ['prefer' => false],
				'max_heap_table_size' => ['prefer' => false],
				'innodb_file_per_table' => ['prefer' => 'On', 'help' => 'LBL_INNODB_FILE_PER_TABLE_HELP_TEXT'],
				'innodb_stats_on_metadata' => ['prefer' => 'Off', 'help' => 'LBL_INNODB_STATS_ON_METADATA_HELP_TEXT'],
				'innodb_buffer_pool_instances' => ['prefer' => false],
				'innodb_buffer_pool_size' => ['prefer' => false],
				'innodb_log_file_size' => ['prefer' => false],
				'innodb_io_capacity_max' => ['prefer' => false],
			]);
			$conf = $db->createCommand('SHOW VARIABLES')->queryAllByGroup(0);
			$directiveValues['max_allowed_packet']['current'] = vtlib\Functions::showBytes($conf['max_allowed_packet']);
			$directiveValues['innodb_log_file_size']['current'] = vtlib\Functions::showBytes($conf['innodb_log_file_size']);
			$directiveValues['key_buffer_size']['current'] = vtlib\Functions::showBytes($conf['key_buffer_size']);
			$directiveValues['query_cache_size']['current'] = vtlib\Functions::showBytes($conf['query_cache_size']);
			$directiveValues['tmp_table_size']['current'] = vtlib\Functions::showBytes($conf['tmp_table_size']);
			$directiveValues['max_heap_table_size']['current'] = vtlib\Functions::showBytes($conf['max_heap_table_size']);
			$directiveValues['innodb_buffer_pool_size']['current'] = vtlib\Functions::showBytes($conf['innodb_buffer_pool_size']);
			$directiveValues['innodb_log_file_size']['current'] = vtlib\Functions::showBytes($conf['innodb_log_file_size']);
			$directiveValues['innodb_lock_wait_timeout']['current'] = $conf['innodb_lock_wait_timeout'];
			$directiveValues['wait_timeout']['current'] = $conf['wait_timeout'];
			$directiveValues['interactive_timeout']['current'] = $conf['interactive_timeout'];
			$directiveValues['sql_mode']['current'] = $conf['sql_mode'];
			$directiveValues['log_error']['current'] = $conf['log_error'];
			$directiveValues['max_connections']['current'] = $conf['max_connections'];
			$directiveValues['thread_cache_size']['current'] = $conf['thread_cache_size'];
			$directiveValues['innodb_buffer_pool_instances']['current'] = $conf['innodb_buffer_pool_instances'];
			$directiveValues['innodb_io_capacity_max']['current'] = $conf['innodb_io_capacity_max'];
			$directiveValues['innodb_file_per_table']['current'] = $conf['innodb_file_per_table'];
			$directiveValues['innodb_stats_on_metadata']['current'] = $conf['innodb_stats_on_metadata'];
			if ($conf['max_allowed_packet'] < 16777216) {
				$directiveValues['max_allowed_packet']['status'] = true;
			}
			if ($conf['innodb_lock_wait_timeout'] < 600) {
				$directiveValues['innodb_lock_wait_timeout']['status'] = true;
			}
			if ($conf['wait_timeout'] < 600) {
				$directiveValues['wait_timeout']['status'] = true;
			}
			if ($conf['interactive_timeout'] < 600) {
				$directiveValues['interactive_timeout']['status'] = true;
			}
			if (!empty($conf['sql_mode']) && (strpos($conf['sql_mode'], 'STRICT_TRANS_TABLE') !== false || strpos($conf['sql_mode'], 'ONLY_FULL_GROUP_BY') !== false)) {
				$directiveValues['sql_mode']['status'] = true;
			}
		}
		if ($onlyError) {
			foreach ($directiveValues as $key => $value) {
				if (empty($value['status'])) {
					unset($directiveValues[$key]);
				}
			}
		}
		return $directiveValues;
	}

	/**
	 * Get system details
	 * @return array
	 */
	public static function getSystemInfo()
	{
		$params = [
			'LBL_OPERATING_SYSTEM' => \AppConfig::main('systemMode') === 'demo' ? php_uname('s') : php_uname(),
			'LBL_PHP_SAPI' => PHP_SAPI,
			'LBL_TMP_DIR' => App\Fields\File::getTmpPath(),
			'LBL_CRM_DIR' => ROOT_DIRECTORY,
			'LBL_LOG_FILE' => ini_get('error_log'),
			'LBL_PHPINI' => php_ini_loaded_file(),
		];
		if (file_exists('user_privileges/cron.php')) {
			include 'user_privileges/cron.php';

			$params['LBL_CRON_PHPINI'] = $ini;
			$params['LBL_CRON_LOG_FILE'] = $log;
			$params['LBL_CRON_PHP'] = $vphp;
			$params['LBL_CRON_PHP_SAPI'] = $sapi;
		}
		return $params;
	}

	/**
	 * Function returns permissions to the core files and folder
	 * @return <Array>
	 */
	public static function getPermissionsFiles($onlyError = false)
	{
		$writableFilesAndFolders = static::$writableFilesAndFolders;
		$permissions = [];
		require_once ROOT_DIRECTORY . '/include/utils/VtlibUtils.php';
		foreach ($writableFilesAndFolders as $index => $value) {
			$isWriteable = \App\Fields\File::isWriteable($value);
			if (!$isWriteable || !$onlyError) {
				$permissions[$index]['permission'] = 'TruePermission';
				$permissions[$index]['path'] = $value;
			}
			if (!$isWriteable) {
				$permissions[$index]['permission'] = 'FailedPermission';
			}
		}
		return $permissions;
	}

	public static function getFlag($val)
	{
		if ($val == 'On' || $val == 1 || stripos($val, 'On') !== false) {
			return 'On';
		}
		return 'Off';
	}

	/**
	 * Test server speed
	 * @return array
	 */
	public static function testSpeed()
	{
		$dir = ROOT_DIRECTORY . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . 'speed' . DIRECTORY_SEPARATOR;
		if (!is_dir($dir)) {
			mkdir($dir, 0777);
		}
		$i = 5000;
		$p = time();
		$writeS = microtime(true);
		for ($index = 0; $index < $i; $index++) {
			file_put_contents("{$dir}{$p}{$index}.php", '<?php return [];');
		}
		$writeE = microtime(true);
		$iterator = new \DirectoryIterator($dir);
		$readS = microtime(true);
		foreach ($iterator as $item) {
			if (!$item->isDot() && !$item->isDir()) {
				include $item->getPathname();
			}
		}
		$readE = microtime(true);
		$read = $i / ($readE - $readS);
		$write = $i / ($writeE - $writeS);
		\vtlib\Functions::recurseDelete('cache/speed');
		return ['FilesRead' => number_format($read, 0, '', ' '), 'FilesWrite' => number_format($write, 0, '', ' ')];
	}
}
