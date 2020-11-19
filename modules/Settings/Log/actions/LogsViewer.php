<?php

/**
 * Settings log viewer action file.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Sołek <a.solek@yetiforce.com>
 */
/**
 * Settings log viewer action class.
 */
class Settings_Log_LogsViewer_Action extends Settings_Vtiger_Basic_Action
{
	/**
	 * Process.
	 *
	 * @param \App\Request $request
	 */
	public function process(App\Request $request)
	{
		$type = $request->getByType('type', 'Text');
		$filterType = $request->get('typefilter');
		if ('DateTimeRange' === $filterType) {
			$range = $request->getByType('valuefilter', 'DateRangeUserFormat');
		}
		if (!isset(\App\Log::$logsViewerColumnMapping[$type])) {
			throw new \App\Exceptions\NoPermittedForAdmin('ERR_ILLEGAL_VALUE');
		}
		$query = (new \App\Db\Query())->from('l_#__' . $type);
		$logsCountAll = (int) $query->count('*');
		$query->offset($request->getInteger('start', 0));
		$query->limit($request->getInteger('limit', 10));
		if ('DateTimeRange' === $filterType) {
			$query->where(['between', 'time', $range[0] . ' 00:00:00', $range[1] . ' 23:59:59']);
		}
		$data = [];
		$dataReader = $query->createCommand()->query();
		while ($row = $dataReader->read()) {
			foreach (\App\Log::$logsViewerColumnMapping[$type]['columns'] as $key => $value) {
				if ('date' === $value['type']) {
					$row[$key] = \DateTimeField::convertToUserFormat($row[$key]);
				} elseif ('text' === $value['type']) {
					$row[$key] = \App\Layout::truncateHtml($row[$key], 'mini', 300);
				}
			}
			$data[] = $row;
		}
		$dataReader->close();
		$columns = [];
		foreach (\App\Log::$logsViewerColumnMapping[$type]['columns'] as $key => $value) {
			$columns[$key] = \App\Language::translate($value['fieldLabel'], $request->getModule(false));
		}
		$response = new Vtiger_Response();
		$response->setEmitType(Vtiger_Response::$EMIT_JSONTEXT);
		$response->setResult(\App\Json::encode([
			'data' => $data,
			'draw' => $request->getInteger('draw', 1),
			'recordsFiltered' => \count($data),
			'recordsTotal' => $logsCountAll,
			'columns' => $columns
		]));
		$response->emit();
	}
}
