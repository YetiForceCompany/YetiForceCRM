<?php

/**
 * Fields Action Class
 * @package YetiForce.Actions
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
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
		$this->exposeMethod('searchValues');
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
			$owner = App\Fields\Owner::getInstance($moduleName);
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

	/**
	 * Function searches for value data 
	 * @param Vtiger_Request $request
	 */
	public function searchValues(Vtiger_Request $request)
	{
		$searchValue = $request->get('value');
		$fieldId = (int) $request->get('fld');
		$moduleName = $request->getModule();
		$response = new Vtiger_Response();
		if (empty($searchValue)) {
			$response->setError('NO');
		} else {
			if (\App\Field::getFieldPermission($moduleName, $fieldId) || $moduleName === 'Users') {
				$fieldModel = Vtiger_Field_Model::getInstanceFromFieldId($fieldId);
				$rows = $fieldModel->getUITypeModel()->getSearchValues($searchValue);
				foreach ($rows as $key => $value) {
					$data[] = ['id' => $key, 'name' => $value];
				}
				$response->setResult(['items' => $data]);
			} else {
				$response->setError('NO');
			}
		}
		$response->emit();
	}

	public function searchReference(Vtiger_Request $request)
	{
		$fieldId = $request->get('fid');
		$searchValue = $request->get('value');

		$fieldModel = Vtiger_Field_Model::getInstanceFromFieldId($fieldId);
		$reference = $fieldModel->getReferenceList();
		$rows = (new \App\RecordSearch($searchValue, $reference))->search();
		$data = $modules = $ids = [];
		foreach ($rows as &$row) {
			$ids[] = $row['crmid'];
			$modules[$row['setype']][] = $row['crmid'];
		}
		$labels = \App\Record::getLabel($ids);
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
