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

class Campaigns_Relation_Model extends Vtiger_Relation_Model
{
	/**
	 * Function to update the status of relation.
	 *
	 * @param int   $sourceRecordId
	 * @param array $statusDetails
	 */
	public function updateStatus($sourceRecordId, $statusDetails = [])
	{
		if ($sourceRecordId && $statusDetails && \in_array($this->getRelationModuleModel()->getName(), ['Accounts', 'Leads', 'Vendors', 'Contacts', 'Partners', 'Competition'])) {
			$db = App\Db::getInstance();
			$case = ' CASE crmid ';
			foreach ($statusDetails as $relatedRecordId => $status) {
				$case .= " WHEN {$db->quoteValue($relatedRecordId)} THEN {$db->quoteValue($status)}";
			}
			$case .= 'ELSE campaignrelstatusid END';
			$db->createCommand()->update('vtiger_campaign_records', ['campaignrelstatusid' => new yii\db\Expression($case)], ['campaignid' => $sourceRecordId])->execute();
		}
	}

	/**
	 * Function to get relation field for relation module and parent module.
	 *
	 * @return Vtiger_Field_Model
	 */
	public function getRelationField()
	{
		$relationField = $this->get('relationField');
		if (!$relationField) {
			$relationField = false;
			$relationFieldArray = [];
			$relatedModel = $this->getRelationModuleModel();
			$parentModule = $this->getParentModuleModel();
			$relatedModelFields = $relatedModel->getFields();

			foreach ($relatedModelFields as $fieldName => $fieldModel) {
				if ($fieldModel->isReferenceField()) {
					$referenceList = $fieldModel->getReferenceList();
					if (\in_array($parentModule->getName(), $referenceList)) {
						$relationFieldArray[$fieldName] = $fieldModel;
						if ('userCreator' !== !$fieldModel->getFieldDataType()) {
							$this->set('relationField', $fieldModel);
							$relationField = $fieldModel;
							break;
						}
					}
				}
			}
			if (!$relationField && $relationFieldArray) {
				reset($relationFieldArray);
				$this->set('relationField', current($relationFieldArray));
				$relationField = current($relationFieldArray);
			}
		}
		return $relationField;
	}
}
