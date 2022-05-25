<?php
/**
 * Custom view file.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

namespace App;

/**
 * Custom view class.
 */
class CustomView
{
	const CV_STATUS_DEFAULT = 0;
	const CV_STATUS_PRIVATE = 1;
	const CV_STATUS_PENDING = 2;
	const CV_STATUS_PUBLIC = 3;
	const CV_STATUS_SYSTEM = 4;

	/**
	 * Do we have multiple ids.
	 *
	 * @param string $cvId (comma separated id list or one id)
	 *
	 * @return bool
	 */
	public static function isMultiViewId($cvId)
	{
		return false !== strpos($cvId, ',');
	}

	/**
	 * Function to get all the date filter type informations.
	 *
	 * @return array
	 */
	public static function getDateFilterTypes()
	{
		$dateFilters = Condition::DATE_OPERATORS;
		foreach (array_keys($dateFilters) as $filterType) {
			$dateValues = \DateTimeRange::getDateRangeByType($filterType);
			$dateFilters[$filterType]['startdate'] = $dateValues[0];
			$dateFilters[$filterType]['enddate'] = $dateValues[1];
		}
		return $dateFilters;
	}

	/**
	 * Get current page.
	 *
	 * @param string     $moduleName
	 * @param int|string $viewId
	 *
	 * @return int
	 */
	public static function getCurrentPage($moduleName, $viewId)
	{
		if (!empty($_SESSION['lvs'][$moduleName][$viewId]['start'])) {
			return $_SESSION['lvs'][$moduleName][$viewId]['start'];
		}
		return 1;
	}

	/**
	 * Set current page.
	 *
	 * @param string     $moduleName
	 * @param int|string $viewId
	 * @param int        $start
	 */
	public static function setCurrentPage($moduleName, $viewId, $start)
	{
		if (empty($start)) {
			unset($_SESSION['lvs'][$moduleName][$viewId]['start']);
		} else {
			$_SESSION['lvs'][$moduleName][$viewId]['start'] = $start;
		}
	}

	/**
	 * Function that sets the module filter in session.
	 *
	 * @param string     $moduleName - module name
	 * @param int|string $viewId     - filter id
	 */
	public static function setCurrentView($moduleName, $viewId)
	{
		$_SESSION['lvs'][$moduleName]['viewname'] = $viewId;
	}

	/**
	 * Function that reads current module filter.
	 *
	 * @param string $moduleName - module name
	 *
	 * @return int|string
	 */
	public static function getCurrentView($moduleName)
	{
		return $_SESSION['lvs'][$moduleName]['viewname'] ?? null;
	}

	/**
	 * Get sorted by.
	 *
	 * @param string $moduleName
	 *
	 * @return string
	 */
	public static function getSortBy($moduleName)
	{
		return empty($_SESSION['lvs'][$moduleName]['sortby']) ? [] : $_SESSION['lvs'][$moduleName]['sortby'];
	}

	/**
	 * Set sorted by.
	 *
	 * @param string $moduleName
	 * @param mixed  $sortBy
	 */
	public static function setSortBy(string $moduleName, $sortBy)
	{
		if (empty($sortBy)) {
			unset($_SESSION['lvs'][$moduleName]['sortby']);
		} else {
			$_SESSION['lvs'][$moduleName]['sortby'] = $sortBy;
		}
	}

	/**
	 * Has view changed.
	 *
	 * @param string     $moduleName
	 * @param int|string $viewId
	 *
	 * @return bool
	 */
	public static function hasViewChanged(string $moduleName, $viewId = false): bool
	{
		return empty($_SESSION['lvs'][$moduleName]['viewname'])
		|| ($viewId && ($viewId !== $_SESSION['lvs'][$moduleName]['viewname']))
		|| !isset($_SESSION['lvs'][$moduleName]['sortby']);
	}

	/**
	 * Static Function to get the Instance of CustomView.
	 *
	 * @param string $moduleName
	 * @param mixed  $userModelOrId
	 *
	 * @return \self
	 */
	public static function getInstance($moduleName, $userModelOrId = false)
	{
		if (!$userModelOrId) {
			$userModelOrId = User::getCurrentUserId();
		}
		if (is_numeric($userModelOrId)) {
			$userModel = User::getUserModel($userModelOrId);
		} else {
			$userModel = $userModelOrId;
		}
		$cacheName = $moduleName . '.' . $userModel->getId();
		if (\App\Cache::staticHas('AppCustomView', $cacheName)) {
			return \App\Cache::staticGet('AppCustomView', $cacheName);
		}
		$instance = new self();
		$instance->moduleName = $moduleName;
		$instance->user = $userModel;
		\App\Cache::staticSave('AppCustomView', $cacheName, $instance);

		return $instance;
	}

	/** @var \Vtiger_Module_Model */
	private $module;
	private $moduleName;
	private $user;
	private $defaultViewId;

	/**
	 * Gets module object.
	 *
	 * @return false|\Vtiger_Module_Model
	 */
	public function getModule()
	{
		if (!$this->module) {
			$this->module = \Vtiger_Module_Model::getInstance($this->moduleName);
		}
		return $this->module;
	}

	/**
	 * Get custom view from file.
	 *
	 * @param string $cvId
	 *
	 * @throws Exceptions\AppException
	 */
	private function getCustomViewFromFile($cvId)
	{
		\App\Log::trace(__METHOD__ . ' - ' . $cvId);
		$handlerClass = \Vtiger_Loader::getComponentClassName('Filter', $cvId, $this->moduleName);
		$filter = new $handlerClass();
		Cache::staticSave('getCustomView', $cvId, $filter);
		return $filter;
	}

	/**
	 * Get custom view from file.
	 *
	 * @param string $cvIds (comma separated multi cdIds)
	 *
	 * @throws Exceptions\AppException
	 */
	public function getCustomView($cvIds)
	{
		\App\Log::trace(__METHOD__ . ' - ' . $cvIds);
		if (Cache::staticHas('getCustomView', $cvIds)) {
			return Cache::staticGet('getCustomView', $cvIds);
		}
		if (empty($cvIds) || !static::isMultiViewId($cvIds)) {
			return $this->getCustomViewFromFile($cvIds);
		}
		$filters = [];
		foreach (explode(',', $cvIds) as $cvId) {
			$filters[] = $this->getCustomViewFromFile($cvId);
		}
		Cache::staticSave('getCustomView', $cvIds, $filters);
		return $filters;
	}

	/**
	 * Columns list by cvid.
	 *
	 * @param mixed $cvId
	 *
	 * @throws Exceptions\AppException
	 *
	 * @return array
	 */
	private function getColumnsByCvidFromDb($cvId)
	{
		\App\Log::trace(__METHOD__ . ' - ' . $cvId);
		$columnList = [];
		if (is_numeric($cvId)) {
			$dataReader = (new Db\Query())->select(['field_name', 'module_name', 'source_field_name', 'label'])
				->from('vtiger_cvcolumnlist')
				->innerJoin('vtiger_tab', 'vtiger_tab.name=vtiger_cvcolumnlist.module_name')
				->innerJoin('vtiger_field', 'vtiger_tab.tabid = vtiger_field.tabid AND vtiger_field.fieldname = vtiger_cvcolumnlist.field_name')
				->where(['cvid' => $cvId, 'vtiger_field.presence' => [0, 2]])->orderBy('columnindex')->createCommand()->query();
			while ($row = $dataReader->read()) {
				if (!empty($row['source_field_name']) && !$this->getModule()->getFieldByName($row['source_field_name'])->isActiveField()) {
					continue;
				}
				$columnList[] = $row;
			}
			$dataReader->close();
			if ($columnList) {
				Cache::save('getColumnsListByCvid', $cvId, $columnList);
			}
		} else {
			$view = $this->getCustomViewFromFile($cvId);
			$columnList = $view->getColumnList();
			Cache::save('getColumnsListByCvid', $cvId, $columnList);
		}
		return $columnList;
	}

	/**
	 * Columns list by cvid.
	 *
	 * @param mixed $cvIds (comma separated)
	 *
	 * @throws Exceptions\AppException
	 *
	 * @return array
	 */
	public function getColumnsListByCvid($cvIds)
	{
		\App\Log::trace(__METHOD__ . ' - ' . $cvIds);
		if (Cache::has('getColumnsListByCvid', $cvIds)) {
			return Cache::get('getColumnsListByCvid', $cvIds);
		}
		if (empty($cvIds) || !static::isMultiViewId($cvIds)) {
			return $this->getColumnsByCvidFromDb($cvIds);
		}
		$columnLists = [];
		foreach (explode(',', $cvIds) as $cvId) {
			$columnLists[] = $this->getColumnsByCvidFromDb($cvId);
		}
		Cache::save('getColumnsListByCvid', $cvIds, $columnLists);
		return $columnLists;
	}

	/**
	 * Returns conditions for filter.
	 *
	 * @param int|string $id
	 *
	 * @return array
	 *               [
	 *               'condition' => "AND" or "OR"
	 *               'rules' => [[
	 *               'fieldname' => name of fields
	 *               'operator' => operator, for instance: 'e'
	 *               'value' => values
	 *               ]]
	 *               ]
	 */
	public static function getConditions($id): array
	{
		if (Cache::has('CustomView_GetConditions', $id)) {
			return Cache::get('CustomView_GetConditions', $id);
		}
		$dataReader = (new \App\Db\Query())->select([
			'u_#__cv_condition.group_id',
			'u_#__cv_condition.field_name',
			'u_#__cv_condition.module_name',
			'u_#__cv_condition.source_field_name',
			'u_#__cv_condition.operator',
			'u_#__cv_condition.value',
			'condition_index' => 'u_#__cv_condition.index',
			'u_#__cv_condition_group.id',
			'u_#__cv_condition_group.condition',
			'u_#__cv_condition_group.parent_id',
			'group_index' => 'u_#__cv_condition_group.index',
		])->from('u_#__cv_condition_group')
			->leftJoin('u_#__cv_condition', 'u_#__cv_condition.group_id = u_#__cv_condition_group.id')
			->where(['u_#__cv_condition_group.cvid' => $id])
			->orderBy(['u_#__cv_condition_group.parent_id' => SORT_ASC])
			->createCommand()->query();
		$referenceGroup = $referenceParent = $conditions = [];
		while ($condition = $dataReader->read()) {
			if ($condition['group_id']) {
				$isEmptyCondition = false;
			} else {
				$condition['group_id'] = $condition['id'];
				$isEmptyCondition = true;
			}
			$value = $condition['value'];
			$fieldName = "{$condition['field_name']}:{$condition['module_name']}" . ($condition['source_field_name'] ? ':' . $condition['source_field_name'] : '');
			if (isset($referenceParent[$condition['parent_id']], $referenceGroup[$condition['group_id']])) {
				$referenceParent[$condition['parent_id']][$condition['condition_index']] = [
					'fieldname' => $fieldName,
					'operator' => $condition['operator'],
					'value' => $value,
				];
			} elseif (isset($referenceGroup[$condition['parent_id']])) {
				if ($isEmptyCondition) {
					$referenceGroup[$condition['parent_id']][$condition['group_index']] = [
						'condition' => $condition['condition'],
						'rules' => [],
					];
				} else {
					$referenceGroup[$condition['parent_id']][$condition['group_index']] = [
						'condition' => $condition['condition'],
						'rules' => [
							$condition['condition_index'] => [
								'fieldname' => $fieldName,
								'operator' => $condition['operator'],
								'value' => $value,
							],
						],
					];
				}
				$referenceParent[$condition['parent_id']] = &$referenceGroup[$condition['parent_id']][$condition['group_index']]['rules'];
				$referenceGroup[$condition['group_id']] = &$referenceGroup[$condition['parent_id']][$condition['group_index']]['rules'];
			} else {
				if ($isEmptyCondition) {
					$conditions = [
						'condition' => $condition['condition'],
						'rules' => [],
					];
				} else {
					$conditions = [
						'condition' => $condition['condition'],
						'rules' => [
							$condition['condition_index'] => [
								'fieldname' => $fieldName,
								'operator' => $condition['operator'],
								'value' => $value,
							],
						],
					];
				}
				$referenceParent[$condition['parent_id']] = &$conditions['rules'];
				$referenceGroup[$condition['group_id']] = &$conditions['rules'];
			}
		}
		$conditions = static::sortConditions($conditions);
		Cache::save('CustomView_GetConditions', $id, $conditions, Cache::LONG);
		return $conditions;
	}

	/**
	 * Sorting conditions.
	 *
	 * @param array|null $array
	 * @param ?array     $arrayToSort
	 *
	 * @return array|null
	 */
	private static function sortConditions(?array $arrayToSort): ?array
	{
		if (isset($arrayToSort['rules'])) {
			ksort($arrayToSort['rules']);
			foreach ($arrayToSort['rules'] as $rule) {
				if (isset($rule['condition'])) {
					static::sortConditions($rule);
				}
			}
		}
		return $arrayToSort;
	}

	/**
	 * Get fields to detect duplicates.
	 *
	 * @param int|string $viewId
	 *
	 * @return array
	 */
	public static function getDuplicateFields($viewId): array
	{
		if (!is_numeric($viewId)) {
			return [];
		}
		if (Cache::has('CustomView_GetDuplicateFields', $viewId)) {
			return Cache::get('CustomView_GetDuplicateFields', $viewId);
		}
		$data = (new \App\Db\Query())->select(['vtiger_field.fieldname', 'u_#__cv_duplicates.ignore'])
			->from('u_#__cv_duplicates')
			->innerJoin('vtiger_field', 'vtiger_field.fieldid = u_#__cv_duplicates.fieldid')
			->where(['u_#__cv_duplicates.cvid' => $viewId])->all();
		Cache::save('CustomView_GetDuplicateFields', $viewId, $data);
		return $data;
	}

	/**
	 * To get the customViewId of the specified module.
	 *
	 * @param mixed $noCache
	 *
	 * @return int|string
	 */
	public function getViewId($noCache = false)
	{
		\App\Log::trace(__METHOD__);
		if (isset($this->defaultViewId)) {
			return $this->defaultViewId;
		}
		if ($noCache || Request::_isEmpty('viewname')) {
			$viewId = null;
			if (Request::_has('mid')) {
				$viewId = current(self::getModuleFiltersByMenuId(Request::_getInteger('mid'), $this->moduleName));
			}
			if (empty($viewId) && !$noCache && self::getCurrentView($this->moduleName)) {
				$viewId = self::getCurrentView($this->moduleName);
				if (empty($this->getFilterInfo($viewId))) {
					$viewId = null;
				}
			}
			if (empty($viewId)) {
				$viewId = $this->getDefaultCvId();
			}
			if (empty($viewId) || !$this->isPermittedCustomView($viewId)) {
				$viewId = $this->getMandatoryFilter();
			}
		} else {
			$viewId = Request::_get('viewname');
			if (!is_numeric($viewId)) {
				if ('All' === $viewId) {
					$viewId = $this->getMandatoryFilter();
				} else {
					$viewId = $this->getViewIdByName($viewId);
				}
				if (!$viewId) {
					$viewId = $this->getDefaultCvId();
				}
			} else {
				$viewId = (int) $viewId;
				if (!$this->isPermittedCustomView($viewId)) {
					throw new Exceptions\NoPermitted('ERR_NO_PERMITTED_TO_VIEW');
				}
			}
		}
		$this->defaultViewId = $viewId;
		return $viewId;
	}

	/**
	 * Get default cvId.
	 *
	 * @return int|string
	 */
	public function getDefaultCvId()
	{
		$cacheName = $this->moduleName . $this->user->getId();
		if (Cache::has('GetDefaultCvId', $cacheName)) {
			return Cache::get('GetDefaultCvId', $cacheName);
		}
		$query = (new Db\Query())->select(['userid', 'default_cvid'])->from('vtiger_user_module_preferences')->where(['tabid' => Module::getModuleId($this->moduleName)]);
		$data = $query->createCommand()->queryAllByGroup();
		$defaultCvId = null;
		foreach ($this->user->getMemberStructure() as $member) {
			if (isset($data[$member]) && $this->isPermittedCustomView($data[$member])) {
				$defaultCvId = $data[$member];
				break;
			}
		}
		if (!$defaultCvId) {
			foreach ($this->getFilters() as $cvId => $values) {
				if (1 === $values['setdefault'] && $this->isPermittedCustomView($cvId)) {
					$defaultCvId = $cvId;
					break;
				}
			}
			if (!$defaultCvId) {
				$defaultCvId = $this->getMandatoryFilter();
			}
		}
		Cache::save('GetDefaultCvId', $cacheName, $defaultCvId);
		return $defaultCvId;
	}

	/**
	 * Function to check if the current user is able to see the customView.
	 *
	 * @param int|string $viewId
	 *
	 * @return bool
	 */
	public function isPermittedCustomView($viewId)
	{
		return self::isPermitted($viewId, $this->moduleName, $this->user->getId());
	}

	/**
	 * Get mandatory filter by module.
	 *
	 * @param bolean $returnData
	 *
	 * @return array|int
	 */
	public function getMandatoryFilter($returnData = false)
	{
		Log::trace(__METHOD__);
		$info = $this->getFilters();
		$returnValue = '';
		foreach ($info as $index => &$values) {
			if (0 === $values['presence']) {
				$returnValue = $index;
				break;
			}
			if (2 === $values['presence']) {
				$returnValue = $index;
			}
		}
		return $returnData ? $info[$returnValue] : $returnValue;
	}

	/**
	 * Get viewId by name.
	 *
	 * @param string $viewName
	 *
	 * @return int|null
	 */
	public function getViewIdByName(string $viewName): ?int
	{
		$viewId = null;
		foreach ($this->getFilters() as $cvId => &$values) {
			if ($values['viewname'] === $viewName) {
				$viewId = $cvId;
				break;
			}
		}
		return $viewId;
	}

	/**
	 * Function to get basic information about filter.
	 *
	 * @param int $cvId
	 *
	 * @return array
	 */
	public function getFilterInfo(int $cvId): array
	{
		return $this->getFilters()[$cvId] ?? [];
	}

	/**
	 * Function to get basic information about all filters.
	 *
	 * @param string $moduleName
	 *
	 * @return array
	 */
	public function getFilters(): array
	{
		return self::getFiltersByModule($this->moduleName);
	}

	/**
	 * Check permissions.
	 *
	 * @param int    $cvId
	 * @param string $moduleName
	 * @param int    $userId
	 *
	 * @return bool
	 */
	public static function isPermitted(int $cvId, string $moduleName = null, int $userId = null): bool
	{
		$userModel = $userId ? \App\User::getUserModel($userId) : \App\User::getCurrentUserModel();
		return ($data = self::getCVDetails($cvId, $moduleName))
		&& ($userModel->isAdmin()
		|| $data['userid'] === $userModel->getId()
		|| 0 === $data['presence']
		|| \in_array($data['status'], [self::CV_STATUS_DEFAULT, self::CV_STATUS_PUBLIC])
		|| (self::CV_STATUS_PRIVATE === $data['status'] && array_intersect($userModel->getMemberStructure(), $data['members'])));
	}

	/**
	 * Function to get basic information about all filters for module.
	 *
	 * @param string $moduleName
	 *
	 * @return array
	 */
	public static function getFiltersByModule(string $moduleName): array
	{
		if (Cache::has('CustomViewInfo', $moduleName)) {
			return Cache::get('CustomViewInfo', $moduleName);
		}
		$members = (new Db\Query())->select(['u_#__cv_privileges.cvid', 'member'])->from('u_#__cv_privileges')
			->innerJoin('vtiger_customview', 'u_#__cv_privileges.cvid=vtiger_customview.cvid')
			->where(['entitytype' => $moduleName])->createCommand()->queryAllByGroup(2);
		$info = (new Db\Query())->from('vtiger_customview')->where(['entitytype' => $moduleName])->indexBy('cvid')->orderBy(['sequence' => SORT_ASC])->all();
		foreach ($info as &$item) {
			$item['cvid'] = (int) $item['cvid'];
			$item['setdefault'] = (int) $item['setdefault'];
			$item['setmetrics'] = (int) $item['setmetrics'];
			$item['status'] = (int) $item['status'];
			$item['privileges'] = (int) $item['privileges'];
			$item['featured'] = (int) $item['featured'];
			$item['presence'] = (int) $item['presence'];
			$item['sequence'] = (int) $item['sequence'];
			$item['userid'] = (int) $item['userid'];
			$item['members'] = $members[$item['cvid']] ?? [];
			Cache::save('CustomViewInfo', $item['cvid'], $item);
		}
		Cache::save('CustomViewInfo', $moduleName, $info);
		return $info;
	}

	/**
	 * Reset current views configuration in session.
	 *
	 * @param string|bool $moduleName
	 */
	public static function resetCurrentView($moduleName = false)
	{
		if (\App\Session::has('lvs')) {
			if ($moduleName) {
				$lvs = \App\Session::get('lvs');
				if (isset($lvs[$moduleName])) {
					unset($lvs[$moduleName]);
					\App\Session::set('lvs', $lvs);
				}
			} else {
				\App\Session::set('lvs', []);
			}
		}
	}

	/**
	 * Get module filters by menu id.
	 *
	 * @param int    $menuId
	 * @param string $moduleName
	 *
	 * @return array
	 */
	public static function getModuleFiltersByMenuId(int $menuId, string $moduleName = ''): array
	{
		$cacheKey = 'getModuleFiltersByMenuId' . $moduleName;
		if (\App\Cache::staticHas($cacheKey, $menuId)) {
			return \App\Cache::staticGet($cacheKey, $menuId);
		}
		$filters = [];
		$userModel = User::getCurrentUserModel();
		$roleMenu = 'user_privileges/menu_' . filter_var($userModel->getDetail('roleid'), FILTER_SANITIZE_NUMBER_INT) . '.php';
		file_exists($roleMenu) ? require $roleMenu : require 'user_privileges/menu_0.php';
		if (0 === \count($menus) && file_exists($roleMenu)) {
			require 'user_privileges/menu_0.php';
		}
		if (isset($filterList[$menuId])) {
			$filtersMenu = explode(',', $filterList[$menuId]['filters']);
			$filtersCustomView = array_keys(\CustomView_Record_Model::getAll($moduleName));
			$filters = array_intersect($filtersMenu, $filtersCustomView);
		}
		\App\Cache::staticSave($cacheKey, $menuId, $filters);
		return $filters;
	}

	/**
	 * Get custom view by ID.
	 *
	 * @param int $cvId
	 *
	 * @return array
	 */
	public static function getCustomViewById(int $cvId): array
	{
		if (Cache::has('CustomViewById', $cvId)) {
			return Cache::get('CustomViewById', $cvId);
		}
		$data = (new Db\Query())->from('vtiger_customview')->where(['cvid' => $cvId])->one() ?: [];
		if (!empty($data['advanced_conditions'])) {
			$data['advanced_conditions'] = \App\Json::decode($data['advanced_conditions']);
		}
		Cache::save('CustomViewById', $cvId, $data);
		return $data;
	}

	/**
	 * Gets custom view details by ID.
	 *
	 * @param int         $cvId
	 * @param string|null $moduleName
	 *
	 * @return array
	 */
	public static function getCVDetails(int $cvId, string $moduleName = null): array
	{
		if (Cache::has('CustomViewInfo', $cvId)) {
			return Cache::get('CustomViewInfo', $cvId);
		}
		if (!$moduleName) {
			$moduleName = self::getCustomViewById($cvId)['entitytype'] ?? '';
		}
		return $moduleName ? (self::getFiltersByModule($moduleName)[$cvId] ?? []) : [];
	}

	/**
	 * Function clear cache by custom view ID.
	 *
	 * @param int         $cvId
	 * @param string|null $moduleName
	 *
	 * @return void
	 */
	public static function clearCacheById(int $cvId, string $moduleName = null): void
	{
		Cache::delete('CustomViewById', $cvId);
		Cache::delete('CustomViewInfo', $cvId);
		Cache::delete('getAllFilterColors', false);
		Cache::delete('getAllFilterColors', true);
		if (null === $moduleName) {
			foreach (\App\Module::getAllModuleNames() as $moduleName) {
				Cache::delete('CustomViewInfo', $moduleName);
			}
		} else {
			Cache::delete('CustomViewInfo', $moduleName);
		}
	}
}
