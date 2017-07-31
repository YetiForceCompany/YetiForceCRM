<?php

/**
 * PaymentsOut GenerateRecords action class
 * @package YetiForce.Action
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 */
class PaymentsOut_GenerateRecords_Action extends Vtiger_Action_Controller
{

	public function checkPermission(\App\Request $request)
	{
		$currentUserPriviligesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if (!$currentUserPriviligesModel->hasModuleActionPermission($moduleName, 'Save')) {
			throw new \Exception\AppException(\App\Language::translate($moduleName) . ' ' . \App\Language::translate('LBL_NOT_ACCESSIBLE'));
		}
	}

	public function process(\App\Request $request)
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
			$result = ['success' => true, 'return' => \App\Language::translate('MSG_SAVE_OK', $moduleName)];
		else
			$result = ['success' => false, 'return' => \App\Language::translate('MSG_SAVE_ERROR', $moduleName)];

		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}
}
