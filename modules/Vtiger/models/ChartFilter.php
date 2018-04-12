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

	/**
	 * Widget model.
	 *
	 * @var \Vtiger_Widget_Model
	 */
	private $widgetModel;

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
	 * Query generator module name.
	 *
	 * @var string
	 */
	private $queryGeneratorModuleName;

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
	 * @var array
	 */
	private $colors = [];

	/**
	 * Colors that was used in data already
	 * grouped by $groupValue or $dividingValue - it depends on areColorsFromDividingField.
	 *
	 * @var array
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
	public function getType()
	{
		// backward compatibility fix
		$type = $this->extraData['chartType'];
		switch ($type) {
			case 'Barchat':
				$type = 'Bar';
				break;
			case 'Bardividing':
				$type = 'Bar';
				break;
		}
		return $type;
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
	 * Is chart dividing / stacked ?
	 *
	 * @return bool
	 */
	public function isDivided()
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
		foreach ($this->getRows() as $groupValue => &$group) {
			$chartData['labels'][] = $groupValue;
			$i = 0;
			foreach ($group as $dividingValue => &$dividing) {
				// each dividingValue should be in different dataset (different stacks)
				if (!isset($chartData['datasets'][$i])) {
					$chartData['datasets'][] = [
						'data' => [],
						'links' => [],
					];
				}
				$dataset = &$chartData['datasets'][$i];
				$dataset['data'][] = $dividing[$this->valueType];
				if ($this->isDivided()) {
					$dataset['label'] = $dividingValue;
				}
				if (!empty($dividing['link']) || $dividing['link'] === null) {
					$dataset['links'][] = $dividing['link'];
				}
				if ((!empty($dividing['color_id']) && !empty($this->colors[$dividing['color_id']])) || $dividing['color_id'] === null) {
					if ($dividing['color_id'] === null) {
						// we have all fields colors
						// if some record doesn't have field which have color use color from other dataset
						$color = 'rgba(0,0,0,0)'; // transparent color if records doesn't have wanted colors (what can i do?)
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
						$chartData['datasets'][$i]['backgroundColor'][] = $color;
						$chartData['datasets'][$i]['pointBackgroundColor'][] = $color;
					} else {
						$chartData['datasets'][$i]['backgroundColor'][] = $this->colors[$dividing['color_id']];
						$chartData['datasets'][$i]['pointBackgroundColor'][] = $this->colors[$dividing['color_id']];
					}
				}
				$chartData['show_chart'] = true;
				$i++;
			}
		}
		return $chartData;
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
		foreach ($this->rows as $row) {
			$this->colors[$row[static::ROW_PICKLIST]] = $colors[$row[static::ROW_PICKLIST]];
		}
	}

	/**
	 * Set colors from assigned user.
	 */
	protected function setColorsFromAssignedUserId()
	{
		$this->colorsFrom = static::ASSIGNED_USER_ID;
		$this->colorsFromRow = static::ROW_ASSIGNED_USER_ID;
		foreach ($this->rows as $row) {
			$this->colors[$row[static::ROW_ASSIGNED_USER_ID]] = \App\Fields\Owner::getColor($row[static::ROW_ASSIGNED_USER_ID]);
		}
	}

	/**
	 * Set colors from record id.
	 */
	protected function setColorsFromRecordId()
	{
		$this->colorsFrom = static::RECORD_ID;
		$this->colorsFromRow = static::ROW_RECORD_ID;
		foreach ($this->rows as $row) {
			$this->colors[$row[static::ROW_RECORD_ID]] = \App\Colors::getRandomColor($row[static::ROW_RECORD_ID]);
		}
	}

	/**
	 * Set colors from record number (array index).
	 */
	protected function setColorsFromRecordNumber()
	{
		$this->colorsFrom = static::RECORD_NUMBER;
		$this->colorsFromRow = static::ROW_RECORD_NUMBER;
		foreach ($this->rows as $index => $row) {
			$this->colors[$index] = \App\Colors::getRandomColor('generated_' . $index);
		}
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
			if (!$this->isDivided() || !$this->areColorsFromDividingField()) {
				if (in_array($this->groupName, $picklists, true)) {
					$primaryKey = App\Fields\Picklist::getPickListId($this->groupName);
					$fieldTable = 'vtiger_' . $this->groupName;
					$query->leftJoin($fieldTable, "{$this->groupFieldModel->table}.{$this->groupFieldModel->column} = {$fieldTable}.{$this->groupName}");
					$query->addSelect([static::ROW_PICKLIST => "$fieldTable.$primaryKey"]);
				}
			}
			if ($this->isDivided() && $this->areColorsFromDividingField()) {
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

	/**
	 * Get chart query.
	 *
	 * @return \App\Db\Query
	 */
	protected function getQuery()
	{
		$queryGenerator = new \App\QueryGenerator($this->getTargetModule());
		$queryGenerator->initForCustomViewById($this->widgetModel->get('filterid'));
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
		foreach ($this->data as &$group) {
			ksort($group);
			foreach ($group as &$dividing) {
				ksort($dividing);
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
		if ($this->isDivided()) {
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
			foreach ($this->data as $groupValue => &$group) {
				foreach ($group as $dividingValue => &$dividing) {
					if ($group['avg']) {
						$group['avg'] = (float) $group['avg'] / $this->numRows[$groupValue][$dividingValue];
					}
				}
			}
		}
	}

	/**
	 * Get rows for dividing field chart.
	 *
	 * @return array
	 */
	protected function getRows()
	{
		$this->setUpModelFields();
		$dataReader = $this->getQuery()->createCommand()->query();
		while ($row = $dataReader->read()) {
			$this->rows[] = $row;
			$fieldValues = $this->getFieldValuesFromRow($row);
			if (!isset($this->numRows[$fieldValues['groupValue']])) {
				$this->numRows[$fieldValues['groupValue']] = [];
			}
			if (!isset($this->numRows[$fieldValues['groupValue']][$fieldValues['dividingValue']])) {
				$this->numRows[$fieldValues['groupValue']][$fieldValues['dividingValue']] = 0;
			}
			if (!empty($row[$this->groupFieldName])) {
				$this->numRows[$fieldValues['groupValue']][$fieldValues['dividingValue']]++;
			}
			if (!empty($this->extraData['showOwnerFilter'])) {
				$this->owners[] = $row['assigned_user_id'];
			}
		}
		$dataReader->close();
		if (count($this->rows)) {
			$this->setColorsFrom($this->findOutColorsFromRows());
		}
		foreach ($this->rows as $rowIndex => $row) {
			$this->setValueFromRow($rowIndex, $row);
		}
		$this->calculateAverage();
		$this->normalizeData();
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
		foreach ($this->rows as $row) {
			if (!empty($row[static::ROW_PICKLIST])) {
				$picklist = true;
			} elseif (!empty($row[static::ROW_ASSIGNED_USER_ID])) {
				$assignedUserId = true;
			} elseif (!empty($row[static::ROW_RECORD_ID])) {
				$recordId = true;
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
	protected function setColorFromRow($rowIndex, $row, $groupValue, $dividingValue)
	{
		if ($this->colorsFrom !== static::RECORD_NUMBER) {
			$colorId = $row[$this->colorsFromRow];
		} else {
			$colorId = $rowIndex;
		}
		$this->data[$groupValue][$dividingValue]['color_id'] = $colorId;
		//var_dump($groupValue, $dividingValue, $this->data[$groupValue][$dividingValue]);
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
			if ($this->isDivided()) {
				$searchParams = array_merge($searchParams, [[$this->dividingFieldName, 'e', $row[$this->dividingName]]]);
			}
			$link = $this->getTargetModuleModel()->getListViewUrl() . '&viewname=' . $this->widgetModel->get('filterid') . '&search_params=' . App\Json::encode([$searchParams]);
			$this->data[$groupValue][$dividingValue]['link'] = $link;
		}
	}

	/**
	 * Get field values from row.
	 * We are operating on groupValue and dividingValue regularly so this fn will return this values from row.
	 *
	 * @param $row
	 *
	 * @return array
	 */
	protected function getFieldValuesFromRow($row)
	{
		$groupValue = $this->groupFieldModel->getDisplayValue($row[$this->groupName], false, false, true);
		if (empty($groupValue)) {
			$groupValue = '(' . \App\Language::translate('LBL_EMPTY', 'Home') . ')';
		}
		if ($this->isDivided()) {
			$dividingValue = $this->dividingFieldModel->getDisplayValue($row[$this->dividingName], false, false, true);
			if (empty($dividingValue)) {
				$dividingValue = '(' . \App\Language::translate('LBL_EMPTY', 'Home') . ')';
			}
		} else {
			$dividingValue = 0;
		}
		return ['groupValue'=>$groupValue, 'dividingValue'=>$dividingValue];
	}

	/**
	 * Get chart value for row (dividing chart).
	 *
	 * @param array $data
	 * @param array $row
	 *
	 * @return array
	 */
	protected function setValueFromRow($rowIndex, $row)
	{
		$valueType = $this->extraData['valueType'];
		$value = $this->getValueFromRow($row);
		$fieldValues = $this->getFieldValuesFromRow($row);
		if (!isset($this->data[$fieldValues['groupValue']])) {
			$this->data[$fieldValues['groupValue']] = [];
		}
		if (!isset($this->data[$fieldValues['groupValue']][$fieldValues['dividingValue']])) {
			$this->data[$fieldValues['groupValue']][$fieldValues['dividingValue']] = [];
		}
		if (!isset($this->data[$fieldValues['groupValue']][$fieldValues['dividingValue']][$valueType])) {
			$this->data[$fieldValues['groupValue']][$fieldValues['dividingValue']][$valueType] = $value;
		} else {
			$this->data[$fieldValues['groupValue']][$fieldValues['dividingValue']][$valueType] += $value;
		}
		$this->setColorFromRow($rowIndex, $row, $fieldValues['groupValue'], $fieldValues['dividingValue']);
		$this->setLinkFromRow($row, $fieldValues['groupValue'], $fieldValues['dividingValue']);
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
			$sectorId = $this->getSector($sectors, $row[$fieldName]);
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
			$searchParams = array_merge($this->searchParams, [[$fieldName, 'm', $sectorValue]]);
			if ($sectorId != 0) {
				$searchParams[] = [$fieldName, 'g', $sectors[$sectorId - 1]];
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
		// Decode data if not done already.
		if (is_string($this->extraData)) {
			$this->extraData = \App\Json::decode(App\Purifier::decodeHtml($this->extraData));
		}
		if ($this->extraData === null) {
			throw new \App\Exceptions\AppException('Invalid data');
		}
		$this->dividingName = !empty($this->extraData['dividingField']) ? $this->extraData['dividingField'] : null;
		$this->groupName = !empty($this->extraData['groupField']) ? $this->extraData['groupField'] : null;
		$this->stacked = !empty($this->extraData['stacked']);
		if ($this->dividingName) {
			$this->colorsFromDividingField = !empty($this->extraData['colorsFromDividingField']);
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
