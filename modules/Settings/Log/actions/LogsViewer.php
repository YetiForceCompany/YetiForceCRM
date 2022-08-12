<?php

/**
 * Settings log viewer action file.
 *
 * @package   Action
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
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
		$rows = $columns = [];
		foreach ($request->getArray('columns') as $key => $value) {
			$columns[$key] = $value['name'];
		}
		$mapping = \App\Log::$logsViewerColumnMapping[$type];
		if (\App\Db::getInstance()->isTableExists($mapping['table'])) {
			$query = (new \App\Db\Query())->from($mapping['table']);
			$logsCountAll = (int) $query->count('*');
			$this->loadFilter($request, $mapping['filter'], $query);
			$count = (int) $query->count('*');
			$query->limit($request->getInteger('length'))->offset($request->getInteger('start'));
			$order = current($request->getArray('order', App\Purifier::ALNUM));
			if ($order && isset($columns[$order['column']], $mapping['columns'][$columns[$order['column']]])) {
				$query->orderBy([$columns[$order['column']] => \App\Db::ASC === strtoupper($order['dir']) ? \SORT_ASC : \SORT_DESC]);
			}
			$dataReader = $query->createCommand()->query();
			while ($row = $dataReader->read()) {
				$r = [];
				foreach ($mapping['columns'] as $key => $value) {
					switch ($value['type']) {
						case 'DateTime':
							$r[] = \App\Fields\DateTime::formatToDisplay($row[$key]);
							break;
						case 'Date':
							$r[] = \App\Fields\Date::formatToDisplay($row[$key]);
							break;
						case 'Text':
							$r[] = $row[$key] ? \App\Layout::truncateText($row[$key], 50, true) : '';
							break;
						case 'Owner':
							$r[] = \App\Fields\Owner::getUserLabel($row[$key]);
							break;
						case 'Reference':
							$r[] = \App\Record::getLabel($row[$key]);
							break;
					}
				}
				$rows[] = $r;
			}
			$dataReader->close();
		}
		header('content-type: text/json; charset=UTF-8');
		echo \App\Json::encode([
			'draw' => $request->getInteger('draw'),
			'iTotalRecords' => $logsCountAll ?? 0,
			'iTotalDisplayRecords' => $count ?? 0,
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
			if ($request->has($key) && '' !== $request->getRaw($key)) {
				switch ($value) {
					case 'DateTimeRange':
						$range = $request->getByType($key, 'DateRangeUserFormat');
						$query->andWhere(['between', $key, $range[0] . ' 00:00:00', $range[1] . ' 23:59:59']);
						break;
					case 'Text':
						$query->andWhere(['like', $key, $request->getByType($key, 'Text')]);
						break;
				}
			}
		}
	}
}
