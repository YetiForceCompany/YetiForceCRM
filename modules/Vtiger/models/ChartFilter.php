<?php
/**
 * Model widget chart with a filter.
 *
 * @package Model
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Tomasz Kur <t.kur@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

/**
 * Widget chart model with a filter.
 */
class Vtiger_ChartFilter_Model extends \App\Base
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
	 * Sum value field object.
	 *
	 * @var \Vtiger_Field_Model
	 */
	private $valueFieldModel;

	/**
	 * Group field name.
	 *
	 * @var string
	 */
	private $groupColumnName;

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
	private $dividingColumnName;

	/**
	 * Dividing name (database compatible).
	 *
	 * @var string
	 */
	private $dividingName;

	/**
	 * Divide field model (for stacked/dividing charts).
	 *
	 * @var \Vtiger_Field_Model
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
	 * Do we have sector?
	 *
	 * @var string
	 */
	private $sectors;

	/**
	 * Sector values.
	 *
	 * @var array
	 */
	private $sectorValues = [];

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
	 * @param bool $lowerCase
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
	 * Check if widget is downloadable.
	 *
	 * @return bool
	 */
	public function isDownloadable(): bool
	{
		return 'Table' !== $this->getType();
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
		return \count($this->filterIds) > 1;
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
		return 'line' === $this->getType(true) || 'lineplain' === $this->getType(true);
	}

	/**
	 * Gets value type.
	 *
	 * @return string
	 */
	public function getValueType(): string
	{
		return $this->valueType;
	}

	/**
	 * Get filters ids.
	 *
	 * @return int[]
	 */
	public function getFilterIds(): array
	{
		return $this->filterIds;
	}

	/**
	 * Get filter id.
	 *
	 * @param int|string $dividingValue
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
		return $this->filterIds[0] ?? 0;
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
	 * Convert to user format.
	 *
	 * @param mixed $value
	 */
	public function convertToUserFormat($value)
	{
		if (!empty($this->valueFieldModel)) {
			$value = $this->valueFieldModel->getDisplayValue($value, false, false, true);
		} elseif (is_numeric($value)) {
			$value = \App\Fields\Double::formatToDisplay($value, false);
		}
		return $value;
	}

	/**
	 * Get chart data.
	 *
	 * @return array
	 */
	public function getChartData()
	{
		$this->setUpModelFields();
		if ('Table' === $this->chartType) {
			return $this->getRows();
		}
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
				if (!\in_array($groupValue, $chartData['labels'])) {
					$chartData['labels'][] = $groupValue;
				}
				$dataset['data'][] = $group[$this->valueType];
				if (\array_key_exists('link', $group)) {
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
			++$datasetIndex;
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
	 * @param array $chartData
	 * @param mixed $datasetIndex
	 * @param mixed $dataset
	 * @param mixed $groupValue
	 * @param array $group
	 * @param mixed $dividingValue
	 * @param mixed $dividing
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
	 * @param callable $callback
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
		$fieldName = $this->areColorsFromDividingField() ? $this->dividingName : $this->groupName;
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
			$this->colors[$dividingValue] = $colors[$this->filterIds[$dividingValue]] ?? null;
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
	 * @param string $from
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
	 * @param \App\QueryGenerator $queryGenerator
	 */
	protected function addPicklistsToQuery($queryGenerator)
	{
		if (!empty($this->groupName)) {
			if ($this->isDividedByField() && $this->areColorsFromDividingField() && \in_array($this->getTargetModuleModel()->getFieldByName($this->dividingName)->getFieldDataType(), ['picklist', 'multipicklist'])) {
				$primaryKey = App\Fields\Picklist::getPickListId($this->dividingName);
				$fieldTable = 'vtiger_' . $this->dividingName;
				$queryGenerator->addJoin(['LEFT JOIN', $fieldTable, "{$queryGenerator->getColumnName($this->dividingName)} = {$fieldTable}.{$this->dividingName}"]);
				$queryGenerator->setCustomColumn(['picklist_id' => new \yii\db\Expression("MAX({$fieldTable}.{$primaryKey})")]);
			} elseif ((!$this->isDividedByField() || !$this->areColorsFromDividingField()) && \in_array($this->getTargetModuleModel()->getFieldByName($this->groupName)->getFieldDataType(), ['picklist', 'multipicklist'])) {
				$primaryKey = App\Fields\Picklist::getPickListId($this->groupName);
				$fieldTable = 'vtiger_' . $this->groupName;
				$queryGenerator->addJoin(['LEFT JOIN', $fieldTable, "{$queryGenerator->getColumnName($this->groupName)} = {$fieldTable}.{$this->groupName}"]);
				$queryGenerator->setCustomColumn(['picklist_id' => new \yii\db\Expression("MAX({$fieldTable}.{$primaryKey})")]);
			}
		}
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
		if ($this->getExtraData('relation_id') && $this->getExtraData('recordId')) {
			$relationModelInstance = Vtiger_Relation_Model::getInstanceById($this->getExtraData('relation_id'));
			$relationModelInstance->set('parentRecord', Vtiger_Record_Model::getInstanceById($this->getExtraData('recordId'), \App\Module::getModuleName($this->widgetModel->get('tabid'))));
			$queryGenerator = $relationModelInstance->getQuery();
		} else {
			$queryGenerator = new \App\QueryGenerator($this->getTargetModule());
			$queryGenerator->initForCustomViewById($filter);
		}
		$queryGenerator->setFields(['id'])->setCustomColumn(['count' => new \yii\db\Expression('COUNT(1)'), 'id' => new \yii\db\Expression('MAX(' . $queryGenerator->getColumnName('id') . ')')]);
		foreach ([$this->groupName => $this->groupColumnName, $this->dividingName => $this->dividingColumnName] as $columnName => $groupBy) {
			if (!empty($columnName) && $columnName !== $groupBy) {
				$sqlColumnName = $queryGenerator->getColumnName($columnName);
				$queryGenerator->setCustomColumn([$columnName => new \yii\db\Expression("MAX({$sqlColumnName})")]);
				switch ($this->sectors) {
						case 'daily':
							if ('datetime' === $queryGenerator->getModuleField($columnName)->getFieldDataType()) {
								$queryGenerator->setCustomColumn([$groupBy => new \yii\db\Expression("CAST({$sqlColumnName} AS DATE)")]);
							} else {
								$queryGenerator->setCustomColumn([$groupBy => $sqlColumnName]);
							}
							break;
						case 'monthly':
							$queryGenerator->setCustomColumn([$groupBy => new \yii\db\Expression("SUBSTRING({$sqlColumnName}, 1, 7)")]);
							break;
						case 'yearly':
							$queryGenerator->setCustomColumn([$groupBy => new \yii\db\Expression("EXTRACT(YEAR FROM {$sqlColumnName})")]);
							break;
						default:
							$groupBy = $queryGenerator->getColumnName($columnName);
							break;
					}
				$queryGenerator->setCustomGroup($groupBy);
			} elseif (!empty($columnName)) {
				$queryGenerator->setField($columnName)->setGroup($columnName);
			}
		}
		if (!empty($this->valueName)) {
			$queryGenerator->setField($this->valueName)
				->setCustomColumn(["{$this->valueName}" => new \yii\db\Expression("SUM({$queryGenerator->getColumnName($this->valueName)})")]);
		}
		if ($params = App\Condition::validSearchParams($this->getTargetModule(), $request->getArray('search_params'))) {
			$this->searchParams = $request->getArray('search_params')[0];
			$transformedSearchParams = $queryGenerator->parseBaseSearchParamsToCondition($params);
			$queryGenerator->parseAdvFilter($transformedSearchParams);
		}
		$this->addPicklistsToQuery($queryGenerator);
		$query = $queryGenerator->createQuery();
		if (!empty($this->extraData['sortOrder'])) {
			$order = 'ASC' === $this->extraData['sortOrder'] ? SORT_ASC : SORT_DESC;
			if (!empty($this->valueName)) {
				$query->orderBy([$this->valueName => $order]);
			} else {
				$query->orderBy(['count' => $order]);
			}
		}
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
		if (empty($this->extraData['sortOrder'])) {
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
	}

	/**
	 * Set up model fields.
	 */
	protected function setUpModelFields()
	{
		$this->valueType = $this->extraData['valueType'] ?? null;
		if ($this->valueName = $this->extraData['valueField'] ?? null) {
			$this->valueFieldModel = $this->getTargetModuleModel()->getFieldByName($this->valueName);
		}
		$this->groupFieldModel = $this->getTargetModuleModel()->getFieldByName($this->extraData['groupField']);
		$this->groupName = $this->groupFieldModel->getName();
		$this->groupColumnName = $this->groupName;
		if ($this->isDividedByField()) {
			$this->dividingFieldModel = $this->getTargetModuleModel()->getFieldByName($this->dividingName);
			$this->dividingColumnName = $this->dividingName . $this->sectors;
		} elseif ($this->sectors) {
			$this->groupColumnName = $this->groupName . $this->sectors;
		}
	}

	/**
	 * Calculate average data if needed.
	 */
	protected function calculateAverage()
	{
		if ('avg' === $this->valueType) {
			foreach ($this->data as $dividingValue => &$dividing) {
				foreach ($dividing as $groupValue => &$group) {
					if ($group['avg']) {
						$group['avg'] = empty($this->numRows[$dividingValue][$groupValue]) ? 0 : (float) $group['avg'] / $this->numRows[$dividingValue][$groupValue];
					}
				}
			}
			if (!empty($this->extraData['sortOrder']) && isset($this->data[0]) && 1 === \count($this->data)) {
				$dataForSort = $this->data[0];
				if ('ASC' === $this->extraData['sortOrder']) {
					$firstReturnValueForSort = -1;
					$secondReturnValueForSort = 1;
				} else {
					$firstReturnValueForSort = 1;
					$secondReturnValueForSort = -1;
				}
				uksort($dataForSort, function ($a, $b) use ($dataForSort, $firstReturnValueForSort, $secondReturnValueForSort) {
					if ($dataForSort[$a]['avg'] === $dataForSort[$b]['avg']) {
						return 0;
					}
					return $dataForSort[$a]['avg'] < $dataForSort[$b]['avg'] ? $firstReturnValueForSort : $secondReturnValueForSort;
				});
				$this->data[0] = $dataForSort;
			}
		}
	}

	/**
	 * Increase number of rows for average calculation.
	 *
	 * @param $groupValue
	 * @param $dividingValue
	 * @param mixed $count
	 */
	protected function incNumRows($groupValue, $dividingValue, $count)
	{
		if (!isset($this->numRows[$dividingValue])) {
			$this->numRows[$dividingValue] = [];
		}
		if (!isset($this->numRows[$dividingValue][$groupValue])) {
			$this->numRows[$dividingValue][$groupValue] = 0;
		}
		$this->numRows[$dividingValue][$groupValue] += $count;
	}

	/**
	 * Add row.
	 *
	 * @param array      $row
	 * @param int|string $groupValue
	 * @param int|string $dividingValue
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
	 * @param int|string $groupValue
	 * @param int|string $dividingValue
	 */
	protected function getCurrentRows($groupValue, $dividingValue)
	{
		return $this->rows[$dividingValue][$groupValue];
	}

	/**
	 * Get rows for dividing field chart.
	 *
	 * @param \App\Db\Query $query
	 * @param int|string    $dividingValue
	 *
	 * @return array
	 */
	protected function getRowsDb($query, $dividingValue)
	{
		$dataReader = $query->createCommand()->query();
		while ($row = $dataReader->read()) {
			[$groupValue, $dividingValue] = $this->getFieldValuesFromRow($row, $dividingValue);
			$this->addRow($row, $groupValue, $dividingValue);
			if (!empty($row[$this->groupName]) && $row['count']) {
				$this->incNumRows($groupValue, $dividingValue, $row['count']);
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
		if (empty($this->filterIds)) {
			return $this->data;
		}
		if ($this->isMultiFilter()) {
			foreach ($this->getQueries() as $dividingValue => $query) {
				$this->getRowsDb($query, $dividingValue);
			}
		} else {
			$query = $this->getQuery($this->filterIds[0] ?? 0);
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
		if ('count' === $this->valueType) {
			$value = $row['count'];
		} elseif (isset($row[$this->valueName])) {
			$value = is_numeric($row[$this->valueName]) ? (float) $row[$this->valueName] : $value;
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
		foreach ($this->rows as $dividing) {
			foreach ($dividing as $group) {
				foreach ($group as $row) {
					if (!empty($row['picklist_id'])) {
						return 'picklist';
					}
					if (!empty($row['assigned_user_id'])) {
						return 'assigned_user_id';
					}
					if (!empty($row['id'])) {
						return 'record_id';
					}
				}
			}
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
		if ('record_number' !== $this->colorsFrom && 'filters' !== $this->colorsFrom) {
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
		if (!$this->sectors && !isset($this->data[$groupValue][$dividingValue]['link']) && $this->groupFieldModel->isActiveSearchView() && (!$this->isDividedByField() || $this->dividingFieldModel->isActiveSearchView())) {
			$params = array_merge($this->searchParams, [$this->getSearchParamValue($this->groupFieldModel, $row[$this->groupName])]);
			if ($this->isDividedByField()) {
				$params = array_merge($params, [$this->getSearchParamValue($this->dividingFieldModel, $row[$this->dividingName])]);
			}
			$link = $this->getTargetModuleModel()->getListViewUrl() . '&viewname=' . $this->getFilterId($dividingValue) . '&search_params=' . rawurlencode(App\Json::encode([$params]));
			$this->addValue('link', $link, $groupValue, $dividingValue);
		}
	}

	/**
	 * Get search param value.
	 *
	 * @param Vtiger_Field_Model $fieldModel
	 * @param mixed              $value
	 *
	 * @return array
	 */
	protected function getSearchParamValue(Vtiger_Field_Model $fieldModel, $value): array
	{
		$operator = 'e';
		$fieldDataType = $fieldModel->getFieldDataType();
		if ($fieldModel->isReferenceField()) {
			$operator = 'a';
		} elseif (\in_array($fieldDataType, ['multipicklist', 'categoryMultipicklist'])) {
			$operator = 'c';
			$value = 'multipicklist' === $fieldDataType ? str_replace(' |##| ', '##', $value) : $value;
		}
		return [$fieldModel->getName(), $operator, $value];
	}

	/**
	 * Get field values from row.
	 * We are operating on groupValue and dividingValue regularly so this fn will return this values from row.
	 *
	 * @param array      $row
	 * @param int|string $dividingValue
	 *
	 * @return array
	 */
	protected function getFieldValuesFromRow($row, $dividingValue)
	{
		$groupValue = $this->groupColumnName !== $this->groupName ? $row[$this->groupColumnName] : $this->groupFieldModel->getDisplayValue($row[$this->groupName], false, false, true);
		if (empty($groupValue)) {
			$groupValue = '(' . \App\Language::translate('LBL_EMPTY', 'Home') . ')';
		}
		if ($this->isDividedByField()) {
			$dividingValue = $this->sectors ? $row[$this->dividingColumnName] : $this->dividingFieldModel->getDisplayValue($row[$this->dividingName], false, false, true);
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
	 * @param int|string $groupValue
	 * @param int|string $dividingValue
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
	 * @param int|string $groupValue
	 * @param int|string $dividingValue
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
	 * @param int|string $groupValue
	 * @param int|string $dividingValue
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
	 * Get concrete value from data.
	 *
	 * @param string     $valueType
	 * @param int|string $groupValue
	 * @param int|string $dividingValue
	 *
	 * @return int
	 */
	protected function getValue($valueType, $groupValue, $dividingValue)
	{
		return $this->data[$dividingValue][$groupValue][$valueType] ?? 0;
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
	 * Get extra data.
	 *
	 * @param string $key
	 *
	 * @return mixed
	 */
	public function getExtraData($key)
	{
		return $this->extraData[$key] ?? null;
	}

	/**
	 * Set filter ids.
	 *
	 * @return int[]
	 */
	private function setFilterIds(): array
	{
		$this->customView = \App\CustomView::getInstance($this->getTargetModule());
		foreach (explode(',', $this->widgetModel->get('filterid')) as $id) {
			$filterData = $this->customView->getFilterInfo((int) $id);
			if ($filterData && $this->customView->isPermittedCustomView($id)) {
				$this->filterIds[] = (int) $id;
				$this->viewNames[$id] = $filterData['viewname'];
			}
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
		if (\is_string($this->extraData)) {
			$this->extraData = \App\Json::decode(App\Purifier::decodeHtml($this->extraData));
		}
		if (null === $this->extraData) {
			throw new App\Exceptions\AppException('Invalid data');
		}
		$this->additionalFiltersFieldsNames = empty($this->extraData['additionalFiltersFields']) ? [] : (array) $this->extraData['additionalFiltersFields'];
		$this->setChartHeaders();
		$this->chartType = $this->extraData['chartType'];
		$this->groupName = !empty($this->extraData['groupField']) ? $this->extraData['groupField'] : null;
		$this->stacked = !empty($this->extraData['stacked']);
		$this->sectors = empty($this->extraData['sectorField']) ? '' : $this->extraData['sectorField'];
		$this->dividingName = 0;
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
	 * @return int|string|null
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
			$cvId = (int) $this->getFilterId(0);
			$viewName = \App\CustomView::getCVDetails($cvId, $this->getTargetModule())['viewname'] ?? '';
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
		if (\count($this->getFilterIds()) > 1) {
			return null;
		}
		return 'index.php?module=' . $this->getTargetModule() . '&action=Pagination&mode=getTotalCount&viewname=' . $this->getFilterId(0);
	}

	/**
	 * Get list view url.
	 *
	 * @param int $dividingValue
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
