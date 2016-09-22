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
		$db = PearDatabase::getInstance();
		$srcModuleName = $request->get('srcModule');
		$currentUser = Users_Privileges_Model::getCurrentUserModel();
		$userId = $currentUser->getId();
		$db->delete('u_yf_openstreetmap_cache', '`user_id` = ? AND module_name = ?', [$userId, $srcModuleName]);
		$response = new Vtiger_Response();
		$response->setResult(0);
		$response->emit();
	}

	public function save(Vtiger_Request $request)
	{
		$db = PearDatabase::getInstance();
		$srcModuleName = $request->get('srcModule');
		$currentUser = Users_Privileges_Model::getCurrentUserModel();
		$userId = $currentUser->getId();
		$records = $request->get('recordIds');
		$db->delete('u_yf_openstreetmap_cache', '`user_id` = ? AND module_name = ?', [$userId, $srcModuleName]);
		$query = 'INSERT INTO `u_yf_openstreetmap_cache` SET `user_id` = ?, module_name = ?, crmids = ?';
		foreach ($records as $record) {
			$params = [$userId, $srcModuleName, $record];
			$db->pquery($query, $params);
		}
		$response = new Vtiger_Response();
		$response->setResult(count($records));
		$response->emit();
	}
}
