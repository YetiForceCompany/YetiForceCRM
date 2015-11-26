<?php
/*+***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 *************************************************************************************************************************************/

class RequirementCards_Record_Model extends Vtiger_Record_Model {

	/**
	 * TODO remove this function
	 * Function to set record module field values
	 * @param parent record model
	 */
	function setRecordFieldValues($parentRecordModel) {
		$log = vglobal('log');
		$log->debug("Entering RequirementCards_Record_Model::setRecordFieldValues() method ...");
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$parentModuleName = $parentRecordModel->getModuleName();
		if($parentModuleName == 'QuotesEnquires'){
			$fieldsToGenerate = $this->getListFieldsToGenerate($parentModuleName, $this->getModuleName());
			foreach ($fieldsToGenerate as $key => $fieldName) {
					if (getFieldVisibilityPermission($parentModuleName, $currentUser->getId(), $key) == 0 || $key == 'id') {
							$this->set($fieldName, $parentRecordModel->get($key));
					}
			}
		}
		parent::process($parentRecordModel);
		$log->debug("Exiting RequirementCards_Record_Model::setRecordFieldValues() method ...");
	}
}
