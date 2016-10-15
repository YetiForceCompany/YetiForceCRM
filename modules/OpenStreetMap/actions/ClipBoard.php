<?php

/**
 * Action to clipboard
 * @package YetiForce.Action
 * @license licenses/License.html
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
class OpenStreetMap_ClipBoard_Action extends Vtiger_BasicAjax_Action
{

	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('save');
		$this->exposeMethod('delete');
	}

	public function process(Vtiger_Request $request)
	{
		$mode = $request->getMode();
		if (!empty($mode)) {
			$this->invokeExposedMethod($mode, $request);
			return;
		}
	}

	public function delete(Vtiger_Request $request)
	{
		$coordinatesModel = OpenStreetMap_Coordinate_Model::getInstance();
		$coordinatesModel->deleteCache(Users_Privileges_Model::getCurrentUserModel()->getId(), $request->get('srcModule'));;
		$response = new Vtiger_Response();
		$response->setResult(0);
		$response->emit();
	}

	public function save(Vtiger_Request $request)
	{
		$srcModuleName = $request->get('srcModule');
		$userId = Users_Privileges_Model::getCurrentUserModel()->getId();
		$records = $request->get('recordIds');
		$coordinatesModel = OpenStreetMap_Coordinate_Model::getInstance();
		$coordinatesModel->deleteCache($userId, $srcModuleName);
		$coordinatesModel->saveCache($userId, $srcModuleName, $records);
		$response = new Vtiger_Response();
		$response->setResult(count($records));
		$response->emit();
	}
}
