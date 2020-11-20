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
		$mapping = \App\Log::$logsViewerColumnMapping[$type];
		$query = (new \App\Db\Query())->from($mapping['table']);
		$logsCountAll = (int) $query->count('*');
		$query->offset($request->getInteger('start', 0));
		$query->limit($request->getInteger('limit', 10));
		$this->loadFilter($request, $mapping['filter'], $query);
		$rows = [];
		$dataReader = $query->createCommand()->query();
		while ($row = $dataReader->read()) {
			$r = [];
			foreach ($mapping['columns'] as $key => $value) {
				switch ($value['type']) {
					case 'date':
						$r[] = \App\Fields\DateTime::formatToDisplay($row[$key]);
						break;
					case 'text':
						$r[] = \App\Layout::truncateText($row[$key], 50, true);
						break;
					case 'userId':
						$r[] = \App\Fields\Owner::getUserLabel($row[$key]);
						break;
					case 'reference':
						$r[] = \App\Record::getLabel($row[$key]);
						break;
				}
			}
			$rows[] = $r;
		}
		$dataReader->close();
		header('content-type: text/json; charset=UTF-8');
		echo \App\Json::encode([
			'draw' => $request->getInteger('draw'),
			'iTotalRecords' => $logsCountAll,
			'iTotalDisplayRecords' => \count($rows),
			'aaData' => $rows
		]);
	}

	/**
	 * Load filter.
	 *
	 * @param App\Request  $request
	 * @param array        $filter
	 * @param App\Db\Query $query
	 */
	public function loadFilter(App\Request $request, array $filter, App\Db\Query &$query)
	{
		foreach ($filter as $key => $value) {
			if ($request->has($key)) {
				switch ($value) {
					case 'DateTimeRange':
						$range = $request->getByType($key, 'DateRangeUserFormat');
						$query->where(['between', $key, $range[0] . ' 00:00:00', $range[1] . ' 23:59:59']);
						break;
					case 'Text':
						$query->andWhere(['like', $key, $request->getByType($key, 'Text')]);
						break;
				}
			}
		}
	}
}
