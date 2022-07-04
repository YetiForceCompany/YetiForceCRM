<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce S.A.
 * *********************************************************************************** */

/**
 * CustomView Record Model Class.
 */
class CustomView_Record_Model extends \App\Base
{
	/** @var bool Is featured */
	protected $isFeatured;
	/** @var bool Is default */
	protected $isDefault;

	/** @var array Record changes */
	protected $changes = [];

	/**
	 * Function to get all the accessible Custom Views, for a given module if specified.
	 *
	 * @param string $moduleName
	 * @param bool   $fromFile
	 *
	 * @return array Array of Vtiger_CustomView_Record models
	 */
	public static function getAll($moduleName = '', bool $fromFile = true)
	{
		$currentUser = \App\User::getCurrentUserModel();
		$cacheName = "{$moduleName}_{$currentUser->getId()}_{$fromFile}";
		if (App\Cache::has('getAllFilters', $cacheName)) {
			return App\Cache::get('getAllFilters', $cacheName);
		}
		$query = (new App\Db\Query())->from('vtiger_customview');
		if (!empty($moduleName)) {
			$query->where(['entitytype' => $moduleName]);
		}
		if (!$currentUser->isAdmin()) {
			$query->andWhere([
				'or',
				['userid' => $currentUser->getId()],
				['presence' => 0],
				['status' => [\App\CustomView::CV_STATUS_DEFAULT, \App\CustomView::CV_STATUS_PUBLIC]],
				['and', ['status' => \App\CustomView::CV_STATUS_PRIVATE], ['cvid' => (new \App\Db\Query())->select(['cvid'])->from('u_#__cv_privileges')->where(['member' => $currentUser->getMemberStructure()])]],
			]);
		}
		$dataReader = $query->orderBy(['sequence' => SORT_ASC])->createCommand()->query();
		$customViews = [];
		while ($row = $dataReader->read()) {
			$customView = new self();
			if (\strlen(App\Purifier::decodeHtml($row['viewname'])) > 40) {
				$row['viewname'] = substr(App\Purifier::decodeHtml($row['viewname']), 0, 36) . '...';
			}
			$customViews[$row['cvid']] = $customView->setData($row)->setModule($row['entitytype']);
		}
		$dataReader->close();

		$filterDir = 'modules' . DIRECTORY_SEPARATOR . $moduleName . DIRECTORY_SEPARATOR . 'filters';
		if ($fromFile && $moduleName && file_exists($filterDir)) {
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
		if ($row = \App\CustomView::getCVDetails($cvId)) {
			$customView = new self();
			return $customView->setData($row)->setModule($row['entitytype']);
		}
		return null;
	}

	/**
	 * Function to get all the custom views, of a given module if specified, grouped by their status.
	 *
	 * @param string $moduleName
	 * @param mixed  $menuId
	 *
	 * @return <Array> - Associative array of Status label to an array of Vtiger_CustomView_Record models
	 */
	public static function getAllByGroup($moduleName = '', $menuId = false)
	{
		$customViews = self::getAll($moduleName);
		$groupedCustomViews = [];
		if (!$menuId || empty($filters = \App\CustomView::getModuleFiltersByMenuId($menuId, $moduleName))) {
			$filters = array_keys($customViews);
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
	 * Function to get the Id.
	 *
	 * @return int Custom View Id
	 */
	public function getId()
	{
		return $this->get('cvid');
	}

	/**
	 * Function to get filter name.
	 *
	 * @return string
	 */
	public function getName(): string
	{
		return $this->get('viewname');
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

	/** {@inheritdoc} */
	public function set($key, $value)
	{
		if ($this->getId() && !\in_array($key, ['cvid', 'entitytype', 'presence']) && (\array_key_exists($key, $this->value) && $this->value[$key] != $value)) {
			$this->changes[$key] = $this->get($key);
		}
		return parent::set($key, $value);
	}

	/**
	 * Function to check if the view is marked as default.
	 *
	 * @return bool true/false
	 */
	public function isDefault()
	{
		\App\Log::trace('Entering ' . __METHOD__ . ' method ...');
		if (null === $this->isDefault) {
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
		return App\CustomView::CV_STATUS_SYSTEM == $this->get('status');
	}

	/**
	 * Function to check if the view is created by the current user or is default view.
	 *
	 * @return bool true/false
	 */
	public function isMine()
	{
		return App\CustomView::CV_STATUS_DEFAULT == $this->get('status') || $this->get('userid') == \App\User::getCurrentUserId();
	}

	/**
	 * Function to check if the view is approved to be Public.
	 *
	 * @return bool true/false
	 */
	public function isPublic()
	{
		return !$this->isMine() && App\CustomView::CV_STATUS_PUBLIC == $this->get('status');
	}

	/**
	 * Function to check if the view is marked as Private.
	 *
	 * @return bool true/false
	 */
	public function isPrivate()
	{
		return App\CustomView::CV_STATUS_PRIVATE == $this->get('status');
	}

	/**
	 * Function to check if the view is requested to be Public and is awaiting for Approval.
	 *
	 * @return bool true/false
	 */
	public function isPending()
	{
		return !$this->isMine() && App\CustomView::CV_STATUS_PENDING == $this->get('status');
	}

	/**
	 * Function to check if the view is created by one of the users, who is below the current user in the role hierarchy.
	 *
	 * @return bool true/false
	 */
	public function isOthers()
	{
		return !$this->isMine() && App\CustomView::CV_STATUS_PUBLIC != $this->get('status');
	}

	/**
	 * Function which checks if a view is set to Public by the user which may/may not be approved.
	 *
	 * @return bool true/false
	 */
	public function isSetPublic()
	{
		return App\CustomView::CV_STATUS_PUBLIC == $this->get('status') || App\CustomView::CV_STATUS_PENDING == $this->get('status');
	}

	/**
	 * Check if filter is featured.
	 *
	 * @return bool
	 */
	public function isFeatured(): bool
	{
		if (null === $this->isFeatured) {
			$this->isFeatured = $this->get('featured')
			|| (new \App\Db\Query())->from('u_#__featured_filter')->where(['cvid' => $this->getId(), 'user' => \App\User::getCurrentUserModel()->getMemberStructure()])->exists();
		}
		return $this->isFeatured;
	}

	/**
	 * Check if user can change featured.
	 *
	 * @return bool
	 */
	public function isFeaturedEditable(): bool
	{
		return !$this->isFeatured() || (new App\Db\Query())->from('u_#__featured_filter')
			->where(['cvid' => $this->getId(), 'user' => \App\PrivilegeUtil::MEMBER_TYPE_USERS . ':' . \App\User::getCurrentUserId()])
			->exists();
	}

	/**
	 * Function to check permission.
	 *
	 * @return bool
	 */
	public function isPermitted(): bool
	{
		return \App\CustomView::isPermitted($this->getId(), $this->getModule()->getName());
	}

	/**
	 * Check permission to edit.
	 *
	 * @throws \Exception
	 *
	 * @return bool
	 */
	public function isEditable(): bool
	{
		$returnVal = false;
		$moduleModel = $this->getModule();
		$moduleName = $moduleModel->get('name');
		if (!\App\CustomView::getInstance($this->getModule()->getName())->isPermittedCustomView($this->getId())) {
			$returnVal = false;
		} elseif (2 !== $this->get('presence') && \App\User::getCurrentUserModel()->isAdmin()) {
			$returnVal = true;
		} elseif (0 === $this->get('privileges') || 2 === $this->get('presence')) {
			$returnVal = false;
		} elseif (!\App\Privilege::isPermitted($moduleName, 'CreateCustomFilter')) {
			$returnVal = false;
		} elseif ($this->isMine()) {
			$returnVal = true;
		}
		return $returnVal;
	}

	/**
	 * Function adds a filter to your favorites.
	 *
	 * @param int    $cvId
	 * @param string $user
	 * @param string $action
	 *
	 * @return bool
	 */
	public function setFeaturedForMember(string $user): bool
	{
		$result = true;
		if (!(new App\Db\Query())->from('u_#__featured_filter')->where(['cvid' => $this->getId(), 'user' => $user])->exists()) {
			$result = (bool) \App\Db::getInstance()->createCommand()->insert('u_#__featured_filter', ['user' => $user, 'cvid' => $this->getId()])->execute();
		}
		return $result;
	}

	/**
	 * Removes the filter from the user favorites filters.
	 *
	 * @param string $user
	 *
	 * @return bool
	 */
	public function removeFeaturedForMember(string $user): bool
	{
		return (bool) \App\Db::getInstance()->createCommand()->delete('u_#__featured_filter', ['user' => $user, 'cvid' => $this->getId()])->execute();
	}

	/**
	 * Sets filter as default for user.
	 *
	 * @param string $user
	 *
	 * @return bool
	 */
	public function setDefaultForMember(string $user): bool
	{
		$dbCommand = \App\Db::getInstance()->createCommand();
		$result = true;
		if (!(new App\Db\Query())->from('vtiger_user_module_preferences')->where(['default_cvid' => $this->getId(), 'userid' => $user])->exists()) {
			$dbCommand->delete('vtiger_user_module_preferences', ['userid' => $user, 'tabid' => $this->getModule()->getId()])->execute();
			$result = (bool) $dbCommand->insert('vtiger_user_module_preferences', [
				'userid' => $user,
				'tabid' => $this->getModule()->getId(),
				'default_cvid' => $this->getId(),
			])->execute();
		}
		return $result;
	}

	/**
	 * Removes the filter from the user default filters.
	 *
	 * @param string $user
	 *
	 * @return bool
	 */
	public function removeDefaultForMember(string $user): bool
	{
		return (bool) \App\Db::getInstance()->createCommand()->delete('vtiger_user_module_preferences', ['userid' => $user, 'default_cvid' => $this->getId()])->execute();
	}

	/**
	 * Grant permissions for the member.
	 *
	 * @param int    $cvId
	 * @param string $user
	 * @param string $action
	 *
	 * @return bool
	 */
	public function setPrivilegesForMember(string $user): bool
	{
		$result = true;
		if (!(new App\Db\Query())->from('u_#__cv_privileges')->where(['cvid' => $this->getId(), 'member' => $user])->exists()) {
			$result = (bool) \App\Db::getInstance()->createCommand()->insert('u_#__cv_privileges', ['cvid' => $this->getId(), 'member' => $user])->execute();
		}
		return $result;
	}

	/**
	 * Removes permissions for the member.
	 *
	 * @param string $user
	 *
	 * @return bool
	 */
	public function removePrivilegesForMember(string $user): bool
	{
		return (bool) \App\Db::getInstance()->createCommand()->delete('u_#__cv_privileges', ['cvid' => $this->getId(), 'member' => $user])->execute();
	}

	/**
	 * Check the permission to delete.
	 *
	 * @return bool
	 */
	public function privilegeToDelete(): bool
	{
		return $this->isEditable() && 0 != $this->get('presence');
	}

	/**
	 * Function which provides the records for the current view.
	 *
	 * @param bool  $skipRecords - List of the RecordIds to be skipped
	 * @param mixed $module
	 * @param mixed $lockRecords
	 *
	 * @return int[] List of RecordsIds
	 */
	public function getRecordIds($skipRecords = false, $module = false, $lockRecords = false)
	{
		$queryGenerator = $this->getRecordsListQuery($skipRecords, $module, $lockRecords)->setFields(['id']);
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
		if (!empty($cvId) && 0 != $cvId) {
			$queryGenerator->initForCustomViewById($cvId);
		} else {
			$queryGenerator->initForDefaultCustomView();
		}
		$searchKey = $this->get('search_key');
		$searchValue = $this->get('search_value');
		if (!empty($searchValue) && ($operator = $this->get('operator'))) {
			$queryGenerator->addCondition($searchKey, $searchValue, $operator);
		}
		if ($advancedConditions = $this->get('advancedConditions')) {
			$queryGenerator->setAdvancedConditions($advancedConditions);
		}
		$searchParams = $this->getArray('search_params');
		if (empty($searchParams)) {
			$searchParams = [];
		}
		$transformedSearchParams = $queryGenerator->parseBaseSearchParamsToCondition($searchParams);
		$queryGenerator->parseAdvFilter($transformedSearchParams);
		if (\is_array($skipRecords) && \count($skipRecords) > 0) {
			$queryGenerator->addNativeCondition(['not in', "$baseTableName.$baseTableId", $skipRecords]);
		}
		if ($this->has('entityState')) {
			$queryGenerator->setStateCondition($this->get('entityState'));
		}
		if (($orderBy = $this->get('orderby')) && \is_array($orderBy)) {
			foreach ($orderBy as $fieldName => $sortFlag) {
				[$fieldName, $moduleName, $sourceFieldName] = array_pad(explode(':', $fieldName), 3, false);
				if ($sourceFieldName) {
					$queryGenerator->setRelatedOrder([
						'sourceField' => $sourceFieldName,
						'relatedModule' => $moduleName,
						'relatedField' => $fieldName,
						'relatedSortOrder' => $sortFlag,
					]);
				} else {
					$queryGenerator->setOrder($fieldName, $sortFlag);
				}
			}
		}
		if ($lockRecords) {
			$lockFields = Vtiger_CRMEntity::getInstance($moduleName)->getLockFields();
			$lockFields = array_replace_recursive($lockFields, \App\RecordStatus::getLockStatus($moduleName));
			foreach ($lockFields as $fieldName => $fieldValues) {
				$queryGenerator->addNativeCondition(['not in', "$baseTableName.$fieldName", $fieldValues]);
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

		if (App\CustomView::CV_STATUS_PENDING == $status && $currentUserModel->isAdminUser()) {
			$status = App\CustomView::CV_STATUS_PUBLIC;
			$this->set('status', $status);
		}
		$transaction = $db->beginTransaction();
		try {
			if ('edit' === $this->get('mode')) {
				$this->saveToDb();
			} else {
				if (!$cvId) {
					$this->addCustomView();
					$cvId = $this->getId();
				} else {
					$this->updateCustomView();
				}

				$userId = 'Users:' . $currentUserModel->getId();
				if (empty($featured) && !empty($cvIdOrg)) {
					$this->removeFeaturedForMember($userId);
				} elseif (!empty($featured)) {
					$this->setFeaturedForMember($userId);
				}
				if (empty($setDefault) && !empty($cvIdOrg)) {
					$this->removeDefaultForMember($userId);
				} elseif (!empty($setDefault)) {
					$this->setDefaultForMember($userId);
				}
			}
			$transaction->commit();
		} catch (\Throwable $ex) {
			$transaction->rollBack();
			\App\Log::error($ex->__toString());
		}
		\App\Cache::clear();
	}

	/**
	 * Save data to the database.
	 */
	public function saveToDb()
	{
		$dbCommand = \App\Db::getInstance()->createCommand();
		$tableData = array_intersect_key($this->getData(), $this->changes);
		if ($tableData) {
			if (1 === ($tableData['setdefault'] ?? null)) {
				$dbCommand->update('vtiger_customview', ['setdefault' => 0], ['entitytype' => $this->getModule()->getName()])->execute();
			}
			$dbCommand->update('vtiger_customview', $tableData, ['cvid' => $this->getId()])->execute();
			if (isset($tableData['sort']) && $this->getId() === App\CustomView::getCurrentView($this->getModule()->getName())) {
				\App\CustomView::setSortBy($this->getModule()->getName(), $tableData['sort'] ? \App\Json::decode($tableData['sort']) : null);
			}
		}
	}

	/**
	 * Set value from request.
	 *
	 * @param \App\Request $request
	 * @param string       $fieldName
	 * @param string       $requestFieldValue
	 */
	public function setValueFromRequest(App\Request $request, string $fieldName, string $requestFieldValue)
	{
		switch ($fieldName) {
			case 'status':
			case 'setdefault':
			case 'privileges':
			case 'featured':
				$value = $request->getInteger($requestFieldValue);
				break;
			case 'sort':
				$value = \App\Json::encode($request->getArray($requestFieldValue, \App\Purifier::STANDARD, [], \App\Purifier::SQL));
				break;
			default:
				$value = null;
				break;
		}
		if (null === $value) {
			throw new \App\Exceptions\IllegalValue('ERR_ILLEGAL_VALUE');
		}
		$this->set($fieldName, $value);
	}

	/**
	 * Function to delete the custom view record.
	 */
	public function delete()
	{
		$dbCommand = App\Db::getInstance()->createCommand();
		$cvId = $this->getId();
		$dbCommand->delete('vtiger_customview', ['cvid' => $cvId])->execute();
		$dbCommand->delete('vtiger_user_module_preferences', ['default_cvid' => $cvId])->execute();
		$dbCommand->delete('vtiger_module_dashboard', ['filterid' => $cvId])->execute();
		$result = $dbCommand->delete('yetiforce_menu', ['dataurl' => $cvId, 'type' => array_search('CustomFilter', \App\Menu::TYPES)])->execute();
		if ($result) {
			(new \App\BatchMethod(['method' => '\App\Menu::reloadMenu', 'params' => []]))->save();
		}
		\App\CustomView::clearCacheById($cvId);
		App\Cache::clear();
	}

	/**
	 * Returns condition.
	 * array() [
	 * 'condition' => "AND" or "OR"
	 * 'rules' => [[
	 *        'fieldname' => name of fields
	 *        'operator' => operator, for instance: 'e'
	 *        'value' => values
	 *    ]]
	 * ].
	 *
	 * @return array
	 */
	public function getConditions(): array
	{
		return $this->getId() ? \App\CustomView::getConditions($this->getId()) : [];
	}

	/**
	 * Return list of field to detect duplicates.
	 *
	 * @return array
	 */
	public function getDuplicateFields(): array
	{
		return (new \App\Db\Query())->select(['fieldid', 'ignore'])->from('u_#__cv_duplicates')->where(['cvid' => $this->getId()])->all();
	}

	/**
	 * Get custom view advanced conditions.
	 *
	 * @return array
	 */
	public function getAdvancedConditions(): array
	{
		return $this->isEmpty('advanced_conditions') ? [] : \App\Json::decode($this->get('advanced_conditions'));
	}

	/**
	 * Add condition to database.
	 *
	 * @param array $rule
	 * @param int   $parentId
	 * @param int   $index
	 *
	 * @throws \App\Exceptions\Security
	 * @throws \yii\db\Exception
	 *
	 * @return void
	 */
	private function addCondition(array $rule, int $parentId, int $index)
	{
		[$fieldName, $fieldModuleName, $sourceFieldName] = array_pad(explode(':', $rule['fieldname']), 3, false);
		$operator = $rule['operator'];
		$value = $rule['value'] ?? '';

		if (!$this->get('advfilterlistDbFormat') && !\in_array($operator, array_merge(\App\Condition::OPERATORS_WITHOUT_VALUES, \App\Condition::FIELD_COMPARISON_OPERATORS, array_keys(App\Condition::DATE_OPERATORS)))) {
			$value = Vtiger_Module_Model::getInstance($fieldModuleName)->getFieldByName($fieldName)
				->getUITypeModel()
				->getDbConditionBuilderValue($value, $operator);
		}
		\App\Db::getInstance()->createCommand()->insert('u_#__cv_condition', [
			'group_id' => $parentId,
			'field_name' => $fieldName,
			'module_name' => $fieldModuleName,
			'source_field_name' => $sourceFieldName,
			'operator' => $operator,
			'value' => $value,
			'index' => $index,
		])->execute();
	}

	/**
	 * Add group to database.
	 *
	 * @param array|null $rule
	 * @param int        $parentId
	 * @param int        $index
	 *
	 * @throws \App\Exceptions\Security
	 * @throws \yii\db\Exception
	 *
	 * @return void
	 */
	private function addGroup(?array $rule, int $parentId, int $index)
	{
		if (empty($rule) || empty($rule['rules'])) {
			return;
		}
		$db = \App\Db::getInstance();
		$db->createCommand()->insert('u_#__cv_condition_group', [
			'cvid' => $this->getId(),
			'condition' => 'AND' === $rule['condition'] ? 'AND' : 'OR',
			'parent_id' => $parentId,
			'index' => $index,
		])->execute();
		$index = 0;
		$parentId = $db->getLastInsertID('u_#__cv_condition_group_id_seq');
		foreach ($rule['rules'] as $ruleInfo) {
			if (isset($ruleInfo['condition'])) {
				$this->addGroup($ruleInfo, $parentId, $index);
			} else {
				$this->addCondition($ruleInfo, $parentId, $index);
			}
			++$index;
		}
	}

	/**
	 * Set conditions for filter.
	 *
	 * @return void
	 */
	public function setConditionsForFilter()
	{
		$this->addGroup($this->get('advfilterlist'), 0, 0);
	}

	public function setColumnlist()
	{
		$db = App\Db::getInstance();
		$cvId = $this->getId();
		foreach ($this->get('columnslist') as $index => $columnInfo) {
			$columnInfoExploded = explode(':', $columnInfo);
			$db->createCommand()->insert('vtiger_cvcolumnlist', [
				'cvid' => $cvId,
				'columnindex' => $index,
				'field_name' => $columnInfoExploded[0],
				'module_name' => $columnInfoExploded[1],
				'source_field_name' => $columnInfoExploded[2] ?? null,
				'label' => $this->get('customFieldNames')[$columnInfo] ?? ''
			])->execute();
		}
	}

	/**
	 * Function to add the custom view record in db.
	 */
	protected function addCustomView()
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
			'advanced_conditions' => $this->get('advanced_conditions'),
		])->execute();
		$this->set('cvid', (int) $db->getLastInsertID('vtiger_customview_cvid_seq'));
		$this->setColumnlist();
		$this->setConditionsForFilter();
		$this->setDuplicateFields();
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
	protected function updateCustomView()
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
			'advanced_conditions' => $this->get('advanced_conditions'),
		], ['cvid' => $cvId]
		)->execute();
		$dbCommand->delete('vtiger_cvcolumnlist', ['cvid' => $cvId])->execute();
		$dbCommand->delete('u_#__cv_condition_group', ['cvid' => $cvId])->execute();
		$dbCommand->delete('u_#__cv_duplicates', ['cvid' => $cvId])->execute();
		$this->setColumnlist();
		$this->setConditionsForFilter();
		$this->setDuplicateFields();
	}

	/**
	 * Save fields to detect dupllicates.
	 *
	 * @return void
	 */
	private function setDuplicateFields()
	{
		$fields = $this->get('duplicatefields');
		if (empty($fields)) {
			return;
		}
		$dbCommand = App\Db::getInstance()->createCommand();
		foreach ($fields as $data) {
			$dbCommand->insert('u_#__cv_duplicates', [
				'cvid' => $this->getId(),
				'fieldid' => $data['fieldid'],
				'ignore' => $data['ignore'],
			])->execute();
		}
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
		$selectedFields = (new App\Db\Query())->select([
			'vtiger_cvcolumnlist.columnindex',
			'vtiger_cvcolumnlist.field_name',
			'vtiger_cvcolumnlist.module_name',
			'vtiger_cvcolumnlist.source_field_name',
			'vtiger_cvcolumnlist.label',
		])
			->from('vtiger_cvcolumnlist')
			->innerJoin('vtiger_customview', 'vtiger_cvcolumnlist.cvid = vtiger_customview.cvid')
			->where(['vtiger_customview.cvid' => $cvId])->orderBy('vtiger_cvcolumnlist.columnindex')
			->createCommand()->queryAllByGroup(1);
		$result = [];
		foreach ($selectedFields as $item) {
			$key = "{$item['field_name']}:{$item['module_name']}" . ($item['source_field_name'] ? ":{$item['source_field_name']}" : '');
			$result[$key] = $item['label'];
		}
		return $result;
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
	 * @param int|null $mid
	 *
	 * @return string - approve url
	 */
	public function getEditUrl($mid = null)
	{
		return 'index.php?module=CustomView&view=EditAjax&source_module=' . $this->getModule()->get('name') . '&record=' . $this->getId() . ($mid ? "&mid={$mid}" : '');
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
		return 'index.php?module=CustomView&view=EditAjax&source_module=' . $this->getModule()->get('name') . '&record=' . $this->getId() . '&duplicate=1';
	}

	/**
	 *  Functions returns delete url.
	 *
	 * @param int|null $mid
	 *
	 * @return string - delete url
	 */
	public function getDeleteUrl($mid = null)
	{
		return 'index.php?module=CustomView&action=Delete&sourceModule=' . $this->getModule()->get('name') . '&record=' . $this->getId() . ($mid ? "&mid={$mid}" : '');
	}

	/**
	 * Function to approve filter.
	 */
	public function approve()
	{
		App\Db::getInstance()->createCommand()
			->update('vtiger_customview', ['status' => App\CustomView::CV_STATUS_PUBLIC], ['cvid' => $this->getId()])
			->execute();
		\App\CustomView::clearCacheById($this->getId(), $this->getModule()->getName());
	}

	/**
	 * Function deny.
	 */
	public function deny()
	{
		App\Db::getInstance()->createCommand()
			->update('vtiger_customview', ['status' => App\CustomView::CV_STATUS_PRIVATE], ['cvid' => $this->getId()])
			->execute();
		\App\CustomView::clearCacheById($this->getId(), $this->getModule()->getName());
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
	 * Get sort data.
	 *
	 * @return array
	 */
	public function getSortOrderBy()
	{
		return empty($this->get('sort')) ? [] : \App\Json::decode($this->get('sort'));
	}
}
