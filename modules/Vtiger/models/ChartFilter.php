<?php

/**
 * Model widget chart with a filter
 * @package YetiForce.Model
 * @license licenses/License.html
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
class Vtiger_ChartFilter_Model extends Vtiger_Widget_Model
{

	private $widgetModel;
	private $extraData;
	private $targetModuleModel;

	public function getSearchParams($column, $value)
	{
		return '&search_params=' . json_encode([[[$column, 'e', $value]]]);
	}

	static function getInstance($linkId = 0, $userId = 0)
	{
		return new self();
	}

	public function getDataHorizontal()
	{
		return $this->getDataBarchat();
	}

	public function getDataLine()
	{
		return $this->getDataBarchat();
	}

	public function getDataBarchat()
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

	public function getDataFunnel()
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

	public function getDataPie()
	{
		$groupData = $this->getDataFromFilter();
		$data = [];
		foreach ($groupData as $fieldName => $value) {
			$data [] = ['last_name' => $fieldName, 'id' => $value['count'], '2' => $value['link']];
		}
		return $data;
	}

	private function getSector($sectors, $value)
	{
		$sectorId = false;
		$countSectors = count($sectors);
		foreach ($sectors as $key => $sector) {
			if ($value <= $sector) {
				$sectorId = $key;
				break;
			}
		}
		return $sectorId;
	}

	public function getDataFromFilter()
	{
		$filterId = $this->widgetModel->get('filterid');
		$groupField = $this->extraData['groupField'];
		$groupFieldModel = Vtiger_Field_Model::getInstance($groupField, $this->getTargetModuleModel());
		$fieldName = $groupFieldModel->get('name');
		$queryGenerator = new \App\QueryGenerator($this->getTargetModule());
		$queryGenerator->initForCustomViewById($filterId);
		$queryGenerator->setField($groupField);
		$dataReader = $queryGenerator->createQuery()->createCommand()->query();
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
						$groupData[$displayValue]['link'] = $moduleModel->getListViewUrl() . "&viewname=$filterId" . $this->getSearchParams($fieldName, $row[$groupField]);
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
				if ($sectorId == 0) {
					$groupData[$displayValue]['link'] = $moduleModel->getListViewUrl() . "&viewname=$filterId" . '&search_params=' . json_encode([[[$fieldName, 'm', $sectorValue]]]);
				} else {
					$groupData[$displayValue]['link'] = $moduleModel->getListViewUrl() . "&viewname=$filterId" . '&search_params=' . json_encode([[[$fieldName, 'm', $sectorValue], [$fieldName, 'g', $sectors[$sectorId - 1]]]]);
				}
			}
		}
		return $groupData;
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

	public function setWidgetModel($widgetModel)
	{
		$this->widgetModel = $widgetModel;
		$this->extraData = $this->widgetModel->get('data');

		// Decode data if not done already.
		if (is_string($this->extraData)) {
			$this->extraData = \App\Json::decode(decode_html($this->extraData));
		}
		if ($this->extraData === null) {
			throw new Exception("Invalid data");
		}
	}

	public function getType()
	{
		return $this->extraData['chartType'];
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
			if ($db->num_rows($customviewrs)) {
				$customview = $db->fetch_array($customviewrs);
				$suffix = ' - ' . vtranslate($customview['viewname'], $this->getTargetModule());
				$groupFieldModel = Vtiger_Field_Model::getInstance($this->extraData['groupField'], $this->getTargetModuleModel());
				$suffix .= ' - ' . vtranslate($groupFieldModel->getFieldLabel(), $this->getTargetModule());
			}
			return $prefix . vtranslate($this->getTargetModuleModel()->label, $this->getTargetModule()) . $suffix;
		}
		return $title;
	}

	public function getGetTotalCountURL()
	{
		return 'index.php?module=' . $this->getTargetModule() . '&action=Pagination&mode=getTotalCount&viewname=' . $this->widgetModel->get('filterid');
	}

	public function getListViewURL()
	{
		return 'index.php?module=' . $this->getTargetModule() . '&view=List&viewname=' . $this->widgetModel->get('filterid');
	}
}
