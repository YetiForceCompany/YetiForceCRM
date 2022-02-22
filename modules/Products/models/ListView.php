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

class Products_ListView_Model extends Vtiger_ListView_Model
{
	/**
	 * Function to get the list view entries.
	 *
	 * @param Vtiger_Paging_Model $pagingModel
	 * @param mixed               $searchResult
	 *
	 * @return array - Associative array of record id mapped to Vtiger_Record_Model instance
	 */
	public function getListViewEntries(Vtiger_Paging_Model $pagingModel, $searchResult = false)
	{
		$moduleModel = $this->getModule();
		$moduleName = $moduleModel->get('name');
		$queryGenerator = $this->getQueryGenerator();
		// Limit the choice of products/services only to the ones related to currently selected Opportunity - last step.
		if (Settings_SalesProcesses_Module_Model::checkRelatedToPotentialsLimit($this->get('src_module'))) {
			if ($this->isEmpty('filterFields')) {
				return [];
			}
			$filterFields = $this->get('filterFields');
			if (!isset($filterFields['salesprocessid']) || empty($salesProcessId = $filterFields['salesprocessid'])) {
				$pagingModel->calculatePageRange(0);
				return [];
			}
			if (\in_array($moduleName, ['Products', 'Services'])) {
				$queryGenerator->addNativeCondition(['or',
					['vtiger_crmentityrel.crmid' => $salesProcessId, 'module' => 'SSalesProcesses'],
					['vtiger_crmentityrel.relcrmid' => $salesProcessId, 'relmodule' => 'SSalesProcesses'],
				]);
				if ('Services' === $moduleName) {
					$queryGenerator->addJoin(['INNER JOIN', 'vtiger_crmentityrel', 'vtiger_crmentityrel.relcrmid = vtiger_service.serviceid OR vtiger_crmentityrel.crmid = vtiger_service.serviceid']);
				} else {
					$queryGenerator->addJoin(['INNER JOIN', 'vtiger_crmentityrel', 'vtiger_crmentityrel.relcrmid = vtiger_products.productid OR vtiger_crmentityrel.crmid = vtiger_products.productid']);
				}
			}
		}
		$this->loadListViewCondition();
		$this->loadListViewOrderBy();
		$query = $queryGenerator->createQuery();
		$pageLimit = $pagingModel->getPageLimit();
		if (0 !== $pagingModel->get('limit')) {
			$query->limit($pageLimit + 1)->offset($pagingModel->getStartIndex());
		}
		$rows = $query->all();
		$count = \count($rows);
		$pagingModel->calculatePageRange($count);
		if ($count > $pageLimit) {
			array_pop($rows);
			$pagingModel->set('nextPageExists', true);
		} else {
			$pagingModel->set('nextPageExists', false);
		}
		$listViewRecordModels = $this->getRecordsFromArray($rows);
		unset($rows);
		return $listViewRecordModels;
	}

	public function getSubProducts($subProductId)
	{
		$flag = false;
		if ($subProductId) {
			$flag = (new App\Db\Query())
				->select(['vtiger_seproductsrel.crmid'])
				->from('vtiger_seproductsrel')->innerJoin('vtiger_crmentity', 'vtiger_seproductsrel.crmid = vtiger_crmentity.crmid')->where(['vtiger_crmentity.deleted' => 0, 'vtiger_seproductsrel.setype' => $this->getModule()->get('name'), 'vtiger_seproductsrel.productid' => $subProductId])->exists();
		}
		return $flag;
	}

	/** {@inheritdoc} */
	public function getAdvancedLinks()
	{
		$advancedLinks = parent::getAdvancedLinks();
		$moduleModel = $this->getModule();
		if ($moduleModel->isPermitted('CreateView') && $moduleModel->isPermitted('Import')) {
			$advancedLinks[] = [
				'linktype' => 'LISTVIEWMASSACTION',
				'linklabel' => 'LBL_IMPORT_STOCKTAKING',
				'linkdata' => ['url' => 'index.php?module=' . $moduleModel->getName() . '&view=StocktakingModal', 'type' => 'modal', 'check-selected' => 0],
				'linkclass' => 'js-mass-action',
				'linkicon' => 'fas fa-boxes'
			];
		}
		return $advancedLinks;
	}
}
