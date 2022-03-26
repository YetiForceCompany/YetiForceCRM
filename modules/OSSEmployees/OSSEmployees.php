<?php
/**
 * OSSEmployees CRMEntity class.
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
include_once 'modules/Vtiger/CRMEntity.php';

class OSSEmployees extends Vtiger_CRMEntity
{
	public $table_name = 'vtiger_ossemployees';
	public $table_index = 'ossemployeesid';
	public $column_fields = [];

	/** Indicator if this is a custom module or standard module */
	public $IsCustomModule = true;

	/**
	 * Mandatory table for supporting custom fields.
	 */
	public $customFieldTable = ['vtiger_ossemployeescf', 'ossemployeesid'];

	/**
	 * Mandatory for Saving, Include tables related to this module.
	 */
	public $tab_name = ['vtiger_crmentity', 'vtiger_ossemployees', 'vtiger_ossemployeescf', 'vtiger_entity_stats'];

	/**
	 * Mandatory for Saving, Include tablename and tablekey columnname here.
	 */
	public $tab_name_index = [
		'vtiger_crmentity' => 'crmid',
		'vtiger_ossemployees' => 'ossemployeesid',
		'vtiger_ossemployeescf' => 'ossemployeesid',
		'vtiger_entity_stats' => 'crmid', ];

	public $list_fields_name = [
		// Format: Field Label => fieldname
		'LBL_NAME' => 'name',
		'LBL_LASTNAME' => 'last_name',
		'LBL_BUSINESSPHONE' => 'business_phone',
		'LBL_BUSINESSMAIL' => 'business_mail',
		'Assigned To' => 'assigned_user_id',
		'FL_POSITION' => 'position',
	];

	/**
	 * @var string[] List of fields in the RelationListView
	 */
	public $relationFields = [];

	// For Popup listview and UI type support
	public $search_fields = [
		'LBL_LASTNAME' => ['ossemployees', 'last_name'],
		'LBL_NAME' => ['ossemployees', 'name'],
		'LBL_BUSINESSPHONE' => ['ossemployees', 'business_phone'],
		'LBL_BUSINESSMAIL' => ['ossemployees', 'business_mail'],
		'Assigned To' => ['crmentity', 'smownerid'],
		'FL_POSITION' => ['crmentity', 'position'],
	];
	public $search_fields_name = [];
	// For Popup window record selection
	public $popup_fields = ['last_name'];
	// For Alphabetical search
	public $def_basicsearch_col = 'last_name';
	// Column value to use on detail view record text display
	public $def_detailview_recname = 'last_name';
	// Callback function list during Importing
	public $special_functions = ['set_import_assigned_user'];
	public $default_order_by = '';
	public $default_sort_order = 'ASC';
	// Used when enabling/disabling the mandatory fields for the module.
	// Refers to vtiger_field.fieldname values.
	public $mandatory_fields = ['createdtime', 'modifiedtime', 'assigned_user_id'];

	/**
	 * Function to get Employees hierarchy of the given Employees.
	 *
	 * @param int $id - employeeid
	 *                returns Employees hierarchy in array format
	 */
	public function getEmployeeHierarchy($id)
	{
		\App\Log::trace('Entering getEmployeeHierarchy(' . $id . ') method ...');

		$listViewHeader = [];
		$listViewEntries = [];

		foreach ($this->list_fields_name as $fieldName => $colName) {
			if (\App\Field::getFieldPermission('OSSEmployees', $colName)) {
				$listViewHeader[] = \App\Language::translate($fieldName);
			}
		}

		$rowsList = [];
		$encounteredAccounts = [$id];
		$rowsList = $this->__getParentEmployees($id, $rowsList, $encounteredAccounts);
		$rowsList = $this->__getChildEmployees($id, $rowsList, $rowsList[$id]['depth']);
		foreach ($rowsList as $employeesId => $accountInfo) {
			$accountInfoData = [];
			$hasRecordViewAccess = \App\Privilege::isPermitted('OSSEmployees', 'DetailView', $employeesId);
			foreach ($this->list_fields_name as $fieldName => $colName) {
				if (!$hasRecordViewAccess && 'name' != $colName) {
					$accountInfoData[] = '';
				} elseif (\App\Field::getFieldPermission('OSSEmployees', $colName)) {
					$data = \App\Purifier::encodeHtml($accountInfo[$colName]);
					if ('name' == $colName) {
						if ($employeesId != $id) {
							if ($hasRecordViewAccess) {
								$data = '<a href="index.php?module=OSSEmployees&view=Detail&record=' . $employeesId . '">' . $data . '</a>';
							} else {
								$data = '<i>' . $data . '</i>';
							}
						} else {
							$data = '<b>' . $data . '</b>';
						}
						$accountDepth = str_repeat(' .. ', $accountInfo['depth']);
						$data = $accountDepth . $data;
					} elseif ('parentid' == $colName) {
						$fieldModel = Vtiger_Module_Model::getInstance('OSSEmployees')->getFieldByName($fieldName);
						$data = $fieldModel->getDisplayValue($data);
					}
					$accountInfoData[] = $data;
				}
			}
			$listViewEntries[$employeesId] = $accountInfoData;
		}
		$hierarchy = ['header' => $listViewHeader, 'entries' => $listViewEntries];
		\App\Log::trace('Exiting getEmployeeHierarchy method ...');

		return $hierarchy;
	}

	public function __getParentEmployees($id, &$parentAccounts, &$encounteredAccounts)
	{
		$parentId = (new App\Db\Query())
			->select(['parentid'])
			->from('vtiger_ossemployees')
			->innerJoin('vtiger_crmentity', 'vtiger_ossemployees.ossemployeesid = vtiger_crmentity.crmid')
			->where(['vtiger_crmentity.deleted' => 0, 'vtiger_ossemployees.ossemployeesid' => $id])->scalar();
		if (!empty($parentId) && !\in_array($parentId, $encounteredAccounts)) {
			$encounteredAccounts[] = $parentId;
			$this->__getParentEmployees($parentId, $parentAccounts, $encounteredAccounts);
		}
		$userNameSql = App\Module::getSqlForNameInDisplayFormat('Users');
		$data = (new App\Db\Query())
			->select(['vtiger_ossemployees.*', 'user_name' => new \yii\db\Expression('CASE when (vtiger_users.user_name not like ' . App\Db::getInstance()->quoteValue('') . ") THEN $userNameSql ELSE vtiger_groups.groupname END")])
			->from('vtiger_ossemployees')
			->innerJoin('vtiger_crmentity', 'vtiger_ossemployees.ossemployeesid = vtiger_crmentity.crmid')
			->leftJoin('vtiger_groups', 'vtiger_crmentity.smownerid = vtiger_groups.groupid')
			->leftJoin('vtiger_users', 'vtiger_crmentity.smownerid = vtiger_users.id')
			->where(['vtiger_crmentity.deleted' => 0, 'vtiger_ossemployees.ossemployeesid' => $id])
			->one();
		$parentAccountInfo = [];
		$depth = 0;
		$immediateParentId = $data['parentid'];
		if (isset($parentAccounts[$immediateParentId])) {
			$depth = $parentAccounts[$immediateParentId]['depth'] + 1;
		}
		$parentAccountInfo['depth'] = $depth;
		foreach ($this->list_fields_name as $columnName) {
			if ('assigned_user_id' == $columnName) {
				$parentAccountInfo[$columnName] = $data['user_name'];
			} else {
				$parentAccountInfo[$columnName] = $data[$columnName];
			}
		}
		$parentAccounts[$id] = $parentAccountInfo;

		return $parentAccounts;
	}

	public function __getChildEmployees($id, &$childAccounts, $depth)
	{
		$userNameSql = App\Module::getSqlForNameInDisplayFormat('Users');
		$dataReader = (new App\Db\Query())
			->select(['vtiger_ossemployees.*', 'user_name' => new \yii\db\Expression('CASE when (vtiger_users.user_name not like ' . App\Db::getInstance()->quoteValue('') . ") THEN $userNameSql ELSE vtiger_groups.groupname END")])
			->from('vtiger_ossemployees')
			->innerJoin('vtiger_crmentity', 'vtiger_ossemployees.ossemployeesid = vtiger_crmentity.crmid')
			->leftJoin('vtiger_groups', 'vtiger_crmentity.smownerid = vtiger_groups.groupid')
			->leftJoin('vtiger_users', 'vtiger_crmentity.smownerid = vtiger_users.id')
			->where(['vtiger_crmentity.deleted' => 0, 'vtiger_ossemployees.parentid' => $id])->createCommand()->query();
		if ($dataReader->count() > 0) {
			++$depth;
			while ($row = $dataReader->read()) {
				$childAccId = $row['ossemployeesid'];
				if (\array_key_exists($childAccId, $childAccounts)) {
					continue;
				}
				$childAccountInfo = [];
				$childAccountInfo['depth'] = $depth;
				foreach ($this->list_fields_name as $columnName) {
					if ('assigned_user_id' == $columnName) {
						$childAccountInfo[$columnName] = $row['user_name'];
					} else {
						$childAccountInfo[$columnName] = $row[$columnName];
					}
				}
				$childAccounts[$childAccId] = $childAccountInfo;
				$this->__getChildEmployees($childAccId, $childAccounts, $depth);
			}
		}
		return $childAccounts;
	}

	public function moduleHandler($moduleName, $eventType)
	{
		if ('module.postinstall' == $eventType) {
			//block with fields in summary
			$tabId = \App\Module::getModuleId($moduleName);
			\App\Db::getInstance()->createCommand()->update('vtiger_field', ['summaryfield' => 1], ['and', ['tabid' => $tabId],
				['columnname' => ['ossemployees_no', 'employee_status', 'name', 'last_name', 'pesel', 'id_card', 'employee_education', 'parentid', 'business_mail']], ])->execute();
			// block with comments
			$modcommentsModuleInstance = vtlib\Module::getInstance('ModComments');
			if ($modcommentsModuleInstance && file_exists('modules/ModComments/ModComments.php')) {
				include_once 'modules/ModComments/ModComments.php';
				if (class_exists('ModComments')) {
					ModComments::addWidgetTo(['OSSEmployees']);
				}
			}
		}
	}
}
