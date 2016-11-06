<?php
namespace App;

/**
 * Custom view class
 * @package YetiForce.App
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class CustomView
{

	private $moduleName;

	public function __construct($moduleName)
	{
		$this->moduleName = $moduleName;
	}

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
			$query = (new \App\Db\Query())->select(['columnindex', 'columnname'])->from('vtiger_cvcolumnlist')->where(['cvid' => $cvId])->orderBy('columnindex');
			$columnList = $query->createCommand()->queryAllByGroup();
			if ($columnList) {
				Cache::save('getCustomViewFile', $cvId, $columnList);
				return $columnList;
			}
		} else {
			$columnList = $this->getCustomViewFromFile($cvId)->getColumnList();
			Cache::save('getCustomViewFile', $cvId, $columnList);
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
			$stdFilter = (new \App\Db\Query())->select('vtiger_cvstdfilter.*')
				->from('vtiger_cvstdfilter')
				->innerJoin('vtiger_customview', 'vtiger_cvstdfilter.cvid = vtiger_customview.cvid')
				->where(['vtiger_cvstdfilter.cvid' => $cvId])
				->one();
		} else {
			$stdFilter = $this->getCustomViewFromFile($cvId)->getStdCriteria();
		}
		if ($stdFilter) {
			$stdFilter = $this->resolveDateFilterValue($stdFilter);
		}
		Cache::save('getCustomViewFile', $cvId, $stdFilter);
		return $stdFilter;
	}

	/**
	 * Resolve date filter value
	 * @param array $dateFilterRow
	 * @return array
	 */
	public function resolveDateFilterValue($dateFilterRow)
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
			$datefilter = $this->getDateforStdFilterBytype($dateFilterRow['stdfilter']);
			$startDateTime = new \DateTimeField($datefilter[0] . ' ' . date('H:i:s'));
			$stdfilterlist['startdate'] = $startDateTime->getDisplayDate();
			$endDateTime = new \DateTimeField($datefilter[1] . ' ' . date('H:i:s'));
			$stdfilterlist['enddate'] = $endDateTime->getDisplayDate();
		}
		return $stdfilterlist;
	}

	/**
	 * Get the date value for the given type
	 * @param string $type
	 * @return array
	 */
	public function getDateforStdFilterBytype($type)
	{
		return \DateTimeRange::getDateRangeByType($type);
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
			$dataReaderGroup = (new \App\Db\Query())->from('vtiger_cvadvfilter_grouping')
					->where(['cvid' => $cvId])
					->orderBy('groupid')
					->createCommand()->query();
			$i = 1;
			$j = 0;
			while ($relCriteriaGroup = $dataReaderGroup->read()) {
				$dataReader = (new \App\Db\Query())->select('vtiger_cvadvfilter.*')
						->from('vtiger_customview')
						->innerJoin('vtiger_cvadvfilter', 'vtiger_cvadvfilter.cvid = vtiger_customview.cvid')
						->leftJoin('vtiger_cvadvfilter_grouping', 'vtiger_cvadvfilter.cvid = vtiger_cvadvfilter_grouping.cvid AND vtiger_cvadvfilter.groupid = vtiger_cvadvfilter_grouping.groupid')
						->where(['vtiger_customview.cvid' => $cvId, 'vtiger_cvadvfilter.groupid' => $relCriteriaGroup['groupid']])
						->orderBy('vtiger_cvadvfilter.columnindex')
						->createCommand()->query();
				if (!$dataReader->count()) {
					continue;
				}
				while ($relCriteriaRow = $dataReader->read()) {
					$criteria = $this->getAdvftCriteria($relCriteriaRow);
					$advftCriteria[$i]['columns'][$j] = $criteria;
					$advftCriteria[$i]['condition'] = $relCriteriaGroup['group_condition'];
					$j++;
				}
				if (!empty($advftCriteria[$i]['columns'][$j - 1]['column_condition'])) {
					$advftCriteria[$i]['columns'][$j - 1]['column_condition'] = '';
				}
				$i++;
			}
		} else {
			$fromFile = $this->getCustomViewFromFile($cvId)->getAdvftCriteria($this);
			$i = $fromFile[0];
			$j = $fromFile[1];
			$advftCriteria = $fromFile[2];
		}
		// Clear the condition (and/or) for last group, if any.
		if (!empty($advftCriteria[$i - 1]['condition'])) {
			$advftCriteria[$i - 1]['condition'] = '';
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
		$col = explode(':', $relCriteriaRow['columnname']);
		$tempVal = explode(',', $relCriteriaRow['value']);
		if ($col[4] === 'D' || ($col[4] === 'T' && $col[1] !== 'time_start' && $col[1] !== 'time_end') || ($col[4] === 'DT')) {
			$val = [];
			foreach ($tempVal as $key => $value) {
				if ($col[4] === 'D') {
					/**
					 * while inserting in db for due_date it was taking date and time values also as it is
					 * date time field. We only need to take date from that value
					 */
					if ($col[0] === 'vtiger_activity' && $col[1] === 'due_date') {
						$values = explode(' ', $value);
						$value = $values[0];
					}
					$val[$key] = (new \DateTimeField(trim($value)))->getDisplayDate();
				} elseif ($col[4] === 'DT') {
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
			'value' => $advFilterVal,
			'column_condition' => $relCriteriaRow['column_condition']
		];
	}
}
