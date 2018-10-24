<?php

/**
 * Multifilter model.
 *
 * @copyright YetiForce Sp. z o.o.
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Dudek <a.dudek@yetiforce.com>
 */
class Vtiger_Multifilter_Model extends Vtiger_Widget_Model
{
	/**
	 * Widget model.
	 *
	 * @var \Vtiger_Widget_Model
	 */
	protected $widgetModel;

	/**
	 * Extra data.
	 *
	 * @var array
	 */
	protected $extraData;

	/**
	 * QueryGenerator model.
	 *
	 * @var QueryGenerator
	 */
	protected $queryGenerator;

	/**
	 * List of view headers.
	 *
	 * @var array
	 */
	protected $listviewHeaders;

	/**
	 * List of view records.
	 *
	 * @var array
	 */
	protected $listviewRecords;

	/**
	 * Target module model.
	 *
	 * @var \Vtiger_Module_Model
	 */
	protected $targetModuleModel;

	/**
	 * Filter id.
	 *
	 * @var int
	 */
	protected $filtersId;

	/**
	 * Module name.
	 *
	 * @var string
	 */
	protected $modulesName;

	/**
	 * Search condition.
	 *
	 * @var array
	 */
	protected $searchParams = [];

	/**
	 * Set numer of shown columns in list.
	 *
	 * @var array
	 */
	const SHOW_COMULNS = 4;

	/**
	 * Set widget model to show.
	 *
	 * @param $widgetModel
	 *
	 * @throws \App\Exceptions\AppException
	 */
	public function setWidgetModel($widgetModel)
	{
		$this->widgetModel = $widgetModel;
		$this->extraData = $this->widgetModel->get('data');
		if (is_string($this->extraData)) {
			$this->extraData = \App\Json::decode(App\Purifier::decodeHtml($this->extraData));
		}
	}

	/**
	 * Set filter id to show.
	 *
	 * @param $filterId
	 */
	public function setFilterId($filterId)
	{
		$this->filtersId = $filterId;
	}

	/**
	 * Set actual module name.
	 *
	 * @param $modulesName
	 */
	public function setModulesName($modulesName)
	{
		$this->modulesName = $modulesName;
	}

	/**
	 * Return filter id.
	 *
	 * @return int
	 */
	public function getFilterId()
	{
		return $this->filtersId;
	}

	/**
	 * Return target module name.
	 *
	 * @return string
	 */
	public function getTargetModule()
	{
		return $this->modulesName;
	}

	/**
	 * @throws \App\Exceptions\NoPermitted
	 *
	 * @return array
	 */
	public function getTargetFields()
	{
		$selectedModule = $this->getTargetModule();
		if (!\App\Privilege::isPermitted($selectedModule)) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
		$queryGeneratorInstance = new \App\QueryGenerator($selectedModule);
		$queryGeneratorInstance->initForCustomViewById($this->getFilterId());
		$fields = [];
		foreach ($queryGeneratorInstance->getListViewFields() as $field) {
			if (self::SHOW_COMULNS <= count($fields)) {
				break;
			}
			$fields[] = $field->getColumnName();
		}
		if (!in_array('id', $fields)) {
			$fields[] = 'id';
		}
		return $fields;
	}

	/**
	 * Return target module model.
	 *
	 * @return Vtiger_Module_Model
	 */
	public function getTargetModuleModel()
	{
		if (!$this->targetModuleModel) {
			$this->targetModuleModel = Vtiger_Module_Model::getInstance($this->getTargetModule());
		}
		return $this->targetModuleModel;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function initListViewController()
	{
		if (!$this->queryGenerator) {
			$this->queryGenerator = new \App\QueryGenerator($this->getTargetModule());
			$this->queryGenerator->initForCustomViewById($this->getFilterId());
			$this->queryGenerator->setFields($this->getTargetFields());
			$this->listviewHeaders = $this->listviewRecords = null;
		}
	}

	/**
	 * Get title of widget.
	 *
	 * @param string $prefix
	 *
	 * @return mixed|string
	 */
	public function getTitle($prefix = '')
	{
		$this->initListViewController();
		$title = $this->widgetModel->get('title');
		if (empty($title)) {
			$suffix = '';
			$viewName = (new App\Db\Query())->select(['viewname'])->from(['vtiger_customview'])->where(['cvid' => $this->widgetModel->get('filterid')])->scalar();
			if ($viewName) {
				$suffix = ' - ' . \App\Language::translate($viewName, $this->getTargetModule());
			}
			return $prefix . \App\Language::translate($this->getTargetModuleModel()->label, $this->getTargetModule()) . $suffix;
		}
		return $title;
	}

	/**
	 * Get columns name.
	 *
	 * @return array
	 */
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

	/**
	 * Function to get the list view header.
	 *
	 * @return array - List of Vtiger_Field_Model instances
	 */
	public function getListViewHeaders()
	{
		$headerFieldModels = [];
		$headerFields = $this->getQueryGenerator()->getListViewFields();
		foreach ($headerFields as $fieldName => $fieldsModel) {
			if ($fieldsModel && (!$fieldsModel->isViewable() || !$fieldsModel->getPermissions())) {
				continue;
			}
			$headerFieldModels[$fieldName] = $fieldsModel;
		}
		return $headerFieldModels;
	}

	/**
	 * Return header count.
	 *
	 * @return int
	 */
	public function getHeaderCount()
	{
		return count($this->getHeaders());
	}

	/**
	 * Return record limit.
	 *
	 * @return int
	 */
	public function getRecordLimit()
	{
		return (int) $this->widgetModel->get('limit');
	}

	/**
	 * Return records list.
	 *
	 * @return array
	 */
	public function getRecords()
	{
		$this->initListViewController();
		if (!$this->listviewRecords) {
			if (!empty($this->searchParams)) {
				$searchConditions = $this->queryGenerator->parseBaseSearchParamsToCondition($this->searchParams);
				$this->queryGenerator->parseAdvFilter($searchConditions);
			}
			$targetModuleName = $this->getTargetModule();
			$targetModuleFocus = CRMEntity::getInstance($targetModuleName);
			$filterModel = CustomView_Record_Model::getInstanceById($this->getFilterId());
			if (!empty($filterModel->get('sort'))) {
				list($orderby, $sort) = explode(',', $filterModel->get('sort'));
				$this->queryGenerator->setOrder($orderby, $sort);
			} elseif ($targetModuleFocus->default_order_by && $targetModuleFocus->default_sort_order) {
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

	/**
	 * Get total count URL.
	 *
	 * @return string
	 */
	public function getTotalCountURL()
	{
		return 'index.php?module=' . $this->getTargetModule() . '&action=Pagination&mode=getTotalCount&viewname=' . $this->getFilterId();
	}

	/**
	 * Get list view URL.
	 *
	 * @return string
	 */
	public function getListViewURL()
	{
		return 'index.php?module=' . $this->getTargetModule() . '&view=List&viewname=' . $this->getFilterId();
	}
}
