<?php
/* * *******************************************************************************
 * The contents of this file are subject to the SugarCRM Public License Version 1.1.2
 * ("License"); You may not use this file except in compliance with the
 * License. You may obtain a copy of the License at http://www.sugarcrm.com/SPL
 * Software distributed under the License is distributed on an  "AS IS"  basis,
 * WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License for
 * the specific language governing rights and limitations under the License.
 * The Original Code is:  SugarCRM Open Source
 * The Initial Developer of the Original Code is SugarCRM, Inc.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.;
 * All Rights Reserved.
 * Contributor(s): YetiForce.com.
 * ****************************************************************************** */
/* * *******************************************************************************
 * $Header$
 * Description:  Includes generic helper functions used throughout the application.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com.
 * ****************************************************************************** */
require_once 'include/database/PearDatabase.php';
require_once 'include/utils/CommonUtils.php';
require_once 'include/fields/DateTimeField.php';
require_once 'include/fields/DateTimeRange.php';
require_once 'include/fields/CurrencyField.php';
require_once 'include/CRMEntity.php';
include_once 'modules/Vtiger/CRMEntity.php';
require_once 'include/runtime/Cache.php';
require_once 'modules/Vtiger/helpers/Util.php';
require_once 'modules/PickList/DependentPickListUtils.php';
require_once 'modules/Users/Users.php';
require_once 'include/Webservices/Utils.php';

// Constants to be defined here
// For Migration status.
define("MIG_CHARSET_PHP_UTF8_DB_UTF8", 1);
define("MIG_CHARSET_PHP_NONUTF8_DB_NONUTF8", 2);
define("MIG_CHARSET_PHP_NONUTF8_DB_UTF8", 3);
define("MIG_CHARSET_PHP_UTF8_DB_NONUTF8", 4);

// For Restoration.
define("RB_RECORD_DELETED", 'delete');
define("RB_RECORD_INSERTED", 'insert');
define("RB_RECORD_UPDATED", 'update');

/** Function to get column fields for a given module
 * @param $module -- module:: Type string
 * @returns $column_fld -- column field :: Type array
 *
 */
function getColumnFields($module)
{

	\App\Log::trace('Entering getColumnFields(' . $module . ') method ...');

	// Lookup in cache for information
	$cachedModuleFields = VTCacheUtils::lookupFieldInfoModule($module);

	if ($cachedModuleFields === false) {
		$fieldsInfo = vtlib\Functions::getModuleFieldInfos($module);
		if (!empty($fieldsInfo)) {
			foreach ($fieldsInfo as $resultrow) {
				// Update information to cache for re-use
				VTCacheUtils::updateFieldInfo(
					$resultrow['tabid'], $resultrow['fieldname'], $resultrow['fieldid'], $resultrow['fieldlabel'], $resultrow['columnname'], $resultrow['tablename'], $resultrow['uitype'], $resultrow['typeofdata'], $resultrow['presence']
				);
			}
		}
		// For consistency get information from cache
		$cachedModuleFields = VTCacheUtils::lookupFieldInfoModule($module);
	}

	if ($module == 'Calendar') {
		$cachedEventsFields = VTCacheUtils::lookupFieldInfoModule('Events');
		if (!$cachedEventsFields) {
			getColumnFields('Events');
			$cachedEventsFields = VTCacheUtils::lookupFieldInfoModule('Events');
		}

		if (!$cachedModuleFields) {
			$cachedModuleFields = $cachedEventsFields;
		} else {
			$cachedModuleFields = array_merge($cachedModuleFields, $cachedEventsFields);
		}
	}

	$column_fld = [];
	if ($cachedModuleFields) {
		foreach ($cachedModuleFields as $fieldinfo) {
			$column_fld[$fieldinfo['fieldname']] = '';
		}
	}

	\App\Log::trace('Exiting getColumnFields method ...');
	return $column_fld;
}
//functions for asterisk integration end
