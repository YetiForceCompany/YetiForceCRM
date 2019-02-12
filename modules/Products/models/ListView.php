<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class Products_ListView_Model extends Vtiger_ListView_Model
{
	/**
	 * Set list view order by.
	 */
	public function loadListViewOrderBy()
	{
		//List view will be displayed on recently created/modified records
		if (empty($this->getForSql('orderby')) && empty($this->getForSql('sortorder')) && $this->getModule()->get('name') != 'Users') {
			$this->set('orderby', 'modifiedtime');
			$this->set('sortorder', 'DESC');
		}
		parent::loadListViewOrderBy();
	}

	/**
	 * Function to get the list view entries.
	 *
	 * @param Vtiger_Paging_Model $pagingModel
	 *
	 * @return array - Associative array of record id mapped to Vtiger_Record_Model instance
	 */
	public function getListViewEntries(Vtiger_Paging_Model $pagingModel, $searchResult = false)
	{
		$moduleModel = $this->getModule();
		$moduleName = $moduleModel->get('name');
		$queryGenerator = $this->get('query_generator');
		// Limit the choice of products/services only to the ones related to currently selected Opportunity - last step.
		if (Settings_SalesProcesses_Module_Model::checkRelatedToPotentialsLimit($this->get('src_module'))) {
			if ($this->isEmpty('salesprocessid')) {
				$pagingModel->calculatePageRange(0);

				return [];
			}
			if ($moduleName === 'Products') {
				$queryGenerator->addJoin(['INNER JOIN', 'vtiger_crmentityrel', 'vtiger_crmentityrel.relcrmid = vtiger_products.productid OR vtiger_crmentityrel.crmid = vtiger_products.productid']);
			} elseif ($moduleName === 'Services') {
				$queryGenerator->addJoin(['INNER JOIN', 'vtiger_crmentityrel', 'vtiger_crmentityrel.relcrmid = vtiger_service.serviceid OR vtiger_crmentityrel.crmid = vtiger_service.serviceid']);
			}
			if (in_array($moduleName, ['Products', 'Services'])) {
				$queryGenerator->addNativeCondition(['or',
					['vtiger_crmentityrel.crmid' => $this->get('salesprocessid'), 'module' => 'SSalesProcesses'],
					['vtiger_crmentityrel.relcrmid' => $this->get('salesprocessid'), 'relmodule' => 'SSalesProcesses'],
				]);
			}
		}
		$this->loadListViewCondition();
		$this->loadListViewOrderBy();
		$query = $queryGenerator->createQuery();
		if ($this->get('subProductsPopup')) {
			$this->addSubProductsQuery($query);
		}
		$sourceModule = $this->get('src_module');
		$sourceField = $this->get('src_field');
		$pageLimit = $pagingModel->getPageLimit();
		//For Products popup in Price Book Related list
		if ($sourceModule !== 'PriceBooks' && $sourceField !== 'priceBookRelatedList') {
			$query->limit($pageLimit + 1)->offset($pagingModel->getStartIndex());
		}
		$rows = $query->all();
		$count = count($rows);
		$pagingModel->calculatePageRange($count);
		if ($count > $pageLimit && $sourceModule !== 'PriceBooks' && $sourceField !== 'priceBookRelatedList') {
			array_pop($rows);
			$pagingModel->set('nextPageExists', true);
		} else {
			$pagingModel->set('nextPageExists', false);
		}
		$listViewRecordModels = $this->getRecordsFromArray($rows);
		unset($rows);
		return $listViewRecordModels;
	}

	public function addSubProductsQuery(App\Db\Query $listQuery)
	{
		$listQuery->leftJoin('vtiger_seproductsrel', 'vtiger_seproductsrel.crmid = vtiger_products.productid AND vtiger_seproductsrel.setype=:products', [':products' => 'Products']);
		$listQuery->andWhere(['vtiger_seproductsrel.productid' => $this->get('productId')]);
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

	/**
	 * Function to get the list view count.
	 *
	 * @return int
	 */
	public function getListViewCount()
	{
		$query = $this->get('query_generator')->createQuery();
		if ($this->get('subProductsPopup')) {
			$this->addSubProductsQuery($query);
		}
		return $query->count();
	}
}
