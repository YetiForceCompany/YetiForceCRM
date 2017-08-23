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

	/**
	 * Function to check permission
	 * @param \App\Request $request
	 * @throws \App\Exceptions\NoPermittedToRecord
	 */
	public function checkPermission(\App\Request $request)
	{
		parent::checkPermission($request);
		if (!App\Field::getFieldPermission($request->getModule(), 'activitystatus')) {
			throw new \App\Exceptions\NoPermittedToRecord('LBL_PERMISSION_DENIED');
		}
	}

	public function process(\App\Request $request)
	{
		$recordId = $request->getInteger('record');
		$state = $request->get('state');
		$recordModel = $this->record ? $this->record : Vtiger_Record_Model::getInstanceById($recordId);
		$recordModel->set('activitystatus', $state);
		$recordModel->save();

		$response = new Vtiger_Response();
		$response->setResult(['success' => true]);
		$response->emit();
	}
}
