<?php

/**
 * Admin access module model class.
 *
 * @package Settings.Model
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
/**
 * Settings_AdminAccess_Module_Model class.
 */
class Settings_AdminAccess_Module_Model extends Settings_Vtiger_Module_Model
{
	/** {@inheritdoc} */
	public $name = 'AdminAccess';

	/** {@inheritdoc} */
	public $baseTable = 'a_#__settings_modules';

	/** {@inheritdoc} */
	public $baseIndex = 'id';

	/** {@inheritdoc} */
	public $listFields = [
		'name' => 'FL_MODULE_NAME',
		'user' => 'FL_USER',
		'status' => 'FL_ACTIVE'
	];

	/** {@inheritdoc} */
	public function getListFields(): array
	{
		$fields = [];
		foreach (array_keys($this->listFields) as $fieldName) {
			$fields[$fieldName] = $this->getFieldInstanceByName($fieldName);
		}
		return $fields;
	}

	/**
	 * Function returns list of fields available in edit view.
	 *
	 * @return \Vtiger_Field_Model[]
	 */
	public function getEditFields(): array
	{
		$fields = [];
		foreach (array_keys($this->listFields) as $fieldName) {
			$fields[$fieldName] = $this->getFieldInstanceByName($fieldName, true);
		}
		return $fields;
	}

	/**
	 * Function gives list fields for save.
	 *
	 * @return array
	 */
	public function getFieldsForSave(): array
	{
		return ['user', 'status'];
	}

	/**
	 * Get fields for config form.
	 *
	 * @param string $moduleName
	 *
	 * @return Vtiger_Field_Model[]
	 */
	public static function getFields(string $moduleName): array
	{
		$config = App\Config::security();
		$fields = [
			'askAdminAboutVisitPurpose' => [
				'purifyType' => 'bool',
				'uitype' => 56,
				'label' => 'LBL_LOGIN_ADMIN_VISIT_PURPOSE',
				'labelDesc' => 'LBL_LOGIN_ADMIN_VISIT_PURPOSE_DESC',
				'fieldvalue' => $config['askAdminAboutVisitPurpose'] ?? ''
			],
			'askAdminAboutVisitSwitchUsers' => [
				'purifyType' => 'bool',
				'uitype' => 56,
				'label' => 'LBL_LOGIN_ADMIN_VISIT_SWITCH_USERS',
				'labelDesc' => 'LBL_LOGIN_ADMIN_VISIT_SWITCH_USERS_DESC',
				'fieldvalue' => $config['askAdminAboutVisitSwitchUsers'] ?? ''
			],
		];
		foreach ($fields as $key => $value) {
			$fields[$key] = \Vtiger_Field_Model::init($moduleName, $value, $key);
		}
		return $fields;
	}

	/**
	 * Gets field instance by name.
	 *
	 * @param string $name
	 * @param bool   $edit
	 *
	 * @return \Vtiger_Field_Model
	 */
	public function getFieldInstanceByName($name, $edit = false)
	{
		if (!isset($this->fields[$name])) {
			$moduleName = $this->getName(true);
			$params = ['column' => $name, 'name' => $name, 'label' => $this->listFields[$name] ?? '', 'displaytype' => 1, 'typeofdata' => 'V~M', 'presence' => '', 'isEditableReadOnly' => false, 'maximumlength' => '255', 'sort' => true];
			switch ($name) {
				case 'name':
					$params['uitype'] = 16;
					$params['table'] = $this->getBaseTable();
					$params['picklistValues'] = [];
					$modules = (new \App\Db\Query())->from($this->getBaseTable())->select(['name'])->column();
					foreach ($modules as $module) {
						$params['picklistValues'][$module] = \App\Language::translate($module, "Settings:{$module}");
					}
					break;
				case 'status':
					$params['uitype'] = 56;
					$params['typeofdata'] = 'C~O';
					$params['table'] = $this->getBaseTable();
					break;
				case 'user':
					$params['uitype'] = 33;
					$params['typeofdata'] = 'V~O';
					$params['sort'] = 'false';
					$params['table'] = \App\Security\AdminAccess::ACCESS_TABLE_NAME;
					$params['picklistValues'] = [];
					$users = $edit ? $this->getUsers() : (new \App\Db\Query())->from($params['table'])->select([$name])->column();
					foreach ($users as $userId) {
						$params['picklistValues'][$userId] = \App\Fields\Owner::getUserLabel($userId);
					}
					break;
				case 'datetime':
					$params['uitype'] = 80;
					$params['label'] = 'FL_DATE_TIME';
					$params['typeofdata'] = 'DT~O';
					$params['table'] = 'l_#__users_login_purpose';
					break;
				case 'purpose':
					$params['uitype'] = 300;
					$params['label'] = 'FL_PURPOSE';
					$params['typeofdata'] = 'V~O';
					$params['table'] = 'l_#__users_login_purpose';
					break;
				case 'baseid':
				case 'userid':
					$params['label'] = 'baseid' === $name ? 'FL_BASE_USER' : 'FL_USER';
					$params['uitype'] = 16;
					$params['typeofdata'] = 'V~O';
					$params['sort'] = 'false';
					$params['table'] = 'l_#__users_login_purpose';
					$params['picklistValues'] = [];
					$users = (new \App\Db\Query())->from($params['table'])->select([$name])->column();
					foreach ($users as $userId) {
						$params['picklistValues'][$userId] = \App\Fields\Owner::getUserLabel($userId);
					}
					break;
				default: break;
			}
			$this->fields[$name] = \Vtiger_Field_Model::init($moduleName, $params, $name);
		}
		return $this->fields[$name];
	}

	/**
	 * Gets value from request.
	 *
	 * @param string      $fieldName
	 * @param App\Request $request
	 *
	 * @return mixed
	 */
	public function getValueFromRequest(string $fieldName, App\Request $request)
	{
		switch ($fieldName) {
			case 'name':
				$value = $request->getArray($fieldName, \App\Purifier::ALNUM);
				break;
			case 'status':
				$value = $request->getInteger($fieldName);
				break;
			case 'baseid':
			case 'userid':
			case 'user':
				$value = $request->getArray($fieldName, \App\Purifier::INTEGER);
				break;
			case 'datetime':
				$value = $this->getFieldInstanceByName($fieldName)->getUITypeModel()->getDbConditionBuilderValue($request->getByType($fieldName, 'Text'), 'bw');
				break;
			case 'purpose':
				$value = $this->getFieldInstanceByName($fieldName)->getUITypeModel()->getDbConditionBuilderValue($request->getByType($fieldName, 'Text'), 'c');
				break;
			default: break;
		}
		return $value;
	}

	/**
	 * Gets users.
	 *
	 * @return int[]
	 */
	public function getUsers(): array
	{
		return (new \App\QueryGenerator('Users'))->setFields(['id'])
			->addCondition('is_admin', 'on', 'n')->createQuery()->column();
	}

	/**
	 * Edit view URL.
	 *
	 * @param int $id
	 *
	 * @return string
	 */
	public function getEditViewUrl(int $id = null): string
	{
		return 'index.php?module=' . $this->getName() . '&parent=Settings&view=Edit' . ($id ? "&id={$id}" : '');
	}

	/**
	 * Function to get the links.
	 *
	 * @return Vtiger_Link_Model[]
	 */
	public function getLinks(): array
	{
		return [Vtiger_Link_Model::getInstanceFromValues([
			'linktype' => 'LISTVIEWBASIC',
			'linklabel' => 'BTN_MASS_EDIT_ACCESS',
			'linkdata' => ['url' => $this->getEditViewUrl()],
			'linkicon' => 'yfi yfi-full-editing-view',
			'linkclass' => 'btn-primary js-show-modal',
			'showLabel' => 1
		])];
	}

	/**
	 * Gets display value.
	 *
	 * @param string $key
	 * @param mixed  $value
	 *
	 * @return mixed
	 */
	public function getDisplayValue(string $key, $value)
	{
		switch ($key) {
			case 'name':
				$value = \App\Language::translate($value, "Settings:{$value}");
				break;
			case 'status':
				$value = \App\Language::translate(1 == $value ? 'LBL_YES' : 'LBL_NO', $this->getName(true));
				break;
			case 'baseid':
			case 'userid':
			case 'user':
				$value = \App\Fields\Owner::getLabel($value);
				$value = \is_array($value) ? implode(', ', $value) : $value;
				break;
			case 'purpose':
				$value = $this->getFieldInstanceByName($key)->getUITypeModel()->getListViewDisplayValue($value);
				break;
			default: break;
		}
		return $value;
	}

	/**
	 * Gets structure.
	 *
	 * @param string $key
	 *
	 * @return array
	 */
	public function getStructure(string $key): array
	{
		$fields = [];
		switch ($key) {
			case 'visitPurpose':
				foreach (['userid', 'datetime', 'purpose', 'baseid'] as $fieldName) {
					$fields[] = $this->getFieldInstanceByName($fieldName);
				}
				break;
			default: break;
		}
		return $fields;
	}

	/**
	 * Function to get Alphabet Search Field.
	 */
	public function getAlphabetSearchField()
	{
		return '';
	}
}
