<?php
/* {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} */

class PaymentsIn_GenerateRecords_Action extends Vtiger_Action_Controller
{

	public function checkPermission(Vtiger_Request $request)
	{
		$currentUserPriviligesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if (!$currentUserPriviligesModel->hasModuleActionPermission($moduleName, 'Save')) {
			throw new \Exception\AppException(vtranslate($moduleName) . ' ' . vtranslate('LBL_NOT_ACCESSIBLE'));
		}
	}

	public function process(Vtiger_Request $request)
	{
		$moduleName = $request->getModule();
		$bag = false;
		$paymentsIn = $request->get('paymentsIn');
		foreach ($paymentsIn as $fields) {
			$ossPaymentsIn = CRMEntity::getInstance($moduleName);
			$ossPaymentsIn->column_fields['paymentsname'] = 'Name';
			$ossPaymentsIn->column_fields['paymentsvalue'] = $fields['amount'];
			$ossPaymentsIn->column_fields['paymentscurrency'] = $fields['third_letter_currency_code'];
			$ossPaymentsIn->column_fields['paymentstitle'] = $fields['details']['title'];
			$ossPaymentsIn->column_fields['bank_account'] = $fields['details']['contAccount'];
			$saved = $ossPaymentsIn->save('PaymentsIn');
			if ($saved === false) {
				$bag = true;
			}
		}

		if ($bag) {
			$result = ['success' => true, 'return' => vtranslate('MSG_SAVE_OK', $moduleName)];
		} else {
			$result = ['success' => false, 'return' => vtranslate('MSG_SAVE_ERROR', $moduleName)];
		}

		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}
}
