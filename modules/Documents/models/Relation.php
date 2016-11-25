<?php

/**
 * Relation Model Class
 * @package YetiForce.Model
 * @license licenses/License.html
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Documents_Relation_Model extends Vtiger_Relation_Model
{

	public function setExceptionData()
	{
		$data = [
			'tabid' => $this->getParentModuleModel()->getId(),
			'related_tabid' => $this->getRelationModuleModel()->getId(),
			'name' => 'getRelatedRecord',
			'actions' => 'ADD, SELECT',
			'modulename' => $this->getParentModuleModel()->getName()
		];
		$this->setData($data);
	}

	public function deleteRelation($relatedRecordId, $sourceRecordId)
	{
		include_once('modules/ModTracker/ModTracker.php');
		$sourceModule = $this->getParentModuleModel();
		$destinationModuleName = $sourceModule->get('name');
		$sourceModuleName = $this->getRelationModuleModel()->get('name');

		if ($destinationModuleName == 'OSSMailView' || $sourceModuleName == 'OSSMailView') {
			if ($destinationModuleName == 'OSSMailView') {
				$mailId = $relatedRecordId;
				$crmid = $sourceRecordId;
			} else {
				$mailId = $sourceRecordId;
				$crmid = $relatedRecordId;
			}
			$db = PearDatabase::getInstance();
			if ($db->delete('vtiger_ossmailview_relation', 'crmid = ? && ossmailviewid = ?', [$crmid, $mailId]) > 0) {
				return true;
			} else {
				return false;
			}
		} else {
			if ($destinationModuleName == 'ModComments') {
				ModTracker::unLinkRelation($destinationModuleName, $relatedRecordId, $sourceModuleName, $sourceRecordId);
				return true;
			}
			$relationFieldModel = $this->getRelationField();
			if ($relationFieldModel && $relationFieldModel->isMandatory()) {
				return false;
			}
			$destinationModuleFocus = CRMEntity::getInstance($destinationModuleName);
			DeleteEntity($destinationModuleName, $sourceModuleName, $destinationModuleFocus, $relatedRecordId, $sourceRecordId, $this->get('name'));
			ModTracker::unLinkRelation($destinationModuleName, $relatedRecordId, $sourceModuleName, $sourceRecordId);
			return true;
		}
	}

	/**
	 * Function to get related record with document
	 */
	public function getRelatedRecord()
	{
		$queryGenerator = $this->getQueryGenerator();
		$queryGenerator->addJoin(['INNER JOIN', 'vtiger_senotesrel', 'vtiger_senotesrel.crmid = vtiger_crmentity.crmid']);
		$queryGenerator->addNativeCondition(['vtiger_senotesrel.notesid' => $this->get('parentRecord')->getId()]);
	}
}
