<?php
namespace App;

use \App\Db;

/**
 * Custom view class
 * @package YetiForce.App
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class CustomView
{

	const CV_STATUS_DEFAULT = 0;
	const CV_STATUS_PRIVATE = 1;
	const CV_STATUS_PENDING = 2;
	const CV_STATUS_PUBLIC = 3;
	const CV_STATUS_SYSTEM = 4;

	/**
	 * Standard filter conditions for date fields
	 */
	const STD_FILTER_CONDITIONS = ['custom', 'prevfy', 'thisfy', 'nextfy', 'prevfq', 'thisfq', 'nextfq', 'yesterday', 'today', 'tomorrow',
		'lastweek', 'thisweek', 'nextweek', 'lastmonth', 'thismonth', 'nextmonth',
		'last7days', 'last15days', 'last30days', 'last60days', 'last90days', 'last120days', 'next15days', 'next30days', 'next60days', 'next90days', 'next120days'];

	/**
	 * Supported advanced filter operations
	 */
	const ADVANCED_FILTER_OPTIONS = [
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
		'y' => 'LBL_IS_EMPTY',
		'ny' => 'LBL_IS_NOT_EMPTY',
		'om' => 'LBL_CURRENTLY_LOGGED_USER',
		'wr' => 'LBL_IS_WATCHING_RECORD',
		'nwr' => 'LBL_IS_NOT_WATCHING_RECORD',
	];

	/**
	 * Data filter list 
	 */
	const DATE_FILTER_CONDITIONS = [
		'custom' => ['label' => 'LBL_CUSTOM'],
		'prevfy' => ['label' => 'LBL_PREVIOUS_FY'],
		'thisfy' => ['label' => 'LBL_CURRENT_FY'],
		'nextfy' => ['label' => 'LBL_NEXT_FY'],
		'prevfq' => ['label' => 'LBL_PREVIOUS_FQ'],
		'thisfq' => ['label' => 'LBL_CURRENT_FQ'],
		'nextfq' => ['label' => 'LBL_NEXT_FQ'],
		'yesterday' => ['label' => 'LBL_YESTERDAY'],
		'today' => ['label' => 'LBL_TODAY'],
		'tomorrow' => ['label' => 'LBL_TOMORROW'],
		'lastweek' => ['label' => 'LBL_LAST_WEEK'],
		'thisweek' => ['label' => 'LBL_CURRENT_WEEK'],
		'nextweek' => ['label' => 'LBL_NEXT_WEEK'],
		'lastmonth' => ['label' => 'LBL_LAST_MONTH'],
		'thismonth' => ['label' => 'LBL_CURRENT_MONTH'],
		'nextmonth' => ['label' => 'LBL_NEXT_MONTH'],
		'last7days' => ['label' => 'LBL_LAST_7_DAYS'],
		'last15days' => ['label' => 'LBL_LAST_15_DAYS'],
		'last30days' => ['label' => 'LBL_LAST_30_DAYS'],
		'last60days' => ['label' => 'LBL_LAST_60_DAYS'],
		'last90days' => ['label' => 'LBL_LAST_90_DAYS'],
		'last120days' => ['label' => 'LBL_LAST_120_DAYS'],
		'next15days' => ['label' => 'LBL_NEXT_15_DAYS'],
		'next30days' => ['label' => 'LBL_NEXT_30_DAYS'],
		'next60days' => ['label' => 'LBL_NEXT_60_DAYS'],
		'next90days' => ['label' => 'LBL_NEXT_90_DAYS'],
		'next120days' => ['label' => 'LBL_NEXT_120_DAYS']
	];

	/**
	 * Function to get all the date filter type informations
	 * @return array
	 */
	public static function getDateFilterTypes()
	{
		$dateFilters = self::DATE_FILTER_CONDITIONS;
		foreach (array_keys($dateFilters) as $filterType) {
			$dateValues = \DateTimeRange::getDateRangeByType($filterType);
			$dateFilters[$filterType]['startdate'] = $dateValues[0];
			$dateFilters[$filterType]['enddate'] = $dateValues[1];
		}
		return $dateFilters;
	}

	/**
	 * Get current page
	 * @param string $moduleName
	 * @param int|string $viewId
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
	 * Set current page
	 * @param string $moduleName
	 * @param int|string $viewId
	 * @param int $start
	 */
	public static function setCurrentPage($moduleName, $viewId, $start)
	{
		$_SESSION['lvs'][$moduleName][$viewId]['start'] = $start;
	}

	/**
	 * Function that sets the module filter in session
	 * @param string $moduleName - module name
	 * @param int|string $viewId - filter id
	 */
	public static function setCurrentView($moduleName, $viewId)
	{
		$_SESSION['lvs'][$moduleName]['viewname'] = $viewId;
	}

	/**
	 * Function that reads current module filter
	 * @param string $moduleName - module name
	 * @return int|string
	 */
	public static function getCurrentView($moduleName)
	{
		if (!empty($_SESSION['lvs'][$moduleName]['viewname'])) {
			return $_SESSION['lvs'][$moduleName]['viewname'];
		}
	}

	/**
	 * Get sort directions
	 * @param string $moduleName
	 * @return string
	 */
	public static function getSorder($moduleName)
	{
		if (!empty($_SESSION['lvs'][$moduleName]['sorder'])) {
			return $_SESSION['lvs'][$moduleName]['sorder'];
		}
	}

	/**
	 * Set sort directions
	 * @param string $moduleName
	 * @param string $order
	 */
	public static function setSorder($moduleName, $order)
	{
		$_SESSION['lvs'][$moduleName]['sorder'] = $order;
	}

	/**
	 * Get sorted by
	 * @param string $moduleName
	 * @return string
	 */
	public static function getSortby($moduleName)
	{
		if (!empty($_SESSION['lvs'][$moduleName]['sortby'])) {
			return $_SESSION['lvs'][$moduleName]['sortby'];
		}
	}

	/**
	 * Set sorted by
	 * @param string $moduleName
	 * @param string $order
	 */
	public static function setSortby($moduleName, $order)
	{
		$_SESSION['lvs'][$moduleName]['sortby'] = $order;
	}

	/**
	 * Set default sort order by
	 * @param string $moduleName
	 * @param string $defaultSortOrderBy
	 */
	public static function setDefaultSortOrderBy($moduleName, $defaultSortOrderBy = [])
	{
		if (\AppRequest::has('orderby')) {
			$_SESSION['lvs'][$moduleName]['sortby'] = \AppRequest::get('orderby');
		}
		if (\AppRequest::has('sortorder')) {
			$_SESSION['lvs'][$moduleName]['sorder'] = \AppRequest::get('sortorder');
		}
		if (isset($defaultSortOrderBy['orderBy'])) {
			$_SESSION['lvs'][$moduleName]['sortby'] = $defaultSortOrderBy['orderBy'];
		}
		if (isset($defaultSortOrderBy['sortOrder'])) {
			$_SESSION['lvs'][$moduleName]['sorder'] = $defaultSortOrderBy['sortOrder'];
		}
	}

	/**
	 * Has view changed
	 * @param string $moduleName
	 * @param int|string $viewId
	 * @return boolean
	 */
	public static function hasViewChanged($moduleName, $viewId = false)
	{
		if (empty($_SESSION['lvs'][$moduleName]['viewname'])) {
			return true;
		}
		if (!\AppRequest::isEmpty('viewname') && (\AppRequest::get('viewname') !== $_SESSION['lvs'][$moduleName]['viewname'])) {
			return true;
		}
		if ($viewId && ($viewId !== $_SESSION['lvs'][$moduleName]['viewname'])) {
			return true;
		}
		return false;
	}

	/**
	 * Static Function to get the Instance of CustomView
	 * @param string $moduleName
	 * @param mixed $user
	 * @return \self
	 */
	public static function getInstance($moduleName, $user = false)
	{
		if (!$user) {
			$user = User::getCurrentUserId();
		}
		if (is_numeric($user)) {
			$user = User::getUserModel($user);
		}
		$cacheName = $moduleName . '.' . $user->getUserId();
		if (\App\Cache::staticHas('AppCustomView', $cacheName)) {
			return \App\Cache::staticGet('AppCustomView', $cacheName);
		}
		$instance = new self();
		$instance->moduleName = $moduleName;
		$instance->user = $user;
		\App\Cache::staticGet('AppCustomView', $cacheName, $instance);
		return $instance;
	}

	private $moduleName;
	private $user;
	private $defaultViewId;
	private $cvStatus;
	private $cvUserId;

	/**
	 * Get custom view from file
	 * @param string $cvId
	 * @throws \Exception\AppException
	 */
	public function getCustomViewFromFile($cvId)
	{
		if (Cache::staticHas('getCustomViewFile', $cvId)) {
			return Cache::staticGet('getCustomViewFile', $cvId);
		}
		$filterDir = 'modules' . DIRECTORY_SEPARATOR . $this->moduleName . DIRECTORY_SEPARATOR . 'filters' . DIRECTORY_SEPARATOR . $cvId . '.php';
		if (file_exists($filterDir)) {
			$handlerClass = \Vtiger_Loader::getComponentClassName('Filter', $cvId, $this->moduleName);
			$filter = new $handlerClass();
			Cache::staticSave('getCustomViewFile', $cvId, $filter);
			return $filter;
		}
		\App\Log::error(Language::translate('LBL_NO_FOUND_VIEW') . "cvId: $cvId");
		throw new \Exception\AppException('LBL_NO_FOUND_VIEW');
	}

	/**
	 * Columns list by cvid
	 * @param mixed $cvId
	 * @return array
	 * @throws \Exception\AppException
	 */
	public function getColumnsListByCvid($cvId)
	{
		\App\Log::trace(__METHOD__ . ' - ' . $cvId);
		if (Cache::has('getColumnsListByCvid', $cvId)) {
			return Cache::get('getColumnsListByCvid', $cvId);
		}
		if (is_numeric($cvId)) {
			$query = (new Db\Query())->select(['columnindex', 'columnname'])->from('vtiger_cvcolumnlist')->where(['cvid' => $cvId])->orderBy('columnindex');
			$columnList = $query->createCommand()->queryAllByGroup();

			if ($columnList) {
				Cache::save('getColumnsListByCvid', $cvId, $columnList);
				return $columnList;
			}
		} else {
			$columnList = $this->getCustomViewFromFile($cvId)->getColumnList();
			Cache::save('getColumnsListByCvid', $cvId, $columnList);
			return $columnList;
		}
		\App\Log::error(Language::translate('LBL_NO_FOUND_VIEW') . "cvId: $cvId");
		throw new \Exception\AppException('LBL_NO_FOUND_VIEW');
	}

	/**
	 * Get the standard filter
	 * @param mixed $cvId
	 * @return array
	 */
	public function getStdFilterByCvid($cvId)
	{
		if (Cache::has('getStdFilterByCvid', $cvId)) {
			return Cache::get('getStdFilterByCvid', $cvId);
		}
		if (is_numeric($cvId)) {
			$stdFilter = (new Db\Query())->select('vtiger_cvstdfilter.*')
				->from('vtiger_cvstdfilter')
				->innerJoin('vtiger_customview', 'vtiger_cvstdfilter.cvid = vtiger_customview.cvid')
				->where(['vtiger_cvstdfilter.cvid' => $cvId])
				->one();
		} else {
			$stdFilter = $this->getCustomViewFromFile($cvId)->getStdCriteria();
		}
		if ($stdFilter) {
			$stdFilter = static::resolveDateFilterValue($stdFilter);
		}
		Cache::save('getStdFilterByCvid', $cvId, $stdFilter);
		return $stdFilter;
	}

	/**
	 * Resolve date filter value
	 * @param array $dateFilterRow
	 * @return array
	 */
	public static function resolveDateFilterValue($dateFilterRow)
	{
		$stdfilterlist = ['columnname' => $dateFilterRow['columnname'], 'stdfilter' => $dateFilterRow['stdfilter']];
		if ($dateFilterRow['stdfilter'] === 'custom' || $dateFilterRow['stdfilter'] === '' || $dateFilterRow['stdfilter'] === 'e' || $dateFilterRow['stdfilter'] === 'n') {
			if ($dateFilterRow['startdate'] !== '0000-00-00' && $dateFilterRow['startdate'] !== '') {
				$startDateTime = new \DateTimeField($dateFilterRow['startdate'] . ' ' . date('H:i:s'));
				$stdfilterlist['startdate'] = $startDateTime->getDisplayDate();
			}
			if ($dateFilterRow['enddate'] !== '0000-00-00' && $dateFilterRow['enddate'] !== '') {
				$endDateTime = new \DateTimeField($dateFilterRow['enddate'] . ' ' . date('H:i:s'));
				$stdfilterlist['enddate'] = $endDateTime->getDisplayDate();
			}
		} else { //if it is not custom get the date according to the selected duration
			$datefilter = \DateTimeRange::getDateRangeByType($dateFilterRow['stdfilter']);
			$startDateTime = new \DateTimeField($datefilter[0] . ' ' . date('H:i:s'));
			$stdfilterlist['startdate'] = $startDateTime->getDisplayDate();
			$endDateTime = new \DateTimeField($datefilter[1] . ' ' . date('H:i:s'));
			$stdfilterlist['enddate'] = $endDateTime->getDisplayDate();
		}
		return $stdfilterlist;
	}

	/**
	 * Get the Advanced filter for the given customview Id
	 * @param mixed $cvId
	 * @return array
	 */
	public function getAdvFilterByCvid($cvId)
	{
		if (Cache::has('getAdvFilterByCvid', $cvId)) {
			return Cache::get('getAdvFilterByCvid', $cvId);
		}
		$advftCriteria = [];
		if (is_numeric($cvId)) {
			$dataReaderGroup = (new Db\Query())->from('vtiger_cvadvfilter_grouping')
					->where(['cvid' => $cvId])
					->orderBy('groupid')
					->createCommand()->query();
			while ($relCriteriaGroup = $dataReaderGroup->read()) {
				$dataReader = (new Db\Query())->select('vtiger_cvadvfilter.*')
						->from('vtiger_customview')
						->innerJoin('vtiger_cvadvfilter', 'vtiger_cvadvfilter.cvid = vtiger_customview.cvid')
						->leftJoin('vtiger_cvadvfilter_grouping', 'vtiger_cvadvfilter.cvid = vtiger_cvadvfilter_grouping.cvid AND vtiger_cvadvfilter.groupid = vtiger_cvadvfilter_grouping.groupid')
						->where(['vtiger_customview.cvid' => $cvId, 'vtiger_cvadvfilter.groupid' => $relCriteriaGroup['groupid']])
						->orderBy('vtiger_cvadvfilter.columnindex')
						->createCommand()->query();
				if (!$dataReader->count()) {
					continue;
				}
				$key = $relCriteriaGroup['groupid'] === 1 ? 'and' : 'or';
				while ($relCriteriaRow = $dataReader->read()) {
					$advftCriteria[$key][] = $this->getAdvftCriteria($relCriteriaRow);
				}
			}
		} else {
			$fromFile = $this->getCustomViewFromFile($cvId)->getAdvftCriteria($this);
			$advftCriteria = $fromFile;
		}
		Cache::save('getAdvFilterByCvid', $cvId, $advftCriteria);
		return $advftCriteria;
	}

	/**
	 * Get the Advanced filter Criteria
	 * @param array $relCriteriaRow
	 * @return array
	 */
	public function getAdvftCriteria($relCriteriaRow)
	{
		$comparator = $relCriteriaRow['comparator'];
		$advFilterVal = html_entity_decode($relCriteriaRow['value'], ENT_QUOTES, \AppConfig::main('default_charset'));
		list ($tableName, $columnName, $fieldName, $moduleFieldLabel, $fieldType) = explode(':', $relCriteriaRow['columnname']);
		$tempVal = explode(',', $relCriteriaRow['value']);
		if ($fieldType === 'D' || ($fieldType === 'T' && $columnName !== 'time_start' && $columnName !== 'time_end') || ($fieldType === 'DT')) {
			$val = [];
			foreach ($tempVal as $key => $value) {
				if ($fieldType === 'D') {
					/**
					 * while inserting in db for due_date it was taking date and time values also as it is
					 * date time field. We only need to take date from that value
					 */
					if ($tableName === 'vtiger_activity' && $columnName === 'due_date') {
						$values = explode(' ', $value);
						$value = $values[0];
					}
					$val[$key] = (new \DateTimeField(trim($value)))->getDisplayDate();
				} elseif ($fieldType === 'DT') {
					if (in_array($comparator, ['e', 'n', 'b', 'a'])) {
						$dateTime = explode(' ', $value);
						$value = $dateTime[0];
					}
					$val[$key] = (new \DateTimeField(trim($value)))->getDisplayDateTimeValue();
				} else {
					$val[$key] = (new \DateTimeField(trim($value)))->getDisplayTime();
				}
			}
			$advFilterVal = implode(',', $val);
		}
		return [
			'columnname' => html_entity_decode($relCriteriaRow['columnname'], ENT_QUOTES, \AppConfig::main('default_charset')),
			'comparator' => $comparator,
			'value' => $advFilterVal
		];
	}

	/**
	 * To get the customViewId of the specified module
	 * @return int|string
	 */
	public function getViewId($noCache = false)
	{
		\App\Log::trace(__METHOD__);
		if (isset($this->defaultViewId)) {
			return $this->defaultViewId;
		}
		if ($noCache || \AppRequest::isEmpty('viewname')) {

			if (!$noCache && self::getCurrentView($this->moduleName)) {
				$viewId = self::getCurrentView($this->moduleName);
			} else {
				$viewId = $this->getDefaultCvId();
			}
			if (empty($viewId) || !$this->isPermittedCustomView($viewId)) {
				$viewId = $this->getMandatoryFilter();
			}
		} else {
			$viewId = \AppRequest::get('viewname');
			if (!is_numeric($viewId)) {
				if ($viewId === 'All') {
					$viewId = $this->getMandatoryFilter();
				} else {
					$viewId = $this->getViewIdByName($viewId);
				}
				if (!$viewId) {
					$viewId = $this->getDefaultCvId();
				}
			}
		}
		$this->defaultViewId = $viewId;
		return $viewId;
	}

	/**
	 * Get default cvId
	 * @return int|string
	 */
	public function getDefaultCvId()
	{
		Log::trace(__METHOD__);
		$cacheName = $this->moduleName . $this->user->getUserId();
		if (Cache::has('GetDefaultCvId', $cacheName)) {
			return Cache::get('GetDefaultCvId', $cacheName);
		}
		$query = (new Db\Query())->select('userid, default_cvid')->from('vtiger_user_module_preferences')->where(['tabid' => Module::getModuleId($this->moduleName)]);
		$data = $query->createCommand()->queryAllByGroup();
		$user = 'Users:' . $this->user->getUserId();
		if (isset($data[$user])) {
			Cache::save('GetDefaultCvId', $cacheName, $data[$user]);
			return $data[$user];
		}
		foreach ($this->user->getGroups() as $groupId) {
			$group = 'Groups:' . $groupId;
			if (isset($data[$group])) {
				Cache::save('GetDefaultCvId', $cacheName, $data[$group]);
				return $data[$group];
			}
		}
		$role = 'Roles:' . $this->user->getRole();
		if (isset($data[$role])) {
			Cache::save('GetDefaultCvId', $cacheName, $data[$role]);
			return $data[$role];
		}
		foreach ($this->user->getParentRoles() as $roleId) {
			$role = 'RoleAndSubordinates:' . $roleId;
			if (isset($data[$role])) {
				Cache::save('GetDefaultCvId', $cacheName, $data[$role]);
				return $data[$role];
			}
		}
		$info = $this->getInfoFilter($this->moduleName);
		foreach ($info as &$values) {
			if ($values['setdefault'] === 1) {
				Cache::save('GetDefaultCvId', $cacheName, $values['cvid']);
				return $values['cvid'];
			}
		}
	}

	/**
	 * Function to check if the current user is able to see the customView
	 * @param int|string $viewId
	 * @return boolean
	 */
	public function isPermittedCustomView($viewId)
	{
		Log::trace(__METHOD__);
		$permission = true;
		if (!empty($viewId)) {
			$statusUseridInfo = $this->getStatusAndUserid($viewId);
			if ($statusUseridInfo) {
				$status = $statusUseridInfo['status'];
				$userId = $statusUseridInfo['userid'];
				if ($status === self::CV_STATUS_DEFAULT || $this->user->isAdmin()) {
					$permission = true;
				} elseif (\AppRequest::get('view') !== 'ChangeStatus') {
					if ($status === self::CV_STATUS_PUBLIC || $userId === $this->user->getUserId()) {
						$permission = true;
					} elseif ($status === self::CV_STATUS_PRIVATE || $status === self::CV_STATUS_PENDING) {
						$subQuery = (new Db\Query())->select(['vtiger_user2role.userid'])->from('vtiger_user2role')
							->innerJoin('vtiger_users', 'vtiger_user2role.userid = vtiger_users.id')
							->innerJoin('vtiger_role', 'vtiger_user2role.userid = vtiger_role.roleid')
							->where(['like', 'vtiger_role.parentrole', $this->user->getParentRolesSeq() . '::']);
						$query = (new Db\Query())
							->select(['vtiger_users.id'])
							->from('vtiger_customview')
							->innerJoin('vtiger_users')
							->where(['vtiger_customview.cvid' => $viewId, 'vtiger_customview.userid' => $subQuery]);
						$userArray = $query->column();
						if ($userArray) {
							if (!in_array($this->user->getUserId(), $userArray)) {
								$permission = false;
							} else {
								$permission = true;
							}
						} else {
							$permission = false;
						}
					} else {
						$permission = true;
					}
				} else {
					$permission = false;
				}
			} else {
				$permission = false;
			}
		}
		return $permission;
	}

	/**
	 * Get the userid, status information of this custom view.
	 * @param int|string $viewId
	 * @return array
	 */
	public function getStatusAndUserid($viewId)
	{
		Log::trace(__METHOD__);
		if (empty($this->cvStatus) || empty($this->cvUserId)) {
			$row = $this->getInfoFilter($viewId);
			if ($row) {
				$this->cvStatus = $row['status'];
				$this->cvUserId = $row['userid'];
			} else {
				return false;
			}
		}
		return ['status' => $this->cvStatus, 'userid' => $this->cvUserId];
	}

	/**
	 * Get mandatory filter by module
	 * @param bolean $returnData
	 * @return array|int
	 */
	public function getMandatoryFilter($returnData = false)
	{
		Log::trace(__METHOD__);
		$info = $this->getInfoFilter($this->moduleName);
		foreach ($info as &$values) {
			if ($values['presence'] === 0) {
				return $returnData ? $values : $values['cvid'];
			}
		}
	}

	/**
	 * Get viewId by name
	 * @param int|string $viewName
	 * @return int
	 */
	public function getViewIdByName($viewName)
	{
		Log::trace(__METHOD__);
		$info = $this->getInfoFilter($this->moduleName);
		foreach ($info as &$values) {
			if ($values['viewname'] === $viewName) {
				return $values['cvid'];
			}
		}
		return false;
	}

	/**
	 * Function to get basic information about filter
	 * @param mixed $mixed id or module name
	 * @return array
	 */
	public function getInfoFilter($mixed)
	{
		if (Cache::has('CustomViewInfo', $mixed)) {
			return Cache::get('CustomViewInfo', $mixed);
		}
		$query = (new Db\Query())->from('vtiger_customview');
		if (is_numeric($mixed)) {
			$info = $query->where(['cvid' => $mixed])->one();
		} else {
			$info = $query->where(['entitytype' => $mixed])->all();
		}
		Cache::save('CustomViewInfo', $mixed, $info);
		return $info;
	}
}
