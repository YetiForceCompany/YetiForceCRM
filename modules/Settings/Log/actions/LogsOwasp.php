<?php

/**
 * Data Action Class for Log.
 *
 * @package   Action
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Michał Lorencik <m.lorencik@yetiforce.com>
 */
class Settings_Log_LogsOwasp_Action extends Settings_Vtiger_Basic_Action
{
	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$type = $request->getByType('type', 1);
		$range = $request->getByType('range', 'DateRangeUserFormat');
		if (!isset(App\Log::$owaspColumnMapping[$type])) {
			throw new \App\Exceptions\NoPermittedForAdmin('ERR_ILLEGAL_VALUE');
		}
		$query = (new \App\Db\Query())->from('o_#__' . $type);
		$logsCountAll = $logsCount = (int) $query->count('*');
		$query->offset($request->getInteger('start', 0));
		$query->limit($request->getInteger('limit', 10));
		$query->where(['between', 'date', $range[0] . ' 00:00:00', $range[1] . ' 23:59:59']);
		$order = $request->getMultiDimensionArray('order', [
			[
				'column' => 'Integer',
				'dir' => 'Standard'
			]
		]);
		if (isset($order['0']['column'])) {
			$column = \App\Log::$owaspColumnMapping[$type][$order['0']['column']];
			$dir = ('asc' === $order['0']['dir']) ? \SORT_ASC : \SORT_DESC;
			$query->orderBy([$column => $dir]);
		} else {
			$query->orderBy(['id' => \SORT_DESC]);
		}
		$data = [];
		foreach ($query->all() as $log) {
			foreach (\App\Log::$owaspColumnMapping[$type] as $column) {
				if (\in_array($column, ['url', 'agent', 'referer'])) {
					$log[$column] = \App\Purifier::encodeHtml($log[$column]);
				}
				if ('url' === $column && ($urlParams = explode('?', $log['url'])) && isset($urlParams[1])) {
					$log[$column] = $urlParams[1];
				} elseif ('request' === $column) {
					$requestArray = '';
					foreach (\App\Json::decode($log[$column]) as $key => $val) {
						$val = (\is_array($val)) ? var_export($val, true) : $val;
						$requestArray .= \App\Purifier::encodeHtml("$key => $val") . PHP_EOL;
					}
					$log[$column] = \App\Layout::truncateText($requestArray, 70, true);
				}
			}
			$data[] = $log;
		}

		$columns = [];
		foreach (\App\Log::$owaspColumnMapping[$type] as $column) {
			$columns[$column] = \App\Language::translate('LBL_' . strtoupper($column), $request->getModule(false));
		}
		$response = new Vtiger_Response();
		$response->setEmitType(Vtiger_Response::$EMIT_JSONTEXT);
		$response->setResult(\App\Json::encode([
			'data' => $data,
			'draw' => $request->getInteger('draw', 1),
			'recordsFiltered' => $logsCount,
			'recordsTotal' => $logsCountAll,
			'columns' => $columns
		]));
		$response->emit();
	}
}
