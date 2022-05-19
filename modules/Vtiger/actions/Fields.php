<?php

/**
 * Fields Action Class.
 *
 * @package Action
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
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
		$mode = $request->getMode();
		if ('verifyPhoneNumber' !== $mode) {
			$userPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
			if (!$userPrivilegesModel->hasModulePermission($request->getModule())) {
				throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
			}
			if ('getReference' !== $mode && !\App\Privilege::isPermitted($request->getModule(), 'EditView')) {
				throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
			}
		}
		if ('findAddress' !== $mode && 'getReference' !== $mode && 'validateByMode' !== $mode) {
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
		$this->exposeMethod('findAddress');
		$this->exposeMethod('validateForField');
		$this->exposeMethod('validateByMode');
		$this->exposeMethod('verifyPhoneNumber');
		$this->exposeMethod('changeFavoriteOwner');
		$this->exposeMethod('validateFile');
	}

	/**
	 * Get owners for ajax owners list.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\NoPermitted
	 */
	public function getOwners(App\Request $request): void
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
	public function getUserRole(App\Request $request): void
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
	public function getReference(App\Request $request): void
	{
		if ($request->has('fieldName')) {
			$fieldModel = Vtiger_Module_Model::getInstance($request->getModule())->getFieldByName($request->getByType('fieldName', 2));
			if (empty($fieldModel) || !$fieldModel->isActiveField() || !$fieldModel->isViewEnabled()) {
				throw new \App\Exceptions\NoPermitted('ERR_NO_PERMISSIONS_TO_FIELD', 406);
			}
			$searchInModule = $fieldModel->getReferenceList();
		} elseif ($request->has('relationId') && ($relation = \App\Relation::getById($request->getInteger('relationId'))) && $relation['related_modulename'] === $request->getModule()) {
			if (\in_array($relation['related_modulename'], ['getDependentsList', 'getManyToMany', 'getRelatedList'])) {
				$searchInModule = $relation['related_modulename'];
			} else {
				$typeRelationModel = \Vtiger_Relation_Model::getInstanceById($relation['relation_id'])->getTypeRelationModel();
				if (method_exists($typeRelationModel, 'getConfigAdvancedConditionsByColumns')) {
					$searchInModule = $typeRelationModel->getConfigAdvancedConditionsByColumns()['relatedModules'] ?? $relation['related_modulename'];
				} else {
					$searchInModule = $relation['related_modulename'];
				}
			}
		} else {
			throw new \App\Exceptions\NoPermitted('ERR_NO_PERMISSIONS_TO_FIELD', 406);
		}
		$response = new Vtiger_Response();
		$limit = \App\Config::search('GLOBAL_SEARCH_AUTOCOMPLETE_LIMIT');
		$searchValue = \App\RecordSearch::getSearchField()->getUITypeModel()->getDbConditionBuilderValue($request->getByType('value', \App\Purifier::TEXT), '');
		$rows = (new \App\RecordSearch($searchValue, $searchInModule, $limit))->setMode(\App\RecordSearch::LABEL_MODE)->search();
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
	public function verifyPhoneNumber(App\Request $request): void
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
	public function findAddress(App\Request $request): void
	{
		$instance = \App\Map\Address::getInstance($request->getByType('type'));
		$response = new Vtiger_Response();
		if ($instance) {
			$response->setResult($instance->find($request->getByType('value', 'Text')));
		}
		$response->emit();
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
	public function changeFavoriteOwner(App\Request $request): void
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

	/**
	 * Validate the field name and value.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\NoPermitted
	 */
	public function validateForField(App\Request $request): void
	{
		$fieldModel = Vtiger_Module_Model::getInstance($request->getModule())->getFieldByName($request->getByType('fieldName', 2));
		if (!$fieldModel || !$fieldModel->isActiveField() || !$fieldModel->isViewEnabled()) {
			throw new \App\Exceptions\NoPermitted('ERR_NO_PERMISSIONS_TO_FIELD', 406);
		}
		$recordModel = \Vtiger_Record_Model::getCleanInstance($fieldModel->getModuleName());
		$fieldModel->getUITypeModel()->setValueFromRequest($request, $recordModel, 'fieldValue');
		$response = new Vtiger_Response();
		$response->setResult([
			'raw' => $recordModel->get($fieldModel->getName()),
			'display' => $recordModel->getDisplayValue($fieldModel->getName()),
		]);
		$response->emit();
	}

	/**
	 * Validate the value based on the type of purify.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\NoPermitted
	 */
	public function validateByMode(App\Request $request): void
	{
		if ($request->isEmpty('purifyMode') || !$request->has('value')) {
			throw new \App\Exceptions\NoPermitted('ERR_ILLEGAL_VALUE', 406);
		}
		$response = new Vtiger_Response();
		$response->setResult([
			'raw' => $request->getByType('value', $request->getByType('purifyMode')),
		]);
		$response->emit();
	}

	/**
	 * Validate file.
	 *
	 * @param App\Request $request
	 *
	 * @return void
	 */
	public function validateFile(App\Request $request): void
	{
		$validate = false;
		if ($request->has('base64')) {
			$fileInstance = \App\Fields\File::loadFromBase($request->getByType('base64', 'base64'), ['validateAllowedFormat' => 'image']);
			if ($fileInstance && $fileInstance->validate()) {
				$validate = true;
			} else {
				$validateError = $fileInstance->validateError;
			}
		}
		$response = new Vtiger_Response();
		$response->setResult([
			'validate' => $validate,
			'validateError' => $validateError ?? null,
		]);
		$response->emit();
	}
}
