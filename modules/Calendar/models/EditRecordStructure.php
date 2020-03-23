<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

/**
 * Calendar Edit View Record Structure Model.
 */
class Calendar_EditRecordStructure_Model extends Vtiger_EditRecordStructure_Model
{
	/**
	 * Function to get the values in stuctured format.
	 *
	 * @return <array> - values in structure array('block'=>array(fieldinfo));
	 */
	public function getStructure()
	{
		if (!empty($this->structuredValues)) {
			return $this->structuredValues;
		}

		$values = [];
		$recordModel = $this->getRecord();
		$recordExists = !empty($recordModel);
		$moduleModel = $this->getModule();
		$blockModelList = $moduleModel->getBlocks();

		foreach ($blockModelList as $blockLabel => $blockModel) {
			$fieldModelList = $blockModel->getFields();
			if (!empty($fieldModelList)) {
				$values[$blockLabel] = [];
				foreach ($fieldModelList as $fieldName => $fieldModel) {
					if ($fieldModel->isEditable()) {
						if ($recordExists) {
							$fieldValue = $recordModel->get($fieldName);
							if ('date_start' === $fieldName) {
								$fieldValue = $fieldValue . ' ' . $recordModel->get('time_start');
							} elseif ('due_date' == $fieldName && 'Calendar' != $moduleModel->get('name')) {
								//Do not concat duedate and endtime for Tasks as it contains only duedate
								if ('Calendar' != $moduleModel->getName()) {
									$fieldValue = $fieldValue . ' ' . $recordModel->get('time_end');
								}
							} elseif ('activitystatus' === $fieldName && empty($fieldValue)) {
								$currentUserModel = Users_Record_Model::getCurrentUserModel();
								$defaulteventstatus = $currentUserModel->get('defaulteventstatus');
								$fieldValue = $defaulteventstatus;
							} elseif ('activitytype' === $fieldName && empty($fieldValue)) {
								$currentUserModel = Users_Record_Model::getCurrentUserModel();
								$defaultactivitytype = $currentUserModel->get('defaultactivitytype');
								$fieldValue = $defaultactivitytype;
							}
							$fieldModel->set('fieldvalue', $fieldValue);
						}
						$values[$blockLabel][$fieldName] = $fieldModel;
						if ($fieldModel->get('tabindex') > Vtiger_Field_Model::$tabIndexLastSeq) {
							Vtiger_Field_Model::$tabIndexLastSeq = $fieldModel->get('tabindex');
						}
					}
				}
			}
		}
		$this->structuredValues = $values;
		++Vtiger_Field_Model::$tabIndexLastSeq;
		return $values;
	}
}
