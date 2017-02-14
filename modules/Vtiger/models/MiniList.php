<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 * *********************************************************************************** */

class Vtiger_MiniList_Model extends Vtiger_Widget_Model
{

	protected $widgetModel;
	protected $extraData;
	protected $queryGenerator;
	protected $listviewHeaders;
	protected $listviewRecords;
	protected $targetModuleModel;

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

	public function getTargetModule()
	{
		return $this->extraData['module'];
	}

	public function getTargetFields()
	{
		$fields = $this->extraData['fields'];
		if (!in_array('id', $fields))
			$fields[] = 'id';
		return $fields;
	}

	public function getTargetModuleModel()
	{
		if (!$this->targetModuleModel) {
			$this->targetModuleModel = Vtiger_Module_Model::getInstance($this->getTargetModule());
		}
		return $this->targetModuleModel;
	}

	protected function initListViewController()
	{
		if (!$this->queryGenerator) {
			$this->queryGenerator = new \App\QueryGenerator($this->getTargetModule());
			$this->queryGenerator->initForCustomViewById($this->widgetModel->get('filterid'));
			$this->queryGenerator->setFields($this->getTargetFields());
			$this->listviewHeaders = $this->listviewRecords = NULL;
		}
	}

	public function getTitle($prefix = '')
	{
		$this->initListViewController();
		$title = $this->widgetModel->get('title');
		if (empty($title)) {
			$db = PearDatabase::getInstance();
			$suffix = '';
			$customviewrs = $db->pquery('SELECT viewname FROM vtiger_customview WHERE cvid=?', array($this->widgetModel->get('filterid')));
			if ($db->num_rows($customviewrs)) {
				$customview = $db->fetch_array($customviewrs);
				$suffix = ' - ' . vtranslate($customview['viewname'], $this->getTargetModule());
			}
			return $prefix . vtranslate($this->getTargetModuleModel()->label, $this->getTargetModule()) . $suffix;
		}
		return $title;
	}

	public function getHeaders()
	{
		$this->initListViewController();
		if (!$this->listviewHeaders) {
			$headerFieldModels = [];
			foreach ($this->queryGenerator->getListViewFields() as $fieldName => &$fieldsModel) {
				$headerFieldModels[$fieldName] = $fieldsModel;
			}
			$this->listviewHeaders = $headerFieldModels;
		}
		return $this->listviewHeaders;
	}

	public function getHeaderCount()
	{
		return count($this->getHeaders());
	}

	public function getRecordLimit()
	{
		return $this->widgetModel->get('limit');
		;
	}

	public function getRecords($user)
	{
		$this->initListViewController();
		if (!$user) {
			$user = App\User::getCurrentUserId();
		} else if ($user === 'all') {
			$user = '';
		}
		if (!$this->listviewRecords) {
			if (!empty($user)) {
				$this->queryGenerator->addNativeCondition(['vtiger_crmentity.smownerid' => $user]);
			}
			$targetModuleName = $this->getTargetModule();
			$targetModuleFocus = CRMEntity::getInstance($targetModuleName);
			$filterId = $this->widgetModel->get('filterid');
			$filterModel = CustomView_Record_Model::getInstanceById($filterId);
			if (!empty($filterModel->get('sort'))) {
				$this->queryGenerator->setOrder(str_replace(',', ' ', $filterModel->get('sort')));
			} else if ($targetModuleFocus->default_order_by && $targetModuleFocus->default_sort_order) {
				$this->queryGenerator->setOrder($targetModuleFocus->default_order_by, $targetModuleFocus->default_sort_order);
			}
			$query = $this->queryGenerator->createQuery();
			$query->limit($this->getRecordLimit());
			$this->listviewRecords = [];
			$dataReader = $query->createCommand()->query();
			while ($row = $dataReader->read()) {
				$this->listviewRecords[$row['id']] = $this->getTargetModuleModel()->getRecordFromArray($row);
			}
		}
		return $this->listviewRecords;
	}

	public function getGetTotalCountURL($user = false)
	{
		$url = 'index.php?module=' . $this->getTargetModule() . '&action=Pagination&mode=getTotalCount&viewname=' . $this->widgetModel->get('filterid');
		if (!$user) {
			$user = App\User::getCurrentUserId();
		}
		return $user === 'all' ? $url : $url .= '&search_params=[[["assigned_user_id","e","' . $user . '"]]]';
	}

	public function getListViewURL($user = false)
	{
		$url = 'index.php?module=' . $this->getTargetModule() . '&view=List&viewname=' . $this->widgetModel->get('filterid');
		if (!$user) {
			$user = App\User::getCurrentUserId();
		}
		return $user === 'all' ? $url : $url .= '&search_params=[[["assigned_user_id","e","' . $user . '"]]]';
	}
}
