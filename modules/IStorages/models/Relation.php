<?php

/**
 * Relation Model Class
 * @package YetiForce.Model
 * @license licenses/License.html
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class IStorages_Relation_Model extends Vtiger_Relation_Model
{

	public function getManyToMany(){
		
		$queryGenerator = $this->getQueryGenerator();
		$relatedModuleName = $this->getRelationModuleName();
		$referenceInfo = Vtiger_Relation_Model::getReferenceTableInfo($relatedModuleName, $this->getParentModuleModel()->getName());
		$queryGenerator->setCustomColumn('u_#__istorages_products.qtyinstock qtyproductinstock');
		$queryGenerator->addJoin(['INNER JOIN', $referenceInfo['table'], $referenceInfo['table'] . '.' . $referenceInfo['rel'] . ' = vtiger_crmentity.crmid']);
		$queryGenerator->addAndConditionNative([$referenceInfo['table'] . '.' . $referenceInfo['base'] => $this->get('parentRecord')->getId()]);
	}
}
