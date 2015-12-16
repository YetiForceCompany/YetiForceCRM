<?php

/**
 * Relation Class for Accounts
 * @package YetiForce.Model
 * @license licenses/License.html
 * @author Maciej Stencel <m.stencel@yetiforce.com>
 */
class Accounts_Relation_Model extends Vtiger_Relation_Model
{

	public function deleteRelation($sourceRecordId, $relatedRecordId)
	{
		if ($this->relatedModule->getName() == 'OSSMailView') {
			$db = PearDatabase::getInstance();
			if ($db->delete('vtiger_ossmailview_relation', 'crmid = ? AND ossmailviewid = ?', [$sourceRecordId, $relatedRecordId]) > 0) {
				return true;
			} else {
				return false;
			}
		} else {
			$sourceModule = $this->getParentModuleModel();
			$sourceModuleName = $sourceModule->get('name');
			$destinationModuleName = $this->getRelationModuleModel()->get('name');
			$destinationModuleFocus = CRMEntity::getInstance($destinationModuleName);
			DeleteEntity($destinationModuleName, $sourceModuleName, $destinationModuleFocus, $relatedRecordId, $sourceRecordId);
			return true;
		}
	}
}
