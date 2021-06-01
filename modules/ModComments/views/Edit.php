<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class ModComments_Edit_View extends Vtiger_Edit_View
{
	/**
	 * Function to check permission.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\NoPermitted
	 */
	public function checkPermission(App\Request $request)
	{
		$moduleName = $request->getModule();
		$record = $request->getInteger('record');
		if ($record || !\App\Privilege::isPermitted($moduleName, 'EditView')) {
			throw new \App\Exceptions\NoPermitted('ERR_NO_PERMISSIONS_FOR_THE_RECORD');
		}
	}
}
