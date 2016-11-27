<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class Settings_CronTasks_Module_Model extends Settings_Vtiger_Module_Model
{

	public $baseTable = 'vtiger_cron_task';
	public $baseIndex = 'id';
	public $listFields = array('sequence' => 'Sequence', 'name' => 'Cron Job', 'frequency' => 'Frequency(H:M)', 'status' => 'Status', 'laststart' => 'Last Start', 'lastend' => 'Last End');
	public $nameFields = array('');
	public $name = 'CronTasks';

	/**
	 * Function to get editable fields from this module
	 * @return array List of fieldNames
	 */
	public function getEditableFieldsList()
	{
		return array('frequency', 'status');
	}

	/**
	 * Function to update sequence of several records
	 * @param array $sequencesList
	 */
	public function updateSequence($sequencesList)
	{
		$db = App\Db::getInstance();
		$caseSequence = 'CASE';
		foreach ($sequencesList as $sequence => $recordId) {
			$caseSequence .= ' WHEN ' . $db->quoteColumnName('id') . ' = ' . $db->quoteValue($recordId) . ' THEN ' . $db->quoteValue($sequence);
		}
		$caseSequence .= ' END';
		$db->createCommand()
			->update('vtiger_cron_task', ['sequence' => new yii\db\Expression($caseSequence)])
			->execute();
	}

	public function hasCreatePermissions()
	{
		return false;
	}

	public function isPagingSupported()
	{
		return false;
	}
}
