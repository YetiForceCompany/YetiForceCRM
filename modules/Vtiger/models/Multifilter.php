<?php

/**
 * Multifilter model.
 *
 * @package Model
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Dudek <a.dudek@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
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
	 * ListView model.
	 *
	 * @var Vtiger_ListView_Model
	 */
	protected $listViewModel;

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
		if (\is_string($this->extraData)) {
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

	/** {@inheritdoc} */
	protected function initListViewController()
	{
		if (!$this->listViewModel) {
			$this->listViewModel = Vtiger_ListView_Model::getInstance($this->getTargetModule(), $this->getFilterId());
		}
	}

	/**
	 * Get columns name.
	 *
	 * @return array
	 */
	public function getHeaders()
	{
		if (!$this->listviewHeaders) {
			$this->initListViewController();
			$this->listviewHeaders = \array_slice($this->listViewModel->getListViewHeaders(), 0, static::SHOW_COMULNS);
		}
		return $this->listviewHeaders;
	}

	/**
	 * Return header count.
	 *
	 * @return int
	 */
	public function getHeaderCount()
	{
		return \count($this->getHeaders());
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
		if (!isset($this->listviewRecords)) {
			$this->initListViewController();
			$customViewModel = CustomView_Record_Model::getInstanceById($this->getFilterId());
			if (!($orderBy = $customViewModel->getSortOrderBy())) {
				$this->listViewModel->set('orderby', $orderBy);
			}
			$fields = array_column($this->getHeaders(), 'name');
			$fields[] = 'id';
			$this->listViewModel->getQueryGenerator()->setFields($fields);
			$pagingModel = (new Vtiger_Paging_Model())->set('limit', $this->getRecordLimit());
			$this->listviewRecords = $this->listViewModel->getListViewEntries($pagingModel);
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
