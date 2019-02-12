<?php
/**
 * Relation Model Class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

/**
 * Class Documents_Relation_Model.
 */
class Documents_Relation_Model extends Vtiger_Relation_Model
{
	/**
	 * Set exceptional data.
	 */
	public function setExceptionData()
	{
		$data = [
			'tabid' => $this->getParentModuleModel()->getId(),
			'related_tabid' => $this->getRelationModuleModel()->getId(),
			'name' => 'getRelatedRecord',
			'actions' => 'ADD, SELECT',
			'modulename' => $this->getParentModuleModel()->getName(),
		];
		$this->setData($data);
	}

	/**
	 * Delete relation.
	 *
	 * @param int $relatedRecordId
	 * @param int $sourceRecordId
	 *
	 * @return bool
	 */
	public function deleteRelation($relatedRecordId, $sourceRecordId)
	{
		include_once 'modules/ModTracker/ModTracker.php';
		$sourceModule = $this->getParentModuleModel();
		$destinationModuleName = $sourceModule->get('name');
		$sourceModuleName = $this->getRelationModuleModel()->get('name');

		if ($destinationModuleName == 'OSSMailView' || $sourceModuleName == 'OSSMailView') {
			if ($destinationModuleName == 'OSSMailView') {
				$mailId = $relatedRecordId;
				$crmId = $sourceRecordId;
			} else {
				$mailId = $sourceRecordId;
				$crmId = $relatedRecordId;
			}

			if (\App\Db::getInstance()->createCommand()->delete('vtiger_ossmailview_relation', ['crmid' => $crmId, 'ossmailviewid' => $mailId])->execute()) {
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
			vtlib\Deprecated::deleteEntity($destinationModuleName, $sourceModuleName, $destinationModuleFocus, $relatedRecordId, $sourceRecordId, $this->get('name'));
			ModTracker::unLinkRelation($destinationModuleName, $relatedRecordId, $sourceModuleName, $sourceRecordId);

			return true;
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function transferDb(array $params)
	{
		return \App\Db::getInstance()->createCommand()->update('vtiger_senotesrel', ['crmid' => $params['sourceRecordId'], 'notesid' => $params['destinationRecordId']], ['crmid' => $params['fromRecordId'], 'notesid' => $params['destinationRecordId']])->execute();
	}

	/**
	 * Function to get related record with document.
	 */
	public function getRelatedRecord()
	{
		$queryGenerator = $this->getQueryGenerator();
		$queryGenerator->addJoin(['INNER JOIN', 'vtiger_senotesrel', 'vtiger_senotesrel.crmid = vtiger_crmentity.crmid']);
		$queryGenerator->addNativeCondition(['vtiger_senotesrel.notesid' => $this->get('parentRecord')->getId()]);
	}
}
