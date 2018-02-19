<?php

/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class Settings_CronTasks_Module_Model extends Settings_Vtiger_Module_Model {

	public $baseTable = 'vtiger_cron_task';
	public $baseIndex = 'id';
	public $listFields = ['sequence' => 'Sequence', 'name' => 'Cron Job', 'frequency' => 'Frequency(H:M)', 'status' => 'Status', 'laststart' => 'Last Start', 'lastend' => 'Last End', 'duration' => 'LBL_DURATION'];
	public $nameFields = [''];
	public $name = 'CronTasks';

	/**
	 * Function to get editable fields from this module.
	 *
	 * @return array List of fieldNames
	 */
	public function getEditableFieldsList() {
		return ['frequency', 'status'];
	}

	/**
	 * Function to update sequence of several records.
	 *
	 * @param array $sequencesList
	 */
	public function updateSequence($sequencesList) {
		$db = App\Db::getInstance();
		$caseSequence = 'CASE';
		foreach ($sequencesList as $sequence => $recordId) {
			$caseSequence .= ' WHEN ' . $db->quoteColumnName('id') . ' = ' . $db->quoteValue($recordId) . ' THEN ' . $db->quoteValue($sequence);
		}
		$caseSequence .= ' END';
		$db->createCommand()->update('vtiger_cron_task', ['sequence' => new yii\db\Expression($caseSequence)])->execute();
	}

	public function hasCreatePermissions() {
		return false;
	}

	public function isPagingSupported() {
		return false;
	}

	/**
	 * Get last executed Cron info formated by user settings.
	 *
	 * @return array ['duration'=>'0g 0m 0s','laststart'=>'2018-12-01 10:00:00','lastend'=>'...']
	 */
	public function getLastCronIteration() {
		$result = [
			'duration' => 0,
			'laststart' => 0,
			'lastend' => 0,
		];
		$totalDiff = 0;

		$rows = (new \App\Db\Query())
				->from('vtiger_cron_task')
				->where(['>', 'status', Settings_CronTasks_Record_Model::$STATUS_DISABLED])
				->orderBy(['frequency' => SORT_ASC, 'sequence' => SORT_ASC])
				->createCommand()
				->query()
				->readAll();

		foreach ($rows as $row) {
			$record = new Settings_CronTasks_Record_Model($row);
			$diff = $record->getTimeDiff();
			if (!$record->isRunning() && $lastEnd && ((int) $lastEnd - (int) $lastStart) >= 0) {
				$totalDiff += $diff;
			} else {
				$result['duration'] = $record->getDuration();
				$result['laststart'] = $record->get('laststart');
				$result['lastend'] = $record->get('lastend');
				return $result;
			}
		}

		$result['']

		return $result;
	}

}
