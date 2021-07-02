<?php

/**
 * Fields Action Class.
 *
 * @package Action
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 4.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Vtiger_Fields_Action extends \App\Controller\Action
{
	use \App\Controller\ExposeMethod;

	/**
	 * Field model instance.
	 *
	 * @var Vtiger_Field_Model
	 */
	protected $fieldModel;

	/**
	 * Function to check permission.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\NoPermitted
	 */
	public function checkPermission(App\Request $request)
	{
		if ('verifyPhoneNumber' !== $request->getMode()) {
			$currentUserPriviligesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
			if (!$currentUserPriviligesModel->hasModulePermission($request->getModule())) {
				throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
			}
			if (!\App\Privilege::isPermitted($request->getModule(), 'EditView')) {
				throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
			}
		}
		if ('findAddress' !== $request->getMode() && 'getReference' !== $request->getMode()) {
			$this->fieldModel = Vtiger_Module_Model::getInstance($request->getModule())->getFieldByName($request->getByType('fieldName', 2));
			if (!$this->fieldModel || !$this->fieldModel->isEditable()) {
				throw new \App\Exceptions\NoPermitted('ERR_NO_PERMISSIONS_TO_FIELD', 406);
			}
		}
	}

	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('getOwners');
		$this->exposeMethod('getReference');
		$this->exposeMethod('getUserRole');
		$this->exposeMethod('verifyPhoneNumber');
		$this->exposeMethod('findAddress');
		$this->exposeMethod('verifyIsHolidayDate');
		$this->exposeMethod('changeFavoriteOwner');
	}

	/**
	 * Get owners for ajax owners list.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\NoPermitted
	 */
	public function getOwners(App\Request $request)
	{
		if (!App\Config::performance('SEARCH_OWNERS_BY_AJAX')) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
		if ('owner' !== $this->fieldModel->getFieldDataType() && 'sharedOwner' !== $this->fieldModel->getFieldDataType()) {
			throw new \App\Exceptions\NoPermitted('ERR_NO_PERMISSIONS_TO_FIELD');
		}
		$moduleName = $request->getModule();
		$searchValue = $request->getByType('value', 'Text');
		if ($request->has('result')) {
			$result = $request->getArray('result', 'Standard');
		} else {
			$result = ['users', 'groups'];
		}
		$response = new Vtiger_Response();
		if (empty($searchValue)) {
			$response->setResult(['items' => []]);
		} else {
			$owner = App\Fields\Owner::getInstance($moduleName);
			$owner->find($searchValue);

			$data = [];
			if (\in_array('users', $result)) {
				$users = $owner->getAccessibleUsers('', 'owner');
				if (!empty($users)) {
					$data[] = ['name' => \App\Language::translate('LBL_USERS'), 'type' => 'optgroup'];
					foreach ($users as $key => $value) {
						$data[] = ['id' => $key, 'name' => $value];
					}
				}
			}
			if (\in_array('groups', $result)) {
				$grup = $owner->getAccessibleGroups('', 'owner', true);
				if (!empty($grup)) {
					$data[] = ['name' => \App\Language::translate('LBL_GROUPS'), 'type' => 'optgroup'];
					foreach ($grup as $key => $value) {
						$data[] = ['id' => $key, 'name' => $value];
					}
				}
			}
			$response->setResult(['items' => $data]);
		}
		$response->emit();
	}

	/**
	 * Search user roles.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\NoPermitted
	 */
	public function getUserRole(App\Request $request)
	{
		if (!App\Config::performance('SEARCH_ROLES_BY_AJAX')) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
		if ('userRole' !== $this->fieldModel->getFieldDataType()) {
			throw new \App\Exceptions\NoPermitted('ERR_NO_PERMISSIONS_TO_FIELD');
		}
		$searchValue = $request->getByType('value', 'Text');
		$response = new Vtiger_Response();
		if (empty($searchValue)) {
			$response->setResult(['items' => []]);
		} else {
			$rows = $this->fieldModel->getUITypeModel()->getSearchValues($searchValue);
			foreach ($rows as $key => $value) {
				$data[] = ['id' => $key, 'name' => $value];
			}
			$response->setResult(['items' => $data]);
		}
		$response->emit();
	}

	/**
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\NoPermitted
	 */
	public function getReference(App\Request $request)
	{
		$fieldModel = Vtiger_Module_Model::getInstance($request->getModule())->getFieldByName($request->getByType('fieldName', 2));
		if (!$fieldModel || !$fieldModel->isActiveField() || !$fieldModel->isViewEnabled()) {
			throw new \App\Exceptions\NoPermitted('ERR_NO_PERMISSIONS_TO_FIELD', 406);
		}
		$response = new Vtiger_Response();
		$limit = \App\Config::search('GLOBAL_SEARCH_AUTOCOMPLETE_LIMIT');
		$searchValue = \App\RecordSearch::getSearchField()->getUITypeModel()->getDbConditionBuilderValue($request->getByType('value', \App\Purifier::TEXT), '');
		$rows = (new \App\RecordSearch($searchValue, $fieldModel->getReferenceList(), $limit))->setMode(\App\RecordSearch::LABEL_MODE)->search();
		$data = $modules = [];
		foreach ($rows as $row) {
			$modules[$row['setype']][] = $row;
		}
		foreach ($modules as $moduleName => $rows) {
			$data[] = ['name' => App\Language::translateSingleMod($moduleName, $moduleName), 'type' => 'optgroup'];
			foreach ($rows as $row) {
				$data[] = ['id' => $row['crmid'], 'name' => \App\Purifier::encodeHtml($row['label'])];
			}
		}
		$response->setResult(['items' => $data]);
		$response->emit();
	}

	/**
	 * Verify phone number.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\NoPermitted
	 */
	public function verifyPhoneNumber(App\Request $request)
	{
		if ('phone' !== $this->fieldModel->getFieldDataType()) {
			throw new \App\Exceptions\NoPermitted('ERR_NO_PERMISSIONS_TO_FIELD');
		}
		$response = new Vtiger_Response();
		$data = ['isValidNumber' => false];
		if ($request->isEmpty('phoneCountry', true)) {
			$data['message'] = \App\Language::translate('LBL_NO_PHONE_COUNTRY');
		}
		if (empty($data['message'])) {
			try {
				$data = App\Fields\Phone::verifyNumber($request->getByType('phoneNumber', 'Text'), $request->getByType('phoneCountry', 1));
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

	/**
	 * Find address.
	 *
	 * @param \App\Request $request
	 */
	public function findAddress(App\Request $request)
	{
		$instance = \App\Map\Address::getInstance($request->getByType('type'));
		$response = new Vtiger_Response();
		if ($instance) {
			$response->setResult($instance->find($request->getByType('value', 'Text')));
		}
		$response->emit();
	}

	/**
	 * Verify is holiday date.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\NoPermitted
	 */
	public function verifyIsHolidayDate(App\Request $request)
	{
		if ('datetime' === $this->fieldModel->getFieldDataType() || 'date' === $this->fieldModel->getFieldDataType()) {
			$response = new Vtiger_Response();
			$result = false;
			if ($request->isEmpty('date', true)) {
				$data['message'] = \App\Language::translate('LBL_NO_DATE');
			} else {
				$date = $request->getArray('date', 'DateInUserFormat');
				if (!empty(App\Fields\Date::getHolidays(App\Fields\Date::formatToDB($date[0]), App\Fields\Date::formatToDB($date[1])))) {
					$result = true;
				}
			}
			$data = ['isHolidayDate' => $result];
			$response->setResult($data);
			$response->emit();
		} else {
			throw new \App\Exceptions\NoPermitted('ERR_NO_PERMISSIONS_TO_FIELD');
		}
	}

	/**
	 * Change favorite owner state.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\IllegalValue
	 * @throws \App\Exceptions\NoPermitted
	 * @throws \yii\db\Exception
	 */
	public function changeFavoriteOwner(App\Request $request)
	{
		if (!App\Config::module('Users', 'FAVORITE_OWNERS') || (\App\User::getCurrentUserRealId() !== \App\User::getCurrentUserId())) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
		$moduleName = $request->getModule();
		$ownerField = \App\Fields\Owner::getInstance($moduleName);
		$result = $ownerField->changeFavorites($this->fieldModel->getFieldDataType(), $request->getInteger('owner'));
		$message = $result ? 'LBL_MODIFICATION_SUCCESSFUL_AND_RELOAD' : 'LBL_MODIFICATION_FAILURE';
		$message = \App\Language::translate($this->fieldModel->getFieldLabel(), $moduleName) . ': ' . \App\Language::translate($message, $moduleName);
		$response = new Vtiger_Response();
		$response->setResult(['result' => $result, 'message' => $message]);
		$response->emit();
	}
}
