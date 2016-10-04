<?php

/**
 * Fields Action Class
 * @package YetiForce.Actions
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Vtiger_Fields_Action extends Vtiger_Action_Controller
{

	public function checkPermission(Vtiger_Request $request)
	{
		$currentUserPriviligesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if (!$currentUserPriviligesModel->hasModulePermission($request->getModule())) {
			throw new \Exception\NoPermitted('LBL_PERMISSION_DENIED');
		}
	}

	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('getOwners');
		$this->exposeMethod('searchReference');
	}

	public function process(Vtiger_Request $request)
	{
		$mode = $request->get('mode');
		if (!empty($mode)) {
			$this->invokeExposedMethod($mode, $request);
			return;
		}
	}

	public function getOwners(Vtiger_Request $request)
	{
		$searchValue = $request->get('value');
		$type = $request->get('type');
		if ($request->has('result')) {
			$result = $request->get('result');
		} else {
			$result = ['users', 'groups'];
		}

		$moduleName = $request->getModule();
		$response = new Vtiger_Response();
		if (empty($searchValue)) {
			$response->setError('NO');
		} else {
			$owner = includes\fields\Owner::getInstance($moduleName);
			$owner->find($searchValue);

			$data = [];
			if (in_array('users', $result)) {
				$users = $owner->getAccessibleUsers('', 'owner');
				if (!empty($users)) {
					$data[] = ['name' => vtranslate('LBL_USERS'), 'type' => 'optgroup'];
					foreach ($users as $key => &$value) {
						$data[] = ['id' => $key, 'name' => $value];
					}
				}
			}
			if (in_array('groups', $result)) {
				$grup = $owner->getAccessibleGroups('', 'owner', true);
				if (!empty($grup)) {
					$data[] = ['name' => vtranslate('LBL_GROUPS'), 'type' => 'optgroup'];
					foreach ($grup as $key => &$value) {
						$data[] = ['id' => $key, 'name' => $value];
					}
				}
			}
			$response->setResult(['items' => $data]);
		}
		$response->emit();
	}

	public function searchReference(Vtiger_Request $request)
	{
		$fieldId = $request->get('fid');
		$searchValue = $request->get('value');

		$fieldModel = Vtiger_Field_Model::getInstanceFromFieldId($fieldId);
		$reference = $fieldModel->getReferenceList();

		$rows = \includes\Record::findCrmidByLabel($searchValue, $reference);
		$data = $modules = $ids = [];
		foreach ($rows as &$row) {
			$ids[] = $row['crmid'];
			$modules[$row['setype']][] = $row['crmid'];
		}
		$labels = \includes\Record::getLabel($ids);
		foreach ($modules as $moduleName => &$rows) {
			$data[] = ['name' => Vtiger_Language_Handler::getTranslatedString($moduleName, $moduleName), 'type' => 'optgroup'];
			foreach ($rows as &$id) {
				$data[] = ['id' => $id, 'name' => $labels[$id]];
			}
		}
		$response = new Vtiger_Response();
		$response->setResult(['items' => $data]);
		$response->emit();
	}
}
