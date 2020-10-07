<?php

/**
 * Settings MailRbl get data action file.
 *
 * @package   Settings.Action
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

/**
 * Settings MailRbl get data action class.
 */
class Settings_MailRbl_GetData_Action extends \App\Controller\Action
{
	use \App\Controller\ExposeMethod;
	use \App\Controller\Traits\SettingsPermission;

	/**
	 * {@inheritdoc}
	 */
	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('request');
		$this->exposeMethod('blackList');
		$this->exposeMethod('whiteList');
		$this->exposeMethod('publicRbl');
	}

	/**
	 * Request mode.
	 *
	 * @param App\Request $request
	 */
	public function request(App\Request $request)
	{
		$rows = [];
		$query = $this->getQuery($request);
		$query->from('s_#__mail_rbl_request')->select(['id', 'status', 'type', 'datetime', 'user', 'header']);
		$dataReader = $query->createCommand(\App\Db::getInstance('admin'))->query();
		while ($row = $dataReader->read()) {
			$message = \ZBateson\MailMimeParser\Message::from($row['header']);
			$status = \App\Mail\Rbl::REQUEST_STATUS[$row['status']];
			$type = \App\Mail\Rbl::LIST_TYPES[$row['type']];
			$rows[] = [
				'id' => $row['id'],
				'datetime' => \App\Fields\DateTime::formatToDisplay($row['datetime']),
				'user' => \App\Fields\Owner::getUserLabel($row['user']),
				'sender' => \App\Purifier::encodeHtml($message->getHeaderValue('from')),
				'recipient' => \App\Purifier::encodeHtml($message->getHeaderValue('to')),
				'statusId' => $row['status'],
				'status' => "<span class=\"{$status['icon']} mr-2\"></span>" . \App\Language::translate($status['label'], 'Settings:MailRbl'),
				'type' => "<span class=\"{$type['icon']} mr-2\"></span>" . \App\Language::translate($type['label'], 'Settings:MailRbl'),
			];
		}
		$query->limit(null)->offset(null)->orderBy(null);
		$count = $query->count();
		$result = [
			'draw' => $request->getInteger('draw'),
			'iTotalRecords' => $query->where(null)->count(),
			'iTotalDisplayRecords' => $count,
			'aaData' => $rows
		];
		header('content-type: text/json; charset=UTF-8');
		echo \App\Json::encode($result);
	}

	/**
	 * Black list mode.
	 *
	 * @param App\Request $request
	 */
	public function blackList(App\Request $request)
	{
		$rows = [];
		$query = $this->getQuery($request);
		$query->from('s_#__mail_rbl_list')->andWhere(['type' => \App\Mail\Rbl::LIST_TYPE_BLACK_LIST]);
		$dataReader = $query->createCommand(\App\Db::getInstance('admin'))->query();
		while ($row = $dataReader->read()) {
			$status = \App\Mail\Rbl::LIST_STATUS[$row['status']];
			$rows[] = [
				'id' => $row['id'],
				'ip' => $row['ip'],
				'statusId' => $row['status'],
				'request' => $row['request'],
				'status' => "<span class=\"{$status['icon']} mr-2\"></span>" . \App\Language::translate($status['label'], 'Settings:MailRbl'),
			];
		}
		$query->limit(null)->offset(null)->orderBy(null);
		$count = $query->count();
		$result = [
			'draw' => $request->getInteger('draw'),
			'iTotalRecords' => $query->where(['type' => \App\Mail\Rbl::LIST_TYPE_BLACK_LIST])->count(),
			'iTotalDisplayRecords' => $count,
			'aaData' => $rows
		];
		header('content-type: text/json; charset=UTF-8');
		echo \App\Json::encode($result);
	}

	/**
	 * White list mode.
	 *
	 * @param App\Request $request
	 */
	public function whiteList(App\Request $request)
	{
		$rows = [];
		$query = $this->getQuery($request);
		$query->from('s_#__mail_rbl_list')->andWhere(['type' => \App\Mail\Rbl::LIST_TYPE_WHITE_LIST]);
		$dataReader = $query->createCommand(\App\Db::getInstance('admin'))->query();
		while ($row = $dataReader->read()) {
			$status = \App\Mail\Rbl::LIST_STATUS[$row['status']];
			$rows[] = [
				'id' => $row['id'],
				'ip' => $row['ip'],
				'statusId' => $row['status'],
				'request' => $row['request'],
				'status' => "<span class=\"{$status['icon']} mr-2\"></span>" . \App\Language::translate($status['label'], 'Settings:MailRbl'),
			];
		}
		$query->limit(null)->offset(null)->orderBy(null);
		$count = $query->count();
		$result = [
			'draw' => $request->getInteger('draw'),
			'iTotalRecords' => $query->where(['type' => \App\Mail\Rbl::LIST_TYPE_WHITE_LIST])->count(),
			'iTotalDisplayRecords' => $count,
			'aaData' => $rows
		];
		header('content-type: text/json; charset=UTF-8');
		echo \App\Json::encode($result);
	}

	/**
	 * Public RBL mode.
	 *
	 * @param App\Request $request
	 */
	public function publicRbl(App\Request $request)
	{
		$rows = [];
		$query = $this->getQuery($request);
		$query->from('s_#__mail_rbl_list')->select(['ip', 'status'])->andWhere(['type' => [\App\Mail\Rbl::LIST_TYPE_PUBLIC_BLACK_LIST, \App\Mail\Rbl::LIST_TYPE_PUBLIC_WHITE_LIST]]);
		$dataReader = $query->createCommand(\App\Db::getInstance('admin'))->query();
		while ($row = $dataReader->read()) {
			$status = \App\Mail\Rbl::LIST_STATUS[$row['status']];
			$rows[] = [
				'ip' => $row['ip'],
				'statusId' => $row['status'],
				'status' => "<span class=\"{$status['icon']} mr-2\"></span>" . \App\Language::translate($status['label'], 'Settings:MailRbl'),
			];
		}
		$query->limit(null)->offset(null)->orderBy(null);
		$count = $query->count();
		$result = [
			'draw' => $request->getInteger('draw'),
			'iTotalRecords' => $query->where(['type' => [\App\Mail\Rbl::LIST_TYPE_PUBLIC_BLACK_LIST, \App\Mail\Rbl::LIST_TYPE_PUBLIC_WHITE_LIST]])->count(),
			'iTotalDisplayRecords' => $count,
			'aaData' => $rows
		];
		header('content-type: text/json; charset=UTF-8');
		echo \App\Json::encode($result);
	}

	/**
	 * Get query.
	 *
	 * @param App\Request $request
	 *
	 * @return App\Db\Query
	 */
	private function getQuery(App\Request $request): App\Db\Query
	{
		$columns = [];
		foreach ($request->getArray('columns') as $key => $value) {
			$columns[$key] = $value['data'];
		}
		$query = (new \App\Db\Query());
		$query->limit($request->getInteger('length'));
		$query->offset($request->getInteger('start'));
		$order = current($request->getArray('order', 'Alnum'));
		if ($order && isset($columns[$order['column']])) {
			$query->orderBy([$columns[$order['column']] => 'asc' === $order['dir'] ? \SORT_ASC : \SORT_DESC]);
		}
		if (!$request->isEmpty('type') && ($type = $request->getArray('type', 'Integer'))) {
			$query->andWhere(['type' => $type]);
		}
		if (!$request->isEmpty('status') && ($status = $request->getArray('status', 'Integer'))) {
			$query->andWhere(['status' => $status]);
		}
		if (!$request->isEmpty('ip') && ($ip = $request->getByType('ip', 'Ip'))) {
			$query->andWhere(['ip' => $ip]);
		}
		if (!$request->isEmpty('date') && ($date = $request->getDateRange('date'))) {
			$query->andWhere(['between', 'datetime', $date[0] . ' 00:00:00', $date[1] . ' 23:59:59']);
		}
		if (!$request->isEmpty('users') && ($users = $request->getArray('users', 'Integer'))) {
			$query->andWhere(['user' => $users]);
		}
		return $query;
	}
}
