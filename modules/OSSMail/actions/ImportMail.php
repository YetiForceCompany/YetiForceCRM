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

class OSSMail_ImportMail_Action extends Vtiger_Action_Controller
{

	public function checkPermission(Vtiger_Request $request)
	{
		if (!Users_Privileges_Model::getCurrentUserPrivilegesModel()->hasModulePermission($request->getModule())) {
			throw new \Exception\NoPermitted('LBL_PERMISSION_DENIED');
		}
	}

	public function process(Vtiger_Request $request)
	{
		$scannerModel = Vtiger_Record_Model::getCleanInstance('OSSMailScanner');
		$mailScanMail = $scannerModel->manualScanMail($request->get('params'));
		$return = false;
		if ($mailScanMail['CreatedEmail']) {
			$return = $mailScanMail['CreatedEmail'];
		}
		$response = new Vtiger_Response();
		$response->setResult($return);
		$response->emit();
	}
}
