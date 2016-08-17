<?php
/*+***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com.
 * All Rights Reserved.
 *************************************************************************************************************************************/
class PaymentsOut_GenerateRecords_Action extends Vtiger_Action_Controller {
	function checkPermission(Vtiger_Request $request)
	{
		$moduleName = $request->getModule();
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		$currentUserPriviligesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();

		if (!$currentUserPriviligesModel->hasModuleActionPermission($moduleModel->getId(), 'Save')) {
			throw new \Exception\AppException(vtranslate($moduleName) . ' ' . vtranslate('LBL_NOT_ACCESSIBLE'));
		}
	}
	
	public function process(Vtiger_Request $request) {
		$moduleName = $request->getModule();
		vimport('~~modules/' . $moduleName . '/' . $moduleName . '.php');
		$bag = false;
		$paymentsOut = $request->get('paymentsOut');
		foreach($paymentsOut as $fields) {

			$ossPaymentsOut = new $moduleName();
			$ossPaymentsOut->column_fields['paymentsname'] = 'Name';
			$ossPaymentsOut->column_fields['paymentsvalue'] = $fields['amount'];
			$ossPaymentsOut->column_fields['paymentscurrency'] = $fields['third_letter_currency_code'];
			$ossPaymentsOut->column_fields['paymentstitle'] = $fields['details']['title'];
			$ossPaymentsOut->column_fields['bank_account'] = $fields['details']['contAccount'];
			$saved = $ossPaymentsOut->save('PaymentsOut');
			if($saved == false){
				$bag = true;
			}
		}

		if ( $bag )
			$result = array('success'=>true, 'return'=>vtranslate('MSG_SAVE_OK', $moduleName) );
		else
			$result = array('success'=>false, 'return'=>vtranslate('MSG_SAVE_ERROR', $moduleName) );

		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}
}

