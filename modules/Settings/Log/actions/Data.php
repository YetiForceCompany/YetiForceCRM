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
		$type = $request->getByType('type', 1);
		$logs = \App\Log::getLogs($type, 'advanced', false, $request->getAll());
		$logsCount = \App\Log::getLogs($type, 'advanced', true, $request->getAll());
		$logsCountAll = \App\Log::getLogs($type, 'all', true);
		$data = [];
		foreach ($logs as $log) {
			$tmp = [];
			foreach (Settings_Log_Module_Model::$tableHeaders[$type] as $column) {
				if ($column === 'url') {
					$url = explode('?', $log['url'])[1];
					$tmp[] = "<a href=\"index.php?$url\" title=\"index.php?$url\">" . substr($url, 0, 50) . '...</a>';
				} elseif ($column === 'agent') {
					$tmp[] = "<span title=\"{$log['agent']}\">" . substr($log['agent'], 0, 50) . '...</span>';
				} elseif ($column === 'request') {
					$requestArray = '';
					foreach (\App\Json::decode($log[$column]) as $key => $val) {
						$requestArray .= "$key => $val<br>";
					}
					$tmp[] = $requestArray;
				} else {
					$tmp[] = $log[$column];
				}
			}
			$data[] = array_values($tmp);
		}
		$response = new Vtiger_Response();
		$response->setClear([
			'data' => $data,
			'draw' => $request->getInteger('draw', 1),
			'recordsFiltered' => (int) $logsCount,
			'recordsTotal' => (int) $logsCountAll
		]);
		$response->emit();
	}
}
