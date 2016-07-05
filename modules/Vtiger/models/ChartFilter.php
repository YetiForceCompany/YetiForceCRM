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
		$groupFieldModel = Vtiger_Field_Model::getInstance($groupField, $this->getTargetModuleModel());
		$groupField = $groupFieldModel->get('column');
		$queryGenerator = new QueryGenerator($this->getTargetModule(), $currentUserModel);
		$queryGenerator->initForCustomViewById($filterId);
		$fields = $queryGenerator->getFields();
		$fields[] = $groupField;
		$queryGenerator->setFields($fields);
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
			$groupFieldModel = Vtiger_Field_Model::getInstance($this->extraData['groupField'], $this->getTargetModuleModel());
			$suffix .= ' - ' . vtranslate($groupFieldModel->getFieldLabel(), $this->getTargetModule());
		}
		return $prefix . vtranslate($this->getTargetModuleModel()->label, $this->getTargetModule()) . $suffix;
	}

	public function getKeyMetricsWithCount()
	{
		$db = PearDatabase::getInstance();
		$currenUserModel = Users_Record_Model::getCurrentUserModel();
		require_once 'modules/CustomView/ListViewTop.php';
		$metriclists = getMetricList([$this->widgetModel->get('filterid')]);

		if (!empty($metriclists)) {
			$metriclist = current($metriclists);
			$queryGenerator = new QueryGenerator($metriclist['module'], $currenUserModel);
			$queryGenerator->initForCustomViewById($metriclist['id']);
			if ($metriclist['module'] == "Calendar") {
				// For calendar we need to eliminate emails or else it will break in status empty condition
				$queryGenerator->addCondition('activitytype', "Emails", 'n', QueryGenerator::$AND);
			}
			$metricsql = $queryGenerator->getQuery();
			$metricresult = $db->query(Vtiger_Functions::mkCountQuery($metricsql));
			if ($metricresult) {
				$rowcount = $db->fetch_array($metricresult);
				return $rowcount['count'];
			}
		}
		return false;
	}

	public function getListViewURL()
	{
		return 'index.php?module=' . $this->getTargetModule() . '&view=List&viewname=' . $this->widgetModel->get('filterid');
	}
}
