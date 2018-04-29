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
 * CustomView Record Model Class.
 */
class CustomView_Record_Model extends \App\Base
{
	protected $isFeatured = false;
	protected $isDefault = false;
	protected $sortOrderBy = false;

	/**
	 * Function to get the Id.
	 *
	 * @return <Number> Custom View Id
	 */
	public function getId()
	{
		return $this->get('cvid');
	}

	/**
	 * Function to get the Owner Id.
	 *
	 * @return <Number> Id of the User who created the Custom View
	 */
	public function getOwnerId()
	{
		return $this->get('userid');
	}

	/**
	 * Function to get the Owner Name.
	 *
	 * @return string Custom View creator User Name
	 */
	public function getOwnerName()
	{
		return \App\Fields\Owner::getUserLabel($this->getOwnerId());
	}

	/**
	 * Function to get the Module to which the record belongs.
	 *
	 * @return Vtiger_Module_Model
	 */
	public function getModule()
	{
		return $this->module;
	}

	/**
	 * Function to set the Module to which the record belongs.
	 *
	 * @param string $moduleName
	 *
	 * @return Vtiger_Record_Model or Module Specific Record Model instance
	 */
	public function setModule($moduleName)
	{
		$this->module = Vtiger_Module_Model::getInstance($moduleName);

		return $this;
	}

	/**
	 * Function to set the Module to which the record belongs from the Module model instance.
	 *
	 * @param Vtiger_Module_Model $module
	 *
	 * @return Vtiger_Record_Model or Module Specific Record Model instance
	 */
	public function setModuleFromInstance($module)
	{
		$this->module = $module;

		return $this;
	}

	/**
	 * Function to check if the view is marked as default.
	 *
	 * @return bool true/false
	 */
	public function isDefault()
	{
		\App\Log::trace('Entering ' . __METHOD__ . ' method ...');
		if ($this->isDefault === false) {
			$currentUser = Users_Record_Model::getCurrentUserModel();
			$cvId = $this->getId();
			if (!$cvId) {
				$this->isDefault = false;

				return false;
			}
			$this->isDefault = (new App\Db\Query())->from('vtiger_user_module_preferences')
				->where(['userid' => 'Users:' . $currentUser->getId(), 'tabid' => $this->getModule()->getId(), 'default_cvid' => $cvId])
				->exists();
		}
		\App\Log::trace('Exiting ' . __METHOD__ . ' method ...');

		return $this->isDefault;
	}

	public function isSystem()
	{
		return $this->get('status') == App\CustomView::CV_STATUS_SYSTEM;
	}

	/**
	 * Function to check if the view is created by the current user or is default view.
	 *
	 * @return bool true/false
	 */
	public function isMine()
	{
		$userPrivilegeModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();

		return $this->get('status') == App\CustomView::CV_STATUS_DEFAULT || $this->get('userid') == $userPrivilegeModel->getId();
	}

	/**
	 * Function to check if the view is approved to be Public.
	 *
	 * @return bool true/false
	 */
	public function isPublic()
	{
		return !$this->isMine() && $this->get('status') == App\CustomView::CV_STATUS_PUBLIC;
	}

	/**
	 * Function to check if the view is marked as Private.
	 *
	 * @return bool true/false
	 */
	public function isPrivate()
	{
		return $this->get('status') == App\CustomView::CV_STATUS_PRIVATE;
	}

	/**
	 * Function to check if the view is requested to be Public and is awaiting for Approval.
	 *
	 * @return bool true/false
	 */
	public function isPending()
	{
		return !$this->isMine() && $this->get('status') == App\CustomView::CV_STATUS_PENDING;
	}

	/**
	 * Function to check if the view is created by one of the users, who is below the current user in the role hierarchy.
	 *
	 * @return bool true/false
	 */
	public function isOthers()
	{
		return !$this->isMine() && $this->get('status') != App\CustomView::CV_STATUS_PUBLIC;
	}

	/**
	 * Function which checks if a view is set to Public by the user which may/may not be approved.
	 *
	 * @return bool true/false
	 */
	public function isSetPublic()
	{
		return $this->get('status') == App\CustomView::CV_STATUS_PUBLIC || $this->get('status') == App\CustomView::CV_STATUS_PENDING;
	}

	public function isFeatured($editView = false)
	{
		\App\Log::trace('Entering ' . __METHOD__ . ' method ...');
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
		\App\Log::trace('Exiting ' . __METHOD__ . ' method ...');

		return $this->isFeatured;
	}

	public function checkFeaturedInEditView()
	{
		$db = App\Db::getInstance('admin');
		$cvId = $this->getId();
		if (!$cvId) {
			return false;
		}

		return (new App\Db\Query())->from('u_#__featured_filter')
			->where(['cvid' => $cvId, 'user' => 'Users:' . Users_Record_Model::getCurrentUserModel()->getId()])
			->exists($db);
	}

	public function checkPermissionToFeatured($editView = false)
	{
		$currentUser = \App\User::getCurrentUserModel();
		$query = (new \App\Db\Query())->from('u_#__featured_filter');
		$where = ['or', ['user' => 'Users:' . $currentUser->getId()], ['user' => 'Roles:' . $currentUser->getRole()]];
		foreach ($currentUser->getGroups() as $groupId) {
			$where[] = ['user' => "Groups:$groupId"];
		}
		foreach (explode('::', $currentUser->getParentRolesSeq()) as $role) {
			$where[] = ['user' => "RoleAndSubordinates:$role"];
		}
		$query->where(['cvid' => $this->getId()]);
		$query->andWhere($where);

		return $query->exists();
	}

	public function isEditable()
	{
		if ($this->get('privileges') == 0) {
			return false;
		}
		if (\App\User::getCurrentUserModel()->isAdmin()) {
			return true;
		}
		$moduleModel = $this->getModule();
		$moduleName = $moduleModel->get('name');
		if (!\App\Privilege::isPermitted($moduleName, 'CreateCustomFilter')) {
			return false;
		}

		if ($this->isMine() || $this->isOthers()) {
			return true;
		}

		return false;
	}

	public function privilegeToDelete()
	{
		return $this->isEditable() && $this->get('presence') != 0;
	}

	/**
	 * Function which provides the records for the current view.
	 *
	 * @param bool $skipRecords - List of the RecordIds to be skipped
	 *
	 * @return int[] List of RecordsIds
	 */
	public function getRecordIds($skipRecords = false, $module = false, $lockRecords = false)
	{
		$queryGenerator = $this->getRecordsListQuery($skipRecords, $module, $lockRecords);

		return $queryGenerator->createQuery()->column();
	}

	/**
	 * Create query.
	 *
	 * @param int[]  $skipRecords
	 * @param string $module
	 * @param bool   $lockRecords
	 *
	 * @return \App\QueryGenerator
	 */
	public function getRecordsListQuery($skipRecords = false, $module = false, $lockRecords = false)
	{
		$cvId = $this->getId();
		$moduleModel = $this->getModule();
		$moduleName = $moduleModel->get('name');
		$baseTableName = $moduleModel->get('basetable');
		$baseTableId = $moduleModel->get('basetableid');
		$queryGenerator = new App\QueryGenerator($moduleName);
		if (!empty($cvId) && $cvId != 0) {
			$queryGenerator->initForCustomViewById($cvId);
		} else {
			$queryGenerator->initForDefaultCustomView();
		}
		$queryGenerator->setFields(['id']);

		$searchKey = $this->get('search_key');
		$searchValue = $this->get('search_value');
		$operator = $this->get('operator');
		if (!empty($searchValue)) {
			$queryGenerator->addBaseSearchConditions($searchKey, $searchValue, $operator);
		}
		$searchParams = $this->get('search_params');
		if (empty($searchParams)) {
			$searchParams = [];
		}
		$transformedSearchParams = $queryGenerator->parseBaseSearchParamsToCondition($searchParams);
		$queryGenerator->parseAdvFilter($transformedSearchParams);
		if (is_array($skipRecords) && count($skipRecords) > 0) {
			$queryGenerator->addNativeCondition(['not in', "$baseTableName.$baseTableId", $skipRecords]);
		}
		if ($lockRecords) {
			$lockFields = Vtiger_CRMEntity::getInstance($moduleName)->getLockFields();
			if (is_array($lockFields)) {
				foreach ($lockFields as $fieldName => $fieldValues) {
					$queryGenerator->addNativeCondition(['not in', "$baseTableName.$fieldName", $fieldValues]);
				}
			}
		}

		return $queryGenerator;
	}

	/**
	 * Function to save the custom view record.
	 */
	public function save()
	{
		$db = \App\Db::getInstance();
		$currentUserModel = Users_Record_Model::getCurrentUserModel();

		$cvIdOrg = $cvId = $this->getId();
		$setDefault = (int) ($this->get('setdefault'));
		$status = $this->get('status');
		$featured = $this->get('featured');

		if ($status == App\CustomView::CV_STATUS_PENDING) {
			if ($currentUserModel->isAdminUser()) {
				$status = App\CustomView::CV_STATUS_PUBLIC;
				$this->set('status', $status);
			}
		}
		$transaction = $db->beginTransaction();
		if (!$cvId) {
			$this->addCustomView();
			$cvId = $this->getId();
		} else {
			$this->updateCustomView();
		}

		$userId = 'Users:' . $currentUserModel->getId();
		if (!empty($featured) && empty($cvIdOrg)) {
			Settings_CustomView_Module_Model::setFeaturedFilterView($cvId, $userId, 'add');
		} elseif (empty($featured) && !empty($cvIdOrg)) {
			Settings_CustomView_Module_Model::setFeaturedFilterView($cvId, $userId, 'remove');
		} elseif (!empty($featured)) {
			$isExists = (new App\Db\Query())->from('u_#__featured_filter')->where(['cvid' => $cvId, 'user' => $userId])->exists();
			if (!$isExists) {
				Settings_CustomView_Module_Model::setFeaturedFilterView($cvId, $userId, 'add');
			}
		}
		if (empty($setDefault) && !empty($cvIdOrg)) {
			App\Db::getInstance()->createCommand()
				->delete('vtiger_user_module_preferences', ['userid' => $userId, 'tabid' => $this->getModule()->getId(), 'default_cvid' => $cvId])
				->execute();
		} elseif (!empty($setDefault)) {
			$this->setDefaultFilter();
		}
		$transaction->commit();
		\App\Cache::clear();
	}

	/**
	 * Function to delete the custom view record.
	 */
	public function delete()
	{
		$db = App\Db::getInstance();
		$cvId = $this->getId();
		$db->createCommand()->delete('vtiger_customview', ['cvid' => $cvId])->execute();
		$db->createCommand()->delete('vtiger_cvcolumnlist', ['cvid' => $cvId])->execute();
		$db->createCommand()->delete('vtiger_cvstdfilter', ['cvid' => $cvId])->execute();
		$db->createCommand()->delete('vtiger_cvadvfilter', ['cvid' => $cvId])->execute();
		$db->createCommand()->delete('vtiger_cvadvfilter_grouping', ['cvid' => $cvId])->execute();
		$db->createCommand()->delete('vtiger_user_module_preferences', ['default_cvid' => $cvId])->execute();
		// To Delete the mini list widget associated with the filter
		$db->createCommand()->delete('vtiger_module_dashboard', ['filterid' => $cvId])->execute();
		App\Cache::clear();
	}

	/**
	 * Function to delete the custom view record.
	 */
	public function setDefaultFilter()
	{
		$db = App\Db::getInstance();
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$userId = 'Users:' . $currentUser->getId();
		$tabId = $this->getModule()->getId();
		$db->createCommand()->delete('vtiger_user_module_preferences', ['userid' => $userId, 'tabid' => $tabId])->execute();
		$db->createCommand()->insert('vtiger_user_module_preferences', [
			'userid' => $userId,
			'tabid' => $tabId,
			'default_cvid' => $this->getId(),
		])->execute();
	}

	/**
	 * Set conditions for filter.
	 */
	public function setConditionsForFilter()
	{
		$db = \App\Db::getInstance();
		$moduleModel = $this->getModule();
		$cvId = $this->getId();

		$stdFilterList = $this->get('stdfilterlist');
		if (!empty($stdFilterList) && !empty($stdFilterList['columnname'])) {
			$db->createCommand()
				->insert('vtiger_cvstdfilter', [
					'cvid' => $cvId,
					'columnname' => $stdFilterList['columnname'],
					'stdfilter' => $stdFilterList['stdfilter'],
					'startdate' => trim($stdFilterList['startdate'], "'"),
					'enddate' => trim($stdFilterList['enddate'], "'"),
				])->execute();
		}

		$advFilterList = $this->get('advfilterlist');
		if (!empty($advFilterList)) {
			foreach ($advFilterList as $groupIndex => $groupInfo) {
				if (empty($groupInfo)) {
					continue;
				}
				$groupColumns = $groupInfo['columns'];
				$groupCondition = $groupInfo['condition'] ?? false;

				foreach ($groupColumns as $columnIndex => $columnCondition) {
					if (empty($columnCondition)) {
						continue;
					}
					$advFilterColumn = $columnCondition['columnname'];
					$advFilterComparator = $columnCondition['comparator'];
					$advFitlerValue = $columnCondition['value'];
					$advFilterColumnCondition = $columnCondition['column_condition'];

					$columnInfo = explode(':', $advFilterColumn);
					$fieldName = $columnInfo[2];
					$fieldModel = $moduleModel->getField($fieldName);
					//Required if Events module fields are selected for the condition
					if (!$fieldModel) {
						$modulename = $moduleModel->get('name');
						if ($modulename === 'Calendar') {
							$eventModuleModel = Vtiger_Module_model::getInstance('Events');
							$fieldModel = $eventModuleModel->getField($fieldName);
						}
					}
					$fieldType = $fieldModel->getFieldDataType();

					if ($fieldType === 'currency') {
						if ($fieldModel->get('uitype') == '72') {
							// Some of the currency fields like Unit Price, Totoal , Sub-total - doesn't need currency conversion during save
							$advFitlerValue = CurrencyField::convertToDBFormat($advFitlerValue, null, true);
						} else {
							$advFitlerValue = CurrencyField::convertToDBFormat($advFitlerValue);
						}
					}
					if (($fieldType === 'date' || ($fieldType === 'time' && $fieldName !== 'time_start' && $fieldName !== 'time_end') || ($fieldType === 'datetime')) && ($fieldType !== '' && $advFitlerValue !== '')) {
						$tempVal = explode(',', $advFitlerValue);
						$val = [];
						$countTempVal = count($tempVal);
						for ($x = 0; $x < $countTempVal; ++$x) {
							//if date and time given then we have to convert the date and
							//leave the time as it is, if date only given then temp_time
							//value will be empty
							if (trim($tempVal[$x]) !== '' && trim($tempVal[$x]) !== '--') {
								$date = new DateTimeField(trim($tempVal[$x]));
								if ($fieldType === 'date') {
									$val[$x] = DateTimeField::convertToDBFormat(trim($tempVal[$x]));
								} elseif ($fieldType === 'datetime') {
									$val[$x] = $date->getDBInsertDateTimeValue();
								} else {
									$val[$x] = $date->getDBInsertTimeValue();
								}
							}
						}
						$advFitlerValue = implode(',', $val);
					}
					if (in_array($advFilterComparator, ['om', 'wr', 'nwr'])) {
						$advFitlerValue = '';
					}
					$db->createCommand()
						->insert('vtiger_cvadvfilter', [
							'cvid' => $cvId,
							'columnindex' => $columnIndex,
							'columnname' => $advFilterColumn,
							'comparator' => $advFilterComparator,
							'value' => $advFitlerValue,
							'groupid' => $groupIndex,
							'column_condition' => $advFilterColumnCondition,
						])->execute();

					// Update the condition expression for the group to which the condition column belongs
					$groupConditionExpression = '';
					if (!empty($advFilterList[$groupIndex]['conditionexpression'])) {
						$groupConditionExpression = $advFilterList[$groupIndex]['conditionexpression'];
					}
					$groupConditionExpression = $groupConditionExpression . ' ' . $columnIndex . ' ' . $advFilterColumnCondition;
					$advFilterList[$groupIndex]['conditionexpression'] = $groupConditionExpression;
				}
				if (empty($advFilterList[$groupIndex]['conditionexpression'])) {
					continue; // Case when the group doesn't have any column criteria
				}
				$db->createCommand()
					->insert('vtiger_cvadvfilter_grouping', [
						'groupid' => $groupIndex,
						'cvid' => $cvId,
						'group_condition' => $groupCondition,
						'condition_expression' => $advFilterList[$groupIndex]['conditionexpression'],
					])->execute();
			}
		}
	}

	public function setColumnlist()
	{
		$db = App\Db::getInstance();
		$cvId = $this->getId();
		foreach ($this->get('columnslist') as $index => $columnName) {
			$db->createCommand()->insert('vtiger_cvcolumnlist', [
				'cvid' => $cvId,
				'columnindex' => $index,
				'columnname' => $columnName,
			])->execute();
		}
	}

	/**
	 * Function to add the custom view record in db.
	 */
	public function addCustomView()
	{
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$moduleName = $this->getModule()->get('name');
		$seq = $this->getNextSeq($moduleName);
		$db = \App\Db::getInstance();
		$db->createCommand()->insert('vtiger_customview', [
			'viewname' => $this->get('viewname'),
			'setmetrics' => $this->get('setmetrics'),
			'entitytype' => $moduleName,
			'status' => $this->get('status'),
			'userid' => $currentUser->getId(),
			'sequence' => $seq,
			'featured' => null,
			'color' => $this->get('color'),
			'description' => $this->get('description'),
		])->execute();
		$this->set('cvid', $db->getLastInsertID('vtiger_customview_cvid_seq'));
		$this->setColumnlist();
		$this->setConditionsForFilter();
	}

	/**
	 * Get next sequence.
	 *
	 * @param string $moduleName
	 *
	 * @return int
	 */
	public function getNextSeq($moduleName)
	{
		$maxSequence = (new \App\Db\Query())->from('vtiger_customview')->where(['entitytype' => $moduleName])->max('sequence');

		return (int) $maxSequence + 1;
	}

	/**
	 * Function to update the custom view record in db.
	 */
	public function updateCustomView()
	{
		$db = App\Db::getInstance();
		$dbCommand = $db->createCommand();
		$cvId = $this->getId();
		$dbCommand->update('vtiger_customview', [
			'viewname' => $this->get('viewname'),
			'setmetrics' => $this->get('setmetrics'),
			'status' => $this->get('status'),
			'color' => $this->get('color'),
			'description' => $this->get('description'),
			], ['cvid' => $cvId]
		)->execute();
		$dbCommand->delete('vtiger_cvcolumnlist', ['cvid' => $cvId])->execute();
		$dbCommand->delete('vtiger_cvstdfilter', ['cvid' => $cvId])->execute();
		$dbCommand->delete('vtiger_cvadvfilter', ['cvid' => $cvId])->execute();
		$dbCommand->delete('vtiger_cvadvfilter_grouping', ['cvid' => $cvId])->execute();
		$this->setColumnlist();
		$this->setConditionsForFilter();
	}

	/**
	 * Function to get the list of selected fields for the current custom view.
	 *
	 * @return array List of Field Column Names
	 */
	public function getSelectedFields()
	{
		$cvId = $this->getId();
		if (!$cvId) {
			return [];
		}

		return (new App\Db\Query())->select('vtiger_cvcolumnlist.columnindex, vtiger_cvcolumnlist.columnname')
			->from('vtiger_cvcolumnlist')
			->innerJoin('vtiger_customview', 'vtiger_cvcolumnlist.cvid = vtiger_customview.cvid')
			->where(['vtiger_customview.cvid' => $cvId])->orderBy('vtiger_cvcolumnlist.columnindex')
			->createCommand()->queryAllByGroup();
	}

	/**
	 * Function to get the Standard filter condition for the current custom view.
	 *
	 * @return array Standard filter condition
	 */
	public function getStandardCriteria()
	{
		$cvId = $this->getId();
		if (empty($cvId)) {
			return [];
		}
		$stdFilterRow = (new App\Db\Query())->select(['vtiger_cvstdfilter.*'])->from('vtiger_cvstdfilter')->innerJoin('vtiger_customview', 'vtiger_cvstdfilter.cvid = vtiger_customview.cvid')->where(['vtiger_cvstdfilter.cvid' => $this->getId()])->one();
		if ($stdFilterRow) {
			$stdFilterList = [];
			$stdFilterList['columnname'] = $stdFilterRow['columnname'];
			$stdFilterList['stdfilter'] = $stdFilterRow['stdfilter'];

			if ($stdFilterRow['stdfilter'] === 'custom' || $stdFilterRow['stdfilter'] === '') {
				if ($stdFilterRow['startdate'] != '0000-00-00' && $stdFilterRow['startdate'] != '') {
					$startDateTime = new DateTimeField($stdFilterRow['startdate'] . ' ' . date('H:i:s'));
					$stdFilterList['startdate'] = $startDateTime->getDisplayDate();
				}
				if ($stdFilterRow['enddate'] != '0000-00-00' && $stdFilterRow['enddate'] != '') {
					$endDateTime = new DateTimeField($stdFilterRow['enddate'] . ' ' . date('H:i:s'));
					$stdFilterList['enddate'] = $endDateTime->getDisplayDate();
				}
			} else { //if it is not custom get the date according to the selected duration
				$dateFilter = DateTimeRange::getDateRangeByType($stdFilterRow['stdfilter']);
				$startDateTime = new DateTimeField($dateFilter[0] . ' ' . date('H:i:s'));
				$stdFilterList['startdate'] = $startDateTime->getDisplayDate();
				$endDateTime = new DateTimeField($dateFilter[1] . ' ' . date('H:i:s'));
				$stdFilterList['enddate'] = $endDateTime->getDisplayDate();
			}
		}

		return $stdFilterList;
	}

	/**
	 * Function to get the list of advanced filter conditions for the current custom view.
	 *
	 * @return array - All the advanced filter conditions for the custom view, grouped by the condition grouping
	 */
	public function getAdvancedCriteria()
	{
		$defaultCharset = AppConfig::main('default_charset');

		$cvId = $this->getId();
		$advFtCriteria = [];
		if (empty($cvId)) {
			return $advFtCriteria;
		}
		$query = (new App\Db\Query())->from('vtiger_cvadvfilter_grouping')->where(['cvid' => $this->getId()])->orderBy('groupid');
		$dataReader = $query->createCommand()->query();

		$i = 1;
		$j = 0;
		while ($relCriteriaGroup = $dataReader->read()) {
			$groupId = $relCriteriaGroup['groupid'];
			$groupCondition = $relCriteriaGroup['group_condition'];
			$rows = (new App\Db\Query())->select(['vtiger_cvadvfilter.*'])->from('vtiger_customview')->innerJoin('vtiger_cvadvfilter', 'vtiger_customview.cvid = vtiger_cvadvfilter.cvid')->leftJoin('vtiger_cvadvfilter_grouping', 'vtiger_cvadvfilter.cvid = vtiger_cvadvfilter_grouping.cvid')->where(['vtiger_customview.cvid' => $this->getId(), 'vtiger_cvadvfilter.groupid' => $groupId])
				->andWhere(['and', new \yii\db\Expression('`vtiger_cvadvfilter`.`groupid` = `vtiger_cvadvfilter_grouping`.`groupid`')])
				->orderBy('vtiger_cvadvfilter.columnindex')->all();

			if (!$rows) {
				continue;
			}

			foreach ($rows as $relCriteriaRow) {
				$criteria = [];
				$criteria['columnname'] = html_entity_decode($relCriteriaRow['columnname'], ENT_QUOTES, $defaultCharset);
				$criteria['comparator'] = $relCriteriaRow['comparator'];
				$advFilterVal = html_entity_decode($relCriteriaRow['value'], ENT_QUOTES, $defaultCharset);
				$col = explode(':', $relCriteriaRow['columnname']);
				if ($col[4] === 'D' || ($col[4] === 'T' && $col[1] !== 'time_start' && $col[1] !== 'time_end') || ($col[4] === 'DT')) {
					$tempVal = explode(',', $relCriteriaRow['value']);
					$val = [];
					$countTempVal = count($tempVal);
					for ($x = 0; $x < $countTempVal; ++$x) {
						if ($col[4] === 'D') {
							/* while inserting in db for due_date it was taking date and time values also as it is
							 * date time field. We only need to take date from that value
							 */
							if ($col[0] === 'vtiger_activity' && $col[1] === 'due_date') {
								$originalValue = $tempVal[$x];
								$dateTime = explode(' ', $originalValue);
								$tempVal[$x] = $dateTime[0];
							}
							$date = new DateTimeField(trim($tempVal[$x]));
							$val[$x] = $date->getDisplayDate();
						} elseif ($col[4] === 'DT') {
							$comparator = ['e', 'n', 'b', 'a'];
							if (in_array($criteria['comparator'], $comparator)) {
								$originalValue = $tempVal[$x];
								$dateTime = explode(' ', $originalValue);
								$tempVal[$x] = $dateTime[0];
							}
							$date = new DateTimeField(trim($tempVal[$x]));
							$val[$x] = $date->getDisplayDateTimeValue();
						} else {
							$date = new DateTimeField(trim($tempVal[$x]));
							$val[$x] = $date->getDisplayTime();
						}
					}
					$advFilterVal = implode(',', $val);
				}
				$criteria['value'] = \App\Purifier::encodeHtml(App\Purifier::decodeHtml($advFilterVal));
				$criteria['column_condition'] = $relCriteriaRow['column_condition'];

				$groupId = $relCriteriaRow['groupid'];
				$advFtCriteria[$groupId]['columns'][$j] = $criteria;
				$advFtCriteria[$groupId]['condition'] = $groupCondition;
				++$j;
			}
			if (!empty($advFtCriteria[$groupId]['columns'][$j - 1]['column_condition'])) {
				$advFtCriteria[$groupId]['columns'][$j - 1]['column_condition'] = '';
			}
			++$i;
		}
		$dataReader->close();
		// Clear the condition (and/or) for last group, if any.
		if (!empty($advFtCriteria[$i - 1]['condition'])) {
			$advFtCriteria[$i - 1]['condition'] = '';
		}

		return $advFtCriteria;
	}

	/**
	 * Function returns approve url.
	 *
	 * @return string - approve url
	 */
	public function getCreateUrl()
	{
		return 'index.php?module=CustomView&view=EditAjax&source_module=' . $this->getModule()->get('name');
	}

	/**
	 * Function returns approve url.
	 *
	 * @return string - approve url
	 */
	public function getEditUrl()
	{
		return 'module=CustomView&view=EditAjax&source_module=' . $this->getModule()->get('name') . '&record=' . $this->getId();
	}

	/**
	 * Function returns approve url.
	 *
	 * @return string - approve url
	 */
	public function getApproveUrl()
	{
		return 'index.php?module=CustomView&action=Approve&sourceModule=' . $this->getModule()->get('name') . '&record=' . $this->getId();
	}

	/**
	 * Function returns deny url.
	 *
	 * @return string - deny url
	 */
	public function getDenyUrl()
	{
		return 'index.php?module=CustomView&action=Deny&sourceModule=' . $this->getModule()->get('name') . '&record=' . $this->getId();
	}

	/**
	 * Function returns duplicate url.
	 *
	 * @return string - duplicate url
	 */
	public function getDuplicateUrl()
	{
		return 'module=CustomView&view=EditAjax&source_module=' . $this->getModule()->get('name') . '&record=' . $this->getId() . '&duplicate=1';
	}

	/**
	 *  Functions returns delete url.
	 *
	 * @return string - delete url
	 */
	public function getDeleteUrl()
	{
		return 'index.php?module=CustomView&action=Delete&sourceModule=' . $this->getModule()->get('name') . '&record=' . $this->getId();
	}

	/**
	 * Function to approve filter.
	 */
	public function approve()
	{
		App\Db::getInstance()->createCommand()
			->update('vtiger_customview', ['status' => App\CustomView::CV_STATUS_PUBLIC], ['cvid' => $this->getId()])
			->execute();
	}

	/**
	 * Function deny.
	 */
	public function deny()
	{
		App\Db::getInstance()->createCommand()
			->update('vtiger_customview', ['status' => App\CustomView::CV_STATUS_PRIVATE], ['cvid' => $this->getId()])
			->execute();
	}

	/**
	 * Function to get the advanced filter option names by Field type.
	 *
	 * @return <Array>
	 */
	public static function getAdvancedFilterOpsByFieldType()
	{
		return [
			'V' => ['e', 'n', 's', 'ew', 'c', 'k'],
			'N' => ['e', 'n', 'l', 'g', 'm', 'h'],
			'T' => ['e', 'n', 'l', 'g', 'm', 'h', 'bw', 'b', 'a'],
			'I' => ['e', 'n', 'l', 'g', 'm', 'h'],
			'C' => ['e', 'n'],
			'D' => ['e', 'n', 'bw', 'b', 'a'],
			'DT' => ['e', 'n', 'bw', 'b', 'a'],
			'NN' => ['e', 'n', 'l', 'g', 'm', 'h'],
			'E' => ['e', 'n', 's', 'ew', 'c', 'k'],
		];
	}

	/**
	 * Function to get all the accessible Custom Views, for a given module if specified.
	 *
	 * @param string $moduleName
	 *
	 * @return <Array> - Array of Vtiger_CustomView_Record models
	 */
	public static function getAll($moduleName = '')
	{
		\App\Log::trace('Entering ' . __METHOD__ . " ($moduleName) method ...");
		$currentUser = \App\User::getCurrentUserModel();
		$cacheName = $moduleName . $currentUser->getId();
		if (App\Cache::has('getAllFilters', $cacheName)) {
			return App\Cache::get('getAllFilters', $cacheName);
		}
		$query = (new App\Db\Query())->from('vtiger_customview');
		if (!empty($moduleName)) {
			$query->where(['entitytype' => $moduleName]);
		}
		if (!$currentUser->isAdmin()) {
			$userParentRoleSeq = $currentUser->getParentRolesSeq();
			$query->andWhere([
				'or',
				['userid' => $currentUser->getId()],
				['status' => 0],
				['status' => 3],
				['userid' => (new App\Db\Query())->select(['vtiger_user2role.userid'])
					->from('vtiger_user2role')
					->innerJoin('vtiger_users', 'vtiger_users.id = vtiger_user2role.userid')
					->innerJoin('vtiger_role', 'vtiger_role.roleid = vtiger_user2role.roleid')
					->where(['like', 'vtiger_role.parentrole', "{$userParentRoleSeq}::%", false]),
				],
			]);
		}
		$dataReader = $query->orderBy(['sequence' => SORT_ASC])->createCommand()->query();
		$customViews = [];
		while ($row = $dataReader->read()) {
			$customView = new self();
			if (strlen(App\Purifier::decodeHtml($row['viewname'])) > 40) {
				$row['viewname'] = substr(App\Purifier::decodeHtml($row['viewname']), 0, 36) . '...';
			}
			$customViews[$row['cvid']] = $customView->setData($row)->setModule($row['entitytype']);
		}
		$dataReader->close();

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
					$view['status'] = App\CustomView::CV_STATUS_SYSTEM;
					$customView = new self();
					$customViews[$name] = $customView->setData($view)->setModule($moduleName);
				}
			}
		}
		\App\Cache::save('getAllFilters', $cacheName, $customViews, \App\Cache::LONG);
		\App\Log::trace('Exiting ' . __METHOD__ . ' method ...');

		return $customViews;
	}

	/**
	 * Function to get the instance of Custom View module, given custom view id.
	 *
	 * @param int $cvId
	 *
	 * @return CustomView_Record_Model instance, if exists. Null otherwise
	 */
	public static function getInstanceById($cvId)
	{
		if (\App\Cache::has('CustomView_Record_ModelgetInstanceById', $cvId)) {
			$row = \App\Cache::get('CustomView_Record_ModelgetInstanceById', $cvId);
		} else {
			$row = (new \App\Db\Query())->from('vtiger_customview')->where(['cvid' => $cvId])->one();
			\App\Cache::save('CustomView_Record_ModelgetInstanceById', $cvId, $row, \App\Cache::LONG);
		}
		if ($row) {
			$customView = new self();

			return $customView->setData($row)->setModule($row['entitytype']);
		}

		return null;
	}

	/**
	 * Function to get all the custom views, of a given module if specified, grouped by their status.
	 *
	 * @param string $moduleName
	 *
	 * @return <Array> - Associative array of Status label to an array of Vtiger_CustomView_Record models
	 */
	public static function getAllByGroup($moduleName = '', $menuId = false)
	{
		$customViews = self::getAll($moduleName);
		$filters = array_keys($customViews);
		$groupedCustomViews = [];
		if ($menuId) {
			$userPrivModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
			$roleMenu = 'user_privileges/menu_' . filter_var($userPrivModel->get('roleid'), FILTER_SANITIZE_NUMBER_INT) . '.php';
			if (file_exists($roleMenu)) {
				require $roleMenu;
			} else {
				require 'user_privileges/menu_0.php';
			}
			if (count($menus) == 0) {
				require 'user_privileges/menu_0.php';
			}
			if (array_key_exists($menuId, $filterList)) {
				$filtersMenu = explode(',', $filterList[$menuId]['filters']);
				$filters = array_intersect($filtersMenu, $filters);
				if (empty($filters)) {
					$filters = [App\CustomView::getInstance($moduleName)->getDefaultCvId()];
				}
			}
		}
		foreach ($filters as $id) {
			$customView = $customViews[$id];
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
	 * Function to get Clean instance of this record.
	 *
	 * @return self
	 */
	public static function getCleanInstance()
	{
		return new self();
	}

	/**
	 * Function to check duplicates from database.
	 *
	 * @return bool
	 */
	public function checkDuplicate()
	{
		$query = (new App\Db\Query())->from('vtiger_customview')
			->where(['viewname' => $this->get('viewname'), 'entitytype' => $this->getModule()->getName()]);
		$cvid = $this->getId();
		if ($cvid) {
			$query->andWhere(['<>', 'cvid', $cvid]);
		}

		return $query->exists();
	}

	/**
	 * Function used to transform the older filter condition to suit newer filters.
	 * The newer filters have only two groups one with ALL(AND) condition between each
	 * filter and other with ANY(OR) condition, this functions tranforms the older
	 * filter with 'AND' condition between filters of a group and will be placed under
	 * match ALL conditions group and the rest of it will be placed under match Any group.
	 *
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
			} elseif ($block == 'and' || $index == 1) {
				$allGroupColumns = array_merge($allGroupColumns, $group['columns']);
			} else {
				$anyGroupColumns = array_merge($anyGroupColumns, $group['columns']);
			}
		}
		if ($standardFilter) {
			$allGroupColumns = array_merge($allGroupColumns, $standardFilter);
		}
		$transformedAdvancedCondition = [];
		$transformedAdvancedCondition[1] = ['columns' => $allGroupColumns, 'condition' => 'and'];
		$transformedAdvancedCondition[2] = ['columns' => $anyGroupColumns, 'condition' => ''];

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

			return [$tranformedStandardFilter];
		} else {
			return false;
		}
	}

	/**
	 * Function gives default custom view for a module.
	 *
	 * @param string $module
	 *
	 * @return CustomView_Record_Model
	 */
	public static function getAllFilterByModule($module)
	{
		$viewId = (new \App\Db\Query())->select(['cvid'])->from('vtiger_customview')->where(['viewname' => 'All', 'entitytype' => $module])->scalar();
		if (!$viewId) {
			$viewId = App\CustomView::getInstance($module)->getViewId();
		}

		return self::getInstanceById($viewId);
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
				$return = $return[1] ?? '';
				break;
			default:
				break;
		}

		return $return;
	}
}
