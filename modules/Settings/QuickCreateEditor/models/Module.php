<?php
/* +***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 * *********************************************************************************************************************************** */

class Settings_QuickCreateEditor_Module_Model extends Vtiger_Module_Model
{

	public function updateFieldSequenceNumber($blockFieldSequence)
	{
		$log = vglobal('log');
		$log->debug("Entering Settings_QuickCreateEditor_Module_Model::updateFieldSequenceNumber(" . $blockFieldSequence . ") method ...");
		$fieldIdList = array();
		$db = PearDatabase::getInstance();

		$query = 'UPDATE vtiger_field SET ';
		$query .=' quickcreatesequence= CASE ';
		foreach ($blockFieldSequence as $newFieldSequence) {
			$fieldId = $newFieldSequence['fieldid'];
			$sequence = $newFieldSequence['sequence'];
			$block = $newFieldSequence['block'];
			$fieldIdList[] = $fieldId;

			$query .= ' WHEN fieldid=' . $fieldId . ' THEN ' . $sequence;
		}

		$query .=' END ';

		$query .= ' WHERE fieldid IN (' . generateQuestionMarks($fieldIdList) . ')';
		$db->pquery($query, array($fieldIdList));
		$log->debug("Exiting Settings_QuickCreateEditor_Module_Model::updateFieldSequenceNumber(" . $blockFieldSequence . ") method ...");
	}
}
