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
 * Provides API to work with vtiger CRM Webservice (available from vtiger 5.1).
 */
class Webservice
{
	/**
	 * Initialize webservice for the given module.
	 *
	 * @param \vtlib\ModuleBasic $moduleInstance
	 */
	public static function initialize(ModuleBasic $moduleInstance)
	{
		if ($moduleInstance->isentitytype && \function_exists('vtws_addDefaultModuleTypeEntity')) {
			vtws_addDefaultModuleTypeEntity($moduleInstance->name);
			\App\Log::trace('Initializing webservices support ...DONE', __METHOD__);
		}
	}
}
