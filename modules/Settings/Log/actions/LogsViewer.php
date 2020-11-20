<?php

/**
 * Settings log viewer action file.
 *
 * @package   Action
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Sołek <a.solek@yetiforce.com>
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
/**
 * Settings log viewer action class.
 */
class Settings_Log_LogsViewer_Action extends Settings_Vtiger_Basic_Action
{
	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$type = $request->getByType('type');
		if (!isset(\App\Log::$logsViewerColumnMapping[$type])) {
			throw new \App\Exceptions\NoPermittedForAdmin('ERR_ILLEGAL_VALUE');
		}
		$query = (new \App\Db\Query())->from(\App\Log::$logsViewerColumnMapping[$type]['table']);
		$logsCountAll = (int) $query->count('*');
		$query->offset($request->getInteger('start', 0));
		$query->limit($request->getInteger('limit', 10));
		if ($request->has('time')) {
			$range = $request->getByType('time', 'DateRangeUserFormat');
			$query->where(['between', 'time', $range[0] . ' 00:00:00', $range[1] . ' 23:59:59']);
		}
		$columns = \App\Log::$logsViewerColumnMapping[$type]['columns'];
		$rows = [];
		$dataReader = $query->createCommand()->query();
		while ($row = $dataReader->read()) {
			$r = [];
			foreach ($columns as $key => $value) {
				if ('date' === $value['type']) {
					$r[] = \DateTimeField::convertToUserFormat($row[$key]);
				} elseif ('text' === $value['type']) {
					$r[] = \App\Layout::truncateText($row[$key], 50, true);
				}
			}
			$rows[] = $r;
		}
		$dataReader->close();
		$result = [
			'draw' => $request->getInteger('draw'),
			'iTotalRecords' => $logsCountAll,
			'iTotalDisplayRecords' => \count($rows),
			'aaData' => $rows
		];
		header('content-type: text/json; charset=UTF-8');
		echo \App\Json::encode($result);
	}
}
