<?php

/**
 * Fields Action Class
 * @package YetiForce.Actions
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Vtiger_Fields_Action extends Vtiger_Action_Controller
{

	function checkPermission(Vtiger_Request $request)
	{
		$moduleName = $request->getModule();
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		$currentUserPriviligesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();

		if (!$currentUserPriviligesModel->hasModulePermission($moduleModel->getId())) {
			throw new \Exception\NoPermitted('LBL_PERMISSION_DENIED');
		}
	}

	function __construct()
	{
		parent::__construct();
		$this->exposeMethod('getOwners');
	}

	function process(Vtiger_Request $request)
	{
		$mode = $request->get('mode');
		if (!empty($mode)) {
			$this->invokeExposedMethod($mode, $request);
			return;
		}
	}

	public function getOwners(Vtiger_Request $request)
	{
		$value = $request->get('value');
		$type = $request->get('type');
		$moduleName = $request->getModule();
		$response = new Vtiger_Response();
		if (empty($value)) {
			$response->setError('NO');
		} else {
			$owner = includes\fields\Owner::getInstance($moduleName);
			$owner->find($value);

			$data = [];
			$users = $owner->getAccessibleUsers('', 'owner');
			if (!empty($users)) {
				$data[] = ['name' => vtranslate('LBL_USERS'), 'type' => 'optgroup'];
				foreach ($users as $key => &$value) {
					if ($type == 'List') {
						$key = $value;
					}
					$data[] = ['id' => $key, 'name' => $value];
				}
			}
			$grup = $owner->getAccessibleGroups('', 'owner', true);
			if (!empty($grup)) {
				$data[] = ['name' => vtranslate('LBL_GROUPS'), 'type' => 'optgroup'];
				foreach ($grup as $key => &$value) {
					if ($type == 'List') {
						$key = $value;
					}
					$data[] = ['id' => $key, 'name' => $value];
				}
			}
			$response->setResult(['items' => $data]);
		}
		$response->emit();
	}
}
