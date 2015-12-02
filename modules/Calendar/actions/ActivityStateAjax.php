<?php
/* {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} */

class Calendar_ActivityStateAjax_Action extends Calendar_SaveAjax_Action
{

	public function process(Vtiger_Request $request)
	{
		$moduleName = $request->getModule();
		$recordId = $request->get('record');
		$state = $request->get('state');

		$recordModel = Vtiger_Record_Model::getInstanceById($recordId);
		$recordModel->set('activitystatus', $state);
		$recordModel->set('mode', 'edit');
		$recordModel->save();

		$response = new Vtiger_Response();
		$response->setResult(['success' => true]);
		$response->emit();
	}
}
