<?php

/**
 * EditFieldByModal Class
 * @package YetiForce.Action
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
class SVendorEnquiries_EditFieldByModal_Action extends Vtiger_EditFieldByModal_Action
{

	public function process(\App\Request $request)
	{
		$params = $request->getArray('param');
		$state = $params['state'];
		$fieldName = $params['fieldName'];

		$recordModel = $this->record ? $this->record : Vtiger_Record_Model::getInstanceById($request->getInteger('record'), $request->getModule());
		if (!$recordModel->getField($fieldName)->isEditable()) {
			throw new \App\Exceptions\NoPermittedToRecord('LBL_NO_PERMISSIONS_FOR_THE_RECORD');
		}
		$recordModel->set($fieldName, $state);
		if (in_array($state, ['PLL_CANCELLED', 'PLL_COMPLETED'])) {
			$currentTime = date('Y-m-d H:i:s');
			$responseTime = strtotime($currentTime) - strtotime($recordModel->get('createdtime'));
			$recordModel->set('response_time', $responseTime / 60 / 60);
			$recordModel->set('closedtime', $currentTime);
		}
		$recordModel->save();

		$response = new Vtiger_Response();
		$response->setResult(['success' => true]);
		$response->emit();
	}
}
