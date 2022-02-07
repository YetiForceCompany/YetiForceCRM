<?php

/**
 * Action to clipboard.
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
class OpenStreetMap_ClipBoard_Action extends Vtiger_BasicAjax_Action
{
	use \App\Controller\ExposeMethod;

	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('save');
		$this->exposeMethod('delete');
		$this->exposeMethod('addAllRecords');
		$this->exposeMethod('addRecord');
	}

	/**
	 * Function to check permission.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\NoPermitted
	 */
	public function checkPermission(App\Request $request)
	{
		$currentUserPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if (!$currentUserPrivilegesModel->hasModulePermission($request->getModule())) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
		if (!$request->isEmpty('srcModule') && !$currentUserPrivilegesModel->hasModulePermission($request->getByType('srcModule'))) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
		if (!$request->isEmpty('srcModuleName') && !$currentUserPrivilegesModel->hasModulePermission($request->getByType('srcModuleName'))) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
	}

	public function addAllRecords(App\Request $request)
	{
		$coordinatesModel = OpenStreetMap_Coordinate_Model::getInstance();
		$coordinatesModel->set('moduleName', $request->getByType('srcModule'));
		$count = $coordinatesModel->saveAllRecordsToCache();
		$response = new Vtiger_Response();
		$response->setResult(['count' => $count]);
		$response->emit();
	}

	public function delete(App\Request $request)
	{
		$coordinatesModel = OpenStreetMap_Coordinate_Model::getInstance();
		$coordinatesModel->set('moduleName', $request->getByType('srcModule'));
		$coordinatesModel->deleteCache();
		$response = new Vtiger_Response();
		$response->setResult(0);
		$response->emit();
	}

	public function save(App\Request $request)
	{
		$records = $request->getArray('recordIds', 'Integer');
		$coordinatesModel = OpenStreetMap_Coordinate_Model::getInstance();
		$coordinatesModel->set('moduleName', $request->getByType('srcModule'));
		$coordinatesModel->deleteCache();
		$coordinatesModel->saveCache($records);
		$response = new Vtiger_Response();
		$response->setResult(\count($records));
		$response->emit();
	}

	public function addRecord(App\Request $request)
	{
		$record = $request->getInteger('record');
		$srcModuleName = $request->getByType('srcModuleName');
		if (!\App\Privilege::isPermitted($srcModuleName, 'DetailView', $record)) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
		$coordinatesModel = OpenStreetMap_Coordinate_Model::getInstance();
		$coordinatesModel->set('moduleName', $srcModuleName);
		$coordinatesModel->addCache($record);
		$moduleModel = Vtiger_Module_Model::getInstance($srcModuleName);
		$coordinatesModel->set('srcModuleModel', $moduleModel);
		$coordinates = $coordinatesModel->readCoordinatesByRecords([$record]);
		if (empty($coordinates)) {
			$coordinates = \App\Language::translate('ERR_ADDRESS_NOT_FOUND', 'OpenStreetMap');
		}
		$response = new Vtiger_Response();
		$response->setResult($coordinates);
		$response->emit();
	}
}
