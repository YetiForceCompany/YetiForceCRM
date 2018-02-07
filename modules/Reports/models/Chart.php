<?php
/* * ***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */
Vtiger_Loader::includeOnce('~modules/Reports/models/BaseChart.php');
Vtiger_Loader::includeOnce('~modules/Reports/models/LineChart.php');
Vtiger_Loader::includeOnce('~modules/Reports/models/PieChart.php');
Vtiger_Loader::includeOnce('~modules/Reports/models/HorizontalbarChart.php');
Vtiger_Loader::includeOnce('~modules/Reports/models/VerticalbarChart.php');

class Reports_Chart_Model extends \App\Base
{

	public static function getInstanceById($reportModel)
	{
		$self = new self();
		$db = PearDatabase::getInstance();
		$result = $db->pquery('SELECT * FROM vtiger_reporttype WHERE reportid = ?', [$reportModel->getId()]);
		$data = $db->queryResult($result, 0, 'data');
		if (!empty($data)) {
			$decodeData = \App\Json::decode(App\Purifier::decodeHtml($data));
			$self->setData($decodeData);
			$self->setParent($reportModel);
			$self->setId($reportModel->getId());
		}
		return $self;
	}

	public function getId()
	{
		return $this->get('reportid');
	}

	public function setId($id)
	{
		$this->set('reportid', $id);
	}

	public function getParent()
	{
		return $this->parent;
	}

	public function setParent($parent)
	{
		$this->parent = $parent;
	}

	public function getChartType()
	{
		$type = $this->get('type');
		if (empty($type))
			$type = 'pieChart';
		return $type;
	}

	public function getGroupByField()
	{
		return $this->get('groupbyfield');
	}

	public function getDataFields()
	{
		return $this->get('datafields');
	}

	public function getData()
	{
		$type = ucfirst($this->getChartType());
		$chartModel = new $type($this);
		return $chartModel->generateData();
	}
}
