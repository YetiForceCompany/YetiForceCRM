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
		if (!isset(App\Log::$tableColumnMapping[$type])) {
			throw new \App\Exceptions\NoPermittedForAdmin('ERR_ILLEGAL_VALUE');
		}
		$query = (new \App\Db\Query())->from('o_#__' . $type);
		$logsCountAll = $logsCount = (int) $query->count('*');
		$query->offset($request->getInteger('start', 0));
		$query->limit($request->getInteger('limit', 10));
		$order = $request->getArray('order', false);
		if (isset($order['0']['column'])) {
			$column = \App\Log::$tableColumnMapping[$type][$order['0']['column']];
			$dir = ($order['0']['dir'] === 'asc') ? \SORT_ASC : \SORT_DESC;
			$query->orderBy([$column => $dir]);
		} else {
			$query->orderBy(['id' => \SORT_DESC]);
		}
		$data = [];
		foreach ($query->all() as $log) {
			$tmp = [];
			foreach (\App\Log::$tableColumnMapping[$type] as $column) {
				if ($column === 'url') {
					$url = \App\Purifier::encodeHtml(explode('?', $log['url'])[1]);
					$tmp[] = "<a href=\"index.php?$url\" title=\"index.php?$url\">" . substr($url, 0, 50) . '...</a>';
				} elseif ($column === 'agent') {
					$tmp[] = "<span title=\"{$log['agent']}\">" . substr($log['agent'], 0, 50) . '...</span>';
				} elseif ($column === 'request') {
					$requestArray = '';
					foreach (\App\Json::decode($log[$column]) as $key => $val) {
						$requestArray .= "$key => $val<br>";
					}
					$tmp[] = \App\Purifier::purifyHtml($requestArray);
				} else {
					$tmp[] = $log[$column];
				}
			}
			$data[] = $tmp;
		}
		$response = new Vtiger_Response();
		$response->setEmitType(Vtiger_Response::$EMIT_JSONTEXT);
		$response->setResult(\App\Json::encode([
			'data' => $data,
			'draw' => $request->getInteger('draw', 1),
			'recordsFiltered' => $logsCount,
			'recordsTotal' => $logsCountAll
		]));
		$response->emit();
	}
}
