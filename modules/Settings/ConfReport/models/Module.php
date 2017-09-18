<?php

/**
 * Settings ConfReport module model class
 * @package YetiForce.Model
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 */
class Settings_ConfReport_Module_Model extends Settings_Vtiger_Module_Model
{

	/**
	 * variable has all the files and folder that should be writable
	 * @var <Array>
	 */
	public static $writableFilesAndFolders = array(
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
	);

	/**
	 * List of libraries
	 * @var array
	 */
	public static $library = array(
		'LBL_IMAP_SUPPORT' => ['type' => 'f', 'name' => 'imap_open', 'mandatory' => true],
		'LBL_ZLIB_SUPPORT' => ['type' => 'f', 'name' => 'gzinflate', 'mandatory' => true],
		'LBL_PDO_SUPPORT' => ['type' => 'e', 'name' => 'pdo_mysql', 'mandatory' => true],
		'LBL_OPEN_SSL' => ['type' => 'e', 'name' => 'openssl', 'mandatory' => true],
		'LBL_CURL' => ['type' => 'e', 'name' => 'curl', 'mandatory' => true],
		'LBL_GD_LIBRARY' => ['type' => 'e', 'name' => 'gd', 'mandatory' => true],
		'LBL_PCRE_LIBRARY' => ['type' => 'e', 'name' => 'pcre', 'mandatory' => true],
		'LBL_XML_LIBRARY' => ['type' => 'e', 'name' => 'xml', 'mandatory' => true],
		'LBL_JSON_LIBRARY' => ['type' => 'e', 'name' => 'json', 'mandatory' => true],
		'LBL_SESSION_LIBRARY' => ['type' => 'e', 'name' => 'session', 'mandatory' => true],
		'LBL_DOM_LIBRARY' => ['type' => 'e', 'name' => 'dom', 'mandatory' => true],
		'LBL_ZIP_ARCHIVE' => ['type' => 'e', 'name' => 'zip', 'mandatory' => true],
		'LBL_MBSTRING_LIBRARY' => ['type' => 'e', 'name' => 'mbstring', 'mandatory' => true],
		'LBL_SOAP_LIBRARY' => ['type' => 'e', 'name' => 'soap', 'mandatory' => true],
		'LBL_MYSQLND_LIBRARY' => ['type' => 'e', 'name' => 'mysqlnd', 'mandatory' => true],
		'LBL_EXIF_LIBRARY' => ['type' => 'f', 'name' => 'exif_read_data', 'mandatory' => false],
		'LBL_LDAP_LIBRARY' => ['type' => 'f', 'name' => 'ldap_connect', 'mandatory' => false],
		'LBL_OPCACHE_LIBRARY' => ['type' => 'f', 'name' => 'opcache_get_configuration', 'mandatory' => false],
		'LBL_APCU_LIBRARY' => ['type' => 'e', 'name' => 'apcu', 'mandatory' => false],
	);

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
			'PHP' => ['prefer' => '7.0.0'],
			'error_reporting' => ['prefer' => 'E_ALL & ~E_NOTICE'],
			'output_buffering' => ['prefer' => 'On'],
			'max_execution_time' => ['prefer' => '600'],
			'max_input_time' => ['prefer' => '600'],
			'default_socket_timeout' => ['prefer' => '600'],
			'memory_limit' => ['prefer' => '512 MB'],
			'log_errors' => ['prefer' => 'On'],
			'file_uploads' => ['prefer' => 'On'],
			'short_open_tag' => ['prefer' => 'On'],
			'post_max_size' => ['prefer' => '50 MB'],
			'upload_max_filesize' => ['prefer' => '100 MB'],
			'max_input_vars' => ['prefer' => '10000'],
			'zlib.output_compression' => ['prefer' => 'Off'],
			'session.auto_start' => ['prefer' => 'Off'],
			'session.gc_maxlifetime' => ['prefer' => '21600'],
			'session.gc_divisor' => ['prefer' => '500'],
			'session.gc_probability' => ['prefer' => '1'],
			'mbstring.func_overload' => ['prefer' => 'Off'],
		];
		if (!$instalMode && App\Db::getInstance()->getDriverName() === 'mysql') {
			$directiveValues['mysql.connect_timeout'] = ['prefer' => '600'];
			$directiveValues['innodb_lock_wait_timeout'] = ['prefer' => '600']; // MySQL
			$directiveValues['wait_timeout'] = ['prefer' => '600']; // MySQL
			$directiveValues['interactive_timeout'] = ['prefer' => '600']; // MySQL
			$directiveValues['sql_mode'] = ['prefer' => '']; // MySQL
			$directiveValues['max_allowed_packet'] = ['prefer' => '10 MB']; // MySQL
		}
		if (extension_loaded('suhosin')) {
			$directiveValues['suhosin.session.encrypt'] = array('prefer' => 'Off');
			$directiveValues['suhosin.request.max_vars'] = array('prefer' => '5000');
			$directiveValues['suhosin.post.max_vars'] = array('prefer' => '5000');
			$directiveValues['suhosin.post.max_value_length'] = array('prefer' => '1500000');
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
		if (ini_get('mbstring.func_overload') == '1' || stripos(ini_get('mbstring.func_overload'), 'On') !== false) {
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
		if (extension_loaded('suhosin')) {
			if (ini_get('suhosin.session.encrypt') == '1' || stripos(ini_get('suhosin.session.encrypt'), 'On') !== false)
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
		if (!$instalMode && App\Db::getInstance()->getDriverName() === 'mysql') {
			if (ini_get('mysql.connect_timeout') != 0 && ini_get('mysql.connect_timeout') < 600)
				$directiveValues['mysql.connect_timeout']['status'] = true;
			$directiveValues['mysql.connect_timeout']['current'] = ini_get('mysql.connect_timeout');

			$db = PearDatabase::getInstance();
			$result = $db->query('SELECT @@max_allowed_packet');
			$maxAllowedPacket = $db->getSingleValue($result);
			if ($maxAllowedPacket < 16777216) {
				$directiveValues['max_allowed_packet']['status'] = true;
			}
			$directiveValues['max_allowed_packet']['current'] = vtlib\Functions::showBytes($maxAllowedPacket);

			$result = $db->query('SELECT @@innodb_lock_wait_timeout');
			$innodbLockWaitTimeout = $db->getSingleValue($result);
			if ($innodbLockWaitTimeout < 600) {
				$directiveValues['innodb_lock_wait_timeout']['status'] = true;
			}
			$directiveValues['innodb_lock_wait_timeout']['current'] = $innodbLockWaitTimeout;

			$result = $db->query('SELECT @@wait_timeout');
			$waitTimeout = $db->getSingleValue($result);
			if ($waitTimeout < 600) {
				$directiveValues['wait_timeout']['status'] = true;
			}
			$directiveValues['wait_timeout']['current'] = $waitTimeout;

			$result = $db->query('SELECT @@interactive_timeout');
			$interactiveTimeout = $db->getSingleValue($result);
			if ($interactiveTimeout < 600) {
				$directiveValues['interactive_timeout']['status'] = true;
			}
			$directiveValues['interactive_timeout']['current'] = $interactiveTimeout;

			$result = $db->query('SELECT @@sql_mode');
			$directiveValues['sql_mode']['current'] = $db->getSingleValue($result);
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
				'current' => static::getFlag(ini_get('display_errors')),
				'status' => \AppConfig::main('systemMode') !== 'demo' && (ini_get('display_errors') == 1 || stripos(ini_get('display_errors'), 'On') !== false)
			],
			'HTTPS' => ['prefer' => 'On', 'help' => 'HTTPS_HELP_TEXT'],
			'.htaccess' => ['prefer' => 'On', 'help' => 'HTACCESS_HELP_TEXT'],
			'public_html' => ['prefer' => 'On', 'help' => 'PUBLIC_HTML_HELP_TEXT'],
			'session.use_strict_mode' => [
				'prefer' => 'On',
				'current' => static::getFlag(ini_get('session.use_strict_mode')),
				'status' => ini_get('session.use_strict_mode') != 1 && stripos(ini_get('session.use_strict_mode'), 'Off') !== false
			],
			'session.cookie_httponly' => [
				'prefer' => 'On',
				'current' => static::getFlag(ini_get('session.cookie_httponly')),
				'status' => ini_get('session.cookie_httponly') != 1 && stripos(ini_get('session.cookie_httponly'), 'Off') !== false
			],
			'session.use_only_cookies' => [
				'prefer' => 'On',
				'current' => static::getFlag(ini_get('session.use_only_cookies')),
				'status' => ini_get('session.use_only_cookies') != 1 && stripos(ini_get('session.use_only_cookies'), 'Off') !== false
			],
			'expose_php' => [
				'prefer' => 'Off',
				'current' => static::getFlag(ini_get('expose_php')),
				'status' => ini_get('expose_php') == 1 || stripos(ini_get('expose_php'), 'On') !== false
			],
			'session_regenerate_id' => [
				'prefer' => 'On',
				'help' => 'SESSION_REGENERATE_HELP_TEXT',
				'current' => static::getFlag(AppConfig::main('session_regenerate_id')),
				'status' => AppConfig::main('session_regenerate_id') !== null && !AppConfig::main('session_regenerate_id')
			],
			'Header: X-Frame-Options' => ['prefer' => 'SAMEORIGIN', 'current' => '?'],
			'Header: X-XSS-Protection' => ['prefer' => '1; mode=block', 'current' => '?'],
			'Header: X-Content-Type-Options' => ['prefer' => 'nosniff', 'current' => '?'],
			'Header: X-Robots-Tag' => ['prefer' => 'none', 'current' => '?'],
			'Header: X-Permitted-Cross-Domain-Policies' => ['prefer' => 'none', 'current' => '?'],
			'Header: X-Powered-By' => ['prefer' => '', 'current' => '?'],
			'Header: Server' => ['prefer' => '', 'current' => '?'],
			'Header: Referrer-Policy' => ['prefer' => 'no-referrer', 'current' => '?'],
			'Header: Expect-CT' => ['prefer' => 'enforce; max-age=3600', 'current' => '?'],
			'Header: Referrer-Policy' => ['prefer' => 'same-origin', 'current' => '?'],
			'Header: Strict-Transport-Security' => ['prefer' => 'max-age=31536000; includeSubDomains; preload', 'current' => '?'],
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
		if (function_exists('apache_response_headers')) {
			$headers = array_change_key_case(apache_response_headers(), CASE_UPPER);
		} else {
			$requestUrl = (\App\RequestUtil::getBrowserInfo()->https ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['SCRIPT_NAME'];
			$headers = array_change_key_case(get_headers($requestUrl, 1), CASE_UPPER);
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
	 * Get system details
	 * @return array
	 */
	public static function getSystemInfo()
	{

		$params = [
			'LBL_OPERATING_SYSTEM' => \AppConfig::main('systemMode') === 'demo' ? php_uname('s') : php_uname(),
			'LBL_PHPINI' => php_ini_loaded_file(),
			'LBL_LOG_FILE' => ini_get('error_log'),
			'LBL_CRM_DIR' => ROOT_DIRECTORY,
			'LBL_PHP_SAPI' => PHP_SAPI
		];
		if (file_exists('user_privileges/cron.php')) {
			include 'user_privileges/cron.php';
			$params['LBL_CRON_PHP'] = $vphp;
			$params['LBL_CRON_PHPINI'] = $ini;
			$params['LBL_CRON_LOG_FILE'] = $log;
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
			$isWriteable = vtlib_isWriteable($value);
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
