<?php

/**
 * Fields Action Class
 * @package YetiForce.Action
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Vtiger_Fields_Action extends Vtiger_Action_Controller
{

	/**
	 * Function to check permission
	 * @param \App\Request $request
	 * @throws \App\Exceptions\NoPermitted
	 */
	public function checkPermission(\App\Request $request)
	{
		$currentUserPriviligesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if (!$currentUserPriviligesModel->hasModulePermission($request->getModule())) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED');
		}
	}

	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('getOwners');
		$this->exposeMethod('searchReference');
		$this->exposeMethod('searchValues');
		$this->exposeMethod('verifyPhoneNumber');
	}

	public function process(\App\Request $request)
	{
		$mode = $request->getMode();
		if (!empty($mode)) {
			$this->invokeExposedMethod($mode, $request);
			return;
		}
	}

	public function getOwners(\App\Request $request)
	{
		$searchValue = $request->get('value');
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
					$data[] = ['name' => \App\Language::translate('LBL_USERS'), 'type' => 'optgroup'];
					foreach ($users as $key => &$value) {
						$data[] = ['id' => $key, 'name' => $value];
					}
				}
			}
			if (in_array('groups', $result)) {
				$grup = $owner->getAccessibleGroups('', 'owner', true);
				if (!empty($grup)) {
					$data[] = ['name' => \App\Language::translate('LBL_GROUPS'), 'type' => 'optgroup'];
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
	 * @param \App\Request $request
	 */
	public function searchValues(\App\Request $request)
	{
		$searchValue = $request->get('value');
		$fieldId = $request->getInteger('fld');
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

	public function searchReference(\App\Request $request)
	{
		$fieldId = $request->getInteger('fid');
		$searchValue = $request->get('value');
		$response = new Vtiger_Response();
		if (\App\Field::getFieldPermission($request->getModule(), $fieldId)) {
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
			$response->setResult(['items' => $data]);
		} else {
			$response->setError('NO');
		}
		$response->emit();
	}

	/**
	 * Verify phone number
	 * @param \App\Request $request
	 * @throws \App\Exceptions\NoPermitted
	 */
	public function verifyPhoneNumber(\App\Request $request)
	{
		$response = new Vtiger_Response();
		if (!\App\Field::getFieldPermission($request->getModule(), $request->getByType('fieldName', 1))) {
			throw new \App\Exceptions\NoPermitted('LBL_NO_PERMISSIONS_TO_FIELD');
		}
		$data = ['isValidNumber' => false];
		if ($request->isEmpty('phoneCountry', true)) {
			$data['message'] = \App\Language::translate('LBL_NO_PHONE_COUNTRY');
		}
		if (empty($data['message'])) {
			try {
				$data = App\Fields\Phone::verifyNumber($request->get('phoneNumber'), $request->getByType('phoneCountry', 1));
			} catch (\App\Exceptions\FieldException $e) {
				$data = ['isValidNumber' => false];
			}
		}
		if (!$data['isValidNumber'] && empty($data['message'])) {
			$data['message'] = \App\Language::translate('LBL_INVALID_PHONE_NUMBER');
		}
		$response->setResult($data);
		$response->emit();
	}
}
