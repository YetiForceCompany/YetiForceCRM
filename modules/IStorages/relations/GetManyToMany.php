<?php
/**
 * Main file that includes basic operations on relations.
 *
 * @package   Relation
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

/**
 * IStorages_GetManyToMany_Relation class.
 */
class IStorages_GetManyToMany_Relation extends Vtiger_GetManyToMany_Relation
{
	/** {@inheritdoc} */
	public function getQuery()
	{
		$relatedModuleName = $this->relationModel->getRelationModuleName();
		if ('Products' === $relatedModuleName) {
			$parentModuleName = $this->relationModel->getParentModuleModel()->getName();
			$referenceInfo = \Vtiger_Relation_Model::getReferenceTableInfo($relatedModuleName, $parentModuleName);
			$this->relationModel->getQueryGenerator()->setCustomColumn(['qtyproductinstock' => $referenceInfo['table'] . '.qtyinstock']);
		}
		parent::getQuery();
	}
}
