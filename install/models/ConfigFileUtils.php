<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 * *********************************************************************************** */

class Install_ConfigFileUtils_Model
{

	private $dbHostname;
	private $dbPort;
	private $dbUsername;
	private $dbPassword;
	private $dbName;
	private $dbType;
	private $siteUrl;
	private $cacheDir;
	private $vtCharset = 'UTF-8';
	private $currencyName;
	private $adminEmail;
	private $default_language;
	private $timezone;

	public function __construct($configFileParameters)
	{
		if (isset($configFileParameters['db_hostname'])) {
			$this->dbHostname = $configFileParameters['db_hostname'];
		}
		if (isset($configFileParameters['db_username'])) {
			$this->dbUsername = $configFileParameters['db_username'];
		}
		if (isset($configFileParameters['db_password'])) {
			$this->dbPassword = $configFileParameters['db_password'];
		}
		if (isset($configFileParameters['db_name'])) {
			$this->dbName = $configFileParameters['db_name'];
		}
		if (isset($configFileParameters['db_type'])) {
			$this->dbType = $configFileParameters['db_type'];
		}
		if (isset($configFileParameters['site_URL'])) {
			$this->siteUrl = $configFileParameters['site_URL'];
		}
		if (isset($configFileParameters['admin_email'])) {
			$this->adminEmail = $configFileParameters['admin_email'];
		}
		if (isset($configFileParameters['currency_name'])) {
			$this->currencyName = $configFileParameters['currency_name'];
		}
		if (isset($configFileParameters['vt_charset'])) {
			$this->vtCharset = $configFileParameters['vt_charset'];
		}
		if (isset($configFileParameters['db_port'])) {
			$this->dbPort = $configFileParameters['db_port'];
		} else {
			$this->dbPort = self::getDbDefaultPort($this->dbType);
		}
		$this->default_language = ($GLOBALS['default_language'] != '') ? $GLOBALS['default_language'] : $configFileParameters['default_language'];
		$this->timezone = ( isset($_SESSION['config_file_info']['timezone']) ) ? $_SESSION['config_file_info']['timezone'] : $configFileParameters['timezone'];
		$this->cacheDir = 'cache/';
	}

	static function getDbDefaultPort($dbType)
	{
		if (Install_Utils_Model::isMySQL($dbType)) {
			return "3306";
		}
	}

	public function createConfigFile()
	{
		/* open template configuration file read only */
		$templateFilename = 'config/config.template.php';
		$templateHandle = fopen($templateFilename, 'r');
		if ($templateHandle) {
			/* open include configuration file write only */
			$includeFilename = 'config/config.inc.php';
			$includeHandle = fopen($includeFilename, 'w');
			if ($includeHandle) {
				while (!feof($templateHandle)) {
					$buffer = fgets($templateHandle);

					/* replace _DBC_ variable */
					$buffer = str_replace('_DBC_SERVER_', $this->dbHostname, $buffer);
					$buffer = str_replace('_DBC_PORT_', $this->dbPort, $buffer);
					$buffer = str_replace('_DBC_USER_', $this->dbUsername, $buffer);
					$buffer = str_replace('_DBC_PASS_', $this->dbPassword, $buffer);
					$buffer = str_replace('_DBC_NAME_', $this->dbName, $buffer);
					$buffer = str_replace('_DBC_TYPE_', $this->dbType, $buffer);
					$buffer = str_replace('_SITE_URL_', $this->siteUrl, $buffer);

					/* replace dir variable */
					$buffer = str_replace('_VT_CACHEDIR_', $this->cacheDir, $buffer);
					$buffer = str_replace('_VT_TMPDIR_', $this->cacheDir . 'images/', $buffer);
					$buffer = str_replace('_DB_STAT_', 'true', $buffer);

					/* replace charset variable */
					$buffer = str_replace('_VT_CHARSET_', $this->vtCharset, $buffer);

					/* replace master currency variable */
					$buffer = str_replace('_MASTER_CURRENCY_', $this->currencyName, $buffer);

					/* replace the application unique key variable */
					$buffer = str_replace('_VT_APP_UNIQKEY_', sha1(time() + rand(1, 9999999)), $buffer);

					/* replace support email variable */
					$buffer = str_replace('_LANG_', $this->default_language, $buffer);
					$buffer = str_replace('_TIMEZONE_', $this->timezone, $buffer);

					fwrite($includeHandle, $buffer);
				}
				fclose($includeHandle);
			}
			fclose($templateHandle);
		}
		if ($templateHandle && $includeHandle) {
			return true;
		}
		return false;
	}
}
