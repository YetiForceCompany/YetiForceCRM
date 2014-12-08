<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

Class ModComments_Edit_View extends Vtiger_Edit_View {

	public function checkPermission(Vtiger_Request $request) {
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		$moduleName = $request->getModule();
		$record = $request->get('record');
		if (!empty($record) || !Users_Privileges_Model::isPermitted($moduleName, 'EditView')) {
			throw new AppException('LBL_PERMISSION_DENIED');
		}
	}
}