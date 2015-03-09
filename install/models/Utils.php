<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class Install_Utils_Model {

	/**
	 * variable has all the files and folder that should be writable
	 * @var <Array>
	 */
	public static $writableFilesAndFolders = array (
		'Configuration File' => './config/config.inc.php',
		'Tabdata File' => './user_privileges/tabdata.php',
		'Parent Tabdata File' => './config/parent_tabdata.php',
		'Cache Directory' => './cache/',
		'Image Cache Directory' => './cache/images/',
		'Import Cache Directory' => './cache/import/',
		'Storage Directory' => './storage/',
		'User Privileges Directory' => './user_privileges/',
		'Modules Directory' => './modules/',
		'Cron Modules Directory' => './cron/modules/',
		'Vtlib Test Directory' => './cache/vtlib/',
		'Vtlib Test HTML Directory' => './cache/vtlib/HTML',
		'Product Image Directory' => './storage/Products/',
		'User Image Directory' => './storage/Users/',
		'Contact Image Directory' => './storage/Contacts/',
		'Logo Directory' => './storage/Logo/',
	);

	/**
	 * Function returns all the files and folder that are not writable
	 * @return <Array>
	 */
	public static function getFailedPermissionsFiles() {
		$writableFilesAndFolders = self::$writableFilesAndFolders;
		$failedPermissions = array();
		require_once ('include/utils/VtlibUtils.php');
		foreach ($writableFilesAndFolders as $index => $value) {
			if (!vtlib_isWriteable($value)) {
				$failedPermissions[$index] = $value;
			}
		}
		return $failedPermissions;
	}

	/**
	 * Function returns the php.ini file settings required for installing vtigerCRM
	 * @return <Array>
	 */
	static function getCurrentDirectiveValue() {
		$directiveValues = array();
		if (ini_get('safe_mode') == '1' || stripos(ini_get('safe_mode'), 'On') !== false)
			$directiveValues['safe_mode'] = 'On';
		if (ini_get('display_errors') != '1' || stripos(ini_get('display_errors'), 'Off') !== false)
			$directiveValues['display_errors'] = 'Off';
		if (ini_get('file_uploads') != '1' || stripos(ini_get('file_uploads'), 'Off') !== false)
			$directiveValues['file_uploads'] = 'Off';
		if (ini_get('register_globals') == '1' || stripos(ini_get('register_globals'), 'On') !== false)
			$directiveValues['register_globals'] = 'On';
		if (ini_get('output_buffering') != '1' || stripos(ini_get('output_buffering'), 'On') !== false)
			$directiveValues['output_buffering'] = ini_get('output_buffering');
		if (ini_get('max_execution_time') < 600)
			$directiveValues['max_execution_time'] = ini_get('max_execution_time');
		if (ini_get('max_input_time') < 600)
			$directiveValues['max_input_time'] = ini_get('max_input_time');
		if (ini_get('max_input_vars') < 5000)
			$directiveValues['max_input_vars'] = ini_get('max_input_vars');
		if (ini_get('memory_limit') < 64)
			$directiveValues['memory_limit'] = ini_get('memory_limit');
		if ( (int) ini_get('post_max_size') < 10)
			$directiveValues['post_max_size'] = ini_get('post_max_size');
		if ( (int) ini_get('upload_max_filesize') < 10)
			$directiveValues['upload_max_filesize'] = ini_get('upload_max_filesize');
		if (ini_get('magic_quotes_gpc') == '1' || stripos(ini_get('magic_quotes_gpc'), 'On') !== false)
			$directiveValues['magic_quotes_gpc'] = ini_get('magic_quotes_gpc');
		if (ini_get('magic_quotes_runtime') == '1' || stripos(ini_get('magic_quotes_runtime'), 'On')!== false)
			$directiveValues['magic_quotes_runtime'] = ini_get('magic_quotes_runtime');
		if (ini_get('zlib.output_compression') == '1' || stripos(ini_get('zlib.output_compression'), 'On') !== false)
			$directiveValues['zlib.output_compression'] = ini_get('zlib.output_compression');			
		if (ini_get('zend.ze1_compatibility_mode') == '1' || stripos(ini_get('zend.ze1_compatibility_mode'), 'On') !== false)
			$directiveValues['zend.ze1_compatibility_mode'] = ini_get('zend.ze1_compatibility_mode');			
		if (ini_get('suhosin.session.encrypt') == '1' || stripos(ini_get('suhosin.session.encrypt'), 'On') !== false)
			$directiveValues['suhosin.session.encrypt'] = ini_get('suhosin.session.encrypt');
		if (ini_get('session.auto_start') == '1' || stripos(ini_get('session.auto_start'), 'On') !== false)
			$directiveValues['session.auto_start'] = ini_get('session.auto_start');
			
		if (ini_get('mbstring.func_overload') == '1' || stripos(ini_get('mbstring.func_overload'), 'On') !== false)
			$directiveValues['mbstring.func_overload'] = ini_get('mbstring.func_overload');
		if (ini_get('magic_quotes_sybase') == '1' || stripos(ini_get('magic_quotes_sybase'), 'On') !== false)
			$directiveValues['magic_quotes_sybase'] = ini_get('magic_quotes_sybase');

		if ( extension_loaded('suhosin') ){
			if ( ini_get('suhosin.request.max_vars') < 5000 )
				$directiveValues['suhosin.request.max_vars'] = ini_get('suhosin.request.max_vars');
			if ( ini_get('suhosin.post.max_vars') < 5000 )
				$directiveValues['suhosin.post.max_vars'] = ini_get('suhosin.post.max_vars');
			if ( ini_get('suhosin.post.max_value_length') < 1500000 )
				$directiveValues['suhosin.post.max_value_length'] = ini_get('suhosin.post.max_value_length');
		}
		$errorReportingValue = E_WARNING & ~E_NOTICE;
		if(version_compare(PHP_VERSION, '5.5.0') >= 0){
			$errorReportingValue = E_WARNING & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT;
		}
		else if(version_compare(PHP_VERSION, '5.3.0') >= 0) {
			$errorReportingValue = E_WARNING & ~E_NOTICE & ~E_DEPRECATED;
		}

		if (ini_get('error_reporting') != $errorReportingValue && ini_get('error_reporting') != 22519)
			$directiveValues['error_reporting'] = 'NOT RECOMMENDED';
		if (ini_get('log_errors') == '1' || stripos(ini_get('log_errors'), 'On') !== false)
			$directiveValues['log_errors'] = 'On';
		if (ini_get('short_open_tag') != '1' || stripos(ini_get('short_open_tag'), 'Off') !== false)
			$directiveValues['short_open_tag'] = 'Off';
		return $directiveValues;
	}

	/**
	 * Variable has the recommended php settings for smooth running of vtigerCRM
	 * @var <Array>
	 */
	public static $recommendedDirectives = array (
		'safe_mode' => 'Off',
		'display_errors' => 'On',
		'file_uploads' => 'On',
		'register_globals' => 'On',
		'output_buffering' => 'On',
		'max_execution_time' => '600',
		'max_input_time' => '600',
		'memory_limit' => '64',
		'error_reporting' => 'E_WARNING & ~E_NOTICE',
		'log_errors' => 'Off',
		'short_open_tag' => 'On',
		'max_input_vars' => '5000',
		'post_max_size' => '10M',
		'upload_max_filesize' => '10M',
		'magic_quotes_gpc' => 'Off',
		'magic_quotes_runtime' => 'Off',
		'zlib.output_compression' => 'Off',
		'zend.ze1_compatibility_mode' => 'Off',
		'session.auto_start' => 'Off',
		'magic_quotes_sybase' => 'Off',
		
		'suhosin.session.encrypt' => 'Off',
		'suhosin.request.max_vars' => '5000',
		'suhosin.post.max_vars' => '5000',
		'suhosin.post.max_value_length' => '1500000',
	);

	/**
	 * Returns the recommended php settings for vtigerCRM
	 * @return type
	 */
	function getRecommendedDirectives(){
		if(version_compare(PHP_VERSION, '5.5.0') >= 0){
			self::$recommendedDirectives['error_reporting'] = 'E_WARNING & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT';
		}
	    else if(version_compare(PHP_VERSION, '5.3.0') >= 0) {
			self::$recommendedDirectives['error_reporting'] = 'E_WARNING & ~E_NOTICE & ~E_DEPRECATED';
		}
		return self::$recommendedDirectives;
	}

	/**
	 * Function checks for vtigerCRM installation prerequisites
	 * @return <Array>
	 */
	function getSystemPreInstallParameters() {
		$preInstallConfig = array();
		// Name => array( System Value, Recommended value, supported or not(true/false) );
		$preInstallConfig['LBL_PHP_VERSION']	= array(phpversion(), '5.5.0', (version_compare(phpversion(), '5.5.0', '<=')));
		$preInstallConfig['LBL_IMAP_SUPPORT']	= array(function_exists('imap_open'), true, (function_exists('imap_open') == true));
		$preInstallConfig['LBL_ZLIB_SUPPORT']	= array(function_exists('gzinflate'), true, (function_exists('gzinflate') == true));
		if ($preInstallConfig['LBL_PHP_VERSION'] >= '5.5.0') {
			$preInstallConfig['LBL_MYSQLI_CONNECT_SUPPORT'] = array(extension_loaded('mysqli'), true, extension_loaded('mysqli'));
		}
		$preInstallConfig['LBL_OPEN_SSL'] = array(extension_loaded('openssl'), true, extension_loaded('openssl'));
		$preInstallConfig['LBL_CURL'] = array(extension_loaded('curl'), true, extension_loaded('curl'));
		$gnInstalled = false;
		if(!function_exists('gd_info')) {
			eval(self::$gdInfoAlternate);
		}
		$gd_info = gd_info();
		if (isset($gd_info['GD Version'])) {
			$gnInstalled = true;
		}
		$preInstallConfig['LBL_GD_LIBRARY']		= array((extension_loaded('gd') || $gnInstalled), true, (extension_loaded('gd') || $gnInstalled));
		$preInstallConfig['LBL_ZLIB_SUPPORT']	= array(function_exists('gzinflate'), true, (function_exists('gzinflate') == true));

		return $preInstallConfig;
	}
	
	/**
	 * Function that provides default configuration based on installer setup
	 * @return <Array>
	 */
	function getDefaultPreInstallParameters() {
		include 'config/config.db.php';
		
		$parameters = array(
			'db_hostname' => 'localhost',
			'db_username' => '',
			'db_password' => '',
			'db_name'     => '',
			'admin_name'  => 'admin',
			'admin_lastname'=> 'Administrator',
			'admin_password'=>'',
			'admin_email' => '',
		);
		
		if (isset($dbconfig) && isset($vtconfig)) {
			if (isset($dbconfig['db_server']) && $dbconfig['db_server'] != '_DBC_SERVER_') {
				$parameters['db_hostname'] = $dbconfig['db_server'] . ':' . $dbconfig['db_port'];
				$parameters['db_username'] = $dbconfig['db_username'];
				$parameters['db_password'] = $dbconfig['db_password'];
				$parameters['db_name']     = $dbconfig['db_name'];
				
				$parameters['admin_password'] = $vtconfig['adminPwd'];
				$parameters['admin_email']    = $vtconfig['adminEmail'];
			}
		}
		
		return $parameters;
	}

	/**
	 * Function returns gd library information
	 * @var type
	 */
	public static $gdInfoAlternate = 'function gd_info() {
		$array = Array(
	               "GD Version" => "",
	               "FreeType Support" => 0,
	               "FreeType Support" => 0,
	               "FreeType Linkage" => "",
	               "T1Lib Support" => 0,
	               "GIF Read Support" => 0,
	               "GIF Create Support" => 0,
	               "JPG Support" => 0,
	               "PNG Support" => 0,
	               "WBMP Support" => 0,
	               "XBM Support" => 0
	             );
		       $gif_support = 0;

		       ob_start();
		       eval("phpinfo();");
		       $info = ob_get_contents();
		       ob_end_clean();

		       foreach(explode("\n", $info) as $line) {
		           if(strpos($line, "GD Version")!==false)
		               $array["GD Version"] = trim(str_replace("GD Version", "", strip_tags($line)));
		           if(strpos($line, "FreeType Support")!==false)
		               $array["FreeType Support"] = trim(str_replace("FreeType Support", "", strip_tags($line)));
		           if(strpos($line, "FreeType Linkage")!==false)
		               $array["FreeType Linkage"] = trim(str_replace("FreeType Linkage", "", strip_tags($line)));
		           if(strpos($line, "T1Lib Support")!==false)
		               $array["T1Lib Support"] = trim(str_replace("T1Lib Support", "", strip_tags($line)));
		           if(strpos($line, "GIF Read Support")!==false)
		               $array["GIF Read Support"] = trim(str_replace("GIF Read Support", "", strip_tags($line)));
		           if(strpos($line, "GIF Create Support")!==false)
		               $array["GIF Create Support"] = trim(str_replace("GIF Create Support", "", strip_tags($line)));
		           if(strpos($line, "GIF Support")!==false)
		               $gif_support = trim(str_replace("GIF Support", "", strip_tags($line)));
		           if(strpos($line, "JPG Support")!==false)
		               $array["JPG Support"] = trim(str_replace("JPG Support", "", strip_tags($line)));
		           if(strpos($line, "PNG Support")!==false)
		               $array["PNG Support"] = trim(str_replace("PNG Support", "", strip_tags($line)));
		           if(strpos($line, "WBMP Support")!==false)
		               $array["WBMP Support"] = trim(str_replace("WBMP Support", "", strip_tags($line)));
		           if(strpos($line, "XBM Support")!==false)
		               $array["XBM Support"] = trim(str_replace("XBM Support", "", strip_tags($line)));
		       }

		       if($gif_support==="enabled") {
		           $array["GIF Read Support"]  = 1;
		           $array["GIF Create Support"] = 1;
		       }

		       if($array["FreeType Support"]==="enabled"){
		           $array["FreeType Support"] = 1;    }

		       if($array["T1Lib Support"]==="enabled")
		           $array["T1Lib Support"] = 1;

		       if($array["GIF Read Support"]==="enabled"){
		           $array["GIF Read Support"] = 1;    }

		       if($array["GIF Create Support"]==="enabled")
		           $array["GIF Create Support"] = 1;

		       if($array["JPG Support"]==="enabled")
		           $array["JPG Support"] = 1;

		       if($array["PNG Support"]==="enabled")
		           $array["PNG Support"] = 1;

		       if($array["WBMP Support"]==="enabled")
		           $array["WBMP Support"] = 1;

		       if($array["XBM Support"]==="enabled")
		           $array["XBM Support"] = 1;

		       return $array;

		}';

	/**
	 * Returns list of currencies
	 * @return <Array>
	 */
	public static function getCurrencyList() {
		require_once 'install/models/Currencies.php';
		return $currencies;
	}

	/**
	 * Function checks if its mysql type
	 * @param type $dbType
	 * @return type
	 */
	static function isMySQL($dbType) {
		return (stripos($dbType ,'mysql') === 0);
	}

	/**
	 * Function returns mysql version
	 * @param type $serverInfo
	 * @return type
	 */
	public static function getMySQLVersion($serverInfo) {
		if(!is_array($serverInfo)) {
			$version = explode('-',$serverInfo);
			$mysql_server_version=$version[0];
		} else {
			$mysql_server_version = $serverInfo['version'];
		}
		return $mysql_server_version;
	}

	/**
	 * Function checks the database connection
	 * @param <String> $db_type
	 * @param <String> $db_hostname
	 * @param <String> $db_username
	 * @param <String> $db_password
	 * @param <String> $db_name
	 * @param <String> $create_db
	 * @param <String> $create_utf8_db
	 * @param <String> $root_user
	 * @param <String> $root_password
	 * @return <Array>
	 */
	public static function checkDbConnection($db_type, $db_hostname, $db_username, $db_password, $db_name, $create_db=false, $create_utf8_db=true, $root_user='', $root_password='') {
		$dbCheckResult = array();

		$db_type_status = false; // is there a db type?
		$db_server_status = false; // does the db server connection exist?
		$db_creation_failed = false; // did we try to create a database and fail?
		$db_exist_status = false; // does the database exist?
		$db_utf8_support = false; // does the database support utf8?

		//Checking for database connection parameters
		if($db_type) {
			$conn = &NewADOConnection($db_type);
			$db_type_status = true;
			if(@$conn->Connect($db_hostname,$db_username,$db_password)) {
				$db_server_status = true;
				$serverInfo = $conn->ServerInfo();
				if(self::isMySQL($db_type)) {
					$mysql_server_version = self::getMySQLVersion($serverInfo);
				}
				if($create_db) {
					// drop the current database if it exists
					$dropdb_conn = &NewADOConnection($db_type);
					if(@$dropdb_conn->Connect($db_hostname, $root_user, $root_password, $db_name)) {
						$query = "DROP DATABASE ".$db_name;
						$dropdb_conn->Execute($query);
						$dropdb_conn->Close();
					}

					// create the new database
					$db_creation_failed = true;
					$createdb_conn = &NewADOConnection($db_type);
					if(@$createdb_conn->Connect($db_hostname, $root_user, $root_password)) {
						$query = "CREATE DATABASE ".$db_name;
						if($create_utf8_db == 'true') {
							if(self::isMySQL($db_type))
								$query .= " DEFAULT CHARACTER SET utf8 DEFAULT COLLATE utf8_general_ci";
							$db_utf8_support = true;
						}
						if($createdb_conn->Execute($query)) {
							$db_creation_failed = false;
						}
						$createdb_conn->Close();
					}
				}

				if(@$conn->Connect($db_hostname, $db_username, $db_password, $db_name)) {
					$db_exist_status = true;
					if(!$db_utf8_support) {
						$db_utf8_support = Vtiger_Util_Helper::checkDbUTF8Support($conn);
					}
				}
				$conn->Close();
			}
		}
		$dbCheckResult['db_utf8_support'] = $db_utf8_support;

		$error_msg = '';
		$error_msg_info = '';

		if(!$db_type_status || !$db_server_status) {
			$error_msg = getTranslatedString('ERR_DATABASE_CONNECTION_FAILED', 'Install').'. '.getTranslatedString('ERR_INVALID_MYSQL_PARAMETERS', 'Install');
			$error_msg_info = getTranslatedString('MSG_LIST_REASONS', 'Install').':<br>
					-  '.getTranslatedString('MSG_DB_PARAMETERS_INVALID', 'Install').'
					-  '.getTranslatedString('MSG_DB_USER_NOT_AUTHORIZED', 'Install');
		} elseif(self::isMySQL($db_type) && $mysql_server_version < 4.1) {
			$error_msg = $mysql_server_version.' -> '.getTranslatedString('ERR_INVALID_MYSQL_VERSION', 'Install');
		} elseif($db_creation_failed) {
			$error_msg = getTranslatedString('ERR_UNABLE_CREATE_DATABASE', 'Install').' '.$db_name;
			$error_msg_info = getTranslatedString('MSG_DB_ROOT_USER_NOT_AUTHORIZED', 'Install');
		} elseif(!$db_exist_status) {
			$error_msg = $db_name.' -> '.getTranslatedString('ERR_DB_NOT_FOUND', 'Install');
		} else {
			$dbCheckResult['flag'] = true;
			return $dbCheckResult;
		}
		$dbCheckResult['flag'] = false;
		$dbCheckResult['error_msg'] = $error_msg;
		$dbCheckResult['error_msg_info'] = $error_msg_info;
		return $dbCheckResult;
	}
	public static function getLanguages() {
		$dir = 'languages/';
		$ffs = scandir($dir);
        $langs = array();
		foreach($ffs as $ff){
			if( $ff != '.' && $ff != '..' ){ 
				if(file_exists($dir.$ff.'/Install.php')){
					$langs[$ff] = Vtiger_Language_Handler::getTranslatedString('LANGNAME', 'Install',$ff);
				}
			}
		}
		return $langs;
	}
}
