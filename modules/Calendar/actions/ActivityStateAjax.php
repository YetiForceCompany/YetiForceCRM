<?php

/**
 *
 * @package YetiForce.actions
 * @license licenses/License.html
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Calendar_ActivityStateAjax_Action extends Calendar_SaveAjax_Action
{

	public function process(Vtiger_Request $request)
	{
		$recordId = $request->get('record');
		$state = $request->get('state');

		$recordModel = Vtiger_Record_Model::getInstanceById($recordId);
		$recordModel->set('activitystatus', $state);
		$recordModel->save();

		$response = new Vtiger_Response();
		$response->setResult(['success' => true]);
		$response->emit();
	}
}
