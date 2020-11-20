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
		$logsViewerColumnMapping = \App\Log::$logsViewerColumnMapping[$type];
		$query = (new \App\Db\Query())->from($logsViewerColumnMapping['table']);
		$logsCountAll = (int) $query->count('*');
		$query->offset($request->getInteger('start', 0));
		$query->limit($request->getInteger('limit', 10));
		$query = $this->getTypeFilter($request, $logsViewerColumnMapping['filter'], $query);
		$rows = [];
		$dataReader = $query->createCommand()->query();
		while ($row = $dataReader->read()) {
			$r = [];
			foreach ($logsViewerColumnMapping['columns'] as $key => $value) {
				switch ($value['type']) {
					case 'date':
						$r[] = \DateTimeField::convertToUserFormat($row[$key]);
						break;
					case 'text':
						$r[] = \App\Layout::truncateText($row[$key], 50, true);
						break;
					case 'userId':
						$r[] = \App\User::getUserModel($row[$key])->getName();
						break;
					case 'reference':
						$r[] = \App\Record::getLabel($row[$key]);
						break;
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

	public function getTypeFilter($request, $filter, $query)
	{
		foreach ($filter as $key => $value) {
			if ($request->has($key) && !$request->isEmpty($key)) {
				switch ($value) {
					case 'DateTimeRange':
						$range = $request->getByType($key, 'DateRangeUserFormat');
						$return = $query->where(['between', $key, $range[0] . ' 00:00:00', $range[1] . ' 23:59:59']);
						break;
					case 'Text':
						$return = $query->andWhere(['like', $key, $request->getByType($key)]);
						break;
				}
			}
		}
		return $return;
	}
}
