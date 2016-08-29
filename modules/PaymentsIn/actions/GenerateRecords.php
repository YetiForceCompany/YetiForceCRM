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

class PaymentsIn_GenerateRecords_Action extends Vtiger_Action_Controller
{
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
		$paymentsIn = $request->get('paymentsIn');
		foreach ($paymentsIn as $fields) {
			$ossPaymentsIn = new $moduleName();
			$ossPaymentsIn->column_fields['paymentsname'] = 'Name';
			$ossPaymentsIn->column_fields['paymentsvalue'] = $fields['amount'];
			$ossPaymentsIn->column_fields['paymentscurrency'] = $fields['third_letter_currency_code'];
			$ossPaymentsIn->column_fields['paymentstitle'] = $fields['details']['title'];
			$ossPaymentsIn->column_fields['bank_account'] = $fields['details']['contAccount'];
			$saved = $ossPaymentsIn->save('PaymentsIn');
			if ($saved == false) {
				$bag = true;
			}
		}

		if ($bag) {
			$result = array('success' => true, 'return' => vtranslate('MSG_SAVE_OK', $moduleName));
		}
		else {
			$result = array('success' => false, 'return' => vtranslate('MSG_SAVE_ERROR', $moduleName));
		}

		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}
}

