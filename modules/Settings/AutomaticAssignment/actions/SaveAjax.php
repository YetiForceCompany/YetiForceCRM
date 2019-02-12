<?php

/**
 * Automatic assignment save action model class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Settings_AutomaticAssignment_SaveAjax_Action extends Settings_Vtiger_Save_Action
{
	/**
	 * Constructor.
	 */
	public function __construct()
	{
		Settings_Vtiger_Tracker_Model::lockTracking();
		parent::__construct();
		$this->exposeMethod('save');
		$this->exposeMethod('deleteElement');
		$this->exposeMethod('changeRoleType');
	}

	/**
	 * Save.
	 *
	 * @param \App\Request $request
	 */
	public function save(\App\Request $request)
	{
		$data = $request->getMultiDimensionArray('param', [
			'tabid' => 'Integer',
			'field' => 'Alnum',
			'roleid' => 'Alnum',
			'value' => 'Text',
			'roles' => ['Alnum'],
			'smowners' => ['Integer'],
			'assign' => 'Integer',
			'conditions' => 'Text',
			'user_limit' => 'Integer',
			'active' => 'Integer'
		]);
		if ($request->isEmpty('record')) {
			$recordModel = Settings_AutomaticAssignment_Record_Model::getCleanInstance();
		} else {
			$recordModel = Settings_AutomaticAssignment_Record_Model::getInstanceById($request->getInteger('record'));
		}

		$dataFull = array_merge($recordModel->getData(), $data);
		$recordModel->setData($dataFull);
		$recordModel->checkDuplicate = true;
		$recordModel->save();

		$responceToEmit = new Vtiger_Response();
		$responceToEmit->setResult($recordModel->getId());
		$responceToEmit->emit();
	}

	/**
	 * Function changes the type of a given role.
	 *
	 * @param \App\Request $request
	 */
	public function changeRoleType(\App\Request $request)
	{
		$member = $request->getByType('param', 'Text');
		$recordId = $request->getInteger('record');
		if ($recordId) {
			$recordModel = Settings_AutomaticAssignment_Record_Model::getInstanceById($recordId);
		} else {
			$recordModel = Settings_AutomaticAssignment_Record_Model::getCleanInstance();
		}
		$recordModel->changeRoleType($member);

		$responceToEmit = new Vtiger_Response();
		$responceToEmit->setResult($recordModel->getId());
		$responceToEmit->emit();
	}

	/**
	 * Function removes given value from record.
	 *
	 * @param \App\Request $request
	 */
	public function deleteElement(\App\Request $request)
	{
		$recordId = $request->getInteger('record');
		$recordModel = Settings_AutomaticAssignment_Record_Model::getInstanceById($recordId);
		$recordModel->deleteElement($request->getByType('name'), $request->getByType('value', 'Text'));

		$responceToEmit = new Vtiger_Response();
		$responceToEmit->setResult($recordModel->getId());
		$responceToEmit->emit();
	}
}
