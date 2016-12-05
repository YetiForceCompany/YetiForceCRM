<?php

/**
 * Automatic assignment save action model class
 * @package YetiForce.Settings.Action
 * @license licenses/License.html
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Settings_AutomaticAssignment_SaveAjax_Action extends Settings_Vtiger_Save_Action
{

	/**
	 * Constructor
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
	 * Save
	 * @param Vtiger_Request $request
	 */
	public function save(Vtiger_Request $request)
	{
		$data = $request->get('param');
		$recordId = $request->get('record');
		if ($recordId) {
			$recordModel = Settings_AutomaticAssignment_Record_Model::getInstanceById($recordId);
		} else {
			$recordModel = Settings_AutomaticAssignment_Record_Model::getCleanInstance();
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
	 * Function changes the type of a given role
	 * @param Vtiger_Request $request
	 */
	public function changeRoleType(Vtiger_Request $request)
	{
		$member = $request->get('param');
		$qualifiedModuleName = $request->getModule(false);
		$recordId = $request->get('record');
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
	 * Function removes given value from record
	 * @param Vtiger_Request $request
	 */
	public function deleteElement(Vtiger_Request $request)
	{
		$member = $request->get('param');
		$qualifiedModuleName = $request->getModule(false);
		$recordId = $request->get('record');
		$recordModel = Settings_AutomaticAssignment_Record_Model::getInstanceById($recordId);
		$recordModel->deleteElement($request->get('name'), $request->get('value'));

		$responceToEmit = new Vtiger_Response();
		$responceToEmit->setResult($recordModel->getId());
		$responceToEmit->emit();
	}
}
