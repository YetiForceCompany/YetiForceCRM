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

	static function getInstance()
	{
		Users_Privileges_Model::getAll();
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
		$data = [];
		foreach ($groupData as $fieldName => $value) {
			$data [] = [$value, $fieldName];
		}
		return $data;
	}

	public function getDataPie()
	{
		$groupData = $this->getDataFromFilter();
		$data = [];
		foreach ($groupData as $fieldName => $value) {
			$data [] = ['last_name' => $fieldName, 'id' => $value];
		}
		return $data;
	}

	public function getDataFromFilter()
	{
		$filterId = $this->widgetModel->get('filterid');
		$currentUserModel = Users_Privileges_Model::getCurrentUserModel();
		$groupField = $this->extraData['groupField'];
		$queryGenerator = new QueryGenerator($this->getTargetModule(), $currentUserModel);
		$queryGenerator->initForCustomViewById($filterId);
		$fields = $queryGenerator->getFields();
		$fields[] = $groupField;
		$queryGenerator->setFields($fields);
		$groupFieldModel = Vtiger_Field_Model::getInstance($groupField, $this->getTargetModuleModel());
		$db = PearDatabase::getInstance();
		$result = $db->query($queryGenerator->getQuery());
		$groupData = [];
		while ($row = $db->getRow($result)) {
			$displayValue = $groupFieldModel->getDisplayValue($row[$groupField]);
			if (!isset($groupData[$displayValue])) {
				$groupData[$displayValue] = 1;
			} else {
				$groupData[$displayValue] ++;
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
			$this->extraData = Zend_Json::decode(decode_html($this->extraData));
		}
		if ($this->extraData == NULL) {
			throw new Exception("Invalid data");
		}
	}

	public function getType()
	{
		return $this->extraData['chartType'];
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
		$db = PearDatabase::getInstance();
		$suffix = '';
		$customviewrs = $db->pquery('SELECT viewname FROM vtiger_customview WHERE cvid=?', array($this->widgetModel->get('filterid')));
		if ($db->num_rows($customviewrs)) {
			$customview = $db->fetch_array($customviewrs);
			$suffix = ' - ' . vtranslate($customview['viewname'], $this->getTargetModule());
		}
		return $prefix . vtranslate($this->getTargetModuleModel()->label, $this->getTargetModule()) . $suffix;
	}
}
