<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ********************************************************************************** */

class Settings_CronTasks_UpdateSequence_Action extends Settings_Vtiger_Index_Action
{
	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$moduleModel = Settings_CronTasks_Module_Model::getInstance($request->getModule(false));
		$response = new Vtiger_Response();
		if ($sequencesList = $request->getArray('sequencesList', 'Integer')) {
			$moduleModel->updateSequence($sequencesList);
			$response->setResult([true]);
		} else {
			$response->setError();
		}
		$response->emit();
	}
}
