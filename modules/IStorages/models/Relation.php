<?php

/**
 * Relation Model Class
 * @package YetiForce.Model
 * @license licenses/License.html
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class IStorages_Relation_Model extends Vtiger_Relation_Model
{

	public function getQuery($parentRecord, $actions = false, $relationListView_Model = false)
	{
		$parentModuleModel = $this->getParentModuleModel();
		$relatedModuleModel = $this->getRelationModuleModel();
		$parentModuleName = $parentModuleModel->getName();
		$relatedModuleName = $relatedModuleModel->getName();
		$functionName = $this->get('name');
		$query = $parentModuleModel->getRelationQuery($parentRecord->getId(), $functionName, $relatedModuleModel, $this, $relationListView_Model);
		if ($functionName == 'get_many_to_many' && $relatedModuleName == 'Products') {
			$query = explode('FROM', $query);
			$query[0] = $query[0] . ', u_yf_istorages_products.qtyinstock as qtyproductinstock ';
			$query = implode('FROM', $query);
		}
		if ($relationListView_Model) {
			$searchParams = $relationListView_Model->get('search_params');
			$this->addSearchConditions($query, $searchParams, $relatedModuleName);
		}
		return $query;
	}
}
