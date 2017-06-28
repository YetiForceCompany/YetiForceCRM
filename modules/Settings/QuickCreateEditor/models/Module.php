<?php

/**
 * Settings QuickCreateEditor module model class
 * @package YetiForce.Model
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 */
class Settings_QuickCreateEditor_Module_Model extends Vtiger_Module_Model
{

	public static function updateFieldSequenceNumber($blockFieldSequence)
	{
		$fieldIdList = [];
		$db = PearDatabase::getInstance();

		$query = 'UPDATE vtiger_field SET ';
		$query .= ' quickcreatesequence= CASE ';
		foreach ($blockFieldSequence as $newFieldSequence) {
			$fieldId = $newFieldSequence['fieldid'];
			$sequence = $newFieldSequence['sequence'];
			$fieldIdList[] = $fieldId;

			$query .= ' WHEN fieldid=' . $fieldId . ' THEN ' . $sequence;
		}

		$query .= ' END ';
		$query .= sprintf(' WHERE fieldid IN (%s)', generateQuestionMarks($fieldIdList));
		$db->pquery($query, array($fieldIdList));
	}
}
