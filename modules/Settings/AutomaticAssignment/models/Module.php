<?php

/**
 * Automatic assignment module model class.
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Settings_AutomaticAssignment_Module_Model extends Settings_Vtiger_Module_Model
{
	/** {@inheritdoc} */
	public $baseTable = 's_#__auto_assign';
	/** {@inheritdoc} */
	public $baseIndex = 'id';

	/** @var array Members table */
	public $customFieldTable = ['s_#__auto_assign_users' => 'id', 's_#__auto_assign_groups' => 'id', 's_#__auto_assign_roles' => 'id'];

	/** {@inheritdoc} */
	public $listFields = ['subject' => 'FL_SUBJECT', 'tabid' => 'FL_MODULE',  'state' => 'FL_STATE'];

	/** {@inheritdoc} */
	public $name = 'AutomaticAssignment';

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
	 * Function verifies if it is possible to sort by given field in list view.
	 *
	 * @param string $fieldName
	 *
	 * @return bool
	 */
	public function isSortByName($fieldName)
	{
		if (\in_array($fieldName, ['tabid', 'state', 'subject'])) {
			return true;
		}
		return false;
	}

	/** {@inheritdoc} */
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

	/** @var string[] Fields name for edit view */
	public $editFields = [
		'tabid', 'subject', 'state', 'workflow', 'handler', 'gui', 'conditions', 'members', 'method', 'default_assign', 'record_limit', 'record_limit_conditions'
	];

	/**
	 * Editable fields.
	 *
	 * @return array
	 */
	public function getEditableFields(): array
	{
		return $this->editFields;
	}

	/**
	 * Get structure fields.
	 *
	 * @param Settings_AutomaticAssignment_Record_Model|null $recordModel
	 *
	 * @return array
	 */
	public function getEditViewStructure($recordModel = null): array
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

	/**
	 * Get block icon.
	 *
	 * @param string $name
	 *
	 * @return string
	 */
	public function getBlockIcon($name): string
	{
		$blocks = [
			'BL_BASIC_DATA' => ['icon' => 'yfi-company-detlis'],
			'BL_CONDITIONS' => ['icon' => 'fas fa-filter fa-sm'],
			'BL_ASSIGN_USERS' => ['icon' => 'yfi yfi-users-2'],
			'BL_USER_SELECTION_CONDITIONS' => ['icon' => 'mdi mdi-account-filter-outline'],
		];
		return $blocks[$name]['icon'] ?? '';
	}

	/**
	 * Get fields instance by name.
	 *
	 * @param string $name
	 *
	 * @return Vtiger_Field_Model
	 */
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
					if (\in_array($module['name'], ['SMSNotifier', 'OSSMailView', 'Dashboard', 'ModComments', 'Notification'])) {
						continue;
					}
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
					\App\AutoAssign::METHOD_LOAD_BALANCE => \App\Language::translate('PLL_LOAD_BALANCED', $this->getName(true)),
					\App\AutoAssign::METHOD_ROUND_ROBIN => \App\Language::translate('PLL_ROUND_ROBIN', $this->getName(true))
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
