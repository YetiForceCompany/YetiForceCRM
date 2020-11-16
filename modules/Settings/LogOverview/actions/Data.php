<?php

/**
 * Settings log overview action file.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Sołek <a.solek@yetiforce.com>
 */
/**
 * Settings log overview action class.
 */
class Settings_LogOverview_Data_Action extends Settings_Vtiger_Basic_Action
{
	/**
	 * Process.
	 *
	 * @param \App\Request $request
	 */
	public function process(App\Request $request)
	{
		$type = $request->getByType('type', 1);
		$range = $request->getByType('range', 'DateRangeUserFormat');
		if (!isset(\App\Log::$tableColumnMapping[$type])) {
			throw new \App\Exceptions\NoPermittedForAdmin('ERR_ILLEGAL_VALUE');
		}
		$query = (new \App\Db\Query())->from('l_#__' . $type);
		$logsCountAll = $logsCount = (int) $query->count('*');
		$query->offset($request->getInteger('start', 0));
		$query->limit($request->getInteger('limit', 10));
		$query->where(['between', 'time', $range[0] . ' 00:00:00', $range[1] . ' 23:59:59']);
		$data = [];

		foreach ($query->all() as $log) {
			foreach (\App\Log::$tableColumnMapping[$type] as $column => $value) {
				if ('date' === $value) {
					$log[$column] = \App\Fields\DateTime::formatToViewDate($log[$column]);
				}
			}
			$data[] = $log;
		}
		$columns = [];
		foreach (\App\Log::$tableColumnMapping[$type] as $column => $value) {
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
