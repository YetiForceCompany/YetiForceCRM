<?php
/*+***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 *************************************************************************************************************************************/
class Settings_ConfReport_Module_Model extends Settings_Vtiger_Module_Model {
	/**
	 * variable has all the files and folder that should be writable
	 * @var <Array>
	 */
	public static $writableFilesAndFolders = array (
		'Configuration File' => 'config/config.inc.php',
		'Tabdata File' => 'user_privileges/tabdata.php',
		'Parent Tabdata File' => 'config/parent_tabdata.php',
		'Cache Directory' => 'cache/',
		'Image Cache Directory' => 'cache/images/',
		'Import Cache Directory' => 'cache/import/',
		'Storage Directory' => 'storage/',
		'User Privileges Directory' => 'user_privileges/',
		'Modules Directory' => 'modules/',
		'Cron Modules Directory' => 'cron/modules/',
		'Vtlib Test Directory' => 'cache/vtlib/',
		'Vtlib Test HTML Directory' => 'cache/vtlib/HTML',
		'Product Image Directory' => 'storage/Products/',
		'User Image Directory' => 'storage/Users/',
		'Contact Image Directory' => 'storage/Contacts/',
		'Logo Directory' => 'storage/Logo/',
	);
	
	public static function getConfigurationValue() {
		$directiveValues = array (
			'php_version' => array('prefer' => PHP_VERSION),
			'safe_mode' => array('prefer' => 'Off'),
			'display_errors' => array('prefer' => 'Off'),
			'file_uploads' => array('prefer' => 'On'),
			'register_globals' => array('prefer' => 'On'),
			'output_buffering' => array('prefer' => 'On'),
			'max_execution_time' => array('prefer' => '600'),
			'memory_limit' => array('prefer' => '32'),
			'error_reporting' => array('prefer' => 'E_WARNING & ~E_NOTICE & ~E_DEPRECATED'),
			'allow_call_time_pass_reference' => array('prefer' => 'On'),
			'log_errors' => array('prefer' => 'Off'),
			'short_open_tag' => array('prefer' => 'On'),
			'max_input_vars' => array('prefer' => '5000'),
			'post_max_size' => array('prefer' => '10M'),
			'upload_max_filesize' => array('prefer' => '10M'),
			'magic_quotes_gpc' => array('prefer' => 'Off'),
			'magic_quotes_runtime' => array('prefer' => 'Off'),
			'zlib.output_compression' => array('prefer' => 'Off'),
			'zend.ze1_compatibility_mode' => array('prefer' => 'Off'),
			'session.auto_start' => array('prefer' => 'Off'),
			'magic_quotes_sybase' => array('prefer' => 'Off'),
			'session.gc_maxlifetime' => array('prefer' => '21600'),
			'session.gc_divisor' => array('prefer' => '500'),
			'session.gc_probability' => array('prefer' => '1'),
		);
		if ( extension_loaded('suhosin') ){
			$directiveValues['suhosin.session.encrypt'] = array('prefer' => 'Off');
			$directiveValues['suhosin.request.max_vars'] = array('prefer' => '5000');
			$directiveValues['suhosin.post.max_vars'] = array('prefer' => '5000');
			$directiveValues['suhosin.post.max_value_length'] = array('prefer' => '1500000');
		}
		
		if (ini_get('safe_mode') == '1' || stripos(ini_get('safe_mode'), 'On') !== false)
			$directiveValues['safe_mode']['current'] = 'On';
		if (ini_get('display_errors') != '0' || stripos(ini_get('display_errors'), 'On') !== false)
			$directiveValues['display_errors']['current'] = 'On';
		if (ini_get('file_uploads') != '1' || stripos(ini_get('file_uploads'), 'Off') !== false)
			$directiveValues['file_uploads']['current'] = 'Off';
		if (ini_get('register_globals') == '1' || stripos(ini_get('register_globals'), 'On') !== false)
			$directiveValues['register_globals']['current'] = 'On';
		if (ini_get(('output_buffering') < '4096' && ini_get('output_buffering') != '0') || stripos(ini_get('output_buffering'), 'Off') !== false)
			$directiveValues['output_buffering']['current'] = 'Off';
		if (ini_get('max_execution_time') < 600)
			$directiveValues['max_execution_time']['current'] = ini_get('max_execution_time');
		if (ini_get('memory_limit') < 32)
			$directiveValues['memory_limit']['current'] = ini_get('memory_limit');
		if ( (int) ini_get('post_max_size') < 10)
			$directiveValues['post_max_size']['current'] = ini_get('post_max_size');
		if ( (int) ini_get('upload_max_filesize') < 10)
			$directiveValues['upload_max_filesize']['current'] = ini_get('upload_max_filesize');
		if (ini_get('magic_quotes_gpc') == '1' || stripos(ini_get('magic_quotes_gpc'), 'On') !== false)
			$directiveValues['magic_quotes_gpc']['current'] = ini_get('magic_quotes_gpc');
		if (ini_get('magic_quotes_runtime') == '1' || stripos(ini_get('magic_quotes_runtime'), 'On') !== false)
			$directiveValues['magic_quotes_runtime']['current'] = ini_get('magic_quotes_runtime');
		if (ini_get('zlib.output_compression') == '1' || stripos(ini_get('zlib.output_compression'), 'On') !== false)
			$directiveValues['zlib.output_compression']['current'] = 'On';			
		if (ini_get('zend.ze1_compatibility_mode') == '1' || stripos(ini_get('zend.ze1_compatibility_mode'), 'On') !== false)
			$directiveValues['zend.ze1_compatibility_mode']['current'] = ini_get('zend.ze1_compatibility_mode');			
		if (ini_get('suhosin.session.encrypt') == '1' || stripos(ini_get('suhosin.session.encrypt'), 'On') !== false)
			$directiveValues['suhosin.session.encrypt']['current'] = ini_get('suhosin.session.encrypt');
		if (ini_get('session.auto_start') == '1' || stripos(ini_get('session.auto_start'), 'On') !== false)
			$directiveValues['session.auto_start']['current'] = ini_get('session.auto_start');
		if (ini_get('mbstring.func_overload') == '1' || stripos(ini_get('mbstring.func_overload'), 'On') !== false)
			$directiveValues['mbstring.func_overload']['current'] = ini_get('mbstring.func_overload');
		if (ini_get('magic_quotes_sybase') == '1' || stripos(ini_get('magic_quotes_sybase'), 'On') !== false)
			$directiveValues['magic_quotes_sybase']['current'] = ini_get('magic_quotes_sybase');
		if (ini_get('session.gc_maxlifetime') < 21600)
			$directiveValues['session.gc_maxlifetime']['current'] = ini_get('session.gc_maxlifetime');
		if (ini_get('session.gc_divisor') < 500)
			$directiveValues['session.gc_divisor']['current'] = ini_get('session.gc_divisor');
		if (ini_get('session.gc_probability') < 1)
			$directiveValues['session.gc_probability']['current'] = ini_get('session.gc_probability');
			
		$directiveValues['error_log'] = ini_get('error_log');
			
		$version = explode('.', PHP_VERSION);
		$php_version = ($version[0] * 10000 + $version[1] * 100 + $version[2]);
		if ($php_version < 50300) {
			$directiveValues['php_version']['current'] = PHP_VERSION;
		}
		if ( extension_loaded('suhosin') ){
			if ( ini_get('suhosin.request.max_vars') < 5000 )
				$directiveValues['suhosin.request.max_vars']['current'] = ini_get('suhosin.request.max_vars');
			if ( ini_get('suhosin.post.max_vars') < 5000 )
				$directiveValues['suhosin.post.max_vars']['current'] = ini_get('suhosin.post.max_vars');
			if ( ini_get('suhosin.post.max_value_length') < 1500000 )
				$directiveValues['suhosin.post.max_value_length']['current'] = ini_get('suhosin.post.max_value_length');
		}
		$errorReportingValue = E_WARNING & ~E_NOTICE;
		if(version_compare(PHP_VERSION, '5.5.0') >= 0){
			$errorReportingValue = E_WARNING & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT;
		}
		else if(version_compare(PHP_VERSION, '5.3.0') >= 0) {
			$errorReportingValue = E_WARNING & ~E_NOTICE & ~E_DEPRECATED;
		}

		if (ini_get('error_reporting') != $errorReportingValue && ini_get('error_reporting') != 22519)
			$directiveValues['error_reporting']['current'] = 'NOT RECOMMENDED';
		if (ini_get('log_errors') == '1' || stripos(ini_get('log_errors'), 'On') !== false)
			$directiveValues['log_errors']['current'] = 'On';
		if (ini_get('short_open_tag') != '1' || stripos(ini_get('short_open_tag'), 'Off') !== false)
			$directiveValues['short_open_tag']['current'] = 'Off';

		return $directiveValues;
	}
	
	/**
	 * Function returns permissions to the core files and folder
	 * @return <Array>
	 */
	public static function getPermissionsFiles() {
		$writableFilesAndFolders = self::$writableFilesAndFolders;
		$permissions = array();
		require_once ('include/utils/VtlibUtils.php');
		foreach ($writableFilesAndFolders as $index => $value) {
			$permissions[$index]['permission'] = 'TruePermission';
			$permissions[$index]['path'] = $value;
			if (!vtlib_isWriteable($value)) {
				$permissions[$index]['permission'] = 'FailedPermission';
			}
		}
		return $permissions;
	}
}
