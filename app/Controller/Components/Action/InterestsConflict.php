<?php
/**
 * Conflict of interests index view file.
 *
 * @package   Controller
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

namespace App\Controller\Components\Action;

/**
 * Conflict of interests index view class.
 */
class InterestsConflict extends \App\Controller\Action
{
	use \App\Controller\ExposeMethod;

	/** {@inheritdoc} */
	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('getUnlock');
		$this->exposeMethod('getConfirm');
		$this->exposeMethod('updateUnlockStatus');
		$this->exposeMethod('updateConfirmStatus');
	}

	/** {@inheritdoc} */
	public function checkPermission(\App\Request $request)
	{
		switch ($request->getMode()) {
			case 'getUnlock':
			case 'updateUnlockStatus':
				if (!\in_array(\App\User::getCurrentUserId(), \Config\Components\InterestsConflict::$unlockUsersAccess) && !\App\User::getCurrentUserModel()->isAdmin()) {
					throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED');
				}
				break;
			case 'getConfirm':
			case 'updateConfirmStatus':
				if (!\in_array(\App\User::getCurrentUserId(), \Config\Components\InterestsConflict::$confirmUsersAccess) && !\App\User::getCurrentUserModel()->isAdmin()) {
					throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED');
				}
				break;
			default:
				throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED');
				break;
		}
		return true;
	}

	/**
	 * Get unlock data.
	 *
	 * @param \App\Request $request
	 *
	 * @return void
	 */
	public function getUnlock(\App\Request $request): void
	{
		header('content-type: text/json; charset=UTF-8');
		echo \App\Json::encode($this->getUnlockResponse($request));
	}

	/**
	 * Get confirmations data.
	 *
	 * @param \App\Request $request
	 *
	 * @return void
	 */
	public function getConfirm(\App\Request $request): void
	{
		header('content-type: text/json; charset=UTF-8');
		echo \App\Json::encode($this->getConfirmResponse($request));
	}

	/**
	 * Save unlock data.
	 *
	 * @param \App\Request $request
	 *
	 * @return void
	 */
	public function updateUnlockStatus(\App\Request $request): void
	{
		\App\Components\InterestsConflict::updateUnlockStatus($request->getInteger('id'), $request->getInteger('status'));
		$response = new \Vtiger_Response();
		$response->emit();
	}

	/**
	 * Save confirmations data.
	 *
	 * @param \App\Request $request
	 *
	 * @return void
	 */
	public function updateConfirmStatus(\App\Request $request): void
	{
		\App\Components\InterestsConflict::setCancel($request->getInteger('id'), $request->getInteger('baseRecord'), $request->getByType('comment', 'Text'));
		$response = new \Vtiger_Response();
		$response->emit();
	}

	/**
	 * Get response.
	 *
	 * @param \App\Request $request
	 *
	 * @return array
	 */
	public function getUnlockResponse(\App\Request $request): array
	{
		$query = $this->getUnlockQuery($request);
		$dataReader = $query->createCommand()->query();
		$rows = [];
		while ($row = $dataReader->read()) {
			$rows[] = [
				'id' => $row['id'],
				'date_time' => \App\Fields\DateTime::formatToDisplay($row['date_time']),
				'status' => $row['status'],
				'user_id' => \App\Fields\Owner::getUserLabel($row['user_id']),
				'related' => $row['related_id'],
				'source_id' => $row['source_id'],
				'comment' => \App\Layout::truncateText($row['comment'], 40, true, true),
				'modify_user' => $row['modify_user_id'] ? \App\Fields\Owner::getUserLabel($row['modify_user_id']) : null,
				'modify_date_time' => $row['modify_date_time'] ? \App\Fields\DateTime::formatToDisplay($row['modify_date_time']) : null,
			];
		}
		$ids = array_merge(array_column($rows, 'related'), array_column($rows, 'source_id'));
		\App\Record::getLabel($ids);
		\vtlib\Functions::getCRMRecordMetadata($ids);
		foreach ($rows as &$row) {
			$row['related'] = \App\Record::getHtmlLink($row['related'], null, \App\Config::main('href_max_length'));
			$info = '';
			if ($row['source_id']) {
				$info .= \App\Language::translate('LBL_SOURCE_RECORD') . ': ' . \App\Record::getHtmlLink($row['source_id'], null, \App\Config::main('href_max_length')) . '<br>';
			}
			if ($row['modify_user']) {
				$info .= \App\Language::translate('Last Modified By') . ': ' . $row['modify_user'] . '<br>';
			}
			if ($row['modify_date_time']) {
				$info .= \App\Language::translate('Modified Time') . ': ' . $row['modify_date_time'];
			}
			$row['info'] = \App\Purifier::encodeHtml($info);
		}
		$query->limit(null)->offset(null)->orderBy(null);
		return [
			'draw' => $request->getInteger('draw'),
			'iTotalRecords' => (new \App\Db\Query())->from('u_#__interests_conflict_unlock')->count(),
			'iTotalDisplayRecords' => $query->count(),
			'aaData' => $rows,
		];
	}

	/**
	 * Get query.
	 *
	 * @param \App\Request $request
	 *
	 * @return \App\Db\Query
	 */
	public function getUnlockQuery(\App\Request $request): \App\Db\Query
	{
		$columns = [];
		foreach ($request->getArray('columns') as $key => $value) {
			$columns[$key] = $value['data'];
		}
		$query = (new \App\Db\Query())->from('u_#__interests_conflict_unlock');
		$query->limit($request->getInteger('length'));
		$query->offset($request->getInteger('start'));
		$order = current($request->getArray('order', 'Alnum'));
		if ($order && isset($columns[$order['column']])) {
			$query->orderBy([$columns[$order['column']] => 'asc' === $order['dir'] ? SORT_ASC : SORT_DESC]);
		}
		if (!$request->isEmpty('date') && ($date = $request->getDateRange('date'))) {
			$query->andWhere(['between', 'date_time', $date[0] . ' 00:00:00', $date[1] . ' 23:59:59']);
		}
		if (!$request->isEmpty('users') && ($users = $request->getArray('users', 'Integer'))) {
			$query->andWhere(['user_id' => $users]);
		}
		if (!$request->isEmpty('status') && ($status = $request->getArray('status', 'Integer'))) {
			$query->andWhere(['status' => $status]);
		}
		if (!$request->isEmpty('related') && ($related = $request->getByType('related', 'Text'))) {
			$query->innerJoin('u_#__crmentity_label', 'u_#__interests_conflict_unlock.related_id = u_#__crmentity_label.crmid');
			$query->andWhere(['like', 'u_#__crmentity_label.label', "{$related}%", false]);
		}
		return $query;
	}

	/**
	 * Get response.
	 *
	 * @param \App\Request $request
	 *
	 * @return array
	 */
	public function getConfirmResponse(\App\Request $request): array
	{
		$queries = [
			'base' => $this->getConfirmQuery($request, 'u'),
		];
		if ($request->getBoolean('showHistory')) {
			$queries['log'] = $this->getConfirmQuery($request, 'b');
		}
		$queries = array_reverse($queries);
		$rows = [];
		foreach ($queries as $dbType => $query) {
			$dataReader = $query->createCommand(\App\Db::getInstance($dbType))->query();
			while ($row = $dataReader->read()) {
				$rows[] = [
					'db' => $dbType,
					'id' => $row['id'],
					'date_time' => \App\Fields\DateTime::formatToDisplay($row['date_time']),
					'status' => $row['status'],
					'user_id' => $row['user_id'],
					'user' => \App\Fields\Owner::getUserLabel($row['user_id']),
					'related_id' => $row['related_id'],
					'source_id' => $row['source_id'],
					'modify_user' => $row['modify_user_id'] ? \App\Fields\Owner::getUserLabel($row['modify_user_id']) : null,
					'modify_date_time' => $row['modify_date_time'] ? \App\Fields\DateTime::formatToDisplay($row['modify_date_time']) : null,
				];
			}
		}
		$ids = array_column($rows, 'related');
		\App\Record::getLabel($ids);
		\vtlib\Functions::getCRMRecordMetadata($ids);
		foreach ($rows as &$row) {
			$row['related'] = \App\Record::getHtmlLink($row['related_id'], null, \App\Config::main('href_max_length'));
			$info = '';
			if ($row['source_id']) {
				$info .= \App\Language::translate('LBL_SOURCE_RECORD') . ': ' . \App\Record::getHtmlLink($row['source_id'], null, \App\Config::main('href_max_length')) . '<br>';
			}
			if ($row['modify_user']) {
				$info .= \App\Language::translate('Last Modified By') . ': ' . $row['modify_user'] . '<br>';
			}
			if ($row['modify_date_time']) {
				$info .= \App\Language::translate('Modified Time') . ': ' . $row['modify_date_time'];
			}
			$row['info'] = \App\Purifier::encodeHtml($info);
		}
		$all = $filter = 0;
		foreach ($queries as $dbType => $query) {
			$query->limit(null)->offset(null)->orderBy(null);
			$filter += $query->count('id', \App\Db::getInstance($dbType));
			$queryAll = clone $query;
			$queryAll->where([]);
			$all += $queryAll->count('id', \App\Db::getInstance($dbType));
		}
		return [
			'draw' => $request->getInteger('draw'),
			'iTotalRecords' => $all,
			'iTotalDisplayRecords' => $filter,
			'aaData' => $rows,
		];
	}

	/**
	 * Get query.
	 *
	 * @param \App\Request $request
	 * @param string       $type
	 *
	 * @return \App\Db\Query
	 */
	public function getConfirmQuery(\App\Request $request, string $type): \App\Db\Query
	{
		$table = 'u' === $type ? 'u_#__interests_conflict_conf' : 'b_#__interests_conflict_conf';
		$columns = [];
		foreach ($request->getArray('columns') as $key => $value) {
			$columns[$key] = $value['data'];
		}
		$query = (new \App\Db\Query())->from($table);
		$query->limit($request->getInteger('length'));
		$query->offset($request->getInteger('start'));
		$order = current($request->getArray('order', 'Alnum'));
		if ($order && isset($columns[$order['column']])) {
			$query->orderBy([$columns[$order['column']] => 'asc' === $order['dir'] ? SORT_ASC : SORT_DESC]);
		}
		if (!$request->isEmpty('date') && ($date = $request->getDateRange('date'))) {
			$query->andWhere(['between', 'date_time', $date[0] . ' 00:00:00', $date[1] . ' 23:59:59']);
		}
		if (!$request->isEmpty('users') && ($users = $request->getArray('users', 'Integer'))) {
			$query->andWhere(['user_id' => $users]);
		}
		if (!$request->isEmpty('status') && ($status = $request->getArray('status', 'Integer'))) {
			$query->andWhere(['status' => $status]);
		}
		if (!$request->isEmpty('related') && ($related = $request->getByType('related', 'Text'))) {
			$query->leftJoin('u_#__crmentity_label', $table . '.related_id = u_#__crmentity_label.crmid');
			$query->andWhere(['or', ['like', 'related_label', "{$related}%", false], ['like', 'u_#__crmentity_label.label', "{$related}%", false]]);
		}
		return $query;
	}
}
