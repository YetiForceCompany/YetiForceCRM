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

class Settings_GlobalPermission_Save_Action extends Settings_Vtiger_Save_Action
{

	public function __construct()
	{
		Settings_Vtiger_Tracker_Model::setRecordId(AppRequest::get('profileID'));
		parent::__construct();
	}

	public function checkPermission(Vtiger_Request $request)
	{
		$currentUser = Users_Record_Model::getCurrentUserModel();
		if (!$currentUser->isAdminUser()) {
			throw new \Exception\AppException('LBL_PERMISSION_DENIED');
		}
	}

	public function process(Vtiger_Request $request)
	{
		$profileID = $request->get('profileID');
		$checked = $request->get('checked');
		$globalactionid = $request->get('globalactionid');
		if ($globalactionid == 1) {
			$globalActionName = 'LBL_VIEW_ALL';
		} else {
			$globalActionName = 'LBL_EDIT_ALL';
		}
		if ($checked == 'true') {
			$checked = 1;
			$prev[$globalActionName] = 0;
		} else {
			$checked = 0;
			$prev[$globalActionName] = 1;
		}
		$post[$globalActionName] = $checked;
		Settings_GlobalPermission_Record_Model::save($profileID, $globalactionid, $checked);
		Settings_Vtiger_Tracker_Model::addDetail($prev, $post);
		$response = new Vtiger_Response();
		$response->setResult(array('success' => true, 'message' => vtranslate('LBL_SAVE_OK', $request->getModule(false))));
		$response->emit();
	}
}
