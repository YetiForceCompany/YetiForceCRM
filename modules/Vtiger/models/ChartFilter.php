<?php

/**
 * Model widget chart with a filter
 * @package YetiForce.Model
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
class Vtiger_ChartFilter_Model extends Vtiger_Widget_Model
{

	private $widgetModel;
	private $extraData;
	private $targetModuleModel;

	public static function getInstance($linkId = 0, $userId = 0)
	{
		return new self();
	}

	public function getType()
	{
		return $this->extraData['chartType'];
	}

	public function getChartData()
	{
		$charType = $this->getType();
		$charType = 'getData' . ucwords(strtolower($charType));
		if (method_exists($this, $charType)) {
			return $this->$charType();
		}
		return [];
	}

	protected function getDataHorizontal()
	{
		return $this->getDataBarchat();
	}

	protected function getDataLine()
	{
		return $this->getDataBarchat();
	}

	protected function getDataBarchat()
	{
		$groupData = $this->getDataFromFilter();
		uasort($groupData, function($first, $second) {
			if ($first['count'] == $second['count']) {
				return 0;
			}
			return ($first['count'] < $second['count']) ? 1 : -1;
		});
		$data = [];
		foreach ($groupData as $fieldName => $value) {
			$data [] = [$value['count'], $fieldName, $value['link']];
		}
		return $data;
	}

	protected function getDataFunnel()
	{
		$groupData = $this->getDataFromFilter();
		uasort($groupData, function($first, $second) {
			if ($first['count'] == $second['count']) {
				return 0;
			}
			return ($first['count'] < $second['count']) ? 1 : -1;
		});
		$data = [];
		foreach ($groupData as $fieldName => $value) {
			$data [] = [$fieldName, $value['count'], $value['link']];
		}

		return $data;
	}

	protected function getDataPie()
	{
		$groupData = $this->getDataFromFilter();
		$data = [];
		foreach ($groupData as $fieldName => $value) {
			$data [] = ['last_name' => $fieldName, 'id' => $value['count'], '2' => $value['link']];
		}
		return $data;
	}

	protected function getDataDonut()
	{
		$groupData = $this->getDataFromFilter();
		$data = [];
		foreach ($groupData as $fieldName => $value) {
			$data [] = ['last_name' => $fieldName, 'id' => $value['count'], '2' => $value['link']];
		}
		return $data;
	}

	public function getDataAxis()
	{
		$groupData = $this->getDataFromFilter();
		$data = [];
		foreach ($groupData as $fieldName => $value) {
			$data [] = ['last_name' => $fieldName, 'id' => $value['count'], '2' => $value['link']];
		}
		return $data;
	}

	public function getDataArea()
	{
		$groupData = $this->getDataFromFilter();
		$data = [];
		foreach ($groupData as $fieldName => $value) {
			$data [] = ['last_name' => $fieldName, 'id' => $value['count'], '2' => $value['link']];
		}
		return $data;
	}

	public function getDataBardivided()
	{
		$groupData = $this->getDataFromFilter();
		$data = [];
		foreach ($groupData as $fieldName => $value) {
			$data [] = ['last_name' => $fieldName, 'id' => $value['count'], '2' => $value['link']];
		}
		return $data;
	}

	public function getDataFromFilter()
	{
		$filterId = $this->widgetModel->get('filterid');
		$groupField = $this->extraData['groupField'];
		$searchParams = [];
		$groupFieldModel = Vtiger_Field_Model::getInstance($groupField, $this->getTargetModuleModel());
		$fieldName = $groupFieldModel->get('name');
		$queryGenerator = new \App\QueryGenerator($this->getTargetModule());
		$queryGenerator->initForCustomViewById($filterId);
		$queryGenerator->setField($groupField);
		$query = $queryGenerator->createQuery();
		if ($this->has('time') && !empty($this->extraData['timeRange'])) {
			$time = $this->get('time');
			$timeFieldModel = Vtiger_Field_Model::getInstance($this->extraData['timeRange'], $this->getTargetModuleModel());
			$tableAndColumnName = $timeFieldModel->getTableName() . '.' . $timeFieldModel->getColumnName();
			$query->andWhere([
				'and',
				['>=', $tableAndColumnName, Vtiger_Date_UIType::getDBInsertedValue($time['start'])],
				['<=', $tableAndColumnName, Vtiger_Date_UIType::getDBInsertedValue($time['end'])]
			]);
			$searchParams[] = [$timeFieldModel->getFieldName(), 'bw', $time['start'] . ',' . $time['end']];
		}
		$dataReader = $query->createCommand()->query();
		$groupData = [];
		if (empty($this->extraData['sector'])) {
			while ($row = $dataReader->read()) {
				if (!empty($row[$groupField])) {
					$displayValue = $groupFieldModel->getDisplayValue($row[$groupField]);
					if (!isset($groupData[$displayValue]['count'])) {
						$groupData[$displayValue]['count'] = 1;
					} else {
						$groupData[$displayValue]['count'] ++;
					}
					if (!isset($groupData[$displayValue]['link'])) {
						$moduleModel = $this->getTargetModuleModel();
						$searchParams[] = [$fieldName, 'e', $row[$groupField]];
						$groupData[$displayValue]['link'] = $moduleModel->getListViewUrl() . "&viewname=$filterId&search_params=" . App\Json::encode([$searchParams]);
					}
				}
			}
		} else {
			$sectors = $this->extraData['sector'];
			$count = [];
			while ($row = $dataReader->read()) {
				$sectorId = $this->getSector($sectors, $row[$groupField]);
				if ($sectorId !== false) {
					if (!isset($count[$sectorId])) {
						$count[$sectorId] = 1;
					} else {
						$count[$sectorId] ++;
					}
				}
			}
			foreach ($sectors as $sectorId => &$sectorValue) {
				$moduleModel = $this->getTargetModuleModel();
				$displayValue = $groupFieldModel->getDisplayValue($sectorValue);
				$displayValue .= ' - (' . (int) $count[$sectorId] . ')';
				$groupData[$displayValue]['count'] = (int) $sectorValue;
				$searchParams[] = [$fieldName, 'm', $sectorValue];
				if ($sectorId != 0) {
					$searchParams[] = [$fieldName, 'g', $sectors[$sectorId - 1]];
				}
				$groupData[$displayValue]['link'] = $moduleModel->getListViewUrl() . "&viewname=$filterId&search_params=" . App\Json::encode([$searchParams]);
			}
		}
		return $groupData;
	}

	protected function getSector($sectors, $value)
	{
		$sectorId = false;
		foreach ($sectors as $key => $sector) {
			if ($value <= $sector) {
				$sectorId = $key;
				break;
			}
		}
		return $sectorId;
	}

	public function setWidgetModel($widgetModel)
	{
		$this->widgetModel = $widgetModel;
		$this->extraData = $this->widgetModel->get('data');

		// Decode data if not done already.
		if (is_string($this->extraData)) {
			$this->extraData = \App\Json::decode(App\Purifier::decodeHtml($this->extraData));
		}
		if ($this->extraData === null) {
			throw new Exception("Invalid data");
		}
	}

	/**
	 * Function to check if chart should be colored
	 * @return boolean
	 */
	public function isColor()
	{
		return $this->extraData['color'];
	}

	public function getTargetModule()
	{
		return $this->extraData['module'];
	}

	public function getTargetModuleModel()
	{
		if (!$this->targetModuleModel) {
			$this->targetModuleModel = Vtiger_Module_Model::getInstance($this->getTargetModule());
		}
		return $this->targetModuleModel;
	}

	public function getTitle($prefix = '')
	{
		$title = $this->widgetModel->get('title');
		if (empty($title)) {
			$db = PearDatabase::getInstance();
			$suffix = '';
			$customviewrs = $db->pquery('SELECT viewname FROM vtiger_customview WHERE cvid=?', array($this->widgetModel->get('filterid')));
			if ($db->numRows($customviewrs)) {
				$customview = $db->fetchArray($customviewrs);
				$suffix = ' - ' . \App\Language::translate($customview['viewname'], $this->getTargetModule());
				if (!empty($this->extraData['groupField'])) {
					$groupFieldModel = Vtiger_Field_Model::getInstance($this->extraData['groupField'], $this->getTargetModuleModel());
					$suffix .= ' - ' . \App\Language::translate($groupFieldModel->getFieldLabel(), $this->getTargetModule());
				}
			}
			return $prefix . \App\Language::translate($this->getTargetModuleModel()->label, $this->getTargetModule()) . $suffix;
		}
		return $title;
	}

	public function getTotalCountURL()
	{
		return 'index.php?module=' . $this->getTargetModule() . '&action=Pagination&mode=getTotalCount&viewname=' . $this->widgetModel->get('filterid');
	}

	public function getListViewURL()
	{
		return 'index.php?module=' . $this->getTargetModule() . '&view=List&viewname=' . $this->widgetModel->get('filterid');
	}
}
