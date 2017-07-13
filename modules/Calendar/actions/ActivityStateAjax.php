<?php

/**
 * Calendar ActivityStateAjax action class
 * @package YetiForce.Action
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Calendar_ActivityStateAjax_Action extends Calendar_SaveAjax_Action
{

	public function process(\App\Request $request)
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
