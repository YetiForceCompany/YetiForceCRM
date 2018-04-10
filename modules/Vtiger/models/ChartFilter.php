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
	 * Group field model.
	 *
	 * @var Vtiger_Field_Model
	 */
	private $groupFieldModel;

	/**
	 * Is chart divided?
	 *
	 * @var bool
	 */
	private $dividedField;

	/**
	 * Divide field model (for stacked/divided charts).
	 *
	 * @var \Vtiger_Module_Model
	 */
	private $divideFieldModel;

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
	 * Colors from picklist.
	 *
	 * @var array
	 */
	private $colors = [];

	/**
	 * Total number of rows grouped by fieldName and displayValue.
	 *
	 * @var array ['leadstatus']['Odrzucone'] === 2
	 */
	private $numRows = [];

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
		// backward compability fix
		$type = $this->extraData['chartType'];
		switch ($type) {
			case 'Barchat':
				$type = 'Bar';
				break;
			case 'Bardivided':
				$type = 'BarDivided';
				break;
		}
		return $type;
	}

	/**
	 * Is chart divided / stacked ?
	 *
	 * @return bool
	 */
	public function isDivided()
	{
		return !empty($this->dividedField);
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
		foreach ($this->getRowsDivided() as $groupValue => $data) {
			$chartData['labels'][] = $groupValue;
			$i = 0;
			foreach ($data as $dividedValue => $value) {
				// each dividedValue should be in different dataset (different stacks)
				if (!isset($chartData['datasets'][$i])) {
					$chartData['datasets'][] = [
						'data' => [],
						'links' => [],
					];
				}
				$dataset = &$chartData['datasets'][$i];
				$dataset['data'][] = $value[$this->extraData['valueType']];
				if ($this->isDivided()) {
					$dataset['label'] = $dividedValue;
				}
				if (!empty($value['link'])) {
					$dataset['links'][] = $value['link'];
				}
				if (!empty($value['color_id']) && !empty($this->colors[$value['color_id']])) {
					$chartData['datasets'][$i]['backgroundColor'][] = $this->colors[$value['color_id']];
				}
				$chartData['show_chart'] = true;
				$i++;
			}
		}
		return $chartData;
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
		if (!empty($this->extraData['groupField'])) {
			$queryGenerator->setField($this->extraData['groupField']);
		}
		if (!empty($this->extraData['valueField'])) {
			$queryGenerator->setField($this->extraData['valueField']);
		}
		if ($this->has('owner') && !empty($this->extraData['showOwnerFilter']) && $this->get('owner') !== 0) {
			$queryGenerator->addCondition('assigned_user_id', $this->get('owner'), 'e');
		}
		if ($this->has('time') && !empty($this->extraData['timeRange']) && $this->extraData['timeRange'] !== '-') {
			$time = $this->get('time');
			$timeFieldModel = Vtiger_Field_Model::getInstance($this->extraData['timeRange'], $this->getTargetModuleModel());
			if ($timeFieldModel) {
				$queryGenerator->addCondition($timeFieldModel->getName(), "{$time[0]} 00:00:00 , {$time[1]} 23:59:59", 'bw');
				$this->searchParams[] = [$timeFieldModel->getFieldName(), 'bw', "{$time[0]} , {$time[1]}"];
			}
		}
		if (!empty($this->extraData['showOwnerFilter'])) {
			$queryGenerator->setField('assigned_user_id');
		}
		$query = $queryGenerator->createQuery();
		if (!empty($this->groupFieldModel) && empty($this->divideFieldModel)) {
			$moduleName = $queryGenerator->getModuleModel()->getName();
			$picklists = \App\Fields\Picklist::getModulesByName($moduleName);
			$fieldName = $this->groupFieldModel->getName();
			if (in_array($fieldName, $picklists, true)) {
				$this->colors = \App\Fields\Picklist::getColors($fieldName);
				$primaryKey = App\Fields\Picklist::getPickListId($fieldName);
				$fieldTable = 'vtiger_' . $this->groupFieldModel->getName();
				$query->leftJoin($fieldTable, "{$this->groupFieldModel->table}.{$this->groupFieldModel->column} = {$fieldTable}.{$fieldName}");
				$query->addSelect(['picklist_id' => "$fieldTable.$primaryKey"]);
			}
		}
		if (!empty($this->divideFieldModel)) {
			$moduleName = $queryGenerator->getModuleModel()->getName();
			$picklists = \App\Fields\Picklist::getModulesByName($moduleName);
			$fieldName = $this->divideFieldModel->getName();
			if (in_array($fieldName, $picklists, true)) {
				$this->colors = \App\Fields\Picklist::getColors($fieldName);
				$primaryKey = App\Fields\Picklist::getPickListId($fieldName);
				$fieldTable = 'vtiger_' . $this->divideFieldModel->getName();
				$query->leftJoin($fieldTable, "{$this->divideFieldModel->table}.{$this->divideFieldModel->column} = {$fieldTable}.{$fieldName}");
				$query->addSelect(['picklist_id' => "$fieldTable.$primaryKey"]);
			}
		}
		return $query;
	}

	/**
	 * Get rows for all chart.
	 *
	 * @return array
	 */
	protected function getRows()
	{
		$sectors = $this->extraData['sectorField'];
		$this->groupFieldModel = Vtiger_Field_Model::getInstance($this->extraData['groupField'], $this->getTargetModuleModel());
		$fieldName = $this->groupFieldModel->getFieldName();
		$dataReader = $this->getQuery()->createCommand()->query();
		$groupData = $sectorValues = [];
		while ($row = $dataReader->read()) {
			$displayValue = $this->groupFieldModel->getDisplayValue($row[$fieldName], false, false, true);
			if (!empty($row['assigned_user_id'])) {
				$this->colors['owner_' . $row['assigned_user_id']] = \App\Fields\Owner::getColor($row['assigned_user_id']);
			}
			if (!empty($row[$fieldName])) {
				if (!empty($this->extraData['showOwnerFilter'])) {
					$this->owners[] = $row['assigned_user_id'];
				}
				if ($sectors) {
					$sectorValues = $this->getValueForSector($sectorValues, $row[$fieldName]);
				} else {
					$groupData = $this->getValue($groupData, $row);
				}
				if (!isset($this->numRows[$fieldName])) {
					$this->numRows[$fieldName] = [];
				}
				if (!isset($this->numRows[$fieldName][$displayValue])) {
					$this->numRows[$fieldName][$displayValue] = 0;
				}
				$this->numRows[$fieldName][$displayValue]++;
			}
		}
		if ($this->extraData['valueType'] === 'avg') {
			if ($sectors) {
				foreach ($sectorValues as $sectorId => &$value) {
					$value = (float) $value / $this->numRows[$fieldName][$sectorId];
				}
			} else {
				foreach ($groupData as $displayValue => &$values) {
					if ($values['avg']) {
						$values['avg'] = (float) $values['avg'] / $this->numRows[$fieldName][$displayValue];
					}
				}
			}
		}
		$dataReader->close();
		return $groupData;
	}

	/**
	 * Normalize divided charts so they have equal number of data.
	 *
	 * @param {array} $data
	 *
	 * @return {mixed}
	 */
	protected function normalizeDivided(&$data)
	{
		$valueType = $this->extraData['valueType'];
		foreach ($data as $groupValueKey => &$group) {
			foreach ($group as $dividedValueKey => &$values) {
				// iterate data one more time to search other group values
				$values[$valueType] = (float) $values[$valueType];
				foreach ($data as $otherGroupKey => $divided) {
					if (!isset($data[$otherGroupKey][$dividedValueKey])) {
						// other group doesn't have this value
						$data[$otherGroupKey][$dividedValueKey] = [$valueType => (float) 0];
					}
				}
			}
		}
		foreach ($data as &$group) {
			ksort($group);
			foreach ($group as &$divided) {
				ksort($divided);
			}
		}
		return $data;
	}

	/**
	 * Get rows for divided field chart.
	 *
	 * @return array
	 */
	protected function getRowsDivided()
	{
		$this->groupFieldModel = Vtiger_Field_Model::getInstance($this->extraData['groupField'], $this->getTargetModuleModel());
		$fieldName = $this->groupFieldModel->getFieldName();
		if ($this->isDivided()) {
			$this->divideFieldModel = Vtiger_Field_Model::getInstance($this->extraData['dividedField'], $this->getTargetModuleModel());
			$divideFieldName = $this->divideFieldModel->getFieldName();
		}
		$dataReader = $this->getQuery()->createCommand()->query();
		$data = [];
		// data = data values grouped by displayValue, dividedValue and valueType
		while ($row = $dataReader->read()) {
			if (!empty($row[$fieldName])) {
				$groupValue = $this->groupFieldModel->getDisplayValue($row[$fieldName], false, false, true);
				if ($this->isDivided()) {
					$dividedValue = $this->divideFieldModel->getDisplayValue($row[$divideFieldName], false, false, true);
				} else {
					$dividedValue = 0;
				}
				if (!empty($row['assigned_user_id'])) {
					$this->colors['owner_' . $row['assigned_user_id']] = \App\Fields\Owner::getColor($row['assigned_user_id']);
				}
				$data = $this->getValueDivided($data, $row);
				if (!isset($this->numRows[$groupValue])) {
					$this->numRows[$groupValue] = [];
				}
				if (!isset($this->numRows[$groupValue][$dividedValue])) {
					$this->numRows[$groupValue][$dividedValue] = 0;
				}
				$this->numRows[$groupValue][$dividedValue]++;
				if (!empty($this->extraData['showOwnerFilter'])) {
					$this->owners[] = $row['assigned_user_id'];
				}
			}
		}
		if ($this->extraData['valueType'] === 'avg') {
			foreach ($data as $groupValue => &$values) {
				foreach ($values as $dividedValue => $value) {
					if ($values['avg']) {
						$values['avg'] = (float) $values['avg'] / $this->numRows[$groupValue][$dividedValue];
					}
				}
			}
		}
		$data = $this->normalizeDivided($data);
		$dataReader->close();
		return $data;
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
		$value = empty($row[$this->extraData['valueField']]) ? 0 : 1;
		$value = is_numeric($row[$this->extraData['valueField']]) ? $row[$this->extraData['valueField']] : $value;
		if ($this->extraData['valueType'] === 'count') {
			$value = 1; // only counting records
		}
		return $value;
	}

	/**
	 * Get chart value for row.
	 *
	 * @param array $groupData
	 * @param array $row
	 *
	 * @return array
	 */
	protected function getValue($groupData, $row)
	{
		$valueType = $this->extraData['valueType'];
		$fieldName = $this->groupFieldModel->getFieldName();
		$value = $this->getValueFromRow($row);
		$groupValue = $this->groupFieldModel->getDisplayValue($row[$fieldName], false, false, true);
		if (!isset($groupData[$groupValue][$valueType])) {
			$groupData[$groupValue][$valueType] = $value;
		} else {
			$groupData[$groupValue][$valueType] += $value;
		}
		if (!isset($groupData[$groupValue]['link'])) {
			$searchParams = array_merge($this->searchParams, [[$this->extraData['groupField'], 'e', $row[$this->extraData['groupField']]]]);
			$groupData[$groupValue]['link'] = $this->getTargetModuleModel()->getListViewUrl() . '&viewname=' . $this->widgetModel->get('filterid') . '&search_params=' . App\Json::encode([$searchParams]);
		}
		if (!empty($row['picklist_id'])) {
			$groupData[$groupValue]['color_id'] = $row['picklist_id'];
		} elseif (!empty($row['assigned_user_id'])) {
			$groupData[$groupValue]['color_id'] = $row['assigned_user_id'];
			$this->colors[$row['assigned_user_id']] = \App\Fields\Owner::getColor($row['assigned_user_id']);
		} elseif (!empty($row['id'])) {
			$groupData[$groupValue]['color_id'] = $row['id'];
			$this->colors[$row['id']] = \App\Colors::getRandomColor($row['id']);
		} else {
			$colorNr = count($this->colors);
			$groupData[$groupValue]['color_id'] = $colorNr;
			$this->colors[$row['id']] = \App\Colors::getRandomColor($colorNr);
		}
		return $groupData;
	}

	/**
	 * Get chart value for row (divided chart).
	 *
	 * @param array $data
	 * @param array $row
	 *
	 * @return array
	 */
	protected function getValueDivided($data, $row)
	{
		$valueType = $this->extraData['valueType'];
		$value = $this->getValueFromRow($row);
		$groupFieldName = $this->groupFieldModel->getFieldName();
		$groupValue = $this->groupFieldModel->getDisplayValue($row[$groupFieldName], false, false, true);
		if ($this->isDivided()) {
			$divideFieldName = $this->divideFieldModel->getFieldName();
			$dividedValue = $this->divideFieldModel->getDisplayValue($row[$divideFieldName], false, false, true);
		} else {
			$dividedValue = 0;
		}
		if (!isset($data[$groupValue])) {
			$data[$groupValue] = [];
		}
		if (!isset($data[$groupValue][$dividedValue])) {
			$data[$groupValue][$dividedValue] = [];
		}
		if (!isset($data[$groupValue][$dividedValue][$valueType])) {
			$data[$groupValue][$dividedValue][$valueType] = $value;
		} else {
			$data[$groupValue][$dividedValue][$valueType] += $value;
		}
		if (!isset($data[$groupValue][$dividedValue]['link'])) {
			$searchParams = array_merge($this->searchParams, [[$groupFieldName, 'e', $row[$groupFieldName]]]);
			if ($this->isDivided()) {
				$searchParams = array_merge($searchParams, [[$divideFieldName, 'e', $row[$divideFieldName]]]);
			}
			$link = $this->getTargetModuleModel()->getListViewUrl() . '&viewname=' . $this->widgetModel->get('filterid') . '&search_params=' . App\Json::encode([$searchParams]);
			$data[$groupValue][$dividedValue]['link'] = $link;
		}
		if (!empty($row['picklist_id'])) {
			$data[$groupValue][$dividedValue]['color_id'] = $row['picklist_id'];
		} elseif (!empty($row['assigned_user_id'])) {
			$data[$groupValue][$dividedValue]['color_id'] = $row['assigned_user_id'];
			$this->colors[$row['assigned_user_id']] = \App\Fields\Owner::getColor($row['assigned_user_id']);
		} elseif (!empty($row['id'])) {
			$data[$groupValue][$dividedValue]['color_id'] = $row['id'];
			$this->colors[$row['id']] = \App\Colors::getRandomColor($row['id']);
		} else {
			$colorNr = count($this->colors);
			$data[$groupValue][$dividedValue]['color_id'] = $colorNr;
			$this->colors[$row['id']] = \App\Colors::getRandomColor($colorNr);
		}
		return $data;
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
		$fieldName = $this->groupFieldModel->getFieldName();
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
		$this->dividedField = !empty($this->extraData['dividedField']) ? $this->extraData['dividedField'] : null;
	}

	/**
	 * Function to check if chart should be colored.
	 *
	 * @return bool
	 */
	public function isColor()
	{
		return $this->extraData['color'];
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
}
