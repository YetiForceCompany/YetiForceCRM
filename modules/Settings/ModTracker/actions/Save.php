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

class Settings_ModTracker_Save_Action extends Settings_Vtiger_Index_Action
{

	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('changeActiveStatus');
	}

	public function changeActiveStatus(Vtiger_Request $request)
	{
		$id = $request->get('id');
		$status = $request->get('status');
		$moduleModel = new Settings_ModTracker_Module_Model();
		$moduleModel->changeActiveStatus($id, $status == 'true' ? 1 : 0 );

		$response = new Vtiger_Response();
		if ($status == 'true') {
			$response->setResult(array('success' => true, 'message' => vtranslate('LBL_TRACK_CHANGES_ENABLED', $request->getModule(false))));
		} else {
			$response->setResult(array('success' => true, 'message' => vtranslate('LBL_TRACK_CHANGES_DISABLE', $request->getModule(false))));
		}
		$response->emit();
	}
}
