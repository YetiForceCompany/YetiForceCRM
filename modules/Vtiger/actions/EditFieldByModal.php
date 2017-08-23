<?php

/**
 * EditFieldByModal Class
 * @package YetiForce.Action
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Vtiger_EditFieldByModal_Action extends Vtiger_Save_Action
{

	/**
	 * Process
	 * @param \App\Request $request
	 * @throws \App\Exceptions\NoPermittedToRecord
	 */
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
		$recordModel->save();

		$response = new Vtiger_Response();
		$response->setResult(['success' => true]);
		$response->emit();
	}
}
