<?php

/**
 * Quick detail  view class.
 *
 * @package   View
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Vtiger_QuickDetail_View extends \App\Controller\Modal
{
	/**
	 * Modal size.
	 *
	 * @var string
	 */
	public $modalSize = 'modal-lg modalRightSiteBar';
	/**
	 * Show modal header.
	 *
	 * @var bool
	 */
	public $showHeader = false;
	/**
	 * Show modal footer.
	 *
	 * @var bool
	 */
	public $showFooter = false;
	/**
	 * Record model instance.
	 *
	 * @var Vtiger_Record_Model
	 */
	public $recordModel;

	/**
	 * Checking permissions.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\NoPermittedToRecord
	 */
	public function checkPermission(App\Request $request)
	{
		if ($request->isEmpty('record', true)) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
		if (!\App\Privilege::isPermitted($request->getModule(), 'DetailView', $request->getInteger('record'))) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
	}

	/**
	 * Process.
	 *
	 * @param \App\Request $request
	 */
	public function process(App\Request $request)
	{
		$moduleName = $request->getModule();
		if ($request->has('modalSize')) {
			$this->modalSize .= ' ' . $request->getByType('modalSize', 'AlnumExtended');
		}
		$this->recordModel = Vtiger_Record_Model::getInstanceById($request->getInteger('record'), $moduleName);
		$tabName = $request->has('tab') ? $request->getByType('tab') : null;
		$viewer = $this->getViewer($request);
		$viewer->assign('RECORD', $this->recordModel);
		$viewer->assign('COMPONENTS', $this->getComponents($tabName));
		$showCloseBtn = true;
		if ($request->getBoolean('hideCloseBtn')) {
			$showCloseBtn = false;
		}
		$viewer->assign('SHOW_CLOSE_BTN', $showCloseBtn);
		$viewer->view('QuickDetail/Modal.tpl', $moduleName);
	}

	/**
	 * Get components.
	 *
	 * @param string $tabName
	 *
	 * @return array
	 */
	protected function getComponents(?string $tabName = null)
	{
		$tabsStructure = \App\Config::module($this->recordModel->getModuleName(), 'quickDetailTabsContent');
		if (empty($tabsStructure)) {
			$fields = [];
			foreach ($this->recordModel->getModule()->getFields() as $fieldName => $fieldModel) {
				if ($fieldModel->isSummaryField() && $fieldModel->isViewableInDetailView()) {
					$fields[] = ['fieldName' => $fieldName, 'showLabel' => true];
				}
			}
			$structure = [[
				'type' => 'Fields',
				'fields' => $fields
			]];
		} elseif ($tabName && isset($tabsStructure[$tabName])) {
			$structure = $tabsStructure[$tabName];
		} else {
			$structure = reset($tabsStructure);
		}
		return $structure;
	}

	/**
	 * Get relation records.
	 *
	 * @param array $relation
	 *
	 * @return array
	 */
	public function getRelationRecords(array $relation): array
	{
		if (isset($relation['fromParentField']) && $this->recordModel->get($relation['fromParentField'])) {
			$recordModel = Vtiger_Record_Model::getInstanceById($this->recordModel->get($relation['fromParentField']));
		} else {
			$recordModel = $this->recordModel;
		}
		$relationListView = Vtiger_RelationListView_Model::getInstance($recordModel, $relation['module']);
		$relationListView->setFields(array_merge(['id'], $relation['fields']));
		if (isset($relation['orderBy'], $relation['sortOrder'])) {
			$relationListView->set('orderBy', $relation['orderBy']);
			$relationListView->set('sortOrder', $relation['sortOrder']);
		}
		if (isset($relation['searchParmams'])) {
			$relationListView->set('search_params', $relationListView->getQueryGenerator()->parseBaseSearchParamsToCondition($relation['searchParmams']));
		}
		$pagingModel = new Vtiger_Paging_Model();
		$pagingModel->set('limit', $relation['limit'] ?? 10);
		return [
			'headers' => $relationListView->getHeaders(),
			'entries' => $relationListView->getEntries($pagingModel),
		];
	}
}
