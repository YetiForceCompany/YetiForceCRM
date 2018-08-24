<?php

/**
 * Data Action Class for Log.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Michał Lorencik <m.lorencik@yetiforce.com>
 */
class Settings_Log_Data_Action extends Settings_Vtiger_Basic_Action
{
	/**
	 * Process.
	 *
	 * @param \App\Request $request
	 */
	public function process(\App\Request $request)
	{
		$logs = \App\Log::getLogs($request->getByType('type', 1), 'all');
		$data = [];
		foreach ($logs as $log) {
			$data[] = array_values($log);
		}
		$response = new Vtiger_Response();
		$response->setResult($data);
		$response->emit();
	}
}
