<?php
/* +***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 * *********************************************************************************************************************************** */

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
		'Parent tabdata file' => 'config/parent_tabdata.php',
		'Cache directory' => 'cache/',
		'Address book directory' => 'cache/addressBook/',
		'Cache backup directory' => 'cache/backup/',
		'Image cache directory' => 'cache/images/',
		'Import cache directory' => 'cache/import/',
		'Logs directory' => 'cache/logs/',
		'Session directory' => 'cache/session/',
		'Cache templates directory' => 'cache/templates_c/',
		'Cache upload directory' => 'cache/upload/',
		'Cron modules directory' => 'cron/modules/',
		'Vtlib test directory' => 'cache/vtlib/',
		'Vtlib test HTML directory' => 'cache/vtlib/HTML',
		'Modules directory' => 'modules/',
		'Storage directory' => 'storage/',
		'Product image directory' => 'storage/Products/',
		'User image directory' => 'storage/Users/',
		'Contact image directory' => 'storage/Contacts/',
		'Logo directory' => 'storage/Logo/',
		'MailView attachments directory' => 'storage/OSSMailView/',
	);
	public static $library = array(
		'LBL_IMAP_SUPPORT' => ['type' => 'f', 'name' => 'imap_open', 'mandatory' => true],
		'LBL_ZLIB_SUPPORT' => ['type' => 'f', 'name' => 'gzinflate', 'mandatory' => true],
		'LBL_PDO_SUPPORT' => ['type' => 'e', 'name' => 'PDO', 'mandatory' => true],
		'LBL_OPEN_SSL' => ['type' => 'e', 'name' => 'openssl', 'mandatory' => true],
		'LBL_CURL' => ['type' => 'e', 'name' => 'curl', 'mandatory' => true],
		'LBL_GD_LIBRARY' => ['type' => 'e', 'name' => 'gd', 'mandatory' => true],
		'LBL_LDAP_LIBRARY' => ['type' => 'f', 'name' => 'ldap_connect', 'mandatory' => false],
		'LBL_PCRE_LIBRARY' => ['type' => 'e', 'name' => 'pcre', 'mandatory' => true],
		'LBL_XML_LIBRARY' => ['type' => 'e', 'name' => 'xml', 'mandatory' => true],
		'LBL_JSON_LIBRARY' => ['type' => 'e', 'name' => 'json', 'mandatory' => true],
		'LBL_SESSION_LIBRARY' => ['type' => 'e', 'name' => 'session', 'mandatory' => true],
		'LBL_DOM_LIBRARY' => ['type' => 'e', 'name' => 'dom', 'mandatory' => true],
	);

	public static function getConfigurationLibrary()
	{
		foreach (self::$library as $k => $v) {
			if ($v['type'] == 'f') {
				$status = function_exists($v['name']);
			} elseif ($v['type'] == 'e') {
				$status = extension_loaded($v['name']);
			}
			self::$library[$k]['status'] = $status ? 'LBL_YES' : 'LBL_NO';
		}
		return self::$library;
	}

	public static function getConfigurationValue($instalMode = false)
	{
		$errorReportingValue = 'E_WARNING & E_ERROR & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT';
		$directiveValues = array(
			'PHP' => array('prefer' => '5.4.0'),
			'error_reporting' => array('prefer' => $errorReportingValue),
			'output_buffering' => array('prefer' => 'On'),
			'max_execution_time' => array('prefer' => '600'),
			'memory_limit' => array('prefer' => '512 MB'),
			'safe_mode' => array('prefer' => 'Off'),
			'display_errors' => array('prefer' => 'Off'),
			'log_errors' => array('prefer' => 'Off'),
			'file_uploads' => array('prefer' => 'On'),
			'register_globals' => array('prefer' => 'On'),
			'short_open_tag' => array('prefer' => 'On'),
			'post_max_size' => array('prefer' => '50 MB'),
			'upload_max_filesize' => array('prefer' => '50 MB'),
			'max_allowed_packet' => array('prefer' => '10 MB'),
			'max_input_vars' => array('prefer' => '5000'),
			'magic_quotes_gpc' => array('prefer' => 'Off'),
			'magic_quotes_runtime' => array('prefer' => 'Off'),
			'zlib.output_compression' => array('prefer' => 'Off'),
			'zend.ze1_compatibility_mode' => array('prefer' => 'Off'),
			'session.auto_start' => array('prefer' => 'Off'),
			'magic_quotes_sybase' => array('prefer' => 'Off'),
			'session.gc_maxlifetime' => array('prefer' => '21600'),
			'session.gc_divisor' => array('prefer' => '500'),
			'session.gc_probability' => array('prefer' => '1'),
			'mbstring.func_overload' => array('prefer' => 'Off'),
		);
		if (extension_loaded('suhosin')) {
			$directiveValues['suhosin.session.encrypt'] = array('prefer' => 'Off');
			$directiveValues['suhosin.request.max_vars'] = array('prefer' => '5000');
			$directiveValues['suhosin.post.max_vars'] = array('prefer' => '5000');
			$directiveValues['suhosin.post.max_value_length'] = array('prefer' => '1500000');
		}

		if (ini_get('safe_mode') == '1' || stripos(ini_get('safe_mode'), 'On') !== false)
			$directiveValues['safe_mode']['status'] = true;
		$directiveValues['safe_mode']['current'] = self::getFlag(ini_get('safe_mode'));

		if (ini_get('display_errors') == '1' || stripos(ini_get('display_errors'), 'On') !== false)
			$directiveValues['display_errors']['status'] = true;
		$directiveValues['display_errors']['current'] = self::getFlag(ini_get('display_errors'));

		if (ini_get('file_uploads') != '1' || stripos(ini_get('file_uploads'), 'Off') !== false)
			$directiveValues['file_uploads']['status'] = true;
		$directiveValues['file_uploads']['current'] = self::getFlag(ini_get('file_uploads'));

		if (ini_get('register_globals') == '1' || stripos(ini_get('register_globals'), 'On') !== false)
			$directiveValues['register_globals']['status'] = true;
		$directiveValues['register_globals']['current'] = self::getFlag(ini_get('register_globals'));

		if (ini_get(('output_buffering') < '4096' && ini_get('output_buffering') != '0') || stripos(ini_get('output_buffering'), 'Off') !== false)
			$directiveValues['output_buffering']['status'] = true;
		$directiveValues['output_buffering']['current'] = self::getFlag(ini_get('output_buffering'));

		if (ini_get('max_execution_time') < 600)
			$directiveValues['max_execution_time']['status'] = true;
		$directiveValues['max_execution_time']['current'] = ini_get('max_execution_time');

		if (Vtiger_Functions::parseBytes(ini_get('memory_limit')) < 33554432)
			$directiveValues['memory_limit']['status'] = true;
		$directiveValues['memory_limit']['current'] = Vtiger_Functions::showBytes(ini_get('memory_limit'));

		if (Vtiger_Functions::parseBytes(ini_get('post_max_size')) < 10485760)
			$directiveValues['post_max_size']['status'] = true;
		$directiveValues['post_max_size']['current'] = Vtiger_Functions::showBytes(ini_get('post_max_size'));

		if (Vtiger_Functions::parseBytes(ini_get('upload_max_filesize')) < 10485760)
			$directiveValues['upload_max_filesize']['status'] = true;
		$directiveValues['upload_max_filesize']['current'] = Vtiger_Functions::showBytes(ini_get('upload_max_filesize'));

		if (ini_get('magic_quotes_gpc') == '1' || stripos(ini_get('magic_quotes_gpc'), 'On') !== false)
			$directiveValues['magic_quotes_gpc']['status'] = true;
		$directiveValues['magic_quotes_gpc']['current'] = self::getFlag(ini_get('magic_quotes_gpc'));

		if (ini_get('magic_quotes_runtime') == '1' || stripos(ini_get('magic_quotes_runtime'), 'On') !== false)
			$directiveValues['magic_quotes_runtime']['status'] = true;
		$directiveValues['magic_quotes_runtime']['current'] = self::getFlag((ini_get('magic_quotes_runtime')));

		if (ini_get('zlib.output_compression') == '1' || stripos(ini_get('zlib.output_compression'), 'On') !== false)
			$directiveValues['zlib.output_compression']['status'] = true;
		$directiveValues['zlib.output_compression']['current'] = self::getFlag((ini_get('zlib.output_compression')));

		if (ini_get('zend.ze1_compatibility_mode') == '1' || stripos(ini_get('zend.ze1_compatibility_mode'), 'On') !== false)
			$directiveValues['zend.ze1_compatibility_mode']['status'] = true;
		$directiveValues['zend.ze1_compatibility_mode']['current'] = self::getFlag(ini_get('zend.ze1_compatibility_mode'));

		if (extension_loaded('suhosin')) {
			if (ini_get('suhosin.session.encrypt') == '1' || stripos(ini_get('suhosin.session.encrypt'), 'On') !== false)
				$directiveValues['suhosin.session.encrypt']['status'] = true;
			$directiveValues['suhosin.session.encrypt']['current'] = self::getFlag(ini_get('suhosin.session.encrypt'));
		}
		if (ini_get('session.auto_start') == '1' || stripos(ini_get('session.auto_start'), 'On') !== false)
			$directiveValues['session.auto_start']['status'] = true;
		$directiveValues['session.auto_start']['current'] = self::getFlag(ini_get('session.auto_start'));

		if (ini_get('mbstring.func_overload') == '1' || stripos(ini_get('mbstring.func_overload'), 'On') !== false)
			$directiveValues['mbstring.func_overload']['status'] = true;
		$directiveValues['mbstring.func_overload']['current'] = self::getFlag(ini_get('mbstring.func_overload'));

		if (ini_get('magic_quotes_sybase') == '1' || stripos(ini_get('magic_quotes_sybase'), 'On') !== false)
			$directiveValues['magic_quotes_sybase']['status'] = true;
		$directiveValues['magic_quotes_sybase']['current'] = self::getFlag(ini_get('magic_quotes_sybase'));

		if (ini_get('log_errors') == '1' || stripos(ini_get('log_errors'), 'On') !== false)
			$directiveValues['log_errors']['status'] = 'On';
		$directiveValues['log_errors']['current'] = self::getFlag(ini_get('log_errors'));

		if (ini_get('short_open_tag') != '1' || stripos(ini_get('short_open_tag'), 'Off') !== false)
			$directiveValues['short_open_tag']['status'] = 'Off';
		$directiveValues['short_open_tag']['current'] = self::getFlag(ini_get('short_open_tag'));

		if (ini_get('session.gc_maxlifetime') < 21600)
			$directiveValues['session.gc_maxlifetime']['status'] = true;
		$directiveValues['session.gc_maxlifetime']['current'] = ini_get('session.gc_maxlifetime');

		if (ini_get('session.gc_divisor') < 500)
			$directiveValues['session.gc_divisor']['status'] = true;
		$directiveValues['session.gc_divisor']['current'] = ini_get('session.gc_divisor');

		if (ini_get('session.gc_probability') < 1)
			$directiveValues['session.gc_probability']['status'] = true;
		$directiveValues['session.gc_probability']['current'] = ini_get('session.gc_probability');

		if (ini_get('max_input_vars') < 5000)
			$directiveValues['max_input_vars']['status'] = true;
		$directiveValues['max_input_vars']['current'] = ini_get('max_input_vars');

		if (version_compare(PHP_VERSION, '5.4.0', '<'))
			$directiveValues['PHP']['status'] = true;
		$directiveValues['PHP']['current'] = PHP_VERSION;

		if (extension_loaded('suhosin')) {
			if (ini_get('suhosin.request.max_vars') < 5000)
				$directiveValues['suhosin.request.max_vars']['status'] = true;
			$directiveValues['suhosin.request.max_vars']['current'] = ini_get('suhosin.request.max_vars');

			if (ini_get('suhosin.post.max_vars') < 5000)
				$directiveValues['suhosin.post.max_vars']['status'] = true;
			$directiveValues['suhosin.post.max_vars']['current'] = ini_get('suhosin.post.max_vars');

			if (ini_get('suhosin.post.max_value_length') < 1500000)
				$directiveValues['suhosin.post.max_value_length']['status'] = true;
			$directiveValues['suhosin.post.max_value_length']['current'] = ini_get('suhosin.post.max_value_length');
		}

		if (!$instalMode) {
			$db = PearDatabase::getInstance();
			$result = $db->query('SELECT @@max_allowed_packet');
			$maxAllowedPacket = $db->getSingleValue($result);
			if ($maxAllowedPacket < 16777216)
				$directiveValues['max_allowed_packet']['status'] = true;
			$directiveValues['max_allowed_packet']['current'] = Vtiger_Functions::showBytes($maxAllowedPacket);
		}

		$errorReporting = stripos(ini_get('error_reporting'), '_') === false ? self::error2string(ini_get('error_reporting')) : ini_get('error_reporting');
		if (in_array('E_NOTICE', $errorReporting) || in_array('E_DEPRECATED', $errorReporting) || in_array('E_STRICT', $errorReporting))
			$directiveValues['error_reporting']['status'] = true;
		$directiveValues['error_reporting']['current'] = implode(' | ', $errorReporting);

		return $directiveValues;
	}

	/**
	 * Function returns permissions to the core files and folder
	 * @return <Array>
	 */
	public static function getPermissionsFiles($onlyError = false)
	{
		$writableFilesAndFolders = self::$writableFilesAndFolders;
		$permissions = array();
		require_once ('include/utils/VtlibUtils.php');
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

	function error2string($value)
	{
		$level_names = array(
			E_ERROR => 'E_ERROR', E_WARNING => 'E_WARNING',
			E_PARSE => 'E_PARSE', E_NOTICE => 'E_NOTICE',
			E_CORE_ERROR => 'E_CORE_ERROR', E_CORE_WARNING => 'E_CORE_WARNING',
			E_COMPILE_ERROR => 'E_COMPILE_ERROR', E_COMPILE_WARNING => 'E_COMPILE_WARNING',
			E_USER_ERROR => 'E_USER_ERROR', E_USER_WARNING => 'E_USER_WARNING',
			E_USER_NOTICE => 'E_USER_NOTICE');
		if (defined('E_STRICT'))
			$level_names[E_STRICT] = 'E_STRICT';
		$levels = array();
		if (($value & E_ALL) == E_ALL) {
			$levels[] = 'E_ALL';
			$value&=~E_ALL;
		}
		foreach ($level_names as $level => $name)
			if (($value & $level) == $level)
				$levels[] = $name;
		return $levels;
	}
}
