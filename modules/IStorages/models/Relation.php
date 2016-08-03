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
			$queryGenerator = $relationListView_Model->get('query_generator');
			$joinTable = $queryGenerator->getFromClause(true);
			if ($joinTable) {
				$queryComponents = preg_split('/WHERE/i', $query);
				$query = $queryComponents[0] . $joinTable . ' WHERE ' . $queryComponents[1];
			}
			$where = $queryGenerator->getWhereClause(true);
			$query .= $where;
		}
		return $query;
	}
}
