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

/**
 * Vtiger Summary View Record Structure Model.
 */
class Vtiger_SummaryRecordStructure_Model extends Vtiger_DetailRecordStructure_Model
{
	/**
	 * Function to get the values in structured format.
	 *
	 * @return array - values in structure array('block'=>array(fieldinfo));
	 */
	public function getStructure(): array
	{
		$summaryFieldsList = $this->getModule()->getSummaryViewFieldsList();
		$recordModel = $this->getRecord();
		$fieldsDependency = \App\FieldsDependency::getByRecordModel('Detail', $recordModel);
		$blockSeqSortSummaryFields = [];
		if ($summaryFieldsList) {
			foreach ($summaryFieldsList as $fieldName => $fieldModel) {
				if ($fieldModel->isViewableInDetailView() && (empty($fieldsDependency['hide']['backend']) || !\in_array($fieldName, $fieldsDependency['hide']['backend']))) {
					$fieldModel->set('fieldvalue', $recordModel->get($fieldName));
					$blockSequence = $fieldModel->block->sequence;
					if (!empty($fieldsDependency['hide']['frontend']) && \in_array($fieldName, $fieldsDependency['hide']['frontend'])) {
						$fieldModel->set('hideField', true);
					}
					$blockSeqSortSummaryFields[$blockSequence]['SUMMARY_FIELDS'][$fieldName] = $fieldModel;
				}
			}
		}
		$summaryFieldModelsList = [];
		ksort($blockSeqSortSummaryFields);
		foreach ($blockSeqSortSummaryFields as $blockSequence => $summaryFields) {
			$summaryFieldModelsList = array_replace_recursive($summaryFieldModelsList, $summaryFields);
		}
		return $summaryFieldModelsList;
	}
}
