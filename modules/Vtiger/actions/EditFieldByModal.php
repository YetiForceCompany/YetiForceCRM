<?php

/**
 * EditFieldByModal Class
 * @package YetiForce.Action
 * @license licenses/License.html
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Vtiger_EditFieldByModal_Action extends Vtiger_Save_Action
{

	public function process(Vtiger_Request $request)
	{
		$params = $request->get('param');
		$moduleName = $request->getModule();
		$recordId = $params['record'];
		$state = $params['state'];
		$fieldName = $params['fieldName'];

		$recordModel = Vtiger_Record_Model::getInstanceById($recordId, $moduleName);
		$recordModel->set('id', $recordId);
		$recordModel->set($fieldName, $state);
		$recordModel->save();

		$response = new Vtiger_Response();
		$response->setResult(['success' => true]);
		$response->emit();
	}
}
