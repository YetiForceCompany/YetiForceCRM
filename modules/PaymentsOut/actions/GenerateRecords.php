<?php
/* {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} */

class PaymentsOut_GenerateRecords_Action extends Vtiger_Action_Controller
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
		$paymentsOut = $request->get('paymentsOut');
		foreach ($paymentsOut as $fields) {
			$ossPaymentsOut = CRMEntity::getInstance($moduleName);
			$ossPaymentsOut->column_fields['paymentsname'] = 'Name';
			$ossPaymentsOut->column_fields['paymentsvalue'] = $fields['amount'];
			$ossPaymentsOut->column_fields['paymentscurrency'] = $fields['third_letter_currency_code'];
			$ossPaymentsOut->column_fields['paymentstitle'] = $fields['details']['title'];
			$ossPaymentsOut->column_fields['bank_account'] = $fields['details']['contAccount'];
			$saved = $ossPaymentsOut->save('PaymentsOut');
			if ($saved === false) {
				$bag = true;
			}
		}

		if ($bag)
			$result = ['success' => true, 'return' => vtranslate('MSG_SAVE_OK', $moduleName)];
		else
			$result = ['success' => false, 'return' => vtranslate('MSG_SAVE_ERROR', $moduleName)];

		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}
}
