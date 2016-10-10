<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 * *********************************************************************************** */

/**
 * CustomView Record Model Class
 */
class CustomView_Record_Model extends Vtiger_Base_Model
{

	// Constants to identify different status of the custom view
	const CV_STATUS_DEFAULT = 0;
	const CV_STATUS_PRIVATE = 1;
	const CV_STATUS_PENDING = 2;
	const CV_STATUS_PUBLIC = 3;
	const CV_STATUS_SYSTEM = 4;

	protected $isFeatured = false;
	protected $isDefault = false;
	protected $sortOrderBy = false;

	/**
	 * Function to get the Id
	 * @return <Number> Custom View Id
	 */
	public function getId()
	{
		return $this->get('cvid');
	}

	/**
	 * Function to get the Owner Id
	 * @return <Number> Id of the User who created the Custom View
	 */
	public function getOwnerId()
	{
		return $this->get('userid');
	}

	/**
	 * Function to get the Owner Name
	 * @return <String> Custom View creator User Name
	 */
	public function getOwnerName()
	{
		$ownerId = $this->getOwnerId();
		$entityNames = getEntityName('Users', array($ownerId));
		return $entityNames[$ownerId];
	}

	/**
	 * Function to get the Module to which the record belongs
	 * @return Vtiger_Module_Model
	 */
	public function getModule()
	{
		return $this->module;
	}

	/**
	 * Function to set the Module to which the record belongs
	 * @param <String> $moduleName
	 * @return Vtiger_Record_Model or Module Specific Record Model instance
	 */
	public function setModule($moduleName)
	{
		$this->module = Vtiger_Module_Model::getInstance($moduleName);
		return $this;
	}

	/**
	 * Function to set the Module to which the record belongs from the Module model instance
	 * @param <Vtiger_Module_Model> $module
	 * @return Vtiger_Record_Model or Module Specific Record Model instance
	 */
	public function setModuleFromInstance($module)
	{
		$this->module = $module;
		return $this;
	}

	/**
	 * Function to check if the view is marked as default
	 * @return <Boolean> true/false
	 */
	public function isDefault()
	{
		$log = LoggerManager::getInstance();
		$log->debug('Entering ' . __CLASS__ . '::' . __METHOD__ . ' method ...');
		if ($this->isDefault === false) {
			$db = PearDatabase::getInstance();
			$currentUser = Users_Record_Model::getCurrentUserModel();
			$sql = 'SELECT 1 FROM vtiger_user_module_preferences WHERE userid = ? && `tabid` = ? && default_cvid= ? LIMIT 1';
			$result = $db->pquery($sql, ['Users:' . $currentUser->getId(), $this->getModule()->getId(), $this->getId()]);
			$this->isDefault = $result->rowCount();
		}
		$log->debug('Exiting ' . __CLASS__ . '::' . __METHOD__ . ' method ...');
		return $this->isDefault;
	}

	public function isSystem()
	{
		return $this->get('status') == self::CV_STATUS_SYSTEM;
	}

	/**
	 * Function to check if the view is created by the current user or is default view
	 * @return <Boolean> true/false
	 */
	public function isMine()
	{
		$userPrivilegeModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		return ($this->get('status') == self::CV_STATUS_DEFAULT || $this->get('userid') == $userPrivilegeModel->getId());
	}

	/**
	 * Function to check if the view is approved to be Public
	 * @return <Boolean> true/false
	 */
	public function isPublic()
	{
		return (!$this->isMine() && $this->get('status') == self::CV_STATUS_PUBLIC);
	}

	/**
	 * Function to check if the view is marked as Private
	 * @return <Boolean> true/false
	 */
	public function isPrivate()
	{
		return ($this->get('status') == self::CV_STATUS_PRIVATE);
	}

	/**
	 * Function to check if the view is requested to be Public and is awaiting for Approval
	 * @return <Boolean> true/false
	 */
	public function isPending()
	{
		return (!$this->isMine() && $this->get('status') == self::CV_STATUS_PENDING);
	}

	/**
	 * Function to check if the view is created by one of the users, who is below the current user in the role hierarchy
	 * @return <Boolean> true/false
	 */
	public function isOthers()
	{
		return (!$this->isMine() && $this->get('status') != self::CV_STATUS_PUBLIC);
	}

	/**
	 * Function which checks if a view is set to Public by the user which may/may not be approved.
	 * @return <Boolean> true/false
	 */
	public function isSetPublic()
	{
		return ($this->get('status') == self::CV_STATUS_PUBLIC || $this->get('status') == self::CV_STATUS_PENDING);
	}

	public function isFeatured($editView = false)
	{
		$log = LoggerManager::getInstance();
		$log->debug('Entering ' . __CLASS__ . '::' . __METHOD__ . ' method ...');
		if ($this->isFeatured === false) {
			if (empty($editView)) {
				if (!empty($this->get('featured'))) {
					$this->isFeatured = true;
				} else {
					$this->isFeatured = $this->checkPermissionToFeatured();
				}
			} else {
				$this->isFeatured = $this->checkFeaturedInEditView();
			}
		}
		$log->debug('Exiting ' . __CLASS__ . '::' . __METHOD__ . ' method ...');
		return $this->isFeatured;
	}

	public function checkFeaturedInEditView()
	{
		$db = PearDatabase::getInstance();
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$sql = 'SELECT `user` FROM a_yf_featured_filter WHERE `cvid` = ? && `user` = ?';
		$result = $db->pquery($sql, [$this->getId(), 'Users:' . $currentUser->getId()]);
		return (bool) $result->rowCount();
	}

	public function checkPermissionToFeatured($editView = false)
	{
		$db = PearDatabase::getInstance();
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$userId = 'Users:' . $currentUser->getId();
		$roleId = 'Roles:' . $currentUser->getRole();
		$sql = 'SELECT 1 FROM a_yf_featured_filter WHERE (a_yf_featured_filter.user = ? || a_yf_featured_filter.user = ? ';
		$params = [$userId, $roleId];
		if ($currentUser->isAdminUser()) {
			$userGroups = $currentUser->getUserGroups($currentUser->getId());
			$parentRoles = getRoleInformation($currentUser->getRole());
			$parentRoles = $parentRoles['parentrole'] ? $parentRoles['parentrole'] : [];
		} else {
			$parentRoles = $currentUser->getParentRoleSequence();
			$userGroups = $currentUser->get('privileges')->get('groups');
		}
		foreach ($userGroups as $groupId) {
			$sql .= ' || a_yf_featured_filter.user = "Groups:' . $groupId . '"';
		}
		foreach (explode('::', $parentRoles) as $role) {
			$sql .= ' || a_yf_featured_filter.user = "RoleAndSubordinates:' . $role . '"';
		}
		$sql .= ') && a_yf_featured_filter.cvid = ?;';
		$params[] = $this->getId();
		$result = $db->pquery($sql, $params);
		return $result->rowCount();
	}

	public function isEditable()
	{
		if ($this->get('privileges') == 0) {
			return false;
		}
		$currentUser = Users_Record_Model::getCurrentUserModel();
		if ($currentUser->isAdminUser()) {
			return true;
		}

		$moduleModel = $this->getModule();
		$moduleName = $moduleModel->get('name');
		if (!Users_Privileges_Model::isPermitted($moduleName, 'CreateCustomFilter')) {
			return false;
		}

		if ($this->isMine() || $this->isOthers()) {
			return true;
		}
		return false;
	}

	public function isDeletable()
	{
		return $this->isEditable() && $this->get('presence') != 0;
	}

	/**
	 * Function which provides the records for the current view
	 * @param <Boolean> $skipRecords - List of the RecordIds to be skipped
	 * @return <Array> List of RecordsIds
	 */
	public function getRecordIds($skipRecords = false, $module = false, $lockRecords = false)
	{
		$params = [];
		$db = PearDatabase::getInstance();
		$cvId = $this->getId();
		$moduleModel = $this->getModule();
		$moduleName = $moduleModel->get('name');
		$baseTableName = $moduleModel->get('basetable');
		$baseTableId = $moduleModel->get('basetableid');

		$listViewModel = Vtiger_ListView_Model::getInstance($moduleName, $cvId);
		$queryGenerator = $listViewModel->get('query_generator');

		$searchKey = $this->get('search_key');
		$searchValue = $this->get('search_value');
		$operator = $this->get('operator');
		if (!empty($searchValue)) {
			$queryGenerator->addUserSearchConditions(array('search_field' => $searchKey, 'search_text' => $searchValue, 'operator' => $operator));
		}

		$searchParams = $this->get('search_params');
		if (empty($searchParams)) {
			$searchParams = [];
		}
		$transformedSearchParams = Vtiger_Util_Helper::transferListSearchParamsToFilterCondition($searchParams, $moduleModel);
		$glue = '';
		if (count($queryGenerator->getWhereFields()) > 0 && (count($transformedSearchParams)) > 0) {
			$glue = QueryGenerator::$AND;
		}
		$queryGenerator->parseAdvFilterList($transformedSearchParams, $glue);

		$listQuery = $queryGenerator->getQuery();
		if ($module == 'RecycleBin') {
			$listQuery = preg_replace("/vtiger_crmentity.deleted\s*=\s*0/i", 'vtiger_crmentity.deleted = 1', $listQuery);
		}

		if ($skipRecords && !empty($skipRecords) && is_array($skipRecords) && count($skipRecords) > 0) {
			$listQuery .= ' && ' . $baseTableName . '.' . $baseTableId . ' NOT IN (' . implode(',', $skipRecords) . ')';
		}
		if ($lockRecords) {
			$crmEntityModel = Vtiger_CRMEntity::getInstance($moduleName);
			$lockFields = $crmEntityModel->getLockFields();
			if (is_array($lockFields)) {
				foreach ($lockFields as $fieldName => $fieldValues) {
					$listQuery .=' && ' . $baseTableName . '.' . $fieldName . ' NOT IN (' . generateQuestionMarks($fieldValues) . ')';
					$params = array_merge($params, $fieldValues);
				}
			}
		}
		$result = $db->pquery($listQuery, $params);
		$recordIds = [];
		while ($row = $db->getRow($result)) {
			$recordIds[] = $row[$baseTableId];
		}
		return $recordIds;
	}

	/**
	 * Function to save the custom view record
	 */
	public function save()
	{
		$db = PearDatabase::getInstance();
		$currentUserModel = Users_Record_Model::getCurrentUserModel();

		$cvIdOrg = $cvId = $this->getId();
		$setDefault = $this->get('setdefault');
		$status = $this->get('status');
		$featured = $this->get('featured');

		if ($status == self::CV_STATUS_PENDING) {
			if ($currentUserModel->isAdminUser()) {
				$status = self::CV_STATUS_PUBLIC;
				$this->set('status', $status);
			}
		}
		$db->startTransaction();
		if (!$cvId) {
			$cvId = $db->getUniqueID('vtiger_customview');
			$this->set('cvid', $cvId);
			$this->addCustomView();
		} else {
			$this->updateCustomView();
		}

		$userId = 'Users:' . $currentUserModel->getId();
		if (!empty($featured) && empty($cvIdOrg)) {
			Settings_CustomView_Module_Model::setFeaturedFilterView($cvId, $userId, 'add');
		} elseif (empty($featured) && !empty($cvIdOrg)) {
			Settings_CustomView_Module_Model::setFeaturedFilterView($cvId, $userId, 'remove');
		} elseif (!empty($featured)) {
			$result = $db->pquery('SELECT 1 FROM a_yf_featured_filter WHERE a_yf_featured_filter.cvid = ? && a_yf_featured_filter.user = ?;', [$cvId, $userId]);
			if (empty($result->rowCount())) {
				Settings_CustomView_Module_Model::setFeaturedFilterView($cvId, $userId, 'add');
			}
		}
		if (empty($setDefault) && !empty($cvIdOrg)) {
			$db->delete('vtiger_user_module_preferences', 'userid = ? && tabid = ? && default_cvid = ?', [$userId, $this->getModule()->getId(), $cvId]);
		} elseif (!empty($setDefault)) {
			$this->setDefaultFilter();
		}
		$db->completeTransaction();
	}

	/**
	 * Function to delete the custom view record
	 */
	public function delete()
	{
		$db = PearDatabase::getInstance();
		$cvId = $this->getId();

		$db->delete('vtiger_customview', 'cvid = ?', [$cvId]);
		$db->delete('vtiger_cvcolumnlist', 'cvid = ?', [$cvId]);
		$db->delete('vtiger_cvstdfilter', 'cvid = ?', [$cvId]);
		$db->delete('vtiger_cvadvfilter', 'cvid = ?', [$cvId]);
		$db->delete('vtiger_cvadvfilter_grouping', 'cvid = ?', [$cvId]);
		$db->delete('vtiger_user_module_preferences', 'default_cvid = ?', [$cvId]);

		// To Delete the mini list widget associated with the filter 
		$db->delete('vtiger_module_dashboard', 'filterid = ?', [$cvId]);
	}

	/**
	 * Function to delete the custom view record
	 */
	public function setDefaultFilter()
	{
		$db = PearDatabase::getInstance();
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$userId = 'Users:' . $currentUser->getId();
		$tabId = $this->getModule()->getId();
		$db->delete('vtiger_user_module_preferences', 'userid = ? && tabid = ?', [$userId, $tabId]);
		$db->insert('vtiger_user_module_preferences', [
			'userid' => $userId,
			'tabid' => $tabId,
			'default_cvid' => $this->getId()
		]);
	}

	public function setConditionsForFilter()
	{
		$db = PearDatabase::getInstance();
		$moduleModel = $this->getModule();
		$cvId = $this->getId();

		$stdFilterList = $this->get('stdfilterlist');
		if (!empty($stdFilterList) && !empty($stdFilterList['columnname'])) {
			$db->insert('vtiger_cvstdfilter', [
				'cvid' => $cvId,
				'columnname' => $stdFilterList['columnname'],
				'stdfilter' => $stdFilterList['stdfilter'],
				'startdate' => $db->formatDate($stdFilterList['startdate'], true),
				'enddate' => $db->formatDate($stdFilterList['enddate'], true)
			]);
		}

		$advFilterList = $this->get('advfilterlist');
		if (!empty($advFilterList)) {
			foreach ($advFilterList as $groupIndex => $groupInfo) {
				if (empty($groupInfo))
					continue;

				$groupColumns = $groupInfo['columns'];
				$groupCondition = $groupInfo['condition'];

				foreach ($groupColumns as $columnIndex => $columnCondition) {
					if (empty($columnCondition))
						continue;

					$advFilterColumn = $columnCondition['columnname'];
					$advFilterComparator = $columnCondition['comparator'];
					$advFitlerValue = $columnCondition['value'];
					$advFilterColumnCondition = $columnCondition['column_condition'];

					$columnInfo = explode(":", $advFilterColumn);
					$fieldName = $columnInfo[2];
					$fieldModel = $moduleModel->getField($fieldName);
					//Required if Events module fields are selected for the condition
					if (!$fieldModel) {
						$modulename = $moduleModel->get('name');
						if ($modulename == 'Calendar') {
							$eventModuleModel = Vtiger_Module_model::getInstance('Events');
							$fieldModel = $eventModuleModel->getField($fieldName);
						}
					}
					$fieldType = $fieldModel->getFieldDataType();

					if ($fieldType == 'currency') {
						if ($fieldModel->get('uitype') == '72') {
							// Some of the currency fields like Unit Price, Totoal , Sub-total - doesn't need currency conversion during save
							$advFitlerValue = CurrencyField::convertToDBFormat($advFitlerValue, null, true);
						} else {
							$advFitlerValue = CurrencyField::convertToDBFormat($advFitlerValue);
						}
					}

					$temp_val = explode(",", $advFitlerValue);
					if (($fieldType == 'date' || ($fieldType == 'time' && $fieldName != 'time_start' && $fieldName != 'time_end') || ($fieldType == 'datetime')) && ($fieldType != '' && $advFitlerValue != '' )) {
						$val = Array();
						for ($x = 0; $x < count($temp_val); $x++) {
							//if date and time given then we have to convert the date and
							//leave the time as it is, if date only given then temp_time
							//value will be empty
							if (trim($temp_val[$x]) != '') {
								$date = new DateTimeField(trim($temp_val[$x]));
								if ($fieldType == 'date') {
									$val[$x] = DateTimeField::convertToDBFormat(
											trim($temp_val[$x]));
								} elseif ($fieldType == 'datetime') {
									$val[$x] = $date->getDBInsertDateTimeValue();
								} else {
									$val[$x] = $date->getDBInsertTimeValue();
								}
							}
						}
						$advFitlerValue = implode(",", $val);
					}
					if (in_array($advFilterComparator, ['om', 'wr', 'nwr'])) {
						$advFitlerValue = '';
					}
					$db->insert('vtiger_cvadvfilter', [
						'cvid' => $cvId,
						'columnindex' => $columnIndex,
						'columnname' => $advFilterColumn,
						'comparator' => $advFilterComparator,
						'value' => $advFitlerValue,
						'groupid' => $groupIndex,
						'column_condition' => $advFilterColumnCondition
					]);

					// Update the condition expression for the group to which the condition column belongs
					$groupConditionExpression = '';
					if (!empty($advFilterList[$groupIndex]["conditionexpression"])) {
						$groupConditionExpression = $advFilterList[$groupIndex]["conditionexpression"];
					}
					$groupConditionExpression = $groupConditionExpression . ' ' . $columnIndex . ' ' . $advFilterColumnCondition;
					$advFilterList[$groupIndex]["conditionexpression"] = $groupConditionExpression;
				}

				$groupConditionExpression = $advFilterList[$groupIndex]["conditionexpression"];
				if (empty($groupConditionExpression))
					continue; // Case when the group doesn't have any column criteria

				$db->insert('vtiger_cvadvfilter_grouping', [
					'groupid' => $groupIndex,
					'cvid' => $cvId,
					'group_condition' => $groupCondition,
					'condition_expression' => $groupConditionExpression
				]);
			}
		}
	}

	public function setColumnlist()
	{
		$db = PearDatabase::getInstance();
		$cvId = $this->getId();
		foreach ($this->get('columnslist') as $index => $columnName) {
			$db->insert('vtiger_cvcolumnlist', [
				'cvid' => $cvId,
				'columnindex' => $index,
				'columnname' => $columnName
			]);
		}
	}

	/**
	 * Function to add the custom view record in db
	 */
	public function addCustomView()
	{
		$db = PearDatabase::getInstance();
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$moduleName = $this->getModule()->get('name');
		$seq = $this->getNextSeq($moduleName);
		$db->insert('vtiger_customview', [
			'cvid' => $this->getId(),
			'viewname' => $this->get('viewname'),
			'setmetrics' => $this->get('setmetrics'),
			'entitytype' => $moduleName,
			'status' => $this->get('status'),
			'userid' => $currentUser->getId(),
			'sequence' => $seq,
			'featured' => null,
			'color' => $this->get('color'),
			'description' => $this->get('description')
		]);
		$this->setColumnlist();
		$this->setConditionsForFilter();
	}

	public function getNextSeq($moduleName)
	{
		$db = PearDatabase::getInstance();
		$result = $db->pquery('SELECT MAX(sequence) AS max  FROM vtiger_customview WHERE entitytype = ?;', [$moduleName]);
		$id = (int) $db->getSingleValue($result) + 1;
		return $id;
	}

	/**
	 * Function to update the custom view record in db
	 */
	public function updateCustomView()
	{
		$db = PearDatabase::getInstance();
		$cvId = $this->getId();
		$db->update('vtiger_customview', [
			'viewname' => $this->get('viewname'),
			'setmetrics' => $this->get('setmetrics'),
			'status' => $this->get('status'),
			'color' => $this->get('color'),
			'description' => $this->get('description')
			], 'cvid = ?', [$cvId]
		);
		$db->delete('vtiger_cvcolumnlist', 'cvid = ?', [$cvId]);
		$db->delete('vtiger_cvstdfilter', 'cvid = ?', [$cvId]);
		$db->delete('vtiger_cvadvfilter', 'cvid = ?', [$cvId]);
		$db->delete('vtiger_cvadvfilter_grouping', 'cvid = ?', [$cvId]);
		$this->setColumnlist();
		$this->setConditionsForFilter();
	}

	/**
	 * Function to get the list of selected fields for the current custom view
	 * @return <Array> List of Field Column Names
	 */
	public function getSelectedFields()
	{
		$db = PearDatabase::getInstance();

		$query = 'SELECT vtiger_cvcolumnlist.* FROM vtiger_cvcolumnlist
					INNER JOIN vtiger_customview ON vtiger_customview.cvid = vtiger_cvcolumnlist.cvid
				WHERE vtiger_customview.cvid  = ? ORDER BY vtiger_cvcolumnlist.columnindex';
		$params = [$this->getId()];

		$result = $db->pquery($query, $params);
		$noOfFields = $db->num_rows($result);
		$selectedFields = [];
		for ($i = 0; $i < $noOfFields; ++$i) {
			$columnIndex = $db->query_result($result, $i, 'columnindex');
			$columnName = $db->query_result($result, $i, 'columnname');
			$selectedFields[$columnIndex] = $columnName;
		}
		return $selectedFields;
	}

	/**
	 * Function to get the Standard filter condition for the current custom view
	 * @return <Array> Standard filter condition
	 */
	public function getStandardCriteria()
	{
		$db = PearDatabase::getInstance();

		$cvId = $this->getId();
		if (empty($cvId)) {
			return [];
		}

		$query = 'SELECT vtiger_cvstdfilter.* FROM vtiger_cvstdfilter
					INNER JOIN vtiger_customview ON vtiger_customview.cvid = vtiger_cvstdfilter.cvid
				WHERE vtiger_cvstdfilter.cvid = ?';
		$params = array($this->getId());
		$result = $db->pquery($query, $params);
		$stdfilterrow = $db->fetch_array($result);
		if (!empty($stdfilterrow)) {
			$stdfilterlist = [];
			$stdfilterlist["columnname"] = $stdfilterrow["columnname"];
			$stdfilterlist["stdfilter"] = $stdfilterrow["stdfilter"];

			if ($stdfilterrow["stdfilter"] == "custom" || $stdfilterrow["stdfilter"] == "") {
				if ($stdfilterrow["startdate"] != "0000-00-00" && $stdfilterrow["startdate"] != "") {
					$startDateTime = new DateTimeField($stdfilterrow["startdate"] . ' ' . date('H:i:s'));
					$stdfilterlist["startdate"] = $startDateTime->getDisplayDate();
				}
				if ($stdfilterrow["enddate"] != "0000-00-00" && $stdfilterrow["enddate"] != "") {
					$endDateTime = new DateTimeField($stdfilterrow["enddate"] . ' ' . date('H:i:s'));
					$stdfilterlist["enddate"] = $endDateTime->getDisplayDate();
				}
			} else { //if it is not custom get the date according to the selected duration
				$datefilter = self::getDateForStdFilterBytype($stdfilterrow["stdfilter"]);
				$startDateTime = new DateTimeField($datefilter[0] . ' ' . date('H:i:s'));
				$stdfilterlist["startdate"] = $startDateTime->getDisplayDate();
				$endDateTime = new DateTimeField($datefilter[1] . ' ' . date('H:i:s'));
				$stdfilterlist["enddate"] = $endDateTime->getDisplayDate();
			}
		}
		return $stdfilterlist;
	}

	/**
	 * Function to get the list of advanced filter conditions for the current custom view
	 * @return <Array> - All the advanced filter conditions for the custom view, grouped by the condition grouping
	 */
	public function getAdvancedCriteria()
	{
		$db = PearDatabase::getInstance();
		$default_charset = vglobal('default_charset');

		$cvId = $this->getId();
		$advft_criteria = [];
		if (empty($cvId)) {
			return $advft_criteria;
		}

		$sql = 'SELECT * FROM vtiger_cvadvfilter_grouping WHERE cvid = ? ORDER BY groupid';
		$groupsresult = $db->pquery($sql, array($this->getId()));

		$i = 1;
		$j = 0;
		while ($relcriteriagroup = $db->fetch_array($groupsresult)) {
			$groupId = $relcriteriagroup["groupid"];
			$groupCondition = $relcriteriagroup["group_condition"];

			$ssql = 'select vtiger_cvadvfilter.* from vtiger_customview
						inner join vtiger_cvadvfilter on vtiger_cvadvfilter.cvid = vtiger_customview.cvid
						left join vtiger_cvadvfilter_grouping on vtiger_cvadvfilter.cvid = vtiger_cvadvfilter_grouping.cvid
								and vtiger_cvadvfilter.groupid = vtiger_cvadvfilter_grouping.groupid';
			$ssql.= " where vtiger_customview.cvid = ? && vtiger_cvadvfilter.groupid = ? order by vtiger_cvadvfilter.columnindex";

			$result = $db->pquery($ssql, array($this->getId(), $groupId));
			$noOfColumns = $db->num_rows($result);
			if ($noOfColumns <= 0)
				continue;

			while ($relcriteriarow = $db->fetch_array($result)) {
				$criteria = [];
				$criteria['columnname'] = html_entity_decode($relcriteriarow["columnname"], ENT_QUOTES, $default_charset);
				$criteria['comparator'] = $relcriteriarow["comparator"];
				$advfilterval = html_entity_decode($relcriteriarow["value"], ENT_QUOTES, $default_charset);
				$col = explode(":", $relcriteriarow["columnname"]);
				$temp_val = explode(",", $relcriteriarow["value"]);
				if ($col[4] == 'D' || ($col[4] == 'T' && $col[1] != 'time_start' && $col[1] != 'time_end') || ($col[4] == 'DT')) {
					$val = Array();
					for ($x = 0; $x < count($temp_val); $x++) {
						if ($col[4] == 'D') {
							/** while inserting in db for due_date it was taking date and time values also as it is 
							 * date time field. We only need to take date from that value
							 */
							if ($col[0] == 'vtiger_activity' && $col[1] == 'due_date') {
								$originalValue = $temp_val[$x];
								$dateTime = explode(' ', $originalValue);
								$temp_val[$x] = $dateTime[0];
							}
							$date = new DateTimeField(trim($temp_val[$x]));
							$val[$x] = $date->getDisplayDate();
						} elseif ($col[4] == 'DT') {
							$comparator = array('e', 'n', 'b', 'a');
							if (in_array($criteria['comparator'], $comparator)) {
								$originalValue = $temp_val[$x];
								$dateTime = explode(' ', $originalValue);
								$temp_val[$x] = $dateTime[0];
							}
							$date = new DateTimeField(trim($temp_val[$x]));
							$val[$x] = $date->getDisplayDateTimeValue();
						} else {
							$date = new DateTimeField(trim($temp_val[$x]));
							$val[$x] = $date->getDisplayTime();
						}
					}
					$advfilterval = implode(",", $val);
				}
				$criteria['value'] = Vtiger_Util_Helper::toSafeHTML(decode_html($advfilterval));
				$criteria['column_condition'] = $relcriteriarow["column_condition"];

				$groupId = $relcriteriarow['groupid'];
				$advft_criteria[$groupId]['columns'][$j] = $criteria;
				$advft_criteria[$groupId]['condition'] = $groupCondition;
				$j++;
			}
			if (!empty($advft_criteria[$groupId]['columns'][$j - 1]['column_condition'])) {
				$advft_criteria[$groupId]['columns'][$j - 1]['column_condition'] = '';
			}
			$i++;
		}
		// Clear the condition (and/or) for last group, if any.
		if (!empty($advft_criteria[$i - 1]['condition']))
			$advft_criteria[$i - 1]['condition'] = '';
		return $advft_criteria;
	}

	/**
	 * Function returns standard filter sql
	 * @return <String>
	 */
	public function getCVStdFilterSQL()
	{
		$customView = new CustomView();
		return $customView->getCVStdFilterSQL($this->getId());
	}

	/**
	 * Function returns Advanced filter sql
	 * @return <String>
	 */
	public function getCVAdvFilterSQL()
	{
		$customView = new CustomView();
		return $customView->getCVAdvFilterSQL($this->getId());
	}

	/**
	 * Function returns approve url
	 * @return String - approve url
	 */
	public function getCreateUrl()
	{
		return 'index.php?module=CustomView&view=EditAjax&source_module=' . $this->getModule()->get('name');
	}

	/**
	 * Function returns approve url
	 * @return String - approve url
	 */
	public function getEditUrl()
	{
		return 'module=CustomView&view=EditAjax&source_module=' . $this->getModule()->get('name') . '&record=' . $this->getId();
	}

	/**
	 * Function returns approve url
	 * @return String - approve url
	 */
	public function getApproveUrl()
	{
		return 'index.php?module=CustomView&action=Approve&sourceModule=' . $this->getModule()->get('name') . '&record=' . $this->getId();
	}

	/**
	 * Function returns deny url
	 * @return String - deny url
	 */
	public function getDenyUrl()
	{
		return 'index.php?module=CustomView&action=Deny&sourceModule=' . $this->getModule()->get('name') . '&record=' . $this->getId();
	}

	/**
	 * Function returns duplicate url
	 * @return String - duplicate url
	 */
	public function getDuplicateUrl()
	{
		return 'module=CustomView&view=EditAjax&source_module=' . $this->getModule()->get('name') . '&record=' . $this->getId() . '&duplicate=1';
	}

	/**
	 *  Functions returns delete url
	 * @return String - delete url
	 */
	public function getDeleteUrl()
	{
		return 'index.php?module=CustomView&action=Delete&sourceModule=' . $this->getModule()->get('name') . '&record=' . $this->getId();
	}

	public function approve()
	{
		$db = PearDatabase::getInstance();
		$db->pquery('UPDATE vtiger_customview SET status = ? WHERE cvid = ?', array(self::CV_STATUS_PUBLIC, $this->getId()));
	}

	public function deny()
	{
		$db = PearDatabase::getInstance();
		$db->pquery('UPDATE vtiger_customview SET status = ? WHERE cvid = ?', array(self::CV_STATUS_PRIVATE, $this->getId()));
	}

	/**
	 * Function to get the date values for the given type of Standard filter
	 * @param <String> $type
	 * @return <Array> - 2 date values representing the range for the given type of Standard filter
	 */
	protected static function getDateForStdFilterBytype($type)
	{
		return DateTimeRange::getDateRangeByType($type);
	}

	/**
	 * Function to get all the date filter type informations
	 * @return <Array>
	 */
	public static function getDateFilterTypes()
	{
		$dateFilters = Array('custom' => array('label' => 'LBL_CUSTOM'),
			'prevfy' => array('label' => 'LBL_PREVIOUS_FY'),
			'thisfy' => array('label' => 'LBL_CURRENT_FY'),
			'nextfy' => array('label' => 'LBL_NEXT_FY'),
			'prevfq' => array('label' => 'LBL_PREVIOUS_FQ'),
			'thisfq' => array('label' => 'LBL_CURRENT_FQ'),
			'nextfq' => array('label' => 'LBL_NEXT_FQ'),
			'yesterday' => array('label' => 'LBL_YESTERDAY'),
			'today' => array('label' => 'LBL_TODAY'),
			'tomorrow' => array('label' => 'LBL_TOMORROW'),
			'lastweek' => array('label' => 'LBL_LAST_WEEK'),
			'thisweek' => array('label' => 'LBL_CURRENT_WEEK'),
			'nextweek' => array('label' => 'LBL_NEXT_WEEK'),
			'lastmonth' => array('label' => 'LBL_LAST_MONTH'),
			'thismonth' => array('label' => 'LBL_CURRENT_MONTH'),
			'nextmonth' => array('label' => 'LBL_NEXT_MONTH'),
			'last7days' => array('label' => 'LBL_LAST_7_DAYS'),
			'last30days' => array('label' => 'LBL_LAST_30_DAYS'),
			'last60days' => array('label' => 'LBL_LAST_60_DAYS'),
			'last90days' => array('label' => 'LBL_LAST_90_DAYS'),
			'last120days' => array('label' => 'LBL_LAST_120_DAYS'),
			'next30days' => array('label' => 'LBL_NEXT_30_DAYS'),
			'next60days' => array('label' => 'LBL_NEXT_60_DAYS'),
			'next90days' => array('label' => 'LBL_NEXT_90_DAYS'),
			'next120days' => array('label' => 'LBL_NEXT_120_DAYS')
		);

		foreach ($dateFilters as $filterType => $filterDetails) {
			$dateValues = self::getDateForStdFilterBytype($filterType);
			$dateFilters[$filterType]['startdate'] = $dateValues[0];
			$dateFilters[$filterType]['enddate'] = $dateValues[1];
		}
		return $dateFilters;
	}

	/**
	 * Function to get all the supported advanced filter operations
	 * @return <Array>
	 */
	public static function getAdvancedFilterOptions()
	{
		return array(
			'e' => 'LBL_EQUALS',
			'n' => 'LBL_NOT_EQUAL_TO',
			's' => 'LBL_STARTS_WITH',
			'ew' => 'LBL_ENDS_WITH',
			'c' => 'LBL_CONTAINS',
			'k' => 'LBL_DOES_NOT_CONTAIN',
			'l' => 'LBL_LESS_THAN',
			'g' => 'LBL_GREATER_THAN',
			'm' => 'LBL_LESS_THAN_OR_EQUAL',
			'h' => 'LBL_GREATER_OR_EQUAL',
			'b' => 'LBL_BEFORE',
			'a' => 'LBL_AFTER',
			'bw' => 'LBL_BETWEEN',
		);
	}

	/**
	 * Function to get the advanced filter option names by Field type
	 * @return <Array>
	 */
	public static function getAdvancedFilterOpsByFieldType()
	{
		return array(
			'V' => array('e', 'n', 's', 'ew', 'c', 'k'),
			'N' => array('e', 'n', 'l', 'g', 'm', 'h'),
			'T' => array('e', 'n', 'l', 'g', 'm', 'h', 'bw', 'b', 'a'),
			'I' => array('e', 'n', 'l', 'g', 'm', 'h'),
			'C' => array('e', 'n'),
			'D' => array('e', 'n', 'bw', 'b', 'a'),
			'DT' => array('e', 'n', 'bw', 'b', 'a'),
			'NN' => array('e', 'n', 'l', 'g', 'm', 'h'),
			'E' => array('e', 'n', 's', 'ew', 'c', 'k')
		);
	}

	/**
	 * Function to get all the accessible Custom Views, for a given module if specified
	 * @param <String> $moduleName
	 * @return <Array> - Array of Vtiger_CustomView_Record models
	 */
	public static function getAll($moduleName = '')
	{
		$log = LoggerManager::getInstance();
		$log->debug('Entering ' . __CLASS__ . '::' . __METHOD__ . " ($moduleName) method ...");
		$cacheName = 'getAll:' . $moduleName;
		$customViews = Vtiger_Cache::get('CustomViews', $cacheName);
		if ($customViews) {
			return $customViews;
		}
		$db = PearDatabase::getInstance();
		$currentUser = Users_Record_Model::getCurrentUserModel();

		$sql = 'SELECT * FROM vtiger_customview';
		$params = [];

		if (!empty($moduleName)) {
			$sql .= ' WHERE entitytype=?';
			$params[] = $moduleName;
		}
		if (!$currentUser->isAdminUser()) {
			$userParentRoleSeq = $currentUser->getParentRoleSequence();
			$sql .= " && ( vtiger_customview.userid = ? || vtiger_customview.status = 0 || vtiger_customview.status = 3
							OR vtiger_customview.userid IN (
								SELECT vtiger_user2role.userid FROM vtiger_user2role
									INNER JOIN vtiger_users ON vtiger_users.id = vtiger_user2role.userid
									INNER JOIN vtiger_role ON vtiger_role.roleid = vtiger_user2role.roleid
								WHERE vtiger_role.parentrole LIKE '" . $userParentRoleSeq . "::%')
						)";
			$params[] = $currentUser->getId();
		}
		$sql .= ' ORDER BY sequence ASC';

		$result = $db->pquery($sql, $params);
		$customViews = [];
		while ($row = $db->fetch_array($result)) {
			$customView = new self();
			if (strlen(decode_html($row['viewname'])) > 40) {
				$row['viewname'] = substr(decode_html($row['viewname']), 0, 36) . '...';
			}
			$customViews[$row['cvid']] = $customView->setData($row)->setModule($row['entitytype']);
		}

		$filterDir = 'modules' . DIRECTORY_SEPARATOR . $moduleName . DIRECTORY_SEPARATOR . 'filters';
		if ($moduleName && file_exists($filterDir)) {
			$view = ['setdefault' => 0, 'setmetrics' => 0, 'status' => 0, 'privileges' => 0];
			$filters = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($filterDir, FilesystemIterator::SKIP_DOTS));
			foreach ($filters as $filter) {
				$name = str_replace('.php', '', $filter->getFilename());
				$handlerClass = Vtiger_Loader::getComponentClassName('Filter', $name, $moduleName);
				if (class_exists($handlerClass)) {
					$handler = new $handlerClass();
					$view['viewname'] = $handler->getViewName();
					$view['cvid'] = $name;
					$view['status'] = self::CV_STATUS_SYSTEM;
					$customView = new self();
					$customViews[$name] = $customView->setData($view)->setModule($moduleName);
				}
			}
		}
		Vtiger_Cache::set('CustomViews', $cacheName, $customViews);
		$log->debug('Exiting ' . __CLASS__ . '::' . __METHOD__ . ' method ...');
		return $customViews;
	}

	/**
	 * Function to get the instance of Custom View module, given custom view id
	 * @param <Integer> $cvId
	 * @return CustomView_Record_Model instance, if exists. Null otherwise
	 */
	public static function getInstanceById($cvId)
	{
		$db = PearDatabase::getInstance();

		$sql = 'SELECT * FROM vtiger_customview WHERE cvid = ?';
		$params = array($cvId);
		$result = $db->pquery($sql, $params);
		if ($db->num_rows($result) > 0) {
			$row = $db->query_result_rowdata($result, 0);
			$customView = new self();
			return $customView->setData($row)->setModule($row['entitytype']);
		}
		return null;
	}

	/**
	 * Function to get all the custom views, of a given module if specified, grouped by their status
	 * @param <String> $moduleName
	 * @return <Array> - Associative array of Status label to an array of Vtiger_CustomView_Record models
	 */
	public static function getAllByGroup($moduleName = '', $menuId = false)
	{
		$customViews = self::getAll($moduleName);
		$filters = $groupedCustomViews = [];
		$menuFilter = false;
		if ($menuId) {
			$userPrivModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
			$roleMenu = 'user_privileges/menu_' . filter_var($userPrivModel->get('roleid'), FILTER_SANITIZE_NUMBER_INT) . '.php';
			if (file_exists($roleMenu)) {
				require($roleMenu);
			} else {
				require('user_privileges/menu_0.php');
			}
			if (count($menus) == 0) {
				require('user_privileges/menu_0.php');
			}
			if (array_key_exists($menuId, $filterList)) {
				$filters = explode(',', $filterList[$menuId]['filters']);
				$menuFilter = true;
			}
		}
		foreach ($customViews as $index => $customView) {
			if ($menuFilter && !in_array($customView->getId(), $filters)) {
				continue;
			}
			if ($customView->isSystem()) {
				$groupedCustomViews['System'][] = $customView;
			} elseif ($customView->isMine()) {
				$groupedCustomViews['Mine'][] = $customView;
			} elseif ($customView->isPending()) {
				$groupedCustomViews['Pending'][] = $customView;
			} else {
				$groupedCustomViews['Others'][] = $customView;
			}
		}
		return $groupedCustomViews;
	}

	/**
	 * Function to get Clean instance of this record
	 * @return self
	 */
	public static function getCleanInstance()
	{
		return new self();
	}

	/**
	 * function to check duplicates from database
	 * @param <type> $viewName
	 * @param <type> module name entity type in database
	 * @return <boolean> true/false
	 */
	public function checkDuplicate()
	{
		$db = PearDatabase::getInstance();

		$query = "SELECT 1 FROM vtiger_customview WHERE viewname = ? && entitytype = ?";
		$params = array($this->get('viewname'), $this->getModule()->getName());

		$cvid = $this->getId();
		if ($cvid) {
			$query .= " && cvid != ?";
			array_push($params, $cvid);
		}

		$result = $db->pquery($query, $params);
		if ($db->num_rows($result)) {
			return true;
		}
		return false;
	}

	/**
	 * Function used to transform the older filter condition to suit newer filters.
	 * The newer filters have only two groups one with ALL(AND) condition between each
	 * filter and other with ANY(OR) condition, this functions tranforms the older
	 * filter with 'AND' condition between filters of a group and will be placed under
	 * match ALL conditions group and the rest of it will be placed under match Any group.
	 * @return <Array>
	 */
	public function transformToNewAdvancedFilter()
	{
		$standardFilter = $this->transformStandardFilter();
		$advancedFilter = $this->getAdvancedCriteria();
		$allGroupColumns = $anyGroupColumns = [];
		foreach ($advancedFilter as $index => $group) {
			$columns = $group['columns'];
			$and = $or = 0;
			$block = $group['condition'];
			if (count($columns) != 1) {
				foreach ($columns as $column) {
					if ($column['column_condition'] == 'and') {
						++$and;
					} else {
						++$or;
					}
				}
				if ($and == count($columns) - 1 && count($columns) != 1) {
					$allGroupColumns = array_merge($allGroupColumns, $group['columns']);
				} else {
					$anyGroupColumns = array_merge($anyGroupColumns, $group['columns']);
				}
			} else if ($block == 'and' || $index == 1) {
				$allGroupColumns = array_merge($allGroupColumns, $group['columns']);
			} else {
				$anyGroupColumns = array_merge($anyGroupColumns, $group['columns']);
			}
		}
		if ($standardFilter) {
			$allGroupColumns = array_merge($allGroupColumns, $standardFilter);
		}
		$transformedAdvancedCondition = [];
		$transformedAdvancedCondition[1] = array('columns' => $allGroupColumns, 'condition' => 'and');
		$transformedAdvancedCondition[2] = array('columns' => $anyGroupColumns, 'condition' => '');

		return $transformedAdvancedCondition;
	}
	/*
	 *  Function used to tranform the standard filter as like as advanced filter format
	 * 	@returns array of tranformed standard filter
	 */

	public function transformStandardFilter()
	{
		$standardFilter = $this->getStandardCriteria();
		if (!empty($standardFilter)) {
			$tranformedStandardFilter = [];
			$tranformedStandardFilter['comparator'] = 'bw';

			$fields = explode(':', $standardFilter['columnname']);

			if ($fields[1] == 'createdtime' || $fields[1] == 'modifiedtime' || ($fields[0] == 'vtiger_activity' && $fields[1] == 'date_start')) {
				$tranformedStandardFilter['columnname'] = $standardFilter['columnname'] . ':DT';
				$date[] = $standardFilter['startdate'] . ' 00:00:00';
				$date[] = $standardFilter['enddate'] . ' 00:00:00';
				$tranformedStandardFilter['value'] = implode(',', $date);
			} else {
				$tranformedStandardFilter['columnname'] = $standardFilter['columnname'] . ':D';
				$tranformedStandardFilter['value'] = $standardFilter['startdate'] . ',' . $standardFilter['enddate'];
			}
			return array($tranformedStandardFilter);
		} else {
			return false;
		}
	}

	/**
	 * Function gives default custom view for a module
	 * @param <String> $module
	 * @return <CustomView_Record_Model>
	 */
	public static function getAllFilterByModule($module)
	{
		$db = PearDatabase::getInstance();
		$query = "SELECT cvid FROM vtiger_customview WHERE viewname='All' && entitytype = ?";
		$result = $db->pquery($query, array($module));
		$viewId = $db->query_result($result, 0, 'cvid');
		if (!$viewId) {
			$customView = new CustomView($module);
			$viewId = $customView->getViewId($module);
		}
		return self::getInstanceById($viewId);
	}

	protected static $moduleViewIdCache = false;

	public static function getViewId(Vtiger_Request $request)
	{
		if (self::$moduleViewIdCache) {
			return self::$moduleViewIdCache;
		}

		$moduleName = $request->getModule();
		$viewName = $request->get('viewname');
		if (empty($viewName)) {
			//If not view name exits then get it from custom view
			//This can return default view id or view id present in session
			$customView = new CustomView();
			$viewName = $customView->getViewId($moduleName);
		} elseif ($viewName == 'All') {
			$db = PearDatabase::getInstance();
			$result = $db->pquery('SELECT cvid FROM vtiger_customview WHERE presence = 0 && entitytype=?', [$moduleName]);
			$viewName = $db->getSingleValue($result);
		}
		self::$moduleViewIdCache = $viewName;
		return $viewName;
	}

	public function getSortOrderBy($name = '')
	{
		if ($this->sortOrderBy === false) {
			$this->sortOrderBy = explode(',', $this->get('sort'));
		}
		$return = $this->sortOrderBy;
		switch ($name) {
			case 'orderBy':
				$return = $return[0];
				break;
			case 'sortOrder':
				$return = isset($return[1]) ? $return[1] : '';
				break;

			default:
				break;
		}
		return $return;
	}
}
