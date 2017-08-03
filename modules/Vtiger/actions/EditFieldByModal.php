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

	public function process(\App\Request $request)
	{
		$params = $request->get('param');
		$moduleName = $request->getModule();
		$recordId = $params['record'];
		$state = $params['state'];
		$fieldName = $params['fieldName'];

		$recordModel = Vtiger_Record_Model::getInstanceById($recordId, $moduleName);
		$recordModel->setId($recordId);
		$recordModel->set($fieldName, $state);
		$recordModel->save();

		$response = new Vtiger_Response();
		$response->setResult(['success' => true]);
		$response->emit();
	}
}
