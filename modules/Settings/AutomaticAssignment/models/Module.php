<?php

/**
 * Automatic assignment module model class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 4.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Settings_AutomaticAssignment_Module_Model extends Settings_Vtiger_Module_Model
{
	/**
	 * Table name.
	 *
	 * @var string
	 */
	public $baseTable = 's_#__auto_assign';
	public $customFieldTable = ['s_#__auto_assign_users' => 'id', 's_#__auto_assign_groups' => 'id', 's_#__auto_assign_roles' => 'id'];

	/**
	 * Table primary key.
	 *
	 * @var string
	 */
	public $baseIndex = 'id';

	/**
	 * List of fields displayed in list view.
	 *
	 * @var string
	 */
	public $listFields = ['tabid' => 'FL_MODULE', 'subject' => 'FL_SUBJECT', 'state' => 'FL_STATE'];

	/**
	 * Module Name.
	 *
	 * @var string
	 */
	public $name = 'AutomaticAssignment';

	/**
	 * List of available field types.
	 *
	 * @var string[]
	 */
	private static $fieldType = ['string'];

	/**
	 * Function to get the url for Create view of the module.
	 *
	 * @return string - url
	 */
	public function getCreateRecordUrl()
	{
		return 'index.php?module=' . $this->getName() . '&parent=Settings&view=Edit';
	}

	/**
	 * Function to get the url for edit view of the module.
	 *
	 * @return string - url
	 */
	public function getEditViewUrl()
	{
		return 'index.php?module=' . $this->getName() . '&parent=Settings&view=Edit';
	}

	/**
	 * Function to get the url for default view of the module.
	 *
	 * @return string URL
	 */
	public function getDefaultUrl()
	{
		return 'index.php?module=' . $this->getName() . '&parent=Settings&view=List';
	}

	/**
	 * Function get supported modules.
	 *
	 * @return array - List of modules
	 */
	public static function getSupportedModules()
	{
		return Vtiger_Module_Model::getAll([0], ['SMSNotifier', 'OSSMailView', 'Dashboard', 'ModComments', 'Notification'], true);
	}

	/**
	 * List of supported module fields.
	 *
	 * @param mixed $moduleName
	 *
	 * @return array
	 */
	public static function getFieldsByModule($moduleName)
	{
		$accessibleFields = [];
		$moduleInstance = Vtiger_Module_Model::getInstance($moduleName);
		foreach ($moduleInstance->getFields() as $fieldName => $fieldObject) {
			if (\in_array($fieldObject->getFieldDataType(), static::$fieldType) && $fieldObject->isActiveField() && 4 !== $fieldObject->getUIType()) {
				$accessibleFields[$fieldObject->getBlockName()][$fieldName] = $fieldObject;
			}
		}
		return $accessibleFields;
	}

	/**
	 * Function verifies if it is possible to sort by given field in list view.
	 *
	 * @param string $fieldName
	 *
	 * @return bool
	 */
	public function isSortByName($fieldName)
	{
		if (\in_array($fieldName, ['value', 'active'])) {
			return true;
		}
		return false;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getListFields(): array
	{
		if (!isset($this->listFieldModels)) {
			$fields = $this->listFields;
			$fieldObjects = [];
			foreach ($fields as $fieldName => $fieldLabel) {
				$fieldObject = new \App\Base(['name' => $fieldName, 'label' => $fieldLabel]);
				if (!$this->isSortByName($fieldName)) {
					$fieldObject->set('sort', true);
				}
				$fieldObjects[$fieldName] = $fieldObject;
			}
			$this->listFieldModels = $fieldObjects;
		}
		return $this->listFieldModels;
	}

	/**
	 * Function searches for record from the Auto assign records panel.
	 *
	 * @param Vtiger_Record_Model $recordModel
	 * @param string              $role
	 *
	 * @return bool|Settings_AutomaticAssignment_Record_Model
	 */
	public function searchRecord(Vtiger_Record_Model $recordModel, $role = '')
	{
		$key = $recordModel->getModuleName() . $role;
		if (\App\Cache::has(__METHOD__, $key)) {
			$data = \App\Cache::get(__METHOD__, $key);
		} else {
			$query = (new \App\Db\Query())
				->select(['field', 'value', 'id'])
				->from($this->baseTable)
				->where(['tabid' => \App\Module::getModuleId($recordModel->getModuleName()), 'active' => 1]);
			if ($role) {
				$query->andWhere(['roleid' => $role]);
			}
			$data = $query->all();
			\App\Cache::save(__METHOD__, $key, $data, \App\Cache::LONG);
		}
		foreach ($data as $row) {
			if (!$recordModel->has($row['field'])) {
				$fieldModel = $recordModel->getModule()->getFieldByName($row['field']);
				$idName = $recordModel->getEntity()->tab_name_index[$fieldModel->getTableName()];
				$value = \vtlib\Functions::getSingleFieldValue($fieldModel->getTableName(), $fieldModel->getColumnName(), $idName, $recordModel->getId());
				$recordModel->set($row['field'], $value);
			}
			if ($row['value'] == $recordModel->get($row['field'])) {
				$autoAssignRecordModel = Settings_AutomaticAssignment_Record_Model::getInstanceById($row['id']);
				$autoAssignRecordModel->sourceRecordModel = $recordModel;

				return $autoAssignRecordModel;
			}
		}
		return false;
	}

	/**
	 * Function clears cache.
	 *
	 * @param array $param
	 */
	public function clearCache($param)
	{
		if (!empty($param) && isset($param['tabid'], $param['roleid'])) {
			$tabId = \App\Module::getModuleName($param['tabid']);
			$cacheKey = $tabId . $param['roleid'];
			\App\Cache::delete(__CLASS__ . '::searchRecord', $cacheKey);
		}
	}

	/**
	 * Execute auto assign.
	 *
	 * @param \Vtiger_Record_Model $recordModel
	 */
	public static function autoAssignExecute(Vtiger_Record_Model $recordModel)
	{
		$moduleInstance = Settings_Vtiger_Module_Model::getInstance('Settings:AutomaticAssignment');
		$autoAssignRecord = $moduleInstance->searchRecord($recordModel);
		if ($autoAssignRecord) {
			$owner = $autoAssignRecord->getAssignUser();
			if ($owner && (int) $owner !== (int) $recordModel->get('assigned_user_id')) {
				$recordModel->set('assigned_user_id', $owner);
				$recordModel->save();
			}
		}
	}

	public $editFields = [
		'tabid', 'subject', 'state', 'workflow', 'handler', 'gui', 'conditions', 'members', 'method', 'default_assign', 'record_limit', 'record_limit_conditions'
	];

	public function getEditableFields()
	{
		return $this->editFields;
	}

	public function getEditViewStructure($recordModel = null)
	{
		$structure = [];
		foreach ($this->editFields as $fieldName) {
			$fieldModel = $this->getFieldInstanceByName($fieldName);
			if ($recordModel && $recordModel->has($fieldName)) {
				$fieldModel->set('fieldvalue', $recordModel->get($fieldName));
			} else {
				$defaultValue = $fieldModel->get('defaultvalue');
				$fieldModel->set('fieldvalue', $defaultValue ?? '');
			}
			$block = $fieldModel->get('blockLabel') ?: '';
			$structure[$block][$fieldName] = $fieldModel;
		}

		return $structure;
	}

	public function getBlockIcon($name)
	{
		$blocks = [
			'BL_BASIC_DATA' => ['icon' => 'yfi-company-detlis'],
			'BL_CONDITIONS' => ['icon' => 'fas fa-filter fa-sm'],
			'BL_ASSIGN_USERS' => ['icon' => 'yfi yfi-users-2'],
			'BL_USER_SELECTION_CONDITIONS' => ['icon' => 'mdi mdi-account-filter-outline'],
		];
		return $blocks[$name]['icon'] ?? '';
	}

	public function getFieldInstanceByName($name)
	{
		switch ($name) {
			case 'subject':
				$params = [
					'name' => $name,
					'label' => 'FL_SUBJECT',
					'uitype' => 1,
					'typeofdata' => 'V~M',
					'maximumlength' => '100',
					'blockLabel' => 'BL_BASIC_DATA',
					'purifyType' => \App\Purifier::TEXT,
					'table' => $this->getBaseTable()
				];
				break;
			case 'workflow':
				$params = [
					'name' => $name,
					'label' => 'FL_WORKFLOW',
					'uitype' => 56,
					'typeofdata' => 'C~O',
					'maximumlength' => '128',
					'blockLabel' => 'BL_BASIC_DATA',
					'purifyType' => \App\Purifier::BOOL,
					'table' => $this->getBaseTable()
				];
				break;
			case 'handler':
				$params = [
					'name' => $name,
					'label' => 'FL_HANDLER',
					'uitype' => 56,
					'typeofdata' => 'C~O',
					'maximumlength' => '128',
					'tooltip' => 'LBL_HANDLER_DESC',
					'blockLabel' => 'BL_BASIC_DATA',
					'purifyType' => \App\Purifier::BOOL,
					'table' => $this->getBaseTable()
				];
				break;
			case 'gui':
				$params = [
					'name' => $name,
					'label' => 'FL_MANUAL',
					'uitype' => 56,
					'typeofdata' => 'C~O',
					'maximumlength' => '128',
					'blockLabel' => 'BL_BASIC_DATA',
					'purifyType' => \App\Purifier::BOOL,
					'table' => $this->getBaseTable()
				];
				break;
			case 'tabid':
				$params = [
					'name' => $name,
					'label' => 'FL_MODULE_NAME',
					'uitype' => 16,
					'typeofdata' => 'I~M',
					'maximumlength' => '9999',
					'blockLabel' => 'BL_BASIC_DATA',
					'purifyType' => \App\Purifier::INTEGER,
					'table' => $this->getBaseTable(),
					'picklistValues' => []
				];
				foreach (\vtlib\Functions::getAllModules(true, false, 0) as $module) {
					$params['picklistValues'][$module['tabid']] = \App\Language::translate($module['name'], $module['name']);
				}
				break;
			case 'state':
				$params = [
					'name' => $name,
					'label' => 'FL_STATE',
					'uitype' => 16,
					'typeofdata' => 'I~M',
					'maximumlength' => '100',
					'defaultvalue' => 1,
					'blockLabel' => 'BL_BASIC_DATA',
					'purifyType' => \App\Purifier::INTEGER,
					'table' => $this->getBaseTable(),
				];
				$params['picklistValues'] = [
					0 => \App\Language::translate('PLL_INACTIVE', $this->getName(true)),
					1 => \App\Language::translate('PLL_ACTIVE', $this->getName(true))
				];
				break;
			case 'method':
				$params = [
					'name' => $name,
					'label' => 'FL_METHOD',
					'uitype' => 16,
					'typeofdata' => 'I~M',
					'maximumlength' => '100',
					'defaultvalue' => 0,
					'blockLabel' => 'BL_USER_SELECTION_CONDITIONS',
					'purifyType' => \App\Purifier::TEXT,
					'table' => $this->getBaseTable(),
				];
				$params['picklistValues'] = [
					0 => \App\Language::translate('PLL_LOAD_BALANCED', $this->getName(true)),
					1 => \App\Language::translate('PLL_ROUND_ROBIN', $this->getName(true))
				];
				break;
			case 'record_limit':
				$params = [
					'name' => $name,
					'label' => 'FL_RECORD_LIMIT',
					'uitype' => 7,
					'typeofdata' => 'I~M',
					'maximumlength' => '99999',
					'defaultvalue' => 0,
					'tooltip' => 'LBL_RECORD_LIMIT_DESC',
					'blockLabel' => 'BL_USER_SELECTION_CONDITIONS',
					'purifyType' => \App\Purifier::INTEGER,
					'table' => $this->getBaseTable(),
				];
				break;
			case 'conditions':
				$params = [
					'name' => $name,
					'label' => 'FL_RECORD_CONDITIONS',
					'uitype' => 21,
					'typeofdata' => 'V~O',
					'maximumlength' => '65535',
					'hideLabel' => true,
					'blockLabel' => 'BL_CONDITIONS',
					'purifyType' => \App\Purifier::TEXT,
					'table' => $this->getBaseTable(),
				];
				break;
			case 'record_limit_conditions':
				$params = [
					'name' => $name,
					'label' => 'FL_CRITERIA_FOR_COUNTING_RECORDS',
					'uitype' => 21,
					'typeofdata' => 'V~O',
					'maximumlength' => '65535',
					'blockLabel' => 'BL_USER_SELECTION_CONDITIONS',
					'purifyType' => \App\Purifier::TEXT,
					'table' => $this->getBaseTable(),
				];
				break;
			case 'members':
				$params = [
					'name' => $name,
					'label' => 'FL_MEMBERS',
					'uitype' => 33,
					'typeofdata' => 'V~M',
					'maximumlength' => '65535',
					'blockLabel' => 'BL_ASSIGN_USERS',
					'purifyType' => \App\Purifier::TEXT
				];
				$params['picklistValues'] = [];
				break;
			case 'default_assign':
				$params = [
					'name' => $name,
					'label' => 'FL_DEFAULT_ASSIGN',
					'uitype' => 53,
					'typeofdata' => 'I~O',
					'maximumlength' => '-2147483648,2147483647',
					'blockLabel' => 'BL_ASSIGN_USERS',
					'tooltip' => 'LBL_DEFAULT_ASSIGN_DEC',
					'purifyType' => \App\Purifier::INTEGER,
					'table' => $this->getBaseTable(),
					'picklistValues' => []
				];
				break;
			default:
				break;
		}
		return \Vtiger_Field_Model::init($this->getName(true), $params, $name);
	}
}
