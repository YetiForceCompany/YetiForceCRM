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
	 * Custom view instance.
	 *
	 * @var \App\CustomView
	 */
	private $customView;

	/**
	 * Custom view names.
	 *
	 * @var array
	 */
	private $viewNames = [];

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
	 * Should color be taken from filters?
	 *
	 * @var bool
	 */
	private $colorsFromFilter = false;

	/**
	 * Do we have sectors?
	 *
	 * @var string[]
	 */
	private $sectors = [];

	/**
	 * Sector values.
	 *
	 * @var array
	 */
	private $sectorValues = [];

	/**
	 * Num rows for the sectors.
	 *
	 * @var array
	 */
	private $sectorNumRows = [];

	/**
	 * All fields for the module.
	 *
	 * @var array
	 */
	private $fields = [];

	/**
	 * Names of the additional filters fileds.
	 *
	 * @var array
	 */
	private $additionalFiltersFieldsNames = [];

	/**
	 * Additional filters for chart.
	 *
	 * @var array
	 */
	private $additionalFiltersFields = [];

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
			default:
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
	 * Determine if colors should be taken from filters.
	 *
	 * @return bool
	 */
	public function areColorsFromFilter()
	{
		return $this->colorsFromFilter;
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
	 * Do we have sectors?
	 *
	 * @return bool
	 */
	public function withSectors()
	{
		return count($this->sectors) > 0;
	}

	/**
	 * Get filters ids.
	 *
	 * @return int[]|string
	 */
	public function getFilterIds($asString = false)
	{
		if (!$asString) {
			return $this->filterIds;
		}
		return implode(',', $this->filterIds);
	}

	/**
	 * Get filter id.
	 *
	 * @param string|int $dividingValue
	 *
	 * @return int
	 */
	public function getFilterId($dividingValue = 0)
	{
		if ($this->isMultiFilter()) {
			// if chart is divided by filters we have couple of id
			return $this->filterIds[$dividingValue];
		}
		// if chart is divided by field or not divided at all it has only one filter
		return $this->filterIds[0];
	}

	/**
	 * Get additional filters fields.
	 *
	 * @return array
	 */
	public function getAdditionalFiltersFields()
	{
		return $this->additionalFiltersFields;
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
		foreach ($this->getRows() as $dividingValue => &$dividing) {
			if (!isset($chartData['datasets'][$datasetIndex])) {
				$chartData['datasets'][] = [
					'data' => [],
					'links' => [],
				];
			}
			// datasetIndex is for dividingValue
			$dataset = &$chartData['datasets'][$datasetIndex];
			if ($this->isMultiFilter()) {
				$dataset['label'] = $this->getViewNameFromId($this->getFilterId($dividingValue));
			} elseif ($this->isDividedByField()) {
				$dataset['label'] = $dividingValue;
			}
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
		$chartData['valueType'] = $this->valueType;
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
		if ((!empty($group['color_id']) && !empty($this->colors[$group['color_id']])) || !isset($dividing['color_id'])) {
			if (!isset($group['color_id'])) {
				$color = $this->getFieldValueColor($groupValue, $dividingValue);
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
		foreach ($chartData['datasets'] as &$dataset) {
			$dataset['backgroundColor'] = \App\Colors::EMPTY_COLOR;
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
		$color = App\Colors::EMPTY_COLOR;
		if ($this->areColorsFromDividingField()) {
			if (!empty($this->fieldValueColors[$dividingValue])) {
				$color = $this->colors[$this->fieldValueColors[$dividingValue]];
			}
		} elseif ($this->areColorsFromFilter()) {
			$color = $this->colors[$dividingValue];
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
		if ((!empty($group['color_id']) && !empty($this->colors[$group['color_id']])) || !isset($group['color_id'])) {
			if (!isset($group['color_id'])) {
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
		$this->colorsFrom = 'picklist';
		$this->colorsFromRow = 'picklist_id';
		$this->iterateAllRows(function ($row, $groupValue, $dividingValue, $rowIndex) use ($colors) {
			if (isset($colors[$row['picklist_id']])) {
				$this->colors[$row['picklist_id']] = $colors[$row['picklist_id']];
			}
		});
	}

	/**
	 * Set colors from filters.
	 */
	protected function setColorsFromFilters()
	{
		$this->colorsFrom = 'filters';
		$this->colorsFromRow = 'color';
		$colors = \App\Colors::getAllFilterColors();
		$this->iterateAllRows(function (&$row, $groupValue, $dividingValue, $rowIndex) use ($colors) {
			$this->colors[$dividingValue] = $colors[$this->filterIds[$dividingValue]];
		});
	}

	/**
	 * Set colors from assigned user.
	 */
	protected function setColorsFromAssignedUserId()
	{
		$this->colorsFrom = 'assigned_user_id';
		$this->colorsFromRow = 'assigned_user_id';
		$this->iterateAllRows(function ($row, $groupValue, $dividingValue, $rowIndex) {
			$this->colors[$row['assigned_user_id']] = \App\Fields\Owner::getColor($row['assigned_user_id']);
		});
	}

	/**
	 * Set colors from record id.
	 */
	protected function setColorsFromRecordId()
	{
		$this->colorsFrom = 'record_id';
		$this->colorsFromRow = 'id';
		$this->iterateAllRows(function ($row, $groupValue, $dividingValue, $rowIndex) {
			$this->colors[$row['id']] = \App\Colors::getRandomColor('from_id_' . $row['id']);
		});
	}

	/**
	 * Set colors from record number (array index).
	 */
	protected function setColorsFromRecordNumber()
	{
		$this->colorsFrom = 'record_number';
		$this->colorsFromRow = 'record_number';
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
			case 'filters':
				$this->setColorsFromFilters();
				break;
			case 'picklist':
				$this->setColorsFromPickList();
				break;
			case 'assigned_user_id':
				$this->setColorsFromAssignedUserId();
				break;
			case 'record_id':
				$this->setColorsFromRecordId();
				break;
			case 'record_number':
				$this->setColorsFromRecordNumber();
				break;
			default:
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
			if ((!$this->isDividedByField() || !$this->areColorsFromDividingField()) && in_array($this->groupName, $picklists, true)) {
				$primaryKey = App\Fields\Picklist::getPickListId($this->groupName);
				$fieldTable = 'vtiger_' . $this->groupName;
				$query->leftJoin($fieldTable, "{$this->groupFieldModel->table}.{$this->groupFieldModel->column} = {$fieldTable}.{$this->groupName}");
				$query->addSelect(['picklist_id' => "$fieldTable.$primaryKey"]);
			}
			if ($this->isDividedByField() && $this->areColorsFromDividingField() && in_array($this->dividingName, $picklists, true)) {
				$primaryKey = App\Fields\Picklist::getPickListId($this->dividingName);
				$fieldTable = 'vtiger_' . $this->dividingName;
				$query->leftJoin($fieldTable, "{$this->dividingFieldModel->table}.{$this->dividingFieldModel->column} = {$fieldTable}.{$this->dividingName}");
				$query->addSelect(['picklist_id' => "$fieldTable.$primaryKey"]);
			}
		}
		return $query;
	}

	/**
	 * Get query for specified filter.
	 *
	 * @param $filter
	 *
	 * @return \App\Db\Query
	 */
	protected function getQuery($filter)
	{
		$request = \App\Request::init();
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
		if ($params = App\Condition::validSearchParams($this->getTargetModule(), $request->getArray('search_params'))) {
			$this->searchParams = $params;
			$transformedSearchParams = $queryGenerator->parseBaseSearchParamsToCondition($params);
			$queryGenerator->parseAdvFilter($transformedSearchParams);
		}
		return $this->addPicklistsToQuery($queryGenerator->createQuery());
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
		foreach ($this->data as &$dividing) {
			foreach ($dividing as $groupValueKey => &$values) {
				// iterate data one more time to search other group values
				$values[$this->valueType] = (float) $values[$this->valueType];
				foreach ($values as $valueKey => $value) {
					foreach ($this->data as &$otherDividing) {
						if (!isset($otherDividing[$groupValueKey][$valueKey])) {
							// if record doesn't have this value,
							// doesn't have records with picklist value that other records have
							// if we doesn't have picklist_id we can't set up color_id (picklist_id)
							// for example this could be work_time but current user is just signed (no work time)
							// set this as null or 0 (if it is valueType)
							// 0 is for chart data (0 work time),
							// null is used to find out missing color (maybe other purpose as well)
							// null colors will be replaced in the last stage getChartData when all colors are already set
							if ($valueKey !== $this->valueType) {
								$otherDividing[$groupValueKey][$valueKey] = null;
							} else {
								$otherDividing[$groupValueKey][$valueKey] = 0;
							}
						}
					}
				}
			}
		}
		unset($group, $values);
		$groupCalculate = $this->groupFieldModel->isCalculateField();
		ksort($this->data, SORT_LOCALE_STRING);
		foreach ($this->data as &$dividing) {
			if ($groupCalculate) {
				ksort($dividing, SORT_NUMERIC);
			} else {
				ksort($dividing, SORT_LOCALE_STRING);
			}
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
		if (isset($this->extraData['valueType'])) {
			$this->valueType = $this->extraData['valueType'];
		}
		if (isset($this->extraData['valueField'])) {
			$this->valueName = $this->extraData['valueField'];
		}
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
						$group['avg'] = (float) $group['avg'] / $this->numRows[$dividingValue][$groupValue];
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
	 * Add row.
	 *
	 * @param array      $row
	 * @param string|int $groupValue
	 * @param string|int $dividingValue
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
	 * @param int        $index         row index
	 * @param string|int $groupValue
	 * @param string|int $dividingValue
	 */
	protected function getCurrentRows($groupValue, $dividingValue)
	{
		return $this->rows[$dividingValue][$groupValue];
	}

	/**
	 * Get rows for dividing field chart.
	 *
	 * @param \App\QueryGenerator $query
	 * @param string|int          $dividingValue
	 *
	 * @return array
	 */
	protected function getRowsDb($query, $dividingValue)
	{
		$dataReader = $query->createCommand()->query();
		while ($row = $dataReader->read()) {
			[$groupValue, $dividingValue] = $this->getFieldValuesFromRow($row, $dividingValue);
			$this->addRow($row, $groupValue, $dividingValue);
			if (!empty($row[$this->groupName])) {
				$this->incNumRows($groupValue, $dividingValue);
			}
			if (!empty($this->extraData['showOwnerFilter']) && !empty($row['assigned_user_id'])) {
				$this->owners[] = $row['assigned_user_id'];
			}
			$this->setValueFromRow($row, $groupValue, $dividingValue);
		}
		$dataReader->close();
		return $this->data;
	}

	/**
	 * Get rows.
	 *
	 * @return array
	 */
	protected function getRows()
	{
		$this->setUpModelFields();
		// dividing value could be int (query index) or if divided by field - field value
		// could be also 0 for simple charts
		if ($this->withSectors()) {
			$query = $this->getQuery($this->filterIds[0]);
			$this->getRowsDb($query, 0);
			return $this->generateSectorsData();
		}
		if ($this->isMultiFilter()) {
			foreach ($this->getQueries() as $dividingValue => $query) {
				$this->getRowsDb($query, $dividingValue);
			}
		} else {
			$query = $this->getQuery($this->filterIds[0]);
			$this->getRowsDb($query, 0);
		}
		$this->calculateAverage();
		$this->normalizeData();
		$this->setColorsFrom($this->findOutColorsFromRows());
		$this->iterateAllRows(function ($row, $groupValue, $dividingValue, $rowIndex) {
			$this->setColorFromRow($row, $groupValue, $dividingValue);
		});
		return $this->data;
	}

	/**
	 * Get value from db record.
	 *
	 * @param array $row
	 *
	 * @return mixed
	 */
	protected function getValueFromRow($row)
	{
		$value = empty($row[$this->valueName]) ? 0 : 1;
		if (isset($row[$this->valueName])) {
			$value = is_numeric($row[$this->valueName]) ? (float) $row[$this->valueName] : $value;
		}
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
		if ($this->areColorsFromFilter()) {
			return 'filters';
		}
		$picklist = false;
		$assignedUserId = false;
		$recordId = false;
		foreach ($this->rows as $dividing) {
			foreach ($dividing as $group) {
				foreach ($group as $row) {
					if (!empty($row['picklist_id'])) {
						$picklist = true;
					} elseif (!empty($row['assigned_user_id'])) {
						$assignedUserId = true;
					} elseif (!empty($row['id'])) {
						$recordId = true;
					}
				}
			}
		}
		if ($picklist) {
			return 'picklist';
		}
		if ($assignedUserId) {
			return 'assigned_user_id';
		}
		if ($recordId) {
			return 'record_id';
		}
		return 'record_number';
	}

	/**
	 * Set color for row in $this->data.
	 *
	 * @param array $row
	 * @param mixed $groupValue
	 * @param mixed $dividingValue
	 */
	protected function setColorFromRow($row, $groupValue, $dividingValue)
	{
		$colorId = null;
		if ($this->colorsFrom !== 'record_number' && $this->colorsFrom !== 'filters') {
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
	 * @param array $row
	 * @param mixed $groupValue
	 * @param mixed $dividingValue
	 *
	 * @throws Exceptions\AppException
	 */
	protected function setLinkFromRow($row, $groupValue, $dividingValue)
	{
		if (!isset($this->data[$groupValue][$dividingValue]['link'])) {
			$operator = 'e';
			if ($this->groupFieldModel->isReferenceField()) {
				$operator = 'a';
			}
			$params = array_merge($this->searchParams, [[$this->groupFieldName, $operator, $row[$this->groupName]]]);
			if ($this->isDividedByField()) {
				$params = array_merge($params, [[$this->dividingFieldName, $operator, $row[$this->dividingName]]]);
			}
			$link = $this->getTargetModuleModel()->getListViewUrl() . '&viewname=' . $this->getFilterId($dividingValue) . '&search_params=' . App\Json::encode([$params]);
			$this->addValue('link', $link, $groupValue, $dividingValue);
		}
	}

	/**
	 * Get field values from row.
	 * We are operating on groupValue and dividingValue regularly so this fn will return this values from row.
	 *
	 * @param array      $row
	 * @param string|int $dividingValue
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
	 * @param mixed      $value
	 * @param string|int $groupValue
	 * @param string|int $dividingValue
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

	/**
	 * Add value to rows (other than $this->valueType).
	 *
	 * @param string     $valueType
	 * @param mixed      $value
	 * @param string|int $groupValue
	 * @param string|int $dividingValue
	 */
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
	 * @param array      $row
	 * @param string|int $groupValue
	 * @param string|int $dividingValue
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
	 * Get sector.
	 *
	 * @param int $value
	 *
	 * @return int
	 */
	protected function getSectorForValue($value)
	{
		$sectorId = false;
		foreach ($this->sectors as $sectorValue) {
			if ((float) $value <= (float) $sectorValue) {
				$sectorId = (float) $sectorValue;
				break;
			}
		}
		return $sectorId;
	}

	/**
	 * Get concrete value from data.
	 *
	 * @param string     $valueType
	 * @param string|int $groupValue
	 * @param string|int $dividingValue
	 *
	 * @return int
	 */
	protected function getValue($valueType, $groupValue, $dividingValue)
	{
		return isset($this->data[$dividingValue][$groupValue][$valueType]) ? $this->data[$dividingValue][$groupValue][$valueType] : 0;
	}

	/**
	 * Convert collected sectors to data (funnel chart).
	 *
	 * @return array
	 */
	protected function convertSectorsToData()
	{
		$this->data = [];
		$this->data[0] = [];
		foreach ($this->sectorValues as $sectorId => $value) {
			$this->data[0][$sectorId] = $value;
		}
		return $this->data;
	}

	/**
	 * Generate sectors data.
	 *
	 * @return array
	 */
	protected function generateSectorsData()
	{
		// in funnel chart there is only one dividingValue 0 so it will iterate only once like flat array
		$this->iterateAllRows(function ($row, $groupValue, $dividingValue, $rowIndex) {
			$value = (float) $row[$this->valueName];
			$sectorId = $this->getSectorForValue($value);
			$sectorIndex = array_search($sectorId, $this->sectors);
			$previousSectorValue = $this->sectors[$sectorIndex - 1];
			if (!isset($this->sectorNumRows[$sectorId])) {
				$this->sectorNumRows[$sectorId] = 0;
			}
			$this->sectorNumRows[$sectorId]++;
			switch ($this->valueType) {
				case 'count':
					$this->sectorValues[$sectorId][$this->valueType]++;
					break;
				case 'sum':
				case 'avg':
					$this->sectorValues[$sectorId][$this->valueType] += $value;
					break;
				default:
					break;
			}
			$params = array_merge($this->searchParams, [[$this->valueName, 'm', $sectorId]]);
			if ($previousSectorValue !== null) {
				$params[] = [$this->valueName, 'g', $previousSectorValue];
			}
			$this->sectorValues[$sectorId]['link'] = $this->getTargetModuleModel()->getListViewUrl() . '&viewname=' . $this->getFilterId($dividingValue) . '&search_params=' . App\Json::encode([$params]);
			$this->sectorValues[$sectorId]['color_id'] = $sectorId;
			$this->colors[$sectorId] = \App\Colors::getRandomColor('generated_' . $sectorId);
		});
		if ($this->valueType === 'avg') {
			foreach ($this->sectorValues as $sectorId => $value) {
				$this->sectorValues[$sectorId][$this->valueType] = $this->sectorValues[$sectorId][$this->valueType] / $this->sectorNumRows[$sectorId];
			}
		}
		// switch $this->sectorValues to $this->data
		return $this->convertSectorsToData();
	}

	/**
	 * Get owners list from result data.
	 *
	 * @return array
	 */
	public function getRowsOwners()
	{
		$ownersArray = [];
		foreach (array_unique($this->owners) as $ownerId) {
			$ownersArray[$ownerId] = App\Fields\Owner::getLabel($ownerId);
		}
		return $ownersArray;
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
		$this->customView = \App\CustomView::getInstance($this->getTargetModule());

		foreach (explode(',', $this->widgetModel->get('filterid')) as $id) {
			$this->filterIds[] = (int) $id;
			$this->viewNames[$id] = $this->customView->getInfoFilter((int) $id)['viewname'] ?? '';
		}
		return $this->filterIds;
	}

	/**
	 * Get headers from list view that are used in chart.
	 */
	private function setChartHeaders()
	{
		foreach ($this->additionalFiltersFieldsNames as $fieldName) {
			$this->additionalFiltersFields[] = $this->targetModuleModel->getFieldByName($fieldName);
		}
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
		$this->extraData = App\Json::decode($this->widgetModel->get('data'));
		$this->getTargetModuleModel();
		$this->setFilterIds();
		// Decode data if not done already.
		if (is_string($this->extraData)) {
			$this->extraData = \App\Json::decode(App\Purifier::decodeHtml($this->extraData));
		}
		if ($this->extraData === null) {
			throw new App\Exceptions\AppException('Invalid data');
		}
		$this->additionalFiltersFieldsNames = empty($this->extraData['additionalFiltersFields']) ? [] : (array) $this->extraData['additionalFiltersFields'];
		$this->setChartHeaders();
		$this->chartType = $this->extraData['chartType'];
		$this->groupName = !empty($this->extraData['groupField']) ? $this->extraData['groupField'] : null;
		$this->stacked = !empty($this->extraData['stacked']);
		$this->sectors = empty($this->extraData['sectorField']) ? [] : $this->extraData['sectorField'];
		$this->dividingFieldName = 0;
		if (!$this->isMultiFilter()) {
			$this->dividingName = !empty($this->extraData['dividingField']) ? $this->extraData['dividingField'] : null;
			if ($this->dividingName) {
				$this->colorsFromDividingField = !empty($this->extraData['colorsFromDividingField']);
			}
		} else {
			$this->colorsFromFilter = !empty($this->extraData['colorsFromFilter']);
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
	 * Get view name.
	 *
	 * @param int $cvid
	 */
	protected function getViewNameFromId($cvid)
	{
		return $this->viewNames[$cvid];
	}

	/**
	 * Get view id from view name.
	 *
	 * @param string $viewName
	 *
	 * @return int|null|string
	 */
	protected function getViewIdFromName($viewName)
	{
		foreach ($this->viewNames as $cvid => $vName) {
			if ($viewName === $vName) {
				return $cvid;
			}
		}
		return null;
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
			$viewName = (new App\Db\Query())->select(['viewname'])->from(['vtiger_customview'])->where(['cvid' => $this->getFilterId(0)])->scalar();
			if ($viewName) {
				$suffix = ' - ' . \App\Language::translate($viewName, $this->getTargetModule());
				if (!empty($this->extraData['groupField'])) {
					$fieldModel = Vtiger_Field_Model::getInstance($this->extraData['groupField'], $this->getTargetModuleModel());
					$suffix .= ' - ' . \App\Language::translate($fieldModel->getFieldLabel(), $this->getTargetModule());
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
		if (count($this->getFilterIds()) > 1) {
			return null;
		} else {
			return 'index.php?module=' . $this->getTargetModule() . '&action=Pagination&mode=getTotalCount&viewname=' . $this->getFilterId(0);
		}
	}

	/**
	 * Get list view url.
	 *
	 * @return string
	 */
	public function getListViewURL($dividingValue = 0)
	{
		return 'index.php?module=' . $this->getTargetModule() . '&view=List&viewname=' . $this->getFilterId($dividingValue);
	}

	public function isColor()
	{
		return false;
	}

	/**
	 * Get all available fields for additional filter.
	 *
	 * @return array
	 */
	public function getFields()
	{
		if (!$this->fields) {
			$moduleBlockFields = Vtiger_Field_Model::getAllForModule($this->targetModuleModel);
			$this->fields = [];
			foreach ($moduleBlockFields as $moduleFields) {
				foreach ($moduleFields as $moduleField) {
					$block = $moduleField->get('block');
					if (empty($block)) {
						continue;
					}
					$this->fields[$moduleField->get('name')] = $moduleField;
				}
			}
		}
		return $this->fields;
	}
}
