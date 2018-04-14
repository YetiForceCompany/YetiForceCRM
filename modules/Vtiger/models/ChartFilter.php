<?php
/**
 * Model widget chart with a filter.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Tomasz Kur <t.kur@yetiforce.com>
 */

/**
 * Widget chart model with a filter.
 */
class Vtiger_ChartFilter_Model extends Vtiger_Widget_Model
{
	public const PICKLIST = 'picklist';
	public const ASSIGNED_USER_ID = 'assigned_user_id';
	public const RECORD_ID = 'record_id';
	public const RECORD_NUMBER = 'record_number';
	public const ROW_PICKLIST = 'picklist_id';
	public const ROW_ASSIGNED_USER_ID = 'assigned_user_id';
	public const ROW_RECORD_ID = 'id';
	public const ROW_RECORD_NUMBER = 'record_number';
	public const EMPTY_COLOR = 'rgba(0,0,0,0.1)';

	/**
	 * Widget model.
	 *
	 * @var \Vtiger_Widget_Model
	 */
	private $widgetModel;

	/**
	 * Filter ids.
	 *
	 * @var int[]
	 */
	private $filterIds = [];

	/**
	 * Extra data.
	 *
	 * @var array
	 */
	private $extraData;

	/**
	 * Target module model.
	 *
	 * @var \Vtiger_Module_Model
	 */
	private $targetModuleModel;

	/**
	 * Query generator queries (for multiple filters).
	 *
	 * @var array
	 */
	private $queries = [];

	/**
	 * Query generator module name.
	 *
	 * @var string[]
	 */
	private $queryGeneratorModuleName;

	/**
	 * Type of the chart 'Bar','Line' etc.
	 *
	 * @var string
	 */
	private $chartType;

	/**
	 * Value type from extra data.
	 *
	 * @var string
	 */
	private $valueType;

	/**
	 * Value name from extra data.
	 *
	 * @var string
	 */
	private $valueName;

	/**
	 * Group field name.
	 *
	 * @var string
	 */
	private $groupFieldName;

	/**
	 * Group name (database table compatible).
	 *
	 * @var string
	 */
	private $groupName;

	/**
	 * Group field model.
	 *
	 * @var Vtiger_Field_Model
	 */
	private $groupFieldModel;

	/**
	 * Dividing field name.
	 *
	 * @var string
	 */
	private $dividingFieldName;

	/**
	 * Dividing name (database compatible).
	 *
	 * @var string
	 */
	private $dividingName;

	/**
	 * Divide field model (for stacked/dividing charts).
	 *
	 * @var \Vtiger_Module_Model
	 */
	private $dividingFieldModel;

	/**
	 * Chart has stacked scales ?
	 *
	 * @var bool
	 */
	private $stacked = false;

	/**
	 * Should colors be taken from dividing field? (or group field).
	 *
	 * @var bool
	 */
	private $colorsFromDividingField = false;

	/**
	 * Url search params.
	 *
	 * @var array
	 */
	private $searchParams = [];

	/**
	 * Owners list.
	 *
	 * @var array
	 */
	private $owners = [];

	/**
	 * Colors.
	 *
	 * @var string[]
	 */
	private $colors = [];

	/**
	 * Colors that was used in data already
	 * grouped by $groupValue or $dividingValue - it depends on areColorsFromDividingField.
	 *
	 * @var string[]
	 */
	private $fieldValueColors = [];

	/**
	 * Rows from query.
	 *
	 * @var array
	 */
	private $rows = [];

	/**
	 * Main object we are working on.
	 *
	 * @var array
	 */
	private $data = [];

	/**
	 * Total number of rows grouped by fieldName and displayValue.
	 *
	 * @var array ['leadstatus']['Odrzucone'] === 2
	 */
	private $numRows = [];

	/**
	 * Colors are from picklist? assigned_user_id? row id? record number?
	 * contain value from const (look at the top).
	 *
	 * @var string
	 */
	private $colorsFrom;

	/**
	 * Same as above but in ROW_ format look at the top const.
	 *
	 * @var string
	 */
	private $colorsFromRow;

	/**
	 * Colors for dataset color generation.
	 *
	 * @var array
	 */
	private $singleColors = [];

	/**
	 * Get instance.
	 *
	 * @param int $linkId
	 * @param int $userId
	 *
	 * @return \self
	 */
	public static function getInstance($linkId = 0, $userId = 0)
	{
		return new self();
	}

	/**
	 * Get type.
	 *
	 * @return string
	 */
	public function getType($lowerCase = false)
	{
		// backward compatibility fix
		$type = $this->chartType;
		switch ($type) {
			case 'Barchat':
				$type = 'Bar';
				break;
			case 'Bardividing':
				$type = 'Bar';
				break;
		}
		return $lowerCase ? strtolower($type) : $type;
	}

	/**
	 * Is chart grouped by field?
	 *
	 * @return bool
	 */
	public function isGrouped()
	{
		return !empty($this->groupName);
	}

	/**
	 * Do we have multiple filters?
	 *
	 * @return bool
	 */
	public function isMultiFilter()
	{
		return count($this->filterIds) > 1;
	}

	/**
	 * Is chart divided (grouped by two fields).
	 *
	 * @return bool
	 */
	public function isDividedByField()
	{
		return !empty($this->dividingName);
	}

	/**
	 * Determine if chart is stacked.
	 *
	 * @return bool
	 */
	public function isStacked()
	{
		return $this->stacked;
	}

	/**
	 * Determine if colors should be taken from dividingField.
	 *
	 * @return bool
	 */
	public function areColorsFromDividingField()
	{
		return $this->colorsFromDividingField;
	}

	/**
	 * Some of chart types doesn't have colors for each data.
	 *
	 * @return bool
	 */
	public function isSingleColored()
	{
		return $this->getType(true) === 'line' || $this->getType(true) === 'lineplain';
	}

	/**
	 * Get chart data.
	 *
	 * @return array
	 */
	public function getChartData()
	{
		$chartData = [
			'labels' => [],
			'datasets' => [],
			'show_chart' => false,
		];
		$datasetIndex = 0;
		$datasetsDividings = [];
		foreach ($this->getRows() as $dividingValue => &$dividing) {
			if (!isset($chartData['datasets'][$datasetIndex])) {
				$chartData['datasets'][] = [
					'data' => [],
					'links' => [],
				];
			}
			// datasetIndex is for dividingValue
			$dataset = &$chartData['datasets'][$datasetIndex];
			$datasetsDividings[$datasetIndex] = $dividingValue;
			foreach ($dividing as $groupValue => &$group) {
				if (!in_array($groupValue, $chartData['labels'])) {
					$chartData['labels'][] = $groupValue;
				}
				$dataset['data'][] = $group[$this->valueType];
				if (!empty($group['link']) || $group['link'] === null) {
					$dataset['links'][] = $group['link'];
				}
				foreach ($chartData['datasets'] as $datasetIndex => &$dataset) {
					if (!$this->isSingleColored()) {
						$this->setChartDatasetsColorsMulti($chartData, $datasetIndex, $dataset, $groupValue, $group, $dividingValue, $dividing);
					} else {
						$this->setChartDatasetsColorsSingle($chartData, $datasetIndex, $dataset, $groupValue, $group, $dividingValue, $dividing);
					}
				}
				$chartData['show_chart'] = true;
			}
			unset($dataset, $group);
			$datasetIndex++;
		}
		unset($dividing);
		if ($this->isSingleColored()) {
			$this->buildSingleColors($chartData);
		}
		return $chartData;
	}

	/**
	 * Gather information about data colors
	 * Later we can build gradient or generate one color for line charts.
	 *
	 * @param $chartData
	 * @param $datasetIndex
	 * @param $dataset
	 * @param $groupValue
	 * @param $group
	 * @param $dividingValue
	 * @param $dividing
	 */
	protected function setChartDatasetsColorsSingle(&$chartData, $datasetIndex, $dataset, $groupValue, $group, $dividingValue, $dividing)
	{
		if (!isset($this->singleColors[$datasetIndex])) {
			$this->singleColors[$datasetIndex] = [];
		}
		if ((!empty($group['color_id']) && !empty($this->colors[$group['color_id']])) || $dividing['color_id'] === null) {
			if ($group['color_id'] === null) {
				// we have all fields colors
				// if some record doesn't have field which have color use color from other dataset
				$color = static::EMPTY_COLOR; // transparent color if records doesn't have wanted colors (what can i do?)
				if ($this->areColorsFromDividingField()) {
					if (!empty($this->fieldValueColors[$dividingValue])) {
						$colorId = $this->fieldValueColors[$dividingValue];
					}
				} else {
					if (!empty($this->fieldValueColors[$groupValue])) {
						$colorId = $this->fieldValueColors[$groupValue];
					}
				}
				if (isset($colorId)) {
					$color = $this->colors[$colorId];
				}
				$this->singleColors[$datasetIndex][] = $color;
				$chartData['datasets'][$datasetIndex]['pointBackgroundColor'][] = $color;
			} else {
				$chartData['datasets'][$datasetIndex]['pointBackgroundColor'][] = $this->colors[$group['color_id']];
				$this->singleColors[$datasetIndex][] = $this->colors[$group['color_id']];
			}
		}
	}

	/**
	 * Build single color from array of dataset colors
	 * It could be used to generate gradient for line charts or return one color that will represent line background.
	 *
	 * @param $chartData
	 */
	protected function buildSingleColors(&$chartData)
	{
		foreach ($chartData['datasets'] as $datasetIndex => &$dataset) {
			$dataset['backgroundColor'] = static::EMPTY_COLOR;
		}
	}

	/**
	 * Get color from existing colors.
	 *
	 * @param $groupValue
	 * @param $dividingValue
	 *
	 * @return string
	 */
	protected function getFieldValueColor($groupValue, $dividingValue)
	{
		$color = static::EMPTY_COLOR;
		if ($this->areColorsFromDividingField()) {
			if (!empty($this->fieldValueColors[$dividingValue])) {
				$color = $this->colors[$this->fieldValueColors[$dividingValue]];
			}
		} else {
			if (!empty($this->fieldValueColors[$groupValue])) {
				$color = $this->colors[$this->fieldValueColors[$groupValue]];
			}
		}
		return $color;
	}

	/**
	 * By default all charts except line can have multiple colors in dataset
	 * each data should have individual color this function is trying to get color from couple sources if available.
	 *
	 * @param $chartData
	 * @param $datasetIndex
	 * @param $dataset
	 * @param $groupValue
	 * @param $group
	 * @param $dividingValue
	 * @param $dividing
	 */
	protected function setChartDatasetsColorsMulti(&$chartData, $datasetIndex, $dataset, $groupValue, $group, $dividingValue, $dividing)
	{
		if ((!empty($group['color_id']) && !empty($this->colors[$group['color_id']])) || $group['color_id'] === null) {
			if ($group['color_id'] === null) {
				// we have all fields colors
				// if some record doesn't have a field which have color use color from other dataset which have same value
				$color = $this->getFieldValueColor($groupValue, $dividingValue);
				$chartData['datasets'][$datasetIndex]['backgroundColor'][] = $color;
				$chartData['datasets'][$datasetIndex]['pointBackgroundColor'][] = $color;
			} else {
				$chartData['datasets'][$datasetIndex]['backgroundColor'][] = $this->colors[$group['color_id']];
				$chartData['datasets'][$datasetIndex]['pointBackgroundColor'][] = $this->colors[$group['color_id']];
			}
		}
	}

	/**
	 * Iterate through all rows collected from db.
	 *
	 * @param {callback} $callback
	 */
	protected function iterateAllRows($callback)
	{
		foreach ($this->rows as $dividingValue => $groupRows) {
			foreach ($groupRows as $groupValue => $group) {
				foreach ($group as $rowIndex => $row) {
					$callback($row, $groupValue, $dividingValue, $rowIndex);
				}
			}
		}
	}

	/**
	 * Set colors from picklist.
	 */
	protected function setColorsFromPickList()
	{
		$fieldName = $this->areColorsFromDividingField() ? $this->dividingFieldName : $this->groupFieldName;
		$colors = \App\Fields\Picklist::getColors($fieldName);
		$this->colorsFrom = static::PICKLIST;
		$this->colorsFromRow = static::ROW_PICKLIST;
		$this->iterateAllRows(function ($row, $groupValue, $dividingValue, $rowIndex) use ($colors) {
			$this->colors[$row[static::ROW_PICKLIST]] = $colors[$row[static::ROW_PICKLIST]];
		});
	}

	/**
	 * Set colors from assigned user.
	 */
	protected function setColorsFromAssignedUserId()
	{
		$this->colorsFrom = static::ASSIGNED_USER_ID;
		$this->colorsFromRow = static::ROW_ASSIGNED_USER_ID;
		$this->iterateAllRows(function ($row, $groupValue, $dividingValue, $rowIndex) {
			$this->colors[$row[static::ROW_ASSIGNED_USER_ID]] = \App\Fields\Owner::getColor($row[static::ROW_ASSIGNED_USER_ID]);
		});
	}

	/**
	 * Set colors from record id.
	 */
	protected function setColorsFromRecordId()
	{
		$this->colorsFrom = static::RECORD_ID;
		$this->colorsFromRow = static::ROW_RECORD_ID;
		$this->iterateAllRows(function ($row, $groupValue, $dividingValue, $rowIndex) {
			$this->colors[$row[static::ROW_RECORD_ID]] = \App\Colors::getRandomColor('from_id_' . $row[static::ROW_RECORD_ID]);
		});
	}

	/**
	 * Set colors from record number (array index).
	 */
	protected function setColorsFromRecordNumber()
	{
		$this->colorsFrom = static::RECORD_NUMBER;
		$this->colorsFromRow = static::ROW_RECORD_NUMBER;
		$this->iterateAllRows(function ($row, $groupValue, $dividingValue, $rowIndex) {
			$this->colors[$groupValue] = \App\Colors::getRandomColor('generated_' . $groupValue);
		});
	}

	/**
	 * Set colors.
	 *
	 * @param {string} $from
	 */
	protected function setColorsFrom($from)
	{
		switch ($from) {
			case static::PICKLIST:
				$this->setColorsFromPickList();
				break;
			case static::ASSIGNED_USER_ID:
				$this->setColorsFromAssignedUserId();
				break;
			case static::RECORD_ID:
				$this->setColorsFromRecordId();
				break;
			case static::RECORD_NUMBER:
				$this->setColorsFromRecordNumber();
				break;
		}
	}

	/**
	 * Add query to get picklists id (for colors).
	 *
	 * @param $query
	 * @param $queryGenerator
	 *
	 * @return $query
	 */
	protected function addPicklistsToQuery($query)
	{
		if (!empty($this->groupName)) {
			$picklists = \App\Fields\Picklist::getModulesByName($this->queryGeneratorModuleName);
			if (!$this->isDividedByField() || !$this->areColorsFromDividingField()) {
				if (in_array($this->groupName, $picklists, true)) {
					$primaryKey = App\Fields\Picklist::getPickListId($this->groupName);
					$fieldTable = 'vtiger_' . $this->groupName;
					$query->leftJoin($fieldTable, "{$this->groupFieldModel->table}.{$this->groupFieldModel->column} = {$fieldTable}.{$this->groupName}");
					$query->addSelect([static::ROW_PICKLIST => "$fieldTable.$primaryKey"]);
				}
			}
			if ($this->isDividedByField() && $this->areColorsFromDividingField()) {
				if (in_array($this->dividingName, $picklists, true)) {
					$primaryKey = App\Fields\Picklist::getPickListId($this->dividingName);
					$fieldTable = 'vtiger_' . $this->dividingName;
					$query->leftJoin($fieldTable, "{$this->dividingFieldModel->table}.{$this->dividingFieldModel->column} = {$fieldTable}.{$this->dividingName}");
					$query->addSelect([static::ROW_PICKLIST => "$fieldTable.$primaryKey"]);
				}
			}
		}
		return $query;
	}

	protected function getQuery($filter)
	{
		$queryGenerator = new \App\QueryGenerator($this->getTargetModule());
		$queryGenerator->initForCustomViewById($filter);
		$this->queryGeneratorModuleName = $queryGenerator->getModuleModel()->getName();
		if (!empty($this->groupName)) {
			$queryGenerator->setField($this->groupName);
		}
		if (!empty($this->dividingName)) {
			$queryGenerator->setField($this->dividingName);
		}
		if (!empty($this->valueName)) {
			$queryGenerator->setField($this->valueName);
		}
		if ($this->has('owner') && !empty($this->extraData['showOwnerFilter']) && $this->get('owner') !== 0) {
			$queryGenerator->addCondition('assigned_user_id', $this->get('owner'), 'e');
			$queryGenerator->setField('assigned_user_id');
		}
		if ($this->has('time') && !empty($this->extraData['timeRange']) && $this->extraData['timeRange'] !== '-') {
			$time = $this->get('time');
			$timeFieldModel = Vtiger_Field_Model::getInstance($this->extraData['timeRange'], $this->getTargetModuleModel());
			if ($timeFieldModel) {
				$queryGenerator->addCondition($timeFieldModel->getName(), "{$time[0]} 00:00:00 , {$time[1]} 23:59:59", 'bw');
				$this->searchParams[] = [$timeFieldModel->getFieldName(), 'bw', "{$time[0]} , {$time[1]}"];
			}
		}
		$query = $queryGenerator->createQuery();
		// we want colors from picklists if available
		$query = $this->addPicklistsToQuery($query);
		return $query;
	}

	/**
	 * Get chart queries.
	 *
	 * @return \App\Db\Query[]
	 */
	protected function getQueries()
	{
		foreach ($this->filterIds as $filterId) {
			$this->queries[] = $this->getQuery($filterId);
		}
		return $this->queries;
	}

	/**
	 * Normalize dividing charts so they have equal number of data.
	 */
	protected function normalizeData()
	{
		$valueType = $this->extraData['valueType'];
		foreach ($this->data as $groupValueKey => &$group) {
			foreach ($group as $dividingValueKey => &$values) {
				// iterate data one more time to search other group values
				$values[$valueType] = (float) $values[$valueType];
				foreach ($values as $valueKey => $value) {
					foreach ($this->data as $otherGroupValueKey => &$otherGroup) {
						if (!isset($otherGroup[$dividingValueKey])) {
							$otherGroup[$dividingValueKey] = [];
						}
						if (!isset($otherGroup[$dividingValueKey][$valueKey])) {
							// if record doesn't have this value,
							// doesn't have records with picklist value that other records have
							// if we doesn't have picklist_id we can't set up color_id (picklist_id)
							// for example this could be work_time but current user is just signed (no work time)
							// set this as null or 0 (if it is valueType)
							// 0 is for chart data (0 work time),
							// null is used to find out missing color (maybe other purpose as well)
							// null colors will be replaced in the last stage getChartData when all colors are already set
							if ($valueKey !== $this->extraData['valueType']) {
								$otherGroup[$dividingValueKey][$valueKey] = null;
							} else {
								$otherGroup[$dividingValueKey][$valueKey] = 0;
							}
						}
					}
				}
			}
		}
		unset($group, $values);
		foreach ($this->data as &$dividing) {
			ksort($dividing);
			foreach ($dividing as &$group) {
				ksort($group);
			}
		}
	}

	/**
	 * Set up model fields.
	 */
	protected function setUpModelFields()
	{
		$this->valueType = $this->extraData['valueType'];
		$this->valueName = $this->extraData['valueField'];
		$this->groupFieldModel = Vtiger_Field_Model::getInstance($this->extraData['groupField'], $this->getTargetModuleModel());
		$this->groupFieldName = $this->groupFieldModel->getFieldName();
		$this->groupName = $this->groupFieldModel->getName();
		if ($this->isDividedByField()) {
			$this->dividingFieldModel = Vtiger_Field_Model::getInstance($this->extraData['dividingField'], $this->getTargetModuleModel());
			$this->dividingFieldName = $this->dividingFieldModel->getFieldName();
			$this->dividingName = $this->dividingFieldModel->getName();
		}
	}

	/**
	 * Calculate average data if needed.
	 */
	protected function calculateAverage()
	{
		if ($this->valueType === 'avg') {
			foreach ($this->data as $dividingValue => &$dividing) {
				foreach ($dividing as $groupValue => &$group) {
					if ($group['avg']) {
						$group['avg'] = (float) $group['avg'] / $this->numRows[$groupValue][$dividingValue];
					}
				}
			}
		}
	}

	/**
	 * Increase number of rows for average calculation.
	 *
	 * @param $groupValue
	 * @param $dividingValue
	 */
	protected function incNumRows($groupValue, $dividingValue)
	{
		if (!isset($this->numRows[$dividingValue])) {
			$this->numRows[$dividingValue] = [];
		}
		if (!isset($this->numRows[$dividingValue][$groupValue])) {
			$this->numRows[$dividingValue][$groupValue] = 0;
		}
		$this->numRows[$dividingValue][$groupValue]++;
	}

	/**
	 * Add row to.
	 *
	 * @param $groupValue
	 * @param $dividingValue
	 */
	protected function addRow($row, $groupValue, $dividingValue)
	{
		if (!isset($this->rows[$dividingValue])) {
			$this->rows[$dividingValue] = [];
		}
		if (!isset($this->rows[$dividingValue][$groupValue])) {
			$this->rows[$dividingValue][$groupValue] = [];
		}
		$this->rows[$dividingValue][$groupValue][] = $row;
	}

	/**
	 * Get current rows.
	 *
	 * @param {int} $index row index
	 * @param $groupValue
	 * @param $dividingValue
	 */
	protected function getCurrentRows($groupValue, $dividingValue)
	{
		return $this->rows[$dividingValue][$groupValue];
	}

	/**
	 * Get rows for dividing field chart.
	 *
	 * @return array
	 */
	protected function _getRows($query, $dividingValue)
	{
		$dataReader = $query->createCommand()->query();
		while ($row = $dataReader->read()) {
			[$groupValue, $dividingValue] = $this->getFieldValuesFromRow($row, $dividingValue);
			$this->addRow($row, $groupValue, $dividingValue);
			if (!empty($row[$this->groupFieldName])) {
				$this->incNumRows($groupValue, $dividingValue);
			}
			if (!empty($this->extraData['showOwnerFilter'])) {
				$this->owners[] = $row['assigned_user_id'];
			}
			$this->setValueFromRow($row, $groupValue, $dividingValue);
		}
		$dataReader->close();
		$this->calculateAverage();
		//$this->normalizeData();
		return $this->data;
	}

	/**
	 * Get queries from filters.
	 *
	 * @return array
	 */
	protected function getRows()
	{
		$this->setUpModelFields();
		// dividing value could be int (query index) or if divided by field - field value
		// could be also 0 for simple charts
		if ($this->isMultiFilter()) {
			$queries = $this->getQueries();
			foreach ($queries as $dividingValue => $query) {
				$this->_getRows($query, $dividingValue);
			}
		} elseif ($this->isDividedByField()) {
		} else {
			$query = $this->getQuery($this->filterIds[0]);
			$this->_getRows($query, 0);
		}
		$this->setColorsFrom($this->findOutColorsFromRows());
		$this->iterateAllRows(function ($row, $groupValue, $dividingValue, $rowIndex) {
			$this->setColorFromRow($row, $groupValue, $dividingValue);
		});
		return $this->data;
	}

	/**
	 * Get value from db record.
	 *
	 * @param $row
	 *
	 * @return {mixed}
	 */
	protected function getValueFromRow($row)
	{
		$value = empty($row[$this->valueName]) ? 0 : 1;
		$value = is_numeric($row[$this->valueName]) ? $row[$this->valueName] : $value;
		if ($this->valueType === 'count') {
			$value = 1; // only counting records
		}
		return $value;
	}

	/**
	 * Find out on which color type we are operating.
	 *
	 * @return string
	 */
	protected function findOutColorsFromRows()
	{
		$picklist = false;
		$assignedUserId = false;
		$recordId = false;
		foreach ($this->rows as $dividingValue => $dividing) {
			foreach ($dividing as $groupValue => $group) {
				foreach ($group as $row) {
					if (!empty($row[static::ROW_PICKLIST])) {
						$picklist = true;
					} elseif (!empty($row[static::ROW_ASSIGNED_USER_ID])) {
						$assignedUserId = true;
					} elseif (!empty($row[static::ROW_RECORD_ID])) {
						$recordId = true;
					}
				}
			}
		}
		if ($picklist) {
			return static::PICKLIST;
		}
		if ($assignedUserId) {
			return static::ASSIGNED_USER_ID;
		}
		if ($recordId) {
			return static::RECORD_ID;
		}
		return static::RECORD_NUMBER;
	}

	/**
	 * Set color for row in $this->data.
	 *
	 * @param {int}   $rowIndex
	 * @param {array} $row
	 * @param {mixed} $groupValue
	 * @param {mixed} $dividingValue
	 */
	protected function setColorFromRow($row, $groupValue, $dividingValue)
	{
		$colorId = null;
		if ($this->colorsFrom !== static::RECORD_NUMBER) {
			$colorId = $row[$this->colorsFromRow];
		}
		$this->addValue('color_id', $colorId, $groupValue, $dividingValue);
		// store color for this field value
		if ($this->areColorsFromDividingField()) {
			$this->fieldValueColors[$dividingValue] = $colorId;
		} else {
			$this->fieldValueColors[$groupValue] = $colorId;
		}
	}

	/**
	 * Set link from row in $this->data.
	 *
	 * @param {array} $row
	 * @param {mixed} $groupValue
	 * @param {mixed} $dividingValue
	 *
	 * @throws \App\Exceptions\AppException
	 */
	protected function setLinkFromRow($row, $groupValue, $dividingValue)
	{
		if (!isset($this->data[$groupValue][$dividingValue]['link'])) {
			$searchParams = array_merge($this->searchParams, [[$this->groupFieldName, 'e', $row[$this->groupName]]]);
			if ($this->isDividedByField()) {
				$searchParams = array_merge($searchParams, [[$this->dividingFieldName, 'e', $row[$this->dividingName]]]);
			}
			$link = $this->getTargetModuleModel()->getListViewUrl() . '&viewname=' . $this->widgetModel->get('filterid') . '&search_params=' . App\Json::encode([$searchParams]);
			$this->addValue('link', $link, $groupValue, $dividingValue);
		}
	}

	/**
	 * Get field values from row.
	 * We are operating on groupValue and dividingValue regularly so this fn will return this values from row.
	 *
	 * @param $row
	 * @param $dividingValue
	 *
	 * @return array
	 */
	protected function getFieldValuesFromRow($row, $dividingValue)
	{
		$groupValue = $this->groupFieldModel->getDisplayValue($row[$this->groupName], false, false, true);
		if (empty($groupValue)) {
			$groupValue = '(' . \App\Language::translate('LBL_EMPTY', 'Home') . ')';
		}
		if ($this->isDividedByField()) {
			$dividingValue = $this->dividingFieldModel->getDisplayValue($row[$this->dividingName], false, false, true);
			if (empty($dividingValue)) {
				$dividingValue = '(' . \App\Language::translate('LBL_EMPTY', 'Home') . ')';
			}
		}
		return [$groupValue, $dividingValue];
	}

	/**
	 * Add data to data placeholder ;).
	 *
	 * @param {string} $valueType
	 * @param {mixed}  $value
	 * @param {string} $groupValue
	 * @param {string} $dividingValue
	 */
	protected function addData($value, $groupValue, $dividingValue)
	{
		if (!isset($this->data[$dividingValue])) {
			$this->data[$dividingValue] = [];
		}
		if (!isset($this->data[$dividingValue][$groupValue])) {
			$this->data[$dividingValue][$groupValue] = [];
		}
		if (!isset($this->data[$dividingValue][$groupValue][$this->valueType])) {
			$this->data[$dividingValue][$groupValue][$this->valueType] = $value;
		} elseif (is_numeric($this->data[$dividingValue][$groupValue][$this->valueType])) {
			$this->data[$dividingValue][$groupValue][$this->valueType] += $value;
		} else {
			$this->data[$dividingValue][$groupValue][$this->valueType] = $value;
		}
	}

	protected function addValue($valueType, $value, $groupValue, $dividingValue)
	{
		if (!isset($this->data[$dividingValue])) {
			$this->data[$dividingValue] = [];
		}
		if (!isset($this->data[$dividingValue][$groupValue])) {
			$this->data[$dividingValue][$groupValue] = [];
		}
		$this->data[$dividingValue][$groupValue][$valueType] = $value;
	}

	/**
	 * Get chart value for row (dividing chart).
	 *
	 * @param array $row
	 * @param       $groupValue
	 * @param       $dividingValue
	 *
	 * @return array
	 */
	protected function setValueFromRow($row, $groupValue, $dividingValue)
	{
		$value = $this->getValueFromRow($row);
		$this->addData($value, $groupValue, $dividingValue);
		$this->setLinkFromRow($row, $groupValue, $dividingValue);
	}

	/**
	 * Get chart value by group for row.
	 *
	 * @param array $sectorValues
	 * @param mixed $value
	 *
	 * @return array
	 */
	protected function getValueForSector($sectorValues, $value)
	{
		$sectorId = $this->getSector($value);
		if ($sectorId !== false) {
			switch ($this->extraData['valueType']) {
				case 'count':
					if (!isset($sectorValues[$sectorId])) {
						$sectorValues[$sectorId] = 1;
					} else {
						++$sectorValues[$sectorId];
					}
					break;
				case 'sum':
				case 'avg':
					if (!isset($sectorValues[$sectorId])) {
						$sectorValues[$sectorId] = (int) $value;
					} else {
						$sectorValues[$sectorId] += (int) $value;
					}
					break;
			}
		}
		return $sectorValues;
	}

	/**
	 * Get rows for funnel chart.
	 *
	 * @return array
	 */
	protected function getRowsFunnel()
	{
		$this->groupFieldModel = Vtiger_Field_Model::getInstance($this->extraData['groupField'], $this->getTargetModuleModel());
		$this->groupFieldName = $this->groupFieldModel->getFieldName();
		$count = $groupData = [];
		$sectors = $this->extraData['sectorField'];
		$dataReader = $this->getQuery()->createCommand()->query();
		while ($row = $dataReader->read()) {
			$sectorId = $this->getSector($sectors, $row[$this->groupName]);
			if (!empty($this->extraData['showOwnerFilter'])) {
				$this->owners[] = $row['assigned_user_id'];
			}
			if ($sectorId !== false) {
				if (!isset($count[$sectorId])) {
					$count[$sectorId] = 1;
				} else {
					++$count[$sectorId];
				}
			}
		}
		foreach ($sectors as $sectorId => &$sectorValue) {
			$displayValue = $this->groupFieldModel->getDisplayValue($sectorValue);
			$displayValue .= ' - (' . (int) $count[$sectorId] . ')';
			$groupData[$displayValue]['count'] = (int) $sectorValue;
			$searchParams = array_merge($this->searchParams, [[$this->groupName, 'm', $sectorValue]]);
			if ($sectorId != 0) {
				$searchParams[] = [$this->groupName, 'g', $sectors[$sectorId - 1]];
			}
			$groupData[$displayValue]['link'] = $this->getTargetModuleModel()->getListViewUrl() . '&viewname=' . $this->widgetModel->get('filterid') . '&search_params=' . App\Json::encode([$searchParams]);
		}
		$dataReader->close();
		return $groupData;
	}

	/**
	 * Get sector.
	 *
	 * @param int $value
	 *
	 * @return int
	 */
	protected function getSector($value)
	{
		$sectorId = false;
		foreach ($this->extraData['sectorField'] as $key => $sector) {
			if ($value <= $sector) {
				$sectorId = $key;
				break;
			}
		}
		return $sectorId;
	}

	/**
	 * Get owners list from result data.
	 *
	 * @return type
	 */
	public function getRowsOwners()
	{
		$owners = [];
		foreach (array_unique($this->owners) as $ownerId) {
			$owners[$ownerId] = App\Fields\Owner::getLabel($ownerId);
		}

		return $owners;
	}

	/**
	 * Get extra data.
	 *
	 * @param string $key
	 *
	 * @return mixed
	 */
	public function getExtraData($key)
	{
		return isset($this->extraData[$key]) ? $this->extraData[$key] : null;
	}

	/**
	 * Set filter ids.
	 *
	 * @return int[]
	 */
	private function setFilterIds()
	{
		foreach (explode(',', $this->widgetModel->get('filterid')) as $id) {
			$this->filterIds[] = (int) $id;
		}
		return $this->filterIds;
	}

	/**
	 * Set widget model.
	 *
	 * @param \Vtiger_Widget_Model $widgetModel
	 *
	 * @throws Exception
	 */
	public function setWidgetModel($widgetModel)
	{
		$this->widgetModel = $widgetModel;
		$this->extraData = $this->widgetModel->get('data');
		$this->filterIds = $this->setFilterIds();
		// Decode data if not done already.
		if (is_string($this->extraData)) {
			$this->extraData = \App\Json::decode(App\Purifier::decodeHtml($this->extraData));
		}
		if ($this->extraData === null) {
			throw new \App\Exceptions\AppException('Invalid data');
		}
		$this->chartType = $this->extraData['chartType'];
		$this->groupName = !empty($this->extraData['groupField']) ? $this->extraData['groupField'] : null;
		$this->stacked = !empty($this->extraData['stacked']);
		$this->dividingFieldName = 0;
		if (!$this->isMultiFilter()) {
			$this->dividingName = !empty($this->extraData['dividingField']) ? $this->extraData['dividingField'] : null;
			if ($this->dividingName) {
				$this->colorsFromDividingField = !empty($this->extraData['colorsFromDividingField']);
			}
		}
	}

	/**
	 * Get target module.
	 *
	 * @return string
	 */
	public function getTargetModule()
	{
		return $this->extraData['module'];
	}

	/**
	 * Get target module model.
	 *
	 * @return \Vtiger_Module_Model
	 */
	public function getTargetModuleModel()
	{
		if (!$this->targetModuleModel) {
			$this->targetModuleModel = Vtiger_Module_Model::getInstance($this->getTargetModule());
		}
		return $this->targetModuleModel;
	}

	/**
	 * Get title.
	 *
	 * @param string $prefix
	 *
	 * @return string
	 */
	public function getTitle($prefix = '')
	{
		$title = $this->widgetModel->get('title');
		if (empty($title)) {
			$suffix = '';
			$viewName = (new App\Db\Query())->select(['viewname'])->from(['vtiger_customview'])->where(['cvid' => $this->widgetModel->get('filterid')])->scalar();
			if ($viewName) {
				$suffix = ' - ' . \App\Language::translate($viewName, $this->getTargetModule());
				if (!empty($this->extraData['groupField'])) {
					$groupFieldModel = Vtiger_Field_Model::getInstance($this->extraData['groupField'], $this->getTargetModuleModel());
					$suffix .= ' - ' . \App\Language::translate($groupFieldModel->getFieldLabel(), $this->getTargetModule());
				}
			}
			return $prefix . \App\Language::translate($this->getTargetModuleModel()->label, $this->getTargetModule()) . $suffix;
		}
		return $title;
	}

	/**
	 * Get total count url.
	 *
	 * @return string
	 */
	public function getTotalCountURL()
	{
		return 'index.php?module=' . $this->getTargetModule() . '&action=Pagination&mode=getTotalCount&viewname=' . $this->widgetModel->get('filterid');
	}

	/**
	 * Get list view url.
	 *
	 * @return string
	 */
	public function getListViewURL()
	{
		return 'index.php?module=' . $this->getTargetModule() . '&view=List&viewname=' . $this->widgetModel->get('filterid');
	}

	public function isColor()
	{
		return false;
	}
}
