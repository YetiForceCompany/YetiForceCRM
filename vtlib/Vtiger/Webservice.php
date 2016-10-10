<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ********************************************************************************** */
namespace vtlib;

/**
 * Provides API to work with vtiger CRM Webservice (available from vtiger 5.1)
 * @package vtlib
 */
class Webservice
{

	/**
	 * Helper function to log messages
	 * @param String Message to log
	 * @param Boolean true appends linebreak, false to avoid it
	 * @access private
	 */
	static function log($message, $delim = true)
	{
		Utils::Log($message, $delim);
	}

	/**
	 * Initialize webservice for the given module
	 * @param Module Instance of the module.
	 */
	static function initialize($moduleInstance)
	{
		if ($moduleInstance->isentitytype) {
			if (function_exists('vtws_addDefaultModuleTypeEntity')) {
				vtws_addDefaultModuleTypeEntity($moduleInstance->name);
				self::log("Initializing webservices support ...DONE");
			}
		}
	}

	/**
	 * Initialize webservice for the given module
	 * @param Module Instance of the module.
	 */
	static function uninitialize($moduleInstance)
	{
		if ($moduleInstance->isentitytype) {

			if (function_exists('vtws_deleteWebserviceEntity')) {
				vtws_deleteWebserviceEntity($moduleInstance->name);
				self::log("De-Initializing webservices support ...DONE");
			}
		}
	}
}
