<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ********************************************************************************** */

class Settings_CronTasks_AddCron_Action extends Settings_Vtiger_Index_Action
{

	public function process(Vtiger_Request $request)
	{
		vtlib\Cron::register(
			$request->get('cron_name'), $request->get('path'), $this->calculateFrequency($request->get('frequency_value'), $request->get('time_format')), $request->get('cron_module'), $request->get('status'), $this->getSquence(), $request->get('description')
		);

		header('Location: index.php?module=CronTasks&parent=Settings&view=List');
	}

	public function calculateFrequency($val, $format)
	{

		if ('mins' == $format) {
			return $val * 60;
		} else {
			return $val * (60 * 60);
		}
	}

	public function getSquence()
	{
		$db = \App\Db::getInstance();
		$maxSequence = $db->getUniqueID('vtiger_cron_task', 'sequence', false);
		return $maxSequence;
	}
}
