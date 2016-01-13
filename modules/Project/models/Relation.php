<?php

/**
 * Relation Class for Projects
 * @package YetiForce.Model
 * @license licenses/License.html
 * @author Maciej Stencel <m.stencel@yetiforce.com>
 */
class Project_Relation_Model extends Vtiger_Relation_Model
{

	/**
	 * Function that deletes Project related records information
	 * @param <Integer> $sourceRecordId - Project Id
	 * @param <Integer> $relatedRecordId - Related Record Id
	 */
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
			$sourceModuleFocus = CRMEntity::getInstance($sourceModuleName);
			$sourceModuleFocus->delete_related_module($sourceModuleName, $sourceRecordId, $destinationModuleName, $relatedRecordId);
			return true;
		}
	}
}
