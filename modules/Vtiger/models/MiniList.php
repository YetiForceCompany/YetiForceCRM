<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce S.A.
 * *********************************************************************************** */

class Vtiger_MiniList_Model extends Vtiger_Widget_Model
{
	protected $widgetModel;
	protected $extraData;
	protected $queryGenerator;
	protected $listviewHeaders;
	protected $listviewRecords;
	protected $targetModuleModel;

	/**
	 * Search condition.
	 *
	 * @var array
	 */
	protected $searchParams = [];

	public function setWidgetModel($widgetModel)
	{
		$this->widgetModel = $widgetModel;
		$this->extraData = $this->widgetModel->get('data');

		// Decode data if not done already.
		if (\is_string($this->extraData)) {
			$this->extraData = \App\Json::decode(App\Purifier::decodeHtml($this->extraData));
		}
		if (null === $this->extraData) {
			throw new \App\Exceptions\AppException('Invalid data');
		}
	}

	/**
	 * Set search condition.
	 *
	 * @param array $searchParams
	 */
	public function setSearchParams($searchParams)
	{
		$this->searchParams = $searchParams;
	}

	public function getTargetModule()
	{
		return $this->extraData['module'];
	}

	public function getTargetFields($extraField = false)
	{
		$fields = $this->extraData['fields'];
		$moduleName = $this->getTargetModule();
		if (!\in_array('id', $fields)) {
			$fields[] = 'id';
		}
		if ('Calendar' === $moduleName && $extraField) {
			$moduleModel = $this->getTargetModuleModel();
			if (\in_array('date_start', $fields) && ($fieldModel = $moduleModel->getFieldByName('time_start')) && $fieldModel->isActiveField() && $fieldModel->isViewable()) {
				$fields[] = 'time_start';
			}
			if (\in_array('due_end', $fields) && ($fieldModel = $moduleModel->getFieldByName('time_end')) && $fieldModel->isActiveField() && $fieldModel->isViewable()) {
				$fields[] = 'time_end';
			}
		}
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
			$this->queryGenerator->setFields($this->getTargetFields(true));
			$this->listviewHeaders = $this->listviewRecords = null;
		}
	}

	public function getTitle($prefix = '')
	{
		$this->initListViewController();
		$title = $this->widgetModel->get('title');
		if (empty($title)) {
			$suffix = '';
			$cvId = (int) $this->widgetModel->get('filterid');
			$viewName = \App\CustomView::getCVDetails($cvId, $this->getTargetModule())['viewname'] ?? '';
			if ($viewName) {
				$suffix = ' - ' . \App\Language::translate($viewName, $this->getTargetModule());
			}

			return $prefix . \App\Language::translate($this->getTargetModuleModel()->label, $this->getTargetModule()) . $suffix;
		}
		return $title;
	}

	public function getHeaders()
	{
		$this->initListViewController();
		if (!$this->listviewHeaders) {
			foreach ($this->getTargetFields() as $fieldName) {
				if ('id' !== $fieldName && ($fieldModel = $this->getTargetModuleModel()->getFieldByName($fieldName))) {
					$this->listviewHeaders[$fieldName] = $fieldModel;
				} else {
					\App\Log::warning('Field not found:' . $fieldName . ' | Module: ' . $this->getTargetModuleModel()->getName(), __METHOD__);
				}
			}
		}
		return $this->listviewHeaders;
	}

	public function getHeaderCount()
	{
		return \count($this->getHeaders());
	}

	public function getRecordLimit()
	{
		return $this->widgetModel->get('limit');
	}

	public function getRecords($user)
	{
		$this->initListViewController();
		if (!$user) {
			$user = App\User::getCurrentUserId();
		} elseif ('all' === $user) {
			$user = '';
		}

		if (!$this->listviewRecords) {
			if (!empty($user)) {
				$this->queryGenerator->addCondition('assigned_user_id', $user, 'e');
			}
			if (!empty($this->searchParams)) {
				$searchParamsCondition = $this->queryGenerator->parseBaseSearchParamsToCondition($this->searchParams);
				$this->queryGenerator->parseAdvFilter($searchParamsCondition);
			}
			$targetModuleName = $this->getTargetModule();
			$targetModuleFocus = CRMEntity::getInstance($targetModuleName);
			$filterModel = CustomView_Record_Model::getInstanceById($this->widgetModel->get('filterid'));
			if ($filterModel && ($orderBy = $filterModel->getSortOrderBy()) && \is_array($orderBy)) {
				$fields = $this->queryGenerator->getModuleModel()->getFields();
				foreach ($orderBy as $fieldName => $sortFlag) {
					[$fieldName, $moduleName, $sourceFieldName] = array_pad(explode(':', $fieldName), 3, false);
					if ($sourceFieldName && isset($fields[$sourceFieldName])) {
						$this->queryGenerator->setRelatedOrder([
							'sourceField' => $sourceFieldName,
							'relatedModule' => $moduleName,
							'relatedField' => $fieldName,
							'relatedSortOrder' => $sortFlag,
						]);
					} elseif (isset($fields[$fieldName])) {
						$this->queryGenerator->setOrder($fieldName, $sortFlag);
					}
				}
			} elseif ($targetModuleFocus->default_order_by && $targetModuleFocus->default_sort_order) {
				foreach ((array) $targetModuleFocus->default_order_by as $value) {
					$this->queryGenerator->setOrder($value, $targetModuleFocus->default_sort_order);
				}
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

	/**
	 * Get total count URL.
	 *
	 * @param mixed $user
	 *
	 * @return string
	 */
	public function getTotalCountURL($user = false)
	{
		$url = 'index.php?module=' . $this->getTargetModule() . '&action=Pagination&mode=getTotalCount&viewname=' . $this->widgetModel->get('filterid');
		if (!$user) {
			$user = App\User::getCurrentUserId();
		}
		$searcParams = [];
		if (!empty($this->searchParams)) {
			foreach (reset($this->searchParams) as $value) {
				$searcParams[] = $value;
			}
		}
		if ('all' !== $user) {
			$searcParams[] = ['assigned_user_id', 'e', $user];
		}
		if ($searcParams) {
			return $url .= '&search_params=[' . json_encode($searcParams) . ']';
		}
		return $url;
	}

	/**
	 * Get list view URL.
	 *
	 * @param mixed $user
	 *
	 * @return string
	 */
	public function getListViewURL($user = false)
	{
		$url = 'index.php?module=' . $this->getTargetModule() . '&view=List&viewname=' . $this->widgetModel->get('filterid');
		if (!$user) {
			$user = App\User::getCurrentUserId();
		}
		$searcParams = [];
		if (!empty($this->searchParams)) {
			foreach (reset($this->searchParams) as $value) {
				$searcParams[] = $value;
			}
		}
		if ('all' !== $user) {
			$searcParams[] = ['assigned_user_id', 'e', $user];
		}
		if ($searcParams) {
			return $url .= '&search_params=[' . json_encode($searcParams) . ']';
		}
		return $url;
	}
}
